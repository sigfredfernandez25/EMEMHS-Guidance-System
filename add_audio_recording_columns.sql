-- Add audio recording columns to complaints_concerns table
-- Run this SQL script in your database to add audio recording support

ALTER TABLE `complaints_concerns` 
ADD COLUMN `audio_recording` MEDIUMBLOB DEFAULT NULL AFTER `mime_type`,
ADD COLUMN `audio_mime_type` VARCHAR(100) DEFAULT NULL AFTER `audio_recording`,
ADD COLUMN `audio_duration` INT DEFAULT NULL COMMENT 'Duration in seconds' AFTER `audio_mime_type`;

-- Update comment for clarity
ALTER TABLE `complaints_concerns` 
MODIFY COLUMN `audio_duration` INT DEFAULT NULL COMMENT 'Audio recording duration in seconds';
