<?php
// ============================================
// modules/dashboard/index.php - Level 1
// Halaman Dashboard
// ============================================
require_once __DIR__ . '/../../includes/header.php';
requireLogin();

$outletId = $_SESSION['outlet_id'];
$today = date('Y-m-d');

// Statistik hari ini
$stmt = $db->prepare("SELECT 
    COUNT(*) as total_transactions,
    COALESCE(SUM(total_price), 0) as total_revenue,
    COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN total_price ELSE 0 END), 0) as paid_revenue
    FROM transactions 
    WHERE outlet_id = :outlet_id AND DATE(created_at) = :today");
$stmt->execute([':outlet_id' => $outletId, ':today' => $today]);
$todayStats = $stmt->fetch();

// Jumlah per status
$stmt = $db->prepare("SELECT laundry_status, COUNT(*) as count 
    FROM transactions 
    WHERE outlet_id = :outlet_id AND DATE(created_at) = :today 
    GROUP BY laundry_status");
$stmt->execute([':outlet_id' => $outletId, ':today' => $today]);
$statusCounts = [];
foreach ($stmt->fetchAll() as $row) {
    $statusCounts[$row['laundry_status']] = $row['count'];
}
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">💰</div>
        <div class="stat-info">
            <h4>Pendapatan Hari Ini</h4>
            <div class="stat-value"><?= formatRupiah($todayStats['total_revenue']) ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">✅</div>
        <div class="stat-info">
            <h4>Sudah Dibayar</h4>
            <div class="stat-value"><?= formatRupiah($todayStats['paid_revenue']) ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon yellow">📋</div>
        <div class="stat-info">
            <h4>Total Transaksi</h4>
            <div class="stat-value"><?= $todayStats['total_transactions'] ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple">🔄</div>
        <div class="stat-info">
            <h4>Sedang Diproses</h4>
            <div class="stat-value"><?= ($statusCounts['washing'] ?? 0) + ($statusCounts['ironing'] ?? 0) ?></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>📊 Status Cucian Hari Ini</h3>
    </div>
    <div class="card-body">
        <div class="kanban-board">
            <div class="kanban-column" style="background: #FEF3C7;">
                <div class="kanban-column-header">
                    <h4>🟡 Menunggu</h4>
                    <span class="kanban-count"><?= $statusCounts['queue'] ?? 0 ?></span>
                </div>
            </div>
            <div class="kanban-column" style="background: #DBEAFE;">
                <div class="kanban-column-header">
                    <h4>🔵 Diproses</h4>
                    <span class="kanban-count"><?= ($statusCounts['washing'] ?? 0) + ($statusCounts['ironing'] ?? 0) ?></span>
                </div>
            </div>
            <div class="kanban-column" style="background: #D1FAE5;">
                <div class="kanban-column-header">
                    <h4>🟢 Siap Diambil</h4>
                    <span class="kanban-count"><?= $statusCounts['ready'] ?? 0 ?></span>
                </div>
            </div>
            <div class="kanban-column">
                <div class="kanban-column-header">
                    <h4>✅ Selesai</h4>
                    <span class="kanban-count"><?= $statusCounts['completed'] ?? 0 ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>🚀 Aksi Cepat</h3>
    </div>
    <div class="card-body">
        <div class="quick-actions">
            <a href="/modules/transactions/" class="quick-action-btn">
                <span class="icon">🧾</span>
                <span>Transaksi Baru</span>
            </a>
            <a href="/modules/transactions/" class="quick-action-btn">
                <span class="icon">📋</span>
                <span>Lihat Transaksi</span>
            </a>
            <a href="/modules/customers/" class="quick-action-btn">
                <span class="icon">👥</span>
                <span>Pelanggan</span>
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>