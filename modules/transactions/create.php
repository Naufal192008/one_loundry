<?php
// ============================================
// modules/transactions/create.php - Level 1
// Form Transaksi Baru
// ============================================
require_once __DIR__ . '/../../includes/header.php';
requireLogin();

$outletId = $_SESSION['outlet_id'];
$userId = $_SESSION['user_id'];
$error = '';

// Ambil daftar layanan aktif
$stmt = $db->prepare("SELECT * FROM services WHERE outlet_id = :oid AND is_active = 1 ORDER BY name");
$stmt->execute([':oid' => $outletId]);
$services = $stmt->fetchAll();

// Ambil daftar pelanggan
$stmt = $db->prepare("SELECT * FROM customers WHERE outlet_id = :oid ORDER BY name");
$stmt->execute([':oid' => $outletId]);
$customers = $stmt->fetchAll();

// Proses form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerId = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
    $serviceId = filter_input(INPUT_POST, 'service_id', FILTER_VALIDATE_INT);
    $weightKg = filter_input(INPUT_POST, 'weight_kg', FILTER_VALIDATE_FLOAT);
    $notes = sanitize($_POST['notes'] ?? '');
    
    if (!$customerId || !$serviceId || !$weightKg || $weightKg <= 0) {
        $error = 'Semua field wajib diisi dengan benar!';
    } else {
        // Ambil harga layanan
        $service = getService($db, $serviceId);
        if (!$service) {
            $error = 'Layanan tidak valid!';
        } else {
            $totalPrice = calculatePrice($service['price_per_kg'], $weightKg);
            $invoiceNumber = generateInvoiceNumber();
            $qrToken = generateQRToken();
            
            try {
                $db->beginTransaction();
                
                $stmt = $db->prepare("INSERT INTO transactions (outlet_id, customer_id, service_id, cashier_id, invoice_number, weight_kg, total_price, payment_status, laundry_status, qr_code_token, notes) VALUES (:oid, :cid, :sid, :uid, :inv, :weight, :total, 'unpaid', 'queue', :qr, :notes)");
                $stmt->execute([
                    ':oid' => $outletId,
                    ':cid' => $customerId,
                    ':sid' => $serviceId,
                    ':uid' => $userId,
                    ':inv' => $invoiceNumber,
                    ':weight' => $weightKg,
                    ':total' => $totalPrice,
                    ':qr' => $qrToken,
                    ':notes' => $notes
                ]);
                
                $transactionId = $db->lastInsertId();
                
                logTransaction($db, $transactionId, $userId, 'create', null, 'queue', 'Transaksi baru');
                
                $db->commit();
                
                redirect("/laundry_lvl1/modules/transactions/detail.php?id=$transactionId", '✅ Transaksi berhasil dibuat! Silakan cetak struk.');
            } catch (Exception $e) {
                $db->rollBack();
                $error = 'Gagal membuat transaksi: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-header">
        <h3>🆕 Transaksi Baru</h3>
        <a href="/laundry_lvl1/modules/transactions/" class="btn btn-secondary btn-sm">← Kembali</a>
    </div>
    <div class="card-body">
        <?php if ($error): ?>
            <div style="background: #FEE2E2; color: #991B1B; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #FECACA;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="transactionForm">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">👤 Pelanggan *</label>
                    <select name="customer_id" class="form-select" required>
                        <option value="">-- Pilih Pelanggan --</option>
                        <?php foreach ($customers as $cust): ?>
                        <option value="<?= $cust['id'] ?>">
                            <?= htmlspecialchars($cust['name']) ?> 
                            <?= $cust['phone'] ? '(' . $cust['phone'] . ')' : '' ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">🧺 Jenis Layanan *</label>
                    <select name="service_id" id="serviceSelect" class="form-select" required>
                        <option value="">-- Pilih Layanan --</option>
                        <?php foreach ($services as $svc): ?>
                        <option value="<?= $svc['id'] ?>" data-price="<?= $svc['price_per_kg'] ?>" data-hours="<?= $svc['estimated_hours'] ?>">
                            <?= htmlspecialchars($svc['name']) ?> - <?= formatRupiah($svc['price_per_kg']) ?>/kg
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">⚖️ Berat Cucian (kg) *</label>
                <input type="number" name="weight_kg" id="weightInput" class="form-input" 
                       placeholder="Masukkan berat dalam kg (contoh: 3.5)" 
                       step="0.1" min="0.1" required>
            </div>
            
            <div class="form-group" style="background: #F0F9FF; padding: 16px; border-radius: 8px; border: 1px solid #BAE6FD;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 16px; font-weight: 600;">💰 Total Harga:</span>
                    <span id="totalPriceDisplay" style="font-size: 28px; font-weight: 700; color: #1D4ED8;">Rp 0</span>
                </div>
                <div id="estimatedTime" style="font-size: 12px; color: #64748B; margin-top: 4px;"></div>
            </div>
            
            <div class="form-group">
                <label class="form-label">📝 Catatan (opsional)</label>
                <textarea name="notes" class="form-textarea" rows="2" placeholder="Catatan khusus..."></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                💾 Simpan & Cetak Struk
            </button>
        </form>
    </div>
</div>

<script>
const serviceSelect = document.getElementById('serviceSelect');
const weightInput = document.getElementById('weightInput');
const totalDisplay = document.getElementById('totalPriceDisplay');
const estimatedTime = document.getElementById('estimatedTime');

function hitungTotal() {
    const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
    const pricePerKg = parseFloat(selectedOption.dataset.price) || 0;
    const weight = parseFloat(weightInput.value) || 0;
    const hours = parseInt(selectedOption.dataset.hours) || 0;
    
    const total = pricePerKg * weight;
    totalDisplay.textContent = 'Rp ' + total.toLocaleString('id-ID');
    
    if (hours > 0 && weight > 0) {
        estimatedTime.textContent = '⏱ Estimasi selesai: ' + hours + ' jam';
    } else {
        estimatedTime.textContent = '';
    }
}

serviceSelect.addEventListener('change', hitungTotal);
weightInput.addEventListener('input', hitungTotal);
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>