<?php
// ============================================
// includes/auth.php - Level 1
// Fungsi autentikasi & otorisasi
// ============================================

/**
 * Cek apakah user sudah login
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Require login, redirect jika belum
 * @return void
 */
function requireLogin() {
    if (!isLoggedIn()) {
        if (!headers_sent()) {
            header('Location: /modules/auth/login.php');
            exit();
        } else {
            echo '<script>window.location.href="/modules/auth/login.php";</script>';
            exit();
        }
    }
}

/**
 * Get current user data
 * @param PDO $db
 * @return array|null
 */
function getCurrentUser(PDO $db) {
    if (!isLoggedIn()) return null;
    
    $stmt = $db->prepare("SELECT * FROM users WHERE id = :id AND is_active = 1");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Check user role
 * @param string $role
 * @return bool
 */
function hasRole(string $role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Check if owner
 * @return bool
 */
function isOwner() {
    return hasRole('owner');
}

/**
 * Check if cashier
 * @return bool
 */
function isCashier() {
    return hasRole('cashier');
}

/**
 * Verify cashier PIN
 * @param PDO $db
 * @param int $userId
 * @param string $pin
 * @return bool
 */
function checkPIN(PDO $db, int $userId, string $pin) {
    $stmt = $db->prepare("SELECT id FROM users WHERE id = :id AND pin = :pin AND role = 'cashier'");
    $stmt->execute([':id' => $userId, ':pin' => $pin]);
    return $stmt->fetch() ? true : false;
}
?>