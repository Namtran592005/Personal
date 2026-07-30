<?php
$code = $_GET['code'] ?? '404';
$msg = match ($code) {
    '403' => 'Forbidden',
    '404' => 'Page not found',
    '500' => 'Internal server error',
    default => 'Something went wrong'
};
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/Personal/assets/favicon.png" />
    <link rel="stylesheet" href="/Personal/assets/site.css">
    <title><?= $code ?> — <?= $msg ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f5f5f7; color: #1d1d1f;
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; text-align: center; padding: 24px;
        }
        .wrap { max-width: 420px; }
        .code { font-size: 80px; font-weight: 700; letter-spacing: -0.04em; line-height: 1; margin-bottom: 8px; }
        .msg { font-size: 18px; color: #86868b; margin-bottom: 32px; }
        a {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 12px 28px; background: #1d1d1f; color: #fff;
            border-radius: 6px; font-size: 15px; font-weight: 500;
            text-decoration: none; transition: background 0.2s, transform 0.2s;
        }
        a:hover { background: #000; transform: translateY(-1px); }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="code"><?= $code ?></div>
        <div class="msg"><?= $msg ?></div>
        <a href="index.php">Về trang chủ</a>
    </div>
</body>
</html>
