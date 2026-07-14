<?php $uri = $_SERVER['REQUEST_URI'] ?? ''; ?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">🧺</div>
        <div class="sidebar-brand"><h2>LaundryKu</h2><span>Level 5</span></div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section">Menu Utama</div>
        <a href="/modules/dashboard/" class="nav-item <?= strpos($uri,'/dashboard')!==false?'active':'' ?>"><span class="nav-icon">📊</span><span>Dashboard</span></a>
        <a href="/modules/transactions/" class="nav-item <?= strpos($uri,'/transactions')!==false?'active':'' ?>"><span class="nav-icon">🧾</span><span>Transaksi</span></a>
        <a href="/modules/customers/" class="nav-item <?= strpos($uri,'/customers')!==false?'active':'' ?>"><span class="nav-icon">👥</span><span>Pelanggan</span></a>
        <div class="nav-section">Fitur Pro</div>
        <a href="/modules/whatsapp/" class="nav-item <?= strpos($uri,'/whatsapp')!==false?'active':'' ?>"><span class="nav-icon">📱</span><span>WhatsApp</span></a>
        <a href="/modules/loyalty/" class="nav-item <?= strpos($uri,'/loyalty')!==false?'active':'' ?>"><span class="nav-icon">⭐</span><span>Loyalti</span></a>
        <div class="nav-section">Sistem</div>
        <a href="/modules/reports/" class="nav-item <?= strpos($uri,'/reports')!==false?'active':'' ?>"><span class="nav-icon">📋</span><span>Laporan</span></a>
        <a href="/modules/settings/" class="nav-item <?= strpos($uri,'/settings')!==false?'active':'' ?>"><span class="nav-icon">⚙️</span><span>Pengaturan</span></a>
    </nav>
    <div class="sidebar-footer">
        <a href="/modules/auth/logout.php" style="display:flex;align-items:center;gap:8px;padding:10px 16px;background:var(--gray-100);border-radius:8px;text-decoration:none;color:var(--gray-700);font-weight:500;width:100%;"><span>🚪</span><span>Keluar</span></a>
    </div>
</aside>