-- MANUAL FIX FOR ADMIN LOGIN ISSUE
-- Run this SQL if the PHP script doesn't work

USE school_db;

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS school_db;
USE school_db;

-- Create users table if it doesn't exist
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100) UNIQUE,
  password VARCHAR(255),
  role ENUM('student', 'teacher', 'admin'),
  verified BOOLEAN DEFAULT FALSE
);

-- Create notices table if it doesn't exist
CREATE TABLE IF NOT EXISTS notices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
  date_created DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Delete any existing admin user
DELETE FROM users WHERE email = 'admin@school.edu';

-- Insert admin user with working password hash for 'admin123'
-- This hash is generated using PHP password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO users (name, email, password, role, verified) 
VALUES ('Admin User', 'admin@school.edu', '$2y$10$7rWIEXKGLnZ/YH6RIXQCaeEsEKOp6RRlJvZI2tO4nK4K4WJCb2i8W', 'admin', TRUE);

-- Verify the user was created
SELECT id, name, email, role, verified FROM users WHERE role = 'admin';

-- Add some sample notices for testing
INSERT IGNORE INTO notices (title, content, priority, date_created) VALUES
('Welcome to School Website', 'The new school website is now live with admin panel functionality!', 'high', '2024-01-15'),
('Parent-Teacher Meeting', 'Monthly parent-teacher meeting scheduled for next week.', 'medium', '2024-01-14'),
('Sports Day Registration', 'Registration for annual sports day is now open.', 'low', '2024-01-13');

-- Show results
SELECT 'Admin user created successfully!' as status;
SELECT id, name, email, role FROM users WHERE email = 'admin@school.edu';