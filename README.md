# Task Management System

A comprehensive web-based task management system with admin controls, user dashboards, and a 5-level referral system.

## Features

### Admin Features
- **Dashboard**: Overview of system statistics and recent activity
- **Task Management**: Create, assign, and manage tasks with timers
- **User Management**: Add, edit, and delete user accounts
- **Package Management**: Manage Gold, Silver, and Diamond packages
- **Task Review**: Approve or reassign completed tasks
- **Earnings Tracking**: Monitor total earnings and payouts

### User Features
- **Dashboard**: View active tasks, earnings, and referral statistics
- **Task System**: Accept/reject tasks with countdown timers
- **File Submission**: Upload task completion files
- **Wallet Management**: Track earnings and request withdrawals
- **Referral System**: 5-level commission structure (10%, 5%, 4%, 3%, 2%)
- **Profile Management**: Update personal and banking information

### System Features
- **Security**: CSRF protection, input sanitization, secure file uploads
- **Responsive Design**: Mobile-friendly interface using modern CSS
- **Real-time Timers**: JavaScript countdown timers for task deadlines
- **Email Notifications**: Basic email system for user communications
- **Package System**: Gold (1 task/day), Silver (2 tasks/day), Diamond (3 tasks/day)

## Installation Instructions

### Prerequisites
- Web server with PHP 7.4+ support
- MySQL 5.7+ database
- cPanel hosting account (recommended)

### Step 1: Upload Files
1. Download all project files
2. Upload to your web server's public directory (e.g., `public_html/taskmanager/`)
3. Ensure proper file permissions (755 for directories, 644 for files)

### Step 2: Database Setup
1. Create a new MySQL database via cPanel
2. Import the `sql/schema.sql` file using phpMyAdmin
3. Note down your database credentials

### Step 3: Configuration
1. Edit `config.php` and update database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'your_database_name');
   define('DB_USER', 'your_database_user');
   define('DB_PASS', 'your_database_password');
   define('SITE_URL', 'https://yourdomain.com/taskmanager');
   ```

### Step 4: Directory Permissions
1. Create an `uploads` directory in the project root
2. Set permissions to 755 for the uploads directory
3. Ensure the web server can write to this directory

### Step 5: Security (Production)
1. Change the default admin password after first login
2. Update error reporting settings in `config.php`:
   ```php
   error_reporting(0);
   ini_set('display_errors', 0);
   ```
3. Enable HTTPS in `.htaccess` if SSL is available

## Default Login Credentials

### Admin Access
- **URL**: `yourdomain.com/taskmanager/login.php?role=admin`
- **Email**: `admin@taskmanagement.com`
- **Password**: `admin123`

**⚠️ IMPORTANT**: Change these credentials immediately after first login!

## File Structure

```
/project-root
├── config.php                 # Database configuration
├── index.php                  # Landing page
├── login.php                  # Login system
├── logout.php                 # Logout handler
├── .htaccess                  # Security and URL rewriting
├── README.md                  # This file
│
├── admin/
│   └── dashboard.php          # Admin control panel
│
├── user/
│   └── dashboard.php          # User dashboard
│
├── signup/
│   ├── register.php           # Step 1 registration
│   └── register_details.php   # Step 2 registration
│
├── includes/
│   ├── header.php             # Common header
│   ├── footer.php             # Common footer
│   └── functions.php          # Helper functions
│
├── assets/
│   ├── css/
│   │   └── main.css           # Main stylesheet
│   └── js/
│       └── main.js            # JavaScript functionality
│
├── sql/
│   └── schema.sql             # Database schema
│
└── uploads/                   # File upload directory
```

## Database Schema

### Main Tables
- **users**: User accounts and profile information
- **tasks**: Task definitions and settings
- **task_assignments**: Task assignments to users
- **packages**: Gold, Silver, Diamond packages
- **earnings**: User earnings and commissions
- **referrals**: 5-level referral relationships
- **withdrawal_requests**: Withdrawal processing
- **system_settings**: Configurable system parameters

## Business Logic

### Referral System
- 5-level deep commission structure
- Automatic commission calculation and distribution
- Real-time referral tree tracking

### Task Distribution
- Automatic task redistribution on rejection
- Daily task limits based on user packages
- Timer-based task acceptance and submission

### Package System
- **Gold**: 1 task per day, basic features
- **Silver**: 2 tasks per day, standard features  
- **Diamond**: 3 tasks per day, premium features

## Security Features

### Input Validation
- CSRF token protection on all forms
- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- File upload validation and restrictions

### Access Control
- Role-based access (Admin/User)
- Session management with timeout
- Secure password hashing
- Protected directories and files

## Customization

### Styling
- Edit `assets/css/main.css` for visual customization
- Uses modern CSS with Google Fonts
- Responsive design with mobile support

### Business Rules
- Modify referral percentages in `system_settings` table
- Adjust package limits and features
- Customize email templates in functions

### Additional Features
- Add payment gateway integration
- Implement SMS notifications
- Add advanced reporting features
- Create mobile app API endpoints

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify database credentials in `config.php`
   - Ensure database server is running
   - Check database user permissions

2. **File Upload Issues**
   - Verify `uploads` directory exists and is writable
   - Check PHP upload limits in server configuration
   - Ensure proper file permissions

3. **Email Not Working**
   - Configure SMTP settings if needed
   - Check server mail configuration
   - Verify email addresses are valid

4. **Timer Issues**
   - Ensure JavaScript is enabled
   - Check browser console for errors
   - Verify server time zone settings

### Support

For technical support or customization requests:
- Check server error logs for detailed error information
- Verify all file permissions are correct
- Ensure PHP version compatibility (7.4+)

## License

This project is provided as-is for educational and commercial use. Modify as needed for your requirements.

## Version History

- **v1.0**: Initial release with core functionality
- **v1.1**: Added security enhancements and mobile responsiveness
- **v1.2**: Improved referral system and task distribution logic

---

**Note**: This system is designed for cPanel hosting environments but can be adapted for other hosting platforms with minimal modifications.
