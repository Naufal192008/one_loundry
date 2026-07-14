<?php
// ============================================
// modules/dashboard/index.php - LaundryKu Level 5
// Dashboard Utama dengan Statistik Lengkap
// ============================================

require_once __DIR__ . '/../../includes/header.php';
requireLogin();

$franchiseId = $_SESSION['franchise_id'] ?? 1;
$outletId = $_SESSION['outlet_id'] ?? 1;
$today = date('Y-m-d');
$currentMonth = date('Y-m');

// ============================================
// STATISTIK HARI INI
// ============================================
$stmt = $db->prepare("SELECT 
    COUNT(*) as total_trx,
    COALESCE(SUM(total_price), 0) as total_revenue,
    COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN total_price ELSE 0 END), 0) as paid_revenue,
    COALESCE(SUM(CASE WHEN payment_status = 'unpaid' THEN total_price ELSE 0 END), 0) as unpaid_revenue,
    COUNT(DISTINCT customer_id) as unique_customers,
    COALESCE(AVG(total_price), 0) as avg_transaction
    FROM transactions 
    WHERE franchise_id = ? AND DATE(created_at) = ?");
$stmt->execute([$franchiseId, $today]);
$todayStats = $stmt->fetch();

// ============================================
// STATISTIK BULAN INI
// ============================================
$stmt = $db->prepare("SELECT 
    COUNT(*) as total_trx,
    COALESCE(SUM(total_price), 0) as total_revenue
    FROM transactions 
    WHERE franchise_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ?");
$stmt->execute([$franchiseId, $currentMonth]);
$monthStats = $stmt->fetch();

// ============================================
// STATUS CUCIAN HARI INI
// ============================================
$stmt = $db->prepare("SELECT laundry_status, COUNT(*) as count 
    FROM transactions 
    WHERE franchise_id = ? AND DATE(created_at) = ? 
    GROUP BY laundry_status");
$stmt->execute([$franchiseId, $today]);
$statusCounts = [];
foreach ($stmt->fetchAll() as $row) {
    $statusCounts[$row['laundry_status']] = $row['count'];
}

// ============================================
// TOTAL PELANGGAN & POIN
// ============================================
$stmt = $db->prepare("SELECT 
    COUNT(*) as total_customers,
    COALESCE(SUM(loyalty_points), 0) as total_points,
    COUNT(CASE WHEN loyalty_points > 0 THEN 1 END) as active_members
    FROM customers WHERE franchise_id = ?");
$stmt->execute([$franchiseId]);
$customerStats = $stmt->fetch();

// ============================================
// WHATSAPP HARI INI
// ============================================
$stmt = $db->prepare("SELECT 
    COUNT(*) as total_wa,
    SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as wa_sent
    FROM whatsapp_logs 
    WHERE DATE(created_at) = ?");
$stmt->execute([$today]);
$waStats = $stmt->fetch();

// ============================================
// TRANSAKSI TERBARU
// ============================================
$stmt = $db->prepare("SELECT t.*, c.name as customer_name, s.name as service_name 
    FROM transactions t 
    JOIN customers c ON t.customer_id = c.id 
    JOIN services s ON t.service_id = s.id 
    WHERE t.franchise_id = ? 
    ORDER BY t.created_at DESC LIMIT 5");
$stmt->execute([$franchiseId]);
$recentTransactions = $stmt->fetchAll();

// ============================================
// PENDAPATAN 7 HARI TERAKHIR (UNTUK CHART)
// ============================================
$stmt = $db->prepare("SELECT 
    DATE(created_at) as date,
    COALESCE(SUM(total_price), 0) as revenue,
    COUNT(*) as total
    FROM transactions 
    WHERE franchise_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC");
$stmt->execute([$franchiseId]);
$chartData = $stmt->fetchAll();
$chartLabels = [];
$chartRevenue = [];
foreach ($chartData as $row) {
    $chartLabels[] = date('d M', strtotime($row['date']));
    $chartRevenue[] = (int)$row['revenue'];
}
?>

<!-- ============================================ -->
<!-- STATS GRID -->
<!-- ============================================ -->
<div class="stats-grid">
    <!-- Pendapatan Hari Ini -->
    <div class="stat-card primary">
        <div class="stat-card-inner">
            <div class="stat-icon-large blue">💰</div>
            <div>
                <div class="stat-label">Pendapatan Hari Ini</div>
                <div class="stat-value"><?= formatRupiah($todayStats['total_revenue']) ?></div>
                <div class="stat-sub"><?= $todayStats['total_trx'] ?> transaksi</div>
            </div>
        </div>
    </div>
    
    <!-- Sudah Dibayar -->
    <div class="stat-card success">
        <div class="stat-card-inner">
            <div class="stat-icon-large green">✅</div>
            <div>
                <div class="stat-label">Sudah Dibayar</div>
                <div class="stat-value"><?= formatRupiah($todayStats['paid_revenue']) ?></div>
                <div class="stat-sub"><?= $todayStats['unique_customers'] ?> pelanggan unik</div>
            </div>
        </div>
    </div>
    
    <!-- Total Transaksi -->
    <div class="stat-card warning">
        <div class="stat-card-inner">
            <div class="stat-icon-large yellow">📋</div>
            <div>
                <div class="stat-label">Total Transaksi</div>
                <div class="stat-value"><?= $todayStats['total_trx'] ?></div>
                <div class="stat-sub">Rata-rata: <?= formatRupiah($todayStats['avg_transaction']) ?></div>
            </div>
        </div>
    </div>
    
    <!-- Sedang Diproses -->
    <div class="stat-card danger">
        <div class="stat-card-inner">
            <div class="stat-icon-large red">🔄</div>
            <div>
                <div class="stat-label">Sedang Diproses</div>
                <div class="stat-value"><?= ($statusCounts['washing'] ?? 0) + ($statusCounts['ironing'] ?? 0) ?></div>
                <div class="stat-sub">Siap: <?= $statusCounts['ready'] ?? 0 ?> | Selesai: <?= $statusCounts['completed'] ?? 0 ?></div>
            </div>
        </div>
    </div>
    
    <!-- Total Pelanggan -->
    <div class="stat-card purple">
        <div class="stat-card-inner">
            <div class="stat-icon-large purple">👥</div>
            <div>
                <div class="stat-label">Total Pelanggan</div>
                <div class="stat-value"><?= number_format($customerStats['total_customers']) ?></div>
                <div class="stat-sub">⭐ <?= number_format($customerStats['total_points']) ?> poin beredar</div>
            </div>
        </div>
    </div>
    
    <!-- WhatsApp Terkirim -->
    <div class="stat-card info">
        <div class="stat-card-inner">
            <div class="stat-icon-large blue">📱</div>
            <div>
                <div class="stat-label">WhatsApp Hari Ini</div>
                <div class="stat-value"><?= $waStats['wa_sent'] ?? 0 ?></div>
                <div class="stat-sub">Dari <?= $waStats['total_wa'] ?? 0 ?> percobaan</div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- CHART + STATUS -->
<!-- ============================================ -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 24px;">
    
    <!-- Chart Pendapatan 7 Hari -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">📈 Pendapatan 7 Hari Terakhir</div>
            <span class="badge badge-info"><?= formatRupiah($monthStats['total_revenue']) ?> / bulan</span>
        </div>
        <div class="card-body">
            <canvas id="revenueChart" height="280"></canvas>
        </div>
    </div>
    
    <!-- Status Cucian -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">📊 Status Cucian</div>
        </div>
        <div class="card-body" style="padding: 16px;">
            <?php
            $statusItems = [
                'queue' => ['label' => 'Menunggu', 'icon' => '📋', 'color' => '#F59E0B', 'bg' => '#FEF3C7'],
                'washing' => ['label' => 'Sedang Dicuci', 'icon' => '🫧', 'color' => '#3B82F6', 'bg' => '#DBEAFE'],
                'ironing' => ['label' => 'Sedang Disetrika', 'icon' => '👕', 'color' => '#8B5CF6', 'bg' => '#EDE9FE'],
                'ready' => ['label' => 'Siap Diambil', 'icon' => '✨', 'color' => '#10B981', 'bg' => '#D1FAE5'],
                'completed' => ['label' => 'Selesai', 'icon' => '✅', 'color' => '#6B7280', 'bg' => '#F3F4F6'],
            ];
            foreach ($statusItems as $key => $item):
                $count = $statusCounts[$key] ?? 0;
            ?>
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; margin-bottom: 8px; background: <?= $item['bg'] ?>; border-radius: 10px; border-left: 4px solid <?= $item['color'] ?>;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;"><?= $item['icon'] ?></span>
                    <span style="font-weight: 600; color: #1E293B;"><?= $item['label'] ?></span>
                </div>
                <span style="font-size: 20px; font-weight: 800; color: <?= $item['color'] ?>;"><?= $count ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- TRANSAKSI TERBARU + QUICK ACTIONS -->
<!-- ============================================ -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    
    <!-- Transaksi Terbaru -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">🕐 Transaksi Terbaru</div>
            <a href="/modules/transactions/" class="btn btn-sm btn-secondary">Lihat Semua</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($recentTransactions)): ?>
                <div style="text-align: center; padding: 40px; color: var(--gray-500);">
                    📭 Belum ada transaksi hari ini
                </div>
            <?php else: ?>
                <?php foreach ($recentTransactions as $trx): 
                    $status = getStatusInfo($trx['laundry_status']);
                ?>
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid var(--gray-100); transition: background 0.2s; cursor: pointer;" 
                     onclick="window.location.href='/modules/transactions/detail.php?id=<?= $trx['id'] ?>'"
                     onmouseover="this.style.background='var(--gray-50)'" 
                     onmouseout="this.style.background=''">
                    <div>
                        <strong style="color: var(--primary);"><?= htmlspecialchars($trx['invoice_number']) ?></strong>
                        <div style="font-size: 13px; color: var(--gray-500); margin-top: 2px;">
                            <?= htmlspecialchars($trx['customer_name']) ?> • <?= htmlspecialchars($trx['service_name']) ?> • <?= $trx['weight_kg'] ?> kg
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <strong><?= formatRupiah($trx['total_price']) ?></strong>
                        <div style="font-size: 11px; color: <?= $status['color'] ?>; font-weight: 600;">
                            <?= $status['icon'] ?> <?= $status['label'] ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">🚀 Aksi Cepat</div>
        </div>
        <div class="card-body">
            <div class="quick-actions">
                <a href="/modules/transactions/create.php" class="quick-action-btn">
                    <span class="icon">🧾</span>
                    <span>Transaksi Baru</span>
                </a>
                <a href="/modules/customers/create.php" class="quick-action-btn">
                    <span class="icon">👤</span>
                    <span>Pelanggan Baru</span>
                </a>
                <a href="/modules/transactions/" class="quick-action-btn">
                    <span class="icon">📋</span>
                    <span>Lihat Transaksi</span>
                </a>
                <a href="/modules/whatsapp/" class="quick-action-btn">
                    <span class="icon">📱</span>
                    <span>Blast WhatsApp</span>
                </a>
                <a href="/modules/loyalty/" class="quick-action-btn">
                    <span class="icon">⭐</span>
                    <span>Program Loyalti</span>
                </a>
                <a href="/modules/reports/" class="quick-action-btn">
                    <span class="icon">📈</span>
                    <span>Lihat Laporan</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- CHART.JS SCRIPT -->
<!-- ============================================ -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('revenueChart');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chartLabels) ?>,
            datasets: [{
                label: 'Pendapatan',
                data: <?= json_encode($chartRevenue) ?>,
                borderColor: '#6366F1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#6366F1',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Rp ' + context.raw.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            if (value >= 1000000) return 'Rp ' + (value / 1000000) + 'M';
                            if (value >= 1000) return 'Rp ' + (value / 1000) + 'K';
                            return 'Rp ' + value;
                        }
                    }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>