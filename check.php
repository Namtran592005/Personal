<?php
require_once __DIR__ . '/includes/functions.php';
$docRoot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__);
$base = substr(__DIR__, strlen(rtrim($docRoot, '/')));
define('BASE_PATH', $base === '' || $base === false || $base === '.' ? '' : $base);
$checks = [];
$allOk = true;

// PHP version
$checks['PHP Version'] = ['ok' => version_compare(PHP_VERSION, '8.0', '>='), 'val' => PHP_VERSION];

// Required extensions
$exts = ['pdo', 'pdo_sqlite', 'json', 'mbstring', 'session', 'fileinfo'];
foreach ($exts as $ext) {
    $checks["Extension: $ext"] = ['ok' => extension_loaded($ext), 'val' => extension_loaded($ext) ? 'loaded' : 'missing'];
}

// Database
$dbPath = __DIR__ . '/data/app.sqlite';
$dbOk = file_exists($dbPath);
$dbWritable = $dbOk ? is_writable($dbPath) : is_writable(dirname($dbPath));
$checks['Database file'] = ['ok' => $dbOk, 'val' => $dbOk ? 'exists' : 'not found'];
$checks['Database writable'] = ['ok' => $dbWritable, 'val' => $dbWritable ? 'yes' : 'no'];

// DB connection
try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    $checks['DB connection'] = ['ok' => true, 'val' => 'connected (' . count($tables) . ' tables)'];
} catch (Exception $e) {
    $checks['DB connection'] = ['ok' => false, 'val' => $e->getMessage()];
}

// Writable dirs
$dirs = ['data', 'cache'];
foreach ($dirs as $dir) {
    $path = __DIR__ . "/$dir";
    $exists = is_dir($path);
    $writable = $exists ? is_writable($path) : false;
    $checks["Directory: $dir"] = ['ok' => $exists && $writable, 'val' => $exists ? ($writable ? 'writable' : 'not writable') : 'not found'];
}

// Key files
$files = ['index.php', 'includes/config.php', 'includes/auth.php', 'includes/functions.php', 'admin/login.php', '.env'];
foreach ($files as $f) {
    $exists = file_exists(__DIR__ . "/$f");
    $checks["File: $f"] = ['ok' => $exists, 'val' => $exists ? 'exists' : 'missing'];
}

// .env has SMTP_USER filled?
$envUser = $_ENV['SMTP_USER'] ?? $_SERVER['SMTP_USER'] ?? getenv('SMTP_USER');
$checks['SMTP configured'] = ['ok' => !empty($envUser), 'val' => empty($envUser) ? 'not set' : 'set'];

