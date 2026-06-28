<?php
/**
 * File: logout.php
 * Deskripsi: Berkas penanganan penghancuran sesi otorisasi pengguna (Sign Out Action).
 */
require_once __DIR__ . '/config/autoload.php';

custom_session_start();

// Catat riwayat audit log sebelum sesi dihancurkan sepenuhnya
if (isset($_SESSION['user_id'])) {
    log_activity("User ".$_SESSION['username']." melakukan logout dari sistem.");
}

// Eksekusi penghancuran total variabel sesi beserta cookie pelacaknya
custom_session_destroy();

// Alihkan balik halaman ke form login awal
header("Location: " . BASE_URL . "login.php");
exit();