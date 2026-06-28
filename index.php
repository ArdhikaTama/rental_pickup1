<?php
// index.php (File utama Root Aplikasi)
error_reporting(E_ALL ^ E_NOTICE);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_ready = false;
$mobiles = [];

// Memuat konfigurasi fungsional murni prosedural
if (file_exists('config/koneksi.php')) {
    try {
        require_once 'config/koneksi.php';
        $db_ready = true;
        
        // Mengambil data mobil & tarif langsung secara berkala
        $query = "SELECT m.*, t.harga_harian, t.harga_mingguan, t.harga_bulanan 
                  FROM m_mobil m 
                  LEFT JOIN m_tarif t ON m.id_mobil = t.id_mobil 
                  WHERE m.status = 'Tersedia'";
        $stmt_mob = $pdo->prepare($query);
        $stmt_mob->execute();
        $mobiles = $stmt_mob->fetchAll();
    } catch (Exception $e) {
        $db_ready = false;
    }
}

// Fallback Array Data jika DB kosong agar visual Car Category tetap tampil sempurna
if (empty($mobiles)) {
    $mobiles = [
        ['name' => 'L300', 'harian' => '350.000', 'mingguan' => '1.800.000', 'bulanan' => '5.300.000', 'file' => 'assets/img/l300-removebg-preview.png'],
        ['name' => 'Carry', 'harian' => '300.000', 'mingguan' => '1.600.000', 'bulanan' => '5.000.000', 'file' => 'assets/img/l300_box-removebg-preview.png'],
        ['name' => 'L300', 'harian' => '300.000', 'mingguan' => '1.600.000', 'bulanan' => '5.000.000', 'file' => 'assets/img/l300-removebg-preview.png'],
        ['name' => 'GranMax', 'harian' => '450.000', 'mingguan' => '1.850.000', 'bulanan' => '5.300.000', 'file' => 'assets/img/granmax-removebg-preview.png'],
        ['name' => 'Viar', 'harian' => '250.000', 'mingguan' => '1.500.000', 'bulanan' => '4.000.000', 'file' => 'assets/img/viar-removebg-preview.png'],
        ['name' => 'Carry box', 'harian' => '400.000', 'mingguan' => '1.800.000', 'bulanan' => '5.300.000', 'file' => 'assets/img/pickup_putih-removebg-preview.png']
    ];
}

// RENDER KOMPONEN HALAMAN DEPAN
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<section class="hero-section position-relative overflow-hidden py-5 d-flex align-items-center" style="background-color: #cdcdcd; min-height: 550px;">
    <div class="position-absolute end-0 top-50 translate-middle-y bg-orange d-none d-lg-block" style="width: 45%; height: 260px; border-top-left-radius: 20px; border-bottom-left-radius: 20px; z-index: 1;"></div>

    <div class="container position-relative" style="z-index: 2;">
        <div class="row align-items-center g-4">
            <div class="col-lg-6 text-start">
                <h1 class="display-4 fw-black text-dark mb-1 text-uppercase m-0 lh-1" style="font-family: 'Arial Black', Gadget, sans-serif; font-weight: 900; letter-spacing: -1px;">
                    PREMIUM <br>
                    CAR <span class="text-orange">RENTAL</span> <br>
                    IN JAKARTA
                </h1>
                <p class="text-dark fw-bold my-4 opacity-90 fs-6" style="max-width: 480px; text-align: justify; line-height: 1.4;">
                    "Layanan rental mobil pickup terpercaya dengan armada berkualitas, harga kompetitif, dan sistem pemesanan yang efisien untuk mendukung kebutuhan logistik Anda."
                </p>
                <div class="mt-2">
                    <a href="https://wa.me/6281234567890" target="_blank" class="btn btn-orange px-4 py-2 fw-bold rounded-1 text-white text-uppercase btn-sm shadow-sm" style="font-size: 0.85rem;">
                        Hubungi kami : 0812-3456-7890
                    </a>
                </div>
            </div>
            
            <div class="col-lg-6 text-center position-relative">
                <img src="assets/img/l300_box-removebg-preview.png" alt="Premium Pickup Fleet" class="img-fluid position-relative floating-img" style="max-height: 340px; object-fit: contain; z-index: 3;">
            </div>
        </div>
    </div>
</section>

