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
