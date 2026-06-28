<?php
/**
 * File: config/auth_helper.php
 * Deskripsi: Kumpulan fungsi pembantu pengecekan otorisasi login dan hak akses pengguna.
 */

function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

function current_user() {
    if (!is_logged_in()) return null;
    return [
        'id_user'  => $_SESSION['user_id'],
        'nama'     => $_SESSION['nama_user'],
        'username' => $_SESSION['username'],
        'role'     => $_SESSION['role']
    ];
}

function current_role() {
    return $_SESSION['role'] ?? null;
}

function is_admin() { return current_role() === ROLE_ADMIN; }
function is_owner() { return current_role() === ROLE_OWNER; }
function is_kasir() { return current_role() === ROLE_KASIR; }
function is_staff() { return current_role() === ROLE_STAFF; }

function redirect_if_not_authenticated() {
    if (!is_logged_in()) {
        remember_url();
        set_flash_message('auth_error', 'Sesi Anda belum teridentifikasi. Silakan masuk terlebih dahulu.', 'warning');
        header("Location: " . BASE_URL . "login.php");
        exit();
    }
}