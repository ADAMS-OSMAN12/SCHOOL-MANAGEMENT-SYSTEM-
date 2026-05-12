<?php
// Final comprehensive check
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== FINAL VERIFICATION ===\n\n";

// 1. Check all critical PHP files for syntax errors
$files = [
    'index.php',
    'auth/index.php',
    'dashboard.php',
    'includes/db.php',
    'includes/functions.php',
    'includes/header.php',
    'admin/teacher_profile.php',
    'admin/edit_teacher.php',
    'admin/add_teacher.php',
    'admin/delete_teacher.php',
    'admin/teachers.php',
    'admin/edit_class.php',
    'admin/add_class.php',
    'admin/classes.php',
    'admin/edit_subject.php',
    'teacher/edit_result.php',
    'teacher/add_result.php',
    'teacher/results.php',
];

echo "1. PHP Syntax Check:\n";
$allOK = true;
foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $output = shell_exec("C:\\xampp\\php\\php.exe -l " . escapeshellarg($path) . " 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "  [OK] $file\n";
        } else {
            echo "  [FAIL] $file: $output\n";
            $allOK = false;
        }
    } else {
        echo "  [MISSING] $file\n";
    }
}

// 2. Check database tables exist
echo "\n2. Database Tables:\n";
require_once __DIR__ . '/config.php';
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$tables = ['classes', 'subjects', 'teachers', 'students', 'results', 'users', 'subject_teachers', 'fees', 'fee_payments', 'attendance', 'timetable', 'activity_log'];
foreach ($tables as $table) {
    $r = $db->query("SHOW TABLES LIKE '$table'");
    echo "  " . ($r->num_rows > 0 ? "[OK]" : "[MISSING]") . " $table\n";
}

// 3. Check critical columns
echo "\n3. Critical Columns:\n";
$checks = [
    'teachers' => ['staff_id', 'contact', 'subject_id'],
    'subjects' => ['teacher_id', 'class_id', 'pass_mark', 'status'],
    'classes' => ['teacher_id', 'capacity', 'status'],
    'results' => ['class_id', 'exam_date', 'comments'],
    'users' => ['teacher_id', 'username', 'password'],
    'subject_teachers' => ['subject_id', 'teacher_id'],
];
foreach ($checks as $table => $columns) {
    $r = $db->query("SHOW COLUMNS FROM $table");
    $existing = [];
    while ($row = $r->fetch_assoc()) $existing[] = $row['Field'];
    foreach ($columns as $col) {
        echo "  " . (in_array($col, $existing) ? "[OK]" : "[MISSING]") . " $table.$col\n";
    }
}
$db->close();

echo "\n" . ($allOK ? "ALL CHECKS PASSED" : "SOME CHECKS FAILED") . "\n";