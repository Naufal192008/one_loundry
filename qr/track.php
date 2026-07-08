<?php
require_once __DIR__ . '/../config/database.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    die('Token tidak valid');
}

$database = new Database();
$db = $database->getConnection();

$stmt = $db->prepare("SELECT t.*, c.name as customer_name, s.name as service_name, o.name as outlet_name 
    FROM transactions t 
    JOIN customers c ON t.customer_id = c.id 
    JOIN services s ON t.service_id = s.id 
    JOIN outlets o ON t.outlet_id = o.id 
    WHERE t.qr_code_token = :token");
$stmt->execute([':token' => $token]);
$trx = $stmt->fetch();

if (!$trx) {
    die('Transaksi tidak ditemukan');
}

$statusInfo = [
    'queue' => ['label' => 'Menunggu', 'emoji' => '🟡', 'color' => '#F59E0B'],
    'washing' => ['label' => 'Sedang Dicuci', 'emoji' => '🔵', 'color' => '#3B82F6'],
    'ironing' => ['label' => 'Sedang Disetrika', 'emoji' => '🟣', 'color' => '#8B5CF6'],
    'ready' => ['label' => 'Siap Diambil', 'emoji' => '🟢', 'color' => '#10B981'],
    'completed' => ['label' => 'Selesai', 'emoji' => '✅', 'color' => '#6B7280']
];

$currentStatus = $statusInfo[$trx['laundry_status']] ?? $statusInfo['queue'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lacak Cucian - <?= htmlspecialchars($trx['invoice_number']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #EFF6FF 0%, #F8FAFC 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .tracker-card {
            background: white;
            border-radius: 20px;
            padding: 32px;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            border: 1px solid #E2E8F0;
        }
        .tracker-header {
            text-align: center;
            margin-bottom: 24px;
        }
        .tracker-header .emoji {
            font-size: 64px;
            margin-bottom: 8px;
        }
        .tracker-header h2 {
            font-size: 20px;
            font-weight: 700;
            color: #1E293B;
        }
        .tracker-header .invoice {
            font-size: 13px;
            color: #64748B;
        }
        .status-section {
            background: #F8FAFC;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .status-emoji {
            font-size: 48px;
        }
        .status-label {
            font-size: 18px;
            font-weight: 700;
            margin-top: 8px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #F1F5F9;
            font-size: 14px;
        }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #64748B; }
        .info-value { font-weight: 600; color: #1E293B; }
        .outlet-info {
            text-align: center;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 2px dashed #E2E8F0;
            font-size: 12px;
            color: #94A3B8;
        }
    </style>
</head>
<body>
    <div class="tracker-card">
        <div class="tracker-header">
            <div class="emoji">🧺</div>
            <h2><?= htmlspecialchars($trx['outlet_name']) ?></h2>
            <p class="invoice"><?= htmlspecialchars($trx['invoice_number']) ?></p>
        </div>
        
        <div class="status-section">
            <div class="status-emoji"><?= $currentStatus['emoji'] ?></div>
            <div class="status-label" style="color: <?= $currentStatus['color'] ?>;">
                <?= $currentStatus['label'] ?>
            </div>
        </div>
        
        <div class="info-row">
            <span class="info-label">Pelanggan</span>
            <span class="info-value"><?= htmlspecialchars($trx['customer_name']) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Layanan</span>
            <span class="info-value"><?= htmlspecialchars($trx['service_name']) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Berat</span>
            <span class="info-value"><?= $trx['weight_kg'] ?> kg</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total</span>
            <span class="info-value">Rp <?= number_format($trx['total_price'], 0, ',', '.') ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Status Bayar</span>
            <span class="info-value"><?= $trx['payment_status'] === 'paid' ? '✅ Lunas' : '❌ Belum' ?></span>
        </div>
        
        <div class="outlet-info">
            🕐 Dibuat: <?= date('d M Y H:i', strtotime($trx['created_at'])) ?>
        </div>
    </div>
</body>
</html>