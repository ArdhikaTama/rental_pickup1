<?php
/**
 * File: config/file_helper.php
 * Deskripsi: Modul asisten manipulasi, pemindahan, pembuatan direktori, dan penghapusan file fisik di server.
 */

function file_check_directory($target_sub_folder) {
    $full_path = rtrim(UPLOAD_DIR, '/') . '/' . trim($target_sub_folder, '/');
    if (!is_dir($full_path)) {
        mkdir($full_path, 0755, true);
    }
    return $full_path . '/';
}

function file_upload_image($file_array, $sub_folder, $prefix = 'IMG') {
    if (!isset($file_array['tmp_name']) || $file_array['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $destination_dir = file_check_directory($sub_folder);
    $extension = strtolower(pathinfo($file_array['name'], PATHINFO_EXTENSION));
    
    // Penamaan ulang file secara acak menggunakan gabungan enkripsi timestamp unik demi menghindari duplikasi nama file
    $new_filename = $prefix . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $full_target_path = $destination_dir . $new_filename;
    
    if (move_uploaded_file($file_array['tmp_name'], $full_target_path)) {
        return $new_filename; // Kembalikan string nama file baru untuk disimpan ke dalam kolom tabel MySQL
    }
    
    return false;
}

function file_delete_media($sub_folder, $filename) {
    if (empty($filename)) return false;
    $target_file = rtrim(UPLOAD_DIR, '/') . '/' . trim($sub_folder, '/') . '/' . $filename;
    if (file_exists($target_file) && is_file($target_file)) {
        return unlink($target_file);
    }
    return false;
}