<?php
$docRoot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__);
$base = substr(__DIR__, strlen(rtrim($docRoot, '/\\')));
$base = str_replace('\\', '/', $base);
define('BASE_PATH', $base === '' || $base === false || $base === '.' ? '' : $base);

// Lightweight i18n (standalone page — works even when the DB is down).
$lang = $_GET['lang'] ?? ($_COOKIE['lang'] ?? 'vi');
$lang = $lang === 'en' ? 'en' : 'vi';

$code = $_GET['code'] ?? '404';
$msg = match ($code) {
    '403' => $lang === 'en' ? 'Forbidden' : 'Forbidden',
    '404' => $lang === 'en' ? 'Page not found' : 'Page not found',
    '500' => $lang === 'en' ? 'Internal server error' : 'Internal server error',
    default => $lang === 'en' ? 'Something went wrong' : 'Something went wrong'
};
$hint = match ($code) {
    '403' => $lang === 'en' ? 'You do not have permission to view this page.' : 'Bạn không có quyền truy cập trang này.',
    '404' => $lang === 'en' ? 'The page you are looking for was moved or no longer exists.' : 'Trang bạn tìm đã bị di chuyển hoặc không còn tồn tại.',
    '500' => $lang === 'en' ? 'A server error occurred. Please try again later.' : 'Đã có lỗi phía máy chủ. Vui lòng thử lại sau.',
    default => $lang === 'en' ? 'Something unexpected happened.' : 'Đã xảy ra sự cố ngoài ý muốn.'
};
?><!DOCTYPE html>
<html lang="<?= $lang === 'en' ? 'en' : 'vi' ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>/assets/favicon.png" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/icons/phosphor/style.css" />
    <title><?= $code ?> — <?= $msg ?></title>
    <script>
    (function() {
        var saved = localStorage.getItem('darkMode');
        if (saved === 'false') document.documentElement.classList.remove('dark');
        else document.documentElement.classList.add('dark');
    })();
    function toggleDark() {
        var html = document.documentElement;
        html.classList.toggle('dark');
        localStorage.setItem('darkMode', html.classList.contains('dark'));
    }
    </script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f5f5f7; color: #1d1d1f;
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; text-align: center; padding: 24px; position: relative;
            overflow-x: hidden;
        }
        html.dark body { background: #1d1d1f; color: #f5f5f7; }

        /* grid backdrop, matches site sections */
        .grid-bg {
            position: fixed; inset: 0; pointer-events: none;
            background-image:
                repeating-linear-gradient(0deg, rgba(0,0,0,0.035) 0 1px, transparent 1px 48px),
                repeating-linear-gradient(90deg, rgba(0,0,0,0.035) 0 1px, transparent 1px 48px);
        }
        html.dark .grid-bg {
            background-image:
                repeating-linear-gradient(0deg, rgba(245,245,247,0.05) 0 1px, transparent 1px 48px),
                repeating-linear-gradient(90deg, rgba(245,245,247,0.05) 0 1px, transparent 1px 48px);
        }

        .glow {
            position: fixed; width: 560px; height: 560px; border-radius: 50%;
            background: radial-gradient(circle, rgba(0,113,227,0.14), transparent 65%);
            top: -160px; left: 50%; transform: translateX(-50%);
            filter: blur(30px); pointer-events: none;
        }
        html.dark .glow { background: radial-gradient(circle, rgba(10,132,255,0.20), transparent 65%); }

        .wrap { position: relative; max-width: 520px; animation: fadeUp .7s cubic-bezier(.2,.8,.2,1) both; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }

        .icon-wrap {
            width: 88px; height: 88px; margin: 0 auto 28px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: #e8e8ed; color: #1d1d1f;
            box-shadow: 0 8px 24px rgba(0,0,0,0.06), inset 0 0 0 1px rgba(0,0,0,0.04);
        }
        html.dark .icon-wrap { background: #2c2c2e; color: #f5f5f7; box-shadow: 0 8px 24px rgba(0,0,0,0.4), inset 0 0 0 1px rgba(255,255,255,0.06); }
        .icon-wrap i { font-size: 42px; }

        .icon-wrap {
            width: 88px; height: 88px; margin: 0 auto 28px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: #e8e8ed; color: #1d1d1f;
            box-shadow: 0 8px 24px rgba(0,0,0,0.06), inset 0 0 0 1px rgba(0,0,0,0.04);
        }
        html.dark .icon-wrap { background: #2c2c2e; color: #f5f5f7; box-shadow: 0 8px 24px rgba(0,0,0,0.4), inset 0 0 0 1px rgba(255,255,255,0.06); }
        .icon-wrap i { font-size: 42px; }

        .code {
            font-size: clamp(96px, 18vw, 160px); font-weight: 800; line-height: .95;
            letter-spacing: -0.05em;
            background: linear-gradient(135deg, #1d1d1f 0%, #6e6e73 60%, #a1a1a6 100%);
            -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
        }
        html.dark .code {
            background: linear-gradient(135deg, #f5f5f7 0%, #86868b 55%, #48484a 100%);
            -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
        }

        .pill {
            display: inline-flex; align-items: center; gap: 8px;
            margin: 18px 0 10px; padding: 7px 18px; border-radius: 999px;
            background: rgba(0,0,0,0.05); color: #515154;
            font-size: 13px; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase;
            border: 1px solid rgba(0,0,0,0.06);
        }
        html.dark .pill { background: rgba(255,255,255,0.06); color: #a1a1a6; border-color: rgba(255,255,255,0.08); }

        .title { font-size: clamp(26px, 4.5vw, 38px); font-weight: 700; letter-spacing: -0.03em; margin-bottom: 12px; }
        .hint { font-size: 16px; color: #86868b; line-height: 1.6; margin-bottom: 36px; }
        html.dark .hint { color: #a1a1a6; }

        .actions { display: flex; align-items: center; justify-content: center; gap: 14px; flex-wrap: wrap; }
        .btn-home {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 14px 30px; border-radius: 999px;
            background: #1d1d1f; color: #f5f5f7;
            font-size: 15px; font-weight: 500; text-decoration: none;
            transition: transform .2s, box-shadow .2s, background .2s;
            box-shadow: 0 4px 14px rgba(0,0,0,0.14);
        }
        .btn-home:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.2); }
        html.dark .btn-home { background: #f5f5f7; color: #1d1d1f; }
        html.dark .btn-home:hover { background: #fff; }
        .btn-admin {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 14px 26px; border-radius: 999px;
            color: #515154; font-size: 15px; font-weight: 500; text-decoration: none;
            border: 1.5px solid #d2d2d7; transition: all .2s;
        }
        .btn-admin:hover { border-color: #1d1d1f; color: #1d1d1f; background: #fff; }
        html.dark .btn-admin { color: #a1a1a6; border-color: #48484a; }
        html.dark .btn-admin:hover { border-color: #f5f5f7; color: #f5f5f7; background: #2c2c2e; }

        .dark-toggle {
            position: fixed; top: 22px; right: 22px; z-index: 50;
            width: 44px; height: 44px; border-radius: 50%; border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            background: rgba(0,0,0,0.06); color: #1d1d1f; font-size: 20px;
            transition: all .2s; backdrop-filter: blur(12px);
        }
        .dark-toggle:hover { background: rgba(0,0,0,0.12); transform: scale(1.06); }
        html.dark .dark-toggle { background: rgba(255,255,255,0.08); color: #f5f5f7; }
        html.dark .dark-toggle:hover { background: rgba(255,255,255,0.16); }
        .dark-toggle .ph-sun { display: none; }
        html.dark .dark-toggle .ph-sun { display: block; }
        html.dark .dark-toggle .ph-moon { display: none; }

        .foot { margin-top: 40px; font-size: 12px; color: #a1a1a6; }
    </style>
</head>
<body>
    <div class="grid-bg" aria-hidden="true"></div>
    <div class="glow" aria-hidden="true"></div>
    <button class="dark-toggle" onclick="toggleDark()" aria-label="Toggle dark mode">
        <i class="ph ph-moon"></i><i class="ph ph-sun"></i>
    </button>
    <div class="wrap">
        <div class="code"><?= $code ?></div>
        <span class="pill"><i class="ph ph-circle-notch"></i> <?= $msg ?></span>
        <h1 class="title"><?= $lang === 'en' ? 'Page could not be loaded' : 'Không thể tải trang' ?></h1>
        <p class="hint"><?= $hint ?></p>
        <div class="actions">
            <a class="btn-home" href="<?= BASE_PATH ?>/index.php"><i class="ph ph-house-line"></i> <?= $lang === 'en' ? 'Back to Home' : 'Về trang chủ' ?></a>
            <a class="btn-admin" href="<?= BASE_PATH ?>/admin/login.php"><i class="ph ph-user-circle"></i> <?= $lang === 'en' ? 'Admin' : 'Quản trị' ?></a>
        </div>
        <p class="foot">Personal Website</p>
    </div>
</body>
</html>
