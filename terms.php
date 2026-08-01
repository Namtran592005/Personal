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
$projects = [];
$faqs = [];

$legalDefaults = require __DIR__ . '/includes/legal_defaults.php';

$legalContent = $legalDefaults['terms'] ?? '';
$legalUpdated = date('d/m/Y');
if ($dbAvailable) {
    try {
        $st = $pdo->prepare("SELECT content, updated_at FROM pages WHERE key = 'terms'");
        $st->execute();
        $row = $st->fetch();
        if ($row) {
            if (!empty($row['content'])) $legalContent = $row['content'];
            if (!empty($row['updated_at'])) $legalUpdated = date('d/m/Y', strtotime($row['updated_at']));
        }
    } catch (PDOException $e) {}
}
$legalHtml = renderLegalText($legalContent);
$legalHtml = str_replace(['{name}', '{email}'], [h($profile['name'] ?? 'Nam Trần'), h($profile['email'] ?? '')], $legalHtml);

$pageTitle = 'Điều Khoản Sử Dụng — ' . ($profile['name'] ?? 'Nam Trần');

$navHomeOnly = true;

include 'partials/header.php';
include 'partials/nav.php';
?>
    <section class="section legal-section">
        <div class="container">
            <div class="section-header">
                <p class="label">Terms of Service</p>
                <h2>Điều Khoản Sử Dụng</h2>
            </div>
            <div class="legal">
                <?= $legalHtml ?>
                <span class="updated">Cập nhật lần cuối: <?= h($legalUpdated) ?></span>
            </div>
        </div>
    </section>
<?php include 'partials/footer.php'; ?>
