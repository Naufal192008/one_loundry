<?php
// ============================================
// modules/transactions/detail.php
// Detail Transaksi + Cetak Struk
// ============================================
require_once __DIR__ . '/../../includes/header.php';
requireLogin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    redirect('/modules/transactions/', 'ID transaksi tidak valid!', 'error');
}

$franchiseId = $_SESSION['franchise_id'] ?? 1;
$userId = $_SESSION['user_id'];

// Ambil data transaksi
$stmt = $db->prepare("SELECT t.*, c.name as customer_name, c.phone as customer_phone, c.loyalty_points as customer_points, 
    s.name as service_name, s.price_per_kg, s.estimated_hours,
    u.name as cashier_name, o.name as outlet_name, o.address as outlet_address, o.phone as outlet_phone
    FROM transactions t 
    JOIN customers c ON t.customer_id = c.id 
    JOIN services s ON t.service_id = s.id 
    JOIN users u ON t.cashier_id = u.id 
    JOIN outlets o ON t.outlet_id = o.id 
    WHERE t.id = :id AND t.franchise_id = :fid");
$stmt->execute([':id' => $id, ':fid' => $franchiseId]);
$trx = $stmt->fetch();

if (!$trx) {
    redirect('/modules/transactions/', 'Transaksi tidak ditemukan!', 'error');
}

// Update status jika ada POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = $_POST['new_status'] ?? '';
    $validStatuses = ['queue', 'washing', 'ironing', 'ready', 'completed'];
    
    if (in_array($newStatus, $validStatuses)) {
        $oldStatus = $trx['laundry_status'];
        
        $stmt = $db->prepare("UPDATE transactions SET laundry_status = :status WHERE id = :id");
        $stmt->execute([':status' => $newStatus, ':id' => $id]);
        
        // Jika selesai, auto lunas
        if ($newStatus === 'completed') {
            $stmt = $db->prepare("UPDATE transactions SET payment_status = 'paid' WHERE id = :id");
            $stmt->execute([':id' => $id]);
        }
        
        // Log aktivitas
        $stmt = $db->prepare("INSERT INTO transaction_logs (transaction_id, user_id, action, old_status, new_status) VALUES (:tid, :uid, 'update_status', :old, :new)");
        $stmt->execute([':tid' => $id, ':uid' => $userId, ':old' => $oldStatus, ':new' => $newStatus]);
        
        // Kirim WhatsApp untuk status ready/completed
        if (in_array($newStatus, ['ready', 'completed']) && !empty($trx['customer_phone'])) {
            $msg = "Halo Kak {$trx['customer_name']}! 👋\n\nCucian dengan invoice *{$trx['invoice_number']}* sudah *" . getStatusInfo($newStatus)['label'] . "*!\n\n💰 Total: " . formatRupiah($trx['total_price']) . "\n⭐ Poin: {$trx['loyalty_points_earned']}\n\nTerima kasih! 🙏";
            
            $wa = sendWhatsApp($trx['customer_phone'], $msg);
            
            $stmt = $db->prepare("INSERT INTO whatsapp_logs (transaction_id, customer_id, phone_number, message_type, message_content, status) VALUES (:tid, :cid, :phone, 'status_update', :msg, :status)");
            $stmt->execute([':tid' => $id, ':cid' => $trx['customer_id'], ':phone' => $trx['customer_phone'], ':msg' => $msg, ':status' => $wa['success'] ? 'sent' : 'failed']);
            
            if ($wa['success']) {
                $stmt = $db->prepare("UPDATE transactions SET whatsapp_notified = 1, last_notification_at = NOW() WHERE id = :id");
                $stmt->execute([':id' => $id]);
            }
        }
        
        redirect("/modules/transactions/detail.php?id=$id", '✅ Status berhasil diperbarui!');
    }
}

// Kirim pengingat WhatsApp
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_reminder'])) {
    if (!empty($trx['customer_phone'])) {
        $msg = "Halo Kak {$trx['customer_name']}, cucian dengan invoice *{$trx['invoice_number']}* sudah jadi nih, yuk diambil! 😊";
        $wa = sendWhatsApp($trx['customer_phone'], $msg);
        
        $stmt = $db->prepare("INSERT INTO whatsapp_logs (transaction_id, customer_id, phone_number, message_type, message_content, status) VALUES (:tid, :cid, :phone, 'reminder', :msg, :status)");
        $stmt->execute([':tid' => $id, ':cid' => $trx['customer_id'], ':phone' => $trx['customer_phone'], ':msg' => $msg, ':status' => $wa['success'] ? 'sent' : 'failed']);
        
        redirect("/modules/transactions/detail.php?id=$id", $wa['success'] ? '✅ Pengingat terkirim!' : '❌ Gagal mengirim pengingat');
    }
}

