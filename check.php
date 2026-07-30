<?php
require_once __DIR__ . '/includes/functions.php';
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

foreach ($checks as $c) if (!$c['ok']) $allOk = false;
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/Personal/assets/favicon.png" />
    <link rel="stylesheet" href="/Personal/assets/site.css">
    <title>System Check</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f2f2f5; color: #1d1d1f;
            padding: 40px 24px;
        }
        .wrap { max-width: 560px; margin: 0 auto; }
        h1 { font-size: 22px; font-weight: 600; letter-spacing: -0.025em; margin-bottom: 4px; }
        .sub { font-size: 13px; color: #86868b; margin-bottom: 24px; }
        .card { background: #fff; border: 1px solid #e8e8ed; border-radius: 10px; overflow: hidden; }
        .row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 12px 20px; border-bottom: 1px solid #f2f2f5;
            font-size: 13px;
        }
        .row:last-child { border: none; }
        .row .label { font-weight: 500; color: #1d1d1f; }
        .row .val { color: #86868b; font-size: 12px; }
        .badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;
            flex-shrink: 0;
        }
        .badge.ok { background: #e8f5e9; color: #2e7d32; }
        .badge.fail { background: #fbe9e7; color: #c62828; }
        .summary {
            text-align: center; padding: 16px 20px;
            font-size: 14px; font-weight: 600;
        }
        .summary.ok { background: #e8f5e9; color: #2e7d32; }
        .summary.fail { background: #fbe9e7; color: #c62828; }
        a.back { display: block; text-align: center; margin-top: 20px; font-size: 13px; color: #86868b; }
    </style>
</head>
<body>
<div class="wrap">
    <h1>System Check</h1>
    <p class="sub">Verifying environment and configuration</p>
    <div class="card">
        <?php foreach ($checks as $label => $c): ?>
        <div class="row">
            <span class="label"><?= h($label) ?></span>
            <span class="val"><?= h($c['val']) ?></span>
            <span class="badge <?= $c['ok'] ? 'ok' : 'fail' ?>"><?= $c['ok'] ? 'OK' : 'FAIL' ?></span>
        </div>
        <?php endforeach; ?>
        <div class="summary <?= $allOk ? 'ok' : 'fail' ?>">
            <?= $allOk ? 'All checks passed' : 'Some checks failed' ?>
        </div>
    </div>
    <a href="<?= defined('BASE_PATH') ? BASE_PATH . '/admin/login.php' : '/Personal/admin/login.php' ?>" class="back">&larr; Back to Login</a>
</div>
</body>
</html>
