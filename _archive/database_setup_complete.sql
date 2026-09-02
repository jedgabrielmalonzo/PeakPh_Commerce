-- =====================================================
-- PeakPH Commerce - Complete Database Setup Script
-- Updated: 2025-10-21
-- Includes all current tables with PayMongo integration
-- =====================================================

-- Create the database
CREATE DATABASE IF NOT EXISTS peakph_db;
USE peakph_db;

-- =====================================================
-- 1. USERS TABLE (User Management)
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
  id int(11) NOT NULL AUTO_INCREMENT,
  username varchar(100) NOT NULL,
  email varchar(150) NOT NULL UNIQUE,
  password varchar(255) NOT NULL,
  role enum('User','Admin') NOT NULL DEFAULT 'User',
  status enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 2. INVENTORY TABLE (Product Management)
-- =====================================================
CREATE TABLE IF NOT EXISTS inventory (
  id int(11) NOT NULL AUTO_INCREMENT,
  product_name varchar(255) NOT NULL,
  price decimal(10,2) NOT NULL,
  stock int(11) NOT NULL,
  tag varchar(50) DEFAULT NULL,
  image varchar(255) DEFAULT NULL,
  label varchar(50) NOT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 3. PRODUCTS TABLE (Catalog Compatibility)
-- =====================================================
CREATE TABLE IF NOT EXISTS products (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  price decimal(10,2) NOT NULL,
  image_path varchar(255) DEFAULT NULL,
  category varchar(100) DEFAULT NULL,
  description text DEFAULT NULL,
  stock int(11) DEFAULT 0,
  status enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 4. ORDERS TABLE (Order Management)
-- =====================================================
CREATE TABLE IF NOT EXISTS orders (
  id int(11) NOT NULL AUTO_INCREMENT,
  order_id varchar(50) DEFAULT NULL UNIQUE,
  user_id int(11) DEFAULT NULL,
  customer_name varchar(255) NOT NULL,
  customer_email varchar(150) NOT NULL,
  customer_phone varchar(20) DEFAULT NULL,
  total_amount decimal(10,2) NOT NULL,
  status enum('Pending','Processing','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
  payment_status enum('Unpaid','Pending','Paid','Failed','Refunded') DEFAULT 'Unpaid',
  shipping_address text NOT NULL,
  billing_address text DEFAULT NULL,
  payment_method varchar(50) DEFAULT NULL,
  order_date timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 5. ORDER ITEMS TABLE (Order Details)
-- =====================================================
CREATE TABLE IF NOT EXISTS order_items (
  id int(11) NOT NULL AUTO_INCREMENT,
  order_id int(11) NOT NULL,
  product_id int(11) NOT NULL,
  product_name varchar(255) NOT NULL,
  quantity int(11) NOT NULL,
  price decimal(10,2) NOT NULL,
  total decimal(10,2) NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES inventory(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 6. PAYMENTS TABLE (Payment Processing)
-- =====================================================
CREATE TABLE IF NOT EXISTS payments (
  id int(11) NOT NULL AUTO_INCREMENT,
  order_id int(11) NOT NULL,
  user_id int(11) DEFAULT NULL,
  payment_method enum('cod','gcash','paymaya','bank_transfer','card','paymongo_gcash','paymongo_card') NOT NULL DEFAULT 'cod',
  amount decimal(10,2) NOT NULL,
  gateway_fee decimal(10,2) DEFAULT 0.00,
  transaction_reference varchar(100) DEFAULT NULL,
  paymongo_payment_intent_id varchar(100) DEFAULT NULL,
  paymongo_source_id varchar(100) DEFAULT NULL,
  status enum('Pending','Processing','Completed','Failed','Refunded','Cancelled') NOT NULL DEFAULT 'Pending',
  payment_details text DEFAULT NULL,
  paid_at datetime DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id),
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 7. PAYMONGO WEBHOOKS TABLE (Webhook Management)
-- =====================================================
CREATE TABLE IF NOT EXISTS paymongo_webhooks (
  id int(11) NOT NULL AUTO_INCREMENT,
  webhook_id varchar(100) NOT NULL,
  event_type varchar(50) NOT NULL,
  payment_intent_id varchar(100) DEFAULT NULL,
  source_id varchar(100) DEFAULT NULL,
  status varchar(30) NOT NULL,
  payload text NOT NULL,
  processed tinyint(1) DEFAULT 0,
  processed_at timestamp NULL DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY uk_webhook_id (webhook_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 8. PAYMENT LOGS TABLE (Transaction Logging)
-- =====================================================
CREATE TABLE IF NOT EXISTS payment_logs (
  id int(11) NOT NULL AUTO_INCREMENT,
  payment_id int(11) DEFAULT NULL,
  order_id int(11) DEFAULT NULL,
  action varchar(50) NOT NULL,
  status varchar(30) NOT NULL,
  gateway_response text DEFAULT NULL,
  error_message text DEFAULT NULL,
  ip_address varchar(45) DEFAULT NULL,
  user_agent text DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 9. AUDIT TRAIL TABLE (System Activity Tracking)
-- =====================================================
CREATE TABLE IF NOT EXISTS audit_trail (
  id int(11) NOT NULL AUTO_INCREMENT,
  table_name varchar(100) NOT NULL,
  record_id int(11) NOT NULL,
  action enum('INSERT','UPDATE','DELETE') NOT NULL,
  old_values text DEFAULT NULL,
  new_values text DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  user_email varchar(150) DEFAULT NULL,
  timestamp timestamp NOT NULL DEFAULT current_timestamp(),
  ip_address varchar(45) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 10. CAROUSEL TABLE (Homepage Banners)
-- =====================================================
CREATE TABLE IF NOT EXISTS carousel (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(255) NOT NULL,
  description text DEFAULT NULL,
  image_path varchar(255) NOT NULL,
  link_url varchar(255) DEFAULT NULL,
  is_active tinyint(1) NOT NULL DEFAULT 1,
  display_order int(11) DEFAULT 0,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 11. BESTSELLERS TABLE (Featured Products)
-- =====================================================
CREATE TABLE IF NOT EXISTS bestsellers (
  id int(11) NOT NULL AUTO_INCREMENT,
  product_id int(11) NOT NULL,
  display_order int(11) DEFAULT 0,
  is_active tinyint(1) NOT NULL DEFAULT 1,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  FOREIGN KEY (product_id) REFERENCES inventory(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 12. NEW ARRIVALS TABLE (Latest Products)
-- =====================================================
CREATE TABLE IF NOT EXISTS new_arrivals (
  id int(11) NOT NULL AUTO_INCREMENT,
  product_id int(11) NOT NULL,
  display_order int(11) DEFAULT 0,
  is_active tinyint(1) NOT NULL DEFAULT 1,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  FOREIGN KEY (product_id) REFERENCES inventory(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 13. CREATE INDEXES FOR PERFORMANCE
-- =====================================================

-- Users table indexes
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_status ON users(status);

-- Inventory table indexes  
CREATE INDEX idx_inventory_tag ON inventory(tag);
CREATE INDEX idx_inventory_stock ON inventory(stock);
CREATE INDEX idx_inventory_created ON inventory(created_at);
CREATE INDEX idx_inventory_price ON inventory(price);

-- Products table indexes
CREATE INDEX idx_products_category ON products(category);
CREATE INDEX idx_products_status ON products(status);
CREATE INDEX idx_products_price ON products(price);

-- Orders table indexes
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_date ON orders(order_date);
CREATE INDEX idx_orders_order_id ON orders(order_id);
CREATE INDEX idx_orders_payment_status ON orders(payment_status);
CREATE INDEX idx_orders_customer_email ON orders(customer_email);

-- Payments table indexes
CREATE INDEX idx_payments_order ON payments(order_id);
CREATE INDEX idx_payments_status ON payments(status);
CREATE INDEX idx_payments_method ON payments(payment_method);
CREATE INDEX idx_payments_reference ON payments(transaction_reference);
CREATE INDEX idx_payments_intent ON payments(paymongo_payment_intent_id);
CREATE INDEX idx_payments_source ON payments(paymongo_source_id);
CREATE INDEX idx_payments_created ON payments(created_at);

-- PayMongo webhooks indexes
CREATE INDEX idx_webhook_intent ON paymongo_webhooks(payment_intent_id);
CREATE INDEX idx_webhook_status ON paymongo_webhooks(status);
CREATE INDEX idx_webhook_processed ON paymongo_webhooks(processed);
CREATE INDEX idx_webhook_created ON paymongo_webhooks(created_at);

-- Payment logs indexes
CREATE INDEX idx_payment_logs_payment ON payment_logs(payment_id);
CREATE INDEX idx_payment_logs_order ON payment_logs(order_id);
CREATE INDEX idx_payment_logs_status ON payment_logs(status);
CREATE INDEX idx_payment_logs_created ON payment_logs(created_at);
CREATE INDEX idx_payment_logs_action ON payment_logs(action);

-- Audit trail indexes
CREATE INDEX idx_audit_table_record ON audit_trail(table_name, record_id);
CREATE INDEX idx_audit_timestamp ON audit_trail(timestamp);
CREATE INDEX idx_audit_user ON audit_trail(user_id);

-- Carousel indexes
CREATE INDEX idx_carousel_active ON carousel(is_active);
CREATE INDEX idx_carousel_order ON carousel(display_order);

-- =====================================================
-- 14. INSERT SAMPLE DATA
-- =====================================================

-- Sample Admin User (password: admin123)
INSERT IGNORE INTO users (username, email, password, role, status) VALUES 
('admin', 'admin@peakph.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'Active');

-- Sample Regular User (password: user123)
INSERT IGNORE INTO users (username, email, password, role, status) VALUES 
('testuser', 'user@peakph.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'User', 'Active');

-- Sample Inventory Data
INSERT IGNORE INTO inventory (product_name, price, stock, tag, image, label) VALUES 
('Professional Camping Tent', 2500.00, 25, 'tent', 'Assets/Gallery_Images/TentSample.jpg', 'Featured'),
('Portable Cooking Set', 750.00, 40, 'cooking', 'Assets/Gallery_Images/CookingGearSample.png', 'Popular'),
('Hiking Backpack Pro', 1800.00, 15, 'equipment', 'Assets/Gallery_Images/HikingBackpackSample.png', 'New'),
('Travel Boots Waterproof', 1200.00, 30, 'equipment', 'Assets/Gallery_Images/TravelBootsSample.png', 'Bestseller'),
('Survival Kit Complete', 950.00, 20, 'equipment', 'Assets/Gallery_Images/Survival Kit Sample.png', 'Essential'),
('Camping Stove Portable', 650.00, 35, 'cooking', 'Assets/Gallery_Images/Camping Stove Sample.png', 'Compact');

-- Sample Products for Catalog Compatibility
INSERT IGNORE INTO products (name, price, image_path, category, description, stock, status) VALUES 
('Professional Camping Tent', 2500.00, 'Assets/Gallery_Images/TentSample.jpg', 'tent', 'Professional grade camping tent for extreme weather conditions', 25, 'Active'),
('Portable Cooking Set', 750.00, 'Assets/Gallery_Images/CookingGearSample.png', 'cooking', 'Complete portable cooking set for outdoor adventures', 40, 'Active'),
('Hiking Backpack Pro', 1800.00, 'Assets/Gallery_Images/HikingBackpackSample.png', 'equipment', 'Professional hiking backpack with advanced features', 15, 'Active'),
('Travel Boots Waterproof', 1200.00, 'Assets/Gallery_Images/TravelBootsSample.png', 'equipment', 'Durable waterproof boots for all terrains', 30, 'Active'),
('Survival Kit Complete', 950.00, 'Assets/Gallery_Images/Survival Kit Sample.png', 'equipment', 'Comprehensive survival kit for outdoor emergencies', 20, 'Active'),
('Camping Stove Portable', 650.00, 'Assets/Gallery_Images/Camping Stove Sample.png', 'cooking', 'Lightweight and efficient camping stove', 35, 'Active');

-- Sample Carousel Data
INSERT IGNORE INTO carousel (title, description, image_path, is_active, display_order) VALUES 
('Welcome to PeakPH', 'Your ultimate destination for camping gear', 'Assets/Carousel_Picts/DeaksV2.png', 1, 1),
('Best Deals Available', 'Check out our amazing deals on camping equipment', 'Assets/Carousel_Picts/Deals.png', 1, 2),
('Special Vouchers', 'Get exclusive vouchers for your next adventure', 'Assets/Carousel_Picts/Vouchers.png', 1, 3);

-- =====================================================
-- 15. SETUP VERIFICATION
-- =====================================================

-- Show database info
SELECT 'PeakPH Commerce Database Setup Completed Successfully!' AS status;
SELECT 'Total Tables Created:' AS info, COUNT(*) AS count FROM information_schema.tables WHERE table_schema = 'peakph_db';

-- Show table summary
SELECT 
    table_name as 'Table Name',
    table_rows as 'Rows',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) as 'Size (MB)'
FROM information_schema.tables 
WHERE table_schema = 'peakph_db' 
ORDER BY table_name;

-- =====================================================
-- 16. PAYMONGO CONFIGURATION NOTES
-- =====================================================
/*
PAYMONGO SETUP CHECKLIST:

1. API Keys Configuration (config/paymongo.php):
   - Test Secret Key: sk_test_xxxxxxxxxxxxx
   - Test Public Key: pk_test_xxxxxxxxxxxxx
   - Production keys when going live

2. Webhook Endpoints:
   - Success URL: your-domain/payment/success.php
   - Failed URL: your-domain/payment/failed.php
   - Webhook URL: your-domain/webhooks/paymongo.php

3. Payment Methods Supported:
   - GCash (paymongo_gcash)
   - Credit/Debit Card (paymongo_card)
   - Cash on Delivery (cod)

4. Fee Structure:
   - GCash: 3.5% + ₱15 fixed fee
   - Card: 3.5% + ₱15 fixed fee
   - COD: No additional fees

5. Testing:
   - Use test API keys for development
   - Test with small amounts (₱100-500)
   - Verify webhook responses
   - Check payment status updates

6. Production Deployment:
   - Update to production API keys
   - Configure SSL certificates
   - Set up proper webhook URLs
   - Enable error logging and monitoring
*/

SELECT 'Database setup complete! PayMongo integration ready for testing.' AS final_message;