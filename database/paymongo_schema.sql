-- PayMongo Database Schema Updates
-- Execute these SQL commands in your database

-- 1. Update payments table for PayMongo integration
ALTER TABLE payments ADD COLUMN IF NOT EXISTS paymongo_payment_intent_id VARCHAR(100) DEFAULT NULL;
ALTER TABLE payments ADD COLUMN IF NOT EXISTS paymongo_source_id VARCHAR(100) DEFAULT NULL;
ALTER TABLE payments ADD COLUMN IF NOT EXISTS gateway_fee DECIMAL(10,2) DEFAULT 0.00;
ALTER TABLE payments MODIFY COLUMN payment_method ENUM('cod','gcash','paymaya','bank_transfer','card','paymongo_gcash','paymongo_card') NOT NULL;

-- 2. Add PayMongo specific indexes
CREATE INDEX IF NOT EXISTS idx_payments_intent ON payments(paymongo_payment_intent_id);
CREATE INDEX IF NOT EXISTS idx_payments_source ON payments(paymongo_source_id);

-- 3. Create PayMongo webhooks table
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
);

-- 4. Create payment_logs table for transaction tracking
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
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE SET NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    INDEX idx_payment_logs_payment (payment_id),
    INDEX idx_payment_logs_order (order_id),
    INDEX idx_payment_logs_status (status),
    INDEX idx_payment_logs_created (created_at)
);

-- 5. Update orders table to track payment status
ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_status ENUM('Unpaid','Pending','Paid','Failed','Refunded') DEFAULT 'Unpaid';
CREATE INDEX IF NOT EXISTS idx_orders_payment_status ON orders(payment_status);

-- 6. Sample data for testing (optional)
-- INSERT INTO payments (order_id, user_id, payment_method, amount, status) 
-- VALUES (1, 1, 'paymongo_gcash', 100.00, 'Pending');