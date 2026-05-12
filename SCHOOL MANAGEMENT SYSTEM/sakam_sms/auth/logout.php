<?php
/**
 * Logout Script
 * Sakam M/A JHS School Management System
 * 
 * Handles user logout and session destruction
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Log activity before logout
if (isLoggedIn()) {
    $userId = getUserId();
    logActivity($userId, 'Logout', 'User logged out');
}

// Destroy all session data
$_SESSION = array();

// Destroy session cookie
if (isset($_COOKIE[session_name()])) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destroy session
session_destroy();

// Redirect to login page
header("Location: index.php");
exit();