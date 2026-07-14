<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireLogin();
$db = (new Database())->getConnection();
$currentUser = getCurrentUser($db);
$outletId = $_SESSION['outlet_id'] ?? 1;
$stmt = $db->prepare("SELECT t.*, c.name as customer_name, s.name as service_name FROM transactions t JOIN customers c ON t.customer_id = c.id JOIN services s ON t.service_id = s.id WHERE t.outlet_id = ? ORDER BY t.created_at DESC LIMIT 50");
$stmt->execute([$outletId]);
$transactions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi - LaundryKu</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--primary:#6366F1;--primary-dark:#4F46E5;--primary-bg:#EEF2FF;--success:#10B981;--warning:#F59E0B;--danger:#EF4444;--gray-50:#F8FAFC;--gray-100:#F1F5F9;--gray-200:#E2E8F0;--gray-500:#64748B;--gray-700:#334155;--gray-900:#0F172A;--white:#FFFFFF;--radius:12px;--radius-sm:8px;--shadow:0 1px 3px rgba(0,0,0,0.1);--font:'Plus Jakarta Sans',sans-serif}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:var(--font);background:var(--gray-50);color:var(--gray-900);display:flex;min-height:100vh}
        .sidebar{width:260px;background:var(--white);border-right:1px solid var(--gray-200);position:fixed;top:0;left:0;bottom:0;z-index:100;display:flex;flex-direction:column;padding:20px 12px}
        .sidebar a{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:var(--radius-sm);text-decoration:none;color:var(--gray-700);font-weight:500;font-size:13px;margin-bottom:2px}
        .sidebar a:hover,.sidebar a.active{background:var(--primary-bg);color:var(--primary)}
        .main-content{flex:1;margin-left:260px;padding:24px}
        .top-bar{background:var(--white);padding:14px 24px;border-bottom:1px solid var(--gray-200);display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;border-radius:var(--radius)}
        .card{background:var(--white);border-radius:var(--radius);border:1px solid var(--gray-200);padding:24px;box-shadow:var(--shadow)}
        .btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:var(--radius-sm);font-weight:600;font-size:13px;text-decoration:none;cursor:pointer;border:none;font-family:var(--font)}
        .btn-primary{background:var(--primary);color:white}.btn-primary:hover{background:var(--primary-dark)}
        .btn-secondary{background:var(--gray-100);color:var(--gray-700)}.btn-sm{padding:5px 10px;font-size:11px}.btn-danger{background:var(--danger);color:white}
        table{width:100%;border-collapse:collapse}
        th{background:var(--gray-50);padding:10px 14px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;color:var(--gray-500);border-bottom:2px solid var(--gray-200)}
        td{padding:12px 14px;font-size:13px;border-bottom:1px solid var(--gray-100)}
        tr:hover td{background:var(--gray-50)}
        .badge{display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:20px;font-size:10px;font-weight:600}
        .badge-success{background:#D1FAE5;color:#065F46}.badge-warning{background:#FEF3C7;color:#92400E}
        @media(max-width:768px){.sidebar{width:70px}.sidebar a span{display:none}.main-content{margin-left:70px}}
    </style>
</head>
<body>
    <aside class="sidebar">
        <div style="font-size:18px;font-weight:800;padding:10px 12px;margin-bottom:16px;">🧺 LaundryKu</div>
        <a href="/laundry_lvl1/modules/dashboard/">📊 <span>Dashboard</span></a>
        <a href="/laundry_lvl1/modules/transactions/" class="active">🧾 <span>Transaksi</span></a>
        <a href="/laundry_lvl1/modules/customers/">👥 <span>Pelanggan</span></a>
        <a href="/laundry_lvl1/modules/whatsapp/">📱 <span>WhatsApp</span></a>
        <a href="/laundry_lvl1/modules/loyalty/">⭐ <span>Loyalti</span></a>
        <a href="/laundry_lvl1/modules/reports/">📋 <span>Laporan</span></a>
        <a href="/laundry_lvl1/modules/auth/logout.php" style="margin-top:auto;background:var(--gray-100);">🚪 <span>Keluar</span></a>
    </aside>
    <main class="main-content">
        <div class="top-bar"><h1>🧾 Transaksi</h1><div><?= htmlspecialchars($currentUser['name'] ?? 'User') ?> (<?= ucfirst($currentUser['role'] ?? '') ?>)</div></div>
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h2>Daftar Transaksi</h2>
                <a href="/laundry_lvl1/modules/transactions/create.php" class="btn btn-primary">+ Transaksi Baru</a>
            </div>
            <div style="overflow-x:auto;">
                <table>
                    <thead><tr><th>Invoice</th><th>Pelanggan</th><th>Layanan</th><th>Berat</th><th>Total</th><th>Status</th><th>Bayar</th><th>Aksi</th></tr></thead>
                    <tbody>
                        <?php if(empty($transactions)): ?><tr><td colspan="8" style="text-align:center;padding:40px;">📭 Belum ada transaksi</td></tr>
                        <?php else: foreach($transactions as $t): ?>
                        <tr>
                            <td><strong style="color:var(--primary)"><?= htmlspecialchars($t['invoice_number']) ?></strong></td>
                            <td><?= htmlspecialchars($t['customer_name']) ?></td>
                            <td><?= htmlspecialchars($t['service_name']) ?></td>
                            <td><?= $t['weight_kg'] ?> kg</td>
                            <td><strong><?= formatRupiah($t['total_price']) ?></strong></td>
                            <td><?= $t['laundry_status'] ?></td>
                            <td><span class="badge badge-<?= $t['payment_status']==='paid'?'success':'warning' ?>"><?= $t['payment_status']==='paid'?'Lunas':'Belum' ?></span></td>
                            <td style="display:flex;gap:4px;">
                                <a href="/laundry_lvl1/modules/transactions/detail.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-secondary">Detail</a>
                                <a href="/laundry_lvl1/modules/transactions/edit.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>