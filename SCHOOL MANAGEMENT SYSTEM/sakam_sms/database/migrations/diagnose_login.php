<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Login Page Diagnosis ===\n\n";

// Check config
echo "1. Config file: ";
$configPath = __DIR__ . '/../../config.php';
if (file_exists($configPath)) {
    echo "EXISTS\n";
} else {
    echo "MISSING at $configPath\n";
}

require_once $configPath;

// Check database
echo "2. Database: ";
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($db->connect_error) {
    echo "FAILED: " . $db->connect_error . "\n";
} else {
    echo "OK\n";
}

// Check users table
echo "3. Users count: ";
$r = $db->query("SELECT COUNT(*) as c FROM users");
echo $r->fetch_assoc()['c'] . "\n";

// Check users columns
echo "4. Users columns: ";
$cols = $db->query("SHOW COLUMNS FROM users");
$colNames = [];
while ($row = $cols->fetch_assoc()) $colNames[] = $row['Field'];
echo implode(', ', $colNames) . "\n";

// Check for password column
echo "5. Password exists: ";
$pwCheck = $db->query("SELECT password FROM users WHERE username = 'admin'");
if ($pwCheck && $pwCheck->num_rows > 0) {
    $pwRow = $pwCheck->fetch_assoc();
    echo "YES (hash: " . substr($pwRow['password'], 0, 20) . "...)\n";
} else {
    echo "NO\n";
}

$db->close();
echo "\n=== Done ===\n";