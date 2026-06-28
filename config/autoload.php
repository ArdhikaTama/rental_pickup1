<?php
/**
 * File: config/autoload.php
 * Deskripsi: Mengotomatiskan pemuatan seluruh pustaka inti, konfigurasi, dan komponen helper sistem.
 */

// 1. Muat Berkas Konfigurasi Fundamental & Konstanta Statis
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/koneksi.php';

// 2. Daftar Komponen Skrip Core Engine yang Wajib Dimuat Secara Berurutan
$core_modules = [
    'session.php',
    'security.php',
    'logger.php',
    'db_helper.php',
    'auth_helper.php',
    'helper.php',
    'validation.php',
    'file_helper.php',
    'response.php',
    'middleware.php'
];

// 3. Lakukan Looping Pemuatan File Secara Aman Menggunakan require_once
foreach ($core_modules as $module) {
    $module_path = __DIR__ . '/' . $module;
    if (file_exists($module_path)) {
        require_once $module_path;
    } else {
        error_log("Gagal memuat modul sistem utama: " . $module_path);
        die("Komponen Sistem Kritis Hilang: " . $module);
    }
}