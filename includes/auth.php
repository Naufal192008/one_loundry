<?php
session_start();

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        if (!headers_sent()) {
            header('Location: /laundry_lvl1/modules/auth/login.php');
            exit;
        }
        echo '<script>window.location.href="/laundry_lvl1/modules/auth/login.php"</script>';
        exit;
    }
}

function getCurrentUser(PDO $db): ?array {
    if (!isLoggedIn()) return null;
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function hasRole(string $role): bool {
    return ($_SESSION['role'] ?? '') === $role;
}

function isSuperAdmin(): bool { return hasRole('super_admin'); }
function isOwner(): bool { return hasRole('owner'); }
function isManager(): bool { return hasRole('manager'); }
function isAdmin(): bool { return hasRole('admin'); }
function isCashier(): bool { return hasRole('cashier'); }

function canAccess(string $module): bool {
    $role = $_SESSION['role'] ?? '';
    $permissions = [
        'super_admin' => '*',
        'owner' => ['dashboard','transactions','customers','services','reports','inventory','drivers','pickup','whatsapp','loyalty','settings','users','api'],
        'manager' => ['dashboard','transactions','customers','services','reports','inventory','drivers','pickup','whatsapp','loyalty'],
        'admin' => ['dashboard','transactions','customers','services','inventory'],
        'cashier' => ['dashboard','transactions','customers'],
    ];
    if ($role === 'super_admin') return true;
    return in_array($module, $permissions[$role] ?? []);
}