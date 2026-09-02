-- =====================================================
-- Add Product Details Enhancement Columns
-- Adds support for: description, specifications, 
-- multiple images, and video URLs
-- =====================================================

-- Add description column for detailed product information
ALTER TABLE inventory 
ADD COLUMN IF NOT EXISTS description TEXT DEFAULT NULL 
AFTER image;

-- Add specifications column (stores JSON format for flexible specs)
ALTER TABLE inventory 
ADD COLUMN IF NOT EXISTS specifications TEXT DEFAULT NULL 
AFTER description;

-- Add additional_images column (stores JSON array of image paths)
ALTER TABLE inventory 
ADD COLUMN IF NOT EXISTS additional_images TEXT DEFAULT NULL 
AFTER specifications;

-- Add video_url column for product demo/promo videos
ALTER TABLE inventory 
ADD COLUMN IF NOT EXISTS video_url VARCHAR(500) DEFAULT NULL 
AFTER additional_images;

-- Add dimensions column for product size information
ALTER TABLE inventory 
ADD COLUMN IF NOT EXISTS dimensions VARCHAR(255) DEFAULT NULL 
AFTER video_url;

-- Add weight column for shipping calculations
ALTER TABLE inventory 
ADD COLUMN IF NOT EXISTS weight VARCHAR(100) DEFAULT NULL 
AFTER dimensions;

-- Add category_details for enhanced categorization
ALTER TABLE inventory 
ADD COLUMN IF NOT EXISTS category_details VARCHAR(255) DEFAULT NULL 
AFTER weight;

SELECT 'Product details columns added successfully!' AS Status;
