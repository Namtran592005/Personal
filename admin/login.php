<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_PATH . '/admin/dashboard.php');
    exit;
}

$error = null;
$noUser = false;
if (!$dbAvailable) {
    $error = 'Database connection unavailable.';
} else {
    // True when no admin account exists yet → tell the user to use admin/setup.php.
    try {
        $noUser = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() === 0;
    } catch (PDOException $e) {
        $noUser = false;
    }
}
if (isset($_GET['timeout'])) {
    $error = 'Session expired. Please sign in again.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if (loginRateLimitCheck()) {
        $error = 'Too many login attempts. Please try again later.';
    } elseif (!validateCsrfToken($_POST['_csrf'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $lock = loginLockMinutes();
        if ($lock > 0) {
            $error = "Too many failed attempts. Try again in $lock minute" . ($lock > 1 ? 's' : '') . '.';
        } else {
            $password = $_POST['password'] ?? '';
            if (empty($password)) {
                $error = 'Please enter your password.';
            } else {
                $userId = login($password);
                if ($userId === null) {
                    recordLoginAttempt();
                    $error = 'Invalid password.';
                } elseif (twoFactorEnabled($userId)) {
                    $_SESSION['2fa_user'] = $userId;
                    header('Location: ' . BASE_PATH . '/admin/2fa.php');
                    exit;
                } else {
                    clearLoginAttempts();
                    completeLogin($userId);
                    header('Location: ' . BASE_PATH . '/admin/dashboard.php');
                    exit;
                }
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
    <title>Sign in — Admin</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/icons/phosphor/style.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>/admin-assets/admin.css" />
    <style>html,body{margin:0;padding:0;background:#0a0a0c;scrollbar-width:none;-ms-overflow-style:none}html::-webkit-scrollbar,body::-webkit-scrollbar{display:none}.login-wrap{scrollbar-width:none;-ms-overflow-style:none}.login-wrap::-webkit-scrollbar{display:none}.back-home{display:inline-flex;align-items:center;justify-content:center;gap:6px;width:100%;margin-top:18px;padding:10px;color:rgba(255,255,255,0.55);font-size:13px;font-weight:500;text-decoration:none;border-radius:8px;transition:color 0.2s,background 0.2s}.back-home:hover{color:#f5f5f7;background:rgba(255,255,255,0.06)}</style>
</head>
<body>
    <div class="login-wrap">
    <div class="login-bg">
        <video autoplay loop muted playsinline>
            <source src="<?= BASE_PATH ?>/<?= h($settings['login_video'] ?: 'assets/video/bg.mp4') ?>" type="<?= h(videoType($settings['login_video'] ?: 'assets/video/bg.mp4')) ?>" />
        </video>
    </div>
    <div class="login-card">
        <h1>Welcome back</h1>
        <p class="sub">Enter your password to sign in</p>
        <?php if ($error): ?>
        <div class="alert alert-no"><?= h($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="_csrf" value="<?= generateCsrfToken() ?>">
            <div class="fg">
                <label class="fg-label">Password</label>
                <input class="fg-input" type="password" name="password" required autocomplete="current-password" />
            </div>
            <button type="submit" name="login" class="btn btn-pri">Sign In</button>
        </form>
        <?php if ($noUser): ?>
        <p class="sub" style="margin-top:14px">No admin account yet — create one at
            <a href="<?= BASE_PATH ?>/admin/setup.php" style="color:#4d9fff">admin/setup.php</a>.</p>
        <?php endif; ?>
        <a href="<?= BASE_PATH ?>/index.php" class="back-home">Back to Home</a>
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
