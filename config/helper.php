<?php
// config/helper.php

function format_rupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

function generate_trans_number($pdo, $prefix, $table, $column) {
    // Format: TR-PICKUP-202606-0001
    $yearMonth = date('Ym');
    $searchPattern = $prefix . "-" . $yearMonth . "-%";
    
    $stmt = $pdo->prepare("SELECT $column FROM $table WHERE $column LIKE ? ORDER BY $column DESC LIMIT 1");
    $stmt->execute([$searchPattern]);
    $lastRecord = $stmt->fetchColumn();

    if ($lastRecord) {
        $lastNumber = (int)substr($lastRecord, -4);
        $nextNumber = $lastNumber + 1;
    } else {
        $nextNumber = 1;
    }

    return $prefix . "-" . $yearMonth . "-" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
}