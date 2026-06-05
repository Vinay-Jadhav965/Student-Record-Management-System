<?php
// Database setup and test script
$host = 'localhost';
$username = 'root';
$password = '';

// First connect without database
$conn = new mysqli($host, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected to MySQL successfully!\n";

// Check if database exists
$result = $conn->query("SHOW DATABASES LIKE 'student_db'");
if ($result->num_rows == 0) {
    echo "Database 'student_db' does not exist. Creating...\n";
    
    // Create database
    if ($conn->query("CREATE DATABASE student_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
        echo "Database created successfully!\n";
    } else {
        die("Error creating database: " . $conn->error);
    }
} else {
    echo "Database 'student_db' already exists.\n";
}

// Connect to the database
$conn->select_db('student_db');

// Check if admins table exists
$result = $conn->query("SHOW TABLES LIKE 'admins'");
if ($result->num_rows == 0) {
    echo "Admins table does not exist. Creating tables...\n";
    
    // Read and execute SQL file
    $sqlFile = 'database/student_db.sql';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        
        // Split SQL statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^--/', $statement)) {
                if (!$conn->query($statement)) {
                    echo "Error executing statement: " . $conn->error . "\n";
                    echo "Statement: " . $statement . "\n";
                }
            }
        }
        echo "Database tables created successfully!\n";
    } else {
        die("SQL file not found: $sqlFile");
    }
} else {
    echo "Admins table already exists.\n";
}

// Check if admin user exists
$result = $conn->query("SELECT * FROM admins WHERE username = 'admin'");
if ($result->num_rows == 0) {
    echo "Admin user does not exist. Creating...\n";
    
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO admins (username, password, email) VALUES ('admin', '$password', 'admin@example.com')";
    
    if ($conn->query($sql)) {
        echo "Admin user created successfully!\n";
    } else {
        die("Error creating admin user: " . $conn->error);
    }
} else {
    echo "Admin user already exists.\n";
}

// Test admin credentials
$result = $conn->query("SELECT * FROM admins WHERE username = 'admin'");
if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    if (password_verify('admin123', $admin['password'])) {
        echo "Password verification successful!\n";
    } else {
        echo "Password verification failed!\n";
    }
}

echo "\nDatabase setup complete!\n";
echo "You can now login with:\n";
echo "Username: admin\n";
echo "Password: admin123\n";

$conn->close();
?>
