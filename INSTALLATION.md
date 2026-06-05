# Installation Guide - Student Record Management System

## 🚀 Quick Start Installation

Follow these step-by-step instructions to install and run the Student Record Management System on your local machine.

---

## 📋 System Requirements

### Minimum Requirements
- **Operating System**: Windows 10/11, macOS 10.14+, or Linux
- **Web Server**: Apache 2.4+
- **PHP**: Version 7.4 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.2+
- **RAM**: Minimum 2GB (4GB recommended)
- **Storage**: 500MB free space

### Recommended Requirements
- **Operating System**: Windows 11, macOS 12+, or Ubuntu 20.04+
- **Web Server**: Apache 2.4.46+
- **PHP**: Version 8.0 or higher
- **Database**: MySQL 8.0+ or MariaDB 10.6+
- **RAM**: 4GB or more
- **Storage**: 1GB free space

---

## 🛠 Installation Steps

### Step 1: Install XAMPP Server

#### For Windows:
1. Download XAMPP from [https://www.apachefriends.org/download.html](https://www.apachefriends.org/download.html)
2. Run the installer as Administrator
3. Choose installation path (default: `C:\xampp`)
4. Select Apache and MySQL components
5. Complete the installation
6. Launch XAMPP Control Panel

#### For macOS:
1. Download XAMPP for macOS
2. Open the downloaded DMG file
3. Drag XAMPP to Applications folder
4. Open XAMPP Control Panel from Applications

#### For Linux (Ubuntu/Debian):
```bash
# Download XAMPP
wget https://downloadsapachefriends.global.ssl.fastly.net/xampp-files/8.1.10/xampp-linux-x64-8.1.10-0-installer.run

# Make it executable
chmod +x xampp-linux-x64-8.1.10-0-installer.run

# Run installer
sudo ./xampp-linux-x64-8.1.10-0-installer.run
```

### Step 2: Start Apache and MySQL

1. Open XAMPP Control Panel
2. Click "Start" button next to Apache
3. Click "Start" button next to MySQL
4. Verify both services are running (green indicator)

### Step 3: Download and Extract Project

1. Download the Student Record Management System ZIP file
2. Extract the ZIP file to your desktop
3. Copy the extracted folder to:
   - **Windows**: `C:\xampp\htdocs\`
   - **macOS**: `/Applications/XAMPP/htdocs/`
   - **Linux**: `/opt/lampp/htdocs/`

4. Rename the folder to `student-record-system` (optional)

### Step 4: Create Database

1. Open your web browser
2. Navigate to: `http://localhost/phpmyadmin`
3. Click "New" in the left sidebar
4. Enter database name: `student_db`
5. Select "utf8mb4_unicode_ci" collation
6. Click "Create"

### Step 5: Import Database Schema

1. Select the `student_db` database from the left sidebar
2. Click the "Import" tab at the top
3. Click "Choose File" button
4. Navigate to the project folder
5. Select `database/student_db.sql`
6. Scroll down and click "Go"
7. Wait for the import to complete (you should see "Import has been successfully finished")

### Step 6: Configure Database Connection

1. Navigate to the project folder
2. Open `includes/db.php` in a text editor (Notepad++, VS Code, etc.)
3. Verify the database settings:
   ```php
   $host = 'localhost';        // Keep as localhost
   $dbname = 'student_db';     // Database name
   $username = 'root';         // Default MySQL username
   $password = '';             // Your MySQL password (usually empty for XAMPP)
   ```
4. Save the file

### Step 7: Set File Permissions (Linux/macOS only)

```bash
# Navigate to project directory
cd /opt/lampp/htdocs/student-record-system

# Set appropriate permissions
sudo chmod -R 755 .
sudo chown -R www-data:www-data .
```

### Step 8: Access the Application

1. Open your web browser
2. Navigate to: `http://localhost/student-record-system`
3. You should see the login page

### Step 9: Login to System

1. Use the default admin credentials:
   - **Username**: `admin`
   - **Password**: `admin123`
2. Click "Login"
3. You should now see the dashboard

---

## ✅ Verification Steps

After installation, verify everything is working:

### 1. Database Connection Test
- Login to the system
- If login succeeds, database connection is working

### 2. Student Management Test
1. Go to "Students" section
2. Click "Add Student"
3. Fill in sample data and submit
4. Verify student appears in the list

### 3. Result Management Test
1. Go to "Results" section
2. Click "Add Result"
3. Select student, subject, and exam
4. Enter marks and submit
5. Verify result appears in the list

### 4. Print Functionality Test
1. Click the print icon next to any result
2. Verify the print preview opens correctly
3. Test print functionality

---

## 🔧 Common Installation Issues & Solutions

### Issue 1: Apache Won't Start
**Problem**: Port 80 or 443 is already in use
**Solution**:
1. Open XAMPP Control Panel
2. Click "Config" next to Apache
3. Select "Apache (httpd.conf)"
4. Find `Listen 80` and change to `Listen 8080`
5. Save and restart Apache
6. Access via `http://localhost:8080`

### Issue 2: MySQL Won't Start
**Problem**: Port 3306 is already in use
**Solution**:
1. Stop any other MySQL services
2. Restart XAMPP MySQL service
3. If still not working, restart computer

### Issue 3: Database Connection Error
**Problem**: "Connection failed" message
**Solution**:
1. Verify MySQL is running in XAMPP
2. Check database name in `includes/db.php`
3. Verify MySQL username and password
4. Ensure database `student_db` exists

### Issue 4: Blank White Pages
**Problem**: Pages load but show blank content
**Solution**:
1. Enable PHP error display:
   - Open `php.ini` (in XAMPP/php folder)
   - Set `display_errors = On`
   - Set `error_reporting = E_ALL`
2. Check Apache error logs
3. Verify all PHP files are uploaded correctly

### Issue 5: Login Not Working
**Problem**: Correct credentials don't work
**Solution**:
1. Check admin table in database:
   ```sql
   SELECT * FROM admins WHERE username = 'admin';
   ```
2. If no records, insert admin:
   ```sql
   INSERT INTO admins (username, password, email) 
   VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com');
   ```

---

## 🔄 Post-Installation Configuration

### Change Default Admin Password
1. Login to the system
2. Access phpMyAdmin
3. Go to `student_db` database
4. Click `admins` table
5. Edit the admin record
6. Generate new password hash:
   ```php
   <?php
   echo password_hash('your_new_password', PASSWORD_DEFAULT);
   ?>
   ```
7. Update the password field with the new hash

### Configure Email Settings (Optional)
For email notifications, edit PHP configuration:
1. Open `php.ini`
2. Configure SMTP settings:
   ```ini
   SMTP = smtp.gmail.com
   smtp_port = 587
   sendmail_from = your-email@gmail.com
   ```

### Enable HTTPS (Optional)
1. Obtain SSL certificate
2. Configure Apache for HTTPS
3. Update base URLs in the application

---

## 📱 Mobile Access

To access the system from mobile devices on the same network:

1. Find your computer's IP address:
   - **Windows**: Open Command Prompt and type `ipconfig`
   - **macOS/Linux**: Open Terminal and type `ifconfig`

2. Allow Apache through firewall:
   - **Windows**: Go to Windows Defender Firewall settings
   - **macOS**: Go to System Preferences > Security & Privacy > Firewall

3. Access from mobile:
   `http://YOUR_IP_ADDRESS/student-record-system`

---

## 🚀 Production Deployment

For deploying on a live server:

### Web Server Configuration
1. Use Apache with mod_rewrite enabled
2. Configure virtual hosts
3. Set up SSL certificate
4. Configure .htaccess for security

### Security Hardening
1. Change default admin password
2. Disable PHP error display in production
3. Set up file permissions properly
4. Configure firewall rules
5. Regular database backups

### Performance Optimization
1. Enable PHP OPcache
2. Configure MySQL for performance
3. Use CDN for static assets
4. Implement caching strategies

---

## 📞 Support

If you encounter any issues during installation:

1. **Check Error Logs**: 
   - Apache: `logs/error.log`
   - PHP: `php_error.log`

2. **Common Solutions**: Refer to the "Common Issues" section above

3. **Contact Support**:
   - Email: support@example.com
   - WhatsApp: +91 1234567890

---

## 🎉 Installation Complete!

Congratulations! You have successfully installed the Student Record Management System. You can now start managing student records and results efficiently.

**Next Steps:**
1. Change the default admin password
2. Add your school/college information
3. Create classes and subjects
4. Add student records
5. Start managing results

---

**© 2024 Student Record Management System**  
Installation Guide v1.0
