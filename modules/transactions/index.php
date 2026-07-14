<?php
// ============================================
// modules/transactions/index.php
// Daftar Transaksi
// ============================================
require_once __DIR__ . '/../../includes/header.php';
requireLogin();

$franchiseId = $_SESSION['franchise_id'] ?? 1;
$outletId = $_SESSION['outlet_id'] ?? 1;

// Filter
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$where = "t.franchise_id = :fid";
$params = [':fid' => $franchiseId];

if (!empty($search)) {
    $where .= " AND (t.invoice_number LIKE :search OR c.name LIKE :search2)";
    $params[':search'] = "%$search%";
    $params[':search2'] = "%$search%";
}

if (!empty($statusFilter)) {
    $where .= " AND t.laundry_status = :status";
    $params[':status'] = $statusFilter;
}

$stmt = $db->prepare("SELECT t.*, c.name as customer_name, s.name as service_name 
    FROM transactions t 
    JOIN customers c ON t.customer_id = c.id 
    JOIN services s ON t.service_id = s.id 
    WHERE $where 
    ORDER BY t.created_at DESC LIMIT 50");
$stmt->execute($params);
$transactions = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">🧾 Daftar Transaksi</div>
        <a href="/modules/transactions/create.php" class="btn btn-primary">+ Transaksi Baru</a>
    </div>
    <div class="card-body">
        <!-- Search & Filter -->
        <div style="display:flex;gap:12px;margin-bottom:20px;">
            <input type="text" id="searchInput" class="form-input" placeholder="🔍 Cari invoice atau pelanggan..." value="<?= htmlspecialchars($search) ?>" style="flex:1;">
            <select id="statusFilter" class="form-select" style="width:180px;" onchange="filterStatus(this.value)">
                <option value="">Semua Status</option>
                <option value="queue" <?= $statusFilter === 'queue' ? 'selected' : '' ?>>📋 Menunggu</option>
                <option value="washing" <?= $statusFilter === 'washing' ? 'selected' : '' ?>>🫧 Dicuci</option>
                <option value="ironing" <?= $statusFilter === 'ironing' ? 'selected' : '' ?>>👕 Disetrika</option>
                <option value="ready" <?= $statusFilter === 'ready' ? 'selected' : '' ?>>✨ Siap</option>
                <option value="completed" <?= $statusFilter === 'completed' ? 'selected' : '' ?>>✅ Selesai</option>
            </select>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Pelanggan</th>
                        <th>Layanan</th>
                        <th>Berat</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Bayar</th>
                        <th>Poin</th>
                        <th>WA</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="11" style="text-align:center;padding:40px;color:#64748B;">
                            📭 Belum ada transaksi. <a href="/modules/transactions/create.php">Buat transaksi pertama!</a>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($transactions as $trx): 
                        $statusLabels = [
                            'queue' => ['label' => 'Menunggu', 'bg' => '#FEF3C7', 'color' => '#F59E0B', 'icon' => '📋'],
                            'washing' => ['label' => 'Dicuci', 'bg' => '#DBEAFE', 'color' => '#3B82F6', 'icon' => '🫧'],
                            'ironing' => ['label' => 'Disetrika', 'bg' => '#EDE9FE', 'color' => '#8B5CF6', 'icon' => '👕'],
                            'ready' => ['label' => 'Siap', 'bg' => '#D1FAE5', 'color' => '#10B981', 'icon' => '✨'],
                            'completed' => ['label' => 'Selesai', 'bg' => '#F3F4F6', 'color' => '#6B7280', 'icon' => '✅'],
                        ];
                        $status = $statusLabels[$trx['laundry_status']] ?? ['label' => 'Unknown', 'bg' => '#F1F5F9', 'color' => '#64748B', 'icon' => '❓'];
                    ?>
                    <tr>
                        <td><strong style="color:var(--primary);"><?= htmlspecialchars($trx['invoice_number']) ?></strong></td>
                        <td><?= htmlspecialchars($trx['customer_name']) ?></td>
                        <td><?= htmlspecialchars($trx['service_name']) ?></td>
                        <td><?= $trx['weight_kg'] ?> kg</td>
                        <td><strong><?= formatRupiah($trx['total_price']) ?></strong></td>
                        <td>
                            <span class="badge" style="background:<?= $status['bg'] ?>;color:<?= $status['color'] ?>;">
                                <?= $status['icon'] ?> <?= $status['label'] ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($trx['payment_status'] === 'paid'): ?>
                                <span class="badge badge-success">✅ Lunas</span>
                            <?php else: ?>
                                <span class="badge badge-warning">❌ Belum</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $trx['loyalty_points_earned'] > 0 ? '⭐' . $trx['loyalty_points_earned'] : '-' ?></td>
                        <td><?= $trx['whatsapp_notified'] ? '✅' : '❌' ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($trx['created_at'])) ?></td>
                        <td style="display:flex;gap:4px;">
                            <a href="/modules/transactions/detail.php?id=<?= $trx['id'] ?>" class="btn btn-sm btn-secondary">Detail</a>
                            <a href="/modules/transactions/edit.php?id=<?= $trx['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                            <?php if (isOwner()): ?>
                            <a href="/modules/transactions/delete.php?id=<?= $trx['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">Hapus</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        var search = encodeURIComponent(this.value);
        var status = document.getElementById('statusFilter').value;
        window.location.href = '?search=' + search + '&status=' + status;
    }
});

function filterStatus(status) {
    var search = document.getElementById('searchInput').value;
    window.location.href = '?search=' + encodeURIComponent(search) + '&status=' + status;
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>