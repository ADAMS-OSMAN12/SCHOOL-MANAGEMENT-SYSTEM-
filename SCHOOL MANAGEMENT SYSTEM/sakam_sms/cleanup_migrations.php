<?php
// Cleanup all migration scripts except the schema file
$files = glob(__DIR__ . '/migrations/*.php');
foreach ($files as $file) {
    unlink($file);
    echo "Deleted: $file\n";
}
echo "Cleanup complete.\n";