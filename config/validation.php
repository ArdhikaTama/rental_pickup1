<?php
/**
 * File: config/validation.php
 * Deskripsi: Fungsi-fungsi validasi aturan nilai input data form demi menjaga integritas database.
 */

function validate_required($value) {
    return !($value === null || trim($value) === '');
}

function validate_email($value) {
    return (bool)filter_var($value, FILTER_VALIDATE_EMAIL);
}

function validate_phone($value) {
    // Format nomor telepon standar Indonesia: minimal 9 digit, maksimal 15 digit angka murni
    return (bool)preg_match('/^[0-9]{9,15}$/', $value);
}

function validate_min_length($value, $min) {
    return strlen(trim($value)) >= $min;
}

function validate_max_length($value, $max) {
    return strlen(trim($value)) <= $max;
}

function validate_number($value) {
    return is_numeric($value);
}

function validate_date($value, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $value);
    return $d && $d->format($format) === $value;
}

function validate_nik($value) {
    // Validasi NIK KTP Indonesia (Wajib 16 digit angka murni)
    return (bool)preg_match('/^[0-9]{16}$/', $value);
}

function validate_sim($value) {
    // Validasi Nomor Surat Izin Mengemudi (Wajib 12 sampai 14 digit)
    return (bool)preg_match('/^[0-9]{12,14}$/', $value);
}

function validate_plate_number($value) {
    // Validasi format Tanda Nomor Kendaraan Bermotor (Plat Nomor Indonesia)
    return (bool)preg_match('/^[A-Z]{1,2}\s[0-9]{1,4}\s[A-Z]{1,3}$/', strtoupper(trim($value)));
}

function validate_file_size($file_array, $max_size_in_mb) {
    $max_bytes = $max_size_in_mb * 1024 * 1024;
    return isset($file_array['size']) && $file_array['size'] <= $max_bytes;
}

function validate_image_extension($file_array) {
    if (!isset($file_array['name'])) return false;
    $allowed_extensions = ['jpg', 'jpeg', 'png'];
    $ext = strtolower(pathinfo($file_array['name'], PATHINFO_EXTENSION));
    return in_array($ext, $allowed_extensions);
}

function validate_pdf_extension($file_array) {
    if (!isset($file_array['name'])) return false;
    $ext = strtolower(pathinfo($file_array['name'], PATHINFO_EXTENSION));
    return $ext === 'pdf';
}