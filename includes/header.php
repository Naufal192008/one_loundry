<?php
// ============================================
// includes/header.php - Level 1
// Header HTML + Session Start + Auth Check
// ============================================
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$database = new Database();
$db = $database->getConnection();
$currentUser = getCurrentUser($db);

// Tentukan judul halaman berdasarkan URL
$pageTitle = 'Dashboard';
if (strpos($_SERVER['REQUEST_URI'] ?? '', '/transactions') !== false) {
    $pageTitle = 'Transaksi';
} elseif (strpos($_SERVER['REQUEST_URI'] ?? '', '/customers') !== false) {
    $pageTitle = 'Pelanggan';
} elseif (strpos($_SERVER['REQUEST_URI'] ?? '', '/reports') !== false) {
    $pageTitle = 'Laporan';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Laundry - <?= $pageTitle ?></title>
    
    <!-- CSS Utama -->
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #1D4ED8;
            --primary-light: #3B82F6;
            --primary-dark: #1E40AF;
            --primary-bg: #EFF6FF;
            --success: #10B981;
            --success-bg: #D1FAE5;
            --warning: #F59E0B;
            --warning-bg: #FEF3C7;
            --danger: #EF4444;
            --danger-bg: #FEE2E2;
            --purple: #8B5CF6;
            --purple-bg: #EDE9FE;
            --gray-50: #F8FAFC;
            --gray-100: #F1F5F9;
            --gray-200: #E2E8F0;
            --gray-300: #CBD5E1;
            --gray-400: #94A3B8;
            --gray-500: #64748B;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1E293B;
            --gray-900: #0F172A;
            --white: #FFFFFF;
            --radius: 8px;
            --radius-lg: 12px;
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
            --shadow: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.07);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #F8FAFC;
            color: #1E293B;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .app-container { display: flex; min-height: 100vh; }

        .sidebar {
            width: 260px;
            background: white;
            border-right: 1px solid #E2E8F0;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid #F1F5F9;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-header h1 {
            font-size: 18px;
            font-weight: 700;
            color: #0F172A;
            white-space: nowrap;
        }

        .sidebar-nav {
            flex: 1;
            padding: 16px 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            overflow-y: auto;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 8px;
            text-decoration: none;
            color: #475569;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .nav-item:hover {
            background: #F1F5F9;
            color: #0F172A;
        }

        .nav-item.active {
            background: #EFF6FF;
            color: #1D4ED8;
            font-weight: 600;
        }

        .nav-icon {
            font-size: 20px;
            width: 24px;
            text-align: center;
            flex-shrink: 0;
        }

        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid #F1F5F9;
        }

        .btn-logout {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: #F1F5F9;
            border-radius: 8px;
            text-decoration: none;
            color: #475569;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            width: 100%;
            font-family: 'Inter', sans-serif;
        }

        .btn-logout:hover {
            background: #FEE2E2;
            color: #EF4444;
        }

        .main-content {
            flex: 1;
            margin-left: 260px;
            min-height: 100vh;
            background: #F8FAFC;
        }

        .top-bar {
            background: white;
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #E2E8F0;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .top-bar-left h2 {
            font-size: 20px;
            font-weight: 600;
            color: #0F172A;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 500;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background: #1D4ED8;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }

        .user-role {
            font-size: 11px;
            padding: 3px 10px;
            border-radius: 20px;
            font-weight: 600;
        }

        .user-role.owner {
            background: #EDE9FE;
            color: #7C3AED;
        }

        .user-role.cashier {
            background: #EFF6FF;
            color: #1D4ED8;
        }

        .content-area {
            padding: 24px 32px;
        }

        .card {
            background: white;
            border-radius: 12px;
            border: 1px solid #E2E8F0;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }

        .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid #F1F5F9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h3 {
            font-size: 16px;
            font-weight: 600;
            color: #0F172A;
        }

        .card-body {
            padding: 24px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px 24px;
            border: 1px solid #E2E8F0;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .stat-icon.blue { background: #EFF6FF; }
        .stat-icon.green { background: #D1FAE5; }
        .stat-icon.yellow { background: #FEF3C7; }
        .stat-icon.purple { background: #EDE9FE; }

        .stat-info h4 {
            font-size: 13px;
            color: #64748B;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .stat-info .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #0F172A;
        }

        .kanban-board {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .kanban-column {
            background: #F1F5F9;
            border-radius: 12px;
            padding: 16px;
            min-height: 120px;
        }

        .kanban-column-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 2px solid #E2E8F0;
        }

        .kanban-column-header h4 {
            font-size: 14px;
            font-weight: 700;
        }

        .kanban-count {
            background: #CBD5E1;
            color: #475569;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
            font-family: 'Inter', sans-serif;
            white-space: nowrap;
        }

        .btn-primary {
            background: #1D4ED8;
            color: white;
        }

        .btn-primary:hover {
            background: #1E40AF;
            box-shadow: 0 4px 12px rgba(29,78,216,0.3);
        }

        .btn-secondary {
            background: #F1F5F9;
            color: #475569;
        }

        .btn-secondary:hover {
            background: #E2E8F0;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        .btn-lg {
            padding: 14px 28px;
            font-size: 16px;
        }

        .table-container {
            overflow-x: auto;
            border-radius: 8px;
            border: 1px solid #E2E8F0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        thead {
            background: #F8FAFC;
            border-bottom: 2px solid #E2E8F0;
        }

        th {
            padding: 12px 16px;
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        td {
            padding: 14px 16px;
            font-size: 14px;
            border-bottom: 1px solid #F1F5F9;
            color: #334155;
        }

        tbody tr:hover {
            background: #F8FAFC;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
        }

        .quick-action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 20px;
            background: white;
            border: 2px solid #E2E8F0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 600;
            font-size: 14px;
            color: #475569;
            text-decoration: none;
            font-family: 'Inter', sans-serif;
        }

        .quick-action-btn:hover {
            border-color: #3B82F6;
            background: #EFF6FF;
            color: #1D4ED8;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .quick-action-btn .icon {
            font-size: 32px;
        }

        .search-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            align-items: center;
        }

        .search-input {
            flex: 1;
            padding: 10px 14px;
            border: 2px solid #E2E8F0;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
        }

        .search-input:focus {
            outline: none;
            border-color: #3B82F6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #E2E8F0;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            color: #1E293B;
            background: white;
            transition: all 0.2s;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #3B82F6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .invoice-container {
            background: white;
            border-radius: 12px;
            padding: 32px;
            border: 1px solid #E2E8F0;
            max-width: 450px;
            margin: 0 auto;
        }

        .invoice-header {
            text-align: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px dashed #E2E8F0;
        }

        .invoice-header h2 {
            font-size: 20px;
            font-weight: 700;
            color: #0F172A;
        }

        .invoice-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
        }

        .invoice-row.total {
            font-weight: 700;
            font-size: 16px;
            border-top: 2px solid #1E293B;
            padding-top: 12px;
            margin-top: 8px;
        }

        .qr-code-container {
            text-align: center;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #E2E8F0;
        }

        .qr-code-container img {
            width: 160px;
            height: 160px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 72px;
            }
            .sidebar-header h1, .nav-item span:not(.nav-icon), 
            .btn-logout span:last-child {
                display: none;
            }
            .nav-item { justify-content: center; padding: 12px; }
            .main-content { margin-left: 72px; }
            .top-bar { padding: 12px 16px; }
            .content-area { padding: 16px; }
            .form-row { grid-template-columns: 1fr; }
            .kanban-board { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; }
        }

        @media print {
            .sidebar, .top-bar, .btn, .no-print { display: none !important; }
            .main-content { margin-left: 0; }
            .content-area { padding: 0; }
            .card { border: none; box-shadow: none; }
            body { background: white; }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <?php if (isLoggedIn()): ?>
    <div class="app-container">
        <?php include __DIR__ . '/sidebar.php'; ?>
        <main class="main-content">
            <header class="top-bar">
                <div class="top-bar-left">
                    <h2><?= $pageTitle ?></h2>
                </div>
                <div class="top-bar-right">
                    <span class="user-info">
                        <span class="user-avatar"><?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?></span>
                        <span><?= htmlspecialchars($currentUser['name'] ?? 'User') ?></span>
                        <span class="user-role <?= $currentUser['role'] ?? '' ?>"><?= ucfirst($currentUser['role'] ?? '') ?></span>
                    </span>
                </div>
            </header>
            <div class="content-area">
                <?php showFlashMessage(); ?>
    <?php endif; ?>