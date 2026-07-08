<?php
// ============================================
// modules/transactions/detail.php - Level 1
// Detail Transaksi + Cetak Struk
// ============================================
require_once __DIR__ . '/../../includes/header.php';
requireLogin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    redirect('/laundry_lvl1/modules/transactions/', 'ID transaksi tidak valid!', 'error');
}

$outletId = $_SESSION['outlet_id'];
$userId = $_SESSION['user_id'];

// Ambil data transaksi
$stmt = $db->prepare("SELECT t.*, c.name as customer_name, c.phone as customer_phone, 
    s.name as service_name, s.price_per_kg, s.estimated_hours,
    u.name as cashier_name, o.name as outlet_name, o.address as outlet_address, o.phone as outlet_phone
    FROM transactions t 
    JOIN customers c ON t.customer_id = c.id 
    JOIN services s ON t.service_id = s.id 
    JOIN users u ON t.cashier_id = u.id 
    JOIN outlets o ON t.outlet_id = o.id 
    WHERE t.id = :id AND t.outlet_id = :outlet_id");
$stmt->execute([':id' => $id, ':outlet_id' => $outletId]);
$transaction = $stmt->fetch();

if (!$transaction) {
    redirect('/laundry_lvl1/modules/transactions/', 'Transaksi tidak ditemukan!', 'error');
}

// Update status jika ada POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = $_POST['new_status'] ?? '';
    $validStatuses = ['queue', 'washing', 'ironing', 'ready', 'completed'];
    
    if (in_array($newStatus, $validStatuses)) {
        $oldStatus = $transaction['laundry_status'];
        
        $stmt = $db->prepare("UPDATE transactions SET laundry_status = :status WHERE id = :id");
        $stmt->execute([':status' => $newStatus, ':id' => $id]);
        
        // Jika selesai, auto lunas
        if ($newStatus === 'completed') {
            $stmt = $db->prepare("UPDATE transactions SET payment_status = 'paid' WHERE id = :id");
            $stmt->execute([':id' => $id]);
        }
        
        logTransaction($db, $id, $userId, 'update_status', $oldStatus, $newStatus, 'Status diubah menjadi: ' . $newStatus);
        
        redirect("/laundry_lvl1/modules/transactions/detail.php?id=$id", 'Status berhasil diperbarui! ✅');
    }
}

$statusInfo = getStatusInfo($transaction['laundry_status']);

// Format tanggal
$tanggal = date('d M Y', strtotime($transaction['created_at']));
$jam = date('H:i', strtotime($transaction['created_at']));
?>

<!-- ============================================ -->
<!-- TOMBOL AKSI -->
<!-- ============================================ -->
<div class="no-print" style="margin-bottom: 20px; display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
    <a href="/laundry_lvl1/modules/transactions/" class="btn btn-secondary">← Kembali</a>
    <button onclick="printStruk()" class="btn btn-primary">🖨️ Cetak Struk</button>
    <a href="/laundry_lvl1/modules/transactions/edit.php?id=<?= $id ?>" class="btn btn-secondary">✏️ Edit</a>
    
    <!-- HAPUS - HANYA OWNER -->
    <?php if (isOwner()): ?>
    <a href="/laundry_lvl1/modules/transactions/delete.php?id=<?= $id ?>" 
       class="btn btn-sm" 
       style="background:#EF4444;color:white;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px;"
       onclick="return confirm('Yakin hapus transaksi ini?')">
       🗑️ Hapus
    </a>
    <?php endif; ?>
    
    <!-- Update Status -->
    <form method="POST" style="display: flex; gap: 8px; align-items: center;">
        <select name="new_status" class="form-select" style="width: auto; min-width: 160px;" onchange="this.form.submit()">
            <option value="">-- Ubah Status --</option>
            <option value="queue" <?= $transaction['laundry_status'] === 'queue' ? 'selected' : '' ?>>📋 Menunggu</option>
            <option value="washing" <?= $transaction['laundry_status'] === 'washing' ? 'selected' : '' ?>>🫧 Sedang Dicuci</option>
            <option value="ironing" <?= $transaction['laundry_status'] === 'ironing' ? 'selected' : '' ?>>👕 Sedang Disetrika</option>
            <option value="ready" <?= $transaction['laundry_status'] === 'ready' ? 'selected' : '' ?>>✨ Siap Diambil</option>
            <option value="completed" <?= $transaction['laundry_status'] === 'completed' ? 'selected' : '' ?>>✅ Selesai</option>
        </select>
        <input type="hidden" name="update_status" value="1">
    </form>
