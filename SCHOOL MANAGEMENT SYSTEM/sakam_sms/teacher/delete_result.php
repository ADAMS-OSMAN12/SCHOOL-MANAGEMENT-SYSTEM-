<?php
/**
 * Delete Result Handler
 * Sakam M/A JHS School Management System
 * 
 * Process result deletion request
 */

// Require teacher access
require_once '../includes/functions.php';
requireTeacher();

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setMessage('Invalid request.', 'error');
    redirect('results.php');
}

$resultId = (int)$_GET['id'];

// Get result info before deletion
$result = fetchOne("SELECT r.*, s.first_name, s.last_name, sub.subject_name 
                   FROM results r 
                   JOIN students s ON r.student_id = s.id 
                   JOIN subjects sub ON r.subject_id = sub.id 
                   WHERE r.id = ?", [$resultId]);

if (!$result) {
    setMessage('Result not found.', 'error');
    redirect('results.php');
}

// Validate CSRF token
if (!isset($_GET['token']) || !validateCSRFToken($_GET['token'])) {
    setMessage('Invalid request token. Please try again.', 'error');
    redirect('results.php');
}

// Delete result
$deleteResult = delete('results', "id = $resultId");

if ($deleteResult) {
    // Log activity
    logActivity(getUserId(), 'Delete Result', "Deleted result for {$result['first_name']} {$result['last_name']} in {$result['subject_name']}");
    
    setMessage('Result deleted successfully!', 'success');
} else {
    setMessage('Failed to delete result. Please try again.', 'error');
}

redirect('results.php');