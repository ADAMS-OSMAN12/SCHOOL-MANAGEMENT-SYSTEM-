<?php
header('Content-Type: text/plain; charset=utf-8');

$hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

// Test if 'password' matches
if (password_verify('password', $hash)) {
    echo "Password 'password' matches the hash in the database!\n";
} else {
    echo "Password 'password' does NOT match the hash.\n";
}

// Let's also check what hash 'password' generates with the same parameters
$testHash = password_hash('password', PASSWORD_BCRYPT, ['cost' => 10]);
echo "password_hash('password', PASSWORD_BCRYPT, ['cost' => 10]) = " . $testHash . "\n";
echo "Matches stored hash: " . ($testHash === $hash ? 'Yes' : 'No') . "\n";

// Now let's check if there might be an issue with the SQL file
// Let's look at the actual INSERT statement again
echo "\nChecking the SQL file for the exact hash...\n";
$sqlContent = file_get_contents(__DIR__ . '/database.sql');
if (preg_match('/VALUES\s*\(\s*\'admin\',\s*\'([^\']+)\',\s*\'admin\',\s*\'admin@sakamsms\.edu\.gh\'/i', $sqlContent, $matches)) {
    $extractedHash = $matches[1];
    echo "Extracted hash from SQL: " . $extractedHash . "\n";
    echo "Matches our hash variable: " . ($extractedHash === $hash ? 'Yes' : 'No') . "\n";
    
    // Check if 'password' matches this extracted hash
    if (password_verify('password', $extractedHash)) {
        echo "Password 'password' matches the extracted hash from SQL!\n";
    } else {
        echo "Password 'password' does NOT match the extracted hash from SQL.\n";
    }
}
?>