<?php
/**
 * Index/Landing Page
 * Sakam M/A JHS School Management System
 * 
 * Redirects to login or dashboard
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once 'includes/db.php';
require_once 'includes/functions.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect('dashboard.php');
} else {
    redirect('auth/index.php');
}