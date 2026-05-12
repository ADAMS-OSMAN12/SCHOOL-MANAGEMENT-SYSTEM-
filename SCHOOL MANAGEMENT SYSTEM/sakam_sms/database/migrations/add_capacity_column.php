<?php
require_once __DIR__ . '/../../../sakam_sms/config.php';

$conn = getDBConnection();

// Check if capacity column exists
$result = $conn->query("SHOW COLUMNS FROM classes LIKE 'capacity'");
if ($result->num_rows === 0) {
    $sql = "ALTER TABLE classes ADD COLUMN capacity INT DEFAULT 40";
    if ($conn->query($sql) === TRUE) {
        echo "Column 'capacity' added to classes table successfully.\n";
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
} else {
    echo "Column 'capacity' already exists in classes table.\n";
}

$conn->close();