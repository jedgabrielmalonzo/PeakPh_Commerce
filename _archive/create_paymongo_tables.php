<?php
// Create PayMongo Payments Table and Updates
require_once('includes/db.php');

echo "Creating PayMongo Database Schema...\n\n";

// Step 1: Create the payments table if it doesn't exist
echo "Step 1: Creating payments table...\n";
$create_payments_table = "
CREATE TABLE IF NOT EXISTS payments (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    order_id INT(11) NOT NULL,
    user_id INT(11) DEFAULT NULL,
    payment_method ENUM('cod','gcash','paymaya','bank_transfer','card','paymongo_gcash','paymongo_card') NOT NULL DEFAULT 'cod',
    amount DECIMAL(10,2) NOT NULL,
    gateway_fee DECIMAL(10,2) DEFAULT 0.00,
    transaction_reference VARCHAR(100) DEFAULT NULL,
    paymongo_payment_intent_id VARCHAR(100) DEFAULT NULL,
    paymongo_source_id VARCHAR(100) DEFAULT NULL,
    status ENUM('Pending','Processing','Completed','Failed','Refunded','Cancelled') NOT NULL DEFAULT 'Pending',
    payment_details TEXT DEFAULT NULL,
    paid_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_payments_order (order_id),
    INDEX idx_payments_status (status),
    INDEX idx_payments_method (payment_method),
    INDEX idx_payments_reference (transaction_reference),
    INDEX idx_payments_intent (paymongo_payment_intent_id),
    INDEX idx_payments_source (paymongo_source_id)
)";

if ($conn->query($create_payments_table)) {
    echo "✅ Payments table created successfully!\n\n";
} else {
    echo "❌ Error creating payments table: " . $conn->error . "\n\n";
}

// Step 2: Create webhooks table
echo "Step 2: Creating PayMongo webhooks table...\n";
$create_webhooks_table = "
CREATE TABLE IF NOT EXISTS paymongo_webhooks (
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
)";

if ($conn->query($create_webhooks_table)) {
    echo "✅ PayMongo webhooks table created successfully!\n\n";
} else {
    echo "❌ Error creating webhooks table: " . $conn->error . "\n\n";
}

// Step 3: Create payment logs table
echo "Step 3: Creating payment logs table...\n";
$create_logs_table = "
CREATE TABLE IF NOT EXISTS payment_logs (
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
)";

if ($conn->query($create_logs_table)) {
    echo "✅ Payment logs table created successfully!\n\n";
} else {
    echo "❌ Error creating payment logs table: " . $conn->error . "\n\n";
}

// Step 4: Update orders table
echo "Step 4: Adding payment_status to orders table...\n";
$update_orders = "ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_status ENUM('Unpaid','Pending','Paid','Failed','Refunded') DEFAULT 'Unpaid'";
if ($conn->query($update_orders)) {
    echo "✅ Orders table updated successfully!\n";
} else {
    echo "❌ Error updating orders table: " . $conn->error . "\n";
}

$create_orders_index = "CREATE INDEX IF NOT EXISTS idx_orders_payment_status ON orders(payment_status)";
if ($conn->query($create_orders_index)) {
    echo "✅ Orders payment status index created!\n\n";
} else {
    echo "❌ Error creating orders index: " . $conn->error . "\n\n";
}

echo "🎉 PayMongo database schema setup completed!\n\n";
echo "Next steps:\n";
echo "1. Update your API keys in config/paymongo.php\n";
echo "2. Update checkout.php with payment UI\n";
echo "3. Test the integration\n\n";

$conn->close();
?>