<?php
/**
 * Delete Class Handler
 * Sakam M/A JHS School Management System
 * 
 * Process class deletion request
 */

// Require admin access
require_once '../includes/functions.php';
requireAdmin();

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setMessage('Invalid request.', 'error');
    redirect('classes.php');
}

$classId = (int)$_GET['id'];

// Get class info before deletion
$class = fetchOne("SELECT class_name FROM classes WHERE id = ?", [$classId]);

if (!$class) {
    setMessage('Class not found.', 'error');
    redirect('classes.php');
}

// Validate CSRF token
if (!isset($_GET['token']) || !validateCSRFToken($_GET['token'])) {
    setMessage('Invalid request token. Please try again.', 'error');
    redirect('classes.php');
}

// Check if class has students
$studentCount = fetchOne("SELECT COUNT(*) as total FROM students WHERE class_id = ?", [$classId]);

if ($studentCount['total'] > 0) {
    setMessage('Cannot delete class with students. Please reassign students first.', 'error');
    redirect('classes.php');
}

// Delete class
$result = delete('classes', "id = $classId");

if ($result) {
    // Log activity
    logActivity(getUserId(), 'Delete Class', "Deleted class: {$class['class_name']}");
    
    setMessage('Class deleted successfully!', 'success');
} else {
    setMessage('Failed to delete class. Please try again.', 'error');
}

redirect('classes.php');