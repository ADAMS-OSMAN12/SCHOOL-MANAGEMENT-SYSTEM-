<?php
/**
 * AJAX - Load Students
 * Sakam M/A JHS School Management System
 * 
 * Returns JSON list of students for a given class
 */

header('Content-Type: application/json');

// Include functions
require_once '../includes/functions.php';

// Check if class_id is provided
$classId = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;

if ($classId <= 0) {
    echo json_encode([]);
    exit;
}

// Get students for the class
$students = fetchAll("SELECT id, first_name, last_name, student_id FROM students WHERE class_id = ? AND status = 'Active' ORDER BY last_name, first_name", [$classId]);

echo json_encode($students);