<?php
// tools/smoke.php — smoke test that verifies the app boots and core pieces work.
// Usage:
//   php tools/smoke.php                 # offline checks only
//   php tools/smoke.php --url http://localhost:8080   # also hits live pages
// Exits with code 1 on any failure.

$root = dirname(__DIR__);
$failures = [];
$passes = [];

function check(string $label, bool $ok, string $detail = ''): void {
    global $passes, $failures;
    if ($ok) $passes[] = $label;
    else $failures[] = $label . ($detail ? " — $detail" : '');
}

// --- PHP environment ---
check('PHP >= 8.0', version_compare(PHP_VERSION, '8.0', '>='), PHP_VERSION);
foreach (['pdo', 'pdo_sqlite', 'json', 'mbstring', 'session', 'fileinfo'] as $ext) {
    check("Extension: $ext", extension_loaded($ext));
}

// --- Bootstrap the app (creates DB + runs migrations if needed) ---
require_once $root . '/includes/schema.php';
require_once $root . '/includes/config.php';
require_once $root . '/includes/functions.php';
require_once $root . '/includes/auth.php';

check('DB available', $dbAvailable);

if ($dbAvailable) {
    // Expected tables exist
    $have = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    foreach (array_keys(schemaTables()) as $t) {
        check("Table: $t", in_array($t, $have, true));
    }

    // Settings seeded
    check('Settings loaded', count($settings) > 0);
    check('db_version set', (int)($settings['db_version'] ?? 0) >= DB_VERSION, $settings['db_version'] ?? 'missing');

    // Legal pages seeded
    $pages = $pdo->query("SELECT key FROM pages")->fetchAll(PDO::FETCH_COLUMN);
    check('Legal pages seeded', in_array('privacy', $pages, true) && in_array('terms', $pages, true));

    // profile.avatar column (DB_VERSION >= 3)
    $pcols = $pdo->query("PRAGMA table_info(profile)")->fetchAll(PDO::FETCH_COLUMN, 1);
    check('profile.avatar column', in_array('avatar', $pcols, true));

    // 2FA columns on users (DB_VERSION >= 4)
    $ucols = $pdo->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_COLUMN, 1);
    check('users.totp_secret column', in_array('totp_secret', $ucols, true));
    check('users.totp_enabled column', in_array('totp_enabled', $ucols, true));

    // rate_limits table (DB_VERSION >= 4)
    $have2 = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='rate_limits'")->fetchColumn();
    check('rate_limits table', (bool)$have2);

    // default_lang setting seeded
    check('default_lang setting', isset($settings['default_lang']), $settings['default_lang'] ?? 'missing');

    // Core helpers exist
    check('Helper: renderLegalText', function_exists('renderLegalText'));
    check('Helper: formatDuration', function_exists('formatDuration'));
    check('Helper: h()', function_exists('h'));

    // Auth helpers
    check('Auth: login()', function_exists('login'));
    check('Auth: loginLockMinutes()', function_exists('loginLockMinutes'));
    check('Auth: completeLogin()', function_exists('completeLogin'));

    // Security helpers
    check('Rate limit: rateLimitCheck()', function_exists('rateLimitCheck'));
    check('Rate limit: loginRateLimitCheck()', function_exists('loginRateLimitCheck'));
    check('TOTP: verifyTotp()', function_exists('verifyTotp'));
    check('TOTP: generateTotpSecret()', function_exists('generateTotpSecret'));
    check('Lang: t()', function_exists('t'));
    check('Lang: currentLang()', function_exists('currentLang'));
}

// --- Optional live HTTP smoke ---
$url = null;
for ($i = 1; $i < $argc; $i++) {
    if ($argv[$i] === '--url' && isset($argv[$i + 1])) $url = rtrim($argv[$i + 1], '/');
}
if ($url) {
    $pages = ['/', '/privacy.php', '/terms.php', '/sitemap.php', '/check.php', '/admin/login.php'];
    foreach ($pages as $p) {
        $code = @get_headers($url . $p)[0] ?? '';
        $ok = strpos($code, '200') !== false;
        check("HTTP $p", $ok, $code);
    }
} else {
    $passes[] = 'HTTP smoke skipped (pass --url http://host to enable)';
}

// --- Report ---
echo "Smoke test: " . count($passes) . " ok, " . count($failures) . " failed\n";
foreach ($passes as $p) echo "  [OK]   $p\n";
foreach ($failures as $f) echo "  [FAIL] $f\n";

exit($failures ? 1 : 0);
