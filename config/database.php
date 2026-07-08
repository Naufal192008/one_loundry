<?php
// ============================================
// config/database.php - Level 1
// Koneksi database PDO
// ============================================

// ============================================
// config/database.php - Updated for Railway
// ============================================

// Mengambil variabel dari Railway jika ada, jika tidak gunakan default lokal
define('DB_HOST', getenv('MYSQLHOST') ?: '127.0.0.1');
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'smart_laundry_level1');
define('DB_USER', getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');

// ... (sisanya tetap sama)

class Database {
    /** @var string */
    private $host = DB_HOST;
    
    /** @var string */
    private $db_name = DB_NAME;
    
    /** @var string */
    private $username = DB_USER;
    
    /** @var string */
    private $password = DB_PASS;
    
    /** @var PDO|null */
    private $conn = null;

    /**
     * Get database connection
     * @return PDO
     */
    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $e) {
            die("Koneksi database gagal: " . $e->getMessage());
        }
        return $this->conn;
    }
}
?>