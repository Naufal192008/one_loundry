<?php
// ============================================
// includes/header.php - LaundryKu Level 5
// Header HTML + Session + Database
// ============================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

/** @var PDO $db */
$database = new Database();
$db = $database->getConnection();
$currentUser = getCurrentUser($db);

// Dynamic page title
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
elseif (strpos($uri, '/drivers') !== false) $pageTitle = 'Driver';
elseif (strpos($uri, '/pickup') !== false) $pageTitle = 'Pickup & Delivery';

$theme = $currentUser['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="id" data-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#6366F1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="LaundryKu">
    <meta name="description" content="LaundryKu - Sistem Manajemen Laundry Terlengkap Level 5">
    <title><?= defined('APP_NAME') ? APP_NAME : 'LaundryKu' ?> - <?= $pageTitle ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/laundry_lvl1/assets/css/style.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <style>
        /* ============================================ */
        /* CSS VARIABLES & THEME */
        /* ============================================ */
        :root {
            --primary: #6366F1;
            --primary-light: #818CF8;
            --primary-dark: #4F46E5;
            --primary-bg: #EEF2FF;
            --success: #10B981;
            --success-bg: #D1FAE5;
            --warning: #F59E0B;
            --warning-bg: #FEF3C7;
            --danger: #EF4444;
            --danger-bg: #FEE2E2;
            --purple: #8B5CF6;
            --purple-bg: #EDE9FE;
            --info: #3B82F6;
            --info-bg: #DBEAFE;
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
            --black: #000000;
            --radius-sm: 8px;
            --radius: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --radius-full: 9999px;
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
            --shadow: 0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.07), 0 2px 4px rgba(0,0,0,0.06);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1), 0 4px 6px rgba(0,0,0,0.05);
            --shadow-xl: 0 20px 25px rgba(0,0,0,0.1), 0 10px 10px rgba(0,0,0,0.04);
            --font: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        
        /* Dark Theme */
        [data-theme="dark"] {
            --primary: #818CF8;
            --primary-light: #A5B4FC;
            --primary-dark: #6366F1;
            --primary-bg: #1E1B4B;
            --success: #34D399;
            --success-bg: #064E3B;
            --warning: #FBBF24;
            --warning-bg: #78350F;
            --danger: #F87171;
            --danger-bg: #7F1D1D;
            --purple: #A78BFA;
            --purple-bg: #4C1D95;
            --info: #60A5FA;
            --info-bg: #1E3A5F;
            --gray-50: #0F172A;
            --gray-100: #1E293B;
            --gray-200: #334155;
            --gray-300: #475569;
            --gray-400: #64748B;
            --gray-500: #94A3B8;
            --gray-600: #CBD5E1;
            --gray-700: #E2E8F0;
            --gray-800: #F1F5F9;
            --gray-900: #F8FAFC;
            --white: #1E293B;
            --black: #FFFFFF;
        }
        
        /* ============================================ */
        /* RESET & BASE */
        /* ============================================ */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html {
            scroll-behavior: smooth;
        }
        
        body {
            font-family: var(--font);
            background: var(--gray-50);
            color: var(--gray-900);
            line-height: 1.6;
            transition: all 0.3s ease;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            min-height: 100vh;
        }
        
        a {
            color: var(--primary);
            text-decoration: none;
            transition: color 0.2s ease;
        }
        
        a:hover {
            color: var(--primary-dark);
        }
        
        /* ============================================ */
        /* LAYOUT */
        /* ============================================ */
        .app-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* ============================================ */
        /* SIDEBAR */
        /* ============================================ */
        .sidebar {
            width: 280px;
            background: var(--white);
            border-right: 1px solid var(--gray-200);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 100;
            box-shadow: var(--shadow-lg);
            transition: all 0.3s ease;
        }
        
        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .sidebar-logo {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: white;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(99,102,241,0.3);
        }
        
        .sidebar-brand h2 {
            font-size: 16px;
            font-weight: 800;
            color: var(--gray-900);
            letter-spacing: -0.5px;
            line-height: 1.2;
        }
        
        .sidebar-brand span {
            font-size: 11px;
            color: var(--primary);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .sidebar-nav {
            flex: 1;
            padding: 16px 12px;
            display: flex;
            flex-direction: column;
            gap: 2px;
            overflow-y: auto;
        }
        
        .nav-section {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--gray-500);
            padding: 16px 16px 4px;
            margin-top: 8px;
        }
        
        .nav-section:first-child {
            margin-top: 0;
            padding-top: 0;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            border-radius: var(--radius-sm);
            text-decoration: none;
            color: var(--gray-700);
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s ease;
            position: relative;
        }
        
        .nav-item:hover {
            background: var(--primary-bg);
            color: var(--primary);
        }
        
        .nav-item.active {
            background: var(--primary-bg);
            color: var(--primary);
            font-weight: 700;
        }
        
        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 24px;
            background: var(--primary);
            border-radius: 0 4px 4px 0;
        }
        
        .nav-icon {
            font-size: 20px;
            width: 24px;
            text-align: center;
            flex-shrink: 0;
        }
        
        .nav-badge {
            margin-left: auto;
            background: var(--danger);
            color: white;
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: 700;
        }
        
        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid var(--gray-200);
        }
        
        .sidebar-footer a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: var(--gray-100);
            border-radius: var(--radius-sm);
            text-decoration: none;
            color: var(--gray-700);
            font-weight: 500;
            width: 100%;
            transition: all 0.2s ease;
        }
        
        .sidebar-footer a:hover {
            background: var(--danger-bg);
            color: var(--danger);
        }
        
        /* ============================================ */
        /* MAIN CONTENT */
        /* ============================================ */
        .main-content {
            flex: 1;
            margin-left: 280px;
            min-height: 100vh;
            background: var(--gray-50);
            transition: margin-left 0.3s ease;
        }
        
        .top-bar {
            background: var(--white);
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--gray-200);
            position: sticky;
            top: 0;
            z-index: 50;
            backdrop-filter: blur(8px);
        }
        
        .top-bar h1 {
            font-size: 22px;
            font-weight: 700;
            color: var(--gray-900);
        }
        
        .top-bar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .theme-btn {
            background: var(--gray-100);
            border: none;
            cursor: pointer;
            font-size: 20px;
            padding: 8px 12px;
            border-radius: var(--radius-sm);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .theme-btn:hover {
            background: var(--primary-bg);
            transform: scale(1.1);
        }
        
        .user-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            background: var(--gray-50);
            border-radius: var(--radius);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 16px;
            flex-shrink: 0;
        }
        
        .user-info {
            line-height: 1.3;
        }
        
        .user-info strong {
            font-size: 14px;
            color: var(--gray-900);
        }
        
        .user-info small {
            font-size: 11px;
            color: var(--primary);
            font-weight: 600;
        }
        
        .content-area {
            padding: 24px 32px;
        }
        
        /* ============================================ */
        /* CARDS */
        /* ============================================ */
        .card {
            background: var(--white);
            border-radius: var(--radius-lg);
            border: 1px solid var(--gray-200);
            box-shadow: var(--shadow);
            margin-bottom: 24px;
            overflow: hidden;
            transition: box-shadow 0.3s ease;
        }
        
        .card:hover {
            box-shadow: var(--shadow-md);
        }
        
        .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--gray-100);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--gray-900);
        }
        
        .card-body {
            padding: 24px;
        }
        
        /* ============================================ */
        /* STATS GRID */
        /* ============================================ */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }
        
        .stat-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 24px;
            border: 1px solid var(--gray-200);
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }
        
        .stat-card.primary::before { background: var(--primary); }
        .stat-card.success::before { background: var(--success); }
        .stat-card.warning::before { background: var(--warning); }
        .stat-card.danger::before { background: var(--danger); }
        .stat-card.purple::before { background: var(--purple); }
        .stat-card.info::before { background: var(--info); }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
        }
        
        .stat-card-inner {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .stat-icon-large {
            font-size: 36px;
            width: 56px;
            height: 56px;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .stat-icon-large.blue { background: var(--primary-bg); }
        .stat-icon-large.green { background: var(--success-bg); }
        .stat-icon-large.yellow { background: var(--warning-bg); }
        .stat-icon-large.red { background: var(--danger-bg); }
        .stat-icon-large.purple { background: var(--purple-bg); }
        
        .stat-label {
            font-size: 13px;
            color: var(--gray-500);
            font-weight: 500;
            margin-bottom: 4px;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 800;
            color: var(--gray-900);
            letter-spacing: -0.5px;
        }
        
        .stat-sub {
            font-size: 12px;
            color: var(--gray-500);
            margin-top: 2px;
        }
        
        /* ============================================ */
        /* BUTTONS */
        /* ============================================ */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.2s ease;
            font-family: var(--font);
            white-space: nowrap;
            line-height: 1;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99,102,241,0.3);
        }
        
        .btn-secondary {
            background: var(--gray-100);
            color: var(--gray-700);
        }
        
        .btn-secondary:hover {
            background: var(--gray-200);
        }
        
        .btn-success {
            background: var(--success);
            color: white;
        }
        
        .btn-success:hover {
            background: #059669;
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background: #DC2626;
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }
        
        .btn-outline:hover {
            background: var(--primary-bg);
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }
        
        .btn-lg {
            padding: 14px 28px;
            font-size: 16px;
        }
        
        .btn-xl {
            padding: 18px 36px;
            font-size: 18px;
        }
        
        .btn-block {
            width: 100%;
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        /* ============================================ */
        /* TABLES */
        /* ============================================ */
        .table-container {
            overflow-x: auto;
            border-radius: var(--radius);
            border: 1px solid var(--gray-200);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--white);
        }
        
        thead {
            background: var(--gray-50);
            border-bottom: 2px solid var(--gray-200);
        }
        
        th {
            padding: 12px 16px;
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        
        td {
            padding: 14px 16px;
            font-size: 14px;
            border-bottom: 1px solid var(--gray-100);
            color: var(--gray-700);
        }
        
        tbody tr {
            transition: background 0.2s ease;
        }
        
        tbody tr:hover {
            background: var(--gray-50);
        }
        
        /* ============================================ */
        /* BADGES */
        /* ============================================ */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 12px;
            border-radius: var(--radius-full);
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }
        
        .badge-success { background: var(--success-bg); color: #065F46; }
        .badge-warning { background: var(--warning-bg); color: #92400E; }
        .badge-danger { background: var(--danger-bg); color: #991B1B; }
        .badge-info { background: var(--info-bg); color: #1E40AF; }
        .badge-purple { background: var(--purple-bg); color: #6D28D9; }
        .badge-gray { background: var(--gray-100); color: var(--gray-600); }
        
        /* ============================================ */
        /* FORMS */
        /* ============================================ */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: var(--gray-700);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-label .required {
            color: var(--danger);
            margin-left: 2px;
        }
        
        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-family: var(--font);
            color: var(--gray-900);
            background: var(--white);
            transition: all 0.2s ease;
        }
        
        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-bg);
        }
        
        .form-input::placeholder {
            color: var(--gray-400);
        }
        
        .form-textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-hint {
            font-size: 12px;
            color: var(--gray-500);
            margin-top: 4px;
        }
        
        /* ============================================ */
        /* QUICK ACTIONS */
        /* ============================================ */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px;
        }
        
        .quick-action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 24px 16px;
            background: var(--white);
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-lg);
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            color: var(--gray-700);
            transition: all 0.2s ease;
            text-align: center;
        }
        
        .quick-action-btn:hover {
            border-color: var(--primary);
            background: var(--primary-bg);
            color: var(--primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .quick-action-btn .icon {
            font-size: 36px;
        }
        
        /* ============================================ */
        /* SEARCH BAR */
        /* ============================================ */
        .search-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            align-items: center;
        }
        
        .search-input {
            flex: 1;
            padding: 10px 14px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-family: var(--font);
            background: var(--white);
            transition: all 0.2s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-bg);
        }
        
        /* ============================================ */
        /* KANBAN BOARD */
        /* ============================================ */
        .kanban-board {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }
        
        .kanban-column {
            background: var(--gray-100);
            border-radius: var(--radius-lg);
            padding: 16px;
            min-height: 120px;
        }
        
        .kanban-column-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--gray-200);
        }
        
        .kanban-column-header h4 {
            font-size: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .kanban-count {
            background: var(--gray-300);
            color: var(--gray-600);
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        /* ============================================ */
        /* MODAL */
        /* ============================================ */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            animation: fadeIn 0.2s ease;
        }
        
        .modal-content {
            background: var(--white);
            border-radius: var(--radius-xl);
            padding: 32px;
            width: 90%;
            max-width: 500px;
            box-shadow: var(--shadow-xl);
            animation: slideUp 0.3s ease;
        }
        
        .modal-content h3 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 16px;
        }
        
        .modal-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 24px;
        }
        
        /* ============================================ */
        /* RESPONSIVE */
        /* ============================================ */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }
        
        @media (max-width: 1024px) {
            .sidebar {
                width: 80px;
            }
            
            .sidebar-brand,
            .nav-item span:not(.nav-icon),
            .nav-section,
            .nav-badge,
            .sidebar-footer a span {
                display: none;
            }
            
            .nav-item {
                justify-content: center;
                padding: 12px;
            }
            
            .main-content {
                margin-left: 80px;
            }
            
            .sidebar-header {
                justify-content: center;
                padding: 16px;
            }
        }
        
        @media (max-width: 768px) {
            .content-area {
                padding: 16px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }
            
            .top-bar {
                padding: 12px 16px;
            }
            
            .top-bar h1 {
                font-size: 18px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .kanban-board {
                grid-template-columns: 1fr;
            }
            
            .quick-actions {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .sidebar {
                width: 0;
                transform: translateX(-100%);
            }
            
            .sidebar.open {
                width: 280px;
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .top-bar h1 {
                font-size: 16px;
            }
            
            .content-area {
                padding: 12px;
            }
            
            .card-body {
                padding: 16px;
            }
        }
        
        /* ============================================ */
        /* ANIMATIONS */
        /* ============================================ */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(100px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        @keyframes slideOutRight {
            from { opacity: 1; transform: translateX(0); }
            to { opacity: 0; transform: translateX(100px); }
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        /* ============================================ */
        /* PRINT STYLES */
        /* ============================================ */
        @media print {
            .sidebar,
            .top-bar,
            .btn,
            .no-print,
            form,
            .modal-overlay,
            .search-bar,
            .stats-grid,
            .quick-actions {
                display: none !important;
            }
            
            .main-content {
                margin-left: 0 !important;
            }
            
            .content-area {
                padding: 0 !important;
            }
            
            .card {
                border: none !important;
                box-shadow: none !important;
                break-inside: avoid;
            }
            
            body {
                background: white !important;
                font-size: 12pt;
            }
            
            @page {
                margin: 1cm;
            }
        }
        
        /* ============================================ */
        /* UTILITIES */
        /* ============================================ */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .text-primary { color: var(--primary); }
        .text-success { color: var(--success); }
        .text-warning { color: var(--warning); }
        .text-danger { color: var(--danger); }
        .text-gray { color: var(--gray-500); }
        .font-bold { font-weight: 700; }
        .font-semibold { font-weight: 600; }
        .text-sm { font-size: 12px; }
        .text-lg { font-size: 18px; }
        .text-xl { font-size: 24px; }
        .mt-1 { margin-top: 4px; }
        .mt-2 { margin-top: 8px; }
        .mt-3 { margin-top: 12px; }
        .mt-4 { margin-top: 16px; }
        .mt-5 { margin-top: 20px; }
        .mt-6 { margin-top: 24px; }
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .mb-3 { margin-bottom: 12px; }
        .mb-4 { margin-bottom: 16px; }
        .mb-5 { margin-bottom: 20px; }
        .mb-6 { margin-bottom: 24px; }
        .p-1 { padding: 4px; }
        .p-2 { padding: 8px; }
        .p-3 { padding: 12px; }
        .p-4 { padding: 16px; }
        .p-5 { padding: 20px; }
        .p-6 { padding: 24px; }
        .flex { display: flex; }
        .flex-col { flex-direction: column; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .justify-center { justify-content: center; }
        .gap-1 { gap: 4px; }
        .gap-2 { gap: 8px; }
        .gap-3 { gap: 12px; }
        .gap-4 { gap: 16px; }
        .gap-5 { gap: 20px; }
        .w-full { width: 100%; }
        .hidden { display: none; }
    </style>
</head>
<body>
    <?php if (isLoggedIn()): ?>
    <div class="app-container">
        <?php include __DIR__ . '/sidebar.php'; ?>
        <main class="main-content">
            <header class="top-bar">
                <h1><?= $pageTitle ?></h1>
                <div class="top-bar-right">
                    <button onclick="toggleTheme()" class="theme-btn" title="Ganti Tema (Dark/Light)">🌓</button>
                    <div class="user-card">
                        <div class="user-avatar"><?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?></div>
                        <div class="user-info">
                            <strong><?= htmlspecialchars($currentUser['name'] ?? 'User') ?></strong><br>
                            <small><?= ucfirst(str_replace('_', ' ', $currentUser['role'] ?? '')) ?></small>
                        </div>
                    </div>
                </div>
            </header>
            <div class="content-area">
                <?php showFlashMessage(); ?>
    <?php endif; ?>