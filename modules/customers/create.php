<?php
require_once __DIR__ . '/../../includes/header.php';
requireLogin();
$franchiseId = $_SESSION['franchise_id'] ?? 1; $outletId = $_SESSION['outlet_id'] ?? 1; $error = '';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $name = sanitize($_POST['name']??''); $phone = sanitize($_POST['phone']??''); $email = sanitize($_POST['email']??''); $address = sanitize($_POST['address']??'');
    if(empty($name)) $error = 'Nama wajib diisi!';
    else {
        $stmt = $db->prepare("INSERT INTO customers (franchise_id, outlet_id, name, phone, email, address) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$franchiseId,$outletId,$name,$phone,$email,$address]);
        redirect('/laundry_lvl1/modules/customers/','✅ Pelanggan berhasil ditambahkan!');
    }
}
?>
<div class="card" style="max-width:500px;margin:0 auto;">
    <div class="card-header"><div class="card-title">➕ Pelanggan Baru</div><a href="/laundry_lvl1/modules/customers/" class="btn btn-secondary btn-sm">← Kembali</a></div>
    <div class="card-body">
        <?php if($error): ?><div style="background:#FEE2E2;color:#991B1B;padding:12px 16px;border-radius:8px;margin-bottom:20px;"><?=htmlspecialchars($error)?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group"><label class="form-label">Nama *</label><input type="text" name="name" class="form-input" required></div>
            <div class="form-group"><label class="form-label">Telepon</label><input type="text" name="phone" class="form-input"></div>
            <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-input"></div>
            <div class="form-group"><label class="form-label">Alamat</label><textarea name="address" class="form-textarea"></textarea></div>
            <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">💾 Simpan</button>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>