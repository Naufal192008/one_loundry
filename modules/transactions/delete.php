<?php
require_once __DIR__ . '/../../includes/header.php';
requireLogin();
if(!isOwner() && !isSuperAdmin()) redirect('/laundry_lvl1/modules/transactions/','❌ Akses ditolak!','error');
$id = filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
if($id){
    $franchiseId = $_SESSION['franchise_id'] ?? 1;
    try{
        $db->beginTransaction();
        $db->prepare("DELETE FROM transaction_logs WHERE transaction_id = ?")->execute([$id]);
        $db->prepare("DELETE FROM whatsapp_logs WHERE transaction_id = ?")->execute([$id]);
        $db->prepare("DELETE FROM loyalty_points_history WHERE transaction_id = ?")->execute([$id]);
        $db->prepare("DELETE FROM transactions WHERE id = ? AND franchise_id = ?")->execute([$id,$franchiseId]);
        $db->commit();
        redirect('/laundry_lvl1/modules/transactions/','✅ Transaksi dihapus!');
    }catch(Exception $e){ $db->rollBack(); redirect('/laundry_lvl1/modules/transactions/','❌ Gagal: '.$e->getMessage(),'error'); }
}
?>