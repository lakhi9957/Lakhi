-- Manual fix for admin user login
-- Run this SQL to create admin user with correct password hash

USE school_db;

-- Delete existing admin user
DELETE FROM users WHERE email = 'admin@school.edu';

-- Insert admin user with correct PHP password hash for 'admin123'
INSERT INTO users (name, email, password, role, verified) 
VALUES ('Admin User', 'admin@school.edu', '$2y$10$7rWIEXKGLnZ/YH6RIXQCaeEsEKOp6RRlJvZI2tO4nK4K4WJCb2i8W', 'admin', TRUE);

-- Verify the user was created
SELECT * FROM users WHERE role = 'admin';