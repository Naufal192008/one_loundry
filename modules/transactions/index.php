<?php
require_once __DIR__ . '/../../includes/header.php';
requireLogin();

$franchiseId = $_SESSION['franchise_id'] ?? 1;
$outletId = $_SESSION['outlet_id'] ?? 1;

$stmt = $db->prepare("SELECT t.*, c.name as customer_name, s.name as service_name FROM transactions t JOIN customers c ON t.customer_id = c.id JOIN services s ON t.service_id = s.id WHERE t.franchise_id = ? ORDER BY t.created_at DESC LIMIT 50");
$stmt->execute([$franchiseId]); $transactions = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header"><div class="card-title">🧾 Daftar Transaksi</div><a href="/laundry_lvl1/modules/transactions/create.php" class="btn btn-primary">+ Transaksi Baru</a></div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead><tr><th>Invoice</th><th>Pelanggan</th><th>Layanan</th><th>Berat</th><th>Total</th><th>Status</th><th>Bayar</th><th>Tanggal</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php if(empty($transactions)): ?><tr><td colspan="9" style="text-align:center;padding:40px;">📭 Belum ada transaksi</td></tr>
                    <?php else: foreach($transactions as $trx): $s=getStatusInfo($trx['laundry_status']); ?>
                    <tr>
                        <td><strong style="color:var(--primary);"><?= htmlspecialchars($trx['invoice_number']) ?></strong></td>
                        <td><?= htmlspecialchars($trx['customer_name']) ?></td>
                        <td><?= htmlspecialchars($trx['service_name']) ?></td>
                        <td><?= $trx['weight_kg'] ?> kg</td>
                        <td><strong><?= formatRupiah($trx['total_price']) ?></strong></td>
                        <td><span class="badge" style="background:<?=$s['bg']?>;color:<?=$s['color']?>;"><?=$s['icon']?> <?=$s['label']?></span></td>
                        <td><?= $trx['payment_status']==='paid'?'<span class="badge badge-success">✅ Lunas</span>':'<span class="badge badge-warning">❌ Belum</span>' ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($trx['created_at'])) ?></td>
                        <td style="display:flex;gap:4px;">
                            <a href="/laundry_lvl1/modules/transactions/detail.php?id=<?=$trx['id']?>" class="btn btn-sm btn-secondary">Detail</a>
                            <a href="/laundry_lvl1/modules/transactions/edit.php?id=<?=$trx['id']?>" class="btn btn-sm btn-primary">Edit</a>
                            <?php if(isOwner()||isSuperAdmin()): ?><a href="/laundry_lvl1/modules/transactions/delete.php?id=<?=$trx['id']?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">Hapus</a><?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>