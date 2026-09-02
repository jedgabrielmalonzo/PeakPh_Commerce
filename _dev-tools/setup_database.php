<?php
/**
 * PeakPH Commerce - Database Setup Helper
 * This script helps apply the complete database schema
 */

require_once 'includes/db.php';

echo "=== PeakPH Commerce Database Setup ===\n\n";

if (!isDatabaseConnected()) {
    die("❌ Database connection failed. Please check your database configuration.\n");
}

echo "✅ Database connected successfully\n\n";

$conn = $GLOBALS['conn'];

// Read and execute the complete database setup
$sql_file = 'database_setup_complete.sql';

if (!file_exists($sql_file)) {
    die("❌ Database setup file not found: $sql_file\n");
}

echo "📄 Reading database setup file: $sql_file\n";
$sql_content = file_get_contents($sql_file);

// Split SQL content into individual statements
$statements = array_filter(
    array_map('trim', explode(';', $sql_content)),
    function($stmt) {
        return !empty($stmt) && 
               !preg_match('/^--/', $stmt) && 
               !preg_match('/^\/\*/', $stmt) &&
               !preg_match('/^\s*$/', $stmt);
    }
);

$success_count = 0;
$error_count = 0;
$warnings = [];

echo "🔧 Executing " . count($statements) . " SQL statements...\n\n";

foreach ($statements as $index => $statement) {
    $statement = trim($statement);
    if (empty($statement)) continue;
    
    // Skip comments and multi-line comments
    if (preg_match('/^(--|\/*|\*|SELECT.*AS|SELECT \'|CREATE INDEX.*IF NOT EXISTS)/', $statement)) {
        continue;
    }
    
    $result = mysqli_query($conn, $statement);
    
    if ($result) {
        $success_count++;
        // Show progress for major operations
        if (preg_match('/^(CREATE TABLE|ALTER TABLE|CREATE INDEX)/', $statement)) {
            if (preg_match('/CREATE TABLE.*?`?(\w+)`?\s*\(/', $statement, $matches)) {
                echo "✅ Created table: " . $matches[1] . "\n";
            } elseif (preg_match('/CREATE INDEX\s+(\w+)/', $statement, $matches)) {
                echo "📊 Created index: " . $matches[1] . "\n";
            } elseif (preg_match('/ALTER TABLE\s+`?(\w+)`?/', $statement, $matches)) {
                echo "🔧 Modified table: " . $matches[1] . "\n";
            }
        }
    } else {
        $error = mysqli_error($conn);
        if (strpos($error, 'already exists') !== false || 
            strpos($error, 'Duplicate') !== false ||
            strpos($error, 'Multiple primary key') !== false) {
            $warnings[] = "⚠️  " . substr($statement, 0, 50) . "... (already exists)";
        } else {
            $error_count++;
            echo "❌ Error in statement " . ($index + 1) . ": $error\n";
            echo "   Statement: " . substr($statement, 0, 100) . "...\n\n";
        }
    }
}

echo "\n=== Setup Summary ===\n";
echo "✅ Successful operations: $success_count\n";
echo "❌ Errors: $error_count\n";
echo "⚠️  Warnings: " . count($warnings) . "\n\n";

if (!empty($warnings)) {
    echo "Warnings (usually safe to ignore):\n";
    foreach (array_slice($warnings, 0, 5) as $warning) {
        echo "$warning\n";
    }
    if (count($warnings) > 5) {
        echo "... and " . (count($warnings) - 5) . " more warnings\n";
    }
    echo "\n";
}

// Verify tables
echo "=== Database Verification ===\n";
$tables = ['users', 'inventory', 'products', 'orders', 'order_items', 
          'payments', 'paymongo_webhooks', 'payment_logs', 'audit_trail', 
          'carousel', 'bestsellers', 'new_arrivals'];

foreach ($tables as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if ($result && mysqli_num_rows($result) > 0) {
        // Get row count
        $count_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM `$table`");
        $count = $count_result ? mysqli_fetch_assoc($count_result)['count'] : 0;
        echo "✅ $table (rows: $count)\n";
    } else {
        echo "❌ $table (missing)\n";
    }
}

echo "\n=== PayMongo Configuration Status ===\n";

// Check PayMongo config
if (file_exists('config/paymongo.php')) {
    $config = include 'config/paymongo.php';
    if (isset($config['secret_key']) && strpos($config['secret_key'], 'sk_test_') === 0) {
        echo "✅ PayMongo API keys configured\n";
    } else {
        echo "⚠️  PayMongo API keys need configuration\n";
    }
} else {
    echo "❌ PayMongo config file missing\n";
}

// Check PayMongo helper
if (file_exists('includes/PayMongoHelper.php')) {
    echo "✅ PayMongo Helper class available\n";
} else {
    echo "❌ PayMongo Helper class missing\n";
}

// Check payment processing files
$payment_files = ['payment/process_payment.php', 'payment/success.php', 'payment/failed.php'];
$payment_ready = true;
foreach ($payment_files as $file) {
    if (file_exists($file)) {
        echo "✅ $file\n";
    } else {
        echo "❌ $file (missing)\n";
        $payment_ready = false;
    }
}

echo "\n=== Final Status ===\n";
if ($error_count === 0 && $payment_ready) {
    echo "🎉 Database setup completed successfully!\n";
    echo "🚀 PayMongo GCash integration is ready for testing\n";
    echo "\nNext steps:\n";
    echo "1. Test the checkout process at: http://localhost/PeakPH_Commerce/test_gcash_web.php\n";
    echo "2. Verify PayMongo API connection\n";
    echo "3. Test complete payment flow\n";
} else {
    echo "⚠️  Setup completed with issues. Please review errors above.\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
?>