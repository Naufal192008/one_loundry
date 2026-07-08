<?php
// ============================================
// includes/functions.php - Level 1
// Fungsi-fungsi helper
// ============================================

/**
 * Generate nomor invoice unik
 * @return string
 */
function generateInvoiceNumber(): string {
    return 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
}

/**
 * Generate token QR Code
 * @return string
 */
function generateQRToken(): string {
    return bin2hex(random_bytes(32));
}

/**
 * Format rupiah
 * @param float $amount
 * @return string
 */
function formatRupiah(float $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

/**
 * Get service by ID
 * @param PDO $db
 * @param int $serviceId
 * @return array|null
 */
function getService(PDO $db, int $serviceId) {
    $stmt = $db->prepare("SELECT * FROM services WHERE id = :id");
    $stmt->execute([':id' => $serviceId]);
    return $stmt->fetch();
}

/**
 * Get customer by ID
 * @param PDO $db
 * @param int $customerId
 * @return array|null
 */
function getCustomer(PDO $db, int $customerId) {
    $stmt = $db->prepare("SELECT * FROM customers WHERE id = :id");
    $stmt->execute([':id' => $customerId]);
    return $stmt->fetch();
}

/**
 * Hitung harga
 * @param float $pricePerKg
 * @param float $weightKg
 * @return float
 */
function calculatePrice(float $pricePerKg, float $weightKg): float {
    return $pricePerKg * $weightKg;
}

/**
 * Get status label dan warna
 * @param string $status
 * @return array
 */
function getStatusInfo(string $status): array {
    $statusMap = [
        'queue' => ['label' => 'Menunggu', 'color' => '#F59E0B', 'bg' => '#FEF3C7'],
        'washing' => ['label' => 'Sedang Dicuci', 'color' => '#3B82F6', 'bg' => '#DBEAFE'],
        'ironing' => ['label' => 'Sedang Disetrika', 'color' => '#8B5CF6', 'bg' => '#EDE9FE'],
        'ready' => ['label' => 'Siap Diambil', 'color' => '#10B981', 'bg' => '#D1FAE5'],
        'completed' => ['label' => 'Selesai', 'color' => '#6B7280', 'bg' => '#F3F4F6']
    ];
    return $statusMap[$status] ?? ['label' => 'Unknown', 'color' => '#000', 'bg' => '#FFF'];
}

/**
 * Log aktivitas transaksi
 */
function logTransaction(PDO $db, int $transactionId, int $userId, string $action, ?string $oldStatus = null, ?string $newStatus = null, ?string $notes = null): void {
    $stmt = $db->prepare("INSERT INTO transaction_logs (transaction_id, user_id, action, old_status, new_status, notes) VALUES (:tid, :uid, :action, :old_status, :new_status, :notes)");
    $stmt->execute([
        ':tid' => $transactionId,
        ':uid' => $userId,
        ':action' => $action,
        ':old_status' => $oldStatus,
        ':new_status' => $newStatus,
        ':notes' => $notes
    ]);
}

/**
 * Sanitasi input
 */
function sanitize(string $data): string {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Redirect - AMAN untuk semua kondisi
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
 * Tampilkan flash message
 */
function showFlashMessage(): void {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'] ?? 'success';
        $bgColor = $type === 'success' ? '#D1FAE5' : '#FEE2E2';
        $textColor = $type === 'success' ? '#065F46' : '#991B1B';
        $borderColor = $type === 'success' ? '#A7F3D0' : '#FECACA';
        
        echo '
        <div style="background: ' . $bgColor . '; color: ' . $textColor . '; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; border-left: 4px solid ' . $borderColor . ';">
            ' . htmlspecialchars($_SESSION['flash_message']) . '
        </div>';
        
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
    }
}
?>