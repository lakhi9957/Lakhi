# School Website

A modern, responsive school website with an admin panel for content management.

## Features

### Public Website
- **Home Section**: Hero banner with school information
- **About Section**: Mission, vision, values, and school statistics
- **Gallery Section**: Dynamic image gallery with hover effects
- **Notices Section**: Latest school announcements and notices
- **Responsive Design**: Works perfectly on desktop, tablet, and mobile devices
- **Modern UI**: Clean, professional design with smooth animations

### Admin Panel
- **User Authentication**: Secure login system for administrators
- **Dashboard**: Overview of website statistics and quick actions
- **Notice Management**: Add, edit, and delete school notices with priority levels
- **Gallery Management**: Upload and manage school images (placeholder implementation)
- **Settings**: Website configuration options (placeholder implementation)

## File Structure

```
├── index.html          # Main website homepage
├── style.css           # Custom CSS styling
├── script.js           # JavaScript functionality
├── config.php          # Database configuration
├── db.sql              # Database schema and initial data
├── images/             # Image assets directory
├── admin/              # Admin panel directory
│   ├── login.php       # Admin login page
│   ├── dashboard.php   # Admin dashboard
│   └── logout.php      # Logout functionality
└── README.md           # This file
```

## Setup Instructions

### 1. Database Setup
1. Create a MySQL database named `school_db`
2. Import the `db.sql` file to create tables and default data:
   ```sql
   mysql -u root -p school_db < db.sql
   ```

### 2. Configuration
1. Update database credentials in `config.php`:
   ```php
   $host = 'localhost';    # Database host
   $user = 'root';         # Database username
   $pass = '';             # Database password
   $db = 'school_db';      # Database name
   ```

### 3. Web Server Setup
1. Place all files in your web server directory (e.g., `htdocs`, `www`, etc.)
2. Ensure PHP and MySQL are installed and running
3. Access the website via your web server (e.g., `http://localhost/school-website/`)

### 4. Admin Access
- **URL**: `http://localhost/school-website/admin/login.php`
- **Email**: `admin@school.edu`
- **Password**: `admin123`

## Usage

### For Administrators

1. **Login to Admin Panel**:
   - Navigate to `/admin/login.php`
   - Use the default credentials or create new admin users

2. **Manage Notices**:
   - Go to "Manage Notices" in the admin dashboard
   - Add new notices with title, content, and priority
   - Delete existing notices as needed

3. **View Website**:
   - Use the "View Website" link in the admin panel
   - Check how changes appear on the public website

### For Visitors

1. **Browse Website**:
   - Navigate through different sections using the top menu
   - View school information, gallery, and latest notices
   - Responsive design works on all devices

## Technical Details

### Technologies Used
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Framework**: Bootstrap 5.3.0
- **Icons**: Font Awesome 6.0.0

### Key Features
- **Responsive Design**: Mobile-first approach with Bootstrap
- **Dynamic Content**: JavaScript-powered gallery and notices loading
- **Secure Admin Panel**: Session-based authentication
- **Modern UI/UX**: Clean design with smooth animations
- **Database Integration**: PHP/MySQL backend for content management

### Browser Support
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Customization

### Changing School Information
1. Edit the content in `index.html`
2. Update school name, address, and contact information
3. Modify the hero section text and statistics

### Adding Custom Styling
1. Add custom CSS to `style.css`
2. Use CSS custom properties (variables) for consistent theming
3. Modify Bootstrap classes as needed

### Extending Functionality
1. Add new admin panel features in `admin/dashboard.php`
2. Create new database tables in `db.sql`
3. Implement additional JavaScript functionality in `script.js`

## Security Notes

- Change default admin credentials after setup
- Use strong passwords for admin accounts
- Keep PHP and MySQL updated
- Consider using HTTPS in production
- Sanitize all user inputs (already implemented for notices)

## Future Enhancements

- File upload functionality for gallery management
- Email notifications for new notices
- Student/parent portal integration
- Event calendar system
- Online admission forms
- Multi-language support

## Troubleshooting

### Common Issues

1. **Database Connection Error**:
   - Check database credentials in `config.php`
   - Ensure MySQL service is running
   - Verify database name exists

2. **Admin Login Not Working**:
   - Ensure database tables are created
   - Check if default admin user exists
   - Verify session configuration in PHP

3. **Images Not Loading**:
   - Check file paths in HTML/CSS
   - Ensure images directory has proper permissions
   - Verify image URLs are correct

## Support

For support or questions about this school website:
- Check the code comments for implementation details
- Review the database schema in `db.sql`
- Examine the admin panel structure in the `admin/` directory

## License

This school website template is open source and available for educational use.