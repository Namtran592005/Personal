<?php
// One-time migrations & seed data.
// Included from config.php only when DB_VERSION is out of date.

require_once __DIR__ . '/schema.php';

global $pdo, $dbAvailable, $settings;
if (defined('MIGRATIONS_RAN')) return;
if (!$dbAvailable || !$pdo) return;
define('MIGRATIONS_RAN', true);

try {
    // --- Schema migrations ---

    // analytics: legacy columns + unique index (all idempotent)
    foreach (['visits'=>'1','pages'=>"''",'first_seen'=>"(datetime('now'))",'last_seen'=>"(datetime('now'))"] as $col => $def) {
        try { $pdo->exec("ALTER TABLE analytics ADD COLUMN $col TEXT NOT NULL DEFAULT $def"); } catch (PDOException $e) {}
    }
    try { $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_analytics_ip ON analytics(ip)"); } catch (PDOException $e) {}
    try { $pdo->exec("DROP INDEX IF EXISTS idx_analytics_created"); } catch (PDOException $e) {}

    // profile: extra social fields
    foreach (['social_facebook','social_instagram','social_threads','social_tiktok'] as $col) {
        try { $pdo->exec("ALTER TABLE profile ADD COLUMN $col TEXT DEFAULT ''"); } catch (PDOException $e) {}
    }

    // messages: feature removed (was contact form + SMTP); drop the table
    try { $pdo->exec("DROP TABLE IF EXISTS messages"); } catch (PDOException $e) {}

    // analytics: time_spent column
    $cols = $pdo->query("PRAGMA table_info(analytics)")->fetchAll(PDO::FETCH_COLUMN, 1);
    if (!in_array('time_spent', $cols, true)) {
        try { $pdo->exec("ALTER TABLE analytics ADD COLUMN time_spent INTEGER DEFAULT 0"); } catch (PDOException $e) {}
    }

    // profile: avatar column
    $pcols = $pdo->query("PRAGMA table_info(profile)")->fetchAll(PDO::FETCH_COLUMN, 1);
    if (!in_array('avatar', $pcols, true)) {
        try { $pdo->exec("ALTER TABLE profile ADD COLUMN avatar TEXT DEFAULT ''"); } catch (PDOException $e) {}
    }

    // users: 2FA (TOTP) columns
    $ucols = $pdo->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_COLUMN, 1);
    if (!in_array('totp_secret', $ucols, true)) {
        try { $pdo->exec("ALTER TABLE users ADD COLUMN totp_secret TEXT DEFAULT ''"); } catch (PDOException $e) {}
    }
    if (!in_array('totp_enabled', $ucols, true)) {
        try { $pdo->exec("ALTER TABLE users ADD COLUMN totp_enabled INTEGER DEFAULT 0"); } catch (PDOException $e) {}
    }

    // --- Seed data ---

    // Legal pages: seed defaults + migrate legacy HTML rows
    $legalDefaults = require __DIR__ . '/legal_defaults.php';
    $pageTitles = ['privacy' => 'Chính Sách Quyền Riêng Tư', 'terms' => 'Điều Khoản Sử Dụng',
        'privacy_en' => 'Privacy Policy', 'terms_en' => 'Terms of Service'];
    foreach ($legalDefaults as $k => $content) {
        $pdo->prepare("INSERT OR IGNORE INTO pages (key, title, content, updated_at) VALUES (?, ?, ?, datetime('now'))")
            ->execute([$k, $pageTitles[$k] ?? $k, $content]);
        $row = $pdo->prepare("SELECT content FROM pages WHERE key = ?");
        $row->execute([$k]);
        $old = $row->fetchColumn();
        if ($old !== false && $old !== $content && strpos($old, '<') !== false) {
            $pdo->prepare("UPDATE pages SET content = ?, updated_at = datetime('now') WHERE key = ?")
                ->execute([$content, $k]);
        }
    }

    // Settings defaults
    foreach (schemaSettingsDefaults() as $k => $v) {
        $pdo->prepare("INSERT OR IGNORE INTO settings (key, value) VALUES (?, ?)")->execute([$k, $v]);
    }

    // NOTE: no admin user is seeded here. The password hash lives only in the
    // users table; the first-run setup page (admin/setup.php) creates or resets
    // the admin account.

    // Profile seed (only when empty) — blank, filled by the admin
    $count = $pdo->query("SELECT COUNT(*) FROM profile")->fetchColumn();
    if ($count == 0) {
        $pdo->prepare("INSERT INTO profile (name, title, email) VALUES (?, ?, ?)")
             ->execute(['', '', '']);
    }

    // Mark schema as up-to-date
    $pdo->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES ('db_version', ?)")->execute([(string)DB_VERSION]);
} catch (PDOException $e) {
    error_log('MIGRATION FAILED: ' . $e->getMessage());
}
