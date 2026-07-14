<?php
// ============================================
// modules/dashboard/index.php - LaundryKu Level 5
// Dashboard Utama (Self-Contained)
// ============================================

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

$db = (new Database())->getConnection();
$currentUser = getCurrentUser($db);

$outletId = $_SESSION['outlet_id'] ?? 1;
$today = date('Y-m-d');

// ============================================
// STATISTIK HARI INI
// ============================================
$stmt = $db->prepare("SELECT COUNT(*) as total, COALESCE(SUM(total_price),0) as revenue, COALESCE(SUM(CASE WHEN payment_status='paid' THEN total_price ELSE 0 END),0) as paid FROM transactions WHERE outlet_id = ? AND DATE(created_at) = ?");
$stmt->execute([$outletId, $today]);
$todayStats = $stmt->fetch();

// ============================================
// STATUS CUCIAN
// ============================================
$stmt = $db->prepare("SELECT laundry_status, COUNT(*) as count FROM transactions WHERE outlet_id = ? AND DATE(created_at) = ? GROUP BY laundry_status");
$stmt->execute([$outletId, $today]);
$statusCounts = [];
foreach ($stmt->fetchAll() as $row) $statusCounts[$row['laundry_status']] = $row['count'];

// ============================================
// TOTAL PELANGGAN
// ============================================
$stmt = $db->prepare("SELECT COUNT(*) as total FROM customers WHERE outlet_id = ?");
$stmt->execute([$outletId]);
$custTotal = $stmt->fetch()['total'];

