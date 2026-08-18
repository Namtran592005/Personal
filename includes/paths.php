<?php
// Data directory resolution (where the SQLite database lives).
// Caddy/Nginx ignore .htaccess, so a database inside the web root can be
// downloaded directly over HTTP. To keep it reachable only by PHP, move it
// OUTSIDE the web root: copy includes/config-local.php.example to
// includes/config-local.php and set $DATA_DIR_OVERRIDE to an absolute,
// PHP-writable path. If the override is unusable, falls back to data/ so the
// site keeps working.
$DATA_DIR_OVERRIDE = '';
if (is_file(__DIR__ . '/config-local.php')) {
    require_once __DIR__ . '/config-local.php';
}

$defaultDir = __DIR__ . '/../data';
$dir = $DATA_DIR_OVERRIDE !== '' ? rtrim($DATA_DIR_OVERRIDE, '/\\') : $defaultDir;
if (!is_dir($dir)) @mkdir($dir, 0775, true);
$usable = is_dir($dir) && is_writable($dir);

define('DB_DATA_DIR', $usable ? $dir : $defaultDir);
