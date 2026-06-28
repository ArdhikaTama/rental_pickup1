<?php
/**
 * File: config/middleware.php
 * Deskripsi: Interseptor penyaring hak akses pengguna sebelum diperbolehkan membuka halaman internal modul (RBAC Guard).
 * Pola: Prosedural Enterprise Standard (Production-Ready).
 */

// Mencegah error jika file auth_helper belum ter-load secara penuh
if (!function_exists('is_logged_in')) {
    require_once __DIR__ . '/auth_helper.php';
}

/**
 * 1. GUARD GUARD (Wajib Login)
 * Memastikan pengguna memiliki sesi aktif yang valid sebelum membuka area internal.
 */
function middleware_authenticate_user() {
    if (!is_logged_in()) {
        if (function_exists('remember_url')) {
            remember_url(); // Catat URL asal agar pasca-login otomatis dikembalikan (Intended Redirect)
        }
        
        if (function_exists('log_warning')) {
            log_warning('SECURITY_BYPASS', 'Percobaan membuka halaman internal tanpa autentikasi login.', [
                'uri' => $_SERVER['REQUEST_URI'] ?? 'UNKNOWN'
            ]);
        }
        
        $base = defined('BASE_URL') ? BASE_URL : '';
        header("Location: " . $base . "login.php?error=unauthenticated");
        exit();
    }
}

/**
 * 2. GUEST ONLY GUARD (Hanya Pengguna Anonim)
 * Melarang pengguna yang sudah login untuk kembali ke halaman Login/Register.
 */
function middleware_guest_only() {
    if (is_logged_in()) {
        $base = defined('BASE_URL') ? BASE_URL : '';
        header("Location: " . $base . "dashboard/index.php");
        exit();
    }
}

/**
 * 3. AGENT RESTRICTOR CORE
 * Fungsi dasar penyaring kecocokan peran (Role Matching Engine).
 */
function middleware_restrict_to_role($allowed_roles = []) {
    // Jalankan validasi login terlebih dahulu
    middleware_authenticate_user();
    
    $user_role = current_role();
    
    // Pastikan peran yang ditarik dari session terdaftar di dalam array hak akses
    if (!in_array($user_role, $allowed_roles)) {
        if (function_exists('log_error')) {
            log_error('SECURITY_UNAUTHORIZED', "User " . ($_SESSION['username'] ?? 'Unknown') . " ber-role '$user_role' mencoba menerobos area terlarang.", [
                'uri'          => $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
                'allowed_roles'=> $allowed_roles
            ]);
        }
        
        $base = defined('BASE_URL') ? BASE_URL : '';
        header("Location: " . $base . "config/403.php");
        exit();
    }
}

// --- 4. MIDDLEWARE LEVEL KELAS HAK AKSES PERAN TUNGGAL (Phase 3 Back-Compatibility) ---
function middleware_admin_only() { middleware_restrict_to_role([defined('ROLE_ADMIN') ? ROLE_ADMIN : 'Administrator']); }
function middleware_owner_only() { middleware_restrict_to_role([defined('ROLE_OWNER') ? ROLE_OWNER : 'Owner']); }
function middleware_kasir_only() { middleware_restrict_to_role([defined('ROLE_KASIR') ? ROLE_KASIR : 'Kasir']); }
function middleware_staff_only() { middleware_restrict_to_role([defined('ROLE_STAFF') ? ROLE_STAFF : 'Staff']); }


// --- 5. PEMETAAN MIDDLEWARE WILAYAH KERJA MODUL (Phase 4 Specification Requirements) ---

/**
 * Hak Akses Dashboard
 * Akses diberikan kepada: Administrator, Owner, dan Staff.
 */
function middleware_can_dashboard() {
    middleware_restrict_to_role(['Administrator', 'Owner', 'Staff']);
}

/**
 * Hak Akses Pengolahan Master Data (10 Master Data)
 * Akses diberikan kepada: Administrator dan Kasir. (Owner & Staff dilarang melihat)
 */
function middleware_can_master() {
    middleware_restrict_to_role(['Administrator', 'Kasir']);
}

/**
 * Hak Akses Operasional Transaksi (5 Transaksi)
 * Akses diberikan kepada: Administrator, Kasir, dan Staff (Untuk porsi booking).
 */
function middleware_can_transaksi() {
    middleware_restrict_to_role(['Administrator', 'Kasir', 'Staff']);
}

/**
 * Hak Akses Analisis Laporan Keuangan Perusahaan
 * Akses diberikan kepada: Administrator dan Owner. (Kasir & Staff dilarang melihat)
 */
function middleware_can_laporan() {
    middleware_restrict_to_role(['Administrator', 'Owner']);
}

/**
 * Hak Akses Pengaturan Utama & Database Backup/Restore
 * Akses diberikan secara eksklusif mutlak hanya kepada: Administrator.
 */
function middleware_can_setting() {
    middleware_restrict_to_role(['Administrator']);
}

/**
 * Hak Akses Pengelolaan Profil Mandiri (Ganti Password & Upload Foto)
 * Akses diberikan secara merata kepada seluruh pengguna yang sudah login.
 */
function middleware_can_profile() {
    middleware_restrict_to_role(['Administrator', 'Owner', 'Kasir', 'Staff']);
}