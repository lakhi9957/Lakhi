CREATE DATABASE IF NOT EXISTS school_db;
USE school_db;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100) UNIQUE,
  password VARCHAR(255),
  role ENUM('student', 'teacher', 'admin') DEFAULT 'student',
  verified BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE jobs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  description TEXT NOT NULL,
  requirements TEXT,
  location VARCHAR(100),
  salary_range VARCHAR(50),
  job_type ENUM('full-time', 'part-time', 'contract', 'internship') DEFAULT 'full-time',
  status ENUM('active', 'inactive', 'closed') DEFAULT 'active',
  posted_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  deadline DATE,
  created_by INT,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE job_applications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  job_id INT,
  applicant_name VARCHAR(100) NOT NULL,
  applicant_email VARCHAR(100) NOT NULL,
  applicant_phone VARCHAR(20),
  resume_path VARCHAR(255),
  cover_letter TEXT,
  application_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status ENUM('pending', 'reviewed', 'shortlisted', 'rejected', 'hired') DEFAULT 'pending',
  FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
);

-- Insert default admin user (password: admin123)
INSERT INTO users (name, email, password, role, verified) VALUES 
('Admin User', 'admin@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', TRUE);