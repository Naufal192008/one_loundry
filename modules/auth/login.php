<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';

if (isset($_SESSION['user_id'])) { header('Location: /laundry_lvl1/modules/dashboard/'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($username) || empty($password)) { $error = 'Username dan password wajib diisi!'; }
    else {
        $database = new Database(); $db = $database->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1 LIMIT 1");
        $stmt->execute([$username, $username]); $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            if ($user['role'] === 'cashier') { $pin = $_POST['pin'] ?? ''; if ($pin !== $user['pin']) { $error = 'PIN kasir tidak valid!'; goto render; } }
            $_SESSION['user_id'] = $user['id']; $_SESSION['username'] = $user['username']; $_SESSION['role'] = $user['role'];
            $_SESSION['franchise_id'] = $user['franchise_id']; $_SESSION['outlet_id'] = $user['outlet_id'];
            $_SESSION['theme'] = $user['theme'] ?? 'light';
            $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?"); $stmt->execute([$user['id']]);
            header('Location: /laundry_lvl1/modules/dashboard/'); exit;
        } else { $error = 'Username atau password salah!'; }
    }
}
render:
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--primary:#6366F1;--primary-dark:#4F46E5;--primary-bg:#EEF2FF;--gray-50:#F8FAFC;--gray-200:#E2E8F0;--gray-500:#64748B;--gray-700:#334155;--gray-900:#0F172A;--white:#FFFFFF;--danger:#EF4444;--radius:12px;--radius-xl:20px;--shadow-xl:0 20px 60px rgba(0,0,0,0.1);--font:'Plus Jakarta Sans',sans-serif}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:var(--font);min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#EEF2FF 0%,#F8FAFC 30%,#E0E7FF 60%,#F8FAFC 100%);padding:20px}
        .login-container{width:100%;max-width:440px;animation:slideUp 0.6s ease}
        .login-card{background:rgba(255,255,255,0.9);backdrop-filter:blur(20px);border-radius:var(--radius-xl);padding:48px 40px;box-shadow:var(--shadow-xl);border:1px solid rgba(255,255,255,0.3)}
        .logo-section{text-align:center;margin-bottom:32px}
        .logo-icon{width:72px;height:72px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));border-radius:20px;display:inline-flex;align-items:center;justify-content:center;font-size:36px;margin-bottom:16px;box-shadow:0 8px 24px rgba(99,102,241,0.3)}
        .logo-section h1{font-size:24px;font-weight:800;color:var(--gray-900);margin-bottom:4px}
        .logo-section p{color:var(--gray-500);font-size:14px}
        .form-group{margin-bottom:20px}
        .form-label{display:block;font-size:13px;font-weight:700;color:var(--gray-700);margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px}
        .form-input{width:100%;padding:14px 16px;border:2px solid var(--gray-200);border-radius:var(--radius);font-size:15px;font-family:var(--font);background:var(--white);transition:all 0.2s}
        .form-input:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 4px var(--primary-bg)}
        .btn-login{width:100%;padding:16px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:white;border:none;border-radius:var(--radius);font-size:16px;font-weight:700;cursor:pointer;font-family:var(--font);transition:all 0.3s;margin-top:8px}
        .btn-login:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(99,102,241,0.4)}
        .error-message{background:#FEE2E2;color:#991B1B;padding:12px 16px;border-radius:var(--radius);margin-bottom:20px;font-size:14px;border-left:4px solid #EF4444;animation:shake 0.5s ease}
        @keyframes slideUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
        @keyframes shake{0%,100%{transform:translateX(0)}25%{transform:translateX(-6px)}75%{transform:translateX(6px)}}
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-section"><div class="logo-icon">🧺</div><h1><?= APP_NAME ?></h1><p>Level 5 • Ultimate Version</p></div>
            <?php if ($error): ?><div class="error-message">❌ <?= htmlspecialchars($error) ?></div><?php endif; ?>
            <form method="POST">
                <div class="form-group"><label class="form-label">Username atau Email</label><input type="text" name="username" class="form-input" placeholder="Masukkan username atau email" required autofocus></div>
                <div class="form-group"><label class="form-label">Password</label><input type="password" name="password" class="form-input" placeholder="Masukkan password" required></div>
                <div class="form-group" id="pinField" style="display:none;"><label class="form-label">PIN Kasir (6 digit)</label><input type="password" name="pin" class="form-input" placeholder="Masukkan PIN" maxlength="6"></div>
                <button type="submit" class="btn-login">🔐 Masuk ke Sistem</button>
            </form>
            <p style="text-align:center;margin-top:20px;font-size:12px;color:var(--gray-500);">© <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.</p>
        </div>
    </div>
    <script>document.querySelector('input[name="username"]').addEventListener('input',function(){document.getElementById('pinField').style.display=this.value.toLowerCase()==='kasir'?'block':'none'});</script>
</body>
</html>