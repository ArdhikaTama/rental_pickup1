<?php
/**
 * File: config/middleware.php
 * Deskripsi: Interseptor penyaring hak akses pengguna sebelum diperbolehkan membuka halaman internal modul.
 */

function middleware_authenticate_user() {
    redirect_if_not_authenticated();
}

function middleware_guest_only() {
    if (is_logged_in()) {
        header("Location: " . BASE_URL . "dashboard/index.php");
        exit();
    }
}

function middleware_restrict_to_role($allowed_roles = []) {
    middleware_authenticate_user();
    
    $user_role = current_role();
    if (!in_array($user_role, $allowed_roles)) {
        log_warning('SECURITY_ACCESS', 'User mencoba membuka area terlarang.', ['role' => $user_role, 'target_roles' => $allowed_roles]);
        header("Location: " . BASE_URL . "config/403.php");
        exit();
    }
}

function middleware_admin_only() { middleware_restrict_to_role([ROLE_ADMIN]); }
function middleware_owner_only() { middleware_restrict_to_role([ROLE_OWNER]); }
function middleware_kasir_only() { middleware_restrict_to_role([ROLE_KASIR]); }
function middleware_staff_only() { middleware_restrict_to_role([ROLE_STAFF]); }