<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/functions.php';

$profile = ['name' => '', 'email' => '', 'bio' => ''];
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

$pageTitle = t('footer_sitemap') . ' — ' . ($profile['name'] ?? '');

$navHomeOnly = true;

include 'partials/header.php';
include 'partials/nav.php';
?>
    <section class="section legal-section">
        <div class="container">
            <div class="section-header">
                <p class="label">Sitemap</p>
                <h2><?= t('footer_sitemap') ?></h2>
            </div>
            <div class="legal">
                <p class="intro"><?= $LANG === 'en'
                    ? 'A complete list of pages and sections on ' . h($profile['name'] ?? 'this site') . '. Click a name to jump straight to it.'
                    : 'Danh sách đầy đủ các trang và mục có trên website ' . h($profile['name'] ?? '') . '. Bấm vào tên mục để truy cập nhanh.' ?></p>

                <h3><?= $LANG === 'en' ? 'Home' : 'Trang chủ' ?></h3>
                <ul class="sitemap-list">
                    <li><a href="<?= BASE_PATH ?>/index.php"><?= t('nav_home') ?></a><span><?= $LANG === 'en' ? 'Introduces me and all the sections below.' : 'Giới thiệu bản thân và toàn bộ các mục dưới đây.' ?></span></li>
                </ul>

                <h3><?= $LANG === 'en' ? 'Homepage sections' : 'Các mục trên trang chủ' ?></h3>
                <ul class="sitemap-list">
                    <?php if (($settings['show_skills'] ?? '1') === '1' && count($skills) > 0): ?>
                    <li><a href="<?= BASE_PATH ?>/index.php#skills"><?= t('nav_skills') ?></a><span><?= $LANG === 'en' ? 'Technologies and professional skills.' : 'Các công nghệ và kỹ năng chuyên môn.' ?></span></li>
                    <?php endif; ?>
                    <?php if (($settings['show_experience'] ?? '1') === '1' && count($experiences) > 0): ?>
                    <li><a href="<?= BASE_PATH ?>/index.php#experience"><?= t('nav_experience') ?></a><span><?= $LANG === 'en' ? 'Work history and companies I\'ve worked with.' : 'Lịch trình làm việc và công ty đã làm việc.' ?></span></li>
                    <?php endif; ?>
                    <?php if (($settings['show_projects'] ?? '1') === '1'): ?>
                    <li><a href="<?= BASE_PATH ?>/index.php#projects"><?= t('nav_projects') ?></a><span><?= $LANG === 'en' ? 'A collection of projects synced from GitHub.' : 'Bộ sưu tập dự án đồng bộ từ GitHub.' ?></span></li>
                    <?php endif; ?>
                    <?php if (($settings['show_pricing'] ?? '1') === '1'): ?>
                    <li><a href="<?= BASE_PATH ?>/index.php#pricing"><?= t('nav_pricing') ?></a><span><?= $LANG === 'en' ? 'Service packages and reference pricing.' : 'Các gói dịch vụ và mức giá tham khảo.' ?></span></li>
                    <?php endif; ?>
                    <?php if (($settings['show_faq'] ?? '1') === '1' && count($faqs) > 0): ?>
                    <li><a href="<?= BASE_PATH ?>/index.php#faq"><?= t('nav_faq') ?></a><span><?= $LANG === 'en' ? 'Answers to common questions.' : 'Giải đáp các thắc mắc phổ biến.' ?></span></li>
                    <?php endif; ?>
                    <?php if (($settings['show_contact'] ?? '1') === '1'): ?>
                    <li><a href="<?= BASE_PATH ?>/index.php#contact"><?= t('nav_contact') ?></a><span><?= $LANG === 'en' ? 'Contact channels and social profiles.' : 'Các kênh liên hệ và mạng xã hội.' ?></span></li>
                    <?php endif; ?>
                </ul>

                <h3><?= $LANG === 'en' ? 'Legal' : 'Pháp lý' ?></h3>
                <ul class="sitemap-list">
                    <li><a href="<?= BASE_PATH ?>/privacy.php"><?= t('footer_privacy') ?></a><span><?= $LANG === 'en' ? 'How we collect and protect your data.' : 'Cách chúng tôi thu thập và bảo vệ dữ liệu của bạn.' ?></span></li>
                    <li><a href="<?= BASE_PATH ?>/terms.php"><?= t('footer_terms') ?></a><span><?= $LANG === 'en' ? 'The terms that govern your use of the website.' : 'Các điều khoản điều chỉnh việc sử dụng website.' ?></span></li>
                    <li><a href="<?= BASE_PATH ?>/sitemap.php"><?= t('footer_sitemap') ?></a><span><?= $LANG === 'en' ? 'This page — a list of all website content.' : 'Trang này — danh sách toàn bộ nội dung website.' ?></span></li>
                </ul>

                <h3><?= $LANG === 'en' ? 'Admin' : 'Quản trị' ?></h3>
                <ul class="sitemap-list">
                    <li><a href="<?= BASE_PATH ?>/admin/login.php"><?= $LANG === 'en' ? 'Admin login' : 'Đăng nhập quản trị' ?></a><span><?= $LANG === 'en' ? 'Restricted area for the website administrator.' : 'Khu vực dành riêng cho quản trị viên website.' ?></span></li>
                </ul>

                <h3><?= $LANG === 'en' ? 'External links' : 'Liên kết ngoài' ?></h3>
                <ul class="sitemap-list">
                    <?php if (!empty($profile['social_github']) && $profile['social_github'] !== '#'): ?>
                    <li><a href="<?= h($profile['social_github']) ?>" rel="noopener" target="_blank">GitHub</a><span><?= $LANG === 'en' ? 'Public source code repository.' : 'Kho mã nguồn công khai.' ?></span></li>
                    <?php endif; ?>
                    <?php if (!empty($profile['email'])): ?>
                    <li><a href="mailto:<?= h($profile['email']) ?>">Email</a><span><?= $LANG === 'en' ? 'Contact me directly by email.' : 'Liên hệ trực tiếp qua email.' ?></span></li>
                    <?php endif; ?>
                </ul>

                <span class="updated"><?= t('legal_updated') ?> <?= date('d/m/Y') ?></span>
            </div>
        </div>
    </section>
<?php include 'partials/footer.php'; ?>
