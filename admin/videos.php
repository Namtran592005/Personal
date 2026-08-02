<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$success = null;
$error = null;

$MAX_VIDEO_SIZE = 20 * 1024 * 1024;
$ALLOWED = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'ogg' => 'video/ogg'];

$setSetting = function (string $key, string $value) use (&$pdo, &$settings) {
    try {
        $pdo->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)")->execute([$key, $value]);
        $settings[$key] = $value;
    } catch (PDOException $e) {}
};

$removeOld = function (?string $path) {
    if ($path && strpos($path, 'media/videos/') === 0) {
        $f = __DIR__ . '/../' . $path;
        if (is_file($f)) @unlink($f);
    }
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['_csrf'] ?? null)) {
        $error = 'Invalid request.';
    } else {
        $target = $_POST['target'] ?? '';
        if ($target === 'hero' || $target === 'login') {
            $key = $target === 'hero' ? 'hero_video' : 'login_video';
            $dir = __DIR__ . '/../media/videos';
            if (!is_dir($dir)) @mkdir($dir, 0755, true);

            if (isset($_POST['reset']) && $_POST['reset'] === '1') {
                $removeOld($settings[$key] ?? null);
                $setSetting($key, '');
                $success = 'Video reset to default.';
            } elseif (isset($_FILES['video']) && is_uploaded_file($_FILES['video']['tmp_name'])) {
                $vid = $_FILES['video'];
                $ext = strtolower(pathinfo($vid['name'], PATHINFO_EXTENSION));
                $mime = @finfo_file(finfo_open(FILEINFO_MIME_TYPE), $vid['tmp_name']) ?: '';
                if (!isset($ALLOWED[$ext]) || $mime !== $ALLOWED[$ext]) {
                    $error = 'Invalid video type (MP4, WEBM or OGG only).';
                } elseif ($vid['error'] !== UPLOAD_ERR_OK || $vid['size'] > $MAX_VIDEO_SIZE || $vid['size'] === 0) {
                    $error = 'Upload failed or video larger than 20MB.';
                } else {
                    $name = ($target === 'hero' ? 'hero-' : 'login-') . time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
                    if (move_uploaded_file($vid['tmp_name'], $dir . '/' . $name)) {
                        $removeOld($settings[$key] ?? null);
                        $setSetting($key, 'media/videos/' . $name);
                        $success = 'Video updated.';
                    } else {
                        $error = 'Could not save the uploaded video.';
                    }
                }
            }
        }
    }
}

$heroPath = $settings['hero_video'] ?: 'assets/video/hero.mp4';
$loginPath = $settings['login_video'] ?: 'assets/video/bg.mp4';

$page = 'videos';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>/assets/favicon.png" />
    <title>Videos — Admin</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/icons/phosphor/style.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>/admin-assets/admin.css" />
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-head">
            <div>
                <h1>Background Videos</h1>
                <div class="sub">Customize the hero and admin login background videos</div>
            </div>
        </div>

        <?php if ($success): ?><div class="alert alert-ok"><?= h($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-no"><?= h($error) ?></div><?php endif; ?>

        <div class="form-card" style="max-width:760px">
            <div class="form-title"><i class="ph ph-house-line"></i> Hero Video</div>
            <p style="font-size:13px;color:#86868b;margin-bottom:14px">Shown behind the hero section on the homepage. Default: <code>assets/video/hero.mp4</code></p>
            <video autoplay loop muted playsinline controls style="width:100%;max-height:260px;border-radius:10px;background:#000">
                <source src="<?= BASE_PATH ?>/<?= h($heroPath) ?>" type="<?= h(videoType($heroPath)) ?>" />
            </video>
            <form method="POST" enctype="multipart/form-data" style="margin-top:14px;display:flex;gap:10px;flex-wrap:wrap;align-items:center">
                <input type="hidden" name="_csrf" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="target" value="hero">
                <input type="file" name="video" accept="video/mp4,video/webm,video/ogg" class="fg-input" style="max-width:320px" />
                <button type="submit" class="btn btn-pri"><i class="ph ph-upload-simple"></i> Upload</button>
                <button type="submit" name="reset" value="1" class="btn btn-out" onclick="return confirm('Reset hero video to default?')"><i class="ph ph-arrow-counter-clockwise"></i> Reset</button>
            </form>
            <div class="txt-sm txt-muted" style="margin-top:8px">MP4, WEBM or OGG, max 20MB.</div>
        </div>

        <div class="form-card" style="max-width:760px;margin-top:20px">
            <div class="form-title"><i class="ph ph-lock-key"></i> Login Video</div>
            <p style="font-size:13px;color:#86868b;margin-bottom:14px">Shown behind the admin login and 2FA screens. Default: <code>assets/video/bg.mp4</code></p>
            <video autoplay loop muted playsinline controls style="width:100%;max-height:260px;border-radius:10px;background:#000">
                <source src="<?= BASE_PATH ?>/<?= h($loginPath) ?>" type="<?= h(videoType($loginPath)) ?>" />
            </video>
            <form method="POST" enctype="multipart/form-data" style="margin-top:14px;display:flex;gap:10px;flex-wrap:wrap;align-items:center">
                <input type="hidden" name="_csrf" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="target" value="login">
                <input type="file" name="video" accept="video/mp4,video/webm,video/ogg" class="fg-input" style="max-width:320px" />
                <button type="submit" class="btn btn-pri"><i class="ph ph-upload-simple"></i> Upload</button>
                <button type="submit" name="reset" value="1" class="btn btn-out" onclick="return confirm('Reset login video to default?')"><i class="ph ph-arrow-counter-clockwise"></i> Reset</button>
            </form>
            <div class="txt-sm txt-muted" style="margin-top:8px">MP4, WEBM or OGG, max 20MB.</div>
        </div>
    </main>
</div>
</body>
</html>
