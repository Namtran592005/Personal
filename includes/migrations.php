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
    try { $pdo->exec("ALTER TABLE messages ADD COLUMN is_anonymous INTEGER DEFAULT 0"); } catch (PDOException $e) {}

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

    // --- Seed data ---

    // Pricing plans (only when empty)
    $planCount = $pdo->query("SELECT COUNT(*) FROM pricing_plans")->fetchColumn();
    if ($planCount == 0) {
        $seedPlans = [
            ['Landing Page', '2.500.000đ', 'trọn gói', '', "Trang giới thiệu 1 trang\nThiết kế responsive\nTối ưu tốc độ & SEO cơ bản\nChỉnh sửa miễn phí 2 vòng\nBàn giao trong 5–7 ngày", 'Bắt đầu ngay', 0, 1],
            ['Website Doanh Nghiệp', '5.500.000đ', 'trọn gói', 'Phổ biến nhất', "5–10 trang nội dung\nAdmin quản lý nội dung\nForm liên hệ & bản đồ\nTích hợp thống kê truy cập\nSEO chuẩn chỉnh\nHỗ trợ 1 tháng sau bàn giao", 'Bắt đầu ngay', 1, 2],
            ['Ứng Dụng Tùy Chỉnh', 'Theo yêu cầu', 'báo giá riêng', '', "Web app theo yêu cầu\nThanh toán / đặt lịch / API\nCơ sở dữ liệu & bảo mật\nPhân quyền người dùng\nBảo trì & nâng cấp dài hạn", 'Liên hệ báo giá', 0, 3],
        ];
        $insPlan = $pdo->prepare("INSERT INTO pricing_plans (title, price, price_note, badge, features, button_text, popular, sort_order) VALUES (?,?,?,?,?,?,?,?)");
        foreach ($seedPlans as $p) $insPlan->execute($p);
    }

    // Legal pages: seed defaults + migrate legacy HTML rows
    $legalDefaults = require __DIR__ . '/legal_defaults.php';
    $pageTitles = ['privacy' => 'Chính Sách Quyền Riêng Tư', 'terms' => 'Điều Khoản Sử Dụng'];
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

    // Admin user (only when none)
    $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($count == 0) {
        $adminPw = getAdminPassword();
        $pdo->prepare("INSERT INTO users (password_hash) VALUES (?)")
             ->execute([password_hash($adminPw, PASSWORD_BCRYPT)]);
    }

    // Profile seed (only when empty)
    $count = $pdo->query("SELECT COUNT(*) FROM profile")->fetchColumn();
    if ($count == 0) {
        $pdo->prepare("INSERT INTO profile (name, title, email) VALUES (?, ?, ?)")
             ->execute(['Nam Trần', 'Developer & Designer', 'hello@namtran.dev']);
    }

    // Mark schema as up-to-date
    $pdo->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES ('db_version', ?)")->execute([(string)DB_VERSION]);
} catch (PDOException $e) {
    error_log('MIGRATION FAILED: ' . $e->getMessage());
}
