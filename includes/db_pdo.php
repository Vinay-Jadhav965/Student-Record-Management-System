<?php
// Database configuration with fallback support
$host = 'localhost';
$dbname = 'student_db';
$username = 'root';
$password = '';

// Try PDO first (more reliable)
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create a wrapper for mysqli compatibility
    class mysqli_wrapper {
        private $pdo;
        public $connect_error;
        
        public function __construct($host, $username, $password, $dbname) {
            global $pdo;
            $this->pdo = $pdo;
        }
        
        public function prepare($sql) {
            return new stmt_wrapper($this->pdo->prepare($sql));
        }
        
        public function query($sql) {
            return new result_wrapper($this->pdo->query($sql));
        }
        
        public function set_charset($charset) {
            // PDO handles charset automatically
        }
        
        public function close() {
            // PDO handles connection automatically
        }
    }
    
    class stmt_wrapper {
        private $stmt;
        
        public function __construct($stmt) {
            $this->stmt = $stmt;
        }
        
        public function bind_param($types, ...$params) {
            // Simple bind_param implementation
            for ($i = 0; $i < count($params); $i++) {
                $type = $types[$i] ?? 's';
                if ($type == 'i') {
                    $this->stmt->bindValue($i + 1, $params[$i], PDO::PARAM_INT);
                } else {
                    $this->stmt->bindValue($i + 1, $params[$i], PDO::PARAM_STR);
                }
            }
        }
        
        public function execute() {
            return $this->stmt->execute();
        }
        
        public function get_result() {
            return new result_wrapper($this->stmt);
        }
        
        public function close() {
            // PDO handles this automatically
        }
    }
    
    class result_wrapper {
        private $stmt;
        
        public function __construct($stmt) {
            if ($stmt instanceof PDOStatement) {
                $this->stmt = $stmt;
            } else {
                $this->stmt = $stmt;
            }
        }
        
        public function fetch_assoc() {
            if ($this->stmt instanceof PDOStatement) {
                return $this->stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                return $this->stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
        
        public function num_rows() {
            if ($this->stmt instanceof PDOStatement) {
                return $this->stmt->rowCount();
            } else {
                return $this->stmt->rowCount();
            }
        }
    }
    
    // Create connection using wrapper
    $conn = new mysqli_wrapper($host, $username, $password, $dbname);
    
} catch (PDOException $e) {
    // Fallback to mysqli if available
    if (function_exists('mysqli_connect')) {
        $conn = new mysqli($host, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $conn->set_charset("utf8");
    } else {
        die("Database connection failed. Neither PDO nor mysqli are available: " . $e->getMessage());
    }
}
?>
