<?php
require_once __DIR__ . '/../../../sakam_sms/config.php';

$conn = getDBConnection();

// Check if exam_date exists
$result = $conn->query("SHOW COLUMNS FROM results LIKE 'exam_date'");
if ($result->num_rows === 0) {
    $conn->query("ALTER TABLE results ADD COLUMN exam_date DATE AFTER exam_score");
    echo "Added exam_date column.\n";
} else {
    echo "exam_date already exists.\n";
}

// Check if comments exists
$result = $conn->query("SHOW COLUMNS FROM results LIKE 'comments'");
if ($result->num_rows === 0) {
    $conn->query("ALTER TABLE results ADD COLUMN comments TEXT AFTER exam_date");
    echo "Added comments column.\n";
} else {
    echo "comments already exists.\n";
}

$conn->close();