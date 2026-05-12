<?php
/**
 * Database Connection File
 * Sakam M/A JHS School Management System
 * 
 * This file establishes database connection using MySQLi
 * with proper error handling
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include configuration
require_once dirname(__DIR__) . '/config.php';

/**
 * Get database connection
 * @return mysqli Database connection object
 */
function getDB() {
    static $db = null;
    
    if ($db === null) {
        // Create connection
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Check connection
        if ($db->connect_error) {
            error_log("Database connection failed: " . $db->connect_error);
            die("System error. Please contact administrator.");
        }
        
        // Set charset
        $db->set_charset("utf8mb4");
    }
    
    return $db;
}

/**
 * Execute prepared statement
 * @param string $sql SQL query with placeholders
 * @param array $params Array of parameters to bind
 * @return mysqli_result|bool
 */
function executeQuery($sql, $params = []) {
    $db = getDB();
    $stmt = $db->prepare($sql);
    
    if (!$stmt) {
        error_log("Prepare failed: " . $db->error);
        return false;
    }
    
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    return $stmt;
}

/**
 * Fetch single row
 * @param string $sql SQL query
 * @param array $params Query parameters
 * @return array|null
 */
function fetchOne($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    if (!$stmt) return null;
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row;
}

/**
 * Fetch all rows
 * @param string $sql SQL query
 * @param array $params Query parameters
 * @return array
 */
function fetchAll($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    if (!$stmt) return [];
    
    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    
    return $rows;
}

/**
 * Insert record and return ID
 * @param string $table Table name
 * @param array $data Associative array of field => value
 * @return int|bool Inserted ID or false
 */
function insert($table, $data) {
    $db = getDB();
    
    $fields = array_keys($data);
    $values = array_values($data);
    $placeholders = array_fill(0, count($fields), '?');
    
    $sql = "INSERT INTO $table (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
    
    $stmt = $db->prepare($sql);
    if (!$stmt) return false;
    
    $types = str_repeat('s', count($values));
    $stmt->bind_param($types, ...$values);
    
    if ($stmt->execute()) {
        $insertId = $stmt->insert_id;
        $stmt->close();
        return $insertId;
    }
    
    $stmt->close();
    return false;
}

/**
 * Update record
 * @param string $table Table name
 * @param array $data Associative array of field => value
 * @param string $condition WHERE condition
 * @param array $params Condition parameters
 * @return bool
 */
function update($table, $data, $condition, $params = []) {
    $db = getDB();
    
    $set = [];
    foreach (array_keys($data) as $field) {
        $set[] = "$field = ?";
    }
    
    $sql = "UPDATE $table SET " . implode(', ', $set) . " WHERE $condition";
    
    $values = array_values($data);
    $allParams = array_merge($values, $params);
    
    $stmt = $db->prepare($sql);
    if (!$stmt) return false;
    
    $types = str_repeat('s', count($allParams));
    $stmt->bind_param($types, ...$allParams);
    
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Delete record
 * @param string $table Table name
 * @param string $condition WHERE condition
 * @param array $params Condition parameters
 * @return bool
 */
function delete($table, $condition, $params = []) {
    $db = getDB();
    
    $sql = "DELETE FROM $table WHERE $condition";
    
    $stmt = $db->prepare($sql);
    if (!$stmt) return false;
    
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Count records
 * @param string $table Table name
 * @param string $condition WHERE condition
 * @param array $params Condition parameters
 * @return int
 */
function countRecords($table, $condition = '1', $params = []) {
    $sql = "SELECT COUNT(*) as total FROM $table WHERE $condition";
    $row = fetchOne($sql, $params);
    return $row ? (int)$row['total'] : 0;
}

/**
 * Get single value
 * @param string $sql SQL query
 * @param array $params Query parameters
 * @return mixed
 */
function getValue($sql, $params = []) {
    $row = fetchOne($sql, $params);
    return $row ? array_values($row)[0] : null;
}

/**
 * Check if record exists
 * @param string $table Table name
 * @param string $condition WHERE condition
 * @param array $params Condition parameters
 * @return bool
 */
function recordExists($table, $condition, $params = []) {
    return countRecords($table, $condition, $params) > 0;
}