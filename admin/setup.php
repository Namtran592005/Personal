<?php
// One-time setup page: create or reset the admin password via the UI.
// Intended for first deployment: open this file, enter a password, then
// DELETE admin/setup.php from the server. It can overwrite the existing
// hash, so leaving it in place would let anyone take over the admin account.
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/crypto.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_PATH . '/admin/dashboard.php');
    exit;
}

$error = null;
$success = false;
if (!$dbAvailable) {
    $error = 'Database connection unavailable.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup'])) {
    if (loginRateLimitCheck()) {
        $error = 'Too many attempts. Please try again later.';
    } elseif (!validateCsrfToken($_POST['_csrf'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';
        $dbKeyInput = $_POST['db_key'] ?? '';
        $dbKeyConfirm = $_POST['db_key_confirm'] ?? '';
        if (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } elseif ($dbKeyInput !== '' && strlen($dbKeyInput) < 8) {
            $error = 'Database key must be at least 8 characters.';
        } elseif ($dbKeyInput !== $dbKeyConfirm) {
            $error = 'Database keys do not match.';
        } else {
            try {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $count = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
                if ($count === 0) {
                    $pdo->prepare("INSERT INTO users (password_hash) VALUES (?)")->execute([$hash]);
                } else {
                    $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = (SELECT id FROM users ORDER BY id LIMIT 1)")
                        ->execute([$hash]);
                }
                if ($dbKeyInput !== '') {
                    $newKey = 'p' . hash('sha256', $dbKeyInput);
                } else {
                    $newKey = 'x' . bin2hex(random_bytes(32));
                }
                saveDbKey($newKey);
                $pdo->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES ('db_key_verifier', ?)")
                    ->execute([dbKeyVerifier($newKey)]);
                $success = true;
            } catch (PDOException $e) {
                $error = 'Save failed.';
            }
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>/assets/favicon.png" />
    <title>Admin Setup</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/icons/phosphor/style.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>/admin-assets/admin.css" />
    <style>html,body{margin:0;padding:0;background:#0a0a0c;scrollbar-width:none;-ms-overflow-style:none}html::-webkit-scrollbar,body::-webkit-scrollbar{display:none}.login-wrap{scrollbar-width:none;-ms-overflow-style:none}.login-wrap::-webkit-scrollbar{display:none}.fg-hint{color:rgba(255,255,255,0.4);font-size:12px;line-height:1.5;margin-top:5px}.back-home{display:inline-flex;align-items:center;justify-content:center;gap:6px;width:100%;margin-top:18px;padding:10px;color:rgba(255,255,255,0.55);font-size:13px;font-weight:500;text-decoration:none;border-radius:8px;transition:color 0.2s,background 0.2s}.back-home:hover{color:#f5f5f7;background:rgba(255,255,255,0.06)}</style>
</head>
<body>
    <div class="login-wrap">
    <div class="login-bg">
        <video autoplay loop muted playsinline>
            <source src="<?= BASE_PATH ?>/<?= h($settings['login_video'] ?: 'assets/video/bg.mp4') ?>" type="<?= h(videoType($settings['login_video'] ?: 'assets/video/bg.mp4')) ?>" />
        </video>
    </div>
    <div class="login-card">
        <h1>Admin setup</h1>
        <p class="sub">Create or reset the admin password and the database key. After saving, <strong>delete admin/setup.php</strong> from the server.</p>
        <?php if ($error): ?>
        <div class="alert alert-no"><?= h($error) ?></div>
        <?php elseif ($success): ?>
        <div class="alert alert-ok">Setup saved. You can now <a href="<?= BASE_PATH ?>/admin/login.php" style="color:#4d9fff">sign in</a>. Remember to delete admin/setup.php.</div>
        <?php else: ?>
        <form method="POST">
            <input type="hidden" name="_csrf" value="<?= generateCsrfToken() ?>">
            <div class="fg">
                <label class="fg-label">Password</label>
                <input class="fg-input" type="password" name="password" required minlength="8" autocomplete="new-password" />
            </div>
            <div class="fg">
                <label class="fg-label">Confirm Password</label>
                <input class="fg-input" type="password" name="confirm" required minlength="8" autocomplete="new-password" />
            </div>
            <div class="fg">
                <label class="fg-label">Database key</label>
                <input class="fg-input" type="password" name="db_key" minlength="8" autocomplete="new-password" placeholder="Leave blank to auto-generate" />
                <div class="fg-hint">Used by the site to encrypt sensitive data (e.g. 2FA secret). Stored in includes/db-key.php, never in the database itself. Keep a copy — losing it makes encrypted data unreadable.</div>
            </div>
            <div class="fg">
                <label class="fg-label">Confirm Database key</label>
                <input class="fg-input" type="password" name="db_key_confirm" minlength="8" autocomplete="new-password" />
            </div>
            <button type="submit" name="setup" class="btn btn-pri">Save Setup</button>
        </form>
        <?php endif; ?>
        <a href="<?= BASE_PATH ?>/admin/login.php" class="back-home"><i class="ph ph-arrow-left"></i> Back to sign in</a>
    </div>
</div>
<script>
document.addEventListener('focusin', function(e) {
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
        setTimeout(function() { e.target.scrollIntoView({ block: 'center', behavior: 'smooth' }); }, 350);
    }
});
</script>
</body>
</html>