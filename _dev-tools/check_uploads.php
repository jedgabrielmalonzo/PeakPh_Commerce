<?php
require_once('../includes/db.php');

echo "=== Image Upload Path Check ===\n\n";

// Check uploads directory in admin
$admin_uploads = __DIR__ . '/../admin/uploads';
echo "Admin uploads directory ($admin_uploads):\n";
echo "Exists: " . (is_dir($admin_uploads) ? "Yes" : "No") . "\n";
if (is_dir($admin_uploads)) {
    echo "Permissions: " . substr(sprintf('%o', fileperms($admin_uploads)), -4) . "\n";
    echo "Writable: " . (is_writable($admin_uploads) ? "Yes" : "No") . "\n";
    
    // List files in uploads directory
    echo "\nFiles in uploads directory:\n";
    $files = scandir($admin_uploads);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $filepath = $admin_uploads . '/' . $file;
            echo "- $file\n";
            echo "  Size: " . filesize($filepath) . " bytes\n";
            echo "  Permissions: " . substr(sprintf('%o', fileperms($filepath)), -4) . "\n";
            echo "  Readable: " . (is_readable($filepath) ? "Yes" : "No") . "\n";
        }
    }
}

// Check database entries
echo "\nChecking database entries:\n";
if (isDatabaseConnected()) {
    $query = "SELECT id, product_name, image FROM inventory WHERE image IS NOT NULL";
    $result = $conn->query($query);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "\nProduct ID: " . $row['id'] . "\n";
            echo "Name: " . $row['product_name'] . "\n";
            echo "Image path in DB: " . $row['image'] . "\n";
            
            // Check if file exists in various locations
            $possible_paths = [
                $admin_uploads . '/' . basename($row['image']),
                __DIR__ . '/../admin/' . $row['image'],
                __DIR__ . '/../' . $row['image']
            ];
            
            echo "Checking file existence:\n";
            foreach ($possible_paths as $path) {
                echo "- $path: " . (file_exists($path) ? "EXISTS" : "Not found") . "\n";
            }
        }
    }
} else {
    echo "Database connection failed\n";
}
?>