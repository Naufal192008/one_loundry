<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Jika sudah login, arahkan langsung ke /modules/dashboard/ (Hapus /laundry_lvl1/)
if (isset($_SESSION['user_id'])) {
    header('Location: /modules/dashboard/');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi!';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        $stmt = $db->prepare("SELECT * FROM users WHERE username = :username AND is_active = 1 LIMIT 1");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            if ($user['role'] === 'cashier') {
                $pin = $_POST['pin'] ?? '';
                if ($pin !== $user['pin']) {
                    $error = 'PIN kasir tidak valid!';
                    goto render;
                }
            }
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['outlet_id'] = $user['outlet_id'];
            
            // Arahkan ke /modules/dashboard/ tanpa /laundry_lvl1/
            header('Location: /modules/dashboard/');
            exit();
        } else {
            $error = 'Username atau password salah!';
        }
    }
}
render:
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Laundry</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">🧺</div>
            <h1>Smart Laundry</h1>
            <p class="subtitle">Sistem Manajemen Laundry Digital</p>
            
            <?php if ($error): ?>
                <div style="background: #FEE2E2; color: #991B1B; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; border-left: 4px solid #FECACA;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="loginForm">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-input" placeholder="Masukkan username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" placeholder="Masukkan password" required>
                </div>
                
                <div class="form-group" id="pinField" style="display:none;">
                    <label class="form-label">PIN Kasir (4 digit)</label>
                    <input type="password" name="pin" class="form-input" placeholder="Masukkan PIN 4 digit" maxlength="4" pattern="[0-9]{4}">
                </div>
                
                <button type="submit" class="btn btn-primary" style="margin-top: 8px;">
                    🔐 Masuk ke Sistem
                </button>
            </form>
            
            <p style="text-align: center; margin-top: 20px; font-size: 13px; color: var(--gray-500);">
                Laundry Level 1 - Basic Package
            </p>
        </div>
    </div>
    
    <script>
    document.querySelector('input[name="username"]').addEventListener('input', function() {
        const pinField = document.getElementById('pinField');
        if (this.value.toLowerCase() === 'kasir') {
            pinField.style.display = 'block';
        } else {
            pinField.style.display = 'none';
        }
    });
    </script>
</body>
</html>