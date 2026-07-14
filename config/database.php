<?php
class Database {
    private $host;
    private $port;
    private $db_name;
    private $username;
    private $password;
    private $conn = null;

    public function __construct() {
        // RAILWAY: Pakai nama variabel yang SESUAI dengan Railway
        $this->host = getenv('MYSQLHOST') ?: 'localhost';
        $this->port = getenv('MYSQLPORT') ?: '3306';
        $this->db_name = getenv('MYSQL_DATABASE') ?: 'railway';
        $this->username = getenv('MYSQLUSER') ?: 'root';
        $this->password = getenv('MYSQL_ROOT_PASSWORD') ?: '';
    }

    public function getConnection() {
        if ($this->conn) return $this->conn;
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 10
            ]);
            return $this->conn;
        } catch(PDOException $e) {
            error_log("DB Error: " . $e->getMessage());
            die("Koneksi database gagal. Pastikan MySQL sudah terhubung.");
        }
    }
}