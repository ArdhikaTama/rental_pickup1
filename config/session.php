<?php
/**
 * File: config/session.php
 * Deskripsi: Manajemen siklus hidup sesi aman dengan validasi sidik jari jaringan (Session Fingerprinting),
 * idle timeout, regenerasi otomatis, dan pengelolaan flash message.
 * Pola: Prosedural Enterprise Standard (Production-Ready).
 */

/**
 * 1. INISIALISASI SESI AMAN BERLAPIS
 * Mengonfigurasi parameter cookie PHP sebelum sesi dimulai untuk menutup celah XSS & Hijacking.
 */
function custom_session_start() {
    if (session_status() === PHP_SESSION_NONE) {
        // Enkapsulasi kriteria Cookie agar tidak bisa dibaca oleh script client/Javascript (Anti-XSS Cookie Theft)
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_use_only_cookies', 1);
        ini_set('session.use_strict_mode', 1);
        
        // Aktifkan flag Secure jika server menggunakan protokol HTTPS
        if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['SERVER_PORT'] == 443)) {
            ini_set('session.cookie_secure', 1);
        }
        
        // Mencegah cookie dikirimkan bersamaan dengan request lintas situs (Anti-CSRF Cookie Level)
        ini_set('session.cookie_samesite', 'Strict');
        
        session_start();
    }

    // --- A. VALIDASI INTEGRITAS SESSION FINGERPRINT (IP SUBNET & BROWSER USER-AGENT) ---
    if (isset($_SESSION['user_id'])) {
        $current_ip   = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $current_ua   = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Browser';
        
        // Ekstraksi subnet IP /24 untuk mentoleransi perubahan IP dinamis ringan (misal dari 192.168.1.5 ke 192.168.1.9)
        // namun tetap memblokir total jika IP berpindah jaringan/kota.
        $current_ip_subnet = implode('.', array_slice(explode('.', $current_ip), 0, 3));
        $session_ip_subnet = implode('.', array_slice(explode('.', $_SESSION['ip_address'] ?? ''), 0, 3));

        if ($session_ip_subnet !== $current_ip_subnet || ($_SESSION['browser'] ?? '') !== $current_ua) {
            if (function_exists('log_error')) {
                log_error('SECURITY_HIJACK', "Sesi dibatalkan! Deteksi anomali sidik jari perangkat browser.", [
                    'expected_subnet' => $session_ip_subnet, 'current_subnet' => $current_ip_subnet,
                    'expected_ua'     => $_SESSION['browser'] ?? 'None', 'current_ua'     => $current_ua
                ]);
            }
            
            custom_session_destroy();
            $base = defined('BASE_URL') ? BASE_URL : '';
            header("Location: " . $base . "login.php?error=security_violation");
            exit();
        }

        // --- B. IDLE TIMEOUT GUARD (Maksimal Pasif 15 Menit / 900 Detik) ---
        $max_idle = 900; 
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $max_idle)) {
            if (function_exists('log_warning')) {
                log_warning('AUTH_TIMEOUT', "Sesi pengguna {$_SESSION['username']} diputus otomatis oleh sistem akibat Idle Timeout.");
            }
            custom_session_destroy();
            $base = defined('BASE_URL') ? BASE_URL : '';
            header("Location: " . $base . "login.php?error=session_expired");
            exit();
        }
        
        // Perbarui timestamp aktivitas terakhir setiap kali halaman diakses
        $_SESSION['last_activity'] = time();
    }

    // --- C. REGENERASI ID SESI BERKALA (Mencegah Session Fixation) ---
    // Regenerasi paksa setiap 5 menit (300 detik) untuk membatasi ruang gerak penyerang
    if (!isset($_SESSION['CREATED_TIME'])) {
        $_SESSION['CREATED_TIME'] = time();
    } elseif (time() - $_SESSION['CREATED_TIME'] > 300) {
        session_regenerate_id(true); // true = hapus file session lama di server
        $_SESSION['CREATED_TIME'] = time();
    }
}

/**
 * 2. PENGHANCURAN TOTAL SESI (CLEAN LOGOUT)
 * Membersihkan array memori sesi dan mematikan masa berlaku cookie di browser.
 */
function custom_session_destroy() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Kosongkan seluruh data variabel sesi di RAM server
    $_SESSION = [];
    
    // Hancurkan cookie PHPSESSID yang tersimpan di browser klien
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

/**
 * 3. SET FLASH MESSAGE
 * Mendaftarkan notifikasi UI sementara ke dalam memori sesi.
 */
function set_flash_message($key, $message, $type = 'success') {
    $_SESSION['flash'][$key] = [
        'message' => $message,
        'type'    => $type
    ];
}

/**
 * 4. GET FLASH MESSAGE
 * Mengambil dan langsung menghapus notifikasi dari memori agar tidak tampil berulang kali (Flash Data).
 */
function get_flash_message($key) {
    if (isset($_SESSION['flash'][$key])) {
        $flash = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $flash;
    }
    return null;
}

/**
 * 5. HAS FLASH MESSAGE
 * Memeriksa ketersediaan notifikasi tertentu.
 */
function has_flash_message($key) {
    return isset($_SESSION['flash'][$key]);
}

/**
 * 6. REMEMBER URL TARGET (INTENDED REDIRECT)
 * Mencatat URL internal terakhir yang gagal diakses akibat belum login.
 */
function remember_url() {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $_SESSION['LAST_REQUESTED_URL'] = $_SERVER['REQUEST_URI'];
    }
}