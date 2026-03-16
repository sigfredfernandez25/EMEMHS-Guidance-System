-- SQL script to add school ID verification columns to existing students table
-- Run this on your InfinityFree database

-- Add the new columns for school ID verification
ALTER TABLE `students` 
ADD COLUMN `school_id_image` LONGBLOB NULL AFTER `address`,
ADD COLUMN `school_id_mime_type` VARCHAR(100) NULL AFTER `school_id_image`,
ADD COLUMN `is_verified` TINYINT(1) NOT NULL DEFAULT 0 AFTER `school_id_mime_type`;

-- Optional: Create an index on is_verified for faster queries
CREATE INDEX idx_students_verified ON `students` (`is_verified`);

-- Verify the changes
DESCRIBE `students`;