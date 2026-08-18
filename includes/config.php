<?php
require_once __DIR__ . '/schema.php';
require_once __DIR__ . '/paths.php';

$sessionParams = session_get_cookie_params();
session_set_cookie_params([
    'lifetime' => $sessionParams['lifetime'],
    'path' => $sessionParams['path'],
    'domain' => $sessionParams['domain'],
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

$dbAvailable = false;
$pdo = null;
$settings = [];

$dbPath = DB_DATA_DIR . '/app.sqlite';
$dataDir = DB_DATA_DIR;
if (!is_dir($dataDir)) @mkdir($dataDir, 0775, true);

// If the DB moved outside the web root, carry over an existing database on first run.
if (!file_exists($dbPath)) {
    $legacy = __DIR__ . '/../data/app.sqlite';
    if (file_exists($legacy)) {
        @copy($legacy, $dbPath);
        if (file_exists($legacy . '-wal')) @copy($legacy . '-wal', $dbPath . '-wal');
    }
}

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec("PRAGMA journal_mode=WAL");
    $pdo->exec("PRAGMA foreign_keys=ON");
    $dbAvailable = true;
} catch (PDOException $e) {
    $dbAvailable = false;
}

$projectDir = dirname(__DIR__);
$docRoot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(dirname(__DIR__));
$basePath = substr($projectDir, strlen(rtrim($docRoot, '/\\')));
// Normalise Windows backslashes so BASE_PATH is always URL-ready (/subdir).
$basePath = str_replace('\\', '/', $basePath);
define('BASE_PATH', $basePath === '' || $basePath === false || $basePath === '.' ? '' : $basePath);

if ($dbAvailable) {
    foreach (schemaTables() as $ddl) {
        try { $pdo->exec($ddl); } catch (PDOException $e) {}
    }

    // Run one-time migrations/seeds only when schema is out of date
    $dbVersion = 0;
    try {
        $dbVersion = (int)$pdo->query("SELECT value FROM settings WHERE key = 'db_version'")->fetchColumn();
    } catch (PDOException $e) {}
    if ($dbVersion < DB_VERSION) {
        require __DIR__ . '/migrations.php';
    }

    $settings = [];
    $stmt = $pdo->query("SELECT key, value FROM settings");
    while ($row = $stmt->fetch()) $settings[$row['key']] = $row['value'];
}

require_once __DIR__ . '/lang.php';

// Global rate limiting (runs on every request, incl. track.php beacon).
// Exempt the admin if already logged in to avoid locking out the owner,
// and skip entirely in CLI (tools/lint.php, tools/smoke.php).
require_once __DIR__ . '/rate-limit.php';
if (PHP_SAPI !== 'cli' && !isset($_SESSION['user_id']) && rateLimitCheck()) {
    http_response_code(429);
    header('Retry-After: ' . RATE_LIMIT_WINDOW);
    exit('Too Many Requests');
}
