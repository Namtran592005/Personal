<?php
function env(string $key, string $default = ''): string {
    $v = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if ($v) return $v;
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;
            if (str_starts_with($line, "$key=")) {
                $v = substr($line, strlen("$key="));
                return trim($v, '"\'');
            }
        }
    }
    return $default;
}
$smtp_host = env('SMTP_HOST');
$smtp_port = (int)env('SMTP_PORT', '587');
$smtp_user = env('SMTP_USER');
$smtp_pass = env('SMTP_PASS');
$smtp_from = env('SMTP_FROM');
$smtp_from_name = env('SMTP_FROM_NAME', 'Nam Trần');

function sendMail(string $to, string $subject, string $htmlBody): bool {
    global $smtp_host, $smtp_port, $smtp_user, $smtp_pass, $smtp_from, $smtp_from_name;
    if (empty($smtp_host)) return false;

    $boundary = '----=_Part_' . md5(uniqid());
    $headers = "From: $smtp_from_name <$smtp_from>\r\n"
             . "Reply-To: $smtp_from_name <$smtp_from>\r\n"
             . "Subject: $subject\r\n"
             . "MIME-Version: 1.0\r\n"
             . "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";

    $textBody = strip_tags($htmlBody);

    $body = "--$boundary\r\n"
          . "Content-Type: text/plain; charset=UTF-8\r\n\r\n"
          . "$textBody\r\n\r\n"
          . "--$boundary\r\n"
          . "Content-Type: text/html; charset=UTF-8\r\n\r\n"
          . "$htmlBody\r\n\r\n"
          . "--$boundary--";

    $errno = 0; $errstr = '';
    $socket = @fsockopen($smtp_host, $smtp_port, $errno, $errstr, 10);
    if (!$socket) return false;

    $ehlo = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $resp = fgets($socket, 512);
    fwrite($socket, "EHLO $ehlo\r\n"); while ($line = fgets($socket, 512)) if (strpos($line, ' ') === 3) break;
    if ($smtp_port == 587) {
        fwrite($socket, "STARTTLS\r\n"); fgets($socket, 512);
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        fwrite($socket, "EHLO $ehlo\r\n"); while ($line = fgets($socket, 512)) if (strpos($line, ' ') === 3) break;
    }
    if ($smtp_user) {
        fwrite($socket, "AUTH LOGIN\r\n"); fgets($socket, 512);
        fwrite($socket, base64_encode($smtp_user) . "\r\n"); fgets($socket, 512);
        fwrite($socket, base64_encode($smtp_pass) . "\r\n"); fgets($socket, 512);
    }
    fwrite($socket, "MAIL FROM:<$smtp_from>\r\n"); fgets($socket, 512);
    fwrite($socket, "RCPT TO:<$to>\r\n"); fgets($socket, 512);
    fwrite($socket, "DATA\r\n"); fgets($socket, 512);
    fwrite($socket, "Date: " . date('r') . "\r\n");
    fwrite($socket, "To: <$to>\r\n$headers\r\n$body\r\n.\r\n"); fgets($socket, 512);
    fwrite($socket, "QUIT\r\n");
    fclose($socket);
    return true;
}