$statusInfo = getStatusInfo($trx['laundry_status']);
$tanggal = date('d M Y', strtotime($trx['created_at']));
$jam = date('H:i', strtotime($trx['created_at']));

// Ambil riwayat WhatsApp
$stmt = $db->prepare("SELECT * FROM whatsapp_logs WHERE transaction_id = :tid ORDER BY created_at DESC LIMIT 5");
$stmt->execute([':tid' => $id]);
$waLogs = $stmt->fetchAll();
?>

<!-- TOMBOL AKSI -->
<div class="no-print" style="margin-bottom:20px;display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
    <a href="/modules/transactions/" class="btn btn-secondary">← Kembali</a>
    <button onclick="printStruk()" class="btn btn-primary">🖨️ Cetak Struk</button>
    <a href="/modules/transactions/edit.php?id=<?= $id ?>" class="btn btn-secondary">✏️ Edit</a>
    
    <?php if (isOwner()): ?>
    <a href="/modules/transactions/delete.php?id=<?= $id ?>" class="btn btn-danger" onclick="return confirm('Yakin hapus transaksi ini?')">🗑️ Hapus</a>
    <?php endif; ?>
    
    <!-- Update Status -->
    <form method="POST" style="display:flex;gap:8px;align-items:center;">
        <select name="new_status" class="form-select" style="width:auto;min-width:160px;" onchange="this.form.submit()">
            <option value="">-- Ubah Status --</option>
            <option value="queue" <?= $trx['laundry_status'] === 'queue' ? 'selected' : '' ?>>📋 Menunggu</option>
            <option value="washing" <?= $trx['laundry_status'] === 'washing' ? 'selected' : '' ?>>🫧 Sedang Dicuci</option>
            <option value="ironing" <?= $trx['laundry_status'] === 'ironing' ? 'selected' : '' ?>>👕 Sedang Disetrika</option>
            <option value="ready" <?= $trx['laundry_status'] === 'ready' ? 'selected' : '' ?>>✨ Siap Diambil</option>
            <option value="completed" <?= $trx['laundry_status'] === 'completed' ? 'selected' : '' ?>>✅ Selesai</option>
        </select>
        <input type="hidden" name="update_status" value="1">
    </form>
    
    <!-- Kirim Pengingat -->
    <?php if (in_array($trx['laundry_status'], ['ready', 'completed']) && !empty($trx['customer_phone'])): ?>
    <form method="POST" style="display:inline;">
        <button type="submit" name="send_reminder" class="btn btn-sm btn-secondary">📱 Kirim Pengingat WA</button>
    </form>
    <?php endif; ?>
</div>

