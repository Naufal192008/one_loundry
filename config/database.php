<?php
// ============================================
// config/database.php - Railway Ready
// ============================================

class Database {
    private $host;
    private $port;
    private $db_name;
    private $username;
    private $password;
    private $conn = null;

    public function __construct() {
        // Railway MySQL environment variables
        $this->host = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'localhost';
        $this->port = getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: '3306';
        $this->db_name = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: getenv('DB_NAME') ?: 'laundry_db';
        $this->username = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root';
        $this->password = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_ROOT_PASSWORD') ?: getenv('DB_PASS') ?: '';
    }

    public function getConnection() {
        if ($this->conn) return $this->conn;
        
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                PDO::ATTR_TIMEOUT => 10
            ];
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
            return $this->conn;
            
        } catch(PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            error_log("Host: " . $this->host);
            error_log("Port: " . $this->port);
            error_log("Database: " . $this->db_name);
            error_log("Username: " . $this->username);
            
            die("Koneksi database gagal: " . $e->getMessage());
        }
    }
}
?>