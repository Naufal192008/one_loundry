<?php
require_once __DIR__ . '/../../includes/header.php';
requireLogin();
$id = filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
if(!$id) redirect('/laundry_lvl1/modules/customers/','ID tidak valid!','error');
$franchiseId = $_SESSION['franchise_id'] ?? 1;
$stmt = $db->prepare("SELECT * FROM customers WHERE id = ? AND franchise_id = ?"); $stmt->execute([$id,$franchiseId]); $cust = $stmt->fetch();
if(!$cust) redirect('/laundry_lvl1/modules/customers/','Tidak ditemukan!','error');
if($_SERVER['REQUEST_METHOD']==='POST'){
    $name = sanitize($_POST['name']??''); $phone = sanitize($_POST['phone']??''); $email = sanitize($_POST['email']??''); $address = sanitize($_POST['address']??'');
    if(empty($name)) $error = 'Nama wajib diisi!';
    else { $db->prepare("UPDATE customers SET name=?, phone=?, email=?, address=? WHERE id=?")->execute([$name,$phone,$email,$address,$id]); redirect('/laundry_lvl1/modules/customers/','✅ Pelanggan diupdate!'); }
}
?>
<div class="card" style="max-width:500px;margin:0 auto;">
    <div class="card-header"><div class="card-title">✏️ Edit Pelanggan</div><a href="/laundry_lvl1/modules/customers/" class="btn btn-secondary btn-sm">← Kembali</a></div>
    <div class="card-body">
        <form method="POST">
            <div class="form-group"><label class="form-label">Nama *</label><input type="text" name="name" class="form-input" value="<?=htmlspecialchars($cust['name'])?>" required></div>
            <div class="form-group"><label class="form-label">Telepon</label><input type="text" name="phone" class="form-input" value="<?=htmlspecialchars($cust['phone']??'')?>"></div>
            <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-input" value="<?=htmlspecialchars($cust['email']??'')?>"></div>
            <div class="form-group"><label class="form-label">Alamat</label><textarea name="address" class="form-textarea"><?=htmlspecialchars($cust['address']??'')?></textarea></div>
            <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">💾 Simpan</button>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>