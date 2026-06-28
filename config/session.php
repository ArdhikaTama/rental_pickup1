<?php
/**
 * File: config/session.php
 * Deskripsi: Mengelola daur hidup session pengguna, mitigasi session hijacking, dan flash messages.
 */

function custom_session_start() {
    if (session_status() === PHP_SESSION_NONE) {
        // Konfigurasi Parameter Cookie Sesi yang Aman (Proteksi Session Hijacking)
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_use_only_cookies', 1);
        
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            ini_set('session.cookie_secure', 1);
        }
        
        session_start();
    }

    // Cek Keaslian Pengguna Berdasarkan Identitas User-Agent Browser
    if (isset($_SESSION['USER_AGENT'])) {
        if ($_SESSION['USER_AGENT'] !== $_SERVER['HTTP_USER_AGENT']) {
            custom_session_destroy();
            header("Location: " . BASE_URL . "login.php?error=session_compromised");
            exit();
        }
    } else {
        $_SESSION['USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
    }

    // Implementasi Masa Kedaluwarsa Sesi (Timeout 30 Menit)
    $timeout_duration = 1800; 
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout_duration)) {
        custom_session_destroy();
        header("Location: " . BASE_URL . "login.php?error=session_timeout");
        exit();
    }
    $_SESSION['LAST_ACTIVITY'] = time();

    // Regenerasi Berkala ID Sesi (Setiap 5 Menit) Guna Mencegah Session Fixation
    if (!isset($_SESSION['CREATED_TIME'])) {
        $_SESSION['CREATED_TIME'] = time();
    } elseif (time() - $_SESSION['CREATED_TIME'] > 300) {
        session_regenerate_id(true);
        $_SESSION['CREATED_TIME'] = time();
    }
}

function custom_session_destroy() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

// Pengelolaan Notifikasi Sementara (Flash Message UI)
function set_flash_message($key, $message, $type = 'success') {
    $_SESSION['flash'][$key] = [
        'message' => $message,
        'type' => $type
    ];
}

function get_flash_message($key) {
    if (isset($_SESSION['flash'][$key])) {
        $flash = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $flash;
    }
    return null;
}

function has_flash_message($key) {
    return isset($_SESSION['flash'][$key]);
}

// Menyimpan URL Terakhir yang Diakses untuk Kebutuhan Redirect Pasca Login Interupt
function remember_url() {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $_SESSION['LAST_REQUESTED_URL'] = $_SERVER['REQUEST_URI'];
    }
}