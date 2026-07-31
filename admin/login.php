<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_PATH . '/admin/dashboard.php');
    exit;
}

$error = null;
if (!$dbAvailable) {
    $error = 'Database connection unavailable.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $password = $_POST['password'] ?? '';
    if (empty($password)) {
        $error = 'Please enter your password.';
    } elseif (login($password)) {
        header('Location: ' . BASE_PATH . '/admin/dashboard.php');
        exit;
    } else {
        $error = 'Invalid password.';
        if (!$dbAvailable) $error = 'DB unavailable.';
        else {
            $error = 'Invalid password.';
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
    <style>html,body{margin:0;padding:0;background:#0a0a0c;scrollbar-width:none;-ms-overflow-style:none}html::-webkit-scrollbar,body::-webkit-scrollbar{display:none}.login-wrap{scrollbar-width:none;-ms-overflow-style:none}.login-wrap::-webkit-scrollbar{display:none}</style>
</head>
<body>
    <div class="login-wrap">
    <div class="login-bg">
        <video autoplay loop muted playsinline>
            <source src="<?= BASE_PATH ?>/assets/video/bg.mp4" type="video/mp4" />
        </video>
    </div>
    <div class="login-card">
        <h1>Welcome back</h1>
        <p class="sub">Enter your password to sign in</p>
        <?php if ($error): ?>
        <div class="alert alert-no"><?= h($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="fg">
                <label class="fg-label">Password</label>
                <input class="fg-input" type="password" name="password" required />
            </div>
            <button type="submit" name="login" class="btn btn-pri">Sign In</button>
        </form>
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