<section id="unit" class="py-5" style="background-color: #e6e6e6;">
    <div class="container py-2">
        <h2 class="fw-black text-dark mb-4 text-uppercase text-start" style="font-family: 'Arial Black', Gadget, sans-serif; font-weight: 900; letter-spacing: -1px;">
            CAR CATEGORY
        </h2>
        
        <div class="row g-4">
            <?php foreach($mobiles as $c): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 p-3 rounded-4 bg-white shadow-sm card-custom">
                    <div class="text-center py-3 bg-white border rounded-4 d-flex align-items-center justify-content-center" style="border-color: #ffbc80 !important; height: 180px;">
                        <?php 
                            $img_src = isset($c['foto']) ? 'assets/upload/mobil/'.$c['foto'] : $c['file'];
                            $title_unit = isset($c['merk']) ? $c['merk'].' '.$c['tipe'] : $c['name'];
                        ?>
                        <img src="<?= $img_src ?>" alt="<?= $title_unit ?>" class="img-fluid px-2 object-fit-contain" style="max-height: 100%;">
                    </div>
                    
                    <div class="card-body px-0 pt-3 pb-0">
                        <h4 class="text-center fw-bold bg-orange text-white py-2 rounded-2 text-uppercase mb-3 fs-5 m-0" style="letter-spacing: 0.5px;">
                            <?= $title_unit ?>
                        </h4>
                        
                        <div class="price-info small mb-3 text-dark fw-bold">
                            <div class="d-flex justify-content-between border-bottom py-1">
                                <span>Sewa Harian/24 jam :</span>
                                <span class="text-orange"><?= isset($c['harga_harian']) ? 'Rp.' . number_format($c['harga_harian'], 0, ',', '.') : 'Rp.'.$c['harian'] ?>,-</span>
                            </div>
                            <div class="d-flex justify-content-between border-bottom py-1">
                                <span>Sewa Mingguan :</span>
                                <span class="text-orange"><?= isset($c['harga_mingguan']) ? 'Rp.' . number_format($c['harga_mingguan'], 0, ',', '.') : 'Rp.'.$c['mingguan'] ?>,-</span>
                            </div>
                            <div class="d-flex justify-content-between py-1">
                                <span>Sewa Bulanan :</span>
                                <span class="text-orange"><?= isset($c['harga_bulanan']) ? 'Rp.' . number_format($c['harga_bulanan'], 0, ',', '.') : 'Rp.'.$c['bulanan'] ?>,-</span>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center px-2 bg-light py-2 rounded-2 border" style="font-size: 0.75rem;">
                            <span class="fw-bold text-muted"><i class="bi bi-check-square-fill text-success me-1"></i>Mesin prima</span>
                            <span class="fw-bold text-muted"><i class="bi bi-check-square-fill text-success me-1"></i>Bisa nego</span>
                            <span class="fw-bold text-muted"><i class="bi bi-check-square-fill text-success me-1"></i>Terpercaya</span>
                        </div>

                        <div class="mt-3">
                            <a href="login.php?action=booking&id=<?= $c['id_mobil'] ?? '' ?>" class="btn btn-orange btn-sm w-100 py-2 text-white fw-bold text-uppercase">Booking Sekarang</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="about" class="py-5" style="background-color: #cccccc;">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <h2 class="fw-black text-uppercase mb-4 text-dark" style="font-family: 'Arial Black', Gadget, sans-serif; font-weight: 900; letter-spacing: -1px;">ABOUT US</h2>
                <p class="text-dark lh-base mb-4" style="text-align: justify; font-size: 0.95rem; font-weight: 500;">Kami hadir sebagai partner terpercaya untuk kebutuhan angkut Anda, memberikan layanan rental mobil pick up yang praktis, cepat, dan tanpa ribet. Dengan armada yang selalu siap pakai dan terawat, kami siap mendukung berbagai kebutuhan mulai dari pindahan hingga operasional bisnis. Didukung pelayanan yang responsif, proses booking yang mudah, dan harga yang bersahabat, kami berkomitmen memberikan pengalaman terbaik agar setiap kebutuhan transportasi Anda jadi lebih ringan dan efisien.</p>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-orange text-white px-3 py-2 rounded-2 fs-6 fw-bold"><i class="bi bi-shield-check me-1"></i> 100% Quality Guaranteed</span>
                    <a href="#unit" class="btn btn-light bg-white border px-4 py-2 text-dark font-monospace btn-sm rounded-2 shadow-sm text-uppercase fw-bold">Lihat unit</a>
                </div>
            </div>
            <div class="col-lg-5 text-center">
                <img src="assets/img/l300_box-removebg-preview.png" alt="About Pickup" class="img-fluid" style="max-height: 260px; object-fit: contain;">
            </div>
        </div>
    </div>
