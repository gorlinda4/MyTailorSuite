<?php
require_once 'auth.php';

$auth = new Auth();

if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user = $auth->getCurrentUser();
if ($user['role'] !== 'customer') {
    header('Location: customer_dashboard.php');
    exit();
}

class Order {
    public function getGarmentTypes() {
        // This is a placeholder - in a real application, you would query the database
        return [
            ['id' => 1, 'name' => 'Shirt'],
            ['id' => 2, 'name' => 'Pants'],
            ['id' => 3, 'name' => 'Dress'],
            ['id' => 4, 'name' => 'Suit'],
            ['id' => 5, 'name' => 'Skirt'],
        ];
    }
}

// Get garment types for the order form
$order = new Order();
$garmentTypes = $order->getGarmentTypes();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - TailorSuite</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
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
            --paypal-color: #FFC439;
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
            min-height: 100vh;
            position: fixed;
            padding: 20px 0;
            overflow-y: auto;
            height: 100vh;
        }

        .sidebar::-webkit-scrollbar {
            width: 8px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 4px;
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

        .user-info {
            display: flex;
            align-items: center;
            position: relative;
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
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        @media (max-width: 1200px) {
            .dashboard-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .dashboard-stats {
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

        .stat-icon.orders {
            background-color: rgba(52, 152, 219, 0.1);
            color: #3498db;
        }

        .stat-icon.payments {
            background-color: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
        }

        .stat-icon.delivered {
            background-color: rgba(155, 89, 182, 0.1);
            color: #9b59b6;
        }

        .stat-icon.login {
            background-color: rgba(241, 196, 15, 0.1);
            color: #f1c40f;
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

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        @media (max-width: 1200px) {
            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .quick-actions {
                grid-template-columns: 1fr;
            }
        }

        .action-btn {
            padding: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            background-color: white;
        }

        .action-btn:hover {
            background-color: #f9f9f9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .action-btn i {
            font-size: 24px;
            margin-bottom: 10px;
            color: var(--sidebar-active);
        }

        .action-btn span {
            font-size: 14px;
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

        .section h2 {
            font-size: 18px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
        }

        .section h2 i {
            margin-right: 10px;
            color: var(--sidebar-active);
        }

        /* Order Table */
        .order-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .order-table th {
            text-align: left;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-secondary);
            font-weight: normal;
        }

        .order-table td {
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .order-id {
            font-weight: bold;
            color: var(--sidebar-active);
        }

        .order-status {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-in-progress {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-delivered {
            background-color: #d4edda;
            color: #155724;
        }

        .status-pending {
            background-color: #cce5ff;
            color: #004085;
        }

        /* Button Styles */
        .btn {
            padding: 8px 15px;
            background-color: var(--sidebar-active);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
            display: inline-block;
            text-align: center;
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

        /* Form Styles */
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

        /* Payment Options */
        .payment-options {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin: 15px 0;
        }

        .payment-option {
            display: flex;
            align-items: center;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .payment-option:hover {
            background-color: #f9f9f9;
        }

        .payment-option input {
            margin-right: 10px;
        }

        .payment-option.paypal {
            border-color: var(--paypal-color);
            background-color: rgba(255, 196, 57, 0.1);
        }

        .payment-option.paypal.selected {
            background-color: var(--paypal-color);
            color: #253b80;
        }

        .payment-icon {
            margin-right: 10px;
            font-size: 20px;
        }

        /* Payment Details Form */
        .payment-details {
            display: none;
            margin-top: 15px;
            padding: 15px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            background-color: #f9f9f9;
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

        /* Make Payment Page with Scroll */
        #make-payment-page {
            display: none;
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            max-height: 80vh;
            overflow-y: auto;
        }

        #make-payment-page.active {
            display: block;
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
            max-width: 500px;
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

        /* Chart Container */
        .chart-container {
            height: 300px;
            margin-top: 20px;
        }

        /* Loading Spinner */
        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top: 4px solid #3498db;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Custom Scrollbar for Make Payment Page */
        #make-payment-page::-webkit-scrollbar {
            width: 8px;
        }

        #make-payment-page::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        #make-payment-page::-webkit-scrollbar-thumb {
            background-color: #c1c1c1;
            border-radius: 4px;
        }

        #make-payment-page::-webkit-scrollbar-thumb:hover {
            background-color: #a8a8a8;
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
            
            <li class="nav-menu-title">My Orders</li>
            <li class="nav-item" onclick="showPage('new-order')">
                <i class="fas fa-plus-circle"></i>
                <span>Place New Order</span>
            </li>
            <li class="nav-item" onclick="showPage('track-orders')">
                <i class="fas fa-search"></i>
                <span>Track Orders</span>
            </li>
            <li class="nav-item" onclick="showPage('cancel-orders')">
                <i class="fas fa-times-circle"></i>
                <span>Cancel Orders</span>
            </li>
            
            <li class="nav-menu-title">Payments</li>
            <li class="nav-item" onclick="showPage('make-payment')">
                <i class="fas fa-money-bill-wave"></i>
                <span>Make Payment</span>
            </li>
            <li class="nav-item" onclick="showPage('payment-history')">
                <i class="fas fa-history"></i>
                <span>Payment History</span>
            </li>
            
            <li class="nav-menu-title">Profile</li>
            <li class="nav-item" onclick="showPage('profile')">
                <i class="fas fa-user-cog"></i>
                <span>Profile Settings</span>
            </li>
            <li class="nav-item" onclick="showPage('notifications')">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
            </li>
            <li class="nav-item" onclick="showDeleteConfirm()">
                <i class="fas fa-trash-alt"></i>
                <span>Delete Account</span>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1 id="page-title">Customer Dashboard</h1>
            <div class="user-info">
                <img id="user-avatar" src="" alt="User">
                <span id="user-name">Loading...</span>
                <div class="dropdown-menu">
                    <a href="#" onclick="showPage('profile')"><i class="fas fa-user"></i> Profile</a>
                    <a href="#" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>

        <!-- Dashboard Page -->
        <div id="dashboard-page" class="page-content active">
            <!-- Stats Cards -->
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon orders">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Active Orders</div>
                        <div class="stat-value" id="active-orders-count">0</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon payments">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Pending Payments</div>
                        <div class="stat-value" id="pending-payments-count">0</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon delivered">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Delivered Orders</div>
                        <div class="stat-value" id="delivered-orders-count">0</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon login">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Last Login</div>
                        <div class="stat-value" id="last-login">Loading...</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <div class="action-btn" onclick="showPage('new-order')">
                    <i class="fas fa-plus-circle"></i>
                    <span>Place New Order</span>
                </div>
                <div class="action-btn" onclick="showPage('track-orders')">
                    <i class="fas fa-search"></i>
                    <span>Track Orders</span>
                </div>
                <div class="action-btn" onclick="showPage('make-payment')">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Make Payment</span>
                </div>
                <div class="action-btn" onclick="showPage('profile')">
                    <i class="fas fa-user-cog"></i>
                    <span>Update Profile</span>
                </div>
            </div>

            <div class="dashboard-sections">
                <div class="section">
                    <h2><i class="fas fa-clipboard-list"></i> Active Orders</h2>
                    <div id="active-orders-table">
                        <div class="spinner"></div>
                    </div>
                </div>

                <div class="section">
                    <h2><i class="fas fa-money-bill-wave"></i> Pending Payments</h2>
                    <div id="pending-payments-table">
                        <div class="spinner"></div>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2><i class="fas fa-chart-line"></i> Order History</h2>
                <div class="chart-container">
                    <canvas id="ordersChart"></canvas>
                </div>
            </div>
        </div>

        <!-- New Order Page -->
        <div id="new-order-page" class="page-content">
            <h2><i class="fas fa-plus-circle"></i> Place New Order</h2>
            
            <form id="orderForm">
                <div class="form-group">
                    <label for="clothingType">Clothing Type</label>
                    <select id="clothingType" required>
                        <option value="">-- Select clothing type --</option>
                        <option value="suit">Suit</option>
                        <option value="shirt">Shirt</option>
                        <option value="pants">Pants</option>
                        <option value="dress">Dress</option>
                        <option value="gown">Gown</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="designUpload">Upload Design (Optional)</label>
                    <input type="file" id="designUpload">
                </div>
                
                <div class="form-group">
                    <label>Measurements (inches)</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label>Chest</label>
                            <input type="number" id="chest-measurement" placeholder="38" min="20" max="60">
                        </div>
                        <div>
                            <label>Waist</label>
                            <input type="number" id="waist-measurement" placeholder="32" min="20" max="60">
                        </div>
                        <div>
                            <label>Hips</label>
                            <input type="number" id="hips-measurement" placeholder="40" min="20" max="60">
                        </div>
                        <div>
                            <label>Length</label>
                            <input type="number" id="length-measurement" placeholder="30" min="20" max="60">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="deliveryDate">Delivery Date</label>
                    <input type="date" id="deliveryDate" required>
                </div>
                
                <div class="form-group">
                    <label>Payment Option</label>
                    <div class="payment-options">
                        <label class="payment-option">
                            <input type="radio" name="payment" value="credit" checked>
                            <i class="fas fa-credit-card payment-icon"></i>
                            Credit Card
                        </label>
                        <label class="payment-option paypal">
                            <input type="radio" name="payment" value="paypal">
                            <i class="fab fa-cc-paypal payment-icon"></i>
                            PayPal
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="payment" value="mpesa">
                            <i class="fas fa-mobile-alt payment-icon"></i>
                            M-Pesa
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="payment" value="cash">
                            <i class="fas fa-money-bill-wave payment-icon"></i>
                            Cash on Delivery
                        </label>
                    </div>
                    
                    <!-- Payment Details Forms -->
                    <div id="credit-details" class="payment-details">
                        <div class="form-group">
                            <label>Card Number</label>
                            <input type="text" id="cardNumber" placeholder="1234 5678 9012 3456">
                        </div>
                        <div class="form-group">
                            <label>Expiry Date</label>
                            <input type="text" id="expiryDate" placeholder="MM/YY">
                        </div>
                        <div class="form-group">
                            <label>CVV</label>
                            <input type="text" id="cvv" placeholder="123">
                        </div>
                    </div>
                    
                    <div id="paypal-details" class="payment-details">
                        <button type="button" id="paypal-button" class="btn" style="background-color: var(--paypal-color); color: #253b80;">
                            <i class="fab fa-cc-paypal"></i> Pay with PayPal
                        </button>
                    </div>
                    
                    <div id="mpesa-details" class="payment-details">
                        <div class="form-group">
                            <label>M-Pesa Phone Number</label>
                            <input type="tel" id="mpesaNumber" placeholder="254712345678">
                        </div>
                        <button type="button" id="mpesa-button" class="btn btn-success">
                            <i class="fas fa-mobile-alt"></i> Request Payment
                        </button>
                    </div>
                    
                    <div id="cash-details" class="payment-details">
                        <p>Pay with cash when you pickup your order.</p>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-success">Submit Order</button>
                    <button type="button" class="btn btn-secondary" onclick="showPage('dashboard')">Cancel</button>
                </div>
            </form>
        </div>

        <!-- Track Orders Page -->
        <div id="track-orders-page" class="page-content">
            <h2><i class="fas fa-search"></i> Track Orders</h2>
            
            <div id="track-orders-table">
                <div class="spinner"></div>
            </div>
        </div>

        <!-- Cancel Orders Page -->
        <div id="cancel-orders-page" class="page-content">
            <h2><i class="fas fa-times-circle"></i> Cancel Orders</h2>
            
            <div id="cancel-orders-table">
                <div class="spinner"></div>
            </div>
        </div>

        <!-- Make Payment Page -->
        <div id="make-payment-page" class="page-content">
            <h2><i class="fas fa-money-bill-wave"></i> Make Payment</h2>
            
            <div id="make-payment-table">
                <div class="spinner"></div>
            </div>
        </div>

        <!-- Payment History Page -->
        <div id="payment-history-page" class="page-content">
            <h2><i class="fas fa-history"></i> Payment History</h2>
            
            <div id="payment-history-table">
                <div class="spinner"></div>
            </div>
        </div>

        <!-- Profile Page -->
        <div id="profile-page" class="page-content">
            <h2><i class="fas fa-user-cog"></i> Profile Settings</h2>
            
            <form id="profileForm">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" required>
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" rows="3" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="phone">Contact Number</label>
                    <input type="tel" id="phone" required>
                </div>
                
                <div class="form-group">
                    <label>Measurements (inches)</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label>Chest</label>
                            <input type="number" id="profile-chest" min="20" max="60">
                        </div>
                        <div>
                            <label>Waist</label>
                            <input type="number" id="profile-waist" min="20" max="60">
                        </div>
                        <div>
                            <label>Hips</label>
                            <input type="number" id="profile-hips" min="20" max="60">
                        </div>
                        <div>
                            <label>Length</label>
                            <input type="number" id="profile-length" min="20" max="60">
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn">Save Changes</button>
            </form>
        </div>

        <!-- Notifications Page -->
        <div id="notifications-page" class="page-content">
            <h2><i class="fas fa-bell"></i> Notifications</h2>
            
            <div id="notifications-table">
                <div class="spinner"></div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-money-bill-wave"></i> Make Payment</h2>
                <button class="close-btn" onclick="closeModal('paymentModal')">&times;</button>
            </div>
            
            <form id="paymentForm">
                <div class="form-group">
                    <label for="paymentOrder">Order ID</label>
                    <input type="text" id="paymentOrder" readonly>
                </div>
                
                <div class="form-group">
                    <label for="paymentAmount">Amount</label>
                    <input type="text" id="paymentAmount" readonly>
                </div>
                
                <div class="form-group">
                    <label>Payment Method</label>
                    <div class="payment-options">
                        <label class="payment-option">
                            <input type="radio" name="paymentMethod" value="credit" checked>
                            <i class="fas fa-credit-card payment-icon"></i>
                            Credit Card
                        </label>
                        <label class="payment-option paypal">
                            <input type="radio" name="paymentMethod" value="paypal">
                            <i class="fab fa-cc-paypal payment-icon"></i>
                            PayPal
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="paymentMethod" value="mpesa">
                            <i class="fas fa-mobile-alt payment-icon"></i>
                            M-Pesa
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="paymentMethod" value="bank">
                            <i class="fas fa-university payment-icon"></i>
                            Bank Transfer
                        </label>
                    </div>
                </div>
                
                <div id="creditCardDetails" class="payment-details">
                    <div class="form-group">
                        <label>Card Number</label>
                        <input type="text" id="modalCardNumber" placeholder="1234 5678 9012 3456">
                    </div>
                    <div class="form-group">
                        <label>Expiry Date</label>
                        <input type="text" id="modalExpiryDate" placeholder="MM/YY">
                    </div>
                    <div class="form-group">
                        <label>CVV</label>
                        <input type="text" id="modalCvv" placeholder="123">
                    </div>
                </div>
                
                <div id="paypalDetails" class="payment-details" style="display: none;">
                    <button type="button" id="modalPaypalButton" class="btn" style="background-color: var(--paypal-color); color: #253b80;">
                        <i class="fab fa-cc-paypal"></i> Pay with PayPal
                    </button>
                </div>
                
                <div id="mpesaDetails" class="payment-details" style="display: none;">
                    <div class="form-group">
                        <label>M-Pesa Phone Number</label>
                        <input type="tel" id="modalMpesaNumber" placeholder="254712345678">
                    </div>
                    <button type="button" id="modalMpesaButton" class="btn btn-success">
                        <i class="fas fa-mobile-alt"></i> Request Payment
                    </button>
                </div>
                
                <div id="bankDetails" class="payment-details" style="display: none;">
                    <div class="form-group">
                        <label>Bank Name</label>
                        <input type="text" id="bankName" placeholder="e.g. Equity Bank">
                    </div>
                    <div class="form-group">
                        <label>Account Number</label>
                        <input type="text" id="accountNumber" placeholder="1234567890">
                    </div>
                </div>
                
                <div style="margin-top: 20px;">
                    <button type="submit" class="btn btn-success">Confirm Payment</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('paymentModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-clipboard-list"></i> Order Details</h2>
                <button class="close-btn" onclick="closeModal('orderDetailsModal')">&times;</button>
            </div>
            
            <div id="orderDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-exclamation-triangle"></i> Confirm Account Deletion</h2>
                <button class="close-btn" onclick="closeModal('deleteModal')">&times;</button>
            </div>
            
            <p>Are you sure you want to delete your account? This action cannot be undone.</p>
            <p>All your order history and personal information will be permanently removed.</p>
            
            <div style="margin-top: 20px;">
                <button class="btn btn-danger" onclick="deleteAccount()">Yes, Delete My Account</button>
                <button class="btn btn-secondary" onclick="closeModal('deleteModal')">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div id="notification-toast" class="notification">
        <i class="fas fa-check-circle"></i>
        <span id="notification-message">Action completed successfully!</span>
    </div>

    <script>
        // API Configuration
        const API_BASE_URL = 'http://localhost/MyTailorSuite/';
        let authToken = 'mock-token'; // For demo purposes
        let currentUser = {};
        let orders = [];
        let payments = [];
        let notifications = [];
        let ordersChart = null;

        // Initialize the dashboard
        document.addEventListener('DOMContentLoaded', function() {
            loadUserProfile();
            loadOrders();
            loadPayments();
            loadNotifications();
            initializeForms();
            setupEventListeners();
            setMinDeliveryDate();
        });

        function setMinDeliveryDate() {
            const today = new Date();
            const minDate = new Date(today.setDate(today.getDate() + 7)); // Minimum 7 days from today
            const formattedDate = minDate.toISOString().split('T')[0];
            document.getElementById('deliveryDate').min = formattedDate;
        }

        function loadUserProfile() {
            axios.get(`${API_BASE_URL}/profile.php`)
                .then(response => {
                    currentUser = response.data;
                    updateUserInfo();
                    populateProfileForm();
                })
                .catch(error => {
                    console.error('Error loading user profile:', error);
                    showNotification('Failed to load profile data', 'error');
                });
        }

        function loadOrders() {
            axios.get(`${API_BASE_URL}/orders.php`)
                .then(response => {
                    orders = response.data;
                    updateStats();
                    renderActiveOrders();
                    renderTrackOrders();
                    renderCancelOrders();
                    renderOrdersChart();
                })
                .catch(error => {
                    console.error('Error loading orders:', error);
                    showNotification('Failed to load orders', 'error');
                });
        }

        function loadPayments() {
            axios.get(`${API_BASE_URL}/payments.php`)
                .then(response => {
                    payments = response.data;
                    renderPendingPayments();
                    renderPaymentHistory();
                    renderMakePayments();
                })
                .catch(error => {
                    console.error('Error loading payments:', error);
                    showNotification('Failed to load payment history', 'error');
                });
        }

        function loadNotifications() {
            axios.get(`${API_BASE_URL}/notifications.php`)
                .then(response => {
                    notifications = response.data;
                    renderNotifications();
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                    showNotification('Failed to load notifications', 'error');
                });
        }

        function updateUserInfo() {
            document.getElementById('user-name').textContent = currentUser.name || 'User';
            document.getElementById('user-avatar').src = currentUser.avatar || 'https://via.placeholder.com/40';
            document.getElementById('last-login').textContent = currentUser.lastLogin ? 
                new Date(currentUser.lastLogin).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 
                'Not available';
        }

        function populateProfileForm() {
            document.getElementById('email').value = currentUser.email || '';
            document.getElementById('address').value = currentUser.address || '';
            document.getElementById('phone').value = currentUser.phone || '';
            
            if (currentUser.measurements) {
                document.getElementById('profile-chest').value = currentUser.measurements.chest || '';
                document.getElementById('profile-waist').value = currentUser.measurements.waist || '';
                document.getElementById('profile-hips').value = currentUser.measurements.hips || '';
                document.getElementById('profile-length').value = currentUser.measurements.length || '';
            }
        }

        function updateStats() {
            const activeOrders = orders.filter(order => order.status === 'In Progress' || order.status === 'Pending').length;
            const pendingPayments = orders.filter(order => order.paymentStatus === 'Pending').length;
            const deliveredOrders = orders.filter(order => order.status === 'Delivered').length;
            
            document.getElementById('active-orders-count').textContent = activeOrders;
            document.getElementById('pending-payments-count').textContent = pendingPayments;
            document.getElementById('delivered-orders-count').textContent = deliveredOrders;
        }

        function renderActiveOrders() {
            const activeOrders = orders.filter(order => order.status === 'In Progress' || order.status === 'Pending');
            const container = document.getElementById('active-orders-table');
            
            if (activeOrders.length === 0) {
                container.innerHTML = '<p>No active orders found.</p>';
                return;
            }
            
            let html = `
                <table class="order-table">
                    <tr>
                        <th>Order ID</th>
                        <th>Garment</th>
                        <th>Status</th>
                        <th>Delivery Date</th>
                    </tr>
            `;
            
            activeOrders.forEach(order => {
                const statusClass = order.status === 'In Progress' ? 'status-in-progress' : 
                                  order.status === 'Delivered' ? 'status-delivered' : 'status-pending';
                
                html += `
                    <tr>
                        <td class="order-id">#${order.id}</td>
                        <td>${order.type}</td>
                        <td><span class="order-status ${statusClass}">${order.status}</span></td>
                        <td>${new Date(order.deliveryDate).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })} 
                            <button class="btn btn-sm" onclick="viewOrderDetails('${order.id}')">→</button>
                        </td>
                    </tr>
                `;
            });
            
            html += `</table>`;
            container.innerHTML = html;
        }

        function renderPendingPayments() {
            const pendingPayments = orders.filter(order => order.paymentStatus === 'Pending');
            const container = document.getElementById('pending-payments-table');
            
            if (pendingPayments.length === 0) {
                container.innerHTML = '<p>No pending payments found.</p>';
                return;
            }
            
            let html = `
                <table class="order-table">
                    <tr>
                        <th>Order ID</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Action</th>
                    </tr>
            `;
            
            pendingPayments.forEach(order => {
                html += `
                    <tr>
                        <td class="order-id">#${order.id}</td>
                        <td>$${order.amount.toFixed(2)}</td>
                        <td>${new Date(order.deliveryDate).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</td>
                        <td><button class="btn btn-sm" onclick="showPaymentModal('${order.id}')">Pay Now</button></td>
                    </tr>
                `;
            });
            
            html += `</table>`;
            container.innerHTML = html;
        }

        function renderTrackOrders() {
            const container = document.getElementById('track-orders-table');
            
            if (orders.length === 0) {
                container.innerHTML = '<p>No orders found.</p>';
                return;
            }
            
            let html = `
                <table class="order-table">
                    <tr>
                        <th>Order ID</th>
                        <th>Garment</th>
                        <th>Status</th>
                        <th>Delivery Date</th>
                        <th>Action</th>
                    </tr>
            `;
            
            orders.forEach(order => {
                const statusClass = order.status === 'In Progress' ? 'status-in-progress' : 
                                  order.status === 'Delivered' ? 'status-delivered' : 'status-pending';
                
                html += `
                    <tr>
                        <td class="order-id">#${order.id}</td>
                        <td>${order.type}</td>
                        <td><span class="order-status ${statusClass}">${order.status}</span></td>
                        <td>${new Date(order.deliveryDate).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</td>
                        <td><button class="btn btn-sm" onclick="viewOrderDetails('${order.id}')">View</button></td>
                    </tr>
                `;
            });
            
            html += `</table>`;
            container.innerHTML = html;
        }

        function renderCancelOrders() {
            const cancelableOrders = orders.filter(order => order.status === 'Pending' || order.status === 'In Progress');
            const container = document.getElementById('cancel-orders-table');
            
            if (cancelableOrders.length === 0) {
                container.innerHTML = '<p>No orders available for cancellation.</p>';
                return;
            }
            
            let html = `
                <table class="order-table">
                    <tr>
                        <th>Order ID</th>
                        <th>Garment</th>
                        <th>Status</th>
                        <th>Delivery Date</th>
                        <th>Action</th>
                    </tr>
            `;
            
            cancelableOrders.forEach(order => {
                const statusClass = order.status === 'In Progress' ? 'status-in-progress' : 'status-pending';
                
                html += `
                    <tr>
                        <td class="order-id">#${order.id}</td>
                        <td>${order.type}</td>
                        <td><span class="order-status ${statusClass}">${order.status}</span></td>
                        <td>${new Date(order.deliveryDate).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</td>
                        <td><button class="btn btn-danger btn-sm" onclick="cancelOrder('${order.id}')">Cancel</button></td>
                    </tr>
                `;
            });
            
            html += `</table>`;
            container.innerHTML = html;
        }

        function renderMakePayments() {
            const pendingPayments = orders.filter(order => order.paymentStatus === 'Pending');
            const container = document.getElementById('make-payment-table');
            
            if (pendingPayments.length === 0) {
                container.innerHTML = '<p>No payments due at this time.</p>';
                return;
            }
            
            let html = `
                <table class="order-table">
                    <tr>
                        <th>Order ID</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Action</th>
                    </tr>
            `;
            
            pendingPayments.forEach(order => {
                html += `
                    <tr>
                        <td class="order-id">#${order.id}</td>
                        <td>$${order.amount.toFixed(2)}</td>
                        <td>${new Date(order.deliveryDate).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</td>
                        <td><button class="btn btn-sm" onclick="showPaymentModal('${order.id}')">Pay Now</button></td>
                    </tr>
                `;
            });
            
            html += `</table>`;
            container.innerHTML = html;
        }

        function renderPaymentHistory() {
            const container = document.getElementById('payment-history-table');
            
            if (payments.length === 0) {
                container.innerHTML = '<p>No payment history found.</p>';
                return;
            }
            
            let html = `
                <table class="order-table">
                    <tr>
                        <th>Payment ID</th>
                        <th>Order ID</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Method</th>
                    </tr>
            `;
            
            payments.forEach(payment => {
                html += `
                    <tr>
                        <td>#${payment.id}</td>
                        <td class="order-id">#${payment.orderId}</td>
                        <td>$${payment.amount.toFixed(2)}</td>
                        <td>${new Date(payment.date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</td>
                        <td>${payment.method}</td>
                    </tr>
                `;
            });
            
            html += `</table>`;
            container.innerHTML = html;
        }

        function renderNotifications() {
            const container = document.getElementById('notifications-table');
            
            if (notifications.length === 0) {
                container.innerHTML = '<p>No notifications found.</p>';
                return;
            }
            
            let html = `
                <table class="order-table">
                    <tr>
                        <th>Date</th>
                        <th>Message</th>
                        <th>Action</th>
                    </tr>
            `;
            
            notifications.forEach(notification => {
                html += `
                    <tr>
                        <td>${new Date(notification.date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</td>
                        <td>${notification.message}</td>
                        <td>
                            ${notification.orderId ? 
                                `<button class="btn btn-sm" onclick="viewOrderDetails('${notification.orderId}')">View</button>` : 
                                ''}
                        </td>
                    </tr>
                `;
            });
            
            html += `</table>`;
            container.innerHTML = html;
        }

        function renderOrdersChart() {
            const ctx = document.getElementById('ordersChart').getContext('2d');
            
            // Group orders by month
            const ordersByMonth = {};
            orders.forEach(order => {
                const date = new Date(order.createdAt || order.deliveryDate);
                const monthYear = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
                
                if (!ordersByMonth[monthYear]) {
                    ordersByMonth[monthYear] = 0;
                }
                ordersByMonth[monthYear]++;
            });
            
            // Sort months chronologically
            const sortedMonths = Object.keys(ordersByMonth).sort();
            const labels = sortedMonths.map(month => {
                const [year, monthNum] = month.split('-');
                return new Date(year, monthNum - 1).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            });
            const data = sortedMonths.map(month => ordersByMonth[month]);
            
            if (ordersChart) {
                ordersChart.destroy();
            }
            
            ordersChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Orders',
                        data: data,
                        backgroundColor: 'rgba(52, 152, 219, 0.7)',
                        borderColor: 'rgba(52, 152, 219, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }

        function initializeForms() {
            // Profile form submission
            document.getElementById('profileForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const updatedProfile = {
                    ...currentUser,
                    email: document.getElementById('email').value,
                    address: document.getElementById('address').value,
                    phone: document.getElementById('phone').value,
                    measurements: {
                        chest: parseInt(document.getElementById('profile-chest').value) || 0,
                        waist: parseInt(document.getElementById('profile-waist').value) || 0,
                        hips: parseInt(document.getElementById('profile-hips').value) || 0,
                        length: parseInt(document.getElementById('profile-length').value) || 0
                    }
                };
                
                // In a real app, this would be a PUT request to the API
                currentUser = updatedProfile;
                updateUserInfo();
                showNotification('Profile updated successfully!', 'success');
            });
            
            // Order form submission
            document.getElementById('orderForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const paymentMethod = document.querySelector('#orderForm input[name="payment"]:checked').value;
                const paymentStatus = paymentMethod === 'cash' ? 'Pending' : 'Paid';
                
                const newOrder = {
                    id: 'ORD' + Math.floor(1000 + Math.random() * 9000),
                    type: document.getElementById('clothingType').value,
                    measurements: {
                        chest: parseInt(document.getElementById('chest-measurement').value) || 0,
                        waist: parseInt(document.getElementById('waist-measurement').value) || 0,
                        hips: parseInt(document.getElementById('hips-measurement').value) || 0,
                        length: parseInt(document.getElementById('length-measurement').value) || 0
                    },
                    deliveryDate: document.getElementById('deliveryDate').value,
                    paymentMethod: paymentMethod,
                    paymentStatus: paymentStatus,
                    status: "Pending",
                    amount: Math.floor(50 + Math.random() * 200),
                    createdAt: new Date().toISOString(),
                    tailor: "Not assigned yet"
                };
                
                // In a real app, this would be a POST request to the API
                orders.unshift(newOrder);
                
                // If payment was made, create a payment record
                if (paymentStatus === 'Paid') {
                    const newPayment = {
                        id: 'PAY' + Math.floor(2000 + Math.random() * 1000),
                        orderId: newOrder.id,
                        amount: newOrder.amount,
                        date: new Date().toISOString(),
                        method: paymentMethod
                    };
                    payments.unshift(newPayment);
                }
                
                // Update UI
                updateStats();
                renderActiveOrders();
                renderTrackOrders();
                renderCancelOrders();
                renderMakePayments();
                renderPaymentHistory();
                renderOrdersChart();
                
                showNotification('Order placed successfully!', 'success');
                showPage('track-orders');
            });
            
            // Payment form submission
            document.getElementById('paymentForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const orderId = document.getElementById('paymentOrder').value;
                const orderIndex = orders.findIndex(o => o.id === orderId);
                
                if (orderIndex !== -1) {
                    const paymentMethod = document.querySelector('#paymentForm input[name="paymentMethod"]:checked').value;
                    
                    // Update order
                    orders[orderIndex].paymentStatus = 'Paid';
                    orders[orderIndex].paymentMethod = paymentMethod;
                    
                    // Create payment record
                    const newPayment = {
                        id: 'PAY' + Math.floor(2000 + Math.random() * 1000),
                        orderId: orderId,
                        amount: parseFloat(document.getElementById('paymentAmount').value.replace('$', '')),
                        date: new Date().toISOString(),
                        method: paymentMethod
                    };
                    
                    payments.unshift(newPayment);
                    
                    // Update UI
                    updateStats();
                    renderActiveOrders();
                    renderPendingPayments();
                    renderMakePayments();
                    renderPaymentHistory();
                    
                    showNotification('Payment processed successfully!', 'success');
                    closeModal('paymentModal');
                    showPage('payment-history');
                }
            });
        }

        function setupEventListeners() {
            // Payment method change handler in order form
            document.querySelectorAll('#orderForm input[name="payment"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    document.getElementById('credit-details').style.display = 'none';
                    document.getElementById('paypal-details').style.display = 'none';
                    document.getElementById('mpesa-details').style.display = 'none';
                    document.getElementById('cash-details').style.display = 'none';
                    
                    if (this.value === 'credit') {
                        document.getElementById('credit-details').style.display = 'block';
                    } else if (this.value === 'paypal') {
                        document.getElementById('paypal-details').style.display = 'block';
                    } else if (this.value === 'mpesa') {
                        document.getElementById('mpesa-details').style.display = 'block';
                    } else if (this.value === 'cash') {
                        document.getElementById('cash-details').style.display = 'block';
                    }
                });
            });
            
            // Payment method change handler in payment modal
            document.querySelectorAll('#paymentForm input[name="paymentMethod"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    document.getElementById('creditCardDetails').style.display = 'none';
                    document.getElementById('paypalDetails').style.display = 'none';
                    document.getElementById('mpesaDetails').style.display = 'none';
                    document.getElementById('bankDetails').style.display = 'none';
                    
                    if (this.value === 'credit') {
                        document.getElementById('creditCardDetails').style.display = 'block';
                    } else if (this.value === 'paypal') {
                        document.getElementById('paypalDetails').style.display = 'block';
                    } else if (this.value === 'mpesa') {
                        document.getElementById('mpesaDetails').style.display = 'block';
                    } else if (this.value === 'bank') {
                        document.getElementById('bankDetails').style.display = 'block';
                    }
                });
            });
            
            // Initialize with first payment method selected
            document.querySelector('#orderForm input[value="credit"]').dispatchEvent(new Event('change'));
            document.querySelector('#paymentForm input[value="credit"]').dispatchEvent(new Event('change'));
            
            // PayPal button handlers
            document.getElementById('paypal-button')?.addEventListener('click', initiatePaypalPayment);
            document.getElementById('modalPaypalButton')?.addEventListener('click', initiatePaypalPayment);
            
            // M-Pesa button handlers
            document.getElementById('mpesa-button')?.addEventListener('click', initiateMpesaPayment);
            document.getElementById('modalMpesaButton')?.addEventListener('click', initiateMpesaPayment);
        }

        function initiatePaypalPayment() {
            const orderId = document.getElementById('paymentOrder')?.value || 'NEW';
            const amount = document.getElementById('paymentAmount')?.value.replace('$', '') || 
                          Math.floor(50 + Math.random() * 200);
            
            showNotification('Redirecting to PayPal for payment...', 'info');
            
            // In a real app, this would call your backend to create a PayPal order
            setTimeout(() => {
                showNotification('PayPal payment completed successfully!', 'success');
                
                if (orderId === 'NEW') {
                    // For new orders
                    showPage('track-orders');
                } else {
                    // For existing orders
                    closeModal('paymentModal');
                    showPage('payment-history');
                }
            }, 2000);
        }

        function initiateMpesaPayment() {
            const phoneNumber = document.getElementById('mpesaNumber')?.value || 
                              document.getElementById('modalMpesaNumber')?.value;
            
            if (!phoneNumber) {
                showNotification('Please enter your M-Pesa phone number', 'error');
                return;
            }
            
            showNotification('Initiating M-Pesa payment request...', 'info');
            
            // In a real app, this would call your backend to initiate M-Pesa payment
            setTimeout(() => {
                showNotification('M-Pesa payment request sent. Check your phone to complete payment.', 'success');
                
                // Simulate payment completion after delay
                setTimeout(() => {
                    showNotification('M-Pesa payment completed successfully!', 'success');
                    
                    const orderId = document.getElementById('paymentOrder')?.value;
                    if (orderId && orderId !== 'NEW') {
                        closeModal('paymentModal');
                        showPage('payment-history');
                    }
                }, 3000);
            }, 1000);
        }

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
                'dashboard': 'Customer Dashboard',
                'new-order': 'Place New Order',
                'track-orders': 'Track Orders',
                'cancel-orders': 'Cancel Orders',
                'make-payment': 'Make Payment',
                'payment-history': 'Payment History',
                'profile': 'Profile Settings',
                'notifications': 'Notifications'
            };
            document.getElementById('page-title').textContent = pageTitles[pageId] || 'Customer Dashboard';
            
            // Update active nav item
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Set the appropriate nav item as active
            if (pageId === 'dashboard') {
                document.querySelector('.nav-item[onclick="showPage(\'dashboard\')"]').classList.add('active');
            } else if (pageId === 'new-order') {
                document.querySelector('.nav-item[onclick="showPage(\'new-order\')"]').classList.add('active');
            } else if (pageId === 'track-orders' || pageId === 'cancel-orders') {
                document.querySelector('.nav-item[onclick="showPage(\'track-orders\')"]').classList.add('active');
            } else if (pageId === 'make-payment' || pageId === 'payment-history') {
                document.querySelector('.nav-item[onclick="showPage(\'make-payment\')"]').classList.add('active');
            } else if (pageId === 'profile') {
                document.querySelector('.nav-item[onclick="showPage(\'profile\')"]').classList.add('active');
            } else if (pageId === 'notifications') {
                document.querySelector('.nav-item[onclick="showPage(\'notifications\')"]').classList.add('active');
            }
        }

        function showPaymentModal(orderId) {
            const order = orders.find(o => o.id === orderId);
            if (order) {
                document.getElementById('paymentOrder').value = order.id;
                document.getElementById('paymentAmount').value = `$${order.amount.toFixed(2)}`;
            }
            document.getElementById('paymentModal').style.display = 'flex';
        }

        function viewOrderDetails(orderId) {
            const order = orders.find(o => o.id === orderId);
            const detailsContent = document.getElementById('orderDetailsContent');
            
            if (order) {
                const statusClass = order.status === 'In Progress' ? 'status-in-progress' : 
                                    order.status === 'Delivered' ? 'status-delivered' : 'status-pending';
                
                detailsContent.innerHTML = `
                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <div>
                                <strong>Order ID:</strong> #${order.id}
                            </div>
                            <div>
                                <span class="order-status ${statusClass}">
                                    ${order.status}
                                </span>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                            <div>
                                <strong>Garment Type:</strong> ${order.type}
                            </div>
                            <div>
                                <strong>Order Date:</strong> ${new Date(order.createdAt).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}
                            </div>
                            <div>
                                <strong>${order.status === 'Delivered' ? 'Delivered On' : 'Estimated Delivery'}:</strong> 
                                ${new Date(order.deliveryDate).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}
                            </div>
                            <div>
                                <strong>Amount:</strong> $${order.amount.toFixed(2)}
                            </div>
                        </div>
                        
                        <div style="margin: 15px 0;">
                            <strong>Measurements:</strong>
                            <div style="margin-top: 5px; padding: 10px; background-color: #f9f9f9; border-radius: 4px;">
                                ${order.measurements.chest ? `Chest: ${order.measurements.chest}"` : ''}
                                ${order.measurements.waist ? `Waist: ${order.measurements.waist}"` : ''}
                                ${order.measurements.hips ? `Hips: ${order.measurements.hips}"` : ''}
                                ${order.measurements.length ? `Length: ${order.measurements.length}"` : ''}
                            </div>
                        </div>
                        
                        <div style="margin: 15px 0;">
                            <strong>Payment Status:</strong>
                            <div style="margin-top: 5px; padding: 10px; background-color: #f9f9f9; border-radius: 4px;">
                                ${order.paymentStatus === 'Paid' ? 'Paid' : 'Pending'} 
                                ${order.paymentMethod ? `(${order.paymentMethod})` : ''}
                            </div>
                        </div>
                        
                        <div style="margin: 15px 0;">
                            <strong>Assigned Tailor:</strong>
                            <div style="margin-top: 5px; padding: 10px; background-color: #f9f9f9; border-radius: 4px;">
                                ${order.tailor || 'Not assigned yet'}
                            </div>
                        </div>
                        
                        ${order.status === 'Delivered' ? `
                        <div style="text-align: center; margin-top: 20px;">
                            <button class="btn btn-success" onclick="showFeedbackModal('${order.id}')">
                                <i class="fas fa-comment-alt"></i> Leave Feedback
                            </button>
                        </div>
                        ` : ''}
                    </div>
                `;
                
                document.getElementById('orderDetailsModal').style.display = 'flex';
            } else {
                showNotification('Order details not found', 'error');
            }
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function cancelOrder(orderId) {
            if (!confirm('Are you sure you want to cancel this order?')) {
                return;
            }
            
            // In a real app, this would be a DELETE request to the API
            const orderIndex = orders.findIndex(o => o.id === orderId);
            if (orderIndex !== -1) {
                orders.splice(orderIndex, 1);
            }
            
            // Update UI
            updateStats();
            renderActiveOrders();
            renderTrackOrders();
            renderCancelOrders();
            renderOrdersChart();
            
            showNotification(`Order #${orderId} has been cancelled`, 'success');
            showPage('track-orders');
        }

        function deleteAccount() {
            if (!confirm('Are you sure you want to delete your account? This cannot be undone.')) {
                return;
            }
            
            // In a real app, this would be a DELETE request to the API
            showNotification('Your account has been deleted successfully.', 'success');
            closeModal('deleteModal');
            
            // Simulate logout
            setTimeout(() => {
                window.location.href = '/';
            }, 2000);
        }

        function logout() {
            showNotification('You have been logged out successfully.', 'success');
            setTimeout(() => {
                window.location.href = '/login';
            }, 1500);
        }

        function showDeleteConfirm() {
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function showFeedbackModal(orderId) {
            showNotification('Feedback functionality would be implemented here', 'info');
        }

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
            } else if (type === 'info') {
                notification.style.backgroundColor = '#3498db';
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
    </script>
</body>
</html>