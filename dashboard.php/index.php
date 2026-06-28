<?php
/**
 * File: dashboard/index.php
 * Deskripsi: Berkas cetak biru (template layout utama) halaman dashboard internal aplikasi.
 */

// Muat berkas inisialisasi system core secara aman
require_once __DIR__ . '/../config/autoload.php';

// Proteksi Gerbang Sesi: Pengguna wajib melakukan otorisasi login sebelum membuka halaman ini
middleware_authenticate_user();

// Panggil header komponen reusable
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div id="page-content-wrapper">
    
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid px-4 py-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-dark m-0">Ringkasan Dashboard Analitik</h4>
                <small class="text-muted">Selamat bekerja kembali, <?= xss_escape($_SESSION['nama_user']) ?>.</small>
            </div>
            <div>
                <span class="badge bg-white text-dark border px-3 py-2 fw-semibold shadow-sm"><i class="bi bi-clock text-orange me-1"></i> <?= date('d M Y') ?></span>
            </div>
        </div>

        <?php if (has_flash_message('login_success')): $flash = get_flash_message('login_success'); ?>
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    Swal.fire({ icon: 'success', title: 'Akses Diberikan', text: '<?= $flash['message'] ?>', timer: 2000, showConfirmButton: false });
                });
            </script>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted small text-uppercase">Total Armada</h6>
                            <h3 class="fw-bold text-dark m-0">12 Unit</h3>
                        </div>
                        <div class="p-3 rounded-circle bg-orange bg-opacity-10 text-orange fs-4"><i class="bi bi-truck"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted small text-uppercase">Unit Tersedia</h6>
                            <h3 class="fw-bold text-success m-0">8 Unit</h3>
                        </div>
                        <div class="p-3 rounded-circle bg-success bg-opacity-10 text-success fs-4"><i class="bi bi-check-circle"></i></div>
                    </div>
                </div>
            </div>
        </div>
        </div>
</div>

<?php 
// Panggil penutup kerangka layout halaman dashboard
require_once __DIR__ . '/../includes/footer.php'; 
?>