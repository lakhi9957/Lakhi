# Excellence Tuition Center Management System

A comprehensive web-based management system for tuition centers built with PHP, MySQL, and Bootstrap. This system provides complete functionality for managing students, teachers, classes, attendance, fees, assignments, and more.

## 🌟 Features

### Admin Features
- **Dashboard** with real-time statistics and analytics
- **Student Management** - Add, edit, view student records
- **Teacher Management** - Manage teacher profiles and assignments
- **Class Management** - Create and manage classes and subjects
- **Fee Management** - Track payments and generate invoices
- **Attendance System** - Digital attendance tracking
- **Assignment & Exam Management** - Create and grade assignments
- **Announcements** - System-wide communication
- **Reports & Analytics** - Comprehensive reporting tools

### Student Features
- **Personal Dashboard** with academic overview
- **Class Information** - View enrolled classes and schedules
- **Assignment Submission** - Submit and track assignments
- **Exam Results** - View grades and performance
- **Attendance Records** - Check attendance history
- **Fee Payment** - View and pay pending fees
- **Timetable** - Personal class schedule

### Teacher Features
- **Teaching Dashboard** with class overview
- **Student Management** - View assigned students
- **Assignment Creation** - Create and grade assignments
- **Attendance Marking** - Mark student attendance
- **Grade Management** - Input and manage grades
- **Class Communication** - Communicate with students

### Parent Features
- **Children Overview** - Monitor all children's progress
- **Attendance Monitoring** - Track children's attendance
- **Grade Tracking** - View academic performance
- **Fee Management** - Pay fees for children
- **Communication** - Contact teachers and school

## 🚀 Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

### Setup Instructions

1. **Clone or Download**
   ```bash
   git clone <repository-url>
   # OR download and extract the files
   ```

2. **Database Setup**
   - Create a new MySQL database named `tuition_center`
   - Import the database schema:
   ```bash
   mysql -u username -p tuition_center < db.sql
   ```

3. **Configuration**
   - Update database credentials in `config.php`:
   ```php
   $host = 'localhost';
   $user = 'your_username';
   $pass = 'your_password';
   $db = 'tuition_center';
   ```

4. **File Permissions**
   - Ensure proper permissions for the web directory
   - Make sure PHP can read/write to the application directory

5. **Access the Application**
   - Navigate to `http://your-domain.com/index.html`
   - Default admin credentials will be created during setup

## 📁 Project Structure

```
tuition-center/
├── index.html              # Main homepage
├── index.php              # PHP routing
├── login.php              # User authentication
├── register.php           # User registration
├── logout.php             # Logout functionality
├── config.php             # Database configuration
├── db.sql                 # Database schema
├── admin/                 # Admin panel
│   ├── dashboard.php
│   ├── students.php
│   └── ...
├── student/               # Student portal
│   ├── dashboard.php
│   └── ...
├── teacher/               # Teacher portal
│   ├── dashboard.php
│   └── ...
├── parent/                # Parent portal
│   ├── dashboard.php
│   └── ...
└── README.md
```

## 🎯 User Roles & Access

### Admin (Super User)
- Full system access
- User management
- System configuration
- Reports and analytics

### Teacher
- Class management
- Student grading
- Attendance marking
- Assignment creation

### Student
- Personal dashboard
- Assignment submission
- Grade viewing
- Fee payment

### Parent
- Children monitoring
- Communication with teachers
- Fee payment
- Progress tracking

## 🔐 Default Login Credentials

**Admin Account:**
- Email: `admin@tuitioncenter.com`
- Password: `password` (Please change after first login)

## 🛠️ Technologies Used

- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, JavaScript
- **Framework:** Bootstrap 5.3
- **Icons:** Font Awesome 6.0
- **Charts:** Chart.js
- **Tables:** DataTables

## 📱 Responsive Design

The system is fully responsive and works on:
- Desktop computers
- Tablets
- Mobile phones
- All modern web browsers

## 🔧 Customization

### Adding New Features
1. Create new PHP files in appropriate role directories
2. Update navigation menus in dashboard files
3. Add database tables if needed in `db.sql`

### Styling
- Modify CSS in the `<style>` sections of each file
- Customize Bootstrap variables
- Add custom CSS files as needed

## 🚨 Security Features

- Password hashing with PHP's `password_hash()`
- SQL injection prevention with prepared statements
- Session management
- Role-based access control
- Input sanitization
- CSRF protection ready

## 📊 Database Schema

The system includes the following main tables:
- `users` - User accounts and authentication
- `students` - Student-specific information
- `teachers` - Teacher profiles and qualifications
- `classes` - Class and subject management
- `attendance` - Attendance tracking
- `fees` - Fee management and payments
- `assignments` - Assignment and submission tracking
- `exams` - Exam and results management
- `announcements` - System announcements

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🆘 Support

For support and questions:
- Create an issue in the repository
- Check the documentation
- Review the code comments

## 🔄 Version History

- **v1.0.0** - Initial release with core features
  - User management system
  - Basic dashboards for all roles
  - Database schema and configuration
  - Responsive design implementation

## 🎉 Acknowledgments

- Bootstrap team for the excellent CSS framework
- Font Awesome for the beautiful icons
- Chart.js for data visualization
- DataTables for enhanced table functionality

---

**Note:** This is a comprehensive tuition center management system designed for educational institutions. Please ensure you have proper backups and security measures in place before deploying to production.

For any questions or issues, please refer to the documentation or create an issue in the repository.