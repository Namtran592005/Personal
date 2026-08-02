<?php
// includes/legal.php — renderers for the plain-text legal pages (privacy/terms).

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
