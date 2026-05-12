<?php
header('Content-Type: text/plain; charset=utf-8');

// Database credentials
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'sakam_sms';

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get admin user password
$sql = "SELECT password FROM users WHERE username = 'admin'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $hash = $row["password"];
    echo "Stored hash: " . $hash . "\n";
    echo "Hash length: " . strlen($hash) . "\n";
    
    // Check if it matches the expected hash from the SQL
    $expectedHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    echo "Expected hash: " . $expectedHash . "\n";
    echo "Match: " . ($hash === $expectedHash ? 'Yes' : 'No') . "\n";
    
    // Test password verification
    $testPassword = 'admin123';
    $verifyResult = password_verify($testPassword, $hash);
    echo "Password 'admin123' verifies: " . ($verifyResult ? 'Yes' : 'No') . "\n";
    
    // Also test with the hash from the SQL directly
    $verifyResult2 = password_verify($testPassword, $expectedHash);
    echo "Password 'admin123' verifies against expected hash: " . ($verifyResult2 ? 'Yes' : 'No') . "\n";
} else {
    echo "Admin user not found\n";
}

$conn->close();
?>