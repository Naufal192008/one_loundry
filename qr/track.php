<?php
// ============================================
// qr/track.php
// Tracking Cucian untuk Pelanggan
// ============================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

$token = $_GET['token'] ?? '';
if (empty($token)) die('Token tidak valid');

/** @var PDO $db */
$database = new Database();
$db = $database->getConnection();

$stmt = $db->prepare("SELECT t.*, c.name as customer_name, s.name as service_name, o.name as outlet_name, o.address as outlet_address, o.phone as outlet_phone 
    FROM transactions t 
    JOIN customers c ON t.customer_id = c.id 
    JOIN services s ON t.service_id = s.id 
    JOIN outlets o ON t.outlet_id = o.id 
    WHERE t.qr_code_token = :token");
$stmt->execute([':token' => $token]);
$trx = $stmt->fetch();

if (!$trx) die('Transaksi tidak ditemukan');

$statusInfo = [
    'queue' => ['label' => 'Menunggu Antrean', 'emoji' => '🟡', 'color' => '#F59E0B'],
    'washing' => ['label' => 'Sedang Dicuci', 'emoji' => '🔵', 'color' => '#3B82F6'],
    'ironing' => ['label' => 'Sedang Disetrika', 'emoji' => '🟣', 'color' => '#8B5CF6'],
    'ready' => ['label' => 'Siap Diambil', 'emoji' => '🟢', 'color' => '#10B981'],
    'completed' => ['label' => 'Sudah Diambil', 'emoji' => '✅', 'color' => '#6B7280']
];

$current = $statusInfo[$trx['laundry_status']] ?? $statusInfo['queue'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lacak Cucian - <?= htmlspecialchars($trx['invoice_number']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, <?= $current['color'] ?>20, #F8FAFC);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .wrapper { width: 100%; max-width: 440px; }
        .card {
            background: white;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            border: 1px solid #E2E8F0;
            margin-bottom: 16px;
        }
        .brand {
            font-size: 22px;
            font-weight: 800;
            color: #6366F1;
            text-align: center;
            margin-bottom: 4px;
        }
        .invoice {
            font-size: 14px;
            color: #6366F1;
            font-weight: 600;
            background: #EEF2FF;
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            text-align: center;
            width: 100%;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 16px;
        }
        .item {
            background: #F8FAFC;
            padding: 12px 16px;
            border-radius: 12px;
            border: 1px solid #E2E8F0;
        }
        .item .lbl {
            font-size: 11px;
            color: #94A3B8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .item .val {
            font-size: 15px;
            font-weight: 700;
            color: #1E293B;
        }
        .item.full { grid-column: 1 / -1; }
        .banner {
            background: <?= $current['color'] ?>20;
            border: 1px solid <?= $current['color'] ?>30;
            border-radius: 16px;
            padding: 16px;
            text-align: center;
            margin-top: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .banner .icon { font-size: 32px; }
        .banner .text { font-size: 16px; font-weight: 700; color: <?= $current['color'] ?>; }
        .btns { display: flex; gap: 10px; margin-top: 12px; }
        .btn {
            flex: 1;
            padding: 14px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            border: none;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .btn-wa { background: #25D366; color: white; }
        .btn-outline { background: white; color: #6366F1; border: 2px solid #6366F1; }
        .info {
            background: white;
            border-radius: 16px;
            padding: 16px;
            text-align: center;
            margin-top: 12px;
            font-size: 13px;
            color: #64748B;
            box-shadow: 0 4px 16px rgba(0,0,0,0.05);
        }
        @media (max-width: 400px) {
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="brand"><?= htmlspecialchars($trx['outlet_name']) ?></div>
            <div class="invoice"><?= htmlspecialchars($trx['invoice_number']) ?></div>
            
            <div class="grid">
                <div class="item">
                    <div class="lbl">👤 Pelanggan</div>
                    <div class="val"><?= htmlspecialchars($trx['customer_name']) ?></div>
                </div>
                <div class="item">
                    <div class="lbl">🧺 Layanan</div>
                    <div class="val"><?= htmlspecialchars($trx['service_name']) ?></div>
                </div>
                <div class="item">
                    <div class="lbl">⚖️ Berat</div>
                    <div class="val"><?= $trx['weight_kg'] ?> kg</div>
                </div>
                <div class="item">
                    <div class="lbl">💵 Total</div>
                    <div class="val">Rp <?= number_format($trx['total_price'], 0, ',', '.') ?></div>
                </div>
                <div class="item full">
                    <div class="lbl">💳 Status Pembayaran</div>
                    <div class="val"><?= $trx['payment_status'] === 'paid' ? '✅ Lunas' : '❌ Belum Lunas' ?></div>
                </div>
            </div>
            
            <div class="banner">
                <span class="icon"><?= $current['emoji'] ?></span>
                <span class="text"><?= $current['label'] ?></span>
            </div>
        </div>
        
        <div class="btns">
            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $trx['outlet_phone']) ?>" target="_blank" class="btn btn-wa">💬 Hubungi</a>
            <button onclick="location.reload()" class="btn btn-outline">🔄 Refresh</button>
        </div>
        
        <div class="info">
            📍 <?= htmlspecialchars($trx['outlet_address']) ?><br>
            📞 <?= htmlspecialchars($trx['outlet_phone']) ?><br>
            🕐 <?= date('d M Y H:i', strtotime($trx['created_at'])) ?>
        </div>
    </div>
</body>
</html>