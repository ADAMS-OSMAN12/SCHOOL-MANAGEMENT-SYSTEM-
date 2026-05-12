<?php
require_once __DIR__ . '/../../../sakam_sms/config.php';

$conn = getDBConnection();

// Check if pass_mark exists
$result = $conn->query("SHOW COLUMNS FROM subjects LIKE 'pass_mark'");
if ($result->num_rows === 0) {
    $conn->query("ALTER TABLE subjects ADD COLUMN pass_mark INT DEFAULT 40 AFTER class_id");
    echo "Added pass_mark column.\n";
} else {
    echo "pass_mark already exists.\n";
}

// Check if description exists
$result = $conn->query("SHOW COLUMNS FROM subjects LIKE 'description'");
if ($result->num_rows === 0) {
    $conn->query("ALTER TABLE subjects ADD COLUMN description TEXT AFTER pass_mark");
    echo "Added description column.\n";
} else {
    echo "description already exists.\n";
}

// Check if status exists
$result = $conn->query("SHOW COLUMNS FROM subjects LIKE 'status'");
if ($result->num_rows === 0) {
    $conn->query("ALTER TABLE subjects ADD COLUMN status ENUM('Active','Inactive') DEFAULT 'Active' AFTER description");
    echo "Added status column.\n";
} else {
    echo "status already exists.\n";
}

$conn->close();