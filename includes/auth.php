<?php
// ============================================
// includes/auth.php
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * @return bool
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * @return void
 */
function requireLogin(): void {
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
 * @param PDO $db
 * @return array|null
 */
function getCurrentUser(PDO $db): ?array {
    if (!isLoggedIn()) return null;
    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch() ?: null;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * @param string $role
 * @return bool
 */
function hasRole(string $role): bool {
    return ($_SESSION['role'] ?? '') === $role;
}

/**
 * @return bool
 */
function isSuperAdmin(): bool {
    return hasRole('super_admin');
}

/**
 * @return bool
 */
function isOwner(): bool {
    return hasRole('owner') || hasRole('franchise_owner') || isSuperAdmin();
}

/**
 * @return bool
 */
function isManager(): bool {
    return hasRole('manager');
}

/**
 * @return bool
 */
function isAdmin(): bool {
    return hasRole('admin');
}

/**
 * @return bool
 */
function isCashier(): bool {
    return hasRole('cashier');
}

/**
 * @param string $module
 * @return bool
 */
function canAccess(string $module): bool {
    if (isSuperAdmin()) return true;
    
    $permissions = [
        'owner' => ['dashboard','transactions','customers','services','reports','inventory','drivers','pickup','whatsapp','loyalty','settings','users'],
        'franchise_owner' => ['dashboard','transactions','customers','services','reports','inventory','drivers','pickup','whatsapp','loyalty','settings','users'],
        'manager' => ['dashboard','transactions','customers','services','reports','inventory','drivers','pickup','whatsapp','loyalty'],
        'admin' => ['dashboard','transactions','customers','services','inventory'],
        'cashier' => ['dashboard','transactions','customers'],
    ];
    
    return in_array($module, $permissions[$_SESSION['role'] ?? ''] ?? []);
}
?>