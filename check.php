<?php
require_once __DIR__ . '/includes/schema.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/dbkey.php';
require_once __DIR__ . '/includes/paths.php';
$docRoot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__);
$base = substr(__DIR__, strlen(rtrim($docRoot, '/\\')));
$base = str_replace('\\', '/', $base);
define('BASE_PATH', $base === '' || $base === false || $base === '.' ? '' : $base);

// Lightweight i18n (standalone diagnostic page — works even when the DB is down).
$lang = $_GET['lang'] ?? ($_COOKIE['lang'] ?? 'vi');
$lang = $lang === 'en' ? 'en' : 'vi';

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
$dbPath = DB_DATA_DIR . '/app.sqlite';
$dbOk = file_exists($dbPath);
$dbWritable = $dbOk ? is_writable($dbPath) : is_writable(dirname($dbPath));
$checks['Database file'] = ['ok' => $dbOk, 'val' => $dbOk ? 'exists' : 'not found'];
$checks['Database writable'] = ['ok' => $dbWritable, 'val' => $dbWritable ? 'yes' : 'no'];

// DB must not be directly downloadable over HTTP (Caddy/Nginx ignore .htaccess).
$docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
$dbNorm = str_replace('\\', '/', DB_DATA_DIR);
$dbInWebRoot = $docRoot !== '' && str_starts_with($dbNorm, $docRoot . '/');
$checks['DB outside web root'] = [
    'ok' => !$dbInWebRoot,
    'val' => $dbInWebRoot
        ? 'downloadable via URL — copy includes/config-local.php.example to config-local.php and set DB_DATA_DIR outside the web root'
        : 'not reachable via HTTP',
];

// DB connection
try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    $checks['DB connection'] = ['ok' => true, 'val' => 'connected (' . count($tables) . ' tables)'];
} catch (Exception $e) {
    $checks['DB connection'] = ['ok' => false, 'val' => $e->getMessage()];
}

