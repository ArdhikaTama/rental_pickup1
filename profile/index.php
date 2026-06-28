<?php
/**
 * File: profile/index.php
 * Deskripsi: Manajemen data mandiri profil petugas dan modifikasi kata sandi.
 */

require_once dirname(__DIR__) . '/config/autoload.php';

// Kunci gerbang otorisasi halaman profil
middleware_can_profile();

// Penanganan Operasional Update Data Password & Foto via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi token pengaman
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        response_error_json('Token keamanan kedaluwarsa. Silakan muat ulang halaman.');
    }

    $action = $_POST['action'] ?? '';

    // --- SUB-AKSI 1: MODIFIKASI KATA SANDI ---
    if ($action === 'change_password') {
        $old_pass = $_POST['old_password'] ?? '';
        $new_pass = $_POST['new_password'] ?? '';
        $conf_pass = $_POST['confirm_password'] ?? '';

        if (empty($old_pass) || empty($new_pass) || empty($conf_pass)) {
            response_error_json('Seluruh kolom data password wajib diisi.');
        }

        if ($new_pass !== $conf_pass) {
            response_error_json('Konfirmasi password baru tidak cocok.');
        }

        // Jalankan uji kelayakan password policy hulu
        if (!validate_password_policy($new_pass)) {
            response_error_json('Password baru tidak memenuhi syarat kebijakan! Wajib minimal 8 karakter, mengandung huruf besar, huruf kecil, angka, dan simbol khusus.');
        }

        // Ambil password lama dari DB untuk divalidasi
        $user_db = db_select_one("SELECT password FROM m_user WHERE id_user = ?", [$_SESSION['user_id']]);
        
        if (!password_verify($old_pass, $user_db['password'])) {
            log_warning('PROFILE_SEC', "User {$_SESSION['username']} gagal mengubah password: Password lama keliru.");
            response_error_json('Kata sandi lama yang Anda masukkan salah.');
        }

        // Enkripsi password baru dengan algoritma BCRYPT bertenaga tinggi
        $final_hashed_password = password_hash($new_pass, PASSWORD_BCRYPT, ['cost' => 10]);
        
        db_update('m_user', ['password' => $final_hashed_password], 'id_user = ?', [$_SESSION['user_id']]);
        
        log_success('PROFILE_SEC', "User {$_SESSION['username']} berhasil memperbarui kata sandi akunnya.");
        response_success_json('Kata sandi akun Anda berhasil diperbarui dengan aman.');
    }

    // --- SUB-AKSI 2: UNGGAH FOTO PROFIL ---
    if ($action === 'upload_photo') {
        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            response_error_json('Berkas gambar foto gagal dibaca.');
        }

        $file = $_FILES['photo'];

        if (!validate_image_extension($file)) {
            response_error_json('Format file dilarang! Wajib berupa berkas gambar berekstensi .jpg, .jpeg, atau .png');
        }

        if (!validate_file_size($file, 2)) { // Batas maksimal 2 Megabyte
            response_error_json('Ukuran gambar terlalu besar! Maksimal toleransi file adalah 2 Megabyte.');
        }

        // Eksekusi pemindahan file fisik ke server via helper Phase 3
        $uploaded_name = file_upload_image($file, 'pelanggan', 'AVATAR'); // Disimpan di sub-folder assets/upload/pelanggan
        
        if ($uploaded_name) {
            // Ambil foto profil lama untuk dihapus dari disk server biar hemat storage
            $old_photo = db_select_one("SELECT foto_profil FROM m_user WHERE id_user = ?", [$_SESSION['user_id']]);
            if ($old_photo['foto_profil'] !== 'default_user.png') {
                file_delete_media('pelanggan', $old_photo['foto_profil']);
            }

            // Update nama file baru ke database
            db_update('m_user', ['foto_profil' => $uploaded_name], 'id_user = ?', [$_SESSION['user_id']]);
            
            log_success('PROFILE_IMG', "User {$_SESSION['username']} mengganti foto profil.");
            response_success_json('Foto profil Anda berhasil diunggah.', ['filename' => $uploaded_name]);
        } else {
            response_error_json('Gagal memindahkan file ke direktori server.');
        }
    }
    exit();
}

// Ambil info detail instan user aktif untuk kebutuhan rendering View
$user_info = db_select_one("SELECT nama, username, role, status, foto_profil FROM m_user WHERE id_user = ?", [$_SESSION['user_id']]);

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/sidebar.php';
?>

