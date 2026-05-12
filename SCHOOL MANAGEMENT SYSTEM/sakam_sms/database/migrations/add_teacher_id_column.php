<?php
require_once __DIR__ . '/../../../sakam_sms/config.php';

$conn = getDBConnection();

// Check if teacher_id column exists
$result = $conn->query("SHOW COLUMNS FROM classes LIKE 'teacher_id'");
if ($result->num_rows === 0) {
    $sql = "ALTER TABLE classes ADD COLUMN teacher_id INT DEFAULT NULL AFTER class_level";
    if ($conn->query($sql) === TRUE) {
        echo "Column 'teacher_id' added to classes table successfully.\n";
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
} else {
    echo "Column 'teacher_id' already exists in classes table.\n";
}

$conn->close();