-- User Seeder for EMEMHS Guidance System
-- This script creates initial users for testing

-- Insert Admin User
INSERT INTO users (email, password, role) 
VALUES ('admin@gmail.com', '$2y$10$fMpjwjEc3vgllX.6/6k0DuEC0vh9F8BUJJMc.PurOiSNZg4OwECS6', 'admin');



-- Insert Student User 1
INSERT INTO users (email, password, role) 
VALUES ('cataancarlnavid@gmail.com', '$2y$10$L/3k.NK4s/44dMy/KC4fWe/pl.mTPkw3RCXCMVOTncaf8ZdVAuE4G', 'student');

-- Get the first student user ID
SET @student1_user_id = LAST_INSERT_ID();

-- Insert Student record 1
INSERT INTO students (user_id, first_name, middle_name, last_name, grade_level, section, email, phone_number, address)
VALUES (@student1_user_id, 'Carl Navid', '', 'Cataan', '11', 'STEM-A', 'cataancarlnavid@gmail.com', '09987654321', '123 Sample Street, City');

-- Insert Student User 2
INSERT INTO users (email, password, role) 
VALUES ('sigfredofernandez25@gmail.com', '$2y$10$1M/yU.zAMffaLVFDKgxNguppNu7tBCSKJ5TyQl0rFKz.orC1v3p6u', 'student');

-- Get the second student user ID
SET @student2_user_id = LAST_INSERT_ID();

-- Insert Student record 2
INSERT INTO students (user_id, first_name, middle_name, last_name, grade_level, section, email, phone_number, address)
VALUES (@student2_user_id, 'Sigfredo', '', 'Fernandez', '12', 'ABM-B', 'sigfredofernandez25@gmail.com', '09123987654', '456 Another Street, City');

-- Display success message
SELECT 'Users seeded successfully!' as message;
SELECT 'Admin: admin@gmail.com / admin123' as admin_credentials;
SELECT 'Student 1: cataancarlnavid@gmail.com / carl123' as student1_credentials;
SELECT 'Student 2: sigfredofernandez25@gmail.com / sigfred123' as student2_credentials;