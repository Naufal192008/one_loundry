<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo '<h1>DEBUG</h1>';

echo '<h3>1. File Includes:</h3>';
$files = [
    '../../config/database.php',
    '../../config/constants.php',
    '../../includes/auth.php',
    '../../includes/functions.php',
    '../../includes/header.php',
    '../../includes/sidebar.php',
    '../../includes/footer.php',
];

foreach ($files as $f) {
    $path = __DIR__ . '/' . $f;
    echo $f . ' - ' . (file_exists($path) ? '✅ ADA' : '❌ TIDAK ADA') . '<br>';
}

echo '<h3>2. Database Connection:</h3>';
try {
    require_once '../../config/database.php';
    $db = new Database();
    $conn = $db->getConnection();
    echo '✅ Database connected<br>';
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM users");
    $row = $stmt->fetch();
    echo 'Users: ' . $row['total'] . '<br>';
    
    $stmt = $conn->query("SELECT * FROM users LIMIT 3");
    echo '<pre>'; print_r($stmt->fetchAll()); echo '</pre>';
} catch(Exception $e) {
    echo '❌ DB Error: ' . $e->getMessage();
}

echo '<h3>3. Session:</h3>';
session_start();
echo '<pre>'; print_r($_SESSION); echo '</pre>';
?>