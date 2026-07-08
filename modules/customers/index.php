<?php
// ============================================
// modules/customers/index.php - Level 1
// Daftar Pelanggan
// ============================================
require_once __DIR__ . '/../../includes/header.php';
requireLogin();

$outletId = $_SESSION['outlet_id'];

$search = $_GET['search'] ?? '';
$where = "outlet_id = :outlet_id";
$params = [':outlet_id' => $outletId];

if (!empty($search)) {
    $where .= " AND (name LIKE :search OR phone LIKE :search2)";
    $params[':search'] = "%$search%";
    $params[':search2'] = "%$search%";
}

$stmt = $db->prepare("SELECT * FROM customers WHERE $where ORDER BY name");
$stmt->execute($params);
$customers = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <h3>👥 Daftar Pelanggan</h3>
        <a href="/modules/customers/create.php" class="btn btn-primary">+ Pelanggan Baru</a>
    </div>
    <div class="card-body">
        <div class="search-bar">
            <input type="text" id="searchInput" class="search-input" placeholder="🔍 Cari nama atau telepon..." value="<?= htmlspecialchars($search) ?>">
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Telepon</th>
                        <th>Poin Loyalti</th>
                        <th>Terdaftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($customers)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: #64748B;">
                            📭 Belum ada pelanggan.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($customers as $cust): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($cust['name']) ?></strong></td>
                        <td><?= htmlspecialchars($cust['phone'] ?: '-') ?></td>
                        <td>
                            <?php if ($cust['loyalty_points'] > 0): ?>
                                <span class="status-badge" style="background: #FEF3C7; color: #92400E;">
                                    ⭐ <?= $cust['loyalty_points'] ?> poin
                                </span>
                            <?php else: ?>
                                <span style="color: #94A3B8;">0 poin</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($cust['created_at'])) ?></td>
                        <td>
                            <a href="/modules/customers/edit.php?id=<?= $cust['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
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
        window.location.href = '?search=' + encodeURIComponent(this.value);
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>