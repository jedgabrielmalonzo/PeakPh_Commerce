-- Admin Logs Table for Tracking Critical Actions
-- Run this SQL to create the admin_logs table for audit tracking

CREATE TABLE IF NOT EXISTS `admin_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `admin_id` INT(11) DEFAULT NULL,
  `admin_email` VARCHAR(255) NOT NULL,
  `action` VARCHAR(100) NOT NULL COMMENT 'Action type: order_deleted, order_cancelled, user_modified, etc.',
  `details` TEXT DEFAULT NULL COMMENT 'Additional details about the action',
  `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'IP address of admin',
  `user_agent` TEXT DEFAULT NULL COMMENT 'Browser/device information',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Admin action audit log';

-- Add foreign key if admin_users table exists
-- ALTER TABLE `admin_logs` 
-- ADD CONSTRAINT `fk_admin_logs_admin` 
-- FOREIGN KEY (`admin_id`) REFERENCES `admin_users`(`id`) 
-- ON DELETE SET NULL ON UPDATE CASCADE;
