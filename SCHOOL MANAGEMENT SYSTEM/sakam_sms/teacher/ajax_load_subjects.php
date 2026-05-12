<?php
/**
 * AJAX - Load Subjects
 * Sakam M/A JHS School Management System
 * 
 * Returns JSON list of subjects for a given class
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

// Get subjects for the class (including general subjects with no class)
$subjects = fetchAll("SELECT id, subject_name, subject_code FROM subjects WHERE (class_id = ? OR class_id IS NULL) AND status = 'Active' ORDER BY subject_name", [$classId]);

echo json_encode($subjects);