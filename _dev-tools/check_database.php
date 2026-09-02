<?php
require_once 'includes/db.php';

echo "=== Database Check ===\n";

if (isDatabaseConnected()) {
    echo "✅ Database connected\n\n";
    
    // Check orders table
    $result = mysqli_query($GLOBALS['conn'], 'DESCRIBE orders');
    if ($result) {
        echo "Orders table structure:\n";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
    } else {
        echo "❌ Orders table does not exist\n";
        echo "Creating orders table...\n";
        
        $create_orders = "
        CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id VARCHAR(50) UNIQUE NOT NULL,
            user_id INT NULL,
            customer_name VARCHAR(255) NOT NULL,
            customer_email VARCHAR(255) NOT NULL,
            customer_phone VARCHAR(20) NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            shipping_address TEXT NOT NULL,
            billing_address TEXT,
            payment_method VARCHAR(50) NOT NULL,
            status ENUM('Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Pending',
            payment_status ENUM('Unpaid', 'Pending', 'Paid', 'Failed', 'Refunded') DEFAULT 'Unpaid',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if (mysqli_query($GLOBALS['conn'], $create_orders)) {
            echo "✅ Orders table created\n";
        } else {
            echo "❌ Error creating orders table: " . mysqli_error($GLOBALS['conn']) . "\n";
        }
    }
    
    echo "\nChecking PayMongo tables...\n";
    
    // Check payments table
    $result = mysqli_query($GLOBALS['conn'], 'SHOW TABLES LIKE "payments"');
    if (mysqli_num_rows($result) > 0) {
        echo "✅ Payments table exists\n";
    } else {
        echo "❌ Payments table missing\n";
    }
    
    // Check paymongo_webhooks table
    $result = mysqli_query($GLOBALS['conn'], 'SHOW TABLES LIKE "paymongo_webhooks"');
    if (mysqli_num_rows($result) > 0) {
        echo "✅ PayMongo webhooks table exists\n";
    } else {
        echo "❌ PayMongo webhooks table missing\n";
    }
    
    // Check payment_logs table
    $result = mysqli_query($GLOBALS['conn'], 'SHOW TABLES LIKE "payment_logs"');
    if (mysqli_num_rows($result) > 0) {
        echo "✅ Payment logs table exists\n";
    } else {
        echo "❌ Payment logs table missing\n";
    }
    
} else {
    echo "❌ Database not connected\n";
}

echo "\n=== Check Complete ===\n";
?>