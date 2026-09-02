-- User Profile Information Table
-- This table stores additional user information for checkout pre-fill

CREATE TABLE IF NOT EXISTS `user_profiles` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `shipping_address` TEXT DEFAULT NULL,
  `shipping_address_2` VARCHAR(255) DEFAULT NULL,
  `shipping_city` VARCHAR(100) DEFAULT NULL,
  `shipping_province` VARCHAR(100) DEFAULT NULL,
  `shipping_postal_code` VARCHAR(10) DEFAULT NULL,
  `shipping_country` VARCHAR(100) DEFAULT 'Philippines',
  `map_latitude` DECIMAL(10, 8) DEFAULT NULL,
  `map_longitude` DECIMAL(11, 8) DEFAULT NULL,
  `map_address` TEXT DEFAULT NULL,
  `billing_same_as_shipping` TINYINT(1) DEFAULT 1,
  `billing_address` TEXT DEFAULT NULL,
  `billing_address_2` VARCHAR(255) DEFAULT NULL,
  `billing_city` VARCHAR(100) DEFAULT NULL,
  `billing_province` VARCHAR(100) DEFAULT NULL,
  `billing_postal_code` VARCHAR(10) DEFAULT NULL,
  `billing_country` VARCHAR(100) DEFAULT 'Philippines',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `fk_user_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add index for faster lookups
CREATE INDEX idx_user_profiles_user_id ON user_profiles(user_id);
