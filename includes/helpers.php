<?php
// includes/helpers.php — generic output/format helpers.

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

function formatDuration(int $seconds): string {
    if ($seconds < 60) return $seconds . 's';
    $h = intdiv($seconds, 3600);
    $m = intdiv($seconds % 3600, 60);
    $s = $seconds % 60;
    if ($h > 0) return sprintf('%dh %dm', $h, $m);
    if ($s > 0) return sprintf('%dm %ds', $m, $s);
    return $m . 'm';
}

function safe(string $key, ?array $arr = null, string $default = ''): string {
    $source = $arr ?? $_POST;
    return h($source[$key] ?? $default);
}

function videoType(string $path): string {
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    return ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'ogg' => 'video/ogg'][$ext] ?? 'video/mp4';
}
