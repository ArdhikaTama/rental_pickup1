<?php
/**
 * File: config/validation.php
 * Deskripsi: Fungsi-fungsi validasi aturan nilai input data formulir untuk menjaga integritas database
 * dan membatasi data sampah yang masuk sebelum diproses oleh database engine.
 * Pola: Prosedural Enterprise Standard (Production-Ready).
 */

/**
 * 1. VALIDASI KOLOM WAJIB ISI (REQUIRED)
 * Memastikan nilai input tidak kosong, null, atau hanya berisi spasi.
 */
function validate_required($value) {
    return !($value === null || trim($value) === '');
}

/**
 * 2. VALIDASI FORMAT EMAIL
 * Memastikan string memenuhi kriteria format surel/email resmi.
 */
function validate_email($value) {
    return (bool)filter_var($value, FILTER_VALIDATE_EMAIL);
}

/**
 * 3. VALIDASI TELEPON (STANDAR INDONESIA)
 * Format: Hanya angka murni dengan panjang minimal 9 digit dan maksimal 15 digit.
 */
function validate_phone($value) {
    return (bool)preg_match('/^[0-9]{9,15}$/', $value);
}

/**
 * 4. VALIDASI BATAS MINIMAL KARAKTER
 */
function validate_min_length($value, $min) {
    return strlen(trim($value)) >= $min;
}

/**
 * 5. VALIDASI BATAS MAKSIMAL KARAKTER
 */
function validate_max_length($value, $max) {
    return strlen(trim($value)) <= $max;
}

/**
 * 6. VALIDASI NUMERIK/ANGKA
 */
function validate_number($value) {
    return is_numeric($value);
}

/**
 * 7. VALIDASI FORMAT TANGGAL
 * Memastikan format input tanggal cocok dengan acuan format (default: Y-m-d).
 */
function validate_date($value, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $value);
    return $d && $d->format($format) === $value;
}

/**
 * 8. VALIDASI NIK KTP INDONESIA
 * Wajib 16 digit angka murni tanpa spasi atau karakter khusus.
 */
function validate_nik($value) {
    return (bool)preg_match('/^[0-9]{16}$/', $value);
}

/**
 * 9. VALIDASI NOMOR SIM PELANGGAN
 * Mendukung format nomor SIM Indonesia (12 hingga 14 digit angka).
 */
function validate_sim($value) {
    return (bool)preg_match('/^[0-9]{12,14}$/', $value);
}

/**
 * 10. VALIDASI PLAT NOMOR KENDARAAN (INDONESIA)
 * Format: Huruf Depan [1-2 digit] + Spasi + Angka [1-4 digit] + Spasi + Huruf Belakang [1-3 digit].
 * Contoh: B 1234 ABC, D 99 Z, KB 4567 XZ
 */
function validate_plate_number($value) {
    return (bool)preg_match('/^[A-Z]{1,2}\s[0-9]{1,4}\s[A-Z]{1,3}$/', strtoupper(trim($value)));
}

/**
 * 11. VALIDASI UKURAN FILE UPLOAD
 * Memastikan ukuran file tidak melebihi batas Megabyte yang ditentukan.
 */
function validate_file_size($file_array, $max_size_in_mb) {
    $max_bytes = $max_size_in_mb * 1024 * 1024;
    return isset($file_array['size']) && $file_array['size'] <= $max_bytes;
}

/**
 * 12. VALIDASI EKSTENSI GAMBAR/FOTO
 * Hanya mengizinkan file berekstensi .jpg, .jpeg, dan .png.
 */
function validate_image_extension($file_array) {
    if (!isset($file_array['name'])) return false;
    $allowed_extensions = ['jpg', 'jpeg', 'png'];
    $ext = strtolower(pathinfo($file_array['name'], PATHINFO_EXTENSION));
    return in_array($ext, $allowed_extensions);
}

/**
 * 13. VALIDASI EKSTENSI DOKUMEN PDF
 */
function validate_pdf_extension($file_array) {
    if (!isset($file_array['name'])) return false;
    $ext = strtolower(pathinfo($file_array['name'], PATHINFO_EXTENSION));
    return $ext === 'pdf';
}

/**
 * ====================================================================
 * PENAMBAHAN CORE ENGINE VALIDATOR UNTUK PHASE 4 (AUTHENTICATION)
 * ====================================================================
 */

/**
 * 14. VALIDASI KEBIJAKAN KOMPLEKSITAS KATA SANDI (PASSWORD POLICY)
 * Aturan Mutlak Keamanan Akun Enterprise:
 * - Minimal panjang 8 karakter
 * - Wajib mengandung minimal 1 huruf besar (Uppercase)
 * - Wajib mengandung minimal 1 huruf kecil (Lowercase)
 * - Wajib mengandung minimal 1 angka numerik (Digit)
 * - Wajib mengandung minimal 1 karakter khusus/simbol (Special Character)
 */
function validate_password_policy($password) {
    if (strlen($password) < 8) return false;
    if (!preg_match('/[A-Z]/', $password)) return false; 
    if (!preg_match('/[a-z]/', $password)) return false; 
    if (!preg_match('/[0-9]/', $password)) return false; 
    if (!preg_match('/[^A-Za-z0-9]/', $password)) return false; 
    return true;
}

/**
 * 15. ALIAS ALIAS COMPATIBILITY FUNCTIONS
 * Menambahkan fungsi pembungkus (wrapper) berformat snake_case agar serasi
 * dengan pemanggilan fungsi pembantu yang diminta di lembar kerja arsitektur.
 */
function required($value) { return validate_required($value); }
function email($value)    { return validate_email($value); }
function phone($value)    { return validate_phone($value); }
function minLength($value, $min) { return validate_min_length($value, $min); }
function maxLength($value, $max) { return validate_max_length($value, $max); }
function number($value)   { return validate_number($value); }
function date_valid($value, $format = 'Y-m-d') { return validate_date($value, $format); }
function nik($value)      { return validate_nik($value); }
function sim($value)      { return validate_sim($value); }
function plateNumber($value) { return validate_plate_number($value); }
function validateImage($file_array) { return validate_image_extension($file_array); }
function validatePDF($file_array)   { return validate_pdf_extension($file_array); }
function validateFileSize($file_array, $max_mb) { return validate_file_size($file_array, $max_mb); }