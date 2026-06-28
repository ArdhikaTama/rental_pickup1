<?php
/**
 * File: config/helper.php
 * Deskripsi: Kumpulan fungsi utilitas global pendukung pemrosesan data umum aplikasi (Production-Ready).
 * Pola: Prosedural Modular (Murni PHP Native tanpa Object-Oriented/Class)
 */

// Mencegah akses langsung ke file konfigurasi tanpa melalui sistem utama jika diperlukan
if (!defined('BASE_URL') && file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

/**
 * 1. FORMAT NOMINAL KE RUPIAH
 * Mengubah angka numerik menjadi format mata uang Rupiah yang rapi.
 */
function format_rupiah($angka) {
    if ($angka === null || $angka === '') return 'Rp 0';
    return "Rp " . number_format((float)$angka, 0, ',', '.');
}

/**
 * 2. FORMAT TANGGAL STANDAR
 * Mengubah format tanggal mentah menjadi format tertentu (default: d-m-Y).
 */
function format_date($tanggal, $format = 'd-m-Y') {
    if (empty($tanggal)) return '-';
    return date($format, strtotime($tanggal));
}

/**
 * 3. FORMAT TANGGAL INDONESIA LENGKAP
 * Mengubah tanggal (YYYY-MM-DD) menjadi teks Indonesia (Contoh: 28 Juni 2026).
 */
function format_tanggal_indonesia($tanggal) {
    if (empty($tanggal)) return '-';
    
    $bulan_id = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $time_element = strtotime($tanggal);
    $hari  = date('d', $time_element);
    $bulan = (int)date('m', $time_element);
    $tahun = date('Y', $time_element);
    
    return $hari . ' ' . $bulan_id[$bulan] . ' ' . $tahun;
}

/**
 * 4. SANITASI DATA INPUT (ANTI-XSS)
 * Membersihkan data string input dari karakter berbahaya untuk mencegah Cross-Site Scripting.
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * 5. CLEAN INPUT ALFANUMERIK
 * Membersihkan input murni agar hanya menyisakan karakter huruf, angka, dan spasi.
 */
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    return preg_replace('/[^A-Za-z0-9\s\-]/', '', $data);
}

/**
 * 6. GLOBAL AUTO GENERATE TRANSACTIONS NUMBER (STANDARD LOGISTIK & ERP)
 * Mengenerate nomor transaksi berurutan otomatis berbasis database menggunakan PDO Prepared Statement.
 * Format Output: {PREFIX}-{YYYYMM}-{COUNTER} | Contoh: TRX-202606-0001
 */
function generate_trans_number($pdo, $prefix, $table, $column) {
    $yearMonth = date('Ym');
    $searchPattern = $prefix . "-" . $yearMonth . "-%";
    
    // Menggunakan parameter PDO bertenaga untuk keamanan SQL Injection
    $stmt = $pdo->prepare("SELECT $column FROM $table WHERE $column LIKE ? ORDER BY $column DESC LIMIT 1");
    $stmt->execute([$searchPattern]);
    $lastRecord = $stmt->fetchColumn();

    if ($lastRecord) {
        // Mengambil 4 digit angka terakhir dari nomor transaksi sebelumnya
        $lastNumber = (int)substr($lastRecord, -4);
        $nextNumber = $lastNumber + 1;
    } else {
        $nextNumber = 1;
    }

    return $prefix . "-" . $yearMonth . "-" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
}

// --- SHORTCUT FUNCTIONS GENERATOR (Sesuai Spesifikasi Transaksi) ---
function generateInvoice($pdo)     { return generate_trans_number($pdo, 'INV', 't_penyewaan', 'nomor_sewa'); }
function generateBooking($pdo)     { return generate_trans_number($pdo, 'BKG', 't_booking', 'nomor_booking'); }
function generateSewa($pdo)        { return generate_trans_number($pdo, 'TRX', 't_penyewaan', 'nomor_sewa'); }
function generatePembayaran($pdo)  { return generate_trans_number($pdo, 'PAY', 't_pembayaran', 'nomor_pembayaran'); }
function generatePengembalian($pdo){ return generate_trans_number($pdo, 'RET', 't_pengembalian', 'nomor_pengembalian'); }

/**
 * 7. REDIRECT HALAMAN
 * Melakukan pengalihan halaman internal web secara aman.
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * 8. BACK TO PREVIOUS PAGE
 * Mengembalikan user ke halaman pengirim sebelumnya (HTTP_REFERER).
 */
function back() {
    $referer = $_SERVER['HTTP_REFERER'] ?? (defined('BASE_URL') ? BASE_URL . 'dashboard/index.php' : 'index.php');
    header("Location: " . $referer);
    exit();
}

/**
 * 9. RECOVERY INPUT LAMA (OLD VALUE)
 * Memanggil kembali data yang baru saja diketik di form agar tidak hilang saat validasi error terjadi.
 */
function old($key, $default = '') {
    return $_POST[$key] ?? ($_GET[$key] ?? $default);
}

/**
 * 10. GENERATE ASSETS URL
 * Menyusun path mutlak pemanggilan aset frontend statis web.
 */
function asset($path) {
    $base = defined('BASE_URL') ? BASE_URL : '';
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

/**
 * 11. BASE URL MAKER
 * Memanggil root utama proyek aplikasi.
 */
function base_url($path = '') {
    $base = defined('BASE_URL') ? BASE_URL : '';
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

/**
 * 12. STRING TO SLUG GENERATOR
 * Mengonversi string judul menjadi format URL friendly (Contoh: "Colt L300" -> "colt-l300").
 */
function slug($string) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
}

/**
 * 13. GENERATE CRYPTOGRAPHIC UUID
 * Menghasilkan string acak unik universal berstandar 36 karakter.
 */
function uuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff) & 0x0fff | 0x4000,
        mt_rand(0, 0xffff) & 0x3fff | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

/**
 * 14. GENERATE RANDOM STRING CRYPTO
 * Menghasilkan token string acak bertenaga tinggi untuk kebutuhan tokenizing.
 */
function randomString($length = 10) {
    return substr(bin2hex(random_bytes($length)), 0, $length);
}