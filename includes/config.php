<?php
require_once __DIR__ . '/schema.php';

function getAdminPassword(): string {
    static $pw = null;
    if ($pw !== null) return $pw;
    $pw = $_ENV['ADMIN_PASSWORD'] ?? $_SERVER['ADMIN_PASSWORD'] ?? getenv('ADMIN_PASSWORD');
    if ($pw) return $pw;
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;
            if (str_starts_with($line, 'ADMIN_PASSWORD=')) {
                $pw = substr($line, strlen('ADMIN_PASSWORD='));
                $pw = trim($pw, '"\'');
                break;
            }
        }
    }
    return $pw ?: '';
}

$sessionParams = session_get_cookie_params();
session_set_cookie_params([
    'lifetime' => $sessionParams['lifetime'],
    'path' => $sessionParams['path'],
    'domain' => $sessionParams['domain'],
    'secure' => $sessionParams['secure'] ?? true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

$dbAvailable = false;
$pdo = null;
$settings = [];

$dbPath = __DIR__ . '/../data/app.sqlite';
$dataDir = dirname($dbPath);
if (!is_dir($dataDir)) @mkdir($dataDir, 0775, true);

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
$basePath = substr($projectDir, strlen(rtrim($docRoot, '/')));
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
