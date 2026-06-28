<?php
/**
 * File: config/constants.php
 * Deskripsi: Definisi nilai konstanta mutlak sistem untuk standarisasi status data.
 */

// Definisi Hak Akses Pengguna (RBAC Roles)
define('ROLE_ADMIN', 'Administrator');
define('ROLE_OWNER', 'Owner');
define('ROLE_KASIR', 'Kasir');
define('ROLE_STAFF', 'Staff');

// Status Operasional Unit Mobil Pickup
define('STATUS_MOBIL_TERSEDIA', 'Tersedia');
define('STATUS_MOBIL_DISEWA', 'Disewa');
define('STATUS_MOBIL_SERVIS', 'Servis');
define('STATUS_MOBIL_BOOKING', 'Booking');

// Status Siklus Reservasi Booking
define('STATUS_BOOKING_PENDING', 'Pending');
define('STATUS_BOOKING_DISETUJUI', 'Disetujui');
define('STATUS_BOOKING_DIAMBIL', 'Diambil');
define('STATUS_BOOKING_BATAL', 'Dibatalkan');

// Status Siklus Transaksi Penyewaan Aktif
define('STATUS_SEWA_BERJALAN', 'Berjalan');
define('STATUS_SEWA_SELESAI', 'Selesai');
define('STATUS_SEWA_TERLAMBAT', 'Terlambat');

// Status Penyelesaian Pengembalian Unit
define('STATUS_KEMBALI_LUNAS', 'Lunas');
define('STATUS_KEMBALI_BELUM_LUNAS', 'Belum Lunas');

// Status Akun Pengguna / User
define('STATUS_USER_AKTIF', 'Aktif');
define('STATUS_USER_NONAKTIF', 'Nonaktif');

// Status Kepegawaian Internal
define('STATUS_PEGAWAI_AKTIF', 'Aktif');
define('STATUS_PEGAWAI_RESIGN', 'Resign');

// Status Ketersediaan Sopir
define('STATUS_SOPIR_TERSEDIA', 'Tersedia');
define('STATUS_SOPIR_BERTUGAS', 'Bertugas');
define('STATUS_SOPIR_NONAKTIF', 'Nonaktif');