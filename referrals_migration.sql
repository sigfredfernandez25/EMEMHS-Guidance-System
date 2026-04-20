-- Simple Referrals System Migration
-- Created: 2026-04-20

-- Create referrals table
CREATE TABLE IF NOT EXISTS `referrals` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `complaint_id` INT(11) NOT NULL,
  `student_id` INT(11) NOT NULL,
  `referred_to` VARCHAR(255) NOT NULL,
  `reason` TEXT NOT NULL,
  `referral_date` DATE NOT NULL,
  `referred_by_name` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('pending', 'completed') DEFAULT 'pending',
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_complaint_id` (`complaint_id`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_status` (`status`),
  FOREIGN KEY (`complaint_id`) REFERENCES `complaints_concerns`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
