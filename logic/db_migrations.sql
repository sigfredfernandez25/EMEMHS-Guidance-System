-- Create notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reference_id INT NOT NULL,
    reference_type ENUM('complaint', 'lost_item') NOT NULL,
    type ENUM('scheduled', 'resolved', 'found_item', 'new_complaint', 'item_claimed') NOT NULL,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    read_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    date_created DATE NOT NULL,
    time_created TIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_reference_id (reference_id),
    INDEX idx_date_created (date_created),
    INDEX idx_is_read (is_read),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add new columns to lost_items table for claim functionality
ALTER TABLE lost_items
ADD COLUMN claimant_photo MEDIUMBLOB NULL AFTER mime_type,
ADD COLUMN claimant_photo_mime_type VARCHAR(255) NULL AFTER claimant_photo,
ADD COLUMN claimed_at TIMESTAMP NULL AFTER time,
ADD COLUMN claimed_by_student_id INT NULL AFTER claimed_at,
ADD COLUMN claim_evidence TEXT NULL AFTER claimed_by_student_id;

-- Add foreign key constraint for claimed_by_student_id
ALTER TABLE lost_items
ADD CONSTRAINT fk_claimed_by_student
FOREIGN KEY (claimed_by_student_id) REFERENCES students(id) ON DELETE SET NULL;

-- Create index for claimed items
CREATE INDEX idx_claimed_at ON lost_items(claimed_at);
CREATE INDEX idx_claimed_by_student_id ON lost_items(claimed_by_student_id);

-- Add severity column to complaints_concerns table
ALTER TABLE complaints_concerns
ADD COLUMN severity ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium' AFTER type;

-- Create index for severity-based queries
CREATE INDEX idx_severity ON complaints_concerns(severity);
CREATE INDEX idx_severity_status ON complaints_concerns(severity, status);