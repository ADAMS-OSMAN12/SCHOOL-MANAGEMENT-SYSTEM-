<?php
/**
 * Delete Student Handler
 * Sakam M/A JHS School Management System
 * 
 * Process student deletion request
 */

// Require admin access
require_once '../includes/functions.php';
requireAdmin();

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setMessage('Invalid request.', 'error');
    redirect('students.php');
}

$studentId = (int)$_GET['id'];

// Get student info before deletion
$student = fetchOne("SELECT first_name, last_name FROM students WHERE id = ?", [$studentId]);

if (!$student) {
    setMessage('Student not found.', 'error');
    redirect('students.php');
}

// Validate CSRF token
if (!isset($_GET['token']) || !validateCSRFToken($_GET['token'])) {
    setMessage('Invalid request token. Please try again.', 'error');
    redirect('students.php');
}

// Delete student (cascade will handle related records)
$result = delete('students', "id = $studentId");

if ($result) {
    // Log activity
    logActivity(getUserId(), 'Delete Student', "Deleted student: {$student['first_name']} {$student['last_name']}");
    
    setMessage('Student deleted successfully!', 'success');
} else {
    setMessage('Failed to delete student. Please try again.', 'error');
}

redirect('students.php');