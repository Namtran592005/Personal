<?php
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
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        password_hash TEXT NOT NULL,
        created_at TEXT DEFAULT (datetime('now'))
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS profile (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL DEFAULT '',
        title TEXT NOT NULL DEFAULT '',
        bio TEXT,
        email TEXT NOT NULL DEFAULT '',
        phone TEXT NOT NULL DEFAULT '',
        location TEXT NOT NULL DEFAULT '',
        social_github TEXT DEFAULT '',
        social_linkedin TEXT DEFAULT '',
        social_twitter TEXT DEFAULT '',
        social_dribbble TEXT DEFAULT '',
        social_facebook TEXT DEFAULT '',
        social_instagram TEXT DEFAULT '',
        social_threads TEXT DEFAULT '',
        social_tiktok TEXT DEFAULT '',
        updated_at TEXT DEFAULT (datetime('now'))
    )");

    foreach (['visits'=>'1','pages'=>"''",'first_seen'=>"(datetime('now'))",'last_seen'=>"(datetime('now'))"] as $col => $def) {
        try { $pdo->exec("ALTER TABLE analytics ADD COLUMN $col TEXT NOT NULL DEFAULT $def"); } catch (PDOException $e) {}
    }
    try { $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_analytics_ip ON analytics(ip)"); } catch (PDOException $e) {}
    try { $pdo->exec("DROP INDEX IF EXISTS idx_analytics_created"); } catch (PDOException $e) {}

    foreach (['social_facebook','social_instagram','social_threads','social_tiktok'] as $col) {
        try { $pdo->exec("ALTER TABLE profile ADD COLUMN $col TEXT DEFAULT ''"); } catch (PDOException $e) {}
    }
    try { $pdo->exec("ALTER TABLE messages ADD COLUMN is_anonymous INTEGER DEFAULT 0"); } catch (PDOException $e) {}

    $pdo->exec("CREATE TABLE IF NOT EXISTS skills (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        category TEXT NOT NULL,
        icon TEXT NOT NULL DEFAULT 'ph-code',
        description TEXT,
        tags TEXT,
        sort_order INTEGER DEFAULT 0,
        visible INTEGER DEFAULT 1,
        created_at TEXT DEFAULT (datetime('now'))
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS projects (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        description TEXT,
        tech_stack TEXT,
        github_url TEXT DEFAULT '',
        live_url TEXT DEFAULT '',
        image TEXT DEFAULT '',
        sort_order INTEGER DEFAULT 0,
        visible INTEGER DEFAULT 1,
        created_at TEXT DEFAULT (datetime('now'))
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS experiences (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        company TEXT NOT NULL,
        location TEXT DEFAULT '',
        start_date TEXT,
        end_date TEXT,
        description TEXT,
        sort_order INTEGER DEFAULT 0,
        visible INTEGER DEFAULT 1,
        created_at TEXT DEFAULT (datetime('now'))
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        subject TEXT DEFAULT '',
        message TEXT NOT NULL,
        is_read INTEGER DEFAULT 0,
        created_at TEXT DEFAULT (datetime('now'))
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS faqs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        question TEXT NOT NULL,
        answer TEXT NOT NULL,
        sort_order INTEGER DEFAULT 0,
        visible INTEGER DEFAULT 1,
        created_at TEXT DEFAULT (datetime('now'))
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS pricing_plans (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        price TEXT NOT NULL DEFAULT '',
        price_note TEXT DEFAULT '',
        badge TEXT DEFAULT '',
        features TEXT DEFAULT '',
        button_text TEXT DEFAULT 'Bắt đầu ngay',
        popular INTEGER DEFAULT 0,
        sort_order INTEGER DEFAULT 0,
        visible INTEGER DEFAULT 1,
        created_at TEXT DEFAULT (datetime('now'))
    )");
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

    $pdo->exec("CREATE TABLE IF NOT EXISTS analytics (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ip TEXT UNIQUE NOT NULL DEFAULT '',
        user_agent TEXT DEFAULT '',
        referrer TEXT DEFAULT '',
        screen_w INTEGER DEFAULT 0,
        screen_h INTEGER DEFAULT 0,
        language TEXT DEFAULT '',
        country TEXT DEFAULT '',
        city TEXT DEFAULT '',
        visits INTEGER DEFAULT 1,
        pages TEXT DEFAULT '',
        first_seen TEXT DEFAULT (datetime('now')),
        last_seen TEXT DEFAULT (datetime('now'))
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        key TEXT PRIMARY KEY NOT NULL,
        value TEXT NOT NULL DEFAULT ''
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ip TEXT NOT NULL,
        attempted_at TEXT DEFAULT (datetime('now'))
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS pages (
        key TEXT PRIMARY KEY NOT NULL,
        title TEXT NOT NULL DEFAULT '',
        content TEXT NOT NULL DEFAULT '',
        updated_at TEXT DEFAULT (datetime('now'))
    )");
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

    // Auto-seed
    $defaults = [
        'show_skills' => '1', 'show_projects' => '1', 'show_pricing' => '1', 'show_faq' => '1',
        'show_contact' => '1', 'enable_analytics' => '1', 'enable_contact_form' => '1', 'github_username' => 'namtran592005',
        'show_back_top' => '1', 'show_call_fab' => '1',
    ];
    foreach ($defaults as $k => $v) {
        $pdo->prepare("INSERT OR IGNORE INTO settings (key, value) VALUES (?, ?)")->execute([$k, $v]);
    }

    $settings = [];
    $stmt = $pdo->query("SELECT key, value FROM settings");
    while ($row = $stmt->fetch()) $settings[$row['key']] = $row['value'];

    $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($count == 0) {
        $adminPw = getAdminPassword();
        $pdo->prepare("INSERT INTO users (password_hash) VALUES (?)")
             ->execute([password_hash($adminPw, PASSWORD_BCRYPT)]);
    }
    $count = $pdo->query("SELECT COUNT(*) FROM profile")->fetchColumn();
    if ($count == 0) {
        $pdo->prepare("INSERT INTO profile (name, title, email) VALUES (?, ?, ?)")
             ->execute(['Nam Trần', 'Developer & Designer', 'hello@namtran.dev']);
    }
}
