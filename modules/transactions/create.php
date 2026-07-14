<?php
require_once __DIR__ . '/../../includes/header.php';
requireLogin();

$franchiseId = $_SESSION['franchise_id'] ?? 1; $outletId = $_SESSION['outlet_id'] ?? 1; $userId = $_SESSION['user_id']; $error = '';

$stmt = $db->prepare("SELECT * FROM services WHERE franchise_id = ? AND is_active = 1 ORDER BY name"); $stmt->execute([$franchiseId]); $services = $stmt->fetchAll();
$stmt = $db->prepare("SELECT * FROM customers WHERE franchise_id = ? ORDER BY name"); $stmt->execute([$franchiseId]); $customers = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerId = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
    $serviceId = filter_input(INPUT_POST, 'service_id', FILTER_VALIDATE_INT);
    $weightKg = filter_input(INPUT_POST, 'weight_kg', FILTER_VALIDATE_FLOAT);
    $useLoyalty = isset($_POST['use_loyalty_points']); $pointsToRedeem = filter_input(INPUT_POST, 'points_to_redeem', FILTER_VALIDATE_INT) ?? 0;
    $sendWhatsApp = isset($_POST['send_whatsapp']); $notes = sanitize($_POST['notes'] ?? '');
    
    if (!$customerId || !$serviceId || !$weightKg || $weightKg <= 0) { $error = 'Semua field wajib diisi!'; }
    else {
        $stmt = $db->prepare("SELECT price_per_kg FROM services WHERE id = ?"); $stmt->execute([$serviceId]); $service = $stmt->fetch();
        if (!$service) { $error = 'Layanan tidak valid!'; }
        else {
            $totalPrice = $service['price_per_kg'] * $weightKg; $discountAmount = 0; $redeemedPoints = 0;
            if ($useLoyalty && $pointsToRedeem > 0) {
                $custPoints = getCustomerPoints($db, $customerId);
                if ($custPoints >= $pointsToRedeem && $pointsToRedeem >= MIN_POINTS_REDEEM) { $discountAmount = pointsToRupiah($pointsToRedeem); if ($discountAmount > $totalPrice) $discountAmount = $totalPrice; $totalPrice -= $discountAmount; $redeemedPoints = $pointsToRedeem; }
            }
            $invoiceNumber = generateInvoiceNumber(); $qrToken = generateQRToken(); $trackingUrl = getTrackingUrl($qrToken); $pointsEarned = calculateLoyaltyPoints($weightKg);
            try {
                $db->beginTransaction();
                $stmt = $db->prepare("INSERT INTO transactions (franchise_id, outlet_id, customer_id, service_id, cashier_id, invoice_number, weight_kg, unit_price, subtotal, discount_amount, total_price, payment_status, laundry_status, qr_code_token, tracking_url, loyalty_points_earned, notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,'unpaid','queue',?,?,?,?)");
                $stmt->execute([$franchiseId,$outletId,$customerId,$serviceId,$userId,$invoiceNumber,$weightKg,$service['price_per_kg'],$service['price_per_kg']*$weightKg,$discountAmount,$totalPrice,$qrToken,$trackingUrl,$pointsEarned,$notes]);
                $transactionId = $db->lastInsertId();
                if ($redeemedPoints > 0) { $db->prepare("UPDATE customers SET loyalty_points = loyalty_points - ? WHERE id = ?")->execute([$redeemedPoints,$customerId]); $db->prepare("INSERT INTO loyalty_points_history (customer_id, transaction_id, points_redeemed, balance_after, description) VALUES (?,?,?,(SELECT loyalty_points FROM customers WHERE id=?),?)")->execute([$customerId,$transactionId,$redeemedPoints,$customerId,'Penukaran poin']); }
                if ($pointsEarned > 0) { $db->prepare("UPDATE customers SET loyalty_points = loyalty_points + ? WHERE id = ?")->execute([$pointsEarned,$customerId]); $db->prepare("INSERT INTO loyalty_points_history (customer_id, transaction_id, points_earned, balance_after, description) VALUES (?,?,?,(SELECT loyalty_points FROM customers WHERE id=?),?)")->execute([$customerId,$transactionId,$pointsEarned,$customerId,'Poin laundry']); }
                $db->prepare("INSERT INTO transaction_logs (transaction_id, user_id, action, new_status, notes) VALUES (?,?,'create','queue','Transaksi baru')")->execute([$transactionId,$userId]);
                $db->commit();
                $waSent = false;
                if ($sendWhatsApp) { $stmt = $db->prepare("SELECT * FROM customers WHERE id = ?"); $stmt->execute([$customerId]); $cust = $stmt->fetch();
                    if ($cust && !empty($cust['phone'])) { $stmt = $db->prepare("SELECT * FROM outlets WHERE id = ?"); $stmt->execute([$outletId]); $outlet = $stmt->fetch();
                        $msg = "Halo Kak {$cust['name']}! 👋\n\nPesanan *{$invoiceNumber}* sudah diterima.\n💰 Total: ".formatRupiah($totalPrice)."\n⭐ Poin: +{$pointsEarned}\n🔗 Lacak: {$trackingUrl}\n\nTerima kasih! 🙏";
                        $wa = sendWhatsApp($cust['phone'], $msg); $waSent = $wa['success'];
                        $db->prepare("INSERT INTO whatsapp_logs (transaction_id, customer_id, phone_number, message_type, message_content, status) VALUES (?,?,?,'welcome',?,?)")->execute([$transactionId,$customerId,$cust['phone'],$msg,$waSent?'sent':'failed']);
                    }
                }
                $msg = '✅ Transaksi berhasil! +'.$pointsEarned.' poin'; if($discountAmount>0) $msg.=' (Diskon Rp '.number_format($discountAmount,0,',','.').')'; if($waSent) $msg.=' | WA terkirim';
                redirect("/laundry_lvl1/modules/transactions/detail.php?id=$transactionId", $msg);
            } catch(Exception $e) { $db->rollBack(); $error = 'Gagal: '.$e->getMessage(); }
        }
    }
}
?>

