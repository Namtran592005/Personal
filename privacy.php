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
$projects = [];
$faqs = [];

$legalDefaults = require __DIR__ . '/includes/legal_defaults.php';

$legalKey = $LANG === 'en' ? 'privacy_en' : 'privacy';
$legalContent = $legalDefaults[$legalKey] ?? $legalDefaults['privacy'] ?? '';
$legalUpdated = date('d/m/Y');
if ($dbAvailable) {
    try {
        $st = $pdo->prepare("SELECT content, updated_at FROM pages WHERE key = ?");
        $st->execute([$legalKey]);
        $row = $st->fetch();
        if ($row) {
            if (!empty($row['content'])) $legalContent = $row['content'];
            if (!empty($row['updated_at'])) $legalUpdated = date('d/m/Y', strtotime($row['updated_at']));
        }
    } catch (PDOException $e) {}
}
$legalHtml = renderLegalText($legalContent);
$legalHtml = str_replace(['{name}', '{email}'], [h($profile['name'] ?? ''), h($profile['email'] ?? '')], $legalHtml);

$pageTitle = t('privacy_title') . ' — ' . ($profile['name'] ?? '');

$navHomeOnly = true;

include 'partials/header.php';
include 'partials/nav.php';
?>
    <section class="section legal-section">
        <div class="container">
            <div class="section-header">
                <p class="label"><?= t('privacy_label') ?></p>
                <h2><?= t('privacy_title') ?></h2>
            </div>
            <div class="legal">
                <?= $legalHtml ?>
                <span class="updated"><?= t('legal_updated') ?> <?= h($legalUpdated) ?></span>
            </div>
        </div>
    </section>
<?php include 'partials/footer.php'; ?>
