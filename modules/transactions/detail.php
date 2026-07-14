<?php
require_once __DIR__ . '/../../includes/header.php';
requireLogin();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if(!$id) redirect('/laundry_lvl1/modules/transactions/','ID tidak valid!','error');
$franchiseId = $_SESSION['franchise_id'] ?? 1; $userId = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT t.*, c.name as customer_name, c.phone as customer_phone, c.loyalty_points as customer_points, s.name as service_name, s.price_per_kg, u.name as cashier_name, o.name as outlet_name, o.address as outlet_address, o.phone as outlet_phone FROM transactions t JOIN customers c ON t.customer_id = c.id JOIN services s ON t.service_id = s.id JOIN users u ON t.cashier_id = u.id JOIN outlets o ON t.outlet_id = o.id WHERE t.id = ? AND t.franchise_id = ?");
$stmt->execute([$id,$franchiseId]); $trx = $stmt->fetch();
if(!$trx) redirect('/laundry_lvl1/modules/transactions/','Tidak ditemukan!','error');

if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_status'])){
    $newStatus = $_POST['new_status'] ?? '';
    if(in_array($newStatus,['queue','washing','ironing','ready','completed'])){
        $old = $trx['laundry_status'];
        $db->prepare("UPDATE transactions SET laundry_status = ? WHERE id = ?")->execute([$newStatus,$id]);
        if($newStatus==='completed') $db->prepare("UPDATE transactions SET payment_status = 'paid' WHERE id = ?")->execute([$id]);
        $db->prepare("INSERT INTO transaction_logs (transaction_id, user_id, action, old_status, new_status) VALUES (?,?,'update_status',?,?)")->execute([$id,$userId,$old,$newStatus]);
        if(in_array($newStatus,['ready','completed']) && !empty($trx['customer_phone'])){
            $msg = "Halo Kak {$trx['customer_name']}! 👋\n\nCucian *{$trx['invoice_number']}* sudah *Siap Diambil*!\n💰 Total: ".formatRupiah($trx['total_price'])."\n\nTerima kasih! 🙏";
            $wa = sendWhatsApp($trx['customer_phone'], $msg);
            $db->prepare("INSERT INTO whatsapp_logs (transaction_id, customer_id, phone_number, message_type, message_content, status) VALUES (?,?,?,'status_update',?,?)")->execute([$id,$trx['customer_id'],$trx['customer_phone'],$msg,$wa['success']?'sent':'failed']);
        }
        redirect("/laundry_lvl1/modules/transactions/detail.php?id=$id",'✅ Status diperbarui!');
    }
}

$s = getStatusInfo($trx['laundry_status']);
$tanggal = date('d M Y', strtotime($trx['created_at'])); $jam = date('H:i', strtotime($trx['created_at']));
?>

<div class="no-print" style="margin-bottom:20px;display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
    <a href="/laundry_lvl1/modules/transactions/" class="btn btn-secondary">← Kembali</a>
    <button onclick="printStruk()" class="btn btn-primary">🖨️ Cetak Struk</button>
    <a href="/laundry_lvl1/modules/transactions/edit.php?id=<?=$id?>" class="btn btn-secondary">✏️ Edit</a>
    <?php if(isOwner()||isSuperAdmin()): ?><a href="/laundry_lvl1/modules/transactions/delete.php?id=<?=$id?>" class="btn btn-danger" onclick="return confirm('Yakin hapus?')">🗑️ Hapus</a><?php endif; ?>
    <form method="POST" style="display:flex;gap:8px;align-items:center;">
        <select name="new_status" class="form-select" style="width:auto;" onchange="this.form.submit()">
            <option value="">-- Ubah Status --</option>
            <option value="queue" <?=$trx['laundry_status']==='queue'?'selected':''?>>📋 Menunggu</option>
            <option value="washing" <?=$trx['laundry_status']==='washing'?'selected':''?>>🫧 Dicuci</option>
            <option value="ironing" <?=$trx['laundry_status']==='ironing'?'selected':''?>>👕 Disetrika</option>
            <option value="ready" <?=$trx['laundry_status']==='ready'?'selected':''?>>✨ Siap</option>
            <option value="completed" <?=$trx['laundry_status']==='completed'?'selected':''?>>✅ Selesai</option>
        </select>
        <input type="hidden" name="update_status" value="1">
    </form>
