<?php
require_once __DIR__ . '/../../includes/header.php';
requireLogin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    redirect('index.php', 'ID tidak valid!', 'error');
}

$outletId = $_SESSION['outlet_id'];

$stmt = $db->prepare("SELECT * FROM customers WHERE id = :id AND outlet_id = :outlet_id");
$stmt->execute([':id' => $id, ':outlet_id' => $outletId]);
$customer = $stmt->fetch();

if (!$customer) {
    redirect('index.php', 'Pelanggan tidak ditemukan!', 'error');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    
    if (empty($name)) {
        $error = 'Nama pelanggan wajib diisi!';
    } else {
        $stmt = $db->prepare("UPDATE customers SET name = :name, phone = :phone WHERE id = :id");
        $stmt->execute([
            ':name' => $name,
            ':phone' => $phone,
            ':id' => $id
        ]);
        
        redirect('index.php', 'Pelanggan berhasil diperbarui!');
    }
}
?>

<div class="card" style="max-width: 500px; margin: 0 auto;">
    <div class="card-header">
        <h3>✏️ Edit Pelanggan</h3>
        <a href="index.php" class="btn btn-secondary btn-sm">← Kembali</a>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Nama Pelanggan *</label>
                <input type="text" name="name" class="form-input" value="<?= htmlspecialchars($customer['name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Nomor Telepon</label>
                <input type="text" name="phone" class="form-input" value="<?= htmlspecialchars($customer['phone'] ?? '') ?>">
            </div>
            
            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                💾 Simpan Perubahan
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>