<?php
// ============================================
// includes/header.php
// ============================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

/** @var PDO $db */
$database = new Database();
$db = $database->getConnection();
$currentUser = getCurrentUser($db);

$pageTitle = 'Dashboard';
$uri = $_SERVER['REQUEST_URI'] ?? '';
if (strpos($uri, '/transactions') !== false) $pageTitle = 'Transaksi';
elseif (strpos($uri, '/customers') !== false) $pageTitle = 'Pelanggan';
elseif (strpos($uri, '/whatsapp') !== false) $pageTitle = 'WhatsApp';
elseif (strpos($uri, '/loyalty') !== false) $pageTitle = 'Loyalti';
elseif (strpos($uri, '/reports') !== false) $pageTitle = 'Laporan';
elseif (strpos($uri, '/inventory') !== false) $pageTitle = 'Inventaris';
elseif (strpos($uri, '/chatbot') !== false) $pageTitle = 'AI Chatbot';
elseif (strpos($uri, '/analytics') !== false) $pageTitle = 'Analitik';
elseif (strpos($uri, '/settings') !== false) $pageTitle = 'Pengaturan';

$theme = $currentUser['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="id" data-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#6366F1">
    <title><?= APP_NAME ?> - <?= $pageTitle ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366F1; --primary-light: #818CF8; --primary-dark: #4F46E5; --primary-bg: #EEF2FF;
            --success: #10B981; --warning: #F59E0B; --danger: #EF4444;
            --gray-50: #F8FAFC; --gray-100: #F1F5F9; --gray-200: #E2E8F0; --gray-500: #64748B; --gray-700: #334155; --gray-900: #0F172A;
            --white: #FFFFFF; --radius: 12px; --radius-sm: 8px; --radius-lg: 16px;
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.05); --shadow: 0 1px 3px rgba(0,0,0,0.1); --shadow-md: 0 4px 6px rgba(0,0,0,0.07); --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
            --font: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        [data-theme="dark"] {
            --primary: #818CF8; --primary-bg: #1E1B4B;
            --gray-50: #1E293B; --gray-100: #334155; --gray-200: #475569; --gray-500: #94A3B8; --gray-700: #CBD5E1; --gray-900: #F8FAFC;
            --white: #0F172A;
        }
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:var(--font);background:var(--gray-50);color:var(--gray-900);line-height:1.6;transition:all 0.3s}
        .app-container{display:flex;min-height:100vh}
        .sidebar{width:280px;background:var(--white);border-right:1px solid var(--gray-200);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:100;box-shadow:var(--shadow-lg)}
        .sidebar-header{padding:24px 20px;border-bottom:1px solid var(--gray-200);display:flex;align-items:center;gap:12px}
        .sidebar-logo{width:42px;height:42px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));border-radius:var(--radius);display:flex;align-items:center;justify-content:center;font-size:22px;color:white}
        .sidebar-brand h2{font-size:16px;font-weight:800;color:var(--gray-900)}
        .sidebar-brand span{font-size:11px;color:var(--primary);font-weight:600;text-transform:uppercase}
        .sidebar-nav{flex:1;padding:16px 12px;display:flex;flex-direction:column;gap:2px;overflow-y:auto}
        .nav-section{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--gray-500);padding:12px 16px 4px;margin-top:8px}
        .nav-item{display:flex;align-items:center;gap:12px;padding:10px 16px;border-radius:var(--radius-sm);text-decoration:none;color:var(--gray-700);font-weight:500;font-size:14px;transition:all 0.2s;position:relative}
        .nav-item:hover{background:var(--primary-bg);color:var(--primary)}
        .nav-item.active{background:var(--primary-bg);color:var(--primary);font-weight:700}
        .nav-item.active::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:20px;background:var(--primary);border-radius:0 4px 4px 0}
        .nav-icon{font-size:20px;width:24px;text-align:center}
        .sidebar-footer{padding:16px;border-top:1px solid var(--gray-200)}
        .main-content{flex:1;margin-left:280px;min-height:100vh;background:var(--gray-50)}
        .top-bar{background:var(--white);padding:16px 32px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--gray-200);position:sticky;top:0;z-index:50}
        .top-bar h1{font-size:22px;font-weight:700}
        .content-area{padding:24px 32px}
        .card{background:var(--white);border-radius:var(--radius-lg);border:1px solid var(--gray-200);box-shadow:var(--shadow);margin-bottom:24px;overflow:hidden}
        .card-header{padding:20px 24px;border-bottom:1px solid var(--gray-100);display:flex;justify-content:space-between;align-items:center}
        .card-title{font-size:18px;font-weight:700}
        .card-body{padding:24px}
        .stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:20px;margin-bottom:24px}
        .stat-card{background:var(--white);border-radius:var(--radius-lg);padding:24px;border:1px solid var(--gray-200);box-shadow:var(--shadow);transition:all 0.3s;cursor:pointer;position:relative;overflow:hidden}
        .stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px}
        .stat-card.primary::before{background:var(--primary)}.stat-card.success::before{background:var(--success)}
        .stat-card.warning::before{background:var(--warning)}.stat-card.danger::before{background:var(--danger)}
        .stat-card:hover{transform:translateY(-4px);box-shadow:var(--shadow-lg)}
        .btn{display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:var(--radius-sm);font-weight:600;font-size:14px;cursor:pointer;border:none;text-decoration:none;transition:all 0.2s;font-family:var(--font)}
        .btn-primary{background:var(--primary);color:white}.btn-primary:hover{background:var(--primary-dark)}
        .btn-secondary{background:var(--gray-100);color:var(--gray-700)}.btn-danger{background:var(--danger);color:white}
        .btn-sm{padding:6px 12px;font-size:12px}.btn-lg{padding:14px 28px;font-size:16px}
        .badge{display:inline-flex;align-items:center;gap:4px;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600}
        .badge-success{background:#D1FAE5;color:#065F46}.badge-warning{background:#FEF3C7;color:#92400E}
        .badge-danger{background:#FEE2E2;color:#991B1B}.badge-info{background:#DBEAFE;color:#1E40AF}
        .table-container{overflow-x:auto;border-radius:var(--radius);border:1px solid var(--gray-200)}
        table{width:100%;border-collapse:collapse;background:var(--white)}
        thead{background:var(--gray-50);border-bottom:2px solid var(--gray-200)}
        th{padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--gray-500);text-transform:uppercase;white-space:nowrap}
        td{padding:14px 16px;font-size:14px;border-bottom:1px solid var(--gray-100);color:var(--gray-700)}
        tbody tr:hover{background:var(--gray-50)}
        .form-group{margin-bottom:20px}
        .form-label{display:block;font-size:13px;font-weight:700;color:var(--gray-700);margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px}
        .form-input,.form-select,.form-textarea{width:100%;padding:10px 14px;border:2px solid var(--gray-200);border-radius:var(--radius-sm);font-size:14px;font-family:var(--font);color:var(--gray-900);background:var(--white);transition:all 0.2s}
        .form-input:focus,.form-select:focus,.form-textarea:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-bg)}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:20px}
        .quick-actions{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px}
        .quick-action-btn{display:flex;flex-direction:column;align-items:center;gap:8px;padding:20px;background:var(--white);border:2px solid var(--gray-200);border-radius:var(--radius-lg);cursor:pointer;text-decoration:none;font-weight:600;font-size:14px;color:var(--gray-700);transition:all 0.2s}
        .quick-action-btn:hover{border-color:var(--primary);background:var(--primary-bg);color:var(--primary);transform:translateY(-2px)}.quick-action-btn .icon{font-size:32px}
        @media(max-width:1024px){.sidebar{width:80px}.sidebar-brand,.nav-item span:not(.nav-icon),.nav-section{display:none}.nav-item{justify-content:center;padding:12px}.main-content{margin-left:80px}}
        @media(max-width:768px){.content-area{padding:16px}.stats-grid{grid-template-columns:1fr 1fr}.top-bar{padding:12px 16px}}
        @media(max-width:480px){.stats-grid{grid-template-columns:1fr}.sidebar{width:0;transform:translateX(-100%)}.sidebar.open{width:280px;transform:translateX(0)}.main-content{margin-left:0}}
        @keyframes slideDown{from{opacity:0;transform:translateY(-10px)}to{opacity:1;transform:translateY(0)}}
    </style>
</head>
<body>
    <?php if (isLoggedIn()): ?>
    <div class="app-container">
        <?php include __DIR__ . '/sidebar.php'; ?>
        <main class="main-content">
            <header class="top-bar">
                <h1><?= $pageTitle ?></h1>
                <div style="display:flex;align-items:center;gap:16px;">
                    <button onclick="toggleTheme()" style="background:none;border:none;cursor:pointer;font-size:20px;">🌓</button>
                    <div style="display:flex;align-items:center;gap:12px;padding:8px 16px;background:var(--gray-50);border-radius:var(--radius);">
                        <div style="width:40px;height:40px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:700;"><?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?></div>
                        <div><strong><?= htmlspecialchars($currentUser['name'] ?? 'User') ?></strong><br><small style="color:var(--primary);"><?= ucfirst(str_replace('_', ' ', $currentUser['role'] ?? '')) ?></small></div>
                    </div>
                </div>
            </header>
            <div class="content-area">
                <?php showFlashMessage(); ?>
    <?php endif; ?>