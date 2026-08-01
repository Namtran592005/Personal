<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/functions.php';

$profile = ['name' => 'Nam Trần', 'email' => 'hello@namtran.dev', 'bio' => ''];
if ($dbAvailable) {
    try {
        $stmt = $pdo->query("SELECT * FROM profile WHERE id = 1");
        $db = $stmt->fetch();
        if ($db) $profile = array_merge($profile, $db);
    } catch (PDOException $e) {}
}

$skills = [];
$faqs = [];
$experiences = [];
if ($dbAvailable) {
    try { $skills = $pdo->query("SELECT * FROM skills WHERE visible = 1 ORDER BY sort_order ASC")->fetchAll(); } catch (PDOException $e) {}
    try { $faqs = $pdo->query("SELECT * FROM faqs WHERE visible = 1 ORDER BY sort_order ASC, id DESC")->fetchAll(); } catch (PDOException $e) {}
    try { $experiences = $pdo->query("SELECT * FROM experiences WHERE visible = 1 ORDER BY sort_order ASC, id DESC")->fetchAll(); } catch (PDOException $e) {}
}
$projects = [];

$pageTitle = 'Bản đồ trang web — ' . ($profile['name'] ?? 'Nam Trần');

$navHomeOnly = true;

include 'partials/header.php';
include 'partials/nav.php';
?>
    <section class="section legal-section">
        <div class="container">
            <div class="section-header">
                <p class="label">Sitemap</p>
                <h2>Bản đồ trang web</h2>
            </div>
            <div class="legal">
                <p class="intro">Danh sách đầy đủ các trang và mục có trên website <?= h($profile['name'] ?? 'Nam Trần') ?>. Bấm vào tên mục để truy cập nhanh.</p>

                <h3>Trang chủ</h3>
                <ul class="sitemap-list">
                    <li><a href="<?= BASE_PATH ?>/index.php">Trang chủ</a><span>Giới thiệu bản thân và toàn bộ các mục dưới đây.</span></li>
                </ul>

                <h3>Các mục trên trang chủ</h3>
                <ul class="sitemap-list">
                    <?php if (($settings['show_skills'] ?? '1') === '1' && count($skills) > 0): ?>
                    <li><a href="<?= BASE_PATH ?>/index.php#skills">Kỹ năng</a><span>Các công nghệ và kỹ năng chuyên môn.</span></li>
                    <?php endif; ?>
                    <?php if (($settings['show_experience'] ?? '1') === '1' && count($experiences) > 0): ?>
                    <li><a href="<?= BASE_PATH ?>/index.php#experience">Kinh nghiệm</a><span>Lịch trình làm việc và công ty đã làm việc.</span></li>
                    <?php endif; ?>
                    <?php if (($settings['show_projects'] ?? '1') === '1'): ?>
                    <li><a href="<?= BASE_PATH ?>/index.php#projects">Dự án</a><span>Bộ sưu tập dự án đồng bộ từ GitHub.</span></li>
                    <?php endif; ?>
                    <?php if (($settings['show_pricing'] ?? '1') === '1'): ?>
                    <li><a href="<?= BASE_PATH ?>/index.php#pricing">Bảng giá</a><span>Các gói dịch vụ và mức giá tham khảo.</span></li>
                    <?php endif; ?>
                    <?php if (($settings['show_faq'] ?? '1') === '1' && count($faqs) > 0): ?>
                    <li><a href="<?= BASE_PATH ?>/index.php#faq">Câu hỏi thường gặp</a><span>Giải đáp các thắc mắc phổ biến.</span></li>
                    <?php endif; ?>
                    <?php if (($settings['show_contact'] ?? '1') === '1'): ?>
                    <li><a href="<?= BASE_PATH ?>/index.php#contact">Liên hệ</a><span>Biểu mẫu gửi tin nhắn trực tiếp.</span></li>
                    <?php endif; ?>
                </ul>

                <h3>Pháp lý</h3>
                <ul class="sitemap-list">
                    <li><a href="<?= BASE_PATH ?>/privacy.php">Chính Sách Quyền Riêng Tư</a><span>Cách chúng tôi thu thập và bảo vệ dữ liệu của bạn.</span></li>
                    <li><a href="<?= BASE_PATH ?>/terms.php">Điều Khoản Sử Dụng</a><span>Các điều khoản điều chỉnh việc sử dụng website.</span></li>
                    <li><a href="<?= BASE_PATH ?>/sitemap.php">Bản đồ trang web</a><span>Trang này — danh sách toàn bộ nội dung website.</span></li>
                </ul>

                <h3>Quản trị</h3>
                <ul class="sitemap-list">
                    <li><a href="<?= BASE_PATH ?>/admin/login.php">Đăng nhập quản trị</a><span>Khu vực dành riêng cho quản trị viên website.</span></li>
                </ul>

                <h3>Liên kết ngoài</h3>
                <ul class="sitemap-list">
                    <?php if (!empty($profile['social_github']) && $profile['social_github'] !== '#'): ?>
                    <li><a href="<?= h($profile['social_github']) ?>" rel="noopener" target="_blank">GitHub</a><span>Kho mã nguồn công khai.</span></li>
                    <?php endif; ?>
                    <?php if (!empty($profile['email'])): ?>
                    <li><a href="mailto:<?= h($profile['email']) ?>">Email</a><span>Liên hệ trực tiếp qua email.</span></li>
                    <?php endif; ?>
                </ul>

                <span class="updated">Cập nhật lần cuối: 31/07/2026</span>
            </div>
        </div>
    </section>
<?php include 'partials/footer.php'; ?>
