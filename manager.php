<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "manager") {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard - TailorSuite</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --sidebar-bg: #2c3e50;
            --sidebar-text: #ecf0f1;
            --sidebar-active: #3498db;
            --header-bg: #ffffff;
            --card-bg: #ffffff;
            --text-primary: #2c3e50;
            --text-secondary: #7f8c8d;
            --border-color: #e0e0e0;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #17a2b8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            display: flex;
            background-color: #f5f7fa;
            color: var(--text-primary);
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background-color: var(--sidebar-bg);
            color: var(--sidebar-text);
            height: 100vh;
            position: fixed;
            padding: 20px 0;
            overflow-y: auto;
        }

        .logo {
            padding: 0 20px 20px;
            font-size: 24px;
            font-weight: bold;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .logo i {
            margin-right: 10px;
            color: var(--sidebar-active);
        }

        .nav-menu {
            list-style: none;
            padding: 0 20px;
        }

        .nav-menu-title {
            color: rgba(255, 255, 255, 0.7);
            font-size: 12px;
            text-transform: uppercase;
            margin: 15px 0 5px 0;
            padding-left: 10px;
        }

        .nav-item {
            padding: 10px 15px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s;
            border-radius: 4px;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .nav-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-item.active {
            background-color: var(--sidebar-active);
        }

        .nav-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .notification-badge {
            background-color: var(--danger-color);
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 10px;
            margin-left: auto;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: var(--header-bg);
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 24px;
            color: var(--text-primary);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .notification-icon {
            position: relative;
            cursor: pointer;
        }

        .notification-bell {
            font-size: 20px;
            color: var(--text-secondary);
        }

        .notification-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--danger-color);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
        }

        .user-info {
            display: flex;
            align-items: center;
            position: relative;
            cursor: pointer;
        }

        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: white;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 10px 0;
            min-width: 150px;
            z-index: 100;
            display: none;
        }

        .dropdown-menu a {
            display: block;
            padding: 8px 15px;
            color: var(--text-primary);
            text-decoration: none;
            transition: background-color 0.2s;
        }

        .dropdown-menu a:hover {
            background-color: #f8f9fa;
        }

        .user-info:hover .dropdown-menu {
            display: block;
        }

        /* Dashboard Stats */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        @media (max-width: 1200px) {
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
        }

        .stat-card {
            background-color: var(--card-bg);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 20px;
        }

        .stat-icon.pending {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--warning-color);
        }

        .stat-icon.progress {
            background-color: rgba(52, 152, 219, 0.1);
            color: var(--sidebar-active);
        }

        .stat-icon.tailors {
            background-color: rgba(155, 89, 182, 0.1);
            color: #9b59b6;
        }

        .stat-icon.inventory {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        .stat-info {
            flex: 1;
        }

        .stat-title {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 5px;
        }

        .stat-value {
            font-size: 22px;
            font-weight: bold;
            color: var(--text-primary);
        }

        .stat-change {
            font-size: 12px;
            margin-top: 3px;
        }

        .stat-change.positive {
            color: var(--success-color);
        }

        .stat-change.negative {
            color: var(--danger-color);
        }

        /* Dashboard Sections */
        .dashboard-sections {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        @media (min-width: 992px) {
            .dashboard-sections {
                grid-template-columns: 1fr 1fr;
            }
        }

        .section {
            background-color: var(--card-bg);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }

        .section h2 {
            font-size: 18px;
            display: flex;
            align-items: center;
        }

        .section h2 i {
            margin-right: 10px;
            color: var(--sidebar-active);
        }

        .section-actions {
            display: flex;
            gap: 10px;
        }

        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .data-table th {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-secondary);
            font-weight: normal;
        }

        .data-table td {
            padding: 12px 10px;
            border-bottom: 1px solid var(--border-color);
        }

        .order-id {
            font-weight: bold;
            color: var(--sidebar-active);
        }

        .customer-name {
            color: var(--text-secondary);
            font-size: 13px;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-in-progress {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-completed {
            background-color: #e2e3e5;
            color: #383d41;
        }

        .status-low {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-sufficient {
            background-color: #d4edda;
            color: #155724;
        }

        /* Charts */
        .chart-container {
            width: 100%;
            height: 300px;
            margin: 15px 0;
        }

        .chart-placeholder {
            width: 100%;
            height: 100%;
            background-color: #f9f9f9;
            border: 1px dashed var(--border-color);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
        }

        /* Buttons */
        .btn {
            padding: 8px 15px;
            background-color: var(--sidebar-active);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            font-size: 14px;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }

        .btn-success {
            background-color: var(--success-color);
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .btn-warning {
            background-color: var(--warning-color);
        }

        .btn-warning:hover {
            background-color: #e0a800;
        }

        .btn-danger {
            background-color: var(--danger-color);
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .btn-secondary {
            background-color: var(--text-secondary);
        }

        .btn-secondary:hover {
            background-color: #6c757d;
        }

        /* Forms */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            color: var(--text-secondary);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 14px;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .priority-select {
            display: flex;
            gap: 10px;
        }

        .priority-select button {
            flex: 1;
            padding: 8px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
            transition: all 0.3s;
            background-color: #f9f9f9;
        }

        .priority-low {
            color: #155724;
        }

        .priority-medium {
            color: #856404;
        }

        .priority-high {
            color: #721c24;
        }

        .priority-select button.active {
            transform: scale(1.02);
            box-shadow: 0 0 0 2px currentColor;
        }

        /* Cards */
        .card {
            background-color: var(--card-bg);
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 15px;
        }

        /* Orders Grid */
        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
        }

        .order-card {
            background-color: var(--card-bg);
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .order-card-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }

        .order-card-id {
            font-weight: bold;
            color: var(--sidebar-active);
        }

        .order-card-status {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .order-card-body p {
            margin: 5px 0;
            font-size: 14px;
        }

        .order-card-footer {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
        }

        /* Tailors List */
        .tailors-list {
            list-style: none;
        }

        .tailor-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
            transition: all 0.3s;
        }

        .tailor-item:hover {
            background-color: #f9f9f9;
        }

        .tailor-item:last-child {
            border-bottom: none;
        }

        .tailor-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 20px;
            color: var(--text-secondary);
        }

        .tailor-info {
            flex: 1;
        }

        .tailor-name {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .tailor-specialty {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .tailor-status {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-available {
            background-color: #d4edda;
            color: #155724;
        }

        .status-busy {
            background-color: #fff3cd;
            color: #856404;
        }

        /* Inventory Grid */
        .inventory-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
        }

        .inventory-item {
            background-color: var(--card-bg);
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }

        .inventory-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .inventory-item-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .inventory-item-name {
            font-weight: bold;
        }

        .inventory-item-body {
            margin: 10px 0;
        }

        .inventory-item-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .inventory-quantity {
            font-size: 14px;
        }

        /* Reports List */
        .reports-list {
            list-style: none;
        }

        .reports-list li {
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .reports-list li:hover {
            background-color: #f9f9f9;
        }

        .reports-list li:last-child {
            border-bottom: none;
        }

        .reports-list i {
            margin-right: 10px;
            color: var(--sidebar-active);
            width: 20px;
            text-align: center;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }

        .modal-header h2 {
            font-size: 20px;
            color: var(--text-primary);
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--text-secondary);
        }

        /* Export Options */
        .export-options {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .export-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .pdf-btn {
            background-color: #e74c3c;
            color: white;
        }

        .excel-btn {
            background-color: #27ae60;
            color: white;
        }

        .csv-btn {
            background-color: #3498db;
            color: white;
        }

        /* Notification */
        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: var(--success-color);
            color: white;
            padding: 15px 25px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            z-index: 1001;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .notification.show {
            transform: translateY(0);
            opacity: 1;
        }

        .notification i {
            margin-right: 10px;
            font-size: 20px;
        }

        /* Page Content */
        .page-content {
            display: none;
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .page-content.active {
            display: block;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .sidebar .logo span,
            .sidebar .nav-item span,
            .sidebar .nav-menu-title {
                display: none;
            }
            
            .sidebar .logo {
                justify-content: center;
                padding: 0 0 20px 0;
            }
            
            .sidebar .nav-item {
                justify-content: center;
                padding: 12px 0;
            }
            
            .main-content {
                margin-left: 70px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-cut"></i>
            <span>TailorSuite</span>
        </div>
        <ul class="nav-menu">
            <li class="nav-menu-title">Dashboard</li>
            <li class="nav-item active" onclick="showPage('dashboard')">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </li>
            
            <li class="nav-menu-title">Orders</li>
            <li class="nav-item" onclick="showPage('order-approval')">
                <i class="fas fa-check-circle"></i>
                <span>Order Approval</span>
                <span class="notification-badge">5</span>
            </li>
            <li class="nav-item" onclick="showPage('assign-orders')">
                <i class="fas fa-user-tag"></i>
                <span>Assign Orders</span>
            </li>
            <li class="nav-item" onclick="showPage('all-orders')">
                <i class="fas fa-clipboard-list"></i>
                <span>All Orders</span>
            </li>
            
            <li class="nav-menu-title">Tailors</li>
            <li class="nav-item" onclick="showPage('tailors')">
                <i class="fas fa-user-friends"></i>
                <span>Manage Tailors</span>
            </li>
            <li class="nav-item" onclick="showPage('tailor-performance')">
                <i class="fas fa-chart-line"></i>
                <span>Tailor Performance</span>
            </li>
            
            <li class="nav-menu-title">Inventory</li>
            <li class="nav-item" onclick="showPage('inventory')">
                <i class="fas fa-boxes"></i>
                <span>Inventory Management</span>
                <span class="notification-badge">3</span>
            </li>
            <li class="nav-item" onclick="showPage('suppliers')">
                <i class="fas fa-truck"></i>
                <span>Suppliers</span>
            </li>
            
            <li class="nav-menu-title">Reports</li>
            <li class="nav-item" onclick="showPage('reports')">
                <i class="fas fa-chart-pie"></i>
                <span>Reports & Analytics</span>
            </li>
            <li class="nav-item" onclick="showPage('export-reports')">
                <i class="fas fa-file-export"></i>
                <span>Export Reports</span>
            </li>
            
            <li class="nav-menu-title">Settings</li>
            <li class="nav-item" onclick="showPage('settings')">
                <i class="fas fa-cog"></i>
                <span>System Settings</span>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1 id="page-title">Manager Dashboard</h1>
            <div class="header-actions">
                <div class="notification-icon">
                    <i class="fas fa-bell notification-bell"></i>
                    <span class="notification-count">3</span>
                </div>
                <div class="user-info">
                    <img src="https://via.placeholder.com/40" alt="User">
                    <span>Manager</span>
                    <div class="dropdown-menu">
                        <a href="#" onclick="showPage('profile')"><i class="fas fa-user"></i> Profile</a>
                        <a href="#" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Page -->
        <div id="dashboard-page" class="page-content active">
            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card" onclick="showPage('order-approval')">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Pending Approval</div>
                        <div class="stat-value">5</div>
                        <div class="stat-change positive">+2 from yesterday</div>
                    </div>
                </div>
                <div class="stat-card" onclick="showPage('all-orders')">
                    <div class="stat-icon progress">
                        <i class="fas fa-spinner"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Orders in Progress</div>
                        <div class="stat-value">12</div>
                        <div class="stat-change positive">+3 this week</div>
                    </div>
                </div>
                <div class="stat-card" onclick="showPage('tailors')">
                    <div class="stat-icon tailors">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Tailors Active</div>
                        <div class="stat-value">4</div>
                        <div class="stat-change">No change</div>
                    </div>
                </div>
                <div class="stat-card" onclick="showPage('inventory')">
                    <div class="stat-icon inventory">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Low Inventory</div>
                        <div class="stat-value">3</div>
                        <div class="stat-change negative">-2 items</div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Sections -->
            <div class="dashboard-sections">
                <!-- Order Approval Section -->
                <div class="section">
                    <div class="section-header">
                        <h2><i class="fas fa-check-circle"></i> Order Approval Queue</h2>
                        <div class="section-actions">
                            <button class="btn btn-sm" onclick="showPage('order-approval')">View All</button>
                        </div>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Garment</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="order-id">ORD2345</td>
                                <td>
                                    John Doe
                                    <div class="customer-name">john.doe@example.com</div>
                                </td>
                                <td>Suit</td>
                                <td>Apr 3</td>
                                <td><span class="status-badge status-pending">Awaiting</span></td>
                                <td>
                                    <button class="btn btn-success btn-sm" onclick="approveOrder('ORD2345')">Approve</button>
                                    <button class="btn btn-danger btn-sm" onclick="rejectOrder('ORD2345')">Reject</button>
                                </td>
                            </tr>
                            <tr>
                                <td class="order-id">ORD2346</td>
                                <td>
                                    Linda Smith
                                    <div class="customer-name">linda.smith@example.com</div>
                                </td>
                                <td>Gown</td>
                                <td>Apr 3</td>
                                <td><span class="status-badge status-pending">Awaiting</span></td>
                                <td>
                                    <button class="btn btn-success btn-sm" onclick="approveOrder('ORD2346')">Approve</button>
                                    <button class="btn btn-danger btn-sm" onclick="rejectOrder('ORD2346')">Reject</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Assign Orders Section -->
                <div class="section">
                    <div class="section-header">
                        <h2><i class="fas fa-user-tag"></i> Assign Orders to Tailors</h2>
                    </div>
                    <form id="assignForm">
                        <div class="form-group">
                            <label for="orderSelect">Select Order</label>
                            <select id="orderSelect">
                                <option value="">-- Select an order --</option>
                                <option value="ORD2345">ORD2345 - John Doe (Suit)</option>
                                <option value="ORD2346">ORD2346 - Linda Smith (Gown)</option>
                                <option value="ORD2347">ORD2347 - Robert Johnson (Shirt)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="tailorSelect">Assign Tailor</label>
                            <select id="tailorSelect">
                                <option value="">-- Select a tailor --</option>
                                <option value="tailor1">Tailor 1 (Formal Wear)</option>
                                <option value="tailor2">Tailor 2 (Casual Wear)</option>
                                <option value="tailor3">Tailor 3 (Wedding Dresses)</option>
                                <option value="tailor4">Tailor 4 (Children's Wear)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="dueDate">Expected Completion Date</label>
                            <input type="date" id="dueDate" min="2023-04-06">
                        </div>
                        
                        <div class="form-group">
                            <label for="notes">Add Notes</label>
                            <textarea id="notes" placeholder="Add any special instructions..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Set Priority</label>
                            <div class="priority-select">
                                <button type="button" class="priority-low" data-priority="low">Low</button>
                                <button type="button" class="priority-medium" data-priority="medium">Medium</button>
                                <button type="button" class="priority-high" data-priority="high">High</button>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn">Assign Order</button>
                    </form>
                </div>

                <!-- Inventory Section -->
                <div class="section">
                    <div class="section-header">
                        <h2><i class="fas fa-boxes"></i> Inventory Overview</h2>
                        <div class="section-actions">
                            <button class="btn btn-sm" onclick="showPage('inventory')">View All</button>
                        </div>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Fabric Type</th>
                                <th>Quantity</th>
                                <th>Min Required</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Cotton</td>
                                <td>15 m</td>
                                <td>20 m</td>
                                <td><span class="status-badge status-low">Low</span></td>
                            </tr>
                            <tr>
                                <td>Silk</td>
                                <td>50 m</td>
                                <td>30 m</td>
                                <td><span class="status-badge status-sufficient">Sufficient</span></td>
                            </tr>
                            <tr>
                                <td>Linen</td>
                                <td>40 m</td>
                                <td>35 m</td>
                                <td><span class="status-badge status-sufficient">Sufficient</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Reports Section -->
                <div class="section">
                    <div class="section-header">
                        <h2><i class="fas fa-chart-pie"></i> Reports Overview</h2>
                    </div>
                    <div class="chart-container">
                        <canvas id="ordersChart"></canvas>
                    </div>
                    <ul class="reports-list">
                        <li onclick="showDailyOrdersReport()">
                            <i class="fas fa-file-alt"></i>
                            <span>Daily Orders Summary</span>
                        </li>
                        <li onclick="showTailorPerformanceReport()">
                            <i class="fas fa-chart-bar"></i>
                            <span>Tailor Performance Graph</span>
                        </li>
                        <li onclick="showFabricUsageReport()">
                            <i class="fas fa-ruler-combined"></i>
                            <span>Fabric Usage Stats</span>
                        </li>
                        <li onclick="showExportOptions()">
                            <i class="fas fa-download"></i>
                            <span>Export Reports</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Order Approval Page -->
        <div id="order-approval-page" class="page-content">
            <div class="section-header">
                <h2><i class="fas fa-check-circle"></i> Order Approval</h2>
                <div class="section-actions">
                    <button class="btn btn-sm" onclick="refreshOrders()"><i class="fas fa-sync-alt"></i> Refresh</button>
                </div>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Garment</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="approval-table">
                    <!-- Orders will be populated by JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Assign Orders Page -->
        <div id="assign-orders-page" class="page-content">
            <h2><i class="fas fa-user-tag"></i> Assign Orders to Tailors</h2>
            
            <div class="orders-grid" id="assign-orders-grid">
                <!-- Order cards will be populated by JavaScript -->
            </div>
        </div>

        <!-- All Orders Page -->
        <div id="all-orders-page" class="page-content">
            <div class="section-header">
                <h2><i class="fas fa-clipboard-list"></i> All Orders</h2>
                <div class="section-actions">
                    <div class="form-group" style="margin-bottom: 0;">
                        <select id="order-filter" onchange="filterOrders()">
                            <option value="all">All Orders</option>
                            <option value="pending">Pending Approval</option>
                            <option value="in-progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="delivered">Delivered</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Garment</th>
                        <th>Tailor</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="all-orders-table">
                    <!-- Orders will be populated by JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Tailors Page -->
        <div id="tailors-page" class="page-content">
            <div class="section-header">
                <h2><i class="fas fa-user-friends"></i> Manage Tailors</h2>
                <div class="section-actions">
                    <button class="btn btn-sm" onclick="showAddTailorModal()"><i class="fas fa-plus"></i> Add Tailor</button>
                </div>
            </div>
            
            <ul class="tailors-list" id="tailors-list">
                <!-- Tailors will be populated by JavaScript -->
            </ul>
        </div>

        <!-- Tailor Performance Page -->
        <div id="tailor-performance-page" class="page-content">
            <h2><i class="fas fa-chart-line"></i> Tailor Performance</h2>
            
            <div class="chart-container">
                <canvas id="tailorPerformanceChart"></canvas>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tailor</th>
                        <th>Orders Completed</th>
                        <th>Avg. Completion Time</th>
                        <th>Quality Rating</th>
                        <th>Efficiency</th>
                    </tr>
                </thead>
                <tbody id="tailor-performance-table">
                    <!-- Performance data will be populated by JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Inventory Page -->
        <div id="inventory-page" class="page-content">
            <div class="section-header">
                <h2><i class="fas fa-boxes"></i> Inventory Management</h2>
                <div class="section-actions">
                    <button class="btn btn-sm" onclick="showAddInventoryModal()"><i class="fas fa-plus"></i> Add Item</button>
                </div>
            </div>
            
            <div class="inventory-grid" id="inventory-grid">
                <!-- Inventory items will be populated by JavaScript -->
            </div>
        </div>

        <!-- Suppliers Page -->
        <div id="suppliers-page" class="page-content">
            <h2><i class="fas fa-truck"></i> Suppliers</h2>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Contact</th>
                        <th>Materials Provided</th>
                        <th>Last Order</th>
                        <th>Rating</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="suppliers-table">
                    <!-- Suppliers will be populated by JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Reports Page -->
        <div id="reports-page" class="page-content">
            <h2><i class="fas fa-chart-pie"></i> Reports & Analytics</h2>
            
            <div class="dashboard-sections">
                <div class="section">
                    <h3><i class="fas fa-chart-line"></i> Orders Overview</h3>
                    <div class="chart-container">
                        <canvas id="ordersReportChart"></canvas>
                    </div>
                </div>
                
                <div class="section">
                    <h3><i class="fas fa-chart-bar"></i> Revenue by Garment Type</h3>
                    <div class="chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
                
                <div class="section">
                    <h3><i class="fas fa-ruler-combined"></i> Fabric Usage</h3>
                    <div class="chart-container">
                        <canvas id="fabricUsageChart"></canvas>
                    </div>
                </div>
                
                <div class="section">
                    <h3><i class="fas fa-user-friends"></i> Tailor Productivity</h3>
                    <div class="chart-container">
                        <canvas id="productivityChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Reports Page -->
        <div id="export-reports-page" class="page-content">
            <h2><i class="fas fa-file-export"></i> Export Reports</h2>
            
            <div class="card">
                <h3>Select Report to Export</h3>
                <div class="form-group">
                    <select id="report-type" class="form-control">
                        <option value="orders">Orders Report</option>
                        <option value="inventory">Inventory Report</option>
                        <option value="tailors">Tailor Performance</option>
                        <option value="financial">Financial Summary</option>
                        <option value="all">All Reports</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Date Range</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="date" id="start-date" class="form-control">
                        <input type="date" id="end-date" class="form-control">
                    </div>
                </div>
                
                <h3>Export Format</h3>
                <div class="export-options">
                    <button class="export-btn pdf-btn" onclick="exportReport('pdf')">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                    <button class="export-btn excel-btn" onclick="exportReport('excel')">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                    <button class="export-btn csv-btn" onclick="exportReport('csv')">
                        <i class="fas fa-file-csv"></i> CSV
                    </button>
                </div>
            </div>
        </div>

        <!-- Settings Page -->
        <div id="settings-page" class="page-content">
            <h2><i class="fas fa-cog"></i> System Settings</h2>
            
            <form id="settings-form">
                <div class="card">
                    <h3>General Settings</h3>
                    <div class="form-group">
                        <label for="shop-name">Shop Name</label>
                        <input type="text" id="shop-name" value="TailorSuite" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="timezone">Timezone</label>
                        <select id="timezone" required>
                            <option>(UTC+00:00) London</option>
                            <option selected>(UTC+05:30) Chennai, Kolkata, Mumbai, New Delhi</option>
                            <option>(UTC-05:00) Eastern Time (US & Canada)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="shop-address">Shop Address</label>
                        <textarea id="shop-address" rows="3" required>123 Tailor Street, Fashion District, Mumbai 400001</textarea>
                    </div>
                </div>
                
                <div class="card">
                    <h3>Notification Settings</h3>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="email-notifications" checked> Email notifications
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="inventory-alerts" checked> Low inventory alerts
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="order-alerts"> Order completion alerts
                        </label>
                    </div>
                </div>
                
                <div class="card">
                    <h3>System Settings</h3>
                    <div class="form-group">
                        <label for="backup-frequency">Backup Frequency</label>
                        <select id="backup-frequency" required>
                            <option>Daily</option>
                            <option selected>Weekly</option>
                            <option>Monthly</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="auto-logout">Auto-logout after</label>
                        <select id="auto-logout" required>
                            <option>15 minutes</option>
                            <option>30 minutes</option>
                            <option selected>1 hour</option>
                            <option>2 hours</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="btn">Save Settings</button>
            </form>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="order-details-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-clipboard-list"></i> Order Details</h2>
                <button class="close-btn" onclick="closeModal('order-details-modal')">&times;</button>
            </div>
            
            <div id="order-details-content">
                <!-- Content will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Add Tailor Modal -->
    <div id="add-tailor-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user-plus"></i> Add New Tailor</h2>
                <button class="close-btn" onclick="closeModal('add-tailor-modal')">&times;</button>
            </div>
            
            <form id="add-tailor-form">
                <div class="form-group">
                    <label for="tailor-name">Full Name</label>
                    <input type="text" id="tailor-name" required>
                </div>
                
                <div class="form-group">
                    <label for="tailor-email">Email</label>
                    <input type="email" id="tailor-email" required>
                </div>
                
                <div class="form-group">
                    <label for="tailor-phone">Phone Number</label>
                    <input type="tel" id="tailor-phone" required>
                </div>
                
                <div class="form-group">
                    <label for="tailor-specialty">Specialty</label>
                    <select id="tailor-specialty" required>
                        <option value="">-- Select specialty --</option>
                        <option value="formal">Formal Wear</option>
                        <option value="casual">Casual Wear</option>
                        <option value="wedding">Wedding Dresses</option>
                        <option value="children">Children's Wear</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="tailor-experience">Experience (years)</label>
                    <input type="number" id="tailor-experience" min="0" required>
                </div>
                
                <button type="submit" class="btn">Add Tailor</button>
            </form>
        </div>
    </div>

    <!-- Add Inventory Modal -->
    <div id="add-inventory-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-box-open"></i> Add Inventory Item</h2>
                <button class="close-btn" onclick="closeModal('add-inventory-modal')">&times;</button>
            </div>
            
            <form id="add-inventory-form">
                <div class="form-group">
                    <label for="item-name">Item Name</label>
                    <input type="text" id="item-name" required>
                </div>
                
                <div class="form-group">
                    <label for="item-type">Item Type</label>
                    <select id="item-type" required>
                        <option value="">-- Select type --</option>
                        <option value="fabric">Fabric</option>
                        <option value="thread">Thread</option>
                        <option value="button">Buttons</option>
                        <option value="zipper">Zippers</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="item-quantity">Quantity</label>
                    <input type="number" id="item-quantity" min="0" step="0.1" required>
                </div>
                
                <div class="form-group">
                    <label for="item-unit">Unit</label>
                    <select id="item-unit" required>
                        <option value="meters">Meters</option>
                        <option value="yards">Yards</option>
                        <option value="pieces">Pieces</option>
                        <option value="spools">Spools</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="item-supplier">Supplier</label>
                    <input type="text" id="item-supplier">
                </div>
                
                <div class="form-group">
                    <label for="item-threshold">Low Stock Threshold</label>
                    <input type="number" id="item-threshold" min="0" step="0.1" required>
                </div>
                
                <button type="submit" class="btn">Add Item</button>
            </form>
        </div>
    </div>

    <!-- Reports Modals -->
    <div id="daily-orders-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-file-alt"></i> Daily Orders Summary</h2>
                <button class="close-btn" onclick="closeModal('daily-orders-modal')">&times;</button>
            </div>
            <div class="chart-container">
                <canvas id="dailyOrdersChart"></canvas>
            </div>
            <table class="data-table">
                <tr>
                    <th>Date</th>
                    <th>Orders Received</th>
                    <th>Orders Completed</th>
                    <th>Revenue</th>
                </tr>
                <tr>
                    <td>Today</td>
                    <td>8</td>
                    <td>5</td>
                    <td>$1,250.00</td>
                </tr>
                <tr>
                    <td>Yesterday</td>
                    <td>12</td>
                    <td>10</td>
                    <td>$2,100.00</td>
                </tr>
                <tr>
                    <td>Last 7 Days</td>
                    <td>65</td>
                    <td>58</td>
                    <td>$12,450.00</td>
                </tr>
            </table>
        </div>
    </div>

    <div id="tailor-performance-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-chart-bar"></i> Tailor Performance Graph</h2>
                <button class="close-btn" onclick="closeModal('tailor-performance-modal')">&times;</button>
            </div>
            <div class="chart-container">
                <canvas id="tailorPerformanceModalChart"></canvas>
            </div>
            <table class="data-table">
                <tr>
                    <th>Tailor</th>
                    <th>Orders Completed</th>
                    <th>Avg. Time</th>
                    <th>Quality Rating</th>
                </tr>
                <tr>
                    <td>Tailor 1</td>
                    <td>15</td>
                    <td>2.5 days</td>
                    <td>4.8/5</td>
                </tr>
                <tr>
                    <td>Tailor 2</td>
                    <td>12</td>
                    <td>3.1 days</td>
                    <td>4.6/5</td>
                </tr>
                <tr>
                    <td>Tailor 3</td>
                    <td>18</td>
                    <td>2.2 days</td>
                    <td>4.9/5</td>
                </tr>
                <tr>
                    <td>Tailor 4</td>
                    <td>10</td>
                    <td>3.5 days</td>
                    <td>4.5/5</td>
                </tr>
            </table>
        </div>
    </div>

    <div id="fabric-usage-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-ruler-combined"></i> Fabric Usage Stats</h2>
                <button class="close-btn" onclick="closeModal('fabric-usage-modal')">&times;</button>
            </div>
            <div class="chart-container">
                <canvas id="fabricUsageModalChart"></canvas>
            </div>
            <table class="data-table">
                <tr>
                    <th>Fabric Type</th>
                    <th>Used This Month</th>
                    <th>Remaining</th>
                    <th>% Used</th>
                </tr>
                <tr>
                    <td>Cotton</td>
                    <td>45 m</td>
                    <td>15 m</td>
                    <td>75%</td>
                </tr>
                <tr>
                    <td>Silk</td>
                    <td>20 m</td>
                    <td>50 m</td>
                    <td>29%</td>
                </tr>
                <tr>
                    <td>Linen</td>
                    <td>30 m</td>
                    <td>40 m</td>
                    <td>43%</td>
                </tr>
                <tr>
                    <td>Wool</td>
                    <td>15 m</td>
                    <td>25 m</td>
                    <td>38%</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Notification Toast -->
    <div id="notification-toast" class="notification">
        <i class="fas fa-check-circle"></i>
        <span id="notification-message">Action completed successfully!</span>
    </div>

    <script>
        // Sample data for the dashboard
        const orders = [
            {
                id: "ORD2345",
                customer: "John Doe",
                email: "john.doe@example.com",
                garment: "Suit",
                date: "2023-04-03",
                status: "pending",
                amount: "$250.00",
                measurements: {
                    chest: "42",
                    waist: "36",
                    length: "42",
                    sleeve: "25"
                },
                notes: "Business suit for conference"
            },
            {
                id: "ORD2346",
                customer: "Linda Smith",
                email: "linda.smith@example.com",
                garment: "Gown",
                date: "2023-04-03",
                status: "pending",
                amount: "$350.00",
                measurements: {
                    bust: "36",
                    waist: "28",
                    hips: "38",
                    length: "58"
                },
                notes: "Evening gown for wedding"
            },
            {
                id: "ORD2347",
                customer: "Robert Johnson",
                email: "robert.j@example.com",
                garment: "Shirt",
                date: "2023-04-02",
                status: "approved",
                amount: "$85.00",
                measurements: {
                    neck: "16",
                    sleeve: "24",
                    chest: "40"
                },
                notes: "Formal dress shirt"
            },
            {
                id: "ORD2348",
                customer: "Sarah Williams",
                email: "sarah.w@example.com",
                garment: "Dress",
                date: "2023-04-01",
                status: "in-progress",
                amount: "$180.00",
                tailor: "Tailor 3",
                dueDate: "2023-04-15",
                measurements: {
                    bust: "34",
                    waist: "30",
                    hips: "36",
                    length: "45"
                },
                notes: "Summer dress with lace"
            }
        ];

        const tailors = [
            {
                id: "tailor1",
                name: "Tailor 1",
                specialty: "Formal Wear",
                status: "available",
                orders: 2,
                experience: 5,
                rating: 4.8
            },
            {
                id: "tailor2",
                name: "Tailor 2",
                specialty: "Casual Wear",
                status: "busy",
                orders: 3,
                experience: 3,
                rating: 4.6
            },
            {
                id: "tailor3",
                name: "Tailor 3",
                specialty: "Wedding Dresses",
                status: "busy",
                orders: 5,
                experience: 7,
                rating: 4.9
            },
            {
                id: "tailor4",
                name: "Tailor 4",
                specialty: "Children's Wear",
                status: "available",
                orders: 1,
                experience: 4,
                rating: 4.5
            }
        ];

        const inventory = [
            {
                id: "item1",
                name: "Cotton",
                type: "fabric",
                quantity: 15,
                unit: "meters",
                threshold: 20,
                status: "low",
                supplier: "Fabric World"
            },
            {
                id: "item2",
                name: "Silk",
                type: "fabric",
                quantity: 50,
                unit: "meters",
                threshold: 30,
                status: "sufficient",
                supplier: "Luxury Fabrics"
            },
            {
                id: "item3",
                name: "Linen",
                type: "fabric",
                quantity: 40,
                unit: "meters",
                threshold: 35,
                status: "sufficient",
                supplier: "Natural Textiles"
            },
            {
                id: "item4",
                name: "Wool",
                type: "fabric",
                quantity: 25,
                unit: "meters",
                threshold: 20,
                status: "sufficient",
                supplier: "Winter Fabrics"
            }
        ];

        const suppliers = [
            {
                id: "supplier1",
                name: "Fabric World",
                contact: "Rajesh Kumar (9876543210)",
                materials: "Cotton, Linen",
                lastOrder: "2023-04-01",
                rating: 4.5
            },
            {
                id: "supplier2",
                name: "Luxury Fabrics",
                contact: "Priya Sharma (8765432109)",
                materials: "Silk, Satin",
                lastOrder: "2023-03-28",
                rating: 4.8
            },
            {
                id: "supplier3",
                name: "Button Emporium",
                contact: "Vikram Patel (7654321098)",
                materials: "Buttons, Zippers",
                lastOrder: "2023-03-25",
                rating: 4.2
            }
        ];

        // Initialize the dashboard when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Load all data
            loadOrderApproval();
            loadAssignOrders();
            loadAllOrders();
            loadTailors();
            loadTailorPerformance();
            loadInventory();
            loadSuppliers();
            
            // Initialize forms
            initForms();
            
            // Initialize charts
            initCharts();
            
            // Set current date for forms
            document.getElementById('dueDate').valueAsDate = new Date();
        });

        // Initialize charts
        function initCharts() {
            // Dashboard Orders Chart
            const ordersCtx = document.getElementById('ordersChart').getContext('2d');
            const ordersChart = new Chart(ordersCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Orders Received',
                        data: [45, 60, 75, 82, 90, 65],
                        borderColor: '#3498db',
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        tension: 0.3,
                        fill: true
                    }, {
                        label: 'Orders Completed',
                        data: [40, 55, 70, 78, 85, 58],
                        borderColor: '#27ae60',
                        backgroundColor: 'rgba(39, 174, 96, 0.1)',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Tailor Performance Chart
            const tailorCtx = document.getElementById('tailorPerformanceChart').getContext('2d');
            const tailorChart = new Chart(tailorCtx, {
                type: 'bar',
                data: {
                    labels: ['Tailor 1', 'Tailor 2', 'Tailor 3', 'Tailor 4'],
                    datasets: [{
                        label: 'Orders Completed',
                        data: [15, 12, 18, 10],
                        backgroundColor: 'rgba(52, 152, 219, 0.7)'
                    }, {
                        label: 'Quality Rating (out of 5)',
                        data: [4.8, 4.6, 4.9, 4.5],
                        backgroundColor: 'rgba(46, 204, 113, 0.7)',
                        type: 'line',
                        borderColor: '#2ecc71',
                        borderWidth: 2,
                        pointBackgroundColor: '#2ecc71',
                        pointRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Orders Report Chart
            const ordersReportCtx = document.getElementById('ordersReportChart').getContext('2d');
            const ordersReportChart = new Chart(ordersReportCtx, {
                type: 'bar',
                data: {
                    labels: ['Pending', 'In Progress', 'Completed', 'Delivered'],
                    datasets: [{
                        label: 'Orders',
                        data: [5, 12, 8, 15],
                        backgroundColor: [
                            'rgba(255, 193, 7, 0.7)',
                            'rgba(52, 152, 219, 0.7)',
                            'rgba(108, 117, 125, 0.7)',
                            'rgba(39, 174, 96, 0.7)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            const revenueChart = new Chart(revenueCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Suits', 'Shirts', 'Pants', 'Dresses', 'Gowns'],
                    datasets: [{
                        data: [12500, 8500, 6000, 10500, 9500],
                        backgroundColor: [
                            'rgba(52, 152, 219, 0.7)',
                            'rgba(155, 89, 182, 0.7)',
                            'rgba(39, 174, 96, 0.7)',
                            'rgba(241, 196, 15, 0.7)',
                            'rgba(231, 76, 60, 0.7)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Fabric Usage Chart
            const fabricCtx = document.getElementById('fabricUsageChart').getContext('2d');
            const fabricChart = new Chart(fabricCtx, {
                type: 'radar',
                data: {
                    labels: ['Cotton', 'Silk', 'Linen', 'Wool', 'Polyester'],
                    datasets: [{
                        label: 'Usage (meters)',
                        data: [45, 20, 30, 15, 25],
                        backgroundColor: 'rgba(52, 152, 219, 0.2)',
                        borderColor: '#3498db',
                        pointBackgroundColor: '#3498db',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#3498db'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Productivity Chart
            const productivityCtx = document.getElementById('productivityChart').getContext('2d');
            const productivityChart = new Chart(productivityCtx, {
                type: 'polarArea',
                data: {
                    labels: ['Tailor 1', 'Tailor 2', 'Tailor 3', 'Tailor 4'],
                    datasets: [{
                        data: [15, 12, 18, 10],
                        backgroundColor: [
                            'rgba(52, 152, 219, 0.7)',
                            'rgba(155, 89, 182, 0.7)',
                            'rgba(39, 174, 96, 0.7)',
                            'rgba(241, 196, 15, 0.7)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        // Load order approval data
        function loadOrderApproval() {
            const tableBody = document.getElementById('approval-table');
            tableBody.innerHTML = '';
            
            const pendingOrders = orders.filter(order => order.status === 'pending');
            
            pendingOrders.forEach(order => {
                const row = document.createElement('tr');
                
                const date = new Date(order.date).toLocaleDateString('en-US', { 
                    month: 'short', 
                    day: 'numeric' 
                });
                
                row.innerHTML = `
                    <td class="order-id">${order.id}</td>
                    <td>
                        ${order.customer}
                        <div class="customer-name">${order.email}</div>
                    </td>
                    <td>${order.garment}</td>
                    <td>${date}</td>
                    <td>${order.amount}</td>
                    <td><span class="status-badge status-pending">Pending</span></td>
                    <td>
                        <button class="btn btn-success btn-sm" onclick="approveOrder('${order.id}')">Approve</button>
                        <button class="btn btn-danger btn-sm" onclick="rejectOrder('${order.id}')">Reject</button>
                    </td>
                `;
                
                tableBody.appendChild(row);
            });
        }

        // Load assign orders data
        function loadAssignOrders() {
            const grid = document.getElementById('assign-orders-grid');
            grid.innerHTML = '';
            
            const approvedOrders = orders.filter(order => order.status === 'approved');
            
            approvedOrders.forEach(order => {
                const card = document.createElement('div');
                card.className = 'order-card';
                
                card.innerHTML = `
                    <div class="order-card-header">
                        <span class="order-card-id">${order.id}</span>
                        <span class="order-card-status status-approved">Approved</span>
                    </div>
                    <div class="order-card-body">
                        <p><strong>Customer:</strong> ${order.customer}</p>
                        <p><strong>Garment:</strong> ${order.garment}</p>
                        <p><strong>Amount:</strong> ${order.amount}</p>
                        <p><strong>Notes:</strong> ${order.notes}</p>
                    </div>
                    <div class="order-card-footer">
                        <button class="btn btn-sm" onclick="viewOrderDetails('${order.id}')">View</button>
                        <button class="btn btn-sm" onclick="assignOrderModal('${order.id}')">Assign</button>
                    </div>
                `;
                
                grid.appendChild(card);
            });
        }

        // Load all orders data
        function loadAllOrders() {
            const tableBody = document.getElementById('all-orders-table');
            tableBody.innerHTML = '';
            
            orders.forEach(order => {
                const row = document.createElement('tr');
                
                let statusClass, statusText;
                switch(order.status) {
                    case 'pending':
                        statusClass = 'status-pending';
                        statusText = 'Pending';
                        break;
                    case 'approved':
                        statusClass = 'status-approved';
                        statusText = 'Approved';
                        break;
                    case 'in-progress':
                        statusClass = 'status-in-progress';
                        statusText = 'In Progress';
                        break;
                    case 'completed':
                        statusClass = 'status-completed';
                        statusText = 'Completed';
                        break;
                    default:
                        statusClass = 'status-pending';
                        statusText = 'Pending';
                }
                
                const tailor = order.tailor || 'Not assigned';
                const dueDate = order.dueDate ? new Date(order.dueDate).toLocaleDateString('en-US', { 
                    month: 'short', 
                    day: 'numeric' 
                }) : '-';
                
                row.innerHTML = `
                    <td class="order-id">${order.id}</td>
                    <td>${order.customer}</td>
                    <td>${order.garment}</td>
                    <td>${tailor}</td>
                    <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                    <td>${dueDate}</td>
                    <td>
                        <button class="btn btn-sm" onclick="viewOrderDetails('${order.id}')">View</button>
                    </td>
                `;
                
                tableBody.appendChild(row);
            });
        }

        // Filter orders
        function filterOrders() {
            const filter = document.getElementById('order-filter').value;
            const rows = document.querySelectorAll('#all-orders-table tr');
            
            rows.forEach(row => {
                if (row.cells.length > 0) {
                    const statusCell = row.cells[4];
                    if (statusCell) {
                        const status = statusCell.textContent.trim().toLowerCase();
                        
                        if (filter === 'all' || 
                            (filter === 'pending' && status.includes('pending')) ||
                            (filter === 'in-progress' && status.includes('progress')) ||
                            (filter === 'completed' && status.includes('completed')) ||
                            (filter === 'delivered' && status.includes('delivered'))) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    }
                }
            });
        }

        // Load tailors data
        function loadTailors() {
            const list = document.getElementById('tailors-list');
            list.innerHTML = '';
            
            tailors.forEach(tailor => {
                const item = document.createElement('li');
                item.className = 'tailor-item';
                
                item.innerHTML = `
                    <div class="tailor-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="tailor-info">
                        <div class="tailor-name">${tailor.name}</div>
                        <div class="tailor-specialty">Specialty: ${tailor.specialty}</div>
                    </div>
                    <div class="tailor-status ${tailor.status === 'available' ? 'status-available' : 'status-busy'}">
                        ${tailor.status === 'available' ? 'Available' : `${tailor.orders} Orders`}
                    </div>
                `;
                
                list.appendChild(item);
            });
        }

        // Load tailor performance data
        function loadTailorPerformance() {
            const tableBody = document.getElementById('tailor-performance-table');
            tableBody.innerHTML = '';
            
            tailors.forEach(tailor => {
                const row = document.createElement('tr');
                
                row.innerHTML = `
                    <td>${tailor.name}</td>
                    <td>${tailor.orders}</td>
                    <td>${(Math.random() * 2 + 2).toFixed(1)} days</td>
                    <td>${tailor.rating}/5</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <div style="flex: 1; height: 8px; background-color: #e9ecef; border-radius: 4px;">
                                <div style="width: ${Math.random() * 60 + 40}%; height: 100%; background-color: #28a745; border-radius: 4px;"></div>
                            </div>
                            <span>${Math.floor(Math.random() * 30 + 70)}%</span>
                        </div>
                    </td>
                `;
                
                tableBody.appendChild(row);
            });
        }

        // Load inventory data
        function loadInventory() {
            const grid = document.getElementById('inventory-grid');
            grid.innerHTML = '';
            
            inventory.forEach(item => {
                const card = document.createElement('div');
                card.className = 'inventory-item';
                
                card.innerHTML = `
                    <div class="inventory-item-header">
                        <span class="inventory-item-name">${item.name}</span>
                        <span class="status-badge ${item.status === 'low' ? 'status-low' : 'status-sufficient'}">
                            ${item.status === 'low' ? 'Low' : 'Sufficient'}
                        </span>
                    </div>
                    <div class="inventory-item-body">
                        <p><strong>Type:</strong> ${item.type}</p>
                        <p><strong>Supplier:</strong> ${item.supplier}</p>
                    </div>
                    <div class="inventory-item-footer">
                        <span class="inventory-quantity"><strong>Quantity:</strong> ${item.quantity} ${item.unit}</span>
                        <button class="btn btn-sm" onclick="orderMoreInventory('${item.id}')">Order More</button>
                    </div>
                `;
                
                grid.appendChild(card);
            });
        }

        // Load suppliers data
        function loadSuppliers() {
            const tableBody = document.getElementById('suppliers-table');
            tableBody.innerHTML = '';
            
            suppliers.forEach(supplier => {
                const row = document.createElement('tr');
                
                const lastOrder = new Date(supplier.lastOrder).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                });
                
                row.innerHTML = `
                    <td>${supplier.name}</td>
                    <td>${supplier.contact}</td>
                    <td>${supplier.materials}</td>
                    <td>${lastOrder}</td>
                    <td>${supplier.rating}/5</td>
                    <td>
                        <button class="btn btn-sm" onclick="viewSupplier('${supplier.id}')">View</button>
                    </td>
                `;
                
                tableBody.appendChild(row);
            });
        }

        // Initialize forms
        function initForms() {
            // Assign order form
            document.getElementById('assignForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const orderSelect = document.getElementById('orderSelect');
                const tailorSelect = document.getElementById('tailorSelect');
                const dueDate = document.getElementById('dueDate');
                const notes = document.getElementById('notes');
                
                if (!orderSelect.value) {
                    showNotification('Please select an order', 'error');
                    return;
                }
                
                if (!tailorSelect.value) {
                    showNotification('Please select a tailor', 'error');
                    return;
                }
                
                if (!dueDate.value) {
                    showNotification('Please select a due date', 'error');
                    return;
                }
                
                // Find the order
                const order = orders.find(o => o.id === orderSelect.value);
                if (order) {
                    order.status = 'in-progress';
                    order.tailor = tailorSelect.options[tailorSelect.selectedIndex].text;
                    order.dueDate = dueDate.value;
                    
                    if (notes.value) {
                        order.notes = notes.value;
                    }
                    
                    showNotification(`Order ${order.id} assigned to ${order.tailor}`, 'success');
                    loadAssignOrders();
                    loadAllOrders();
                    loadOrderApproval();
                }
                
                // Reset form
                this.reset();
            });
            
            // Add tailor form
            document.getElementById('add-tailor-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const name = document.getElementById('tailor-name').value;
                const email = document.getElementById('tailor-email').value;
                const phone = document.getElementById('tailor-phone').value;
                const specialty = document.getElementById('tailor-specialty').value;
                const experience = document.getElementById('tailor-experience').value;
                
                const newTailor = {
                    id: `tailor${tailors.length + 1}`,
                    name: name,
                    specialty: specialty,
                    status: "available",
                    orders: 0,
                    experience: experience,
                    rating: (Math.random() * 0.5 + 4.5).toFixed(1)
                };
                
                tailors.push(newTailor);
                loadTailors();
                loadTailorPerformance();
                closeModal('add-tailor-modal');
                showNotification(`Tailor ${name} added successfully`, 'success');
            });
            
            // Add inventory form
            document.getElementById('add-inventory-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const name = document.getElementById('item-name').value;
                const type = document.getElementById('item-type').value;
                const quantity = parseFloat(document.getElementById('item-quantity').value);
                const unit = document.getElementById('item-unit').value;
                const supplier = document.getElementById('item-supplier').value;
                const threshold = parseFloat(document.getElementById('item-threshold').value);
                
                const newItem = {
                    id: `item${inventory.length + 1}`,
                    name: name,
                    type: type,
                    quantity: quantity,
                    unit: unit,
                    threshold: threshold,
                    status: quantity < threshold ? 'low' : 'sufficient',
                    supplier: supplier
                };
                
                inventory.push(newItem);
                loadInventory();
                closeModal('add-inventory-modal');
                showNotification(`Inventory item ${name} added successfully`, 'success');
            });
            
            // Settings form
            document.getElementById('settings-form').addEventListener('submit', function(e) {
                e.preventDefault();
                showNotification('Settings saved successfully', 'success');
            });
        }

        // View order details
        function viewOrderDetails(orderId) {
            const order = orders.find(o => o.id === orderId);
            const modalContent = document.getElementById('order-details-content');
            
            if (order) {
                let statusClass, statusText;
                switch(order.status) {
                    case 'pending':
                        statusClass = 'status-pending';
                        statusText = 'Pending Approval';
                        break;
                    case 'approved':
                        statusClass = 'status-approved';
                        statusText = 'Approved';
                        break;
                    case 'in-progress':
                        statusClass = 'status-in-progress';
                        statusText = 'In Progress';
                        break;
                    case 'completed':
                        statusClass = 'status-completed';
                        statusText = 'Completed';
                        break;
                    default:
                        statusClass = 'status-pending';
                        statusText = 'Pending';
                }
                
                const date = new Date(order.date).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                });
                
                const dueDate = order.dueDate ? new Date(order.dueDate).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                }) : 'Not set';
                
                const tailor = order.tailor || 'Not assigned';
                
                modalContent.innerHTML = `
                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <div>
                                <strong>Order ID:</strong> ${order.id}
                            </div>
                            <div>
                                <span class="status-badge ${statusClass}">${statusText}</span>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                            <div>
                                <strong>Customer:</strong> ${order.customer}
                            </div>
                            <div>
                                <strong>Email:</strong> ${order.email}
                            </div>
                            <div>
                                <strong>Garment:</strong> ${order.garment}
                            </div>
                            <div>
                                <strong>Amount:</strong> ${order.amount}
                            </div>
                            <div>
                                <strong>Order Date:</strong> ${date}
                            </div>
                            <div>
                                <strong>Due Date:</strong> ${dueDate}
                            </div>
                            <div>
                                <strong>Assigned Tailor:</strong> ${tailor}
                            </div>
                        </div>
                        
                        ${order.measurements ? `
                        <div style="margin: 15px 0;">
                            <strong>Measurements:</strong>
                            <div style="margin-top: 5px; padding: 10px; background-color: #f9f9f9; border-radius: 4px;">
                                ${Object.entries(order.measurements).map(([key, value]) => 
                                    `${key.charAt(0).toUpperCase() + key.slice(1)}: ${value}"`).join(', ')}
                            </div>
                        </div>
                        ` : ''}
                        
                        ${order.notes ? `
                        <div style="margin: 15px 0;">
                            <strong>Notes:</strong>
                            <div style="margin-top: 5px; padding: 10px; background-color: #f9f9f9; border-radius: 4px;">
                                ${order.notes}
                            </div>
                        </div>
                        ` : ''}
                    </div>
                `;
                
                openModal('order-details-modal');
            } else {
                showNotification('Order not found', 'error');
            }
        }

        // View supplier details
        function viewSupplier(supplierId) {
            const supplier = suppliers.find(s => s.id === supplierId);
            const modalContent = document.getElementById('order-details-content');
            
            if (supplier) {
                modalContent.innerHTML = `
                    <div style="margin-bottom: 20px;">
                        <h3 style="margin-bottom: 15px;">${supplier.name}</h3>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                            <div>
                                <strong>Contact:</strong> ${supplier.contact}
                            </div>
                            <div>
                                <strong>Materials Provided:</strong> ${supplier.materials}
                            </div>
                            <div>
                                <strong>Last Order:</strong> ${new Date(supplier.lastOrder).toLocaleDateString('en-US', { 
                                    year: 'numeric', 
                                    month: 'short', 
                                    day: 'numeric' 
                                })}
                            </div>
                            <div>
                                <strong>Rating:</strong> ${supplier.rating}/5
                            </div>
                        </div>
                        
                        <div style="margin: 15px 0;">
                            <strong>Recent Orders:</strong>
                            <table class="data-table" style="margin-top: 10px;">
                                <tr>
                                    <th>Date</th>
                                    <th>Material</th>
                                    <th>Quantity</th>
                                    <th>Amount</th>
                                </tr>
                                <tr>
                                    <td>${new Date(supplier.lastOrder).toLocaleDateString('en-US', { 
                                        year: 'numeric', 
                                        month: 'short', 
                                        day: 'numeric' 
                                    })}</td>
                                    <td>${supplier.materials.split(', ')[0]}</td>
                                    <td>25 m</td>
                                    <td>$125.00</td>
                                </tr>
                                <tr>
                                    <td>${new Date('2023-03-15').toLocaleDateString('en-US', { 
                                        year: 'numeric', 
                                        month: 'short', 
                                        day: 'numeric' 
                                    })}</td>
                                    <td>${supplier.materials.split(', ')[1] || supplier.materials.split(', ')[0]}</td>
                                    <td>15 m</td>
                                    <td>$85.00</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                `;
                
                openModal('order-details-modal');
            } else {
                showNotification('Supplier not found', 'error');
            }
        }

        // Approve order
        function approveOrder(orderId) {
            const order = orders.find(o => o.id === orderId);
            if (order) {
                order.status = 'approved';
                loadOrderApproval();
                loadAssignOrders();
                loadAllOrders();
                showNotification(`Order ${orderId} approved`, 'success');
            }
        }

        // Reject order
        function rejectOrder(orderId) {
            const order = orders.find(o => o.id === orderId);
            if (order) {
                order.status = 'rejected';
                loadOrderApproval();
                showNotification(`Order ${orderId} rejected`, 'success');
            }
        }

        // Order more inventory
        function orderMoreInventory(itemId) {
            const item = inventory.find(i => i.id === itemId);
            if (item) {
                showNotification(`Order placed for more ${item.name}`, 'success');
            }
        }

        // Show add tailor modal
        function showAddTailorModal() {
            document.getElementById('add-tailor-form').reset();
            openModal('add-tailor-modal');
        }

        // Show add inventory modal
        function showAddInventoryModal() {
            document.getElementById('add-inventory-form').reset();
            openModal('add-inventory-modal');
        }

        // Show daily orders report
        function showDailyOrdersReport() {
            const modalContent = document.getElementById('order-details-content');
            
            modalContent.innerHTML = `
                <div class="chart-container">
                    <canvas id="dailyOrdersModalChart"></canvas>
                </div>
                <table class="data-table">
                    <tr>
                        <th>Date</th>
                        <th>Orders Received</th>
                        <th>Orders Completed</th>
                        <th>Revenue</th>
                    </tr>
                    <tr>
                        <td>Today</td>
                        <td>8</td>
                        <td>5</td>
                        <td>$1,250.00</td>
                    </tr>
                    <tr>
                        <td>Yesterday</td>
                        <td>12</td>
                        <td>10</td>
                        <td>$2,100.00</td>
                    </tr>
                    <tr>
                        <td>Last 7 Days</td>
                        <td>65</td>
                        <td>58</td>
                        <td>$12,450.00</td>
                    </tr>
                </table>
            `;
            
            openModal('order-details-modal');
            
            // Initialize chart after modal is open
            setTimeout(() => {
                const ctx = document.getElementById('dailyOrdersModalChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                        datasets: [{
                            label: 'Orders Received',
                            data: [10, 12, 8, 15, 11, 9],
                            backgroundColor: 'rgba(52, 152, 219, 0.7)'
                        }, {
                            label: 'Orders Completed',
                            data: [8, 10, 7, 12, 9, 12],
                            backgroundColor: 'rgba(46, 204, 113, 0.7)'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }, 100);
        }

        // Show tailor performance report
        function showTailorPerformanceReport() {
            const modalContent = document.getElementById('order-details-content');
            
            modalContent.innerHTML = `
                <div class="chart-container">
                    <canvas id="tailorPerformanceModalChart"></canvas>
                </div>
                <table class="data-table">
                    <tr>
                        <th>Tailor</th>
                        <th>Orders Completed</th>
                        <th>Avg. Time</th>
                        <th>Quality Rating</th>
                    </tr>
                    <tr>
                        <td>Tailor 1</td>
                        <td>15</td>
                        <td>2.5 days</td>
                        <td>4.8/5</td>
                    </tr>
                    <tr>
                        <td>Tailor 2</td>
                        <td>12</td>
                        <td>3.1 days</td>
                        <td>4.6/5</td>
                    </tr>
                    <tr>
                        <td>Tailor 3</td>
                        <td>18</td>
                        <td>2.2 days</td>
                        <td>4.9/5</td>
                    </tr>
                    <tr>
                        <td>Tailor 4</td>
                        <td>10</td>
                        <td>3.5 days</td>
                        <td>4.5/5</td>
                    </tr>
                </table>
            `;
            
            openModal('order-details-modal');
            
            // Initialize chart after modal is open
            setTimeout(() => {
                const ctx = document.getElementById('tailorPerformanceModalChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Tailor 1', 'Tailor 2', 'Tailor 3', 'Tailor 4'],
                        datasets: [{
                            label: 'Orders Completed',
                            data: [15, 12, 18, 10],
                            backgroundColor: 'rgba(52, 152, 219, 0.7)'
                        }, {
                            label: 'Quality Rating (out of 5)',
                            data: [4.8, 4.6, 4.9, 4.5],
                            backgroundColor: 'rgba(46, 204, 113, 0.7)',
                            type: 'line',
                            borderColor: '#2ecc71',
                            borderWidth: 2,
                            pointBackgroundColor: '#2ecc71',
                            pointRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }, 100);
        }

        // Show fabric usage report
        function showFabricUsageReport() {
            const modalContent = document.getElementById('order-details-content');
            
            modalContent.innerHTML = `
                <div class="chart-container">
                    <canvas id="fabricUsageModalChart"></canvas>
                </div>
                <table class="data-table">
                    <tr>
                        <th>Fabric Type</th>
                        <th>Used This Month</th>
                        <th>Remaining</th>
                        <th>% Used</th>
                    </tr>
                    <tr>
                        <td>Cotton</td>
                        <td>45 m</td>
                        <td>15 m</td>
                        <td>75%</td>
                    </tr>
                    <tr>
                        <td>Silk</td>
                        <td>20 m</td>
                        <td>50 m</td>
                        <td>29%</td>
                    </tr>
                    <tr>
                        <td>Linen</td>
                        <td>30 m</td>
                        <td>40 m</td>
                        <td>43%</td>
                    </tr>
                    <tr>
                        <td>Wool</td>
                        <td>15 m</td>
                        <td>25 m</td>
                        <td>38%</td>
                    </tr>
                </table>
            `;
            
            openModal('order-details-modal');
            
            // Initialize chart after modal is open
            setTimeout(() => {
                const ctx = document.getElementById('fabricUsageModalChart').getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Cotton', 'Silk', 'Linen', 'Wool'],
                        datasets: [{
                            data: [45, 20, 30, 15],
                            backgroundColor: [
                                'rgba(52, 152, 219, 0.7)',
                                'rgba(155, 89, 182, 0.7)',
                                'rgba(46, 204, 113, 0.7)',
                                'rgba(241, 196, 15, 0.7)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }, 100);
        }

        // Show export options
        function showExportOptions() {
            const modalContent = document.getElementById('order-details-content');
            
            modalContent.innerHTML = `
                <h3>Select Report to Export</h3>
                <div class="form-group">
                    <select id="export-report-type" class="form-control">
                        <option value="orders">Orders Report</option>
                        <option value="inventory">Inventory Report</option>
                        <option value="tailors">Tailor Performance</option>
                        <option value="financial">Financial Summary</option>
                        <option value="all">All Reports</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Date Range</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="date" id="export-start-date" class="form-control">
                        <input type="date" id="export-end-date" class="form-control">
                    </div>
                </div>
                
                <h3 style="margin-top: 20px;">Export Format</h3>
                <div class="export-options">
                    <button class="export-btn pdf-btn" onclick="exportReport('pdf')">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                    <button class="export-btn excel-btn" onclick="exportReport('excel')">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                    <button class="export-btn csv-btn" onclick="exportReport('csv')">
                        <i class="fas fa-file-csv"></i> CSV
                    </button>
                </div>
            `;
            
            // Set default dates
            document.getElementById('export-start-date').valueAsDate = new Date();
            document.getElementById('export-end-date').valueAsDate = new Date();
            
            openModal('order-details-modal');
        }

        // Export report
        function exportReport(format) {
            const reportType = document.getElementById('export-report-type').value;
            const startDate = document.getElementById('export-start-date').value;
            const endDate = document.getElementById('export-end-date').value;
            
            let message = `Exporting ${reportType} report as ${format.toUpperCase()}`;
            if (startDate && endDate) {
                message += ` for ${startDate} to ${endDate}`;
            }
            
            showNotification(`${message} successfully!`, 'success');
            closeModal('order-details-modal');
            
            // In a real app, this would trigger a download
            console.log(message);
        }

        // Open modal
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }

        // Close modal
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Show notification
        function showNotification(message, type) {
            const notification = document.getElementById('notification-toast');
            const notificationMsg = document.getElementById('notification-message');
            
            // Set message and style based on type
            notificationMsg.textContent = message;
            
            // Reset classes
            notification.className = 'notification';
            
            // Add type-specific class
            if (type === 'success') {
                notification.style.backgroundColor = '#27ae60';
            } else if (type === 'error') {
                notification.style.backgroundColor = '#e74c3c';
            } else if (type === 'warning') {
                notification.style.backgroundColor = '#f39c12';
            } else {
                notification.style.backgroundColor = '#3498db';
            }
            
            // Show notification
            notification.classList.add('show');
            
            // Hide after 3 seconds
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }

        // Show page function
        function showPage(pageId) {
            // Hide all pages
            document.querySelectorAll('.page-content').forEach(page => {
                page.classList.remove('active');
            });
            
            // Show selected page
            const pageElement = document.getElementById(`${pageId}-page`);
            if (pageElement) {
                pageElement.classList.add('active');
            }
            
            // Update page title
            const pageTitles = {
                'dashboard': 'Manager Dashboard',
                'order-approval': 'Order Approval',
                'assign-orders': 'Assign Orders',
                'all-orders': 'All Orders',
                'tailors': 'Manage Tailors',
                'tailor-performance': 'Tailor Performance',
                'inventory': 'Inventory Management',
                'suppliers': 'Suppliers',
                'reports': 'Reports & Analytics',
                'export-reports': 'Export Reports',
                'settings': 'System Settings'
            };
            document.getElementById('page-title').textContent = pageTitles[pageId] || 'Manager Dashboard';
            
            // Update active nav item
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Set the appropriate nav item as active
            const activeItem = document.querySelector(`.nav-item[onclick="showPage('${pageId}')"]`);
            if (activeItem) {
                activeItem.classList.add('active');
            } else {
                // Default to dashboard if page not found
                document.querySelector(`.nav-item[onclick="showPage('dashboard')"]`).classList.add('active');
            }
        }

        // Refresh orders
        function refreshOrders() {
            showNotification('Orders refreshed', 'success');
        }

        // Logout function
        function logout() {
            showNotification('Logging out...', 'success');
            setTimeout(() => {
                // In a real app, this would redirect to login page
                window.location.href = '/login';
            }, 1500);
        }

        function loadManagerStats() {
  fetch('api.php?action=manager_stats')
    .then(res => res.json())
    .then(data => {
      document.getElementById("total-orders").textContent = data.total_orders;
      document.getElementById("pending-orders").textContent = data.pending_orders;
      document.getElementById("completed-orders").textContent = data.completed_orders;
    });
}
setInterval(loadManagerStats, 5000);
loadManagerStats();


        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>