<div class="card" style="max-width:650px;margin:0 auto;">
    <div class="card-header"><div class="card-title">🆕 Transaksi Baru</div><a href="/laundry_lvl1/modules/transactions/" class="btn btn-secondary btn-sm">← Kembali</a></div>
    <div class="card-body">
        <?php if($error): ?><div style="background:#FEE2E2;color:#991B1B;padding:12px 16px;border-radius:8px;margin-bottom:20px;"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-row">
                <div class="form-group"><label class="form-label">👤 Pelanggan</label><select name="customer_id" id="customerSelect" class="form-select" required><option value="">-- Pilih --</option><?php foreach($customers as $c): ?><option value="<?=$c['id']?>" data-points="<?=getCustomerPoints($db,$c['id'])?>"><?=htmlspecialchars($c['name'])?> ⭐<?=getCustomerPoints($db,$c['id'])?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label class="form-label">🧺 Layanan</label><select name="service_id" id="serviceSelect" class="form-select" required><option value="">-- Pilih --</option><?php foreach($services as $s): ?><option value="<?=$s['id']?>" data-price="<?=$s['price_per_kg']?>"><?=htmlspecialchars($s['name'])?> - <?=formatRupiah($s['price_per_kg'])?>/kg</option><?php endforeach; ?></select></div>
            </div>
            <div class="form-group"><label class="form-label">⚖️ Berat (kg)</label><input type="number" name="weight_kg" id="weightInput" class="form-input" placeholder="Berat" step="0.1" min="0.1" required></div>
            <div id="loyaltySection" style="display:none;background:#FEF3C7;border:1px solid #FDE68A;border-radius:8px;padding:16px;margin-bottom:16px;">
                <div style="margin-bottom:8px;">⭐ Poin: <strong id="availablePoints">0</strong> (Rp <span id="pointsValue">0</span>)</div>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;"><input type="checkbox" name="use_loyalty_points" id="useLoyalty" style="width:18px;height:18px;">Tukar poin</label>
                <div id="redeemSection" style="display:none;margin-top:8px;"><input type="number" name="points_to_redeem" id="pointsToRedeem" class="form-input" placeholder="Jumlah poin" min="<?=MIN_POINTS_REDEEM?>"></div>
            </div>
            <div style="background:#F0F9FF;padding:16px;border-radius:8px;margin-bottom:16px;"><div style="display:flex;justify-content:space-between;"><span>💰 Total:</span><span id="totalDisplay" style="font-size:28px;font-weight:700;color:var(--primary);">Rp 0</span></div><div id="discountDisplay" style="display:none;color:#10B981;font-size:13px;"></div><div id="pointsEarnedDisplay" style="color:#F59E0B;font-size:12px;"></div></div>
            <div class="form-group"><label class="form-label">📝 Catatan</label><textarea name="notes" class="form-textarea" rows="2"></textarea></div>
            <div class="form-group"><label style="display:flex;align-items:center;gap:8px;cursor:pointer;"><input type="checkbox" name="send_whatsapp" checked style="width:18px;height:18px;">📱 Kirim WhatsApp</label></div>
            <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">💾 Simpan & Cetak Struk</button>
        </form>
    </div>
</div>

<script>
let originalTotal=0,discountAmount=0;
document.getElementById('customerSelect').addEventListener('change',function(){var p=parseInt(this.selectedOptions[0].dataset.points)||0;var s=document.getElementById('loyaltySection');if(p>0){s.style.display='block';document.getElementById('availablePoints').textContent=p;document.getElementById('pointsValue').textContent=(p*<?=POINTS_VALUE?>).toLocaleString('id-ID');document.getElementById('pointsToRedeem').max=p}else{s.style.display='none';document.getElementById('useLoyalty').checked=false;document.getElementById('redeemSection').style.display='none'}});
document.getElementById('useLoyalty').addEventListener('change',function(){document.getElementById('redeemSection').style.display=this.checked?'block':'none';if(!this.checked){discountAmount=0;document.getElementById('pointsToRedeem').value='';updateDisplay()}});
document.getElementById('pointsToRedeem').addEventListener('input',function(){var p=parseInt(this.value)||0;var max=parseInt(this.max)||0;if(p>max){this.value=max;p=max}discountAmount=p*<?=POINTS_VALUE?>;if(discountAmount>originalTotal)discountAmount=originalTotal;updateDisplay()});
document.getElementById('serviceSelect').addEventListener('change',calcTotal);
document.getElementById('weightInput').addEventListener('input',calcTotal);
function calcTotal(){var price=parseFloat(document.getElementById('serviceSelect').selectedOptions[0].dataset.price)||0;var weight=parseFloat(document.getElementById('weightInput').value)||0;originalTotal=price*weight;document.getElementById('pointsEarnedDisplay').textContent=weight>0?'⭐ Poin: +'+Math.floor(weight*<?=POINTS_PER_KG?>):'';updateDisplay()}
function updateDisplay(){var final=Math.max(0,originalTotal-discountAmount);document.getElementById('totalDisplay').textContent='Rp '+final.toLocaleString('id-ID');var d=document.getElementById('discountDisplay');if(discountAmount>0){d.style.display='block';d.textContent='💚 Diskon: -Rp '+discountAmount.toLocaleString('id-ID')}else{d.style.display='none'}}
</script>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>