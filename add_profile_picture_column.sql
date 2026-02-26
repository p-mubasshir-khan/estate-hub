USE estate_hub;

-- Add profile_picture column if it doesn't exist
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS profile_picture VARCHAR(255) DEFAULT NULL; 