<?php
/**
 * File: login.php
 * Deskripsi: Halaman antarmuka masuk akun (Sign In Form) - Session Ready & CSRF Terintegrasi.
 */
require_once __DIR__ . '/config/autoload.php';

// Proteksi Gerbang Sesi: Jika terdeteksi akun sudah aktif login, otomatis lempar balik ke dashboard
middleware_guest_only();

// Inisialisasi token keamanan form submit
$token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Aplikasi - PREMIUM PICKUP RENTAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f5f7; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card-login { width: 400px; border-radius: 15px; border: none; }
        .btn-orange { background-color: #ff7a00; color: white; border: none; }
        .btn-orange:hover { background-color: #e06c00; color: white; }
    </style>
</head>
<body>

    <div class="card card-login shadow-sm p-4 bg-white">
        <div class="text-center mb-4">
            <h4 class="fw-bold text-dark mb-1">PREMIUM PICKUP RENTAL</h4>
            <small class="text-muted">Gunakan hak akses resmi untuk masuk ke panel kerja</small>
        </div>

        <?php if (has_flash_message('auth_error')): $flash = get_flash_message('auth_error'); ?>
            <div class="alert alert-warning border-0 small py-2 rounded-2" role="alert">
                <?= $flash['message'] ?>
            </div>
        <?php endif; ?>

        <form action="auth/proses_login_dummy.php" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?= $token ?>">

            <div class="mb-3">
                <label for="username" class="form-label small fw-bold">Username Akun</label>
                <input type="text" name="username" id="username" class="form-control" placeholder="Masukkan username" required autocomplete="off">
                <div class="invalid-feedback small">Kolom username tidak boleh dikosongkan.</div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label small fw-bold">Kata Sandi (Password)</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" required>
                <div class="invalid-feedback small">Kolom password tidak boleh dikosongkan.</div>
            </div>

            <button type="submit" class="btn btn-orange w-100 py-2 fw-bold rounded-2 text-uppercase mb-2 shadow-sm">Masuk Sistem</button>
            <a href="index.php" class="btn btn-light w-100 border py-2 text-muted small rounded-2">Kembali ke Landing Page</a>
        </form>
    </div>

    <script>
        // Validasi Sisi Klien Instan Tanpa Menggunakan jQuery
        document.addEventListener('DOMContentLoaded', function () {
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