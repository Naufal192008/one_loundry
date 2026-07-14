<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireLogin();
$db = (new Database())->getConnection();
$currentUser = getCurrentUser($db);
$outletId = $_SESSION['outlet_id'] ?? 1;
$stmt = $db->prepare("SELECT * FROM customers WHERE outlet_id = ? ORDER BY name");
$stmt->execute([$outletId]);
$customers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelanggan - LaundryKu</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--primary:#6366F1;--primary-dark:#4F46E5;--primary-bg:#EEF2FF;--gray-50:#F8FAFC;--gray-100:#F1F5F9;--gray-200:#E2E8F0;--gray-500:#64748B;--gray-700:#334155;--gray-900:#0F172A;--white:#FFFFFF;--radius:12px;--radius-sm:8px;--shadow:0 1px 3px rgba(0,0,0,0.1);--font:'Plus Jakarta Sans',sans-serif}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:var(--font);background:var(--gray-50);color:var(--gray-900);display:flex;min-height:100vh}
        .sidebar{width:260px;background:var(--white);border-right:1px solid var(--gray-200);position:fixed;top:0;left:0;bottom:0;z-index:100;display:flex;flex-direction:column;padding:20px 12px}
        .sidebar a{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:var(--radius-sm);text-decoration:none;color:var(--gray-700);font-weight:500;font-size:13px;margin-bottom:2px}
        .sidebar a:hover,.sidebar a.active{background:var(--primary-bg);color:var(--primary)}
        .main-content{flex:1;margin-left:260px;padding:24px}
        .top-bar{background:var(--white);padding:14px 24px;border-bottom:1px solid var(--gray-200);display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;border-radius:var(--radius)}
        .card{background:var(--white);border-radius:var(--radius);border:1px solid var(--gray-200);padding:24px;box-shadow:var(--shadow)}
        .btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:var(--radius-sm);font-weight:600;font-size:13px;text-decoration:none;cursor:pointer;border:none;font-family:var(--font)}
        .btn-primary{background:var(--primary);color:white}.btn-sm{padding:5px 10px;font-size:11px}
        table{width:100%;border-collapse:collapse}
        th{background:var(--gray-50);padding:10px 14px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;color:var(--gray-500);border-bottom:2px solid var(--gray-200)}
        td{padding:12px 14px;font-size:13px;border-bottom:1px solid var(--gray-100)}
        @media(max-width:768px){.sidebar{width:70px}.sidebar a span{display:none}.main-content{margin-left:70px}}
    </style>
</head>
<body>
    <aside class="sidebar">
        <div style="font-size:18px;font-weight:800;padding:10px 12px;margin-bottom:16px;">🧺 LaundryKu</div>
        <a href="/laundry_lvl1/modules/dashboard/">📊 <span>Dashboard</span></a>
        <a href="/laundry_lvl1/modules/transactions/">🧾 <span>Transaksi</span></a>
        <a href="/laundry_lvl1/modules/customers/" class="active">👥 <span>Pelanggan</span></a>
        <a href="/laundry_lvl1/modules/whatsapp/">📱 <span>WhatsApp</span></a>
        <a href="/laundry_lvl1/modules/loyalty/">⭐ <span>Loyalti</span></a>
        <a href="/laundry_lvl1/modules/reports/">📋 <span>Laporan</span></a>
        <a href="/laundry_lvl1/modules/auth/logout.php" style="margin-top:auto;background:var(--gray-100);">🚪 <span>Keluar</span></a>
    </aside>
    <main class="main-content">
        <div class="top-bar"><h1>👥 Pelanggan</h1><div><?= htmlspecialchars($currentUser['name'] ?? 'User') ?></div></div>
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h2>Daftar Pelanggan</h2>
                <a href="/laundry_lvl1/modules/customers/create.php" class="btn btn-primary">+ Pelanggan Baru</a>
            </div>
            <table>
                <thead><tr><th>Nama</th><th>Telepon</th><th>Poin</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php if(empty($customers)): ?><tr><td colspan="4" style="text-align:center;padding:40px;">📭 Belum ada pelanggan</td></tr>
                    <?php else: foreach($customers as $c): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
                        <td><?= htmlspecialchars($c['phone'] ?: '-') ?></td>
                        <td>⭐ <?= number_format($c['loyalty_points']) ?></td>
                        <td><a href="/laundry_lvl1/modules/customers/edit.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-primary">Edit</a></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>