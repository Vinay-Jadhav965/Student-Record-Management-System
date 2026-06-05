<?php
// Simple database test without mysqli
echo "<h2>Database Connection Test</h2>";

// Test if mysqli is available
if (!function_exists('mysqli_connect')) {
    echo "<p style='color: red;'>ERROR: mysqli extension is not loaded!</p>";
    echo "<p>Please enable mysqli extension in php.ini</p>";
    
    // Show available extensions
    echo "<h3>Available Extensions:</h3>";
    $extensions = get_loaded_extensions();
    echo "<ul>";
    foreach ($extensions as $ext) {
        echo "<li>$ext</li>";
    }
    echo "</ul>";
    
    // Try PDO
    if (class_exists('PDO')) {
        echo "<h3>PDO is available - trying PDO connection...</h3>";
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=student_db', 'root', '');
            echo "<p style='color: green;'>PDO Connection successful!</p>";
            
            // Check if admin table exists
            $stmt = $pdo->query("SELECT * FROM admins WHERE username = 'admin'");
            if ($stmt->rowCount() > 0) {
                echo "<p>Admin user found in database</p>";
            } else {
                echo "<p style='color: orange;'>Admin user not found</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>PDO Connection failed: " . $e->getMessage() . "</p>";
        }
    }
} else {
    echo "<p style='color: green;'>mysqli extension is available</p>";
    
    // Test mysqli connection
    $conn = mysqli_connect('localhost', 'root', '', 'student_db');
    if ($conn) {
        echo "<p style='color: green;'>mysqli Connection successful!</p>";
        
        // Check if admin table exists
        $result = mysqli_query($conn, "SELECT * FROM admins WHERE username = 'admin'");
        if (mysqli_num_rows($result) > 0) {
            echo "<p>Admin user found in database</p>";
        } else {
            echo "<p style='color: orange;'>Admin user not found</p>";
        }
        mysqli_close($conn);
    } else {
        echo "<p style='color: red;'>mysqli Connection failed</p>";
    }
}

// Show PHP info
echo "<h3>PHP Version: " . phpversion() . "</h3>";
?>
