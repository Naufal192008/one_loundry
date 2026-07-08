<?php
// ============================================
// config/database.php - Final Optimized
// ============================================

// Mengambil variabel dari Railway dengan pengecekan ganda
define('DB_HOST', getenv('MYSQLHOST') ?: '127.0.0.1');
define('DB_PORT', getenv('MYSQLPORT') ?: '3306');
define('DB_NAME', getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'smart_laundry_level1');
define('DB_USER', getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: getenv('MYSQL_ROOT_PASSWORD') ?: '');

class Database {
    private $host = DB_HOST;
    private $port = DB_PORT;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $conn = null;

    public function getConnection() {
        $this->conn = null;
        try {
            // Menyertakan port di dalam DSN agar koneksi ke Railway berhasil
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            
            $this->conn = new PDO(
                $dsn,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $e) {
            // Menampilkan pesan error yang membantu untuk debugging
            die("Koneksi database gagal: " . $e->getMessage());
        }
        return $this->conn;
    }
}
?>