<?php
// ============================================
// includes/functions.php
// ============================================

/**
 * @param float $amount
 * @return string
 */
function formatRupiah(float $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

/**
 * @return string
 */
function generateInvoiceNumber(): string {
    return 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

/**
 * @return string
 */
function generateQRToken(): string {
    return bin2hex(random_bytes(32));
}

/**
 * @param string $status
 * @return array
 */
function getStatusInfo(string $status): array {
    $map = [
        'queue' => ['label' => 'Menunggu', 'color' => '#F59E0B', 'bg' => '#FEF3C7', 'icon' => '📋'],
        'washing' => ['label' => 'Sedang Dicuci', 'color' => '#3B82F6', 'bg' => '#DBEAFE', 'icon' => '🫧'],
        'ironing' => ['label' => 'Sedang Disetrika', 'color' => '#8B5CF6', 'bg' => '#EDE9FE', 'icon' => '👕'],
        'ready' => ['label' => 'Siap Diambil', 'color' => '#10B981', 'bg' => '#D1FAE5', 'icon' => '✨'],
        'completed' => ['label' => 'Selesai', 'color' => '#6B7280', 'bg' => '#F3F4F6', 'icon' => '✅'],
        'cancelled' => ['label' => 'Dibatalkan', 'color' => '#EF4444', 'bg' => '#FEE2E2', 'icon' => '❌'],
    ];
    return $map[$status] ?? ['label' => 'Unknown', 'color' => '#000', 'bg' => '#FFF', 'icon' => '❓'];
}

/**
 * @param string $url
 * @param string|null $message
 * @param string $type
 * @return void
 */
function redirect(string $url, ?string $message = null, string $type = 'success'): void {
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    if (!headers_sent()) {
        header("Location: $url");
        exit();
    } else {
        echo '<script>window.location.href="' . addslashes($url) . '";</script>';
        exit();
    }
}

/**
 * @return void
 */
function showFlashMessage(): void {
    if (!isset($_SESSION['flash_message'])) return;
    $type = $_SESSION['flash_type'] ?? 'success';
    $bg = $type === 'success' ? '#D1FAE5' : '#FEE2E2';
    $color = $type === 'success' ? '#065F46' : '#991B1B';
    $icon = $type === 'success' ? '✅' : '❌';
    echo '<div style="background:' . $bg . ';color:' . $color . ';padding:12px 16px;border-radius:8px;margin-bottom:16px;border-left:4px solid ' . $color . ';">' . $icon . ' ' . htmlspecialchars($_SESSION['flash_message']) . '</div>';
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
}

/**
 * @param string $data
 * @return string
 */
function sanitize(string $data): string {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * @param float $weightKg
 * @return int
 */
function calculateLoyaltyPoints(float $weightKg): int {
    return floor($weightKg * POINTS_PER_KG);
}

/**
 * @param int $points
 * @return int
 */
function pointsToRupiah(int $points): int {
    return $points * POINTS_VALUE;
}

/**
 * @param PDO $db
 * @param int $customerId
 * @return int
 */
function getCustomerPoints(PDO $db, int $customerId): int {
    try {
        $stmt = $db->prepare("SELECT loyalty_points FROM customers WHERE id = ?");
        $stmt->execute([$customerId]);
        return (int)($stmt->fetch()['loyalty_points'] ?? 0);
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * @param string $phone
 * @return string
 */
function formatPhoneWA(string $phone): string {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (substr($phone, 0, 1) === '0') $phone = '62' . substr($phone, 1);
    if (substr($phone, 0, 2) !== '62') $phone = '62' . $phone;
    return $phone;
}

/**
 * @param string $phone
 * @param string $message
 * @return array
 */
function sendWhatsApp(string $phone, string $message): array {
    if (empty(WHATSAPP_TOKEN)) {
        return ['success' => false, 'error' => 'WhatsApp token not configured'];
    }
    
    $phone = formatPhoneWA($phone);
    
    $ch = curl_init(WHATSAPP_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => ['target' => $phone, 'message' => $message, 'countryCode' => '62'],
        CURLOPT_HTTPHEADER => ['Authorization: ' . WHATSAPP_TOKEN],
        CURLOPT_TIMEOUT => 15,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Tutup curl (pakai try-catch untuk PHP 8.5 compatibility)
    if (function_exists('curl_close')) {
        curl_close($ch);
    }
    
    return ['success' => $httpCode === 200, 'http_code' => $httpCode, 'response' => $response];
}

/**
 * @param string $token
 * @return string
 */
function getTrackingUrl(string $token): string {
    $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $protocol . $host . '/qr/track.php?token=' . $token;
}

/**
 * @param int $code
 * @param mixed $data
 * @return void
 */
function apiResponse(int $code, mixed $data): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
}
?>