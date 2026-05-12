<?php
/**
 * Delete Teacher Handler
 * Sakam M/A JHS School Management System
 * 
 * Process teacher deletion request
 */

// Require admin access
require_once '../includes/functions.php';
requireAdmin();

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setMessage('Invalid request.', 'error');
    redirect('teachers.php');
}

$teacherId = (int)$_GET['id'];

// Get teacher info before deletion
$teacher = fetchOne("SELECT first_name, last_name FROM teachers WHERE id = ?", [$teacherId]);

if (!$teacher) {
    setMessage('Teacher not found.', 'error');
    redirect('teachers.php');
}

// Get associated user ID
$userInfo = fetchOne("SELECT id FROM users WHERE teacher_id = ?", [$teacherId]);

// Validate CSRF token
if (!isset($_GET['token']) || !validateCSRFToken($_GET['token'])) {
    setMessage('Invalid request token. Please try again.', 'error');
    redirect('teachers.php');
}

// Start transaction
global $conn;
$conn->begin_transaction();

try {
    // Delete teacher
    $result = delete('teachers', "id = $teacherId");
    
    if (!$result) {
        throw new Exception('Failed to delete teacher.');
    }
    
// Delete associated user account if exists
     if ($userInfo) {
         delete('users', "id = {$userInfo['id']}");
     }
    
    // Commit transaction
    $conn->commit();
    
    // Log activity
    logActivity(getUserId(), 'Delete Teacher', "Deleted teacher: {$teacher['first_name']} {$teacher['last_name']}");
    
    setMessage('Teacher deleted successfully!', 'success');
    
} catch (Exception $e) {
    $conn->rollback();
    setMessage('Failed to delete teacher. Please try again.', 'error');
}

redirect('teachers.php');