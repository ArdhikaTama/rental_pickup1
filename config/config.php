<?php
/**
 * File: config/config.php
 * Deskripsi: Menyimpan seluruh konfigurasi global operasional sistem.
 */

// Pengaturan Zona Waktu
date_default_timezone_set('Asia/Jakarta');

// Konfigurasi Dasar Aplikasi
define('APP_NAME', 'PICKUP-RENT');
define('APP_VERSION', '1.0.0');
define('APP_LOGO', 'assets/img/logo.png');

// Deteksi Otomatis Base URL secara Dinamis
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];
$script_name = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
$base_url = $protocol . $domain . $script_name;

// Pastikan base URL selalu merujuk ke root folder proyek jika diakses dari subfolder
if (strpos($script_name, '/master/') !== false || strpos($script_name, '/transaksi/') !== false || strpos($script_name, '/laporan/') !== false || strpos($script_name, '/dashboard/') !== false || strpos($script_name, '/profile/') !== false || strpos($script_name, '/setting/') !== false) {
    $parts = explode('/', trim($script_name, '/'));
    $base_url = $protocol . $domain . '/' . $parts[0] . '/';
}

define('BASE_URL', $base_url);

// Informasi Profil Perusahaan (Enterprise Identity)
define('COMPANY_NAME', 'PT. Premium Pickup Rental Jakarta');
define('COMPANY_ADDRESS', 'Jl. Mangga Dua Rt.1/Rw.1, Kec. Grogol Selatan, Kota Jakarta Selatan, DKI Jakarta 12220');
define('COMPANY_PHONE', '0812-3456-7890');
define('COMPANY_EMAIL', 'support@rentalpickupjkt.com');

// Preferensi Regional Lokalisasi Data
define('CURRENCY', 'Rp');
define('FORMAT_DATE', 'Y-m-d');
define('FORMAT_TIME', 'H:i:s');

// Lokasi Folder Penyimpanan Fisik Berkas Upload
define('UPLOAD_DIR', __DIR__ . '/../assets/upload/');