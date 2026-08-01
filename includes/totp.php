<?php
// Minimal RFC 6238 TOTP (2FA) helpers, no external libraries.
// Generates 6-digit codes every 30s using HMAC-SHA1 + base32 secrets.

function totpBase32Encode(string $data): string {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $bin = '';
    foreach (str_split($data) as $c) $bin .= str_pad(decbin(ord($c)), 8, '0', STR_PAD_LEFT);
    $out = '';
    for ($i = 0; $i < strlen($bin); $i += 5) {
        $chunk = substr($bin, $i, 5);
        $out .= $alphabet[bindec(str_pad($chunk, 5, '0', STR_PAD_RIGHT))];
    }
    return $out;
}

function totpBase32Decode(string $b32): string {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $b32 = strtoupper(preg_replace('/[^A-Z2-7]/', '', $b32));
    $bin = '';
    foreach (str_split($b32) as $c) {
        $pos = strpos($alphabet, $c);
        if ($pos === false) continue;
        $bin .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
    }
    $out = '';
    for ($i = 0; $i + 8 <= strlen($bin); $i += 8) {
        $out .= chr(bindec(substr($bin, $i, 8)));
    }
    return $out;
}

function generateTotpSecret(int $bytes = 20): string {
    return totpBase32Encode(random_bytes($bytes));
}

function totpCode(string $secret, ?int $time = null): string {
    $time = $time ?? time();
    $counter = intdiv($time, TOTP_PERIOD);
    $binCounter = pack('N*', 0, $counter);
    $hash = hash_hmac('sha1', $binCounter, totpBase32Decode($secret), true);
    $offset = ord($hash[strlen($hash) - 1]) & 0x0f;
    $binary = ((ord($hash[$offset]) & 0x7f) << 24)
            | ((ord($hash[$offset + 1]) & 0xff) << 16)
            | ((ord($hash[$offset + 2]) & 0xff) << 8)
            | (ord($hash[$offset + 3]) & 0xff);
    return str_pad((string)($binary % (10 ** TOTP_DIGITS)), TOTP_DIGITS, '0', STR_PAD_LEFT);
}

function verifyTotp(string $secret, string $code): bool {
    $code = preg_replace('/[^0-9]/', '', $code);
    if (strlen($code) !== TOTP_DIGITS) return false;
    $now = time();
    for ($i = -TOTP_WINDOW; $i <= TOTP_WINDOW; $i++) {
        if (hash_equals(totpCode($secret, $now + $i * TOTP_PERIOD), $code)) return true;
    }
    return false;
}

// otpauth:// provisioning URI for authenticator apps.
function totpProvisioningUri(string $secret, string $issuer, string $account): string {
    $label = rawurlencode($issuer . ':' . $account);
    return "otpauth://totp/$label?secret=" . rawurlencode($secret)
         . '&issuer=' . rawurlencode($issuer) . '&digits=' . TOTP_DIGITS . '&period=' . TOTP_PERIOD;
}
