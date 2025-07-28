# Task Management System - cPanel Setup Guide

## 🚀 Quick Setup for cPanel Hosting

### Step 1: Database Setup in cPanel

1. **Login to your cPanel** (https://swift.herosite.pro:8443)

2. **Create MySQL Database:**
   - Go to "MySQL Databases"
   - Create a new database: `taskmanager_db`
   - Your full database name will be: `your_username_taskmanager_db`

3. **Create Database User:**
   - Create a new user: `taskmanager_user`
   - Set a strong password
   - Your full username will be: `your_username_taskmanager_user`

4. **Add User to Database:**
   - Add the user to the database with "ALL PRIVILEGES"

5. **Import Database Schema:**
   - Go to "phpMyAdmin"
   - Select your database
   - Click "Import" tab
   - Upload the `sql/schema.sql` file
   - Click "Go" to import

### Step 2: File Upload

1. **Upload Files:**
   - Use cPanel File Manager or FTP
   - Upload all files to `public_html/taskmanager/` (or your preferred directory)

2. **Set Permissions:**
   ```
   Folders: 755
   Files: 644
   uploads/ folder: 755 (will be created automatically)
   ```

### Step 3: Configuration

1. **Edit config.php:**
   ```php
   // Replace these with your actual cPanel details:
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'your_username_taskmanager_db');  // Your actual database name
   define('DB_USER', 'your_username_taskmanager_user'); // Your actual database user
   define('DB_PASS', 'your_actual_password');           // Your database password
   define('SITE_URL', 'https://swift.herosite.pro/taskmanager'); // Your actual URL
   ```

### Step 4: Test Installation

1. **Visit your website:** `https://swift.herosite.pro/taskmanager/`
2. **Default Admin Login:**
   - Email: `admin@taskmanagement.com`
   - Password: `admin123`
   - **⚠️ Change this immediately after first login!**

## 🔧 Troubleshooting Common Issues

### Database Connection Failed

**Error:** "Database connection failed. Please check your database configuration."

**Solutions:**
1. **Check Database Credentials:**
   - Verify database name format: `cpanel_username_databasename`
   - Verify user format: `cpanel_username_username`
   - Ensure password is correct

2. **Check Database Exists:**
   - Login to cPanel → MySQL Databases
   - Verify database and user exist
   - Ensure user has privileges on database

3. **Check MySQL Extension:**
   - Contact hosting provider if PDO MySQL is not available

### File Upload Issues

**Error:** File uploads not working

**Solutions:**
1. **Check Directory Permissions:**
   ```bash
   chmod 755 uploads/
   ```

2. **Check PHP Settings:**
   - Verify `upload_max_filesize` and `post_max_size` in PHP settings
   - Default is set to 5MB in the system

### Email Not Working

**Error:** Emails not being sent

**Solutions:**
1. **Check PHP Mail Function:**
   - Most cPanel hosting supports PHP mail() function
   - Verify your domain has proper MX records

2. **Alternative SMTP Setup:**
   - You can modify the `sendEmail()` function in `includes/functions.php`
   - Use PHPMailer with SMTP if needed

## 📋 Post-Installation Checklist

### Security Setup
- [ ] Change default admin password
- [ ] Update `config.php` error reporting for production:
  ```php
  error_reporting(0);
  ini_set('display_errors', 0);
  ```
- [ ] Enable HTTPS redirect in `.htaccess` if SSL is available
- [ ] Set strong database passwords

### System Configuration
- [ ] Test admin login and dashboard
- [ ] Test user registration process
- [ ] Verify file upload functionality
- [ ] Test email notifications
- [ ] Check referral system functionality

### Performance Optimization
- [ ] Enable caching in cPanel if available
- [ ] Optimize database indexes
- [ ] Set up regular database backups
- [ ] Monitor disk space usage

## 🎯 Feature Testing Guide

### Admin Features to Test:
1. **Login:** Use admin credentials
2. **Dashboard:** View statistics and recent activity
3. **Create Task:** Add new task with file upload
4. **User Management:** View and manage users
5. **Task Review:** Approve/reject completed tasks

### User Features to Test:
1. **Registration:** Complete 2-step signup process
2. **Login:** Access user dashboard
3. **Task Management:** Accept/reject tasks
4. **File Upload:** Submit task with file
5. **Referral System:** Generate and share referral links
6. **Wallet:** View earnings and request withdrawal

## 🔄 Backup and Maintenance

### Regular Backups
1. **Database Backup:**
   - Use cPanel backup feature
   - Export database via phpMyAdmin
   - Schedule automatic backups

2. **File Backup:**
   - Backup entire application directory
   - Include uploaded files in `uploads/` folder

### Maintenance Tasks
- Monitor error logs regularly
- Clean up old uploaded files if needed
- Update user passwords periodically
- Review and optimize database performance

## 📞 Support Information

### Common File Locations:
- **Error Logs:** Check cPanel Error Logs
- **Upload Directory:** `uploads/` (created automatically)
- **Configuration:** `config.php`
- **Database Schema:** `sql/schema.sql`

### System Requirements:
- **PHP:** 7.4 or higher
- **MySQL:** 5.7 or higher
- **Extensions:** PDO, PDO_MySQL, GD (for image handling)
- **Disk Space:** Minimum 100MB (more for file uploads)

### Getting Help:
1. Check cPanel error logs for detailed error messages
2. Verify all file permissions are correct
3. Ensure database connection details are accurate
4. Contact your hosting provider for server-specific issues

---

**Note:** This system is specifically designed for cPanel hosting environments and should work out-of-the-box with most shared hosting providers.
