<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$ip = getClientIp();
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$ref = $_SERVER['HTTP_REFERER'] ?? '';
$path = $_GET['path'] ?? '/';
$sw = (int)($_GET['sw'] ?? 0);
$sh = (int)($_GET['sh'] ?? 0);
$lang = $_GET['lang'] ?? '';
$country = $_GET['country'] ?? '';
$city = $_GET['city'] ?? '';
$duration = max(0, min(3600, (int)($_GET['duration'] ?? 0)));

if ($dbAvailable && $ip && ($settings['enable_analytics'] ?? '1') === '1') {
    try {
        if ($duration > 0) {
            // Time-on-page beacon (sent from the client on page leave)
            $pdo->prepare("UPDATE analytics SET time_spent = time_spent + ?, last_seen = datetime('now') WHERE ip = ?")
                ->execute([$duration, $ip]);
            header('Content-Type: text/plain');
            echo 'ok';
            exit;
        }
        $stmt = $pdo->prepare("SELECT id, visits, pages FROM analytics WHERE ip = ?");
        $stmt->execute([$ip]);
        $existing = $stmt->fetch();

        if ($existing) {
            $pages = $existing['pages'] ? explode(',', $existing['pages']) : [];
            if (!in_array($path, $pages)) $pages[] = $path;
            $newPages = implode(',', array_slice($pages, -20));
            $newVisits = (int)$existing['visits'] + 1;
            $pdo->prepare("UPDATE analytics SET visits=?, pages=?, last_seen=datetime('now'), user_agent=?, referrer=?, screen_w=?, screen_h=?, language=? WHERE ip=?")
                ->execute([$newVisits, $newPages, $ua, $ref, $sw, $sh, $lang, $ip]);
        } else {
            $pdo->prepare("INSERT INTO analytics (ip, user_agent, referrer, screen_w, screen_h, language, pages, visits, first_seen, last_seen) VALUES (?,?,?,?,?,?,?,1,datetime('now'),datetime('now'))")
                ->execute([$ip, $ua, $ref, $sw, $sh, $lang, $path]);
        }
    } catch (PDOException $e) {}
}

header('Content-Type: image/gif');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
