<?php
// Global rate limiting by IP using a fixed sliding window (rate_limits table).
// Called from config.php on every request. Returns true when the IP is over
// the limit (caller should respond 429). Idempotent and DB-safe.

function rateLimitCheck(): bool {
    global $pdo, $dbAvailable;
    if (!$dbAvailable || !$pdo) return false;

    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $now = time();
    $window = (int)floor($now / RATE_LIMIT_WINDOW) * RATE_LIMIT_WINDOW;

    try {
        $pdo->prepare("INSERT INTO rate_limits (ip, window_start, hits) VALUES (?, ?, 1)
            ON CONFLICT(ip, window_start) DO UPDATE SET hits = hits + 1")
            ->execute([$ip, $window]);

        $stmt = $pdo->prepare("SELECT hits FROM rate_limits WHERE ip = ? AND window_start = ?");
        $stmt->execute([$ip, $window]);
        $hits = (int)$stmt->fetchColumn();

        // Best-effort cleanup of expired windows (at most a few per request).
        $pdo->prepare("DELETE FROM rate_limits WHERE window_start < ?")
            ->execute([$now - RATE_LIMIT_WINDOW * 2]);

        return $hits > RATE_LIMIT_MAX;
    } catch (PDOException $e) {
        return false;
    }
}

// Dedicated throttle for the admin login / 2FA forms (login_rate_limits table).
// Stricter than the global limiter: caps login POST attempts per IP per window
// regardless of success/failure. Caller should reject the attempt when true.
function loginRateLimitCheck(): bool {
    global $pdo, $dbAvailable;
    if (!$dbAvailable || !$pdo) return false;

    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $now = time();
    $window = (int)floor($now / LOGIN_RATE_WINDOW) * LOGIN_RATE_WINDOW;

    try {
        $pdo->prepare("INSERT INTO login_rate_limits (ip, window_start, hits) VALUES (?, ?, 1)
            ON CONFLICT(ip, window_start) DO UPDATE SET hits = hits + 1")
            ->execute([$ip, $window]);

        $stmt = $pdo->prepare("SELECT hits FROM login_rate_limits WHERE ip = ? AND window_start = ?");
        $stmt->execute([$ip, $window]);
        $hits = (int)$stmt->fetchColumn();

        $pdo->prepare("DELETE FROM login_rate_limits WHERE window_start < ?")
            ->execute([$now - LOGIN_RATE_WINDOW * 2]);

        return $hits > LOGIN_RATE_MAX;
    } catch (PDOException $e) {
        return false;
    }
}
