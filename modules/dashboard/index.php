<?php
require_once __DIR__ . '/../../includes/header.php';
requireLogin();

$franchiseId = $_SESSION['franchise_id'] ?? 1;
$today = date('Y-m-d');

$stmt = $db->prepare("SELECT COUNT(*) as total, COALESCE(SUM(total_price),0) as revenue FROM transactions WHERE franchise_id = ? AND DATE(created_at) = ?");
$stmt->execute([$franchiseId, $today]);
$todayStats = $stmt->fetch();

$stmt = $db->prepare("SELECT laundry_status, COUNT(*) as count FROM transactions WHERE franchise_id = ? AND DATE(created_at) = ? GROUP BY laundry_status");
$stmt->execute([$franchiseId, $today]);
$statusCounts = [];
foreach ($stmt->fetchAll() as $row) $statusCounts[$row['laundry_status']] = $row['count'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM customers WHERE franchise_id = ?");
$stmt->execute([$franchiseId]);
$custTotal = $stmt->fetch()['total'];
?>

<div class="stats-grid">
    <div class="stat-card primary">
        <div style="display:flex;align-items:center;gap:16px;">
            <div style="font-size:36px;">💰</div>
            <div>
                <div style="font-size:13px;color:var(--gray-500);">Pendapatan Hari Ini</div>
                <div style="font-size:28px;font-weight:800;"><?= formatRupiah($todayStats['revenue']) ?></div>
            </div>
        </div>
    </div>
    <div class="stat-card success">
        <div style="display:flex;align-items:center;gap:16px;">
            <div style="font-size:36px;">📋</div>
            <div>
                <div style="font-size:13px;color:var(--gray-500);">Total Transaksi</div>
                <div style="font-size:28px;font-weight:800;"><?= $todayStats['total'] ?></div>
            </div>
        </div>
    </div>
    <div class="stat-card warning">
        <div style="display:flex;align-items:center;gap:16px;">
            <div style="font-size:36px;">🔄</div>
            <div>
                <div style="font-size:13px;color:var(--gray-500);">Sedang Diproses</div>
                <div style="font-size:28px;font-weight:800;"><?= ($statusCounts['washing']??0)+($statusCounts['ironing']??0) ?></div>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div style="display:flex;align-items:center;gap:16px;">
            <div style="font-size:36px;">👥</div>
            <div>
                <div style="font-size:13px;color:var(--gray-500);">Total Pelanggan</div>
                <div style="font-size:28px;font-weight:800;"><?= $custTotal ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><div class="card-title">📊 Status Cucian Hari Ini</div></div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;text-align:center;">
            <?php
            $statuses = ['queue'=>['label'=>'Menunggu','icon'=>'📋','color'=>'#F59E0B'],'washing'=>['label'=>'Dicuci','icon'=>'🫧','color'=>'#3B82F6'],'ironing'=>['label'=>'Disetrika','icon'=>'👕','color'=>'#8B5CF6'],'ready'=>['label'=>'Siap','icon'=>'✨','color'=>'#10B981'],'completed'=>['label'=>'Selesai','icon'=>'✅','color'=>'#6B7280']];
            foreach($statuses as $k=>$s): ?>
            <div style="background:<?=$s['color']?>15;padding:20px;border-radius:12px;border:2px solid <?=$s['color']?>30;">
                <div style="font-size:32px;"><?=$s['icon']?></div>
                <div style="font-size:24px;font-weight:800;color:<?=$s['color']?>;"><?=$statusCounts[$k]??0?></div>
                <div style="font-size:12px;color:var(--gray-500);"><?=$s['label']?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

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

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>