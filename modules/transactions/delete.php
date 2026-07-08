<?php
// ============================================
// modules/transactions/delete.php - Level 1
// Hapus Transaksi (Hanya Owner)
// ============================================
require_once __DIR__ . '/../../includes/header.php';
requireLogin();

// Hanya owner yang bisa hapus
if (!isOwner()) {
    redirect('/laundry_lvl1/modules/transactions/', '❌ Hanya owner yang bisa menghapus!', 'error');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    redirect('/laundry_lvl1/modules/transactions/', '❌ ID tidak valid!', 'error');
}

$outletId = $_SESSION['outlet_id'];

try {
    $db->beginTransaction();
    
    // 1. Hapus dulu transaction_logs yang terkait
    $stmt = $db->prepare("DELETE FROM transaction_logs WHERE transaction_id = :id");
    $stmt->execute([':id' => $id]);
    
    // 2. Hapus transaksi utama
    $stmt = $db->prepare("DELETE FROM transactions WHERE id = :id AND outlet_id = :oid");
    $stmt->execute([':id' => $id, ':oid' => $outletId]);
    
    $db->commit();
    
    redirect('/laundry_lvl1/modules/transactions/', '✅ Transaksi berhasil dihapus!');
    
} catch (Exception $e) {
    $db->rollBack();
    redirect('/laundry_lvl1/modules/transactions/', '❌ Gagal menghapus: ' . $e->getMessage(), 'error');
}
?>