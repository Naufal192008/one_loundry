<?php
require_once __DIR__ . '/../../includes/header.php';
requireLogin();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if(!$id) redirect('/laundry_lvl1/modules/transactions/','ID tidak valid!','error');
$franchiseId = $_SESSION['franchise_id'] ?? 1;
$stmt = $db->prepare("SELECT * FROM transactions WHERE id = ? AND franchise_id = ?"); $stmt->execute([$id,$franchiseId]); $trx = $stmt->fetch();
if(!$trx) redirect('/laundry_lvl1/modules/transactions/','Tidak ditemukan!','error');
$stmt = $db->prepare("SELECT * FROM services WHERE franchise_id = ? AND is_active = 1"); $stmt->execute([$franchiseId]); $services = $stmt->fetchAll();
$stmt = $db->prepare("SELECT * FROM customers WHERE franchise_id = ? ORDER BY name"); $stmt->execute([$franchiseId]); $customers = $stmt->fetchAll();

if($_SERVER['REQUEST_METHOD']==='POST'){
    $customerId = filter_input(INPUT_POST,'customer_id',FILTER_VALIDATE_INT); $serviceId = filter_input(INPUT_POST,'service_id',FILTER_VALIDATE_INT);
    $weightKg = filter_input(INPUT_POST,'weight_kg',FILTER_VALIDATE_FLOAT); $notes = sanitize($_POST['notes']??'');
    if($customerId && $serviceId && $weightKg>0){
        $stmt = $db->prepare("SELECT price_per_kg FROM services WHERE id = ?"); $stmt->execute([$serviceId]); $s = $stmt->fetch();
        $total = $s['price_per_kg'] * $weightKg;
        $db->prepare("UPDATE transactions SET customer_id=?, service_id=?, weight_kg=?, total_price=?, notes=? WHERE id=?")->execute([$customerId,$serviceId,$weightKg,$total,$notes,$id]);
        $db->prepare("INSERT INTO transaction_logs (transaction_id, user_id, action, notes) VALUES (?,'".$_SESSION['user_id']."','edit','Data diubah')")->execute([$id]);
        redirect("/laundry_lvl1/modules/transactions/detail.php?id=$id",'✅ Transaksi diupdate!');
    }
}
?>
<div class="card" style="max-width:650px;margin:0 auto;">
    <div class="card-header"><div class="card-title">✏️ Edit Transaksi</div><a href="/laundry_lvl1/modules/transactions/detail.php?id=<?=$id?>" class="btn btn-secondary btn-sm">← Kembali</a></div>
    <div class="card-body">
        <form method="POST">
            <div class="form-row">
                <div class="form-group"><label class="form-label">👤 Pelanggan</label><select name="customer_id" class="form-select" required><?php foreach($customers as $c): ?><option value="<?=$c['id']?>" <?=$c['id']==$trx['customer_id']?'selected':''?>><?=htmlspecialchars($c['name'])?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label class="form-label">🧺 Layanan</label><select name="service_id" class="form-select" required><?php foreach($services as $s): ?><option value="<?=$s['id']?>" <?=$s['id']==$trx['service_id']?'selected':''?>><?=htmlspecialchars($s['name'])?> - <?=formatRupiah($s['price_per_kg'])?>/kg</option><?php endforeach; ?></select></div>
            </div>
            <div class="form-group"><label class="form-label">⚖️ Berat (kg)</label><input type="number" name="weight_kg" class="form-input" value="<?=$trx['weight_kg']?>" step="0.1" min="0.1" required></div>
            <div class="form-group"><label class="form-label">📝 Catatan</label><textarea name="notes" class="form-textarea"><?=htmlspecialchars($trx['notes']??'')?></textarea></div>
            <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">💾 Simpan Perubahan</button>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>