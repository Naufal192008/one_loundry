<?php
require_once __DIR__ . '/../../includes/header.php';
requireLogin();

// Hanya owner yang bisa akses laporan
if (!isOwner()) {
    redirect('../dashboard/', 'Anda tidak memiliki izin!', 'error');
}

$outletId = $_SESSION['outlet_id'];

// Laporan hari ini
$today = date('Y-m-d');
$stmt = $db->prepare("SELECT 
    COUNT(*) as total_trx,
    COALESCE(SUM(total_price), 0) as total_revenue,
    COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN total_price ELSE 0 END), 0) as paid_amount,
    COALESCE(SUM(CASE WHEN payment_status = 'unpaid' THEN total_price ELSE 0 END), 0) as unpaid_amount
    FROM transactions 
    WHERE outlet_id = :outlet_id AND DATE(created_at) = :today");
$stmt->execute([':outlet_id' => $outletId, ':today' => $today]);
$todayReport = $stmt->fetch();

// Laporan 7 hari terakhir
$stmt = $db->prepare("SELECT 
    DATE(created_at) as date,
    COUNT(*) as total_trx,
    COALESCE(SUM(total_price), 0) as revenue
    FROM transactions 
    WHERE outlet_id = :outlet_id AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date DESC");
$stmt->execute([':outlet_id' => $outletId]);
$weeklyReport = $stmt->fetchAll();
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">💰</div>
        <div class="stat-info">
            <h4>Total Pendapatan Hari Ini</h4>
            <div class="stat-value"><?= formatRupiah($todayReport['total_revenue']) ?></div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon green">✅</div>
        <div class="stat-info">
            <h4>Sudah Dibayar</h4>
            <div class="stat-value"><?= formatRupiah($todayReport['paid_amount']) ?></div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon yellow">⚠️</div>
        <div class="stat-info">
            <h4>Belum Dibayar</h4>
            <div class="stat-value"><?= formatRupiah($todayReport['unpaid_amount']) ?></div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon purple">📋</div>
        <div class="stat-info">
            <h4>Total Transaksi</h4>
            <div class="stat-value"><?= $todayReport['total_trx'] ?></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>📈 Pendapatan 7 Hari Terakhir</h3>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Total Transaksi</th>
                        <th>Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($weeklyReport as $row): ?>
                    <tr>
                        <td><?= date('d M Y', strtotime($row['date'])) ?></td>
                        <td><?= $row['total_trx'] ?> transaksi</td>
                        <td><strong><?= formatRupiah($row['revenue']) ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($weeklyReport)): ?>
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 40px; color: var(--gray-500);">
                            📭 Belum ada data.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>