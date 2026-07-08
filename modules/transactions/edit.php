<?php
require_once __DIR__ . '/../../includes/header.php';
requireLogin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    redirect('index.php', 'ID transaksi tidak valid!', 'error');
}

$outletId = $_SESSION['outlet_id'];

$stmt = $db->prepare("SELECT * FROM transactions WHERE id = :id AND outlet_id = :outlet_id");
$stmt->execute([':id' => $id, ':outlet_id' => $outletId]);
$transaction = $stmt->fetch();

if (!$transaction) {
    redirect('index.php', 'Transaksi tidak ditemukan!', 'error');
}

// Ambil daftar layanan & pelanggan
$stmt = $db->prepare("SELECT * FROM services WHERE outlet_id = :outlet_id AND is_active = 1");
$stmt->execute([':outlet_id' => $outletId]);
$services = $stmt->fetchAll();

$stmt = $db->prepare("SELECT * FROM customers WHERE outlet_id = :outlet_id");
$stmt->execute([':outlet_id' => $outletId]);
$customers = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerId = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
    $serviceId = filter_input(INPUT_POST, 'service_id', FILTER_VALIDATE_INT);
    $weightKg = filter_input(INPUT_POST, 'weight_kg', FILTER_VALIDATE_FLOAT);
    $notes = sanitize($_POST['notes'] ?? '');
    
    if ($customerId && $serviceId && $weightKg > 0) {
        $service = getService($db, $serviceId);
        $totalPrice = calculatePrice($service['price_per_kg'], $weightKg);
        
        $stmt = $db->prepare("UPDATE transactions SET customer_id = :cid, service_id = :sid, weight_kg = :weight, total_price = :total, notes = :notes WHERE id = :id");
        $stmt->execute([
            ':cid' => $customerId,
            ':sid' => $serviceId,
            ':weight' => $weightKg,
            ':total' => $totalPrice,
            ':notes' => $notes,
            ':id' => $id
        ]);
        
        logTransaction($db, $id, $_SESSION['user_id'], 'edit', null, null, 'Data transaksi diubah');
        
        redirect("detail.php?id=$id", 'Transaksi berhasil diperbarui!');
    }
}
?>

<div class="card" style="max-width: 700px; margin: 0 auto;">
    <div class="card-header">
        <h3>✏️ Edit Transaksi</h3>
        <a href="detail.php?id=<?= $id ?>" class="btn btn-secondary btn-sm">← Kembali</a>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Pelanggan</label>
                    <select name="customer_id" class="form-select" required>
                        <?php foreach ($customers as $cust): ?>
                        <option value="<?= $cust['id'] ?>" <?= $cust['id'] == $transaction['customer_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cust['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Jenis Layanan</label>
                    <select name="service_id" class="form-select" required>
                        <?php foreach ($services as $svc): ?>
                        <option value="<?= $svc['id'] ?>" <?= $svc['id'] == $transaction['service_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($svc['name']) ?> - <?= formatRupiah($svc['price_per_kg']) ?>/kg
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Berat Cucian (kg)</label>
                <input type="number" name="weight_kg" class="form-input" 
                       value="<?= $transaction['weight_kg'] ?>" 
                       step="0.1" min="0.1" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Catatan</label>
                <textarea name="notes" class="form-textarea"><?= htmlspecialchars($transaction['notes'] ?? '') ?></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                💾 Simpan Perubahan
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>