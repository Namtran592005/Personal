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

function legalInline(string $s): string {
    $s = preg_replace('/\[([^\]]+)\]\(([^)\s]+)\)/', '<a href="$2">$1</a>', $s);
    $s = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $s);
    return $s;
}

function renderLegalText(string $text): string {
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $lines = preg_split('/\r?\n/', $text);
    $out = '';
    $list = [];
    $first = true;
    $flush = function () use (&$out, &$list) {
        if ($list) {
            $out .= '<ul>';
            foreach ($list as $li) $out .= '<li>' . legalInline($li) . '</li>';
            $out .= '</ul>';
            $list = [];
        }
    };
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') { $flush(); continue; }
        if (preg_match('/^###\s+(.*)$/', $line, $m)) { $flush(); $out .= '<h4>' . legalInline($m[1]) . '</h4>'; continue; }
        if (preg_match('/^##\s+(.*)$/', $line, $m)) { $flush(); $out .= '<h3>' . legalInline($m[1]) . '</h3>'; continue; }
        if (preg_match('/^-\s+(.*)$/', $line, $m)) { $list[] = $m[1]; continue; }
        $flush();
        $out .= '<p' . ($first ? ' class="intro"' : '') . '>' . legalInline($line) . '</p>';
        $first = false;
    }
    $flush();
    return $out;
}
