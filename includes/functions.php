<?php
function getClientIp(): string {
    $forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? '';
    if ($forwarded) return trim(explode(',', $forwarded)[0]);
    return $_SERVER['REMOTE_ADDR'] ?? '';
}

function h(?string $str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function formatDate(?string $date, string $format = 'M Y'): string {
    if (!$date) return 'Present';
    $dt = new DateTime($date);
    return $dt->format($format);
}

function truncate(?string $text, int $limit = 120): string {
    if (!$text) return '';
    return mb_strlen($text) > $limit ? mb_substr($text, 0, $limit) . '...' : $text;
}

function safe(string $key, ?array $arr = null, string $default = ''): string {
    $source = $arr ?? $_POST;
    return h($source[$key] ?? $default);
}
