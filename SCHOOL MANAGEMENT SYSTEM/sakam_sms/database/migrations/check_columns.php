<?php
require_once __DIR__ . '/../../../sakam_sms/config.php';

$conn = getDBConnection();

// Check subjects table columns
$result = $conn->query("SHOW COLUMNS FROM subjects");
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
}
echo "Subjects table columns: " . implode(', ', $columns) . "\n";

// Check users table columns
$result2 = $conn->query("SHOW COLUMNS FROM users");
$columns2 = [];
while ($row = $result2->fetch_assoc()) {
    $columns2[] = $row['Field'];
}
echo "Users table columns: " . implode(', ', $columns2) . "\n";

$conn->close();