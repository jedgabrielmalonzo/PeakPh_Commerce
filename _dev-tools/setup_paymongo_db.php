<?php
// Database Schema Update Script
require_once('includes/db.php');

echo "Starting PayMongo Database Schema Updates...\n";

// SQL commands to execute
$sql_commands = [
    // 1. Update payments table
    "ALTER TABLE payments ADD COLUMN IF NOT EXISTS paymongo_payment_intent_id VARCHAR(100) DEFAULT NULL",
    "ALTER TABLE payments ADD COLUMN IF NOT EXISTS paymongo_source_id VARCHAR(100) DEFAULT NULL", 
    "ALTER TABLE payments ADD COLUMN IF NOT EXISTS gateway_fee DECIMAL(10,2) DEFAULT 0.00",
    
    // 2. Update payment_method enum
    "ALTER TABLE payments MODIFY COLUMN payment_method ENUM('cod','gcash','paymaya','bank_transfer','card','paymongo_gcash','paymongo_card') NOT NULL",
    
    // 3. Add indexes
    "CREATE INDEX IF NOT EXISTS idx_payments_intent ON payments(paymongo_payment_intent_id)",
    "CREATE INDEX IF NOT EXISTS idx_payments_source ON payments(paymongo_source_id)",
    
    // 4. Create webhooks table
    "CREATE TABLE IF NOT EXISTS paymongo_webhooks (
        id INT(11) PRIMARY KEY AUTO_INCREMENT,
        webhook_id VARCHAR(100) NOT NULL,
        event_type VARCHAR(50) NOT NULL,
        payment_intent_id VARCHAR(100) DEFAULT NULL,
        source_id VARCHAR(100) DEFAULT NULL,
        status VARCHAR(30) NOT NULL,
        payload TEXT NOT NULL,
        processed TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        processed_at TIMESTAMP NULL DEFAULT NULL,
        INDEX idx_webhook_intent (payment_intent_id),
        INDEX idx_webhook_status (status),
        INDEX idx_webhook_processed (processed),
        INDEX idx_webhook_created (created_at)
    )",
    
    // 5. Create payment logs table
    "CREATE TABLE IF NOT EXISTS payment_logs (
        id INT(11) PRIMARY KEY AUTO_INCREMENT,
        payment_id INT(11) DEFAULT NULL,
        order_id INT(11) DEFAULT NULL,
        action VARCHAR(50) NOT NULL,
        status VARCHAR(30) NOT NULL,
        gateway_response TEXT DEFAULT NULL,
        error_message TEXT DEFAULT NULL,
        ip_address VARCHAR(45) DEFAULT NULL,
        user_agent TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_payment_logs_payment (payment_id),
        INDEX idx_payment_logs_order (order_id),
        INDEX idx_payment_logs_status (status),
        INDEX idx_payment_logs_created (created_at)
    )",
    
    // 6. Update orders table
    "ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_status ENUM('Unpaid','Pending','Paid','Failed','Refunded') DEFAULT 'Unpaid'",
    "CREATE INDEX IF NOT EXISTS idx_orders_payment_status ON orders(payment_status)"
];

$success_count = 0;
$total_count = count($sql_commands);

foreach ($sql_commands as $index => $sql) {
    try {
        echo "Executing command " . ($index + 1) . "/$total_count: ";
        
        if ($conn->query($sql)) {
            echo "✅ SUCCESS\n";
            $success_count++;
        } else {
            echo "❌ ERROR: " . $conn->error . "\n";
        }
    } catch (Exception $e) {
        echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "Total commands: $total_count\n";
echo "Successful: $success_count\n";
echo "Failed: " . ($total_count - $success_count) . "\n";

if ($success_count == $total_count) {
    echo "\n🎉 All database updates completed successfully!\n";
} else {
    echo "\n⚠️  Some updates failed. Please check the errors above.\n";
}

$conn->close();
?>