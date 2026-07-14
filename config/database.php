<?php
// ============================================
// config/database.php
// ============================================

class Database {
    /** @var string */
    private string $host;
    
    /** @var string */
    private string $port;
    
    /** @var string */
    private string $db_name;
    
    /** @var string */
    private string $username;
    
    /** @var string */
    private string $password;
    
    /** @var PDO|null */
    private ?PDO $conn = null;

    public function __construct() {
        $this->host = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'localhost';
        $this->port = getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: '3306';
        $this->db_name = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: getenv('DB_NAME') ?: 'laundry_db';
        $this->username = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root';
        $this->password = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_ROOT_PASSWORD') ?: getenv('DB_PASS') ?: '';
    }

    /**
     * @return PDO
     */
    public function getConnection(): PDO {
        if ($this->conn) return $this->conn;
        
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";
            
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                PDO::ATTR_TIMEOUT => 10
            ]);
            
            return $this->conn;
            
        } catch(PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            die("Koneksi database gagal: " . $e->getMessage());
        }
    }
}
?>