// DB key (encryption)
$checks['DB key file'] = ['ok' => dbKeyExists(), 'val' => dbKeyExists() ? 'exists' : 'missing — run admin/setup.php'];
if (dbKeyExists()) {
    $verifier = dbKeyVerifier(dbKey());
    $stored = null;
    if (isset($pdo) && $pdo) {
        try { $stored = $pdo->query("SELECT value FROM settings WHERE key = 'db_key_verifier'")->fetchColumn(); } catch (Exception $e) {}
    }
    $match = $stored !== null && $stored === $verifier;
    $checks['DB key verifier'] = ['ok' => $match, 'val' => $stored !== null ? ($match ? 'matches DB' : 'mismatch') : 'not stored yet'];
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
$files = ['index.php', 'includes/config.php', 'includes/auth.php', 'includes/functions.php', 'includes/schema.php', 'includes/migrations.php', 'includes/lang.php', 'includes/totp.php', 'admin/login.php', 'admin/security.php', 'partials/header.php'];
foreach ($files as $f) {
    $exists = file_exists(__DIR__ . "/$f");
    $checks["File: $f"] = ['ok' => $exists, 'val' => $exists ? 'exists' : 'missing'];
}

// DB version: code (DB_VERSION from schema.php) vs server (settings.db_version)
$dbVersionServer = null;
if (isset($pdo) && $pdo) {
    try { $dbVersionServer = (int)$pdo->query("SELECT value FROM settings WHERE key = 'db_version'")->fetchColumn(); } catch (Exception $e) {}
}
$dbVersionOk = $dbVersionServer !== null && $dbVersionServer >= DB_VERSION;
$checks['DB version (server vs code)'] = [
    'ok' => $dbVersionOk,
    'val' => $dbVersionServer !== null ? "server $dbVersionServer / code " . DB_VERSION : 'DB not initialised yet',
];

// --- Deploy / permission helper (Linux + SFTP) ---
// Detects the PHP process owner/group, inspects each critical path, and
// produces the chown/chmod commands to run over SSH after an SFTP upload.
$phpUser = 'www-data';
$webGroup = 'www-data';
if (function_exists('posix_geteuid')) {
    $u = @posix_getpwuid(posix_geteuid());
    if (is_array($u) && !empty($u['name'])) $phpUser = $u['name'];
}
if (function_exists('posix_getegid')) {
    $g = @posix_getgrgid(posix_getegid());
    if (is_array($g) && !empty($g['name'])) $webGroup = $g['name'];
}

function checkPermInfo(string $path): ?array {
    if (!file_exists($path)) return null;
    $owner = function_exists('posix_getpwuid') ? (@posix_getpwuid(fileowner($path))['name'] ?? (string)fileowner($path)) : (string)fileowner($path);
    $group = function_exists('posix_getgrgid') ? (@posix_getgrgid(filegroup($path))['name'] ?? (string)filegroup($path)) : (string)filegroup($path);
    return [
        'mode' => decoct(fileperms($path) & 0777),
        'owner' => $owner,
        'group' => $group,
        'writable' => is_writable($path),
        'isDir' => is_dir($path),
    ];
}

$permTargets = [
    'data'             => ['mode' => '775', 'hint' => 'SQLite DB folder'],
    'cache'            => ['mode' => '775', 'hint' => 'GitHub cache folder'],
    'media'            => ['mode' => '775', 'hint' => 'Avatar uploads'],
    'data/app.sqlite'  => ['mode' => '664', 'hint' => 'Database file'],
];

$permRows = [];
$permAllOk = true;
$fixCmds = [];
foreach ($permTargets as $rel => $t) {
    $path = __DIR__ . '/' . $rel;
    $info = checkPermInfo($path);
    if ($info === null) {
        $permRows[$rel] = [
            'ok' => false,
            'detail' => 'missing',
            'cmd' => null,
            'hint' => $t['hint'],
            'missing' => true,
        ];
        $permAllOk = false;
        continue;
    }
    $modeOk = $info['mode'] === $t['mode'];
    $ownerOk = $info['owner'] === $phpUser && $info['group'] === $webGroup;
    $ok = $modeOk && $ownerOk;
    if (!$ok) $permAllOk = false;
    $parts = [];
    if (!$ownerOk) $parts[] = "sudo chown -R $phpUser:$webGroup $rel";
    if (!$modeOk) $parts[] = "sudo chmod {$t['mode']} $rel";
    $permRows[$rel] = [
        'ok' => $ok,
        'detail' => $info['mode'] . "  {$info['owner']}:{$info['group']}",
        'cmd' => implode(' && ', $parts),
        'hint' => $t['hint'],
    ];
    if (!$ok && $parts) $fixCmds[] = implode(' && ', $parts);
}
$fixCmds = array_values(array_unique($fixCmds));
$permCmdBlock = count($fixCmds) > 0
    ? "cd " . __DIR__ . "\n" . implode("\n", $fixCmds)
    : '';

$total = count($checks);
$okCount = 0;
foreach ($checks as $c) { if ($c['ok']) $okCount++; else $allOk = false; }
$permFailCount = count(array_filter($permRows, fn($r) => !$r['ok']));
if ($permFailCount > 0) $allOk = false;
$total += count($permRows);
$okCount += count($permRows) - $permFailCount;
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
        .val-pill.warn { background: #fff8e1; color: #b26a00; }

        .perm-row { display: flex; align-items: center; gap: 14px; padding: 13px 20px; border-bottom: 1px solid #f2f2f5; }
        html.dark .perm-row { border-color: #3a3a3c; }
        .perm-row:last-child { border: none; }
        .perm-row .p-path { font-family: ui-monospace, SFMono-Regular, Consolas, monospace; font-size: 12.5px; font-weight: 500; flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .perm-row .p-hint { font-size: 11px; color: #86868b; margin-left: 8px; flex-shrink: 0; }
        .perm-row .p-mode { font-family: ui-monospace, SFMono-Regular, Consolas, monospace; font-size: 12px; color: #515154; flex-shrink: 0; }
        html.dark .perm-row .p-mode { color: #a1a1a6; }

        .cmd-box-wrap { padding: 14px 20px 18px; }
        .cmd-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
        .cmd-head .t { font-size: 12.5px; font-weight: 600; color: #1d1d1f; }
        html.dark .cmd-head .t { color: #f5f5f7; }
        .cmd-head .sub { font-size: 11.5px; color: #86868b; margin-top: 1px; }
        .cmd-box {
            font-family: ui-monospace, SFMono-Regular, Consolas, monospace;
            font-size: 12px; line-height: 1.7;
            background: #f5f5f7; color: #1d1d1f;
            border: 1px solid #e8e8ed; border-radius: 10px;
            padding: 12px 14px; overflow-x: auto; white-space: pre;
        }
        html.dark .cmd-box { background: #1d1d1f; color: #f5f5f7; border-color: #3a3a3c; }
        .copy-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 14px; border-radius: 999px; border: 1.5px solid #d2d2d7;
            background: none; color: #515154; font-size: 12px; font-weight: 600;
            cursor: pointer; transition: all .2s;
        }
        .copy-btn:hover { border-color: #1d1d1f; color: #1d1d1f; }
        html.dark .copy-btn { color: #a1a1a6; border-color: #48484a; }
        html.dark .copy-btn:hover { border-color: #f5f5f7; color: #f5f5f7; }
        .copy-btn.copied { border-color: #2e7d32; color: #2e7d32; }
        html.dark .copy-btn.copied { border-color: #4caf50; color: #4caf50; }

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
                <p class="sub"><?= $lang === 'en' ? 'Environment & configuration check' : 'Kiểm tra môi trường và cấu hình' ?></p>
            </div>
        </div>

        <div class="summary <?= $allOk ? 'ok' : 'fail' ?>">
            <div class="s-icon"><i class="ph <?= $allOk ? 'ph-shield-check' : 'ph-warning-circle' ?>"></i></div>
            <div>
                <div class="s-title"><?= $allOk ? ($lang === 'en' ? 'All systems operational' : 'Tất cả hệ thống hoạt động tốt') : ($lang === 'en' ? 'Issues need attention' : 'Có vấn đề cần xử lý') ?></div>
                <div class="s-desc"><?= $okCount ?> / <?= $total ?> <?= $lang === 'en' ? 'checks passed' : 'kiểm tra thành công' ?><?= $failCount > 0 ? " — $failCount " . ($lang === 'en' ? 'failed' : 'thất bại') : '' ?></div>
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

        <div class="card" style="margin-top:18px">
            <div class="row" style="border-bottom:1px solid #f2f2f5">
                <div class="r-icon <?= $permAllOk ? 'ok' : 'fail' ?>">
                    <i class="ph <?= $permAllOk ? 'ph-lock-key' : 'ph-lock-key' ?>"></i>
                </div>
                <span class="label"><?= $lang === 'en' ? 'File permissions (SFTP deploy)' : 'Quyền tập tin (deploy qua SFTP)' ?></span>
                <span class="val" title="PHP user: <?= h($phpUser) ?> / group: <?= h($webGroup) ?>">php: <?= h($phpUser) ?>:<?= h($webGroup) ?></span>
                <span class="val-pill <?= $permAllOk ? 'ok' : 'fail' ?>"><?= $permAllOk ? 'OK' : 'FIX' ?></span>
            </div>
            <?php foreach ($permRows as $rel => $pr): ?>
            <div class="perm-row">
                <div class="r-icon <?= $pr['ok'] ? 'ok' : 'fail' ?>">
                    <i class="ph <?= $pr['ok'] ? 'ph-check' : 'ph-x' ?>"></i>
                </div>
                <span class="p-path"><?= h($rel) ?><span class="p-hint"><?= h($pr['hint']) ?></span></span>
                <span class="p-mode"><?= h($pr['detail']) ?></span>
                <span class="val-pill <?= $pr['ok'] ? 'ok' : 'warn' ?>"><?= $pr['ok'] ? 'OK' : 'FIX' ?></span>
            </div>
            <?php endforeach; ?>
            <div class="cmd-box-wrap">
                <div class="cmd-head">
                    <div>
                        <div class="t"><?= $lang === 'en' ? 'Run over SSH after SFTP upload' : 'Chạy qua SSH sau khi kéo file bằng SFTP' ?></div>
                        <div class="sub"><?= $lang === 'en'
                            ? 'Paste into the server terminal, then refresh this page.'
                            : 'Dán vào terminal trên server, sau đó tải lại trang này.' ?></div>
                    </div>
                    <button class="copy-btn" onclick="copyCmd()" id="copyBtn"><i class="ph ph-copy-simple"></i> Copy</button>
                </div>
                <?php if ($permCmdBlock !== ''): ?>
                <div class="cmd-box" id="cmdBox"><?= h($permCmdBlock) ?></div>
                <?php else: ?>
                <div class="cmd-box" id="cmdBox" style="color:#2e7d32"><?= $lang === 'en' ? '# Permissions are correct — nothing to run.' : '# Quyền đã đúng — không cần chạy gì.' ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="actions">
            <a class="btn btn-pri" href="<?= BASE_PATH ?>/admin/login.php"><i class="ph ph-user-circle"></i> <?= $lang === 'en' ? 'Admin' : 'Quản trị' ?></a>
            <a class="btn btn-out" href="<?= BASE_PATH ?>/index.php"><i class="ph ph-house-line"></i> <?= $lang === 'en' ? 'Back to Home' : 'Về trang chủ' ?></a>
        </div>
        <p class="foot">Personal Website</p>
    </div>
    <script>
    function copyCmd() {
        var box = document.getElementById('cmdBox');
        var btn = document.getElementById('copyBtn');
        if (!box || !btn) return;
        var text = box.textContent;
        function done() {
            var label = btn.innerHTML;
            btn.classList.add('copied');
            btn.innerHTML = '<i class="ph ph-check"></i> Copied';
            setTimeout(function() {
                btn.classList.remove('copied');
                btn.innerHTML = label;
            }, 1800);
        }
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(done, function() { fallbackCopy(text, done); });
        } else {
            fallbackCopy(text, done);
        }
    }
    function fallbackCopy(text, done) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); } catch (e) {}
        document.body.removeChild(ta);
        done();
    }
    </script>
</body>
</html>
