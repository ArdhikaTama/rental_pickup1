<?php
/**
 * File: login.php
 * Deskripsi: Halaman antarmuka masuk akun (Sign In Form) - Non MVC Modular Prosedural.
 * Fitur: Session Security Ready, Proteksi CSRF Terintegrasi, dan AJAX Fetch API Handler.
 */

// Memuat berkas inisialisasi system core secara aman
require_once __DIR__ . '/config/autoload.php';

// Proteksi Gerbang Sesi: Jika terdeteksi akun sudah aktif login, otomatis lempar balik ke dashboard
if (function_exists('middleware_guest_only')) {
    middleware_guest_only();
} else {
    if (isset($_SESSION['user_id'])) {
        header("Location: dashboard/index.php");
        exit();
    }
}

// Inisialisasi token keamanan form submit untuk menangkal Cross-Site Request Forgery
$token = function_exists('generate_csrf_token') ? generate_csrf_token() : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Masuk Aplikasi - PREMIUM PICKUP RENTAL</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        body { 
            background-color: #f4f5f7; 
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        .card-login { 
            width: 400px; 
            border-radius: 15px; 
            border: none; 
        }
        .btn-orange { 
            background-color: #ff7a00; 
            color: white; 
            border: none; 
            transition: all 0.2s ease;
        }
        .btn-orange:hover { 
            background-color: #e06c00; 
            color: white; 
        }
    </style>
</head>
<body>

    <div class="card card-login shadow-sm p-4 bg-white">
        <div class="text-center mb-4">
            <h4 class="fw-bold text-dark mb-1">PREMIUM PICKUP RENTAL</h4>
            <small class="text-muted">Gunakan hak akses resmi untuk masuk ke panel kerja</small>
        </div>

        <?php if (function_exists('has_flash_message') && has_flash_message('auth_error')): $flash = get_flash_message('auth_error'); ?>
            <div class="alert alert-warning border-0 small py-2 rounded-2 mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= $flash['message'] ?>
            </div>
        <?php endif; ?>

        <?php 
        // Penanganan parameter get HTTP konvensional fallback
        $error_msg = $_GET['error'] ?? '';
        if ($error_msg === 'unauthenticated') {
            echo '<div class="alert alert-danger border-0 small py-2 rounded-2 mb-3"><i class="bi bi-shield-lock-fill me-1"></i> Sesi Anda belum teridentifikasi. Silakan login.</div>';
        } elseif ($error_msg === 'session_expired') {
            echo '<div class="alert alert-warning border-0 small py-2 rounded-2 mb-3"><i class="bi bi-clock-history me-1"></i> Sesi Anda telah berakhir (Idle Timeout). Silakan login kembali.</div>';
        } elseif ($error_msg === 'security_violation') {
            echo '<div class="alert alert-danger border-0 small py-2 rounded-2 mb-3"><i class="bi bi-exclamation-octagon-fill me-1"></i> Anomali sidik jari perangkat dideteksi. Sesi ditutup demi keamanan.</div>';
        }
        ?>

        <form id="form-login-main" action="auth/login_process.php" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?= $token ?>">

            <div class="mb-3">
                <label for="username" class="form-label small fw-bold">Username Akun</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" id="username" class="form-control border-start-0 ps-1" placeholder="Masukkan username" required autocomplete="off">
                    <div class="invalid-feedback small">Kolom username tidak boleh dikosongkan.</div>
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label small fw-bold">Kata Sandi (Password)</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="password" class="form-control border-start-0 ps-1" placeholder="Masukkan password" required>
                    <div class="invalid-feedback small">Kolom password tidak boleh dikosongkan.</div>
                </div>
            </div>

            <button type="submit" class="btn btn-orange w-100 py-2 fw-bold rounded-2 text-uppercase mb-2 shadow-sm">Masuk Sistem</button>
            <a href="index.php" class="btn btn-light w-100 border py-2 text-muted small rounded-2">Kembali ke Landing Page</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const formLogin = document.getElementById('form-login-main');
            
            if (formLogin) {
                formLogin.addEventListener('submit', function (e) {
                    // 1. Biarkan Bootstrap mengecek validasi HTML5 bawaan form (required)
                    if (!this.checkValidity()) {
                        return; 
                    }
                    
                    // 2. Batalkan pengiriman form konvensional agar tidak memicu reload halaman
                    e.preventDefault();

                    const btn = this.querySelector('button[type="submit"]');
                    const originalText = btn.innerHTML;
                    
                    // Mengubah status tombol menjadi animasi loading screen mini
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memverifikasi...';

                    // 3. Kumpulkan payload input data form
                    const formData = new FormData(this);
                    
                    // 4. Eksekusi pengiriman via Asynchronous Fetch API
                    fetch(this.getAttribute('action'), {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Jika username & password lolos kriteria enkripsi database
                            Swal.fire({
                                icon: 'success',
                                title: 'Autentikasi Berhasil',
                                text: data.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                // Alihkan halaman ke modul kerja dashboard
                                window.location.href = data.target;
                            });
                        } else {
                            // Jika gagal (salah password, akun terblokir sementara, atau dinonaktifkan)
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Masuk',
                                text: data.message,
                                confirmButtonColor: '#ff7a00'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        Swal.fire({
                            icon: 'error',
                            title: 'System Error',
                            text: 'Gagal melakukan pertukaran data otorisasi dengan server.',
                            confirmButtonColor: '#ff7a00'
                        });
                    });
                }, false);
            }

            // Validasi Bootstrap styling triggers
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        });
    </script>
</body>
</html>