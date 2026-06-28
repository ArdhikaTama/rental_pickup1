<?php
// config/keamanan.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteksi Cross-Site Scripting (XSS)
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Pembuatan Token CSRF Berbasis Sesi
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validasi Token CSRF pasca submit form
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Role-Based Access Control (RBAC) Guard
function ceko_hak_akses($allowed_roles = []) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../../login.php");
        exit();
    }
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: ../../dashboard/index.php?error=unauthorized");
        exit();
    }
}