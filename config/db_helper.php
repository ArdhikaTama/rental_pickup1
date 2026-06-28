<?php
/**
 * File: config/db_helper.php
 * Deskripsi: Wrapper modular prosedural untuk mempermudah eksekusi query SQL via PDO Prepared Statements.
 */

function db_query($sql, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        log_error('DATABASE', 'Query Execution Failed: ' . $e->getMessage(), ['sql' => $sql, 'params' => $params]);
        throw new PDOException($e->getMessage(), (int)$e->getCode());
    }
}

function db_select($sql, $params = []) {
    $stmt = db_query($sql, $params);
    return $stmt->fetchAll();
}

function db_select_one($sql, $params = []) {
    $stmt = db_query($sql, $params);
    return $stmt->fetch();
}

function db_insert($table, $data) {
    $fields = array_keys($data);
    $placeholders = array_map(function($field) { return ':' . $field; }, $fields);
    
    $sql = "INSERT INTO " . $table . " (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
    db_query($sql, $data);
    
    global $pdo;
    return $pdo->lastInsertId();
}

function db_update($table, $data, $where_clause, $where_params = []) {
    $set_segments = [];
    foreach ($data as $field => $value) {
        $set_segments[] = $field . ' = :' . $field;
    }
    
    $sql = "UPDATE " . $table . " SET " . implode(', ', $set_segments) . " WHERE " . $where_clause;
    
    // Gabungkan parameter data update dengan parameter kondisi WHERE secara aman
    $combined_params = array_merge($data, $where_params);
    $stmt = db_query($sql, $combined_params);
    return $stmt->rowCount();
}

function db_delete($table, $where_clause, $params = []) {
    $sql = "DELETE FROM " . $table . " WHERE " . $where_clause;
    $stmt = db_query($sql, $params);
    return $stmt->rowCount();
}

function db_count($table, $where_clause = '', $params = []) {
    $sql = "SELECT COUNT(*) FROM " . $table;
    if (!empty($where_clause)) {
        $sql .= " WHERE " . $where_clause;
    }
    $stmt = db_query($sql, $params);
    return (int)$stmt->fetchColumn();
}

function db_exists($table, $where_clause, $params = []) {
    return db_count($table, $where_clause, $params) > 0;
}

// Manajemen Ruang Lingkup Transaksi Database (Atomic Operations)
function db_transaction_start() { global $pdo; $pdo->beginTransaction(); }
function db_transaction_commit() { global $pdo; $pdo->commit(); }
function db_transaction_rollback() { global $pdo; $pdo->rollback(); }