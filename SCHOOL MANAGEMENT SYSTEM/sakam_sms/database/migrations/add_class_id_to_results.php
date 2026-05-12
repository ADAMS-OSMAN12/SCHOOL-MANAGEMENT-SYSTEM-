<?php
require_once __DIR__ . '/../../../sakam_sms/config.php';

$conn = getDBConnection();

// Check if class_id column exists in results table
$result = $conn->query("SHOW COLUMNS FROM results LIKE 'class_id'");
if ($result->num_rows === 0) {
    $sql = "ALTER TABLE results ADD COLUMN class_id INT NOT NULL AFTER subject_id";
    if ($conn->query($sql) === TRUE) {
        echo "Column 'class_id' added to results table successfully.\n";
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
} else {
    echo "Column 'class_id' already exists in results table.\n";
}

$conn->close();