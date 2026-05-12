<?php
require_once __DIR__ . '/../../../sakam_sms/config.php';

$conn = getDBConnection();

echo "=== TABLES AND COLUMNS ===\n\n";

$tables = ['classes', 'subjects', 'teachers', 'students', 'results', 'users', 'subject_teachers', 'fees', 'fee_payments', 'attendance', 'timetable', 'activity_log'];

foreach ($tables as $table) {
    $result = $conn->query("SHOW COLUMNS FROM $table");
    echo "$table:\n";
    while ($row = $result->fetch_assoc()) {
        echo "  - {$row['Field']} ({$row['Type']})\n";
    }
    echo "\n";
}

$conn->close();