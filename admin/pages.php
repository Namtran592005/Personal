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

$legalDefaults = require __DIR__ . '/../includes/legal_defaults.php';

$msg = null;
$csrfOk = validateCsrfToken($_POST['_csrf'] ?? null);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_pages'])) {
    if (!$csrfOk) {
        $msg = 'Invalid request.';
    } else {
        $pageTitles = ['privacy' => 'Chính Sách Quyền Riêng Tư', 'terms' => 'Điều Khoản Sử Dụng'];
        foreach (['privacy', 'terms'] as $key) {
            $content = $_POST[$key . '_content'] ?? '';
            $pdo->prepare("INSERT OR REPLACE INTO pages (key, title, content, updated_at) VALUES (?, ?, ?, datetime('now'))")
                ->execute([$key, $pageTitles[$key], $content]);
        }
        $msg = 'Pages saved.';
    }
}

$pages = [];
foreach (['privacy', 'terms'] as $key) {
    $row = null;
    try {
        $st = $pdo->prepare("SELECT * FROM pages WHERE key = ?");
        $st->execute([$key]);
        $row = $st->fetch();
    } catch (PDOException $e) {}
    if (!$row) {
        $row = ['key' => $key, 'content' => $legalDefaults[$key] ?? ''];
    }
    $pages[$key] = $row;
}

$page = 'pages';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>/assets/favicon.png" />
    <title>Pages — Admin</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/icons/phosphor/style.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>/admin-assets/admin.css" />
    <style>
        .page-edit { max-width: 800px; }
        .page-edit textarea {
            min-height: 320px;
            font-family: ui-monospace, SFMono-Regular, Consolas, monospace;
            font-size: 12.5px;
            line-height: 1.6;
            white-space: pre-wrap;
        }
        .help-note { font-size: 12px; color: #86868b; margin: 8px 0 0; }
        .help-note code { background: #f2f2f7; padding: 1px 5px; border-radius: 4px; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-head">
            <div>
                <h1>Pages</h1>
                <div class="sub">Edit the privacy policy and terms of service</div>
            </div>
        </div>

        <?php if ($msg): ?><div class="alert alert-ok"><?= h($msg) ?></div><?php endif; ?>

        <form method="POST">
            <input type="hidden" name="_csrf" value="<?= generateCsrfToken() ?>">
            <div class="form-card page-edit">
                <div class="form-title">Chính Sách Quyền Riêng Tư</div>
                <div class="fg">
                    <textarea name="privacy_content" rows="20"><?= h($pages['privacy']['content'] ?? '') ?></textarea>
                </div>
                <p class="help-note">Plain-text format: dòng bắt đầu bằng <code>##</code> là tiêu đề, <code>###</code> là tiêu đề phụ, <code>-</code> là mục trong danh sách, <code>**đậm**</code> để in đậm, <code>[chữ](https://...)</code> tạo liên kết, dòng trống để xuống đoạn. Placeholders <code>{name}</code> và <code>{email}</code> sẽ tự động thay bằng tên &amp; email trong hồ sơ khi hiển thị.</p>
            </div>

            <div class="form-card page-edit" style="margin-top:20px">
                <div class="form-title">Điều Khoản Sử Dụng</div>
                <div class="fg">
                    <textarea name="terms_content" rows="20"><?= h($pages['terms']['content'] ?? '') ?></textarea>
                </div>
                <p class="help-note">Plain-text format, tương tự trang Chính Sách. Placeholders <code>{name}</code> và <code>{email}</code> sẽ tự động thay bằng tên &amp; email trong hồ sơ khi hiển thị.</p>
            </div>

            <div class="btn-group" style="margin-top:20px">
                <button type="submit" name="save_pages" class="btn btn-pri">Save Pages</button>
            </div>
        </form>
    </main>
</div>
</body>
</html>
