CREATE DATABASE IF NOT EXISTS tuition_center;
USE tuition_center;

-- Users table (students, teachers, admins, parents)
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(15),
  address TEXT,
  role ENUM('student', 'teacher', 'admin', 'parent') NOT NULL,
  profile_image VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  verified BOOLEAN DEFAULT FALSE
);

-- Students table
CREATE TABLE students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  student_id VARCHAR(20) UNIQUE,
  class VARCHAR(50),
  section VARCHAR(10),
  roll_number VARCHAR(20),
  date_of_birth DATE,
  parent_id INT,
  emergency_contact VARCHAR(15),
  admission_date DATE,
  status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (parent_id) REFERENCES users(id)
);

-- Teachers table
CREATE TABLE teachers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  teacher_id VARCHAR(20) UNIQUE,
  qualification VARCHAR(255),
  experience_years INT,
  specialization VARCHAR(100),
  salary DECIMAL(10,2),
  join_date DATE,
  status ENUM('active', 'inactive') DEFAULT 'active',
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Subjects table
CREATE TABLE subjects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  code VARCHAR(20) UNIQUE,
  description TEXT,
  class_level VARCHAR(50),
  credits INT DEFAULT 1
);

-- Classes table
CREATE TABLE classes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  section VARCHAR(10),
  teacher_id INT,
  subject_id INT,
  room_number VARCHAR(20),
  capacity INT,
  schedule_time TIME,
  schedule_days VARCHAR(20), -- JSON or comma-separated
  fee DECIMAL(10,2),
  FOREIGN KEY (teacher_id) REFERENCES teachers(id),
  FOREIGN KEY (subject_id) REFERENCES subjects(id)
);

-- Student enrollments
CREATE TABLE enrollments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT,
  class_id INT,
  enrollment_date DATE,
  status ENUM('active', 'completed', 'dropped') DEFAULT 'active',
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (class_id) REFERENCES classes(id)
);

-- Attendance table
CREATE TABLE attendance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT,
  class_id INT,
  date DATE,
  status ENUM('present', 'absent', 'late') DEFAULT 'present',
  remarks TEXT,
  marked_by INT,
  marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (class_id) REFERENCES classes(id),
  FOREIGN KEY (marked_by) REFERENCES users(id)
);

-- Fees table
CREATE TABLE fees (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT,
  class_id INT,
  amount DECIMAL(10,2),
  due_date DATE,
  paid_amount DECIMAL(10,2) DEFAULT 0,
  payment_date DATE,
  status ENUM('pending', 'paid', 'overdue') DEFAULT 'pending',
  payment_method VARCHAR(50),
  transaction_id VARCHAR(100),
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (class_id) REFERENCES classes(id)
);

-- Assignments table
CREATE TABLE assignments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  class_id INT,
  teacher_id INT,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  due_date DATE,
  max_marks INT,
  attachment VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (class_id) REFERENCES classes(id),
  FOREIGN KEY (teacher_id) REFERENCES teachers(id)
);

-- Assignment submissions
CREATE TABLE assignment_submissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  assignment_id INT,
  student_id INT,
  submission_text TEXT,
  attachment VARCHAR(255),
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  marks_obtained INT,
  feedback TEXT,
  graded_by INT,
  graded_at TIMESTAMP,
  FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (graded_by) REFERENCES teachers(id)
);

-- Exams table
CREATE TABLE exams (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  class_id INT,
  subject_id INT,
  exam_date DATE,
  start_time TIME,
  duration INT, -- in minutes
  max_marks INT,
  description TEXT,
  created_by INT,
  FOREIGN KEY (class_id) REFERENCES classes(id),
  FOREIGN KEY (subject_id) REFERENCES subjects(id),
  FOREIGN KEY (created_by) REFERENCES teachers(id)
);

-- Exam results
CREATE TABLE exam_results (
  id INT AUTO_INCREMENT PRIMARY KEY,
  exam_id INT,
  student_id INT,
  marks_obtained INT,
  grade VARCHAR(5),
  remarks TEXT,
  FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Announcements
CREATE TABLE announcements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  content TEXT,
  target_audience ENUM('all', 'students', 'teachers', 'parents'),
  priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  expires_at TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Timetable
CREATE TABLE timetable (
  id INT AUTO_INCREMENT PRIMARY KEY,
  class_id INT,
  day_of_week INT, -- 1=Monday, 7=Sunday
  start_time TIME,
  end_time TIME,
  room_number VARCHAR(20),
  FOREIGN KEY (class_id) REFERENCES classes(id)
);

-- Messages/Communication
CREATE TABLE messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sender_id INT,
  receiver_id INT,
  subject VARCHAR(255),
  message TEXT,
  sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  read_at TIMESTAMP,
  FOREIGN KEY (sender_id) REFERENCES users(id),
  FOREIGN KEY (receiver_id) REFERENCES users(id)
);

-- Insert sample admin user
INSERT INTO users (name, email, password, role, verified) VALUES 
('Admin User', 'admin@tuitioncenter.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', TRUE);

-- Insert sample subjects
INSERT INTO subjects (name, code, class_level) VALUES 
('Mathematics', 'MATH101', 'Grade 10'),
('Physics', 'PHY101', 'Grade 10'),
('Chemistry', 'CHEM101', 'Grade 10'),
('English', 'ENG101', 'Grade 10'),
('Biology', 'BIO101', 'Grade 10');