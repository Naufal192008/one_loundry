<?php
// ============================================
// modules/transactions/index.php - Level 1
// Daftar Transaksi
// ============================================
require_once __DIR__ . '/../../includes/header.php';
requireLogin();

$outletId = $_SESSION['outlet_id'];

$stmt = $db->prepare("SELECT t.*, c.name as customer_name, s.name as service_name 
    FROM transactions t 
    JOIN customers c ON t.customer_id = c.id 
    JOIN services s ON t.service_id = s.id 
    WHERE t.outlet_id = :outlet_id 
    ORDER BY t.created_at DESC LIMIT 50");
$stmt->execute([':outlet_id' => $outletId]);
$transactions = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <h3>🧾 Daftar Transaksi</h3>
        <a href="/laundry_lvl1/modules/transactions/create.php" class="btn btn-primary">+ Transaksi Baru</a>
    </div>
    <div class="card-body">
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
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: #64748B;">
                            📭 Belum ada transaksi. <a href="/laundry_lvl1/modules/transactions/create.php">Buat transaksi pertama!</a>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($transactions as $trx): 
                        $statusLabels = [
                            'queue' => ['label' => 'Menunggu', 'bg' => '#FEF3C7', 'color' => '#F59E0B'],
                            'washing' => ['label' => 'Dicuci', 'bg' => '#DBEAFE', 'color' => '#3B82F6'],
                            'ironing' => ['label' => 'Disetrika', 'bg' => '#EDE9FE', 'color' => '#8B5CF6'],
                            'ready' => ['label' => 'Siap', 'bg' => '#D1FAE5', 'color' => '#10B981'],
                            'completed' => ['label' => 'Selesai', 'bg' => '#F3F4F6', 'color' => '#6B7280']
                        ];
                        $status = $statusLabels[$trx['laundry_status']] ?? ['label' => 'Unknown', 'bg' => '#F1F5F9', 'color' => '#64748B'];
                    ?>
                    <tr>
                        <td><strong style="color: #1D4ED8;"><?= htmlspecialchars($trx['invoice_number']) ?></strong></td>
                        <td><?= htmlspecialchars($trx['customer_name']) ?></td>
                        <td><?= htmlspecialchars($trx['service_name']) ?></td>
                        <td><?= $trx['weight_kg'] ?> kg</td>
                        <td><strong><?= formatRupiah($trx['total_price']) ?></strong></td>
                        <td>
                            <span class="status-badge" style="background: <?= $status['bg'] ?>; color: <?= $status['color'] ?>;">
                                <?= $status['label'] ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($trx['created_at'])) ?></td>
                        <td style="display: flex; gap: 4px;">
                            <a href="/laundry_lvl1/modules/transactions/detail.php?id=<?= $trx['id'] ?>" class="btn btn-sm btn-secondary">Detail</a>
                            <a href="/laundry_lvl1/modules/transactions/edit.php?id=<?= $trx['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                            <?php if (isOwner()): ?>
                            <a href="/laundry_lvl1/modules/transactions/delete.php?id=<?= $trx['id'] ?>" 
                               class="btn btn-sm" 
                               style="background:#EF4444;color:white;"
                               onclick="return confirm('Yakin hapus transaksi ini?')">Hapus</a>
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

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>