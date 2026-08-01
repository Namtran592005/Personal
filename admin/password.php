<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$profile = ['name' => 'Admin'];
if ($dbAvailable) {
    try {
        $dbp = $pdo->query("SELECT * FROM profile WHERE id = 1")->fetch();
        if ($dbp) $profile = $dbp;
    } catch (PDOException $e) {}
}

$success = null; $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change'])) {
    if (!validateCsrfToken($_POST['_csrf'] ?? null)) {
        $error = 'Invalid request.';
    } else {
        $current = $_POST['current'] ?? '';
        $new = $_POST['new'] ?? '';
        $confirm = $_POST['confirm'] ?? '';
        if ($dbAvailable) {
            try {
                $stmt = $pdo->query("SELECT id, password_hash FROM users ORDER BY id LIMIT 1");
                $user = $stmt->fetch();
                if (!$user) {
                    $error = 'No admin user found.';
                } elseif (!password_verify($current, $user['password_hash'])) {
                    $error = 'Current password is incorrect.';
                } elseif (strlen($new) < 8) {
                    $error = 'New password must be at least 8 characters.';
                } elseif ($new !== $confirm) {
                    $error = 'New password and confirmation do not match.';
                } else {
                    $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")
                        ->execute([password_hash($new, PASSWORD_BCRYPT), $user['id']]);
                    $success = 'Password changed successfully.';
                }
            } catch (PDOException $e) { $error = 'Update failed.'; }
        } else {
            $error = 'Database unavailable.';
        }
    }
}

$page = 'password';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>/assets/favicon.png" />
    <title>Change Password — Admin</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/icons/phosphor/style.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>/admin-assets/admin.css" />
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-head">
            <div>
                <h1>Change Password</h1>
                <div class="sub">Update your admin login password</div>
            </div>
        </div>

        <?php if ($success): ?><div class="alert alert-ok"><?= h($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-no"><?= h($error) ?></div><?php endif; ?>

        <form method="POST" class="form-card" style="max-width:520px">
            <input type="hidden" name="_csrf" value="<?= generateCsrfToken() ?>">
            <div class="fg">
                <label class="fg-label">Current Password</label>
                <input class="fg-input" type="password" name="current" required autocomplete="current-password" />
            </div>
            <div class="fg">
                <label class="fg-label">New Password</label>
                <input class="fg-input" type="password" name="new" required minlength="8" autocomplete="new-password" />
            </div>
            <div class="fg">
                <label class="fg-label">Confirm New Password</label>
                <input class="fg-input" type="password" name="confirm" required minlength="8" autocomplete="new-password" />
            </div>
            <div class="btn-group" style="margin-top:20px">
                <button type="submit" name="change" class="btn btn-pri">Change Password</button>
            </div>
        </form>
    </main>
</div>
</body>
</html>
