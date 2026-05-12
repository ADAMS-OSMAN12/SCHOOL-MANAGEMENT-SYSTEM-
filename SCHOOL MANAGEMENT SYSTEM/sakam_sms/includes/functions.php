<?php
/**
 * Authentication Check File
 * Sakam M/A JHS School Management System
 * 
 * This file checks if user is logged in and has proper permissions
 * Include this file at the top of all protected pages
 */

// Include database connection and configuration
require_once __DIR__ . '/db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user has admin role
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if user has teacher role
 * @return bool
 */
function isTeacher() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'teacher';
}

/**
 * Check if user has specific role
 * @param string $role Role to check
 * @return bool
 */
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Redirect to specified URL
 * @param string $url URL to redirect to
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Require login - redirect to login if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect(SITE_URL . '/index.php');
    }
}

/**
 * Require admin role - redirect to dashboard if not admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        redirect('../dashboard.php');
    }
}

/**
 * Require teacher or admin role
 */
function requireTeacher() {
    requireLogin();
    if (!isTeacher() && !isAdmin()) {
        redirect('../index.php');
    }
}

/**
 * Get current user ID
 * @return int|null
 */
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 * @return string|null
 */
function getUserRole() {
    return $_SESSION['role'] ?? null;
}

/**
 * Get current username
 * @return string
 */
function getUsername() {
    return $_SESSION['username'] ?? 'Guest';
}

/**
 * Get current user full name
 * @return string
 */
function getUserFullName() {
    return $_SESSION['full_name'] ?? 'User';
}

/**
 * Display session message if exists
 */
function displayMessage() {
    if (isset($_SESSION['message'])) {
        $type = $_SESSION['message_type'] ?? 'info';
        echo '<div class="alert alert-' . escapeHTML($type) . '">';
        echo '<i class="fas fa-' . ($type === 'success' ? 'check-circle' : ($type === 'error' ? 'exclamation-circle' : ($type === 'warning' ? 'exclamation-triangle' : 'info-circle'))) . '"></i>';
        echo '<span>' . escapeHTML($_SESSION['message']) . '</span>';
        echo '<button class="close-alert">&times;</button>';
        echo '</div>';
        
        unset($_SESSION['message'], $_SESSION['message_type']);
    }
}

/**
 * Set session message
 * @param string $message Message to display
 * @param string $type Message type (success, error, warning, info)
 */
function setMessage($message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}



/**
 * Get current page name
 * @return string
 */
function getCurrentPage() {
    return basename($_SERVER['PHP_SELF'], '.php');
}

/**
 * Check if current page is active
 * @param string $page Page name to check
 * @return bool
 */
function isActivePage($page) {
    return getCurrentPage() === $page;
}

/**
 * Generate CSRF token
 * @return string
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * @param string $token Token to validate
 * @return bool
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get teacher's name by user ID
 * @param int $userId User ID
 * @return string
 */
function getTeacherNameByUserId($userId) {
    $teacher = fetchOne("SELECT CONCAT(first_name, ' ', last_name) as name FROM teachers WHERE id = (SELECT teacher_id FROM users WHERE id = ?)", [$userId]);
    return $teacher ? $teacher['name'] : '';
}

/**
 * Get class name by ID
 * @param int $classId Class ID
 * @return string
 */
function getClassName($classId) {
    $class = fetchOne("SELECT class_name FROM classes WHERE id = ?", [$classId]);
    return $class ? $class['class_name'] : '';
}

/**
 * Get subject name by ID
 * @param int $subjectId Subject ID
 * @return string
 */
function getSubjectName($subjectId) {
    $subject = fetchOne("SELECT subject_name FROM subjects WHERE id = ?", [$subjectId]);
    return $subject ? $subject['subject_name'] : '';
}

/**
 * Get student name by ID
 * @param int $studentId Student ID
 * @return string
 */
function getStudentName($studentId) {
    $student = fetchOne("SELECT CONCAT(first_name, ' ', last_name) as name FROM students WHERE id = ?", [$studentId]);
    return $student ? $student['name'] : '';
}

/**
 * Get all classes
 * @return array
 */
function getAllClasses() {
    return fetchAll("SELECT * FROM classes ORDER BY class_level");
}

/**
 * Get all subjects
 * @return array
 */
function getAllSubjects() {
    return fetchAll("SELECT * FROM subjects ORDER BY subject_name");
}

/**
 * Get all teachers
 * @return array
 */
function getAllTeachers() {
    return fetchAll("SELECT t.*, s.subject_name FROM teachers t LEFT JOIN subjects s ON t.subject_id = s.id ORDER BY t.last_name");
}

/**
 * Get students by class
 * @param int $classId Class ID
 * @return array
 */
function getStudentsByClass($classId) {
    return fetchAll("SELECT * FROM students WHERE class_id = ? AND status = 'Active' ORDER BY last_name", [$classId]);
}

/**
 * Format date for display
 * @param string $date Date string
 * @return string
 */
function formatDate($date) {
    if (empty($date)) return '';
    return date('d M, Y', strtotime($date));
}

/**
 * Format datetime for display
 * @param string $datetime Datetime string
 * @return string
 */
function formatDateTime($datetime) {
    if (empty($datetime)) return '';
    return date('d M, Y h:i A', strtotime($datetime));
}

/**
 * Calculate grade from score
 * @param float $score Score
 * @return string
 */
function calculateGrade($score) {
    $score = (float)$score;
    if ($score >= 90) return 'A+';
    if ($score >= 80) return 'A';
    if ($score >= 75) return 'B+';
    if ($score >= 70) return 'B';
    if ($score >= 65) return 'C+';
    if ($score >= 60) return 'C';
    if ($score >= 55) return 'D+';
    if ($score >= 50) return 'D';
    if ($score >= 45) return 'E';
    return 'F';
}

/**
 * Get grade color class
 * @param string $grade Grade
 * @return string
 */
function getGradeClass($grade) {
    $grade = strtoupper($grade);
    if (in_array($grade, ['A+', 'A'])) return 'grade-a';
    if (in_array($grade, ['B+', 'B'])) return 'grade-b';
    if (in_array($grade, ['C+', 'C'])) return 'grade-c';
    if (in_array($grade, ['D+', 'D'])) return 'grade-d';
    if ($grade === 'E') return 'grade-e';
    return 'grade-f';
}

/**
 * Get status badge class
 * @param string $status Status
 * @return string
 */
function getStatusBadgeClass($status) {
    $status = strtolower($status);
    if ($status === 'active' || $status === 'paid') return 'badge-success';
    if ($status === 'inactive' || $status === 'suspended') return 'badge-danger';
    if ($status === 'partial') return 'badge-warning';
    return 'badge-info';
}

/**
 * Pagination helper
 * @param int $total Total records
 * @param int $perPage Records per page
 * @param int $currentPage Current page
 * @return array
 */
function paginate($total, $perPage, $currentPage) {
    $totalPages = ceil($total / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    
    return [
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => $offset,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}