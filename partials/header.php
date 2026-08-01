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
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/site.css?v=<?= filemtime(__DIR__ . '/../assets/site.css') ?>">
    <script>
    (function() {
        var saved = localStorage.getItem('darkMode');
        if (saved === 'false') {
            document.documentElement.classList.remove('dark');
        } else {
            document.documentElement.classList.add('dark');
            if (saved === null) {
                localStorage.setItem('darkMode', 'true');
            }
        }
    })();
    function toggleDark() {
        var html = document.documentElement;
        html.classList.toggle('dark');
        localStorage.setItem('darkMode', html.classList.contains('dark'));
    }
    </script>
    <?php if (($settings['enable_analytics'] ?? '1') === '1'): ?>
    <script>
    (function() {
        var p = location.pathname;
        var i = new Image();
        i.src = '<?= BASE_PATH ?>/includes/track.php?path=' + encodeURIComponent(p)
            + '&sw=' + screen.width + '&sh=' + screen.height
            + '&lang=' + encodeURIComponent(navigator.language || '');

        // Time-on-page: count only while the tab is visible, report on leave.
        var t0 = Date.now();
        var acc = 0;
        var last = t0;
        function mark() {
            var now = Date.now();
            if (!document.hidden) acc += now - last;
            last = now;
        }
        document.addEventListener('visibilitychange', mark);
        window.addEventListener('pagehide', function() {
            mark();
            var s = Math.round(acc / 1000);
            if (s > 0) {
                try {
                    navigator.sendBeacon('<?= BASE_PATH ?>/includes/track.php?duration=' + Math.min(s, <?= BEACON_MAX_SECONDS ?>));
                } catch (e) {}
            }
        });
    })();
    </script>
    <?php endif; ?>
</head>
<body>