<div id="page-content-wrapper">
    <?php require_once dirname(__DIR__) . '/includes/navbar.php'; ?>

    <div class="container-fluid px-4 py-4">
        <div class="mb-4">
            <h4 class="fw-bold text-dark m-0">Manajemen Profil Akun</h4>
            <small class="text-muted">Kelola data rahasia kata sandi dan identitas digital Anda secara mandiri.</small>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm text-center p-4 bg-white rounded-3">
                    <div class="position-relative d-inline-block mx-auto mb-3">
                        <img src="<?= BASE_URL . 'assets/upload/pelanggan/' . ($user_info['foto_profil'] ?? 'default_user.png') ?>" 
                             id="avatar-preview" class="rounded-circle border p-1" style="width: 130px; height: 130px; object-fit: cover;" alt="Avatar">
                    </div>
                    <h5 class="fw-bold text-dark m-0"><?= xss_escape($user_info['nama']) ?></h5>
                    <p class="badge bg-orange bg-opacity-10 text-orange px-3 py-2 mt-2 rounded-2 small"><?= $user_info['role'] ?></p>
                    <hr class="text-muted my-3">
                    <div class="text-start small text-secondary">
                        <div class="mb-2"><strong>Username:</strong> <span class="text-dark"><?= xss_escape($user_info['username']) ?></span></div>
                        <div class="mb-2"><strong>Waktu Masuk:</strong> <span class="text-dark"><?= $_SESSION['login_time'] ?></span></div>
                        <div><strong>IP Jaringan:</strong> <span class="text-dark"><?= $_SESSION['ip_address'] ?></span></div>
                    </div>
                    <div class="mt-4">
                        <input type="file" id="input-photo-ajax" class="d-none" accept="image/*">
                        <button class="btn btn-outline-orange btn-sm w-100 fw-bold" onclick="document.getElementById('input-photo-ajax').click();">
                            <i class="bi bi-camera me-1"></i> Unggah Foto Baru
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
                    <h6 class="fw-bold text-dark mb-3 text-uppercase tracking-wider"><i class="bi bi-key-fill text-orange me-1"></i> Ganti Kata Sandi</h6>
                    
                    <form id="form-change-password-ajax" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="action" value="change_password">

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Kata Sandi Lama *</label>
                            <input type="password" name="old_password" class="form-control" placeholder="Masukkan password saat ini" required>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Kata Sandi Baru *</label>
                                <input type="password" name="new_password" class="form-control" placeholder="Minimal 8 karakter kompleks" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Konfirmasi Kata Sandi Baru *</label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password baru" required>
                            </div>
                        </div>
                        <div class="alert alert-light border small text-muted p-2 rounded-2 mb-4">
                            <i class="bi bi-info-circle-fill text-orange me-1"></i> <strong>Kriteria Keamanan:</strong> Kombinasi wajib mengandung minimal 1 huruf besar, 1 huruf kecil, angka murni, dan simbol tanda baca khusus.
                        </div>
                        <div class="text-end">
                            <button type="submit" id="btn-submit-pass" class="btn btn-orange px-4 fw-bold">Perbarui Kata Sandi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    
    // --- 1. HANDLING AJAX UPDATE PASSWORD ---
    const formPass = document.getElementById('form-change-password-ajax');
    if(formPass) {
        formPass.addEventListener('submit', function(e) {
            if (!this.checkValidity()) return; // Serahkan validasi ke bootstrap engine di footer
            e.preventDefault();

            const btn = document.getElementById('btn-submit-pass');
            btn.disabled = true;
            btn.innerText = "Memproses...";

            const formData = new FormData(this);
            fetch('index.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerText = "Perbarui Kata Sandi";
                if(data.success) {
                    Swal.fire('Berhasil!', data.message, 'success');
                    formPass.reset();
                    formPass.classList.remove('was-validated');
                } else {
                    Swal.fire('Gagal!', data.message, 'error');
                }
            }).catch(() => {
                btn.disabled = false;
                btn.innerText = "Perbarui Kata Sandi";
                Swal.fire('System Error', 'Gagal bertukar data dengan server.', 'error');
            });
        });
    }

    // --- 2. HANDLING AJAX UPLOAD FOTO PROFIL ---
    const inputPhoto = document.getElementById('input-photo-ajax');
    if(inputPhoto) {
        inputPhoto.addEventListener('change', function() {
            if(this.files.length === 0) return;
            
            const formData = new FormData();
            formData.append('photo', this.files[0]);
            formData.append('action', 'upload_photo');
            formData.append('csrf_token', '<?= $csrf_token ?>');

            Swal.fire({ title: 'Mengunggah...', text: 'Mohon tunggu berkas sedang diproses.', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

            fetch('index.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                Swal.close();
                if(data.success) {
                    Swal.fire('Sukses!', data.message, 'success');
                    // Ganti visual gambar secara realtime tanpa reload halaman
                    document.getElementById('avatar-preview').src = '<?= BASE_URL ?>assets/upload/pelanggan/' + data.data.filename;
                } else {
                    Swal.fire('Gagal!', data.message, 'error');
                }
            }).catch(() => {
                Swal.close();
                Swal.fire('System Error', 'Gagal mengunggah foto profil.', 'error');
            });
        });
    }
});
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>