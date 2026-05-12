<?php
// Simulate loading the login page
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering to catch any issues
ob_start();

try {
    // Simulate session start
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    echo "Session started OK\n";

    // Load config
    require_once __DIR__ . '/config.php';
    echo "Config loaded OK\n";

    // Load db.php
    require_once __DIR__ . '/includes/db.php';
    echo "DB loaded OK\n";

    // Load functions.php
    require_once __DIR__ . '/includes/functions.php';
    echo "Functions loaded OK\n";

    // Check if user is logged in
    if (isLoggedIn()) {
        echo "User is logged in, would redirect to dashboard\n";
    } else {
        echo "User not logged in, showing login form\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

// Check for any output before the login page HTML
$output = ob_get_contents();
ob_end_clean();
echo "=== Captured output ===\n";
echo $output;
echo "\n=== End captured output ===\n";