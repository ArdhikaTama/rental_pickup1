<?php
/**
 * File: config/logger.php
 * Deskripsi: Sistem pencatatan riwayat operasional aplikasi ke dalam berkas log teks harian fisik.
 */

define('LOG_PATH', __DIR__ . '/../logs/');

function write_app_log($level, $category, $message, $details = null) {
    // Pastikan direktori penampung log harian tersedia
    if (!is_dir(LOG_PATH)) {
        mkdir(LOG_PATH, 0755, true);
    }

    $current_date = date('Y-m-d');
    $log_file = LOG_PATH . 'log_' . $current_date . '.txt';
    
    $timestamp = date('Y-m-d H:i:s');
    $user_id = $_SESSION['user_id'] ?? 'GUEST/ANONYMOUS';
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN_IP';
    
    $formatted_details = $details ? (is_array($details) ? json_encode($details) : $details) : '';
    
    $log_entry = sprintf(
        "[%s] [%s] [%s] [User ID: %s] [IP: %s] - Message: %s | Details: %s" . PHP_EOL,
        $timestamp, strtoupper($level), strtoupper($category), $user_id, $ip_address, $message, $formatted_details
    );

    // Tulis entri log secara append ke dalam file harian
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}

// Shortcut Pemanggilan Level Log Spesifik
function log_info($category, $message, $details = null) { write_app_log('INFO', $category, $message, $details); }
function log_warning($category, $message, $details = null) { write_app_log('WARNING', $category, $message, $details); }
function log_error($category, $message, $details = null) { write_app_log('ERROR', $category, $message, $details); }
function log_success($category, $message, $details = null) { write_app_log('SUCCESS', $category, $message, $details); }
function log_activity($message, $details = null) { write_app_log('ACTIVITY', 'USER_ACTION', $message, $details); }