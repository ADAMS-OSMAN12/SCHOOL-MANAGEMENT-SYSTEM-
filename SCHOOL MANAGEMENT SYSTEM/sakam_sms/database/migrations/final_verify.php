<?php
require_once __DIR__ . '/../../../sakam_sms/config.php';

$conn = getDBConnection();

// Verify columns needed by edit_result.php exist
$needed = ['class_id', 'exam_date', 'comments'];
foreach ($needed as $col) {
    $result = $conn->query("SHOW COLUMNS FROM results LIKE '$col'");
    echo "results.$col: " . ($result->num_rows > 0 ? "EXISTS" : "MISSING") . "\n";
}

// Verify columns needed by add_result.php exist
$needed2 = ['class_id', 'entered_by'];
foreach ($needed2 as $col) {
    $result = $conn->query("SHOW COLUMNS FROM results LIKE '$col'");
    echo "results.$col: " . ($result->num_rows > 0 ? "EXISTS" : "MISSING") . "\n";
}

echo "\nAll results columns:\n";
$result = $conn->query("SHOW COLUMNS FROM results");
while ($row = $result->fetch_assoc()) {
    echo "  {$row['Field']}\n";
}

$conn->close();