$total = count($checks);
$okCount = 0;
foreach ($checks as $c) { if ($c['ok']) $okCount++; else $allOk = false; }
$failCount = $total - $okCount;
?><!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>/assets/favicon.png" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/icons/phosphor/style.css" />
    <title>System Check</title>
    <script>
    (function() {
        var saved = localStorage.getItem('darkMode');
        if (saved === 'false') document.documentElement.classList.remove('dark');
        else document.documentElement.classList.add('dark');
    })();
    function toggleDark() {
        var html = document.documentElement;
        html.classList.toggle('dark');
        localStorage.setItem('darkMode', html.classList.contains('dark'));
    }
    </script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f5f5f7; color: #1d1d1f;
            min-height: 100vh; padding: 56px 24px 64px; position: relative;
        }
        html.dark body { background: #1d1d1f; color: #f5f5f7; }

        .grid-bg {
            position: fixed; inset: 0; pointer-events: none;
            background-image:
                repeating-linear-gradient(0deg, rgba(0,0,0,0.035) 0 1px, transparent 1px 48px),
                repeating-linear-gradient(90deg, rgba(0,0,0,0.035) 0 1px, transparent 1px 48px);
        }
        html.dark .grid-bg {
            background-image:
                repeating-linear-gradient(0deg, rgba(245,245,247,0.05) 0 1px, transparent 1px 48px),
                repeating-linear-gradient(90deg, rgba(245,245,247,0.05) 0 1px, transparent 1px 48px);
        }

        .wrap { max-width: 600px; margin: 0 auto; position: relative; animation: fadeUp .6s cubic-bezier(.2,.8,.2,1) both; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }

        .head { display: flex; align-items: center; gap: 16px; margin-bottom: 28px; }
        .logo {
            width: 54px; height: 54px; border-radius: 16px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            background: #1d1d1f; color: #f5f5f7; font-size: 26px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.16);
        }
        html.dark .logo { background: #f5f5f7; color: #1d1d1f; }
        .head h1 { font-size: 24px; font-weight: 700; letter-spacing: -0.03em; }
        .head .sub { font-size: 13px; color: #86868b; margin-top: 2px; }
        html.dark .head .sub { color: #a1a1a6; }

        .summary {
            display: flex; align-items: center; gap: 14px;
            padding: 18px 22px; border-radius: 16px; margin-bottom: 18px;
            background: #fff; border: 1px solid #e8e8ed;
            box-shadow: 0 4px 16px rgba(0,0,0,0.05);
        }
        html.dark .summary { background: #2c2c2e; border-color: #3a3a3c; box-shadow: 0 4px 16px rgba(0,0,0,0.35); }
        .summary .s-icon {
            width: 46px; height: 46px; border-radius: 50%; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: 24px;
        }
        .summary.ok .s-icon { background: #e8f5e9; color: #2e7d32; }
        .summary.fail .s-icon { background: #fbe9e7; color: #c62828; }
        .summary .s-title { font-size: 15px; font-weight: 600; }
        .summary .s-desc { font-size: 12.5px; color: #86868b; margin-top: 2px; }
        html.dark .summary .s-desc { color: #a1a1a6; }

        .card {
            background: #fff; border: 1px solid #e8e8ed; border-radius: 16px;
            overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.05);
        }
        html.dark .card { background: #2c2c2e; border-color: #3a3a3c; box-shadow: 0 4px 16px rgba(0,0,0,0.35); }

        .row {
            display: flex; align-items: center; gap: 14px;
            padding: 13px 20px; border-bottom: 1px solid #f2f2f5;
        }
        html.dark .row { border-color: #3a3a3c; }
        .row:last-child { border: none; }
        .r-icon { width: 26px; height: 26px; border-radius: 50%; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: 14px; }
        .r-icon.ok { background: #e8f5e9; color: #2e7d32; }
        .r-icon.fail { background: #fbe9e7; color: #c62828; }
        .row .label { font-weight: 500; font-size: 13.5px; flex: 1; min-width: 0; }
        .row .val {
            font-size: 12px; color: #86868b; text-align: right;
            max-width: 45%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        html.dark .row .val { color: #a1a1a6; }
        .val-pill {
            flex-shrink: 0; padding: 4px 12px; border-radius: 999px;
            font-size: 11px; font-weight: 700; letter-spacing: 0.05em;
        }
        .val-pill.ok { background: #e8f5e9; color: #2e7d32; }
        .val-pill.fail { background: #fbe9e7; color: #c62828; }

        .actions { display: flex; gap: 12px; justify-content: center; margin-top: 24px; flex-wrap: wrap; }
        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 13px 28px; border-radius: 999px; font-size: 14px; font-weight: 500;
            text-decoration: none; transition: all .2s;
        }
        .btn-pri { background: #1d1d1f; color: #f5f5f7; box-shadow: 0 4px 14px rgba(0,0,0,0.14); }
        .btn-pri:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.2); }
        html.dark .btn-pri { background: #f5f5f7; color: #1d1d1f; }
        html.dark .btn-pri:hover { background: #fff; }
        .btn-out { border: 1.5px solid #d2d2d7; color: #515154; }
        .btn-out:hover { border-color: #1d1d1f; color: #1d1d1f; background: #fff; }
        html.dark .btn-out { color: #a1a1a6; border-color: #48484a; }
        html.dark .btn-out:hover { border-color: #f5f5f7; color: #f5f5f7; background: #2c2c2e; }

        .foot { text-align: center; margin-top: 28px; font-size: 12px; color: #a1a1a6; }

        .dark-toggle {
            position: fixed; top: 22px; right: 22px; z-index: 50;
            width: 44px; height: 44px; border-radius: 50%; border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            background: rgba(0,0,0,0.06); color: #1d1d1f; font-size: 20px;
            transition: all .2s; backdrop-filter: blur(12px);
        }
        .dark-toggle:hover { background: rgba(0,0,0,0.12); transform: scale(1.06); }
        html.dark .dark-toggle { background: rgba(255,255,255,0.08); color: #f5f5f7; }
        html.dark .dark-toggle:hover { background: rgba(255,255,255,0.16); }
        .dark-toggle .ph-sun { display: none; }
        html.dark .dark-toggle .ph-sun { display: block; }
        html.dark .dark-toggle .ph-moon { display: none; }

        @media (max-width: 520px) {
            body { padding: 44px 16px 48px; }
            .row { padding: 12px 16px; }
            .row .val { max-width: 38%; }
        }
    </style>
</head>
<body>
    <div class="grid-bg" aria-hidden="true"></div>
    <button class="dark-toggle" onclick="toggleDark()" aria-label="Toggle dark mode">
        <i class="ph ph-moon"></i><i class="ph ph-sun"></i>
    </button>
    <div class="wrap">
        <div class="head">
            <div class="logo"><i class="ph ph-pulse"></i></div>
            <div>
                <h1>System Check</h1>
                <p class="sub">Kiểm tra môi trường và cấu hình</p>
            </div>
        </div>

        <div class="summary <?= $allOk ? 'ok' : 'fail' ?>">
            <div class="s-icon"><i class="ph <?= $allOk ? 'ph-shield-check' : 'ph-warning-circle' ?>"></i></div>
            <div>
                <div class="s-title"><?= $allOk ? 'Tất cả hệ thống hoạt động tốt' : 'Có vấn đề cần xử lý' ?></div>
                <div class="s-desc"><?= $okCount ?> / <?= $total ?> kiểm tra thành công<?= $failCount > 0 ? " — $failCount thất bại" : '' ?></div>
            </div>
        </div>

        <div class="card">
            <?php foreach ($checks as $label => $c): ?>
            <div class="row">
                <div class="r-icon <?= $c['ok'] ? 'ok' : 'fail' ?>">
                    <i class="ph <?= $c['ok'] ? 'ph-check' : 'ph-x' ?>"></i>
                </div>
                <span class="label"><?= h($label) ?></span>
                <span class="val" title="<?= h($c['val']) ?>"><?= h($c['val']) ?></span>
                <span class="val-pill <?= $c['ok'] ? 'ok' : 'fail' ?>"><?= $c['ok'] ? 'OK' : 'FAIL' ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="actions">
            <a class="btn btn-pri" href="<?= BASE_PATH ?>/admin/login.php"><i class="ph ph-user-circle"></i> Quản trị</a>
            <a class="btn btn-out" href="<?= BASE_PATH ?>/index.php"><i class="ph ph-house-line"></i> Về trang chủ</a>
        </div>
        <p class="foot">Nam Trần — Personal Website</p>
    </div>
</body>
</html>
