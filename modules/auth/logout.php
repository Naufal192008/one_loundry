<?php
// ============================================
// modules/auth/logout.php - Level 1
// Logout & hapus session
// ============================================
session_start();
session_destroy();
header('Location: /laundry_lvl1/modules/auth/login.php');
exit();
?>