// ============================================
// TRANSAKSI TERBARU
// ============================================
$stmt = $db->prepare("SELECT t.*, c.name as customer_name, s.name as service_name FROM transactions t JOIN customers c ON t.customer_id = c.id JOIN services s ON t.service_id = s.id WHERE t.outlet_id = ? ORDER BY t.created_at DESC LIMIT 5");
$stmt->execute([$outletId]);
$recentTrx = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - LaundryKu</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366F1; --primary-dark: #4F46E5; --primary-bg: #EEF2FF;
            --success: #10B981; --warning: #F59E0B; --danger: #EF4444;
            --gray-50: #F8FAFC; --gray-100: #F1F5F9; --gray-200: #E2E8F0; --gray-500: #64748B; --gray-700: #334155; --gray-900: #0F172A;
            --white: #FFFFFF; --radius: 12px; --radius-sm: 8px; --radius-lg: 16px;
            --shadow: 0 1px 3px rgba(0,0,0,0.1); --shadow-md: 0 4px 6px rgba(0,0,0,0.07); --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
            --font: 'Plus Jakarta Sans', sans-serif;
        }
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:var(--font);background:var(--gray-50);color:var(--gray-900);line-height:1.6;min-height:100vh}
        
        .app-container{display:flex;min-height:100vh}
        
        /* SIDEBAR */
        .sidebar{width:260px;background:var(--white);border-right:1px solid var(--gray-200);position:fixed;top:0;left:0;bottom:0;z-index:100;box-shadow:var(--shadow-lg);display:flex;flex-direction:column;transition:all 0.3s}
        .sidebar-header{padding:20px;border-bottom:1px solid var(--gray-200);display:flex;align-items:center;gap:10px}
        .sidebar-logo{width:40px;height:40px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));border-radius:var(--radius);display:flex;align-items:center;justify-content:center;font-size:20px;color:white;flex-shrink:0}
        .sidebar-brand h2{font-size:15px;font-weight:800;color:var(--gray-900);line-height:1.2}
        .sidebar-brand span{font-size:10px;color:var(--primary);font-weight:600;text-transform:uppercase;letter-spacing:1px}
        .sidebar-nav{flex:1;padding:12px;display:flex;flex-direction:column;gap:2px;overflow-y:auto}
        .nav-section{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--gray-500);padding:12px 12px 4px;margin-top:8px}
        .nav-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:var(--radius-sm);text-decoration:none;color:var(--gray-700);font-weight:500;font-size:13px;transition:all 0.2s}
        .nav-item:hover{background:var(--primary-bg);color:var(--primary)}
        .nav-item.active{background:var(--primary-bg);color:var(--primary);font-weight:700}
        .nav-icon{font-size:18px;width:22px;text-align:center;flex-shrink:0}
        .sidebar-footer{padding:12px;border-top:1px solid var(--gray-200)}
        .sidebar-footer a{display:flex;align-items:center;gap:8px;padding:10px 12px;background:var(--gray-100);border-radius:var(--radius-sm);text-decoration:none;color:var(--gray-700);font-weight:500;width:100%}
        .sidebar-footer a:hover{background:#FEE2E2;color:var(--danger)}
        
        /* MAIN */
        .main-content{flex:1;margin-left:260px;min-height:100vh;transition:margin-left 0.3s}
        .top-bar{background:var(--white);padding:14px 24px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--gray-200);position:sticky;top:0;z-index:50}
        .top-bar h1{font-size:20px;font-weight:700}
        .top-bar-right{display:flex;align-items:center;gap:16px}
        .theme-btn{background:var(--gray-100);border:none;cursor:pointer;font-size:20px;padding:8px 12px;border-radius:var(--radius-sm);transition:all 0.2s}
        .theme-btn:hover{background:var(--primary-bg);transform:scale(1.1)}
        .user-info{display:flex;align-items:center;gap:10px;font-size:13px}
        .user-avatar{width:36px;height:36px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:700;flex-shrink:0}
        .content-area{padding:20px 24px}
        
        /* CARDS */
        .card{background:var(--white);border-radius:var(--radius-lg);border:1px solid var(--gray-200);box-shadow:var(--shadow);margin-bottom:20px;overflow:hidden}
        .card-header{padding:16px 20px;border-bottom:1px solid var(--gray-100);display:flex;justify-content:space-between;align-items:center}
        .card-title{font-size:16px;font-weight:700}
        .card-body{padding:20px}
        
        /* STATS */
        .stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px}
        .stat-card{background:var(--white);border-radius:var(--radius-lg);padding:20px;border:1px solid var(--gray-200);box-shadow:var(--shadow);transition:all 0.3s;cursor:pointer;position:relative;overflow:hidden}
        .stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px}
        .stat-card.c1::before{background:var(--primary)}.stat-card.c2::before{background:var(--success)}.stat-card.c3::before{background:var(--warning)}.stat-card.c4::before{background:#8B5CF6}
        .stat-card:hover{transform:translateY(-2px);box-shadow:var(--shadow-md)}
        .stat-card .icon{font-size:32px;margin-bottom:8px}
        .stat-card .label{font-size:12px;color:var(--gray-500);margin-bottom:4px}
        .stat-card .value{font-size:24px;font-weight:800;color:var(--gray-900)}
        
        /* STATUS GRID */
        .status-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:10px;text-align:center}
        .status-item{padding:16px 8px;border-radius:var(--radius);border:2px solid}
        .status-item .icon{font-size:28px}
        .status-item .count{font-size:22px;font-weight:800}
        .status-item .label{font-size:11px;margin-top:4px}
        
        /* BUTTONS */
        .btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:var(--radius-sm);font-weight:600;font-size:13px;cursor:pointer;border:none;text-decoration:none;transition:all 0.2s;font-family:var(--font)}
        .btn-primary{background:var(--primary);color:white}.btn-primary:hover{background:var(--primary-dark)}
        .btn-secondary{background:var(--gray-100);color:var(--gray-700)}.btn-secondary:hover{background:var(--gray-200)}
        .btn-sm{padding:5px 10px;font-size:11px}
        
        /* QUICK ACTIONS */
        .quick-actions{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
        .quick-action-btn{display:flex;flex-direction:column;align-items:center;gap:6px;padding:20px 12px;background:var(--white);border:2px solid var(--gray-200);border-radius:var(--radius-lg);cursor:pointer;text-decoration:none;font-weight:600;font-size:13px;color:var(--gray-700);transition:all 0.2s}
        .quick-action-btn:hover{border-color:var(--primary);background:var(--primary-bg);color:var(--primary);transform:translateY(-2px)}
        .quick-action-btn .icon{font-size:30px}
        
        /* RECENT TRANSACTIONS */
        .trx-list{list-style:none}
        .trx-item{display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--gray-100);cursor:pointer;transition:background 0.2s}
        .trx-item:hover{background:var(--gray-50)}
        .trx-item:last-child{border-bottom:none}
        .trx-invoice{font-weight:700;color:var(--primary)}
        .trx-customer{font-size:12px;color:var(--gray-500)}
        .trx-amount{font-weight:700}
        
        .badge{display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:20px;font-size:10px;font-weight:600}
        .badge-success{background:#D1FAE5;color:#065F46}.badge-warning{background:#FEF3C7;color:#92400E}.badge-info{background:#DBEAFE;color:#1E40AF}
        
        /* RESPONSIVE */
        @media(max-width:1024px){.sidebar{width:70px}.sidebar-brand,.nav-item span:not(.nav-icon),.nav-section,.sidebar-footer a span{display:none}.nav-item{justify-content:center;padding:10px}.main-content{margin-left:70px}.stats-grid{grid-template-columns:repeat(2,1fr)}.status-grid{grid-template-columns:repeat(3,1fr)}}
        @media(max-width:768px){.stats-grid{grid-template-columns:1fr 1fr}.quick-actions{grid-template-columns:1fr 1fr}.status-grid{grid-template-columns:repeat(2,1fr)}.content-area{padding:16px}}
        @media(max-width:480px){.sidebar{width:0;transform:translateX(-100%)}.sidebar.open{width:260px;transform:translateX(0)}.main-content{margin-left:0}.stats-grid{grid-template-columns:1fr}.quick-actions{grid-template-columns:1fr}.status-grid{grid-template-columns:1fr}}
    </style>
</head>
<body>
    <div class="app-container">
        <!-- SIDEBAR -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">🧺</div>
                <div class="sidebar-brand"><h2>LaundryKu</h2><span>Level 5</span></div>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-section">Menu Utama</div>
                <a href="/laundry_lvl1/modules/dashboard/" class="nav-item active"><span class="nav-icon">📊</span><span>Dashboard</span></a>
                <a href="/laundry_lvl1/modules/transactions/" class="nav-item"><span class="nav-icon">🧾</span><span>Transaksi</span></a>
                <a href="/laundry_lvl1/modules/customers/" class="nav-item"><span class="nav-icon">👥</span><span>Pelanggan</span></a>
                <div class="nav-section">Fitur Pro</div>
                <a href="/laundry_lvl1/modules/whatsapp/" class="nav-item"><span class="nav-icon">📱</span><span>WhatsApp</span></a>
                <a href="/laundry_lvl1/modules/loyalty/" class="nav-item"><span class="nav-icon">⭐</span><span>Loyalti</span></a>
                <div class="nav-section">Sistem</div>
                <a href="/laundry_lvl1/modules/reports/" class="nav-item"><span class="nav-icon">📋</span><span>Laporan</span></a>
                <a href="/laundry_lvl1/modules/settings/" class="nav-item"><span class="nav-icon">⚙️</span><span>Pengaturan</span></a>
            </nav>
            <div class="sidebar-footer">
                <a href="/laundry_lvl1/modules/auth/logout.php"><span>🚪</span><span>Keluar</span></a>
            </div>
        </aside>
        
        <!-- MAIN CONTENT -->
        <main class="main-content">
            <header class="top-bar">
                <h1>📊 Dashboard</h1>
                <div class="top-bar-right">
                    <button onclick="toggleTheme()" class="theme-btn" title="Ganti Tema">🌓</button>
                    <div class="user-info">
                        <div class="user-avatar"><?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?></div>
                        <div>
                            <strong><?= htmlspecialchars($currentUser['name'] ?? 'User') ?></strong><br>
                            <small style="color:var(--primary);"><?= ucfirst($currentUser['role'] ?? '') ?></small>
                        </div>
                    </div>
                </div>
            </header>
            
            <div class="content-area">
                <!-- STATS -->
                <div class="stats-grid">
                    <div class="stat-card c1"><div class="icon">💰</div><div class="label">Pendapatan Hari Ini</div><div class="value"><?= formatRupiah($todayStats['revenue']) ?></div></div>
                    <div class="stat-card c2"><div class="icon">📋</div><div class="label">Total Transaksi</div><div class="value"><?= $todayStats['total'] ?></div></div>
                    <div class="stat-card c3"><div class="icon">🔄</div><div class="label">Sedang Diproses</div><div class="value"><?= ($statusCounts['washing']??0)+($statusCounts['ironing']??0) ?></div></div>
                    <div class="stat-card c4"><div class="icon">👥</div><div class="label">Total Pelanggan</div><div class="value"><?= $custTotal ?></div></div>
                </div>
                
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
                    <!-- STATUS CUCIAN -->
                    <div class="card">
                        <div class="card-header"><div class="card-title">📊 Status Cucian</div></div>
                        <div class="card-body">
                            <div class="status-grid">
                                <?php
                                $statuses = [
                                    'queue'=>['label'=>'Menunggu','icon'=>'📋','color'=>'#F59E0B','bg'=>'#FEF3C7'],
                                    'washing'=>['label'=>'Dicuci','icon'=>'🫧','color'=>'#3B82F6','bg'=>'#DBEAFE'],
                                    'ironing'=>['label'=>'Disetrika','icon'=>'👕','color'=>'#8B5CF6','bg'=>'#EDE9FE'],
                                    'ready'=>['label'=>'Siap','icon'=>'✨','color'=>'#10B981','bg'=>'#D1FAE5'],
                                    'completed'=>['label'=>'Selesai','icon'=>'✅','color'=>'#6B7280','bg'=>'#F3F4F6']
                                ];
                                foreach($statuses as $k=>$s): ?>
                                <div class="status-item" style="background:<?=$s['bg']?>;border-color:<?=$s['color']?>30">
                                    <div class="icon"><?=$s['icon']?></div>
                                    <div class="count" style="color:<?=$s['color']?>"><?=$statusCounts[$k]??0?></div>
                                    <div class="label" style="color:<?=$s['color']?>"><?=$s['label']?></div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- TRANSAKSI TERBARU -->
                    <div class="card">
                        <div class="card-header"><div class="card-title">🕐 Transaksi Terbaru</div></div>
                        <div class="card-body">
                            <?php if(empty($recentTrx)): ?>
                                <p style="text-align:center;color:var(--gray-500);padding:20px;">📭 Belum ada transaksi</p>
                            <?php else: ?>
                            <ul class="trx-list">
                                <?php foreach($recentTrx as $trx): ?>
                                <li class="trx-item" onclick="window.location.href='/laundry_lvl1/modules/transactions/detail.php?id=<?=$trx['id']?>'">
                                    <div>
                                        <div class="trx-invoice"><?= htmlspecialchars($trx['invoice_number']) ?></div>
                                        <div class="trx-customer"><?= htmlspecialchars($trx['customer_name']) ?> • <?= htmlspecialchars($trx['service_name']) ?> • <?= $trx['weight_kg'] ?> kg</div>
                                    </div>
                                    <div style="text-align:right;">
                                        <div class="trx-amount"><?= formatRupiah($trx['total_price']) ?></div>
                                        <span class="badge badge-<?= $trx['payment_status']==='paid'?'success':'warning' ?>"><?= $trx['payment_status']==='paid'?'✅ Lunas':'❌ Belum' ?></span>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- QUICK ACTIONS -->
                <div class="card">
                    <div class="card-header"><div class="card-title">🚀 Quick Actions</div></div>
                    <div class="card-body">
                        <div class="quick-actions">
                            <a href="/laundry_lvl1/modules/transactions/create.php" class="quick-action-btn"><span class="icon">🧾</span><span>Transaksi Baru</span></a>
                            <a href="/laundry_lvl1/modules/customers/create.php" class="quick-action-btn"><span class="icon">👤</span><span>Pelanggan Baru</span></a>
                            <a href="/laundry_lvl1/modules/transactions/" class="quick-action-btn"><span class="icon">📋</span><span>Lihat Transaksi</span></a>
                            <a href="/laundry_lvl1/modules/whatsapp/" class="quick-action-btn"><span class="icon">📱</span><span>WhatsApp</span></a>
                            <a href="/laundry_lvl1/modules/loyalty/" class="quick-action-btn"><span class="icon">⭐</span><span>Loyalti</span></a>
                            <a href="/laundry_lvl1/modules/reports/" class="quick-action-btn"><span class="icon">📈</span><span>Laporan</span></a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
    function toggleTheme(){var h=document.documentElement;var c=h.getAttribute('data-theme');var n=c==='dark'?'light':'dark';h.setAttribute('data-theme',n);localStorage.setItem('theme',n)}
    (function(){var s=localStorage.getItem('theme')||'light';document.documentElement.setAttribute('data-theme',s)})();
    function toggleSidebar(){document.getElementById('sidebar').classList.toggle('open')}
    function formatRupiah(a){return'Rp '+a.toLocaleString('id-ID')}
    console.log('✅ Dashboard Ready!');
    </script>
</body>
</html>