<?php
require_once 'includes/db.php';

echo "Checking orders table structure:\n\n";

try {
    $result = $conn->query('DESCRIBE orders');
    
    if ($result) {
        echo "Orders table columns:\n";
        echo "Column\t\t\tType\n";
        echo "------\t\t\t----\n";
        
        while ($row = $result->fetch_assoc()) {
            echo $row['Field'] . "\t\t\t" . $row['Type'] . "\n";
        }
    } else {
        echo "Error: " . $conn->error . "\n";
    }
    
    echo "\n\nChecking if orders table exists:\n";
    $check = $conn->query("SHOW TABLES LIKE 'orders'");
    
    if ($check && $check->num_rows > 0) {
        echo "✅ Orders table exists\n";
    } else {
        echo "❌ Orders table does not exist - need to create it\n";
        
        // Create orders table
        $create_orders = "
        CREATE TABLE orders (
            id INT(11) PRIMARY KEY AUTO_INCREMENT,
            user_id INT(11) DEFAULT NULL,
            customer_name VARCHAR(255) NOT NULL,
            customer_email VARCHAR(150) NOT NULL,
            customer_phone VARCHAR(20) DEFAULT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            status ENUM('Pending','Processing','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
            shipping_address TEXT NOT NULL,
            order_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            payment_status ENUM('Unpaid','Pending','Paid','Failed','Refunded') DEFAULT 'Unpaid',
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_orders_status (status),
            INDEX idx_orders_date (order_date),
            INDEX idx_orders_payment_status (payment_status)
        )";
        
        if ($conn->query($create_orders)) {
            echo "✅ Orders table created successfully!\n";
        } else {
            echo "❌ Error creating orders table: " . $conn->error . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn->close();
?>