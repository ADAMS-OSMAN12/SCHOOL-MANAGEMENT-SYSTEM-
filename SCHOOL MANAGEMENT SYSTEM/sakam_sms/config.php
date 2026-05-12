<?php
/**
 * Database Configuration File
 * Sakam M/A JHS School Management System
 * 
 * This file handles database connection using MySQLi
 * with prepared statements for security
 */

// Database credentials
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sakam_sms');

// Site configuration
define('SITE_NAME', 'Sakam M/A JHS');
define('SITE_URL', 'http://localhost/sakam_sms');
define('ADMIN_EMAIL', 'admin@sakamsms.edu.gh');

// Session configuration
define('SESSION_TIMEOUT', 3600); // 1 hour

// Create database connection
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        // Create connection
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Check connection
        if ($conn->connect_error) {
            die("Database connection failed: " . $conn->connect_error);
        }
        
        // Set charset to UTF-8
        $conn->set_charset("utf8mb4");
    }
    
    return $conn;
}

/**
 * Sanitize input to prevent SQL injection
 * @param string $input The input to sanitize
 * @return string Sanitized input
 */
function sanitizeInput($input) {
    $conn = getDBConnection();
    return mysqli_real_escape_string($conn, trim($input));
}

/**
 * Escape HTML to prevent XSS attacks
 * @param string $input The input to escape
 * @return string Escaped output
 */
function escapeHTML($input) {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}



/**
 * Get current academic year
 * @return string Academic year (e.g., 2024-2025)
 */
function getAcademicYear() {
    $currentYear = date('Y');
    $nextYear = $currentYear + 1;
    return "$currentYear-$nextYear";
}

/**
 * Get current term
 * @return string Current term (1st, 2nd, or 3rd)
 */
function getCurrentTerm() {
    $month = date('n');
    if ($month >= 1 && $month <= 4) return '1st';
    if ($month >= 5 && $month <= 8) return '2nd';
    return '3rd';
}

/**
 * Log activity to database
 * @param int $userId User ID
 * @param string $action Action performed
 * @param string $description Description of action
 */
function logActivity($userId, $action, $description = '') {
    $conn = getDBConnection();
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    $stmt = $conn->prepare("INSERT INTO activity_log (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $userId, $action, $description, $ip);
    $stmt->execute();
    $stmt->close();
}

/**
 * Display message and redirect
 * @param string $message Message to display
 * @param string $url URL to redirect to
 * @param string $type Message type (success, error, warning, info)
 */
function showMessageAndRedirect($message, $url, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    redirect($url);
}