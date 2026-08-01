<?php
// i18n: language detection + t() helper.
// Loaded from config.php (after settings) so it's available on all pages.
// Language resolution: ?lang= param > cookie > settings.default_lang > vi.

$ALLOWED_LANGS = ['vi', 'en'];

function currentLang(): string {
    global $ALLOWED_LANGS, $settings;
    $lang = $_GET['lang'] ?? '';
    if (!in_array($lang, $ALLOWED_LANGS, true)) {
        $lang = $_COOKIE['lang'] ?? '';
    }
    if (!in_array($lang, $ALLOWED_LANGS, true)) {
        $lang = ($settings['default_lang'] ?? '') === 'en' ? 'en' : 'vi';
    }
    if (!in_array($lang, $ALLOWED_LANGS, true)) {
        $lang = 'vi';
    }
    return $lang;
}

// Persist the language preference (only when explicitly switched).
if (isset($_GET['lang']) && in_array($_GET['lang'], $ALLOWED_LANGS, true)) {
    setcookie('lang', $_GET['lang'], time() + 60 * 60 * 24 * 365, '/', '', false, true);
}

$LANG = currentLang();

function t(string $key, string $default = ''): string {
    global $LANG;
    static $strings = null;
    if ($strings === null) {
        $strings = require __DIR__ . '/i18n.php';
    }
    if (isset($strings[$LANG][$key])) return $strings[$LANG][$key];
    if (isset($strings['vi'][$key])) return $strings['vi'][$key];
    return $default !== '' ? $default : $key;
}

// Current page URL with an explicit lang param (for the language switcher).
// SCRIPT_NAME already includes BASE_PATH (e.g. /subdir/index.php), so don't
// prefix it again; only fall back to the homepage when the route isn't a .php file.
function langUrl(string $lang): string {
    $qs = $_GET;
    $qs['lang'] = $lang;
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    if (!str_ends_with($script, '.php')) $script = BASE_PATH . '/index.php';
    return $script . '?' . http_build_query($qs);
}
