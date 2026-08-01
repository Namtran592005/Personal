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

$msg = null;
$error = null;
$csrfOk = validateCsrfToken($_POST['_csrf'] ?? null);

$user = getUser();
$userId = (int)($_SESSION['user_id'] ?? 0);
$twoFactorOn = $user && (int)($user['totp_enabled'] ?? 0) === 1;

// Pending secret generation shown before enabling.
$pendingSecret = $_SESSION['pending_2fa_secret'] ?? '';
$issuer = $profile['name'] !== 'Admin' ? $profile['name'] : 'Personal Admin';
$account = $profile['email'] !== '' ? $profile['email'] : 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$csrfOk) {
        $error = 'Invalid request.';
    } elseif (isset($_POST['generate_2fa'])) {
        $pendingSecret = generateTotpSecret();
        $_SESSION['pending_2fa_secret'] = $pendingSecret;
        $msg = 'Scan the QR / enter the secret in your authenticator app, then confirm with a code.';
    } elseif (isset($_POST['enable_2fa'])) {
        $pendingSecret = $_SESSION['pending_2fa_secret'] ?? '';
        if ($pendingSecret === '') {
            $error = 'Generate a secret first.';
        } elseif (!verifyTotp($pendingSecret, $_POST['confirm_code'] ?? '')) {
            $error = 'Invalid verification code. Try again.';
        } else {
            setTwoFactorSecret($userId, $pendingSecret, 1);
            unset($_SESSION['pending_2fa_secret']);
            $twoFactorOn = true;
            $msg = 'Two-factor authentication enabled.';
        }
    } elseif (isset($_POST['disable_2fa'])) {
        disableTwoFactor($userId);
        unset($_SESSION['pending_2fa_secret']);
        $twoFactorOn = false;
        $msg = 'Two-factor authentication disabled.';
    }
}

$page = 'security';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>/assets/favicon.png" />
    <title>Security — Admin</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/icons/phosphor/style.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>/admin-assets/admin.css" />
    <style>
        .sec-secret { font-family: ui-monospace, SFMono-Regular, Consolas, monospace; font-size: 15px; letter-spacing: 2px; background:#f2f2f7; padding:10px 14px; border-radius:8px; display:inline-block; margin:6px 0 12px; }
        .sec-uri { word-break: break-all; font-size:12px; color:#86868b; }
        .info-row { display:flex; justify-content:space-between; padding:12px 0; border-bottom:1px solid #f2f2f5; font-size:14px; }
        .info-row:last-child { border:none; }
        .info-row .k { color:#86868b; }
        .info-row .v { font-weight:600; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-head">
            <div>
                <h1>Security</h1>
                <div class="sub">Two-factor authentication & session settings</div>
            </div>
        </div>

        <?php if ($msg): ?><div class="alert alert-ok"><?= h($msg) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-no"><?= h($error) ?></div><?php endif; ?>

        <div class="form-card" style="max-width:640px">
            <div class="form-title">Session</div>
            <div class="info-row"><span class="k">Idle timeout</span><span class="v"><?= SESSION_TIMEOUT_MINUTES ?> minutes</span></div>
            <div class="info-row"><span class="k">Global rate limit</span><span class="v"><?= RATE_LIMIT_MAX ?> req / <?= RATE_LIMIT_WINDOW ?>s per IP</span></div>
            <div class="info-row"><span class="k">Login lockout</span><span class="v"><?= LOGIN_MAX_ATTEMPTS ?> tries / <?= LOGIN_LOCK_MINUTES ?> min</span></div>
            <p style="font-size:13px;color:#86868b;margin-top:14px">Sessions auto-expire after <?= SESSION_TIMEOUT_MINUTES ?> minutes of inactivity and are bound to your IP + browser (fingerprint).</p>
        </div>

        <div class="form-card" style="max-width:640px;margin-top:20px">
            <div class="form-title">Two-Factor Authentication</div>
            <?php if ($twoFactorOn): ?>
            <p style="font-size:14px;margin-bottom:16px"><i class="ph ph-shield-check" style="color:#2e7d32"></i> <strong>Enabled</strong> — a one-time code from your authenticator app is required at sign in.</p>
            <form method="POST" onsubmit="return confirm('Disable two-factor authentication?')">
                <input type="hidden" name="_csrf" value="<?= generateCsrfToken() ?>">
                <button type="submit" name="disable_2fa" class="btn btn-dan">Disable 2FA</button>
            </form>
            <?php else: ?>
            <p style="font-size:14px;margin-bottom:16px">2FA is currently <strong>off</strong>. Enable it to require a time-based one-time password (TOTP) after your password.</p>

            <?php if (!$pendingSecret): ?>
            <form method="POST">
                <input type="hidden" name="_csrf" value="<?= generateCsrfToken() ?>">
                <button type="submit" name="generate_2fa" class="btn btn-pri">Generate Secret</button>
            </form>
            <?php else: ?>
            <p style="font-size:13px;color:#86868b">Add the account to Google Authenticator / Authy / 1Password by scanning this URI, or enter the secret manually. Then enter a code to confirm.</p>
            <div style="margin:14px 0 4px">
                <div class="sec-secret"><?= h($pendingSecret) ?></div>
            </div>
            <p class="sec-uri"><?= h(totpProvisioningUri($pendingSecret, $issuer, $account)) ?></p>
            <form method="POST" style="margin-top:14px">
                <input type="hidden" name="_csrf" value="<?= generateCsrfToken() ?>">
                <div class="fg" style="max-width:220px">
                    <label class="fg-label">Confirm code</label>
                    <input class="fg-input" type="text" name="confirm_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required placeholder="000000" />
                </div>
                <div class="btn-group" style="margin-top:14px">
                    <button type="submit" name="enable_2fa" class="btn btn-pri">Enable 2FA</button>
                    <a href="<?= BASE_PATH ?>/admin/security.php" class="btn btn-out">Cancel</a>
                </div>
            </form>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
