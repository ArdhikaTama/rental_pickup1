<?php
/**
 * File: auth/login_process.php
 * Deskripsi: Handler pemrosesan login aman berbasis AJAX Fetch API.
 */

// Muat core engine via relative path menuju autoload
require_once dirname(__DIR__) . '/config/autoload.php';

// Pastikan request diakses melalui metode POST dan dikirim via AJAX Fetch API
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("HTTP/1.1 405 Method Not Allowed");
    echo json_encode(['success' => false, 'message' => 'Metode akses tidak diizinkan.']);
    exit();
}

// 1. Validasi Token CSRF untuk menangkal serangan Cross-Site Request Forgery
$headers = getallheaders();
$submitted_token = $_POST['csrf_token'] ?? '';

if (!function_exists('validate_csrf_token') || !validate_csrf_token($submitted_token)) {
    if (function_exists('log_warning')) {
        log_warning('SECURITY_CSRF', 'Percobaan bypass token CSRF dideteksi pada form login.');
    }
    header("HTTP/1.1 403 Forbidden");
    echo json_encode(['success' => false, 'message' => 'Token keamanan CSRF tidak valid.']);
    exit();
}

// 2. Ambil dan sanitasi data input dari user
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validasi mendasar kekosongan input
if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Username dan password wajib diisi.']);
    exit();
}

try {
    // 3. Cari entitas user berdasarkan username menggunakan PDO Prepared Statement asli
    $sql = "SELECT id_user, nama, username, password, role, status, failed_logins, login_lock_until FROM m_user WHERE username = :username LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    $current_time = date('Y-m-d H:i:s');

    // 4. PRE-CHECK: Deteksi proteksi brute force / Status akun terkunci
    if ($user && $user['login_lock_until'] !== null) {
        if (strtotime($user['login_lock_until']) > time()) {
            $selisih_menit = ceil((strtotime($user['login_lock_until']) - time()) / 60);
            log_warning('AUTH_LOCK', "Percobaan login pada akun yang sedang terblokir: $username");
            echo json_encode([
                'success' => false, 
                'message' => "Akun Anda diblokir sementara akibat salah password 5 kali. Silakan coba kembali dalam $selisih_menit menit."
            ]);
            exit();
        }
    }

    // 5. EVALUASI VALIDITAS USER DAN PASSWORD
    if (!$user) {
        // Username tidak terdaftar di sistem
        log_warning('AUTH_FAILED', "Percobaan login gagal, username tidak ditemukan: $username");
        echo json_encode(['success' => false, 'message' => 'Username atau password yang Anda masukkan salah.']);
        exit();
    }

    // Cek apakah status user dinonaktifkan oleh pimpinan/admin
    if ($user['status'] !== 'Aktif') {
        log_warning('AUTH_DISABLED', "Percobaan login pada akun nonaktif: $username");
        echo json_encode(['success' => false, 'message' => 'Akun Anda telah dinonaktifkan oleh administrator. Hubungi pimpinan Anda.']);
        exit();
    }

    // Jalankan pencocokan hash password tingkat enterprise
    if (password_verify($password, $user['password'])) {
        
        // --- LOGIN BERHASIL ---
        // Bersihkan data kegagalan login sebelumnya jika ada
        if ($user['failed_logins'] > 0 || $user['login_lock_until'] !== null) {
            $sql_reset = "UPDATE m_user SET failed_logins = 0, login_lock_until = NULL WHERE id_user = :id";
            $pdo->prepare($sql_reset)->execute(['id' => $user['id_user']]);
        }

        // Pemicuan regenerasi ID Sesi untuk menghindari Session Fixation Vulnerability
        session_regenerate_id(true);

        // Pengisian data spesifikasi session secara lengkap sesuai standardisasi parameter
        $_SESSION['user_id']     = $user['id_user'];
        $_SESSION['nama_user']   = $user['nama'];
        $_SESSION['username']    = $user['username'];
        $_SESSION['role']        = $user['role'];
        $_SESSION['status']      = $user['status'];
        $_SESSION['login_time']   = $current_time;
        $_SESSION['last_activity']= time();
        $_SESSION['ip_address']   = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $_SESSION['browser']      = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Browser';

        // Catat kesuksesan masuk ke sistem audit log harian
        log_success('AUTH_LOGIN', "User {$user['username']} ({$user['role']}) berhasil login ke dalam sistem.");

        // Deteksi tujuan redirect: apakah ada URL lama yang sempat tertahan akibat interupsi login
        $redirect_target = 'dashboard/index.php';
        if (isset($_SESSION['LAST_REQUESTED_URL']) && !empty($_SESSION['LAST_REQUESTED_URL'])) {
            // Hindari pengulangan looping menuju halaman login itu sendiri
            if (strpos($_SESSION['LAST_REQUESTED_URL'], 'login.php') === false) {
                $redirect_target = ltrim($_SESSION['LAST_REQUESTED_URL'], '/');
                unset($_SESSION['LAST_REQUESTED_URL']); // Hapus jejak pasca dibaca
            }
        }

        echo json_encode([
            'success' => true,
            'message' => 'Autentikasi berhasil! Mengalihkan halaman...',
            'target'  => $redirect_target
        ]);
        exit();

    } else {
        
        // --- LOGIN GAGAL (PASSWORD SALAH) ---
        $new_failed_count = $user['failed_logins'] + 1;
        $lock_until_str = 'NULL';
        $message_output = 'Username atau password yang Anda masukkan salah.';

        if ($new_failed_count >= 5) {
            // Batas maksimal salah password terpenuhi, kunci akun selama 10 menit secara presisi
            $lock_duration = date('Y-m-d H:i:s', time() + 600);
            $sql_lock = "UPDATE m_user SET failed_logins = :fc, login_lock_until = :lu WHERE id_user = :id";
            $pdo->prepare($sql_lock)->execute([
                'fc' => $new_failed_count,
                'lu' => $lock_duration,
                'id' => $user['id_user']
            ]);
            
            log_error('AUTH_BRUTEFORCE', "Akun $username diblokir sementara selama 10 menit akibat 5x salah input password.");
            $message_output = 'Akun Anda diblokir sementara selama 10 menit akibat 5 kali salah memasukkan password.';
        } else {
            // Naikkan counter kegagalan log di database
            $sql_counter = "UPDATE m_user SET failed_logins = :fc WHERE id_user = :id";
            $pdo->prepare($sql_counter)->execute([
                'fc' => $new_failed_count,
                'id' => $user['id_user']
            ]);
            log_warning('AUTH_WRONG_PASSWORD', "User $username gagal login. Salah password ke-$new_failed_count.");
        }

        echo json_encode(['success' => false, 'message' => $message_output]);
        exit();
    }

} catch (PDOException $e) {
    if (function_exists('log_error')) {
        log_error('DATABASE_AUTH', 'Kegagalan query basis data sistem login: ' . $e->getMessage());
    }
    echo json_encode(['success' => false, 'message' => 'Terjadi gangguan sistem internal pada database server.']);
    exit();
}