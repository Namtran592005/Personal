<?php
function emailTemplate(string $title, string $bodyHtml): string {
    $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scheme = $_SERVER['REQUEST_SCHEME'] ?? 'https';
    $siteUrl = $scheme . '://' . $domain . (BASE_PATH ?: '');
    return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<style>
  *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f5f5f7; color: #1d1d1f; line-height: 1.6; -webkit-font-smoothing: antialiased; }
  .wrap { max-width: 600px; margin: 0 auto; padding: 40px 24px; }
  .card { background: #ffffff; border: 1px solid #e8e8ed; border-radius: 6px; overflow: hidden; }
  .head { padding: 32px 32px 0; }
  .head h1 { font-size: 17px; font-weight: 600; letter-spacing: -0.02em; color: #1d1d1f; }
  .body { padding: 24px 32px 32px; }
  .body p { font-size: 15px; color: #515154; margin-bottom: 16px; }
  .body p:last-child { margin-bottom: 0; }
  .body strong { color: #1d1d1f; }
  .label { font-size: 11px; font-weight: 600; color: #86868b; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 4px; }
  .divider { height: 1px; background: #e8e8ed; margin: 20px 0; }
  .foot { text-align: center; padding: 24px 32px; font-size: 12px; color: #a1a1a6; border-top: 1px solid #e8e8ed; }
  .foot a { color: #1d1d1f; text-decoration: underline; }
  .btn { display: inline-block; padding: 12px 28px; background: #1d1d1f; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 500; margin: 8px 0; }
  @media (max-width: 480px) { .head, .body, .foot { padding-left: 20px; padding-right: 20px; } }
</style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <div class="head"><h1>$title</h1></div>
      <div class="body">$bodyHtml</div>
    </div>
    <div class="foot">
      <p>Nam Trần &middot; <a href="$siteUrl">$domain</a></p>
    </div>
  </div>
</body>
</html>
HTML;
}
