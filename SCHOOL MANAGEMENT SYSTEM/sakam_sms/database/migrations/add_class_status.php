<?php
require_once __DIR__ . '/../../../sakam_sms/config.php';

$conn = getDBConnection();

// Check if status column exists
$result = $conn->query("SHOW COLUMNS FROM classes LIKE 'status'");
if ($result->num_rows === 0) {
    $sql = "ALTER TABLE classes ADD COLUMN status ENUM('Active','Inactive') DEFAULT 'Active'";
    if ($conn->query($sql) === TRUE) {
        echo "Column 'status' added to classes table successfully.\n";
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
} else {
    echo "Column 'status' already exists in classes table.\n";
}

$conn->close();