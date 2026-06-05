<?php
// PHP info test
echo "PHP Version: " . phpversion() . "\n";
echo "Loaded Extensions:\n";
$extensions = get_loaded_extensions();
foreach ($extensions as $ext) {
    if (strpos($ext, 'mysql') !== false || strpos($ext, 'mysqli') !== false) {
        echo "- $ext\n";
    }
}

// Test basic file operations
echo "\nTesting file operations...\n";
$sqlFile = 'database/student_db.sql';
if (file_exists($sqlFile)) {
    echo "SQL file exists: $sqlFile\n";
} else {
    echo "SQL file NOT found: $sqlFile\n";
}

// Check if we can create a simple database connection test
echo "\nTesting database connection...\n";
try {
    if (function_exists('mysqli_connect')) {
        echo "mysqli_connect function exists\n";
        $conn = mysqli_connect('localhost', 'root', '');
        if ($conn) {
            echo "Connected to MySQL successfully\n";
            mysqli_close($conn);
        } else {
            echo "MySQL connection failed\n";
        }
    } else {
        echo "mysqli_connect function does NOT exist\n";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
?>
