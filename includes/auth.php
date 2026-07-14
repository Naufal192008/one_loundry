<?php
// ============================================
// includes/auth.php
// ============================================

// Session hanya dimulai jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        $loginUrl = '/modules/auth/login.php';
        if (!headers_sent()) {
            header('Location: ' . $loginUrl);
            exit();
        } else {
            echo '<script>window.location.href="' . $loginUrl . '";</script>';
            exit();
        }
    }
}

function getCurrentUser(PDO $db): ?array {
    if (!isLoggedIn()) return null;
    
    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch() ?: null;
    } catch (Exception $e) {
        return null;
    }
}

function hasRole(string $role): bool {
    return ($_SESSION['role'] ?? '') === $role;
}

function isSuperAdmin(): bool { return hasRole('super_admin'); }
function isOwner(): bool { return hasRole('owner') || hasRole('franchise_owner'); }
function isManager(): bool { return hasRole('manager'); }
function isAdmin(): bool { return hasRole('admin'); }
function isCashier(): bool { return hasRole('cashier'); }

function canAccess(string $module): bool {
    $role = $_SESSION['role'] ?? '';
    
    if ($role === 'super_admin') return true;
    
    $permissions = [
        'owner' => ['dashboard','transactions','customers','services','reports','inventory','drivers','pickup','whatsapp','loyalty','settings','users'],
        'franchise_owner' => ['dashboard','transactions','customers','services','reports','inventory','drivers','pickup','whatsapp','loyalty','settings','users'],
        'manager' => ['dashboard','transactions','customers','services','reports','inventory','drivers','pickup','whatsapp','loyalty'],
        'admin' => ['dashboard','transactions','customers','services','inventory'],
        'cashier' => ['dashboard','transactions','customers'],
    ];
    
    return in_array($module, $permissions[$role] ?? []);
}
?>