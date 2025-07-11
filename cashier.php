
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Dashboard - TailorSuite</title>
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
            --info-color: #3498db;
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

        /* Dashboard Sections */
        .dashboard-sections {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
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

        /* Stats Cards */
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
            padding: 15px;
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

        .stat-icon.payments {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--success-color);
        }

        .stat-icon.invoices {
            background-color: rgba(52, 152, 219, 0.1);
            color: var(--sidebar-active);
        }

        .stat-icon.reconciliation {
            background-color: rgba(241, 196, 15, 0.1);
            color: var(--warning-color);
        }

        .stat-icon.transactions {
            background-color: rgba(155, 89, 182, 0.1);
            color: #9b59b6;
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

        /* Table Styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .data-table th {
            text-align: left;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-secondary);
            font-weight: normal;
        }

        .data-table td {
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .order-id {
            font-weight: bold;
            color: var(--sidebar-active);
        }

        .status {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }

        .status-reconciled {
            background-color: #cce5ff;
            color: #004085;
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
            min-height: 80px;
            resize: vertical;
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

        .btn-warning {
            background-color: var(--warning-color);
        }

        .btn-warning:hover {
            background-color: #e0a800;
        }

        .btn-secondary {
            background-color: var(--text-secondary);
        }

        .btn-secondary:hover {
            background-color: #6c757d;
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

        .payment-icon {
            margin-right: 10px;
            font-size: 20px;
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

        /* Chart Container */
        .chart-container {
            width: 100%;
            height: 300px;
            margin: 20px 0;
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
            <i class="fas fa-cash-register"></i>
            <span>TailorSuite</span>
        </div>
        <ul class="nav-menu">
            <li class="nav-menu-title">Dashboard</li>
            <li class="nav-item active" onclick="showPage('dashboard')">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </li>
            
            <li class="nav-menu-title">Payments</li>
            <li class="nav-item" onclick="showPage('new-payment')">
                <i class="fas fa-money-bill-wave"></i>
                <span>New Payment</span>
                <span class="notification-badge" id="pending-payments-badge">3</span>
            </li>
            <li class="nav-item" onclick="showPage('generate-invoice')">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Generate Invoice</span>
            </li>
            
            <li class="nav-menu-title">Reports</li>
            <li class="nav-item" onclick="showPage('transaction-logs')">
                <i class="fas fa-clipboard-list"></i>
                <span>Transaction Logs</span>
            </li>
            <li class="nav-item" onclick="showPage('daily-reports')">
                <i class="fas fa-chart-line"></i>
                <span>Daily Reports</span>
            </li>
            <li class="nav-item" onclick="showPage('payment-settings')">
                <i class="fas fa-cog"></i>
                <span>Payment Settings</span>
            </li>
            
            <li class="nav-menu-title">Profile</li>
            <li class="nav-item" onclick="showPage('profile')">
                <i class="fas fa-user-cog"></i>
                <span>Profile Settings</span>
            </li>
            <li class="nav-item" onclick="logout()">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1 id="page-title">Cashier Dashboard</h1>
            
            <div class="user-info">
                <img src="https://via.placeholder.com/40" alt="User">
                <span>Cashier</span>
                <div class="dropdown-menu">
                    <a href="#" onclick="showPage('profile')"><i class="fas fa-user"></i> Profile</a>
                    <a href="#" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>

        <!-- Dashboard Page -->
        <div id="dashboard-page" class="page-content active">
            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon payments">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Today's Payments</div>
                        <div class="stat-value" id="todays-payments">$1,245.50</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon invoices">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Invoices Generated</div>
                        <div class="stat-value" id="invoices-count">8</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon reconciliation">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Last Reconciliation</div>
                        <div class="stat-value" id="last-reconciliation">Apr 2, 2025</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon transactions">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Total Transactions</div>
                        <div class="stat-value" id="total-transactions">42</div>
                    </div>
                </div>
            </div>

            <div class="dashboard-sections">
                <!-- New Payment Entry Section -->
                <div class="section">
                    <h2><i class="fas fa-money-bill-wave"></i> New Payment Entry</h2>
                    <form id="newPaymentForm">
                        <div class="form-group">
                            <label for="orderSelect">Order</label>
                            <select id="orderSelect" required>
                                <option value="">-- Select an order --</option>
                                <option value="ORD1234">ORD1234 - John - $150.00</option>
                                <option value="ORD1235">ORD1235 - Sarah - $85.50</option>
                                <option value="ORD1236">ORD1236 - Michael - $220.00</option>
                                <option value="ORD1237">ORD1237 - Linda - $120.00</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Payment Method</label>
                            <div class="payment-options">
                                <label class="payment-option">
                                    <input type="radio" name="paymentMethod" value="cash" checked>
                                    <i class="fas fa-money-bill-wave payment-icon"></i>
                                    Cash
                                </label>
                                <label class="payment-option">
                                    <input type="radio" name="paymentMethod" value="card">
                                    <i class="fas fa-credit-card payment-icon"></i>
                                    Card
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
                        
                        <div class="form-group">
                            <label for="transactionId">Transaction ID (if applicable)</label>
                            <input type="text" id="transactionId" placeholder="Enter transaction ID">
                        </div>
                        
                        <div class="form-group">
                            <label for="receiptUpload">Attach Receipt (Optional)</label>
                            <input type="file" id="receiptUpload">
                        </div>
                        
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check-circle"></i> Confirm Payment
                        </button>
                    </form>
                </div>

                <!-- Recent Transactions Section -->
                <div class="section">
                    <h2><i class="fas fa-history"></i> Recent Transactions</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Txn ID</th>
                                <th>Date</th>
                                <th>Method</th>
                                <th>Amount</th>
                                <th>Order ID</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>TX123</td>
                                <td>Apr 3, 2025</td>
                                <td>Card</td>
                                <td>$120.00</td>
                                <td class="order-id">ORD2345</td>
                                <td>
                                    <button class="btn btn-sm" onclick="viewTransaction('TX123')">View</button>
                                    <button class="btn btn-sm btn-secondary" onclick="generateReport('TX123')">Report</button>
                                </td>
                            </tr>
                            <tr>
                                <td>TX124</td>
                                <td>Apr 3, 2025</td>
                                <td>Cash</td>
                                <td>$250.00</td>
                                <td class="order-id">ORD2346</td>
                                <td>
                                    <button class="btn btn-sm" onclick="viewTransaction('TX124')">View</button>
                                    <button class="btn btn-sm btn-secondary" onclick="generateReport('TX124')">Report</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- New Payment Page -->
        <div id="new-payment-page" class="page-content">
            <h2><i class="fas fa-money-bill-wave"></i> New Payment</h2>
            
            <form id="paymentForm">
                <div class="form-group">
                    <label for="paymentOrderSelect">Order</label>
                    <select id="paymentOrderSelect" required>
                        <option value="">-- Select an order --</option>
                        <option value="ORD1234">ORD1234 - John - $150.00</option>
                        <option value="ORD1235">ORD1235 - Sarah - $85.50</option>
                        <option value="ORD1236">ORD1236 - Michael - $220.00</option>
                        <option value="ORD1237">ORD1237 - Linda - $120.00</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="paymentAmount">Amount</label>
                    <input type="text" id="paymentAmount" placeholder="Enter amount" required>
                </div>
                
                <div class="form-group">
                    <label>Payment Method</label>
                    <div class="payment-options">
                        <label class="payment-option">
                            <input type="radio" name="paymentMethod2" value="cash" checked>
                            <i class="fas fa-money-bill-wave payment-icon"></i>
                            Cash
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="paymentMethod2" value="card">
                            <i class="fas fa-credit-card payment-icon"></i>
                            Card
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="paymentMethod2" value="mpesa">
                            <i class="fas fa-mobile-alt payment-icon"></i>
                            M-Pesa
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="paymentMethod2" value="bank">
                            <i class="fas fa-university payment-icon"></i>
                            Bank Transfer
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="paymentTransactionId">Transaction ID (if applicable)</label>
                    <input type="text" id="paymentTransactionId" placeholder="Enter transaction ID">
                </div>
                
                <div class="form-group">
                    <label for="paymentReceiptUpload">Attach Receipt (Optional)</label>
                    <input type="file" id="paymentReceiptUpload">
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle"></i> Confirm Payment
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="showPage('dashboard')">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>

        <!-- Generate Invoice Page -->
        <div id="generate-invoice-page" class="page-content">
            <h2><i class="fas fa-file-invoice-dollar"></i> Invoice Generator</h2>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="order-id">ORD2345</td>
                        <td>John</td>
                        <td>$120.00</td>
                        <td>
                            <button class="btn btn-sm" onclick="generateInvoice('ORD2345')">
                                <i class="fas fa-file-pdf"></i> Generate PDF
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td class="order-id">ORD2346</td>
                        <td>Linda</td>
                        <td>$250.00</td>
                        <td>
                            <button class="btn btn-sm" onclick="generateInvoice('ORD2346')">
                                <i class="fas fa-file-pdf"></i> Generate PDF
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Transaction Logs Page -->
        <div id="transaction-logs-page" class="page-content">
            <h2><i class="fas fa-clipboard-list"></i> Transaction Logs</h2>
            
            <div style="margin-bottom: 20px; display: flex; gap: 10px;">
                <button class="btn btn-sm" onclick="filterTransactions('all')">All</button>
                <button class="btn btn-sm" onclick="filterTransactions('today')">Today</button>
                <button class="btn btn-sm" onclick="filterTransactions('week')">This Week</button>
                <button class="btn btn-sm" onclick="filterTransactions('month')">This Month</button>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Txn ID</th>
                        <th>Date</th>
                        <th>Method</th>
                        <th>Amount</th>
                        <th>Order ID</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="transactionsTableBody">
                    <tr>
                        <td>TX123</td>
                        <td>Apr 3, 2025</td>
                        <td>Card</td>
                        <td>$120.00</td>
                        <td class="order-id">ORD2345</td>
                        <td><span class="status status-paid">Paid</span></td>
                        <td>
                            <button class="btn btn-sm" onclick="viewTransaction('TX123')">View</button>
                            <button class="btn btn-sm btn-secondary" onclick="generateReport('TX123')">Report</button>
                        </td>
                    </tr>
                    <tr>
                        <td>TX124</td>
                        <td>Apr 3, 2025</td>
                        <td>Cash</td>
                        <td>$250.00</td>
                        <td class="order-id">ORD2346</td>
                        <td><span class="status status-reconciled">Reconciled</span></td>
                        <td>
                            <button class="btn btn-sm" onclick="viewTransaction('TX124')">View</button>
                            <button class="btn btn-sm btn-secondary" onclick="generateReport('TX124')">Report</button>
                        </td>
                    </tr>
                    <tr>
                        <td>TX125</td>
                        <td>Apr 2, 2025</td>
                        <td>M-Pesa</td>
                        <td>$85.50</td>
                        <td class="order-id">ORD2347</td>
                        <td><span class="status status-paid">Paid</span></td>
                        <td>
                            <button class="btn btn-sm" onclick="viewTransaction('TX125')">View</button>
                            <button class="btn btn-sm btn-secondary" onclick="generateReport('TX125')">Report</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Daily Reports Page -->
        <div id="daily-reports-page" class="page-content">
            <h2><i class="fas fa-chart-line"></i> Daily Reports</h2>
            
            <div class="section">
                <h3><i class="fas fa-chart-pie"></i> Daily Collection Stats</h3>
                <div class="chart-container">
                    <canvas id="dailyCollectionChart"></canvas>
                </div>
            </div>
            
            <div class="section">
                <h3><i class="fas fa-chart-bar"></i> Payment Method Distribution</h3>
                <div class="chart-container">
                    <canvas id="paymentMethodChart"></canvas>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <button class="btn" onclick="exportDailyReport('pdf')">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </button>
                <button class="btn btn-success" onclick="exportDailyReport('excel')">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
            </div>
        </div>

        <!-- Payment Settings Page -->
        <div id="payment-settings-page" class="page-content">
            <h2><i class="fas fa-cog"></i> Payment Settings</h2>
            
            <form id="settingsForm">
                <div class="form-group">
                    <label for="autoReconciliation">Auto-Reconciliation</label>
                    <select id="autoReconciliation">
                        <option value="enabled">Enabled</option>
                        <option value="disabled" selected>Disabled</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="receiptTemplate">Receipt Template</label>
                    <select id="receiptTemplate">
                        <option value="simple">Simple</option>
                        <option value="detailed" selected>Detailed</option>
                        <option value="branded">Branded</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="invoicePrefix">Invoice Prefix</label>
                    <input type="text" id="invoicePrefix" value="INV" placeholder="Enter invoice prefix">
                </div>
                
                <div class="form-group">
                    <label for="paymentMethods">Enabled Payment Methods</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <label>
                            <input type="checkbox" checked> Cash
                        </label>
                        <label>
                            <input type="checkbox" checked> Card
                        </label>
                        <label>
                            <input type="checkbox" checked> M-Pesa
                        </label>
                        <label>
                            <input type="checkbox" checked> Bank Transfer
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-success">Save Settings</button>
            </form>
        </div>

        <!-- Profile Page -->
        <div id="profile-page" class="page-content">
            <h2><i class="fas fa-user-cog"></i> Profile Settings</h2>
            
            <form id="profileForm">
                <div class="form-group">
                    <label for="profileName">Full Name</label>
                    <input type="text" id="profileName" value="Cashier User" required>
                </div>
                
                <div class="form-group">
                    <label for="profileEmail">Email</label>
                    <input type="email" id="profileEmail" value="cashier@tailorsuite.com" required>
                </div>
                
                <div class="form-group">
                    <label for="profilePhone">Phone Number</label>
                    <input type="tel" id="profilePhone" value="+254712345678" required>
                </div>
                
                <div class="form-group">
                    <label for="profilePassword">Change Password</label>
                    <input type="password" id="profilePassword" placeholder="Enter new password">
                </div>
                
                <div class="form-group">
                    <label for="profilePasswordConfirm">Confirm Password</label>
                    <input type="password" id="profilePasswordConfirm" placeholder="Confirm new password">
                </div>
                
                <button type="submit" class="btn btn-success">Save Changes</button>
            </form>
        </div>
    </div>

    <!-- Transaction Details Modal -->
    <div id="transactionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-receipt"></i> Transaction Details</h2>
                <button class="close-btn" onclick="closeModal('transactionModal')">&times;</button>
            </div>
            
            <div id="transactionDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>

    <!-- Invoice Preview Modal -->
    <div id="invoiceModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-file-invoice-dollar"></i> Invoice Preview</h2>
                <button class="close-btn" onclick="closeModal('invoiceModal')">&times;</button>
            </div>
            
            <div id="invoicePreviewContent" style="padding: 20px; background-color: white;">
                <!-- Invoice content will be loaded here -->
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <button class="btn" onclick="printInvoice()">
                    <i class="fas fa-print"></i> Print Invoice
                </button>
                <button class="btn btn-success" onclick="downloadInvoice()">
                    <i class="fas fa-download"></i> Download PDF
                </button>
                <button class="btn btn-secondary" onclick="emailInvoice()">
                    <i class="fas fa-envelope"></i> Email to Customer
                </button>
            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div id="notification-toast" class="notification">
        <i class="fas fa-check-circle"></i>
        <span id="notification-message">Action completed successfully!</span>
    </div>

    <script>
        // Sample transaction data
        const transactions = [
            {
                id: "TX123",
                date: "Apr 3, 2025",
                method: "Card",
                amount: 120.00,
                orderId: "ORD2345",
                customer: "John",
                status: "Paid",
                receipt: "receipt_12345.pdf",
                notes: "Payment for custom suit",
                reconciled: false
            },
            {
                id: "TX124",
                date: "Apr 3, 2025",
                method: "Cash",
                amount: 250.00,
                orderId: "ORD2346",
                customer: "Linda",
                status: "Reconciled",
                receipt: "",
                notes: "Payment for evening gown",
                reconciled: true
            },
            {
                id: "TX125",
                date: "Apr 2, 2025",
                method: "M-Pesa",
                amount: 85.50,
                orderId: "ORD2347",
                customer: "Sarah",
                status: "Paid",
                receipt: "mpesa_12345.pdf",
                notes: "Payment for formal shirt",
                reconciled: false
            }
        ];

        // Sample orders data
        const orders = [
            {
                id: "ORD1234",
                customer: "John",
                amount: 150.00,
                status: "Pending Payment",
                items: [
                    { name: "Custom Suit", price: 150.00 }
                ]
            },
            {
                id: "ORD1235",
                customer: "Sarah",
                amount: 85.50,
                status: "Pending Payment",
                items: [
                    { name: "Formal Shirt", price: 85.50 }
                ]
            },
            {
                id: "ORD1236",
                customer: "Michael",
                amount: 220.00,
                status: "Pending Payment",
                items: [
                    { name: "Wedding Dress", price: 220.00 }
                ]
            },
            {
                id: "ORD1237",
                customer: "Linda",
                amount: 120.00,
                status: "Pending Payment",
                items: [
                    { name: "Business Suit", price: 120.00 }
                ]
            }
        ];

        // Initialize the dashboard
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize forms
            initializeForms();
            
            // Update stats
            updateDashboardStats();
            
            // Initialize charts
            initializeCharts();
            
            // Set up event listeners
            setupEventListeners();
            
            // Simulate real-time updates
            simulateRealTimeUpdates();
        });

        function initializeForms() {
            // New payment form submission
            document.getElementById('newPaymentForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const orderId = document.getElementById('orderSelect').value;
                const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
                const transactionId = document.getElementById('transactionId').value;
                
                // Create new transaction
                const order = orders.find(o => o.id === orderId.split(' ')[0]);
                if (order) {
                    const newTransaction = {
                        id: "TX" + Math.floor(100 + Math.random() * 900),
                        date: new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }),
                        method: paymentMethod.charAt(0).toUpperCase() + paymentMethod.slice(1),
                        amount: order.amount,
                        orderId: order.id,
                        customer: order.customer,
                        status: "Paid",
                        receipt: document.getElementById('receiptUpload').files.length > 0 ? 
                                document.getElementById('receiptUpload').files[0].name : "",
                        notes: "Payment for " + order.items[0].name,
                        reconciled: false
                    };
                    
                    transactions.unshift(newTransaction);
                    order.status = "Paid";
                    
                    showNotification(`Payment of $${order.amount} for order ${order.id} recorded successfully!`, 'success');
                    updateDashboardStats();
                    showPage('dashboard');
                }
            });
            
            // Payment form submission (from payment page)
            document.getElementById('paymentForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const orderId = document.getElementById('paymentOrderSelect').value.split(' ')[0];
                const amount = parseFloat(document.getElementById('paymentAmount').value);
                const paymentMethod = document.querySelector('input[name="paymentMethod2"]:checked').value;
                const transactionId = document.getElementById('paymentTransactionId').value;
                
                // Create new transaction
                const order = orders.find(o => o.id === orderId);
                if (order) {
                    const newTransaction = {
                        id: "TX" + Math.floor(100 + Math.random() * 900),
                        date: new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }),
                        method: paymentMethod.charAt(0).toUpperCase() + paymentMethod.slice(1),
                        amount: amount,
                        orderId: order.id,
                        customer: order.customer,
                        status: "Paid",
                        receipt: document.getElementById('paymentReceiptUpload').files.length > 0 ? 
                                document.getElementById('paymentReceiptUpload').files[0].name : "",
                        notes: "Payment for " + order.items[0].name,
                        reconciled: false
                    };
                    
                    transactions.unshift(newTransaction);
                    order.status = "Paid";
                    
                    showNotification(`Payment of $${amount} for order ${order.id} recorded successfully!`, 'success');
                    updateDashboardStats();
                    showPage('dashboard');
                }
            });
            
            // Settings form submission
            document.getElementById('settingsForm').addEventListener('submit', function(e) {
                e.preventDefault();
                showNotification('Payment settings updated successfully!', 'success');
            });
            
            // Profile form submission
            document.getElementById('profileForm').addEventListener('submit', function(e) {
                e.preventDefault();
                showNotification('Profile updated successfully!', 'success');
            });
        }

        function updateDashboardStats() {
            // Calculate today's payments
            const today = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            const todaysPayments = transactions
                .filter(tx => tx.date === today)
                .reduce((sum, tx) => sum + tx.amount, 0);
            
            // Update stats
            document.getElementById('todays-payments').textContent = `$${todaysPayments.toFixed(2)}`;
            document.getElementById('invoices-count').textContent = transactions.length;
            document.getElementById('total-transactions').textContent = transactions.length;
            
            // Update pending payments badge
            const pendingPayments = orders.filter(o => o.status === "Pending Payment").length;
            document.getElementById('pending-payments-badge').textContent = pendingPayments;
            document.getElementById('pending-payments-badge').style.display = pendingPayments > 0 ? 'block' : 'none';
        }

        function initializeCharts() {
            // Daily Collection Chart
            const dailyCtx = document.getElementById('dailyCollectionChart').getContext('2d');
            const dailyCollectionChart = new Chart(dailyCtx, {
                type: 'line',
                data: {
                    labels: ['Apr 1', 'Apr 2', 'Apr 3', 'Apr 4', 'Apr 5', 'Apr 6', 'Today'],
                    datasets: [{
                        label: 'Daily Collections ($)',
                        data: [320, 450, 370, 520, 480, 410, 1245],
                        backgroundColor: 'rgba(52, 152, 219, 0.2)',
                        borderColor: 'rgba(52, 152, 219, 1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
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
            
            // Payment Method Chart
            const methodCtx = document.getElementById('paymentMethodChart').getContext('2d');
            const paymentMethodChart = new Chart(methodCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Cash', 'Card', 'M-Pesa', 'Bank Transfer'],
                    datasets: [{
                        data: [45, 30, 15, 10],
                        backgroundColor: [
                            'rgba(46, 204, 113, 0.7)',
                            'rgba(52, 152, 219, 0.7)',
                            'rgba(155, 89, 182, 0.7)',
                            'rgba(241, 196, 15, 0.7)'
                        ],
                        borderColor: [
                            'rgba(46, 204, 113, 1)',
                            'rgba(52, 152, 219, 1)',
                            'rgba(155, 89, 182, 1)',
                            'rgba(241, 196, 15, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });
        }

        function setupEventListeners() {
            // Payment method change handler for payment form
            document.querySelectorAll('input[name="paymentMethod2"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    const orderId = document.getElementById('paymentOrderSelect').value.split(' ')[0];
                    const order = orders.find(o => o.id === orderId);
                    if (order) {
                        document.getElementById('paymentAmount').value = order.amount.toFixed(2);
                    }
                });
            });
            
            // Order select change handler for dashboard form
            document.getElementById('orderSelect').addEventListener('change', function() {
                if (this.value) {
                    const orderId = this.value.split(' ')[0];
                    const order = orders.find(o => o.id === orderId);
                    if (order) {
                        // You could auto-fill other fields here if needed
                    }
                }
            });
        }

        function simulateRealTimeUpdates() {
            // Simulate new transactions coming in
            setInterval(() => {
                if (Math.random() > 0.7) { // 30% chance of a new transaction
                    const randomOrder = orders[Math.floor(Math.random() * orders.length)];
                    if (randomOrder.status === "Pending Payment") {
                        const methods = ["Cash", "Card", "M-Pesa", "Bank Transfer"];
                        const newTransaction = {
                            id: "TX" + Math.floor(100 + Math.random() * 900),
                            date: new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }),
                            method: methods[Math.floor(Math.random() * methods.length)],
                            amount: randomOrder.amount,
                            orderId: randomOrder.id,
                            customer: randomOrder.customer,
                            status: "Paid",
                            receipt: "",
                            notes: "Payment for " + randomOrder.items[0].name,
                            reconciled: false
                        };
                        
                        transactions.unshift(newTransaction);
                        randomOrder.status = "Paid";
                        
                        updateDashboardStats();
                        
                        if (document.getElementById('transaction-logs-page').classList.contains('active')) {
                            // Refresh transactions table if on that page
                            filterTransactions('all');
                        }
                        
                        // Show notification if user is not on dashboard
                        if (!document.getElementById('dashboard-page').classList.contains('active')) {
                            showNotification(`New payment received for order ${randomOrder.id}`, 'info');
                        }
                    }
                }
            }, 10000); // Check every 10 seconds
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
                'dashboard': 'Cashier Dashboard',
                'new-payment': 'New Payment',
                'generate-invoice': 'Generate Invoice',
                'transaction-logs': 'Transaction Logs',
                'daily-reports': 'Daily Reports',
                'payment-settings': 'Payment Settings',
                'profile': 'Profile Settings'
            };
            document.getElementById('page-title').textContent = pageTitles[pageId] || 'Cashier Dashboard';
            
            // Update active nav item
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Set the appropriate nav item as active
            if (pageId === 'dashboard') {
                document.querySelector('.nav-item[onclick="showPage(\'dashboard\')"]').classList.add('active');
            } else if (pageId === 'new-payment') {
                document.querySelector('.nav-item[onclick="showPage(\'new-payment\')"]').classList.add('active');
            } else if (pageId === 'generate-invoice') {
                document.querySelector('.nav-item[onclick="showPage(\'generate-invoice\')"]').classList.add('active');
            } else if (pageId === 'transaction-logs') {
                document.querySelector('.nav-item[onclick="showPage(\'transaction-logs\')"]').classList.add('active');
            } else if (pageId === 'daily-reports') {
                document.querySelector('.nav-item[onclick="showPage(\'daily-reports\')"]').classList.add('active');
            } else if (pageId === 'payment-settings') {
                document.querySelector('.nav-item[onclick="showPage(\'payment-settings\')"]').classList.add('active');
            } else if (pageId === 'profile') {
                document.querySelector('.nav-item[onclick="showPage(\'profile\')"]').classList.add('active');
            }
            
            // Special initialization for certain pages
            if (pageId === 'daily-reports') {
                // Reinitialize charts when reports page is shown
                setTimeout(initializeCharts, 100);
            }
        }

        // View transaction details
        function viewTransaction(transactionId) {
            const transaction = transactions.find(tx => tx.id === transactionId);
            const detailsContent = document.getElementById('transactionDetailsContent');
            
            if (transaction) {
                detailsContent.innerHTML = `
                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <div>
                                <strong>Transaction ID:</strong> ${transaction.id}
                            </div>
                            <div>
                                <span class="status ${transaction.status === 'Reconciled' ? 'status-reconciled' : 'status-paid'}">
                                    ${transaction.status}
                                </span>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                            <div>
                                <strong>Date:</strong> ${transaction.date}
                            </div>
                            <div>
                                <strong>Payment Method:</strong> ${transaction.method}
                            </div>
                            <div>
                                <strong>Amount:</strong> $${transaction.amount.toFixed(2)}
                            </div>
                            <div>
                                <strong>Order ID:</strong> <span class="order-id">${transaction.orderId}</span>
                            </div>
                        </div>
                        
                        <div style="margin: 15px 0;">
                            <strong>Customer:</strong>
                            <div style="margin-top: 5px; padding: 10px; background-color: #f9f9f9; border-radius: 4px;">
                                ${transaction.customer}
                            </div>
                        </div>
                        
                        <div style="margin: 15px 0;">
                            <strong>Notes:</strong>
                            <div style="margin-top: 5px; padding: 10px; background-color: #f9f9f9; border-radius: 4px;">
                                ${transaction.notes}
                            </div>
                        </div>
                        
                        ${transaction.receipt ? `
                        <div style="margin: 15px 0;">
                            <strong>Receipt:</strong>
                            <div style="margin-top: 5px;">
                                <a href="#" onclick="viewReceipt('${transaction.receipt}')" class="btn btn-sm">
                                    <i class="fas fa-file-pdf"></i> View Receipt
                                </a>
                            </div>
                        </div>
                        ` : ''}
                        
                        ${!transaction.reconciled ? `
                        <div style="text-align: center; margin-top: 20px;">
                            <button class="btn btn-success" onclick="reconcileTransaction('${transaction.id}')">
                                <i class="fas fa-exchange-alt"></i> Reconcile Transaction
                            </button>
                        </div>
                        ` : ''}
                    </div>
                `;
                
                document.getElementById('transactionModal').style.display = 'flex';
            } else {
                showNotification('Transaction not found', 'error');
            }
        }

        // Generate invoice
        function generateInvoice(orderId) {
            const order = orders.find(o => o.id === orderId);
            if (order) {
                const invoiceContent = document.getElementById('invoicePreviewContent');
                invoiceContent.innerHTML = `
                    <div style="border: 1px solid #eee; padding: 20px; max-width: 800px; margin: 0 auto;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 30px;">
                            <div>
                                <h2 style="color: #2c3e50;">TailorSuite</h2>
                                <p>123 Tailor Street<br>Fashion District, Nairobi<br>Kenya</p>
                            </div>
                            <div style="text-align: right;">
                                <h2 style="color: #3498db;">INVOICE</h2>
                                <p><strong>Invoice #:</strong> INV-${Math.floor(1000 + Math.random() * 9000)}</p>
                                <p><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
                            </div>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                            <div>
                                <h3 style="color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 5px;">Bill To</h3>
                                <p>${order.customer}<br>Customer</p>
                            </div>
                            <div style="text-align: right;">
                                <h3 style="color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 5px;">Order Details</h3>
                                <p><strong>Order ID:</strong> ${order.id}</p>
                                <p><strong>Status:</strong> ${order.status}</p>
                            </div>
                        </div>
                        
                        <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
                            <thead>
                                <tr style="background-color: #f8f9fa;">
                                    <th style="text-align: left; padding: 10px; border-bottom: 1px solid #ddd;">Item</th>
                                    <th style="text-align: right; padding: 10px; border-bottom: 1px solid #ddd;">Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${order.items.map(item => `
                                <tr>
                                    <td style="padding: 10px; border-bottom: 1px solid #eee;">${item.name}</td>
                                    <td style="text-align: right; padding: 10px; border-bottom: 1px solid #eee;">$${item.price.toFixed(2)}</td>
                                </tr>
                                `).join('')}
                                <tr>
                                    <td style="padding: 10px; font-weight: bold;">Total</td>
                                    <td style="text-align: right; padding: 10px; font-weight: bold;">$${order.amount.toFixed(2)}</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center;">
                            <p>Thank you for your business!</p>
                            <p style="font-size: 12px; color: #7f8c8d;">This is a computer generated invoice and does not require a signature.</p>
                        </div>
                    </div>
                `;
                
                document.getElementById('invoiceModal').style.display = 'flex';
            } else {
                showNotification('Order not found', 'error');
            }
        }

        // Filter transactions
        function filterTransactions(filter) {
            const tbody = document.getElementById('transactionsTableBody');
            tbody.innerHTML = '';
            
            let filteredTransactions = [...transactions];
            
            if (filter === 'today') {
                const today = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
                filteredTransactions = transactions.filter(tx => tx.date === today);
            } else if (filter === 'week') {
                // Simplified week filter for demo
                filteredTransactions = transactions.slice(0, 7);
            } else if (filter === 'month') {
                // Simplified month filter for demo
                filteredTransactions = transactions.slice(0, 30);
            }
            
            filteredTransactions.forEach(tx => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${tx.id}</td>
                    <td>${tx.date}</td>
                    <td>${tx.method}</td>
                    <td>$${tx.amount.toFixed(2)}</td>
                    <td class="order-id">${tx.orderId}</td>
                    <td><span class="status ${tx.status === 'Reconciled' ? 'status-reconciled' : 'status-paid'}">${tx.status}</span></td>
                    <td>
                        <button class="btn btn-sm" onclick="viewTransaction('${tx.id}')">View</button>
                        <button class="btn btn-sm btn-secondary" onclick="generateReport('${tx.id}')">Report</button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Generate report
        function generateReport(transactionId) {
            showNotification(`Report for transaction ${transactionId} generated successfully!`, 'success');
            // In a real app, this would generate and download a report
        }

        // Export daily report
        function exportDailyReport(format) {
            showNotification(`Daily report exported as ${format.toUpperCase()} successfully!`, 'success');
            // In a real app, this would export the report in the specified format
        }

        // Reconcile transaction
        function reconcileTransaction(transactionId) {
            const transaction = transactions.find(tx => tx.id === transactionId);
            if (transaction) {
                transaction.status = "Reconciled";
                transaction.reconciled = true;
                showNotification(`Transaction ${transactionId} reconciled successfully!`, 'success');
                closeModal('transactionModal');
                
                // Refresh transactions table if on that page
                if (document.getElementById('transaction-logs-page').classList.contains('active')) {
                    filterTransactions('all');
                }
                
                updateDashboardStats();
            }
        }

        // View receipt
        function viewReceipt(receiptFile) {
            showNotification(`Opening receipt: ${receiptFile}`, 'info');
            // In a real app, this would open the receipt file
        }

        // Print invoice
        function printInvoice() {
            showNotification('Invoice sent to printer!', 'success');
            // In a real app, this would print the invoice
        }

        // Download invoice
        function downloadInvoice() {
            showNotification('Invoice downloaded as PDF!', 'success');
            // In a real app, this would download the invoice as PDF
        }

        // Email invoice
        function emailInvoice() {
            showNotification('Invoice emailed to customer!', 'success');
            // In a real app, this would email the invoice to the customer
        }

        // Close modal
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Logout
        function logout() {
            showNotification('You have been logged out successfully.', 'success');
            setTimeout(() => {
                window.location.href = '/login';
            }, 1500);
        }

        // Notification function
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

       function loadCashierStats() {
  fetch('api.php?action=cashier_stats')
    .then(res => res.json())
    .then(data => {
      document.getElementById("todays-payments").textContent = `$${data.todays_payments.toFixed(2)}`;
      document.getElementById("total-transactions").textContent = data.total_transactions;
    });
}
setInterval(loadCashierStats, 5000);
loadCashierStats();



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