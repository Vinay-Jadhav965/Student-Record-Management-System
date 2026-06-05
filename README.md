# Student Record Management System

A comprehensive web-based student management system built with PHP and MySQL for educational institutions to manage student records and results efficiently.

## 🌟 Features

### 🔐 Admin Authentication
- Secure admin login system
- Session-based authentication
- Password protection for sensitive operations

### 👨‍🎓 Student Management
- **Add Students**: Enter comprehensive student details
- **Edit Students**: Update student information
- **Delete Students**: Remove student records
- **Search Students**: Find students by name or roll number
- **Student Status**: Active/Inactive status management

### 📊 Result Management
- **Add Results**: Enter subject-wise marks and grades
- **Edit Results**: Modify existing results
- **Delete Results**: Remove result records
- **Automatic Grade Calculation**: Based on percentage
- **Result Filtering**: Filter by class, exam, or student

### 🏫 Class & Subject Management
- **Manage Classes**: Create and organize classes/sections
- **Manage Subjects**: Add subjects with maximum marks
- **Class-wise Organization**: Organize students by classes

### 📋 Exam Management
- **Create Exams**: Different exam types (Mid Term, Final Term, etc.)
- **Exam Scheduling**: Set start and end dates
- **Exam Status Tracking**: Upcoming, Ongoing, Completed

### 🖨 Print Functionality
- **Generate Result Reports**: Professional result sheets
- **Print-ready Format**: Optimized for printing
- **Complete Student Reports**: All subjects in one report

### 🔍 Advanced Features
- **Dashboard Analytics**: Overview of system statistics
- **Pagination**: Handle large datasets efficiently
- **Responsive Design**: Works on all devices
- **Search & Filter**: Advanced filtering options

## 🛠 Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **UI Framework**: Bootstrap 5.3
- **Icons**: Font Awesome 6
- **Security**: Prepared Statements, Input Sanitization

## 📦 Installation Guide

### Prerequisites
- XAMPP/WAMP/MAMP server stack
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser

### Step 1: Download & Extract
1. Download the project ZIP file
2. Extract it to your server's web directory:
   - **XAMPP**: `htdocs/`
   - **WAMP**: `www/`
   - **MAMP**: `htdocs/`

### Step 2: Database Setup
1. Start your Apache and MySQL servers
2. Open phpMyAdmin: `http://localhost/phpmyadmin`
3. Create a new database named `student_db`
4. Import the SQL file:
   - Click on the `student_db` database
   - Click "Import" tab
   - Choose `database/student_db.sql` from the project
   - Click "Go" to import

### Step 3: Configure Database
1. Open `includes/db.php` in a text editor
2. Update database credentials if needed:
   ```php
   $host = 'localhost';
   $dbname = 'student_db';
   $username = 'root';
   $password = ''; // Your MySQL password
   ```

### Step 4: Access the Application
1. Open your web browser
2. Navigate to: `http://localhost/student-record-system`
3. Login with default credentials:
   - **Username**: `admin`
   - **Password**: `admin123`

## 📁 Project Structure

```
Student Record Management System/
├── database/
│   └── student_db.sql          # Database schema and sample data
├── includes/
│   ├── db.php                  # Database connection
│   └── functions.php           # Helper functions
├── login.php                   # Admin login page
├── logout.php                  # Logout handler
├── index.php                   # Dashboard
├── students.php                # Student management
├── results.php                 # Result management
├── classes.php                 # Class management
├── subjects.php                # Subject management
├── exams.php                   # Exam management
├── print_result.php            # Print result reports
└── README.md                   # This file
```

## 🎯 Default Credentials

**Admin Login:**
- Username: `admin`
- Password: `admin123`

⚠️ **Important**: Change the default password after first login for security.

## 📱 Usage Guide

### Dashboard
- View system statistics
- Recent students and results overview
- Quick navigation to all modules

### Managing Students
1. Click "Add Student" to register new students
2. Use search to find existing students
3. Click edit icon to update student details
4. Click delete icon to remove students (with confirmation)

### Managing Results
1. Click "Add Result" to enter marks
2. Select student, subject, and exam
3. Enter marks (grade calculated automatically)
4. Use filters to view specific results
5. Print individual result reports

### Managing Classes & Subjects
1. Create classes and sections
2. Add subjects to each class
3. Set maximum marks for each subject

### Managing Exams
1. Create different types of exams
2. Set exam schedules
3. Track exam status

## 🔒 Security Features

- **SQL Injection Protection**: Prepared statements used throughout
- **XSS Prevention**: Input sanitization and output encoding
- **Session Security**: Secure session management
- **Authentication**: Password-protected admin access
- **Input Validation**: Server-side validation for all inputs

## 🎨 Customization

### Changing Theme Colors
Edit the CSS variables in any PHP file:
```css
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
```

### Adding New Fields
1. Modify database tables via phpMyAdmin
2. Update PHP forms to include new fields
3. Update SQL queries to handle new data

### Customizing Reports
Edit `print_result.php` to modify:
- Report layout
- Additional fields
- Styling and formatting

## 🐛 Troubleshooting

### Common Issues

**Database Connection Error**
- Verify MySQL server is running
- Check database credentials in `includes/db.php`
- Ensure database `student_db` exists

**Blank Pages**
- Check PHP error logs
- Verify file permissions
- Ensure all files are in correct directory

**Login Issues**
- Verify admin credentials in database
- Check session settings in php.ini
- Clear browser cookies and cache

**Import Issues**
- Ensure SQL file is not corrupted
- Check MySQL version compatibility
- Verify database permissions

### Error Log Location
- **XAMPP**: `C:/xampp/apache/logs/error.log`
- **WAMP**: `C:/wamp/logs/apache_error.log`

## 📞 Support

For technical support and customization services:

**Email:** support@example.com  
**WhatsApp:** +91 1234567890

## 📄 License

This project is provided for educational purposes. Feel free to modify and use it according to your requirements.

## 🔄 Updates & Features

### Planned Features
- [ ] Student attendance tracking
- [ ] Fee management system
- [ ] Parent portal
- [ ] SMS/Email notifications
- [ ] Advanced reporting
- [ ] Data export (Excel/PDF)
- [ ] Multi-language support

### Version History
- **v1.0.0**: Initial release with core features
- Basic student and result management
- Admin authentication
- Print functionality

## 📚 Documentation

For detailed documentation and video tutorials, visit our website or contact support.

---

**© 2024 Student Record Management System. All rights reserved.**
