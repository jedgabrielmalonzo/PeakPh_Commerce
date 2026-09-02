-- =====================================================
-- Database Schema Update - Fix Order Items Table
-- PeakPH Commerce - October 21, 2025
-- =====================================================

USE peakph_db;

-- Update order_items table to allow NULL product_id for non-database products
ALTER TABLE order_items MODIFY COLUMN product_id int(11) DEFAULT NULL;

-- Remove the foreign key constraint temporarily to allow NULL values
ALTER TABLE order_items DROP FOREIGN KEY order_items_ibfk_2;

-- Re-add the foreign key constraint that allows NULL
ALTER TABLE order_items ADD CONSTRAINT order_items_ibfk_2 
FOREIGN KEY (product_id) REFERENCES inventory(id) ON DELETE SET NULL;

-- Show the updated table structure
DESCRIBE order_items;

-- Display success message
SELECT 'Order items table updated successfully - product_id now allows NULL values' as message;