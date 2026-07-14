<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo '<!DOCTYPE html>
<html>
<head>
    <title>System Check - LaundryKu</title>
    <style>
        body{font-family:monospace;background:#1a1a2e;color:#e0e0e0;padding:20px;max-width:900px;margin:0 auto}
        h1{color:#6366F1;border-bottom:2px solid #6366F1;padding-bottom:10px}
        h2{color:#10B981;margin-top:30px}
        .pass{color:#10B981}.fail{color:#EF4444}.warn{color:#F59E0B}
        .item{padding:8px;margin:4px 0;background:#16213e;border-radius:4px;border-left:3px solid #6366F1}
        pre{background:#0f0f23;padding:10px;border-radius:4px;overflow-x:auto;font-size:12px}
        .btn{display:inline-block;padding:10px 20px;background:#6366F1;color:white;text-decoration:none;border-radius:6px;margin:5px}
    </style>
</head>
<body>
<h1>🔍 LaundryKu System Check</h1>';

// ============================================
// 1. PHP VERSION
// ============================================
echo '<h2>1. PHP Version</h2>';
echo '<div class="item">PHP Version: <span class="pass">' . phpversion() . '</span></div>';

// ============================================
// 2. FILE CHECK
// ============================================
echo '<h2>2. File Structure Check</h2>';
$files = [
    'config/database.php',
    'config/constants.php',
    'config/app.php',
    'includes/auth.php',
    'includes/functions.php',
    'includes/header.php',
    'includes/footer.php',
    'includes/sidebar.php',
    'modules/auth/login.php',
    'modules/auth/logout.php',
    'modules/dashboard/index.php',
    'modules/transactions/index.php',
    'modules/transactions/create.php',
    'modules/transactions/detail.php',
    'modules/transactions/edit.php',
    'modules/transactions/delete.php',
    'modules/customers/index.php',
    'modules/customers/create.php',
    'modules/customers/edit.php',
    'qr/track.php',
];

foreach ($files as $f) {
    $path = __DIR__ . '/../../' . $f;
    $exists = file_exists($path);
    $size = $exists ? filesize($path) : 0;
    $color = $exists ? ($size > 100 ? 'pass' : 'warn') : 'fail';
    $icon = $exists ? ($size > 100 ? '✅' : '⚠️') : '❌';
    echo "<div class='item'>{$icon} <span class='{$color}'>{$f}</span> - " . ($exists ? number_format($size) . ' bytes' : 'NOT FOUND') . "</div>";
}

// ============================================
// 3. DATABASE CHECK
// ============================================
echo '<h2>3. Database Check</h2>';
try {
    require_once __DIR__ . '/../../config/database.php';
    $db = new Database();
    $conn = $db->getConnection();
    echo "<div class='item'>✅ <span class='pass'>Database Connected</span></div>";
    
    // Cek tabel
    $tables = ['users', 'customers', 'services', 'transactions', 'outlets', 'transaction_logs'];
    $stmt = $conn->query("SHOW TABLES");
    $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        $exists = in_array($table, $existingTables);
        echo "<div class='item'>" . ($exists ? '✅' : '❌') . " Table '{$table}': " . 
             ($exists ? "<span class='pass'>EXISTS</span>" : "<span class='fail'>MISSING</span>") . "</div>";
    }
    
    // Cek users
    $stmt = $conn->query("SELECT COUNT(*) as total FROM users");
    $userCount = $stmt->fetch()['total'];
    echo "<div class='item'>👤 Users: <span class='pass'>{$userCount}</span></div>";
    
    // Cek customers
    $stmt = $conn->query("SELECT COUNT(*) as total FROM customers");
    $custCount = $stmt->fetch()['total'];
    echo "<div class='item'>👥 Customers: <span class='pass'>{$custCount}</span></div>";
    
    // Cek services
    $stmt = $conn->query("SELECT COUNT(*) as total FROM services");
    $svcCount = $stmt->fetch()['total'];
    echo "<div class='item'>🏷️ Services: <span class='pass'>{$svcCount}</span></div>";
    
    // Cek transactions
    $stmt = $conn->query("SELECT COUNT(*) as total FROM transactions");
    $trxCount = $stmt->fetch()['total'];
    echo "<div class='item'>🧾 Transactions: <span class='pass'>{$trxCount}</span></div>";
    
    // Cek kolom users
    echo "<h3>Users Table Columns:</h3>";
    $stmt = $conn->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<div class='item'>" . implode(', ', $columns) . "</div>";
    
    // Cek data users
    echo "<h3>Users Data:</h3>";
    $stmt = $conn->query("SELECT id, name, username, role, pin, is_active FROM users");
    echo '<pre>';
    foreach ($stmt->fetchAll() as $row) {
        echo "ID:{$row['id']} | {$row['name']} | {$row['username']} | {$row['role']} | PIN:{$row['pin']} | Active:{$row['is_active']}\n";
    }
    echo '</pre>';
    
} catch(Exception $e) {
    echo "<div class='item'>❌ <span class='fail'>Database Error: " . $e->getMessage() . "</span></div>";
}

// ============================================
// 4. SESSION CHECK
// ============================================
echo '<h2>4. Session Check</h2>';
session_start();
if (isset($_SESSION['user_id'])) {
    echo "<div class='item'>✅ <span class='pass'>Logged In</span></div>";
    echo "<div class='item'>User ID: {$_SESSION['user_id']}</div>";
    echo "<div class='item'>Username: {$_SESSION['username']}</div>";
    echo "<div class='item'>Role: {$_SESSION['role']}</div>";
    echo "<div class='item'>Outlet ID: " . ($_SESSION['outlet_id'] ?? 'N/A') . "</div>";
} else {
    echo "<div class='item'>❌ <span class='fail'>NOT Logged In</span></div>";
    echo "<div class='item'><a href='/laundry_lvl1/modules/auth/login.php' class='btn'>Go to Login</a></div>";
}

// ============================================
// 5. INCLUDE TEST
// ============================================
echo '<h2>5. Include Test</h2>';

echo '<h3>Testing header.php:</h3>';
try {
    ob_start();
    require_once __DIR__ . '/../../includes/header.php';
    $headerOutput = ob_get_clean();
    $headerLen = strlen($headerOutput);
    echo "<div class='item'>✅ header.php included ({$headerLen} bytes)</div>";
} catch (Exception $e) {
    echo "<div class='item'>❌ header.php Error: " . $e->getMessage() . "</div>";
}

echo '<h3>Testing functions.php:</h3>';
if (function_exists('formatRupiah')) {
    echo "<div class='item'>✅ formatRupiah(10000) = " . formatRupiah(10000) . "</div>";
} else {
    echo "<div class='item'>❌ formatRupiah not found</div>";
}

if (function_exists('getStatusInfo')) {
    $status = getStatusInfo('queue');
    echo "<div class='item'>✅ getStatusInfo('queue') = {$status['label']}</div>";
} else {
    echo "<div class='item'>❌ getStatusInfo not found</div>";
}

// ============================================
// 6. PHP EXTENSIONS
// ============================================
echo '<h2>6. PHP Extensions</h2>';
$extensions = ['pdo', 'pdo_mysql', 'curl', 'json', 'mbstring', 'session'];
foreach ($extensions as $ext) {
    $loaded = extension_loaded($ext);
    echo "<div class='item'>" . ($loaded ? '✅' : '❌') . " {$ext}: " . 
         ($loaded ? "<span class='pass'>LOADED</span>" : "<span class='fail'>MISSING</span>") . "</div>";
}

// ============================================
// 7. ERROR LOGS
// ============================================
echo '<h2>7. Recent PHP Errors</h2>';
$logFile = ini_get('error_log');
echo "<div class='item'>Error log file: {$logFile}</div>";

if (file_exists($logFile) && is_readable($logFile)) {
    $logs = file($logFile);
    $logs = array_slice($logs, -20);
    echo '<pre>';
    foreach ($logs as $log) {
        echo htmlspecialchars($log);
    }
    echo '</pre>';
} else {
    echo "<div class='item'>⚠️ Cannot read error log</div>";
}

// ============================================
// 8. QUICK LINKS
// ============================================
echo '<h2>8. Quick Links</h2>';
echo "<a href='/laundry_lvl1/modules/auth/login.php' class='btn'>Login Page</a> ";
echo "<a href='/laundry_lvl1/modules/dashboard/' class='btn'>Dashboard</a> ";
echo "<a href='/laundry_lvl1/modules/transactions/' class='btn'>Transactions</a> ";

echo '<hr style="margin:30px 0;border-color:#333">';
echo '<p style="text-align:center;color:#666">LaundryKu System Check v1.0 | ' . date('Y-m-d H:i:s') . '</p>';

echo '</body></html>';
?>
