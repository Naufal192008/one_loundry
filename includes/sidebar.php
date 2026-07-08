<?php
// ============================================
// includes/sidebar.php - Level 1
// Navigasi sidebar
// ============================================
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <h1>🧺 Smart Laundry</h1>
        <span class="badge">Level 1</span>
    </div>
    <nav class="sidebar-nav">
        <a href="/laundry_lvl1/modules/dashboard/" class="nav-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/dashboard') !== false ? 'active' : '' ?>">
            <span class="nav-icon">📊</span>
            <span>Dashboard</span>
        </a>
        
        <a href="/laundry_lvl1/modules/transactions/" class="nav-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/transactions') !== false ? 'active' : '' ?>">
            <span class="nav-icon">🧾</span>
            <span>Transaksi</span>
        </a>
        
        <a href="/laundry_lvl1/modules/customers/" class="nav-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/customers') !== false ? 'active' : '' ?>">
            <span class="nav-icon">👥</span>
            <span>Pelanggan</span>
        </a>
        
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'owner'): ?>
        <a href="/laundry_lvl1/modules/reports/" class="nav-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/reports') !== false ? 'active' : '' ?>">
            <span class="nav-icon">📈</span>
            <span>Laporan</span>
        </a>
        <?php endif; ?>
    </nav>
    <div class="sidebar-footer">
        <a href="/laundry_lvl1/modules/auth/logout.php" class="btn-logout">
            <span>🚪</span> Keluar
        </a>
    </div>
</aside>