# Job Portal Website

A comprehensive job portal website with admin control panel built using PHP, MySQL, HTML, CSS, JavaScript, and Bootstrap.

## Features

### Public Features
- **Modern Landing Page**: Attractive homepage with hero section, statistics, and featured jobs
- **Job Browse Page**: Advanced search and filtering by job type, location, and keywords
- **Job Details Page**: Comprehensive job information with application form
- **Responsive Design**: Mobile-friendly interface using Bootstrap 5
- **Real-time Search**: Dynamic job filtering and pagination

### Admin Features
- **Secure Authentication**: Admin login system with session management
- **Dashboard**: Overview of job statistics and recent activity
- **Job Management**: Complete CRUD operations for job postings
- **Application Tracking**: View and manage job applications
- **Real-time Statistics**: Live job counts and application metrics

## Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5, Font Awesome
- **Backend**: PHP 7.4+
- **Database**: MySQL 8.0+
- **Server**: Apache/Nginx

## Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- Web browser

### Database Setup

1. **Create Database**:
   ```sql
   -- Run the db.sql file to create the database and tables
   mysql -u root -p < db.sql
   ```

2. **Load Sample Data** (Optional):
   ```sql
   -- Add sample jobs for testing
   mysql -u root -p school_db < sample_data.sql
   ```

### Configuration

1. **Database Configuration**:
   Edit `config.php` with your database credentials:
   ```php
   $host = 'localhost';
   $user = 'your_username';
   $pass = 'your_password';
   $db = 'school_db';
   ```

2. **Web Server Setup**:
   - Place all files in your web server document root
   - Ensure PHP is properly configured
   - Enable mod_rewrite if using Apache

### Admin Access

**Default Admin Credentials**:
- Email: `admin@company.com`
- Password: `admin123`

## File Structure

```
job-portal/
├── index.html              # Main homepage
├── jobs.php                # Job listings page
├── job-details.php          # Individual job details and application
├── config.php              # Database configuration
├── db.sql                  # Database schema
├── sample_data.sql         # Sample data for testing
├── api/
│   └── get_jobs.php        # API endpoint for job data
├── admin/
│   ├── login.php           # Admin login page
│   ├── dashboard.php       # Admin dashboard
│   ├── add-job.php         # Add new job form
│   └── logout.php          # Admin logout
└── README.md              # This file
```

## Database Schema

### Tables

1. **users**: Admin user accounts
   - id, name, email, password, role, verified, created_at

2. **jobs**: Job postings
   - id, title, description, requirements, location, salary_range, job_type, status, posted_date, deadline, created_by, updated_at

3. **job_applications**: Job applications
   - id, job_id, applicant_name, applicant_email, applicant_phone, resume_path, cover_letter, application_date, status

## API Endpoints

### Public API
- `GET /api/get_jobs.php` - Retrieve job listings
  - Parameters: search, job_type, location, featured, limit, count_only

## Admin Features

### Dashboard
- Job statistics overview
- Recent job postings
- Application metrics
- Quick actions panel
- System status monitoring

### Job Management
- Create new job postings
- Edit existing jobs
- Change job status (active/inactive/closed)
- Delete job postings
- View job applications

### Application Management
- View all applications
- Filter by job or status
- Update application status
- Contact applicants

## Security Features

- **Session Management**: Secure admin sessions
- **Input Validation**: SQL injection prevention
- **XSS Protection**: Output sanitization
- **Access Control**: Admin-only areas protected
- **Password Security**: Prepared for hashed passwords

## Customization

### Styling
- Bootstrap 5 for responsive design
- Custom CSS for branding
- Font Awesome icons
- Gradient backgrounds and modern UI

### Configuration
- Easy database configuration
- Customizable job types
- Flexible application fields
- Modular design for extensions

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers

## Performance Features

- **Lazy Loading**: Dynamic content loading
- **Pagination**: Efficient job browsing
- **Caching**: Session-based optimization
- **Responsive Images**: Optimized for all devices
- **Minified Assets**: Fast loading times

## Deployment

### Production Setup
1. Upload files to web server
2. Configure database connection
3. Set proper file permissions
4. Enable HTTPS for security
5. Configure backup strategy

### Environment Configuration
- Development: Local XAMPP/WAMP
- Staging: Test server setup
- Production: Live server deployment

## Maintenance

### Regular Tasks
- Database backups
- Security updates
- Performance monitoring
- User feedback review

### Monitoring
- Job posting analytics
- Application conversion rates
- User engagement metrics
- System performance

## Support

For technical support or feature requests, please contact the development team.

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## Changelog

### Version 1.0.0
- Initial release
- Basic job portal functionality
- Admin panel
- Responsive design
- Job application system

---

**Note**: This is a demo application. For production use, implement additional security measures, proper error handling, and comprehensive testing.