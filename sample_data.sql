-- Sample data for tuition center
USE tuition_center;

-- Insert sample subjects
INSERT INTO subjects (name, code, description, class_level, credits) VALUES
('Mathematics', 'MATH101', 'Basic mathematics for grade 10', 'Grade 10', 3),
('Physics', 'PHY101', 'Introduction to physics concepts', 'Grade 10', 3),
('Chemistry', 'CHEM101', 'Basic chemistry principles', 'Grade 10', 3),
('Biology', 'BIO101', 'Introduction to biological sciences', 'Grade 10', 3),
('English Literature', 'ENG101', 'English language and literature', 'Grade 10', 2),
('Computer Science', 'CS101', 'Basic programming and computer concepts', 'Grade 10', 2),
('Advanced Mathematics', 'MATH201', 'Advanced mathematics for grade 11', 'Grade 11', 4),
('Advanced Physics', 'PHY201', 'Advanced physics concepts', 'Grade 11', 4),
('Advanced Chemistry', 'CHEM201', 'Advanced chemistry principles', 'Grade 11', 4),
('Statistics', 'STAT101', 'Basic statistics and probability', 'Grade 11', 3);

-- Insert sample teacher users
INSERT INTO users (name, email, password, phone, address, role, verified) VALUES
('Dr. Sarah Johnson', 'sarah.johnson@tuitioncenter.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-1234', '123 Teacher St', 'teacher', 1),
('Prof. Michael Chen', 'michael.chen@tuitioncenter.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-5678', '456 Education Ave', 'teacher', 1),
('Ms. Emily Davis', 'emily.davis@tuitioncenter.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-9012', '789 Learning Blvd', 'teacher', 1),
('Dr. Robert Wilson', 'robert.wilson@tuitioncenter.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-3456', '321 Science Way', 'teacher', 1),
('Ms. Lisa Anderson', 'lisa.anderson@tuitioncenter.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-7890', '654 Literature Lane', 'teacher', 1);

-- Insert corresponding teacher records
INSERT INTO teachers (user_id, teacher_id, qualification, experience_years, specialization, salary, status) VALUES
((SELECT id FROM users WHERE email = 'sarah.johnson@tuitioncenter.com'), 'TCH001', 'PhD in Mathematics', 8, 'Mathematics, Statistics', 65000, 'active'),
((SELECT id FROM users WHERE email = 'michael.chen@tuitioncenter.com'), 'TCH002', 'MSc in Physics', 6, 'Physics, Mathematics', 58000, 'active'),
((SELECT id FROM users WHERE email = 'emily.davis@tuitioncenter.com'), 'TCH003', 'MSc in Chemistry', 5, 'Chemistry, Biology', 55000, 'active'),
((SELECT id FROM users WHERE email = 'robert.wilson@tuitioncenter.com'), 'TCH004', 'PhD in Biology', 10, 'Biology, Environmental Science', 70000, 'active'),
((SELECT id FROM users WHERE email = 'lisa.anderson@tuitioncenter.com'), 'TCH005', 'MA in English Literature', 7, 'English, Creative Writing', 52000, 'active');

-- Insert sample classes
INSERT INTO classes (name, section, subject_id, teacher_id, room_number, capacity, schedule_time, schedule_days, fee) VALUES
('Grade 10 Mathematics - A', 'A', 1, 1, 'R-101', 30, '09:00:00', 'Mon,Wed,Fri', 150.00),
('Grade 10 Physics - A', 'A', 2, 2, 'R-102', 25, '10:00:00', 'Tue,Thu', 140.00),
('Grade 10 Chemistry - B', 'B', 3, 3, 'R-103', 28, '11:00:00', 'Mon,Wed,Fri', 145.00),
('Grade 10 Biology - A', 'A', 4, 4, 'R-104', 30, '14:00:00', 'Tue,Thu,Sat', 140.00),
('Grade 10 English - A', 'A', 5, 5, 'R-105', 35, '15:00:00', 'Mon,Wed', 120.00),
('Grade 11 Advanced Math - A', 'A', 7, 1, 'R-201', 25, '16:00:00', 'Mon,Wed,Fri', 180.00),
('Grade 11 Advanced Physics - A', 'A', 8, 2, 'R-202', 20, '17:00:00', 'Tue,Thu', 175.00),
('Grade 10 Computer Science - A', 'A', 6, 2, 'R-301', 20, '18:00:00', 'Sat', 160.00);

-- Insert some sample student users
INSERT INTO users (name, email, password, phone, address, role, verified) VALUES
('John Smith', 'john.smith@student.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-1111', '111 Student St', 'student', 1),
('Emma Johnson', 'emma.johnson@student.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-2222', '222 Study Ave', 'student', 1),
('Michael Brown', 'michael.brown@student.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-3333', '333 Learning Blvd', 'student', 1);

-- Insert corresponding student records
INSERT INTO students (user_id, student_id, date_of_birth, parent_name, parent_phone, grade_level, admission_date, status) VALUES
((SELECT id FROM users WHERE email = 'john.smith@student.com'), 'STU001', '2008-05-15', 'Robert Smith', '555-1110', 'Grade 10', '2024-01-15', 'active'),
((SELECT id FROM users WHERE email = 'emma.johnson@student.com'), 'STU002', '2008-08-22', 'Mary Johnson', '555-2220', 'Grade 10', '2024-01-20', 'active'),
((SELECT id FROM users WHERE email = 'michael.brown@student.com'), 'STU003', '2007-11-10', 'David Brown', '555-3330', 'Grade 11', '2024-01-25', 'active');

-- Insert sample enrollments
INSERT INTO enrollments (student_id, class_id, enrollment_date, status) VALUES
(1, 1, '2024-01-15', 'active'),  -- John in Math
(1, 2, '2024-01-15', 'active'),  -- John in Physics
(2, 1, '2024-01-20', 'active'),  -- Emma in Math
(2, 3, '2024-01-20', 'active'),  -- Emma in Chemistry
(3, 6, '2024-01-25', 'active'),  -- Michael in Advanced Math
(3, 7, '2024-01-25', 'active');  -- Michael in Advanced Physics