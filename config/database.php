<?php
// config/database.php
// Subject: Web Technology - Server-side programming

class Database {
    private $host = 'localhost';
    private $db_name = 'habit_tracker';
    private $username = 'root';  // Default XAMPP username
    private $password = 'Admin@123';      // Default XAMPP password (empty)
    private $conn;

    // Get database connection
    public function getConnection() {
        $this->conn = null;

        try {
            // Create PDO connection
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            
            // Set PDO attributes for error handling
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->exec("set names utf8");
            
        } catch(PDOException $e) {
            // Log error (in production, don't show detailed errors)
            error_log("Connection error: " . $e->getMessage());
            return null;
        }

        return $this->conn;
    }

    // Test connection (useful for debugging)
    public function testConnection() {
        try {
            $conn = $this->getConnection();
            if($conn) {
                return "Database connection successful!";
            } else {
                return "Database connection failed!";
            }
        } catch(Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }
}

// Create global database instance
$database = new Database();
$db = $database->getConnection();
?>