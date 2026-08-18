<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_PATH . '/admin/dashboard.php');
    exit;
}

// Must have completed the password step.
if (empty($_SESSION['2fa_user'])) {
    header('Location: ' . BASE_PATH . '/admin/login.php');
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify'])) {
    if (loginRateLimitCheck()) {
        $error = 'Too many verification attempts. Please try again later.';
    } elseif (!validateCsrfToken($_POST['_csrf'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $userId = (int)$_SESSION['2fa_user'];
        $user = getUser();
        $secret = dbDecrypt($user['totp_secret'] ?? '');
        $code = $_POST['code'] ?? '';

        if ($secret === '') {
            $error = '2FA is not configured.';
        } elseif (verifyTotp($secret, $code)) {
            unset($_SESSION['2fa_user']);
            clearLoginAttempts();
            completeLogin($userId);
            header('Location: ' . BASE_PATH . '/admin/dashboard.php');
            exit;
        } else {
            recordLoginAttempt();
            $error = 'Invalid verification code.';
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>/assets/favicon.png" />
    <title>Verify — Admin</title>
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
        <h1>Two-factor check</h1>
        <p class="sub">Enter the 6-digit code from your authenticator app</p>
        <?php if ($error): ?>
        <div class="alert alert-no"><?= h($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="_csrf" value="<?= generateCsrfToken() ?>">
            <div class="fg">
                <label class="fg-label">Verification code</label>
                <input class="fg-input" type="text" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required autofocus placeholder="000000" />
            </div>
            <button type="submit" name="verify" class="btn btn-pri">Verify</button>
        </form>
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