</div>

<!-- ============================================ -->
<!-- STRUK / INVOICE (Area Cetak) -->
<!-- ============================================ -->
<div id="strukArea">
    <div class="struk-wrapper">
        
        <!-- HEADER STRUK -->
        <div class="struk-header">
            <div class="struk-logo">🧺</div>
            <h1 class="struk-title"><?= htmlspecialchars($transaction['outlet_name']) ?></h1>
            <p class="struk-address"><?= htmlspecialchars($transaction['outlet_address']) ?></p>
            <p class="struk-phone">📞 <?= htmlspecialchars($transaction['outlet_phone']) ?></p>
            <div class="struk-divider">━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</div>
        </div>
        
        <!-- INFO INVOICE -->
        <div class="struk-info">
            <div class="struk-row">
                <span class="struk-label">No. Invoice</span>
                <span class="struk-value invoice-number"><?= htmlspecialchars($transaction['invoice_number']) ?></span>
            </div>
            <div class="struk-row">
                <span class="struk-label">Tanggal</span>
                <span class="struk-value"><?= $tanggal ?> / <?= $jam ?></span>
            </div>
            <div class="struk-row">
                <span class="struk-label">Kasir</span>
                <span class="struk-value"><?= htmlspecialchars($transaction['cashier_name']) ?></span>
            </div>
            <div class="struk-divider">━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</div>
        </div>
        
        <!-- DATA PELANGGAN -->
        <div class="struk-info">
            <div class="struk-row">
                <span class="struk-label">Pelanggan</span>
                <span class="struk-value"><strong><?= htmlspecialchars($transaction['customer_name']) ?></strong></span>
            </div>
            <?php if (!empty($transaction['customer_phone'])): ?>
            <div class="struk-row">
                <span class="struk-label">Telepon</span>
                <span class="struk-value"><?= htmlspecialchars($transaction['customer_phone']) ?></span>
            </div>
            <?php endif; ?>
            <div class="struk-divider">━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</div>
        </div>
        
        <!-- DETAIL LAYANAN -->
        <div class="struk-info">
            <div class="struk-row">
                <span class="struk-label">Layanan</span>
                <span class="struk-value"><?= htmlspecialchars($transaction['service_name']) ?></span>
            </div>
            <div class="struk-row">
                <span class="struk-label">Harga /kg</span>
                <span class="struk-value"><?= formatRupiah($transaction['price_per_kg']) ?></span>
            </div>
            <div class="struk-row">
                <span class="struk-label">Berat</span>
                <span class="struk-value"><?= $transaction['weight_kg'] ?> kg</span>
            </div>
            <?php if (!empty($transaction['notes'])): ?>
            <div class="struk-row">
                <span class="struk-label">Catatan</span>
                <span class="struk-value"><?= htmlspecialchars($transaction['notes']) ?></span>
            </div>
            <?php endif; ?>
            <div class="struk-divider">━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</div>
        </div>
        
        <!-- TOTAL -->
        <div class="struk-total-section">
            <div class="struk-row total-row">
                <span class="struk-label">TOTAL</span>
                <span class="struk-value total-price"><?= formatRupiah($transaction['total_price']) ?></span>
            </div>
            <div class="struk-divider">━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</div>
        </div>
        
        <!-- STATUS -->
        <div class="struk-info">
            <div class="struk-row">
                <span class="struk-label">Status Bayar</span>
                <span class="struk-value">
                    <?php if ($transaction['payment_status'] === 'paid'): ?>
                        ✅ LUNAS
                    <?php else: ?>
                        ❌ BELUM LUNAS
                    <?php endif; ?>
                </span>
            </div>
            <div class="struk-row">
                <span class="struk-label">Status Cucian</span>
                <span class="struk-value" style="color: <?= $statusInfo['color'] ?>; font-weight: 700;">
                    <?= $statusInfo['label'] ?>
                </span>
            </div>
            <?php if ($transaction['estimated_hours']): ?>
            <div class="struk-row">
                <span class="struk-label">Estimasi Selesai</span>
                <span class="struk-value"><?= $transaction['estimated_hours'] ?> jam</span>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- QR CODE -->
        <?php if (!empty($transaction['qr_code_token'])): ?>
        <div class="struk-qr">
            <div class="struk-divider">━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</div>
            <p style="text-align: center; font-size: 11px; margin-bottom: 8px;">Scan untuk lacak cucian:</p>
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . '/laundry_lvl1/qr/track.php?token=' . $transaction['qr_code_token']) ?>" 
                 alt="QR Code" 
                 style="display: block; margin: 0 auto; width: 120px; height: 120px;">
        </div>
        <?php endif; ?>
        
        <!-- FOOTER STRUK -->
        <div class="struk-footer">
            <div class="struk-divider">━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</div>
            <p>Terima kasih telah mempercayakan</p>
            <p>cucian Anda kepada kami! 🙏</p>
            <p style="margin-top: 8px;">🧺 <?= htmlspecialchars($transaction['outlet_name']) ?></p>
            <p style="font-size: 10px;">Dicetak: <?= date('d/m/Y H:i') ?></p>
        </div>
        
    </div>
