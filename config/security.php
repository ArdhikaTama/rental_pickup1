<?php
/**
 * File: config/security.php
 * Deskripsi: Proteksi intrusi siber (XSS, CSRF Token, HTTP Headers, Simple Rate Limiter).
 */

// Penerapan HTTP Security Headers Penting
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

function init_security_headers() {
    // Dipanggil di awal rendering untuk menjamin header terkirim
}

// Manajemen Token CSRF (Cross-Site Request Forgery Protection)
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Fungsi Sanitasi Karakter Mencegah Cross-Site Scripting (XSS)
function xss_escape($data) {
    if (is_array($data)) {
        return array_map('xss_escape', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Pembatas Laju Request Sederhana (Simple Rate Limiter Berbasis IP)
function check_rate_limit() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $time_window = 60; // Hitungan dalam satuan Detik
    $max_requests = 100; // Batas toleransi jumlah request dalam satu rentang waktu

    if (!isset($_SESSION['rate_limit'][$ip])) {
        $_SESSION['rate_limit'][$ip] = [
            'count' => 1,
            'start_time' => time()
        ];
    } else {
        if (time() - $_SESSION['rate_limit'][$ip]['start_time'] < $time_window) {
            $_SESSION['rate_limit'][$ip]['count']++;
            if ($_SESSION['rate_limit'][$ip]['count'] > $max_requests) {
                header('HTTP/1.1 429 Too Many Requests');
                die('Terlalu banyak permintaan dalam waktu singkat. Silakan tunggu beberapa saat lagi.');
            }
        } else {
            $_SESSION['rate_limit'][$ip] = [
                'count' => 1,
                'start_time' => time()
            ];
        }
    }
}