<!DOCTYPE html>
<html lang="<?= h($LANG ?? 'vi') ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= h($pageTitle ?? 'Nam Trần — Personal Website') ?></title>
    <meta name="description" content="<?= h($profile['bio'] ?? $profile['title'] ?? t('meta_default_desc')) ?>" />
    <meta property="og:title" content="<?= h($pageTitle ?? 'Nam Trần — Personal Website') ?>" />
    <meta property="og:description" content="<?= h(truncate($profile['bio'] ?? $profile['title'] ?? t('meta_default_desc'), 160)) ?>" />
    <meta property="og:type" content="website" />
    <?php
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $ogUrl = $scheme . '://' . $host . ($_SERVER['SCRIPT_NAME'] ?? BASE_PATH . '/');
    ?>
    <meta property="og:url" content="<?= h($ogUrl) ?>" />
    <link rel="alternate" hreflang="vi" href="<?= BASE_PATH ?>/index.php?lang=vi" />
    <link rel="alternate" hreflang="en" href="<?= BASE_PATH ?>/index.php?lang=en" />
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>/assets/favicon.png" />
    <link rel="sitemap" type="application/xml" href="<?= BASE_PATH ?>/sitemap.xml" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/icons/phosphor/style.css" />
    <?php
    $cssFiles = ['base', 'decor', 'nav', 'hero', 'sections', 'layout', 'components', 'theme'];
    foreach ($cssFiles as $f) {
        $p = __DIR__ . '/../assets/css/' . $f . '.css';
        $v = file_exists($p) ? filemtime($p) : '0';
        echo '<link rel="stylesheet" href="' . BASE_PATH . '/assets/css/' . $f . '.css?v=' . $v . '">' . "\n";
    }
    ?>
    <script src="<?= BASE_PATH ?>/assets/js/darkmode.js"></script>
</head>
<body>
