<?php
require_once __DIR__ . '/../../../sakam_sms/config.php';

$conn = getDBConnection();

$result = $conn->query("SHOW COLUMNS FROM results");
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
}
echo "Results table columns: " . implode(', ', $columns) . "\n";

$conn->close();