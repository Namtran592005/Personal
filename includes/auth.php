<?php
require_once __DIR__ . '/config.php';

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_PATH . '/admin/login.php');
        exit;
    }
}

function login(string $password): bool {
    global $pdo, $dbAvailable;
    if (!$dbAvailable) return false;
    $adminPassword = getAdminPassword();
    try {
        $stmt = $pdo->query("SELECT id, password_hash FROM users ORDER BY id LIMIT 1");
        $user = $stmt->fetch();
        if (!$user) return false;
        if (password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            return true;
        }
        if ($password === $adminPassword) {
            $hash = password_hash($adminPassword, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$hash, $user['id']]);
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            return true;
        }
        return false;
    } catch (PDOException $e) {
        return false;
    }
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
    session_destroy();
    header('Location: ' . BASE_PATH . '/admin/login.php');
    exit;
}

define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCK_MINUTES', 15);

function clientIp(): string {
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
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
