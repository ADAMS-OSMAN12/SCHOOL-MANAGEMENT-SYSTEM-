<?php
/**
 * Delete Subject Handler
 * Sakam M/A JHS School Management System
 * 
 * Process subject deletion request
 */

// Require admin access
require_once '../includes/functions.php';
requireAdmin();

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setMessage('Invalid request.', 'error');
    redirect('subjects.php');
}

$subjectId = (int)$_GET['id'];

// Get subject info before deletion
$subject = fetchOne("SELECT subject_name FROM subjects WHERE id = ?", [$subjectId]);

if (!$subject) {
    setMessage('Subject not found.', 'error');
    redirect('subjects.php');
}

// Validate CSRF token
if (!isset($_GET['token']) || !validateCSRFToken($_GET['token'])) {
    setMessage('Invalid request token. Please try again.', 'error');
    redirect('subjects.php');
}

// Delete subject
$result = delete('subjects', "id = $subjectId");

if ($result) {
    // Log activity
    logActivity(getUserId(), 'Delete Subject', "Deleted subject: {$subject['subject_name']}");
    
    setMessage('Subject deleted successfully!', 'success');
} else {
    setMessage('Failed to delete subject. Please try again.', 'error');
}

redirect('subjects.php');