</section>

<section class="py-5" style="background-color: #e6e6e6;">
    <div class="container py-4 text-center">
        <h4 class="fw-bold text-dark mb-2" style="font-family: 'Arial', sans-serif; font-weight: 800;">Kami Menyediakan Layanan Sewa Mobil Terbaik di Jakarta</h4>
        <p class="text-muted small mb-5">Kami hadir dengan bangga sebagai mitra perjalanan Anda yang terpercaya.</p>
        
        <div class="row g-4 mx-auto" style="max-width: 1000px;">
            <div class="col-md-4">
                <div class="card p-4 card-feature h-100 border-0 shadow-sm">
                    <div class="text-orange fs-1 mb-3"><i class="bi bi-cash-coin"></i></div>
                    <h6 class="fw-bold text-dark mb-2">Harga sangat kompetitif</h6>
                    <p class="text-muted small mb-0 lh-base" style="text-align: justify;">Harga yang kami berikan sangat kompetitif murah, namun tidak mengurangi sedikitpun kualitas pelayanan yang kami berikan.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 card-feature h-100 border-0 shadow-sm">
                    <div class="text-orange fs-1 mb-3"><i class="bi bi-telephone-inbound"></i></div>
                    <h6 class="fw-bold text-dark mb-2">Layanan Fleksibilitas</h6>
                    <p class="text-muted small mb-0 lh-base" style="text-align: justify;">Kami siap mendukung berbagai keperluan perjalanan anda. Fleksibilitas layanan dari kami yang sesuai dengan kebutuhan anda.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 card-feature h-100 border-0 shadow-sm">
                    <div class="text-orange fs-1 mb-3"><i class="bi bi-truck-flatbed"></i></div>
                    <h6 class="fw-bold text-dark mb-2">Armada mobil berkualitas</h6>
                    <p class="text-muted small mb-0 lh-base" style="text-align: justify;">Dengan perawatan mobil secara berkala dan kebersihan yang terjaga, kami menjamin kenyamanan dan keamanan perjalanan anda.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="contact" class="py-5 text-dark" style="background: linear-gradient(to bottom, #e6e6e6 0%, #ffffff 40%, #999999 100%);">
    <div class="container py-4">
        <div class="row g-5">
            <div class="col-md-6">
                <h5 class="fw-bold mb-4 text-uppercase" style="font-family: 'Arial Black', sans-serif; font-weight: 900;">Hubungi kami</h5>
                <ul class="list-unstyled d-flex flex-column gap-3 small fw-bold">
                    <li><i class="bi bi-envelope-fill text-dark me-2"></i> support@rentalpickupjkt.com</li>
                    <li><i class="bi bi-telephone-fill text-dark me-2"></i> Telepon: 0812-3456-7890</li>
                    <li><i class="bi bi-globe text-dark me-2"></i> www.rentalmobilpickupjkt.com</li>
                    <li class="d-flex align-items-start">
                        <i class="bi bi-geo-alt-fill text-dark me-2 mt-1"></i> 
                        <span>jl.mangga dua Rt.1/Rw.1, kec.Grogol selatan,<br>kota Jakarta selatan, Daerah Khusus<br>Ibukota Jakarta 12220</span>
                    </li>
                </ul>
            </div>
            <div class="col-md-6">
                <h5 class="fw-bold mb-4 text-uppercase" style="font-family: 'Arial Black', sans-serif; font-weight: 900;">Media sosial</h5>
                <ul class="list-unstyled d-flex flex-column gap-3 small fw-bold">
                    <li><a href="#" class="text-decoration-none text-dark"><i class="bi bi-facebook me-2 text-primary"></i> @pickupjakarta</a></li>
                    <li><a href="#" class="text-decoration-none text-dark"><i class="bi bi-instagram me-2 text-danger"></i> @rental_pickup_jakarta</a></li>
                </ul>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>