-- Sample data for Job Portal

-- Insert sample jobs
INSERT INTO jobs (title, description, requirements, location, salary_range, job_type, status, posted_date, deadline, created_by) VALUES 
(
    'Senior Software Engineer',
    'We are looking for a Senior Software Engineer to join our dynamic team. You will be responsible for designing, developing, and maintaining high-quality software solutions. This role involves collaborating with cross-functional teams to deliver innovative products.',
    'Bachelor''s degree in Computer Science or related field
• 5+ years of experience in software development
• Proficiency in Python, JavaScript, and SQL
• Experience with cloud platforms (AWS, Azure)
• Strong problem-solving skills
• Excellent communication abilities',
    'San Francisco, CA',
    '$120,000 - $160,000',
    'full-time',
    'active',
    NOW(),
    DATE_ADD(NOW(), INTERVAL 30 DAY),
    1
),
(
    'Frontend Developer',
    'Join our frontend team to build amazing user experiences. You will work with modern technologies like React, Vue.js, and TypeScript to create responsive and intuitive web applications.',
    'Bachelor''s degree in Computer Science or equivalent experience
• 3+ years of frontend development experience
• Expertise in React.js or Vue.js
• Proficiency in HTML5, CSS3, and JavaScript
• Experience with responsive design
• Knowledge of version control (Git)',
    'Remote',
    '$80,000 - $110,000',
    'full-time',
    'active',
    NOW(),
    DATE_ADD(NOW(), INTERVAL 25 DAY),
    1
),
(
    'Marketing Coordinator',
    'We are seeking a creative Marketing Coordinator to support our marketing initiatives. You will assist in developing marketing campaigns, managing social media, and analyzing market trends.',
    'Bachelor''s degree in Marketing, Communications, or related field
• 2+ years of marketing experience
• Strong written and verbal communication skills
• Experience with social media platforms
• Proficiency in Google Analytics
• Creative thinking and attention to detail',
    'New York, NY',
    '$45,000 - $60,000',
    'full-time',
    'active',
    NOW(),
    DATE_ADD(NOW(), INTERVAL 20 DAY),
    1
),
(
    'Data Analyst Intern',
    'Great opportunity for students or recent graduates to gain hands-on experience in data analysis. You will work with our data team to analyze business metrics and create meaningful reports.',
    'Currently pursuing or recently completed degree in Statistics, Mathematics, or related field
• Knowledge of SQL and Excel
• Familiarity with Python or R
• Strong analytical skills
• Eagerness to learn
• Detail-oriented approach',
    'Chicago, IL',
    '$20 - $25 per hour',
    'internship',
    'active',
    NOW(),
    DATE_ADD(NOW(), INTERVAL 15 DAY),
    1
),
(
    'Project Manager',
    'Lead cross-functional teams to deliver projects on time and within budget. This role requires strong organizational skills and the ability to coordinate multiple stakeholders.',
    'Bachelor''s degree in Business, Engineering, or related field
• 4+ years of project management experience
• PMP certification preferred
• Experience with Agile methodologies
• Strong leadership and communication skills
• Proficiency in project management tools',
    'Austin, TX',
    '$90,000 - $120,000',
    'full-time',
    'active',
    NOW(),
    DATE_ADD(NOW(), INTERVAL 35 DAY),
    1
),
(
    'UI/UX Designer',
    'Create beautiful and functional user interfaces for our web and mobile applications. You will work closely with product managers and developers to bring designs to life.',
    'Bachelor''s degree in Design, HCI, or related field
• 3+ years of UI/UX design experience
• Proficiency in Figma, Sketch, or Adobe Creative Suite
• Understanding of user-centered design principles
• Portfolio demonstrating design skills
• Experience with prototyping tools',
    'Seattle, WA',
    '$70,000 - $95,000',
    'full-time',
    'active',
    NOW(),
    DATE_ADD(NOW(), INTERVAL 28 DAY),
    1
);

-- Sample job applications (optional - for testing)
INSERT INTO job_applications (job_id, applicant_name, applicant_email, applicant_phone, cover_letter, status) VALUES
(1, 'John Smith', 'john.smith@email.com', '+1-555-0123', 'I am excited about the Senior Software Engineer position. With my 6 years of experience in full-stack development and expertise in Python and cloud technologies, I believe I would be a great fit for your team.', 'pending'),
(1, 'Sarah Johnson', 'sarah.j@email.com', '+1-555-0124', 'As a passionate software engineer with extensive experience in scalable systems, I am thrilled to apply for this position. My background in microservices and AWS makes me an ideal candidate.', 'reviewed'),
(2, 'Mike Davis', 'mike.davis@email.com', '+1-555-0125', 'I am applying for the Frontend Developer position. My experience with React and modern JavaScript frameworks aligns perfectly with your requirements.', 'pending'),
(3, 'Emily Chen', 'emily.chen@email.com', '+1-555-0126', 'I am interested in the Marketing Coordinator role. My creative background and social media expertise would bring fresh ideas to your marketing team.', 'pending'),
(4, 'Alex Rodriguez', 'alex.r@email.com', '+1-555-0127', 'As a recent Computer Science graduate, I am eager to start my career with the Data Analyst Intern position. My coursework in statistics and Python programming has prepared me well.', 'shortlisted');