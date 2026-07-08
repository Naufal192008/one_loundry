<?php
require_once __DIR__ . '/../../includes/header.php';
requireLogin();

$outletId = $_SESSION['outlet_id'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    
    if (empty($name)) {
        $error = 'Nama pelanggan wajib diisi!';
    } else {
        $stmt = $db->prepare("INSERT INTO customers (outlet_id, name, phone) VALUES (:outlet_id, :name, :phone)");
        $stmt->execute([
            ':outlet_id' => $outletId,
            ':name' => $name,
            ':phone' => $phone
        ]);
        
        redirect('index.php', 'Pelanggan berhasil ditambahkan!');
    }
}
?>

<div class="card" style="max-width: 500px; margin: 0 auto;">
    <div class="card-header">
        <h3>➕ Pelanggan Baru</h3>
        <a href="index.php" class="btn btn-secondary btn-sm">← Kembali</a>
    </div>
    <div class="card-body">
        <?php if ($error): ?>
            <div style="background: #FEE2E2; color: #991B1B; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Nama Pelanggan *</label>
                <input type="text" name="name" class="form-input" placeholder="Nama lengkap" required autofocus>
            </div>
            
            <div class="form-group">
                <label class="form-label">Nomor Telepon</label>
                <input type="text" name="phone" class="form-input" placeholder="08xxxxxxxxxx">
            </div>
            
            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                💾 Simpan Pelanggan
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>