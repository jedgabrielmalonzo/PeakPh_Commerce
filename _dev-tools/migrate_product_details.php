<?php
/**
 * Database Migration: Add Product Details Columns
 * Run this file once to add enhanced product detail support
 */

require_once('../includes/db.php');

if (!isDatabaseConnected()) {
    die("❌ Database connection failed! Please check your database settings.\n");
}

echo "🔄 Starting database migration...\n\n";

// Access the global $conn variable
global $conn;

// Array of ALTER TABLE statements
$alterStatements = [
    "ALTER TABLE inventory ADD COLUMN IF NOT EXISTS description TEXT DEFAULT NULL AFTER image",
    "ALTER TABLE inventory ADD COLUMN IF NOT EXISTS specifications TEXT DEFAULT NULL AFTER description",
    "ALTER TABLE inventory ADD COLUMN IF NOT EXISTS additional_images TEXT DEFAULT NULL AFTER specifications",
    "ALTER TABLE inventory ADD COLUMN IF NOT EXISTS video_url VARCHAR(500) DEFAULT NULL AFTER additional_images",
    "ALTER TABLE inventory ADD COLUMN IF NOT EXISTS dimensions VARCHAR(255) DEFAULT NULL AFTER video_url",
    "ALTER TABLE inventory ADD COLUMN IF NOT EXISTS weight VARCHAR(100) DEFAULT NULL AFTER dimensions",
    "ALTER TABLE inventory ADD COLUMN IF NOT EXISTS category_details VARCHAR(255) DEFAULT NULL AFTER weight"
];

$success = 0;
$errors = 0;

foreach ($alterStatements as $sql) {
    // Extract column name for display
    preg_match('/ADD COLUMN IF NOT EXISTS (\w+)/', $sql, $matches);
    $columnName = $matches[1] ?? 'unknown';
    
    if ($conn->query($sql)) {
        echo "✅ Column '$columnName' added successfully\n";
        $success++;
    } else {
        // Check if error is because column already exists
        if (strpos($conn->error, 'Duplicate column name') !== false) {
            echo "ℹ️  Column '$columnName' already exists (skipped)\n";
        } else {
            echo "❌ Error adding '$columnName': " . $conn->error . "\n";
            $errors++;
        }
    }
}

echo "\n";
echo "=" . str_repeat("=", 50) . "\n";
echo "Migration Complete!\n";
echo "Successful: $success\n";
echo "Errors: $errors\n";
echo "=" . str_repeat("=", 50) . "\n\n";

if ($errors === 0) {
    echo "✅ All columns added successfully! Database is ready for enhanced product details.\n";
} else {
    echo "⚠️  Some errors occurred. Please check the error messages above.\n";
}

// Display current table structure
echo "\n📋 Current inventory table structure:\n";
$result = $conn->query("DESCRIBE inventory");
if ($result) {
    echo str_repeat("-", 80) . "\n";
    printf("%-25s %-20s %-10s %-10s\n", "Field", "Type", "Null", "Default");
    echo str_repeat("-", 80) . "\n";
    while ($row = $result->fetch_assoc()) {
        printf("%-25s %-20s %-10s %-10s\n", 
            $row['Field'], 
            $row['Type'], 
            $row['Null'],
            $row['Default'] ?? 'NULL'
        );
    }
    echo str_repeat("-", 80) . "\n";
}

$conn->close();
?>
