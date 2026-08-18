<?php
// Application-layer encryption for sensitive DB values (AES-256-GCM).
// Uses the key from includes/db-key.php. Values are prefixed with "enc1:" so
// legacy plaintext values keep working until they are rewritten.
require_once __DIR__ . '/dbkey.php';

function cryptoKeyBytes(string $key): string {
    return substr(hash('sha256', $key, true), 0, 32);
}

function dbEncrypt(string $plain): string {
    $key = dbKey();
    if ($key === '') return $plain;
    $iv = random_bytes(12);
    $tag = '';
    $cipher = openssl_encrypt($plain, 'aes-256-gcm', cryptoKeyBytes($key), OPENSSL_RAW_DATA, $iv, $tag);
    if ($cipher === false) return $plain;
    return 'enc1:' . base64_encode($iv . $tag . $cipher);
}

function dbDecrypt(string $payload): string {
    if (!str_starts_with($payload, 'enc1:')) return $payload;
    $key = dbKey();
    if ($key === '') return '';
    $raw = base64_decode(substr($payload, 5), true);
    if ($raw === false || strlen($raw) < 28) return '';
    $iv = substr($raw, 0, 12);
    $tag = substr($raw, 12, 16);
    $cipher = substr($raw, 28);
    $plain = openssl_decrypt($cipher, 'aes-256-gcm', cryptoKeyBytes($key), OPENSSL_RAW_DATA, $iv, $tag);
    return $plain !== false ? $plain : '';
}
