<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/totp.php';
require_once __DIR__ . '/crypto.php';

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_PATH . '/admin/login.php');
        exit;
    }
    enforceSessionSecurity();
}

// Session hardening: idle timeout + fingerprint (IP + User-Agent) binding.
// Destroys the session when it's been idle too long or was hijacked.
function enforceSessionSecurity(): void {
    if (!isLoggedIn()) return;

    if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > SESSION_TIMEOUT_MINUTES * 60) {
        session_unset();
        session_destroy();
        header('Location: ' . BASE_PATH . '/admin/login.php?timeout=1');
        exit;
    }
    $_SESSION['last_activity'] = time();

    $fp = sessionFingerprint();
    if (isset($_SESSION['fingerprint']) && !hash_equals($_SESSION['fingerprint'], $fp)) {
        session_unset();
        session_destroy();
        header('Location: ' . BASE_PATH . '/admin/login.php');
        exit;
    }
    $_SESSION['fingerprint'] = $fp;
}

function sessionFingerprint(): string {
    return hash('sha256', clientIp() . '|' . ($_SERVER['HTTP_USER_AGENT'] ?? ''));
}

// Verify password against the stored bcrypt hash. Returns user id or null.
function login(string $password): ?int {
    global $pdo, $dbAvailable;
    if (!$dbAvailable) return null;
    try {
        $stmt = $pdo->query("SELECT id, password_hash FROM users ORDER BY id LIMIT 1");
        $user = $stmt->fetch();
        if (!$user || $user['password_hash'] === '') return null;
        return password_verify($password, $user['password_hash']) ? (int)$user['id'] : null;
    } catch (PDOException $e) {
        return null;
    }
}

// Fully establish the authenticated session (regenerates the id).
function completeLogin(int $userId): void {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['last_activity'] = time();
    $_SESSION['fingerprint'] = sessionFingerprint();
}

function generateCsrfToken(): string {
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function validateCsrfToken(?string $token): bool {
    return !empty($token) && hash_equals($_SESSION['_csrf_token'] ?? '', $token);
}

function logout(): void {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_PATH . '/admin/login.php');
    exit;
}

function clientIp(): string {
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// --- 2FA (TOTP) helpers ---

function getUser(): ?array {
    global $pdo, $dbAvailable;
    if (!$dbAvailable) return null;
    try {
        $stmt = $pdo->query("SELECT * FROM users ORDER BY id LIMIT 1");
        $user = $stmt->fetch();
        return $user ?: null;
    } catch (PDOException $e) {
        return null;
    }
}

function twoFactorEnabled(int $userId): bool {
    global $pdo, $dbAvailable;
    if (!$dbAvailable) return false;
    try {
        $stmt = $pdo->prepare("SELECT totp_enabled FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn() === 1;
    } catch (PDOException $e) {
        return false;
    }
}

function setTwoFactorSecret(int $userId, string $secret, int $enabled = 0): void {
    global $pdo, $dbAvailable;
    if (!$dbAvailable) return;
    try {
        $pdo->prepare("UPDATE users SET totp_secret = ?, totp_enabled = ? WHERE id = ?")
            ->execute([dbEncrypt($secret), $enabled, $userId]);
    } catch (PDOException $e) {}
}

function disableTwoFactor(int $userId): void {
    global $pdo, $dbAvailable;
    if (!$dbAvailable) return;
    try {
        $pdo->prepare("UPDATE users SET totp_secret = '', totp_enabled = 0 WHERE id = ?")
            ->execute([$userId]);
    } catch (PDOException $e) {}
}

// Returns minutes remaining before the next allowed attempt (0 = not locked).
// Runs server-side on every request, so it also blocks direct POST / scripted
// brute force (Burp, curl...), not just the UI.
function loginLockMinutes(): int {
    global $pdo, $dbAvailable;
    if (!$dbAvailable) return 0;
    try {
        $pdo->prepare("DELETE FROM login_attempts WHERE attempted_at < datetime('now', ?)")
            ->execute(['-' . LOGIN_LOCK_MINUTES . ' minutes']);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip = ?");
        $stmt->execute([clientIp()]);
        if ((int)$stmt->fetchColumn() < LOGIN_MAX_ATTEMPTS) return 0;
        $stmt = $pdo->prepare("SELECT MIN(attempted_at) FROM login_attempts WHERE ip = ?");
        $stmt->execute([clientIp()]);
        $oldest = $stmt->fetchColumn();
        if (!$oldest) return 0;
        $remaining = strtotime($oldest) + LOGIN_LOCK_MINUTES * 60 - time();
        return max(1, (int)ceil($remaining / 60));
    } catch (PDOException $e) {
        return 0;
    }
}

function recordLoginAttempt(): void {
    global $pdo, $dbAvailable;
    if (!$dbAvailable) return;
    try {
        $pdo->prepare("INSERT INTO login_attempts (ip) VALUES (?)")->execute([clientIp()]);
    } catch (PDOException $e) {}
}

function clearLoginAttempts(): void {
    global $pdo, $dbAvailable;
    if (!$dbAvailable) return;
    try {
        $pdo->prepare("DELETE FROM login_attempts WHERE ip = ?")->execute([clientIp()]);
    } catch (PDOException $e) {}
}
