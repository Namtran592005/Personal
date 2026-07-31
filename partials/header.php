<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= h($pageTitle ?? 'Nam Trần — Personal Website') ?></title>
    <meta name="description" content="<?= h($profile['bio'] ?? $profile['title'] ?? 'Personal website') ?>" />
    <meta property="og:title" content="<?= h($pageTitle ?? 'Nam Trần — Personal Website') ?>" />
    <meta property="og:description" content="<?= h(truncate($profile['bio'] ?? $profile['title'] ?? 'Personal website', 160)) ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?= BASE_PATH ?>/" />
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>/assets/favicon.png" />
    <link rel="sitemap" type="application/xml" href="<?= BASE_PATH ?>/sitemap.xml" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/icons/phosphor/style.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/site.css">
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
    })();
    </script>
    <?php endif; ?>
</head>
<body>
