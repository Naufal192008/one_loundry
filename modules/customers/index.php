<?php
require_once __DIR__ . '/../../includes/header.php';
requireLogin();
$franchiseId = $_SESSION['franchise_id'] ?? 1;
$search = $_GET['search'] ?? '';
$where = "franchise_id = ?"; $params = [$franchiseId];
if(!empty($search)){ $where .= " AND (name LIKE ? OR phone LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
$stmt = $db->prepare("SELECT * FROM customers WHERE $where ORDER BY name"); $stmt->execute($params); $customers = $stmt->fetchAll();
?>
<div class="card">
    <div class="card-header"><div class="card-title">👥 Daftar Pelanggan</div><a href="/laundry_lvl1/modules/customers/create.php" class="btn btn-primary">+ Pelanggan Baru</a></div>
    <div class="card-body">
        <div class="search-bar" style="margin-bottom:20px;"><input type="text" id="searchInput" class="form-input" placeholder="🔍 Cari..." value="<?=htmlspecialchars($search)?>"></div>
        <div class="table-container">
            <table><thead><tr><th>Nama</th><th>Telepon</th><th>Email</th><th>Poin</th><th>Tier</th><th>Aksi</th></tr></thead>
                <tbody><?php if(empty($customers)): ?><tr><td colspan="6" style="text-align:center;padding:40px;">📭 Belum ada pelanggan</td></tr>
                <?php else: foreach($customers as $c): $tiers=['bronze'=>'🥉','silver'=>'🥈','gold'=>'🥇','platinum'=>'💎']; ?>
                <tr><td><strong><?=htmlspecialchars($c['name'])?></strong></td><td><?=htmlspecialchars($c['phone']?:'-')?></td><td><?=htmlspecialchars($c['email']?:'-')?></td><td>⭐ <?=number_format($c['loyalty_points'])?></td><td><?=$tiers[$c['membership_tier']]??'🥉'?> <?=ucfirst($c['membership_tier'])?></td><td><a href="/laundry_lvl1/modules/customers/edit.php?id=<?=$c['id']?>" class="btn btn-sm btn-primary">Edit</a></td></tr>
                <?php endforeach; endif; ?></tbody>
            </table>
        </div>
    </div>
</div>
<script>document.getElementById('searchInput').addEventListener('keypress',function(e){if(e.key==='Enter')window.location.href='?search='+encodeURIComponent(this.value)});</script>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>