<!-- STRUK / INVOICE -->
<div id="strukArea">
    <div style="max-width:380px;margin:0 auto;background:white;padding:20px 16px;border:1px solid #E2E8F0;border-radius:12px;font-family:'Courier New',monospace;font-size:13px;">
        
        <!-- HEADER -->
        <div style="text-align:center;margin-bottom:8px;">
            <div style="font-size:40px;">🧺</div>
            <h1 style="font-size:16px;font-weight:900;text-transform:uppercase;"><?= htmlspecialchars($trx['outlet_name']) ?></h1>
            <p style="font-size:11px;color:#64748B;"><?= htmlspecialchars($trx['outlet_address']) ?></p>
            <p style="font-size:11px;color:#64748B;">📞 <?= htmlspecialchars($trx['outlet_phone']) ?></p>
            <div style="color:#CBD5E1;margin:8px 0;">━━━━━━━━━━━━━━━━━━━━</div>
        </div>
        
        <!-- INFO -->
        <div style="display:flex;justify-content:space-between;padding:3px 0;"><span style="color:#64748B;">Invoice</span><span style="font-weight:700;color:#1D4ED8;"><?= htmlspecialchars($trx['invoice_number']) ?></span></div>
        <div style="display:flex;justify-content:space-between;padding:3px 0;"><span style="color:#64748B;">Tanggal</span><span><?= $tanggal ?> / <?= $jam ?></span></div>
        <div style="display:flex;justify-content:space-between;padding:3px 0;"><span style="color:#64748B;">Kasir</span><span><?= htmlspecialchars($trx['cashier_name']) ?></span></div>
        <div style="color:#CBD5E1;margin:8px 0;">━━━━━━━━━━━━━━━━━━━━</div>
        
        <!-- PELANGGAN -->
        <div style="display:flex;justify-content:space-between;padding:3px 0;"><span style="color:#64748B;">Pelanggan</span><strong><?= htmlspecialchars($trx['customer_name']) ?></strong></div>
        <?php if (!empty($trx['customer_phone'])): ?>
        <div style="display:flex;justify-content:space-between;padding:3px 0;"><span style="color:#64748B;">Telepon</span><span><?= htmlspecialchars($trx['customer_phone']) ?></span></div>
        <?php endif; ?>
        <div style="display:flex;justify-content:space-between;padding:3px 0;"><span style="color:#64748B;">Poin</span><span>⭐ <?= number_format($trx['customer_points']) ?></span></div>
        <div style="color:#CBD5E1;margin:8px 0;">━━━━━━━━━━━━━━━━━━━━</div>
        
        <!-- LAYANAN -->
        <div style="display:flex;justify-content:space-between;padding:3px 0;"><span style="color:#64748B;">Layanan</span><span><?= htmlspecialchars($trx['service_name']) ?></span></div>
        <div style="display:flex;justify-content:space-between;padding:3px 0;"><span style="color:#64748B;">Harga /kg</span><span><?= formatRupiah($trx['price_per_kg']) ?></span></div>
        <div style="display:flex;justify-content:space-between;padding:3px 0;"><span style="color:#64748B;">Berat</span><span><?= $trx['weight_kg'] ?> kg</span></div>
        <?php if (!empty($trx['notes'])): ?>
        <div style="display:flex;justify-content:space-between;padding:3px 0;"><span style="color:#64748B;">Catatan</span><span><?= htmlspecialchars($trx['notes']) ?></span></div>
        <?php endif; ?>
        <div style="color:#CBD5E1;margin:8px 0;">━━━━━━━━━━━━━━━━━━━━</div>
        
        <!-- TOTAL -->
        <div style="display:flex;justify-content:space-between;padding:6px 0;font-weight:700;font-size:16px;border-top:2px solid #1E293B;"><span>TOTAL</span><span style="color:#1D4ED8;"><?= formatRupiah($trx['total_price']) ?></span></div>
        <div style="color:#CBD5E1;margin:8px 0;">━━━━━━━━━━━━━━━━━━━━</div>
        
        <!-- STATUS -->
        <div style="display:flex;justify-content:space-between;padding:3px 0;"><span style="color:#64748B;">Status Bayar</span><span><?= $trx['payment_status'] === 'paid' ? '✅ LUNAS' : '❌ BELUM' ?></span></div>
        <div style="display:flex;justify-content:space-between;padding:3px 0;"><span style="color:#64748B;">Status Cucian</span><span style="color:<?= $statusInfo['color'] ?>;font-weight:700;"><?= $statusInfo['icon'] ?> <?= $statusInfo['label'] ?></span></div>
        <div style="display:flex;justify-content:space-between;padding:3px 0;"><span style="color:#64748B;">Poin Didapat</span><span>⭐ +<?= $trx['loyalty_points_earned'] ?></span></div>
        <div style="display:flex;justify-content:space-between;padding:3px 0;"><span style="color:#64748B;">WA Notifikasi</span><span><?= $trx['whatsapp_notified'] ? '✅ Terkirim' : '❌ Belum' ?></span></div>
        
        <!-- QR CODE -->
        <?php if (!empty($trx['qr_code_token'])): ?>
        <div style="text-align:center;margin-top:8px;">
            <div style="color:#CBD5E1;margin:8px 0;">━━━━━━━━━━━━━━━━━━━━</div>
            <p style="font-size:11px;">Scan untuk lacak cucian:</p>
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= urlencode($trx['tracking_url']) ?>" alt="QR Code" style="width:120px;height:120px;">
        </div>
        <?php endif; ?>
        
        <!-- FOOTER -->
        <div style="text-align:center;margin-top:8px;font-size:11px;color:#94A3B8;">
            <div style="color:#CBD5E1;margin:8px 0;">━━━━━━━━━━━━━━━━━━━━</div>
            <p>Terima kasih telah mempercayakan</p>
            <p>cucian Anda kepada kami! 🙏</p>
            <p style="margin-top:8px;">🧺 <?= htmlspecialchars($trx['outlet_name']) ?></p>
            <p style="font-size:10px;">Dicetak: <?= date('d/m/Y H:i') ?></p>
        </div>
    </div>
</div>

<!-- CSS PRINT -->
<style>
@media print {
    body * { visibility: hidden; }
    #strukArea, #strukArea * { visibility: visible; }
    #strukArea { position: absolute; left: 0; top: 0; width: 100%; padding: 0; }
    .no-print, .sidebar, .top-bar, .btn, form, .content-area > *:not(#strukArea) { display: none !important; }
    .main-content { margin-left: 0 !important; }
    .content-area { padding: 0 !important; }
    body { background: white; margin: 0; padding: 0; }
    @page { size: 80mm auto; margin: 0; }
}
</style>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>