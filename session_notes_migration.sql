-- Session Notes Migration Script
-- This script creates the session_notes table and migrates existing complaints to Session 1

-- Create session_notes table
CREATE TABLE IF NOT EXISTS session_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    session_number INT NOT NULL DEFAULT 1,
    session_date DATE NOT NULL,
    session_time TIME NOT NULL,
    
    -- Presenting Problems (from initial complaint or new session)
    presenting_problem_1 TEXT,
    presenting_problem_2 TEXT,
    presenting_problem_3 TEXT,
    
    -- Session Details
    general_observations TEXT,
    session_summary TEXT NOT NULL,
    action_taken TEXT,
    follow_up_recommendations TEXT,
    next_appointment_date DATE,
    next_appointment_time TIME,
    
    -- Counselor Information
    counselor_name VARCHAR(255),
    counselor_id INT,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (complaint_id) REFERENCES complaints_concerns(id) ON DELETE CASCADE,
    
    -- Ensure unique session numbers per complaint
    UNIQUE KEY unique_session (complaint_id, session_number),
    
    -- Indexes for performance
    INDEX idx_complaint_id (complaint_id),
    INDEX idx_session_date (session_date),
    INDEX idx_counselor_id (counselor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migrate existing complaints to Session 1
-- Only migrate complaints that have been scheduled or resolved (have admin interaction)
INSERT INTO session_notes (
    complaint_id,
    session_number,
    session_date,
    session_time,
    presenting_problem_1,
    general_observations,
    session_summary,
    action_taken,
    counselor_name
)
SELECT 
    cc.id as complaint_id,
    1 as session_number,
    COALESCE(cc.scheduled_date, cc.date_created) as session_date,
    COALESCE(cc.scheduled_time, cc.time_created) as session_time,
    cc.description as presenting_problem_1,
    CONCAT('Initial complaint submission. Type: ', cc.type, '. Severity: ', COALESCE(cc.severity, 'medium')) as general_observations,
    COALESCE(cc.description, 'Initial complaint submitted') as session_summary,
    cc.admin_remark as action_taken,
    'System Migration' as counselor_name
FROM complaints_concerns cc
WHERE cc.status IN ('scheduled', 'resolved')
AND NOT EXISTS (
    SELECT 1 FROM session_notes sn WHERE sn.complaint_id = cc.id
);

-- Add session_count column to complaints_concerns for quick reference
ALTER TABLE complaints_concerns 
ADD COLUMN IF NOT EXISTS session_count INT DEFAULT 0;

-- Update session counts
UPDATE complaints_concerns cc
SET session_count = (
    SELECT COUNT(*) 
    FROM session_notes sn 
    WHERE sn.complaint_id = cc.id
);

-- Add counselor assignment to complaints
ALTER TABLE complaints_concerns
ADD COLUMN IF NOT EXISTS assigned_counselor_id INT,
ADD COLUMN IF NOT EXISTS assigned_counselor_name VARCHAR(255);

-- Note: VIEW creation skipped due to hosting restrictions
-- Session data will be accessed via JOIN queries in the application code

-- Success message
SELECT 'Session notes table created and data migrated successfully!' as status;
