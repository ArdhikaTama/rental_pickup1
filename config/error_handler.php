<?php
/**
 * File: config/error_handler.php
 * Deskripsi: Penanganan tersentralisasi untuk meredam error mentah PHP dan mengalihkannya ke tampilan yang aman.
 */

function global_exception_handler($exception) {
    // Catat pesan kesalahan asli secara detail ke file log harian server
    if (function_exists('log_error')) {
        log_error('SYSTEM_CRASH', $exception->getMessage(), [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode()
        ]);
    } else {
        error_log("Critical System Crash: " . $exception->getMessage());
    }

    // Deteksi jika request datang dari AJAX Fetch API berbasis JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' || 
        strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) {
        header_remove();
        header("Content-Type: application/json; charset=UTF-8");
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Terjadi kesalahan internal pada server database. Silakan hubungi tim administrator.'
        ]);
        exit();
    }

    // Alihkan tampilan visual pengguna ke halaman penanganan error status 500
    // Menggunakan path relatif aman
    if (defined('BASE_URL')) {
        header("Location: " . BASE_URL . "config/500.php");
    } else {
        echo "<h3>Internal Server Error (500)</h3><p>Terjadi kesalahan kritis pada sistem. Silakan muat ulang halaman beberapa saat lagi.</p>";
    }
    exit();
}

function global_error_handler($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    // Ubah error prosedural konvensional menjadi pola Exception agar tertangkap oleh Exception Handler di atas
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

// Daftarkan fungsi penanganan ke inti konfigurasi internal PHP engine
set_exception_handler('global_exception_handler');
set_error_handler('global_error_handler');