-- Add first_name and last_name to user_profiles table
ALTER TABLE user_profiles 
ADD COLUMN first_name VARCHAR(100) DEFAULT NULL AFTER user_id,
ADD COLUMN last_name VARCHAR(100) DEFAULT NULL AFTER first_name;

-- Show updated structure
DESCRIBE user_profiles;

SELECT 'Migration completed: Added first_name and last_name to user_profiles table' as Status;
