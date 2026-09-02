<?php
require_once 'includes/db.php';

if (isDatabaseConnected()) {
    // Add order_id column if missing
    $result = mysqli_query($GLOBALS['conn'], "SHOW COLUMNS FROM orders LIKE 'order_id'");
    if (mysqli_num_rows($result) == 0) {
        $sql = "ALTER TABLE orders ADD COLUMN order_id VARCHAR(50) UNIQUE AFTER id";
        if (mysqli_query($GLOBALS['conn'], $sql)) {
            echo "✅ Added order_id column to orders table\n";
        } else {
            echo "❌ Error adding order_id column: " . mysqli_error($GLOBALS['conn']) . "\n";
        }
    } else {
        echo "✅ order_id column already exists\n";
    }
    
    // Add billing_address column if missing
    $result = mysqli_query($GLOBALS['conn'], "SHOW COLUMNS FROM orders LIKE 'billing_address'");
    if (mysqli_num_rows($result) == 0) {
        $sql = "ALTER TABLE orders ADD COLUMN billing_address TEXT AFTER shipping_address";
        if (mysqli_query($GLOBALS['conn'], $sql)) {
            echo "✅ Added billing_address column to orders table\n";
        } else {
            echo "❌ Error adding billing_address column: " . mysqli_error($GLOBALS['conn']) . "\n";
        }
    } else {
        echo "✅ billing_address column already exists\n";
    }
    
    // Add payment_method column if missing
    $result = mysqli_query($GLOBALS['conn'], "SHOW COLUMNS FROM orders LIKE 'payment_method'");
    if (mysqli_num_rows($result) == 0) {
        $sql = "ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) AFTER billing_address";
        if (mysqli_query($GLOBALS['conn'], $sql)) {
            echo "✅ Added payment_method column to orders table\n";
        } else {
            echo "❌ Error adding payment_method column: " . mysqli_error($GLOBALS['conn']) . "\n";
        }
    } else {
        echo "✅ payment_method column already exists\n";
    }
    
    echo "Database structure updated!\n";
} else {
    echo "❌ Database not connected\n";
}
?>