</div>

<div id="strukArea">
    <div style="max-width:380px;margin:0 auto;background:white;padding:20px 16px;border:1px solid #E2E8F0;border-radius:12px;font-family:'Courier New',monospace;font-size:13px;">
        <div style="text-align:center;"><div style="font-size:40px;">🧺</div><h1 style="font-size:16px;font-weight:900;"><?=htmlspecialchars($trx['outlet_name'])?></h1><p style="font-size:11px;color:#64748B;"><?=htmlspecialchars($trx['outlet_address'])?></p><div style="color:#CBD5E1;margin:8px 0;">━━━━━━━━━━━━━━━━━━━━</div></div>
        <div style="display:flex;justify-content:space-between;"><span>Invoice</span><span style="font-weight:700;color:var(--primary);"><?=htmlspecialchars($trx['invoice_number'])?></span></div>
        <div style="display:flex;justify-content:space-between;"><span>Tanggal</span><span><?=$tanggal?> / <?=$jam?></span></div>
        <div style="display:flex;justify-content:space-between;"><span>Kasir</span><span><?=htmlspecialchars($trx['cashier_name'])?></span></div>
        <div style="color:#CBD5E1;margin:8px 0;">━━━━━━━━━━━━━━━━━━━━</div>
        <div style="display:flex;justify-content:space-between;"><span>Pelanggan</span><strong><?=htmlspecialchars($trx['customer_name'])?></strong></div>
        <div style="display:flex;justify-content:space-between;"><span>Layanan</span><span><?=htmlspecialchars($trx['service_name'])?></span></div>
        <div style="display:flex;justify-content:space-between;"><span>Berat</span><span><?=$trx['weight_kg']?> kg</span></div>
        <div style="color:#CBD5E1;margin:8px 0;">━━━━━━━━━━━━━━━━━━━━</div>
        <div style="display:flex;justify-content:space-between;font-weight:700;font-size:16px;border-top:2px solid #1E293B;padding-top:8px;"><span>TOTAL</span><span style="color:var(--primary);"><?=formatRupiah($trx['total_price'])?></span></div>
        <div style="color:#CBD5E1;margin:8px 0;">━━━━━━━━━━━━━━━━━━━━</div>
        <div style="display:flex;justify-content:space-between;"><span>Status</span><span style="color:<?=$s['color']?>;font-weight:700;"><?=$s['icon']?> <?=$s['label']?></span></div>
        <div style="display:flex;justify-content:space-between;"><span>Bayar</span><span><?=$trx['payment_status']==='paid'?'✅ Lunas':'❌ Belum'?></span></div>
        <div style="display:flex;justify-content:space-between;"><span>Poin</span><span>⭐ +<?=$trx['loyalty_points_earned']?></span></div>
        <?php if(!empty($trx['qr_code_token'])): ?><div style="text-align:center;margin-top:8px;"><div style="color:#CBD5E1;margin:8px 0;">━━━━━━━━━━━━━━━━━━━━</div><p style="font-size:11px;">Scan untuk lacak:</p><img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?=urlencode($trx['tracking_url'])?>" style="width:120px;height:120px;"></div><?php endif; ?>
        <div style="text-align:center;margin-top:8px;font-size:11px;color:#94A3B8;"><div style="color:#CBD5E1;margin:8px 0;">━━━━━━━━━━━━━━━━━━━━</div><p>Terima kasih! 🙏</p><p style="font-size:10px;">Dicetak: <?=date('d/m/Y H:i')?></p></div>
    </div>
</div>

<style>@media print{body *{visibility:hidden}#strukArea,#strukArea *{visibility:visible}#strukArea{position:absolute;left:0;top:0;width:100%;padding:0}.no-print,.sidebar,.top-bar,.btn,form{display:none!important}.main-content{margin-left:0!important}.content-area{padding:0!important}body{background:white}@page{size:80mm auto;margin:0}}</style>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>