</div>

<!-- CSS STRUK -->
<style>
    .struk-wrapper{max-width:380px;margin:0 auto;background:white;padding:20px 16px;border:1px solid #E2E8F0;border-radius:12px;font-family:'Courier New','Inter',monospace;font-size:13px;line-height:1.6;color:#1E293B}
    .struk-header{text-align:center;margin-bottom:8px}
    .struk-logo{font-size:40px;margin-bottom:4px}
    .struk-title{font-size:16px;font-weight:900;color:#0F172A;text-transform:uppercase;letter-spacing:1px;margin-bottom:2px}
    .struk-address{font-size:11px;color:#64748B;margin-bottom:2px}
    .struk-phone{font-size:11px;color:#64748B}
    .struk-divider{text-align:center;color:#CBD5E1;font-size:10px;margin:8px 0;letter-spacing:2px}
    .struk-row{display:flex;justify-content:space-between;padding:3px 0}
    .struk-label{color:#64748B;font-size:12px}
    .struk-value{font-weight:600;text-align:right;font-size:12px}
    .invoice-number{color:#1D4ED8;font-weight:700;letter-spacing:.5px}
    .struk-total-section{margin:4px 0}
    .total-row{padding:6px 0}
    .total-price{font-size:18px;font-weight:900;color:#1D4ED8}
    .struk-qr{text-align:center;margin-top:8px}
    .struk-footer{text-align:center;font-size:11px;color:#94A3B8;margin-top:8px}
    
    @media print{
        body *{visibility:hidden}
        #strukArea,#strukArea *{visibility:visible}
        #strukArea{position:absolute;left:0;top:0;width:100%;padding:0}
        .struk-wrapper{max-width:80mm;width:80mm;margin:0 auto;padding:5mm;border:none;border-radius:0;box-shadow:none;font-size:11px;color:#000}
        .struk-divider,.struk-title,.struk-label,.struk-value,.total-price,.invoice-number{color:#000}
        .struk-footer{color:#666}
        .no-print,.sidebar,.top-bar,.btn,form,.content-area>*:not(#strukArea),.card:not(#strukArea){display:none!important}
        .main-content{margin-left:0!important}
        .content-area{padding:0!important}
        body{background:white;margin:0;padding:0}
        @page{size:80mm auto;margin:0}
    }
    @media screen{.struk-wrapper{box-shadow:0 4px 20px rgba(0,0,0,0.08)}}
</style>

<script>
function printStruk(){window.print()}
document.addEventListener('keydown',function(e){if(e.ctrlKey&&e.key==='p'){e.preventDefault();printStruk()}});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>