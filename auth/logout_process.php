<?php
/**
 * File: auth/logout_process.php
 * Deskripsi: Eksekusi pembersihan sesi keluar dari sistem (Sign Out Handler).
 */

require_once dirname(__DIR__) . '/config/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    // Catat log audit aktivitas sebelum variabel dihancurkan
    log_success('AUTH_LOGOUT', "User {$_SESSION['username']} secara sadar melakukan keluar sistem (logout).");
}

// Panggil fungsi pembersih terpusat dari Phase 3
if (function_exists('custom_session_destroy')) {
    custom_session_destroy();
} else {
    $_SESSION = [];
    session_destroy();
}

// Alihkan halaman ke form masuk awal dengan parameter penanda sukses
header("Location: " . BASE_URL . "login.php?logout=success");
exit();