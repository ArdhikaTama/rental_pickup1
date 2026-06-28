<?php
/**
 * File: config/response.php
 * Deskripsi: Pembungkus keseragaman format data keluaran respon sistem baik ke View maupun Ajax JSON Client.
 */

function response_json($status_code, $success, $message, $data = []) {
    header_remove();
    header("Content-Type: application/json; charset=UTF-8");
    http_response_code($status_code);
    
    echo json_encode([
        'success'   => $success,
        'message'   => $message,
        'data'      => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit();
}

function response_success_json($message, $data = []) {
    response_json(200, true, $message, $data);
}

function response_error_json($message, $status_code = 400, $data = []) {
    response_json($status_code, false, $message, $data);
}

function redirect_to($url) {
    header("Location: " . $url);
    exit();
}

function redirect_back() {
    $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL . 'dashboard/index.php';
    header("Location: " . $referer);
    exit();
}