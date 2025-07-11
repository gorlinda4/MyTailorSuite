<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Control Center - TailorSuite</title>
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

        .header-nav {
            display: flex;
            gap: 15px;
        }

        .header-nav a {
            color: var(--text-primary);
            text-decoration: none;
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 4px;
            transition: all 0.3s;
        }

        .header-nav a:hover {
            background-color: #f8f9fa;
        }

        .header-nav a.active {
            color: var(--sidebar-active);
            font-weight: bold;
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

        .stat-icon.users {
            background-color: rgba(52, 152, 219, 0.1);
            color: var(--sidebar-active);
        }

        .stat-icon.roles {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--success-color);
        }

        .stat-icon.issues {
            background-color: rgba(241, 196, 15, 0.1);
            color: var(--warning-color);
        }

        .stat-icon.activity {
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

        .user-id {
            font-weight: bold;
            color: var(--sidebar-active);
        }

        .status {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-active {
            background-color: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
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

        /* Role Permissions */
        .permissions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin: 15px 0;
        }

        .permission-item {
            display: flex;
            align-items: center;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }

        .permission-item input {
            margin-right: 10px;
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

        /* CRUD Operations Table */
        .crud-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .crud-table th, .crud-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .crud-table th {
            background-color: #f8f9fa;
            color: var(--text-secondary);
        }

        .crud-success {
            color: var(--success-color);
        }

        .crud-error {
            color: var(--danger-color);
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
            <i class="fas fa-user-shield"></i>
            <span>Admin Control</span>
        </div>
        <ul class="nav-menu">
            <li class="nav-menu-title">Dashboard</li>
            <li class="nav-item active" onclick="showPage('dashboard')">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </li>
            
            <li class="nav-menu-title">Management</li>
            <li class="nav-item" onclick="showPage('users')">
                <i class="fas fa-users"></i>
                <span>Manage Users</span>
            </li>
            <li class="nav-item" onclick="showPage('roles')">
                <i class="fas fa-user-tag"></i>
                <span>Assign Roles</span>
            </li>
            
            <li class="nav-menu-title">System</li>
            <li class="nav-item" onclick="showPage('settings')">
                <i class="fas fa-cog"></i>
                <span>System Settings</span>
            </li>
            <li class="nav-item" onclick="showPage('logs')">
                <i class="fas fa-clipboard-list"></i>
                <span>Audit Logs</span>
                <span class="notification-badge" id="logs-badge">2</span>
            </li>
            <li class="nav-item" onclick="showPage('reports')">
                <i class="fas fa-chart-bar"></i>
                <span>All Reports</span>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1 id="page-title">Admin Control Center</h1>
            
            <div class="header-nav">
                <a href="#" class="active" onclick="showPage('dashboard')">Dashboard</a>
                <a href="#" onclick="showPage('users')">Users</a>
                <a href="#" onclick="showPage('settings')">Settings</a>
                <a href="#" onclick="showPage('logs')">Logs</a>
                <a href="#" onclick="showPage('reports')">Reports</a>
            </div>
            
            <div class="user-info">
                <img src="https://via.placeholder.com/40" alt="Admin">
                <span>Admin</span>
                <div class="dropdown-menu">
                    <a href="#" onclick="showPage('profile')"><i class="fas fa-user-cog"></i> Profile</a>
                    <a href="#" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>

        <!-- Dashboard Page -->
      

            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon users">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Total Users</div>
                        <div class="stat-value" id="total-users">128</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon roles">
                        <i class="fas fa-user-tag"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Active Roles</div>
                        <div class="stat-value" id="active-roles">5</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon issues">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Logged Issues</div>
                        <div class="stat-value" id="logged-issues">2</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon activity">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Last Admin Login</div>
                        <div class="stat-value" id="last-login">Apr 4, 2025 – 9:03 AM</div>
                    </div>
                </div>
            </div>

            <div class="dashboard-sections">
                <!-- User Management Section -->
                <div class="section">
                    <h2><i class="fas fa-users"></i> User Management</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="user-id">U1021</td>
                                <td>John Doe</td>
                                <td>Manager</td>
                                <td><span class="status status-active">Active</span></td>
                                <td>
                                    <button class="btn btn-sm" onclick="editUser('U1021')">Edit</button>
                                    <button class="btn btn-sm btn-secondary" onclick="viewUser('U1021')">View</button>
                                </td>
                            </tr>
                            <tr>
                                <td class="user-id">U1034</td>
                                <td>Sarah Lee</td>
                                <td>Tailor</td>
                                <td><span class="status status-active">Active</span></td>
                                <td>
                                    <button class="btn btn-sm" onclick="editUser('U1034')">Edit</button>
                                    <button class="btn btn-sm btn-secondary" onclick="viewUser('U1034')">View</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- System Activity Section -->
                <div class="section">
                    <h2><i class="fas fa-clipboard-list"></i> Recent System Activity</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Log ID</th>
                                <th>Action</th>
                                <th>User</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>A301</td>
                                <td>User Updated</td>
                                <td>Admin</td>
                                <td>Apr 4, 2025 – 9:03 AM</td>
                            </tr>
                            <tr>
                                <td>A302</td>
                                <td>Order Deleted</td>
                                <td>Manager</td>
                                <td>Apr 3, 2025 – 9:43 PM</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Users Management Page -->
        <div id="users-page" class="page-content">
            <h2><i class="fas fa-users"></i> User Management</h2>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <div>
                    <button class="btn btn-success" onclick="showAddUserModal()">
                        <i class="fas fa-plus"></i> Add New User
                    </button>
                </div>
                <div>
                    <input type="text" placeholder="Search users..." style="padding: 8px; border: 1px solid var(--border-color); border-radius: 4px;">
                </div>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <!-- Users will be loaded dynamically -->
                </tbody>
            </table>
        </div>

        <!-- Roles Management Page -->
        <div id="roles-page" class="page-content">
            <h2><i class="fas fa-user-tag"></i> Role Management</h2>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <div>
                    <button class="btn btn-success" onclick="showAddRoleModal()">
                        <i class="fas fa-plus"></i> Create Role
                    </button>
                </div>
                <div>
                    <input type="text" placeholder="Search roles..." style="padding: 8px; border: 1px solid var(--border-color); border-radius: 4px;">
                </div>
            </div>
            
            <div class="section">
                <h3><i class="fas fa-list"></i> Existing Roles</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Role Name</th>
                            <th>Users</th>
                            <th>Permissions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="rolesTableBody">
                        <!-- Roles will be loaded dynamically -->
                    </tbody>
                </table>
            </div>
            
            <div class="section">
                <h3><i class="fas fa-key"></i> Default Permissions</h3>
                <div class="permissions-grid" id="defaultPermissions">
                    <!-- Permissions will be loaded dynamically -->
                </div>
            </div>
        </div>

        <!-- System Settings Page -->
        <div id="settings-page" class="page-content">
            <h2><i class="fas fa-cog"></i> System Settings</h2>
            
            <div class="dashboard-sections">
                <div class="section">
                    <h3><i class="fas fa-building"></i> Business Details</h3>
                    <form id="businessDetailsForm">
                        <div class="form-group">
                            <label for="businessName">Business Name</label>
                            <input type="text" id="businessName" value="TailorSuite" required>
                        </div>
                        <div class="form-group">
                            <label for="businessAddress">Address</label>
                            <textarea id="businessAddress" rows="3" required>123 Tailor Street, Fashion District, Nairobi, Kenya</textarea>
                        </div>
                        <div class="form-group">
                            <label for="businessPhone">Phone Number</label>
                            <input type="tel" id="businessPhone" value="+254712345678" required>
                        </div>
                        <div class="form-group">
                            <label for="businessEmail">Email</label>
                            <input type="email" id="businessEmail" value="info@tailorsuite.com" required>
                        </div>
                        <button type="submit" class="btn btn-success">Save Changes</button>
                    </form>
                </div>
                
                <div class="section">
                    <h3><i class="fas fa-envelope"></i> Email Templates</h3>
                    <div class="form-group">
                        <label for="emailTemplate">Select Template</label>
                        <select id="emailTemplate" onchange="loadEmailTemplate(this.value)">
                            <option value="welcome">Welcome Email</option>
                            <option value="order_confirmation">Order Confirmation</option>
                            <option value="payment_receipt">Payment Receipt</option>
                            <option value="password_reset">Password Reset</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="emailSubject">Subject</label>
                        <input type="text" id="emailSubject" value="Welcome to TailorSuite">
                    </div>
                    <div class="form-group">
                        <label for="emailContent">Content</label>
                        <textarea id="emailContent" rows="8" placeholder="Email content..."></textarea>
                    </div>
                    <button class="btn btn-success">Save Template</button>
                    <button class="btn btn-secondary">Preview Email</button>
                </div>
            </div>
            
            <div class="section">
                <h3><i class="fas fa-money-bill-wave"></i> Payment Settings</h3>
                <form id="paymentSettingsForm">
                    <div class="form-group">
                        <label for="currency">Default Currency</label>
                        <select id="currency">
                            <option value="USD">US Dollar (USD)</option>
                            <option value="KES" selected>Kenyan Shilling (KES)</option>
                            <option value="EUR">Euro (EUR)</option>
                            <option value="GBP">British Pound (GBP)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="taxRate">Tax Rate (%)</label>
                        <input type="number" id="taxRate" value="16" min="0" max="50" step="0.1">
                    </div>
                    <div class="form-group">
                        <label>Enabled Payment Methods</label>
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
            
            <div class="section">
                <h3><i class="fas fa-database"></i> Backup & Restore</h3>
                <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                    <button class="btn btn-success">
                        <i class="fas fa-download"></i> Create Backup
                    </button>
                    <button class="btn">
                        <i class="fas fa-upload"></i> Restore Backup
                    </button>
                    <button class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i> Clear Test Data
                    </button>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Backup File</th>
                            <th>Date</th>
                            <th>Size</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>tailorsuite_backup_20250404.zip</td>
                            <td>Apr 4, 2025</td>
                            <td>45 MB</td>
                            <td>
                                <button class="btn btn-sm">Download</button>
                                <button class="btn btn-sm btn-secondary">Restore</button>
                            </td>
                        </tr>
                        <tr>
                            <td>tailorsuite_backup_20250401.zip</td>
                            <td>Apr 1, 2025</td>
                            <td>42 MB</td>
                            <td>
                                <button class="btn btn-sm">Download</button>
                                <button class="btn btn-sm btn-secondary">Restore</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Audit Logs Page -->
        <div id="logs-page" class="page-content">
            <h2><i class="fas fa-clipboard-list"></i> Audit Logs</h2>
            
            <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                <button class="btn btn-sm" onclick="filterLogs('all')">All</button>
                <button class="btn btn-sm" onclick="filterLogs('today')">Today</button>
                <button class="btn btn-sm" onclick="filterLogs('week')">This Week</button>
                <button class="btn btn-sm" onclick="filterLogs('month')">This Month</button>
                <button class="btn btn-sm btn-danger" onclick="clearOldLogs()">
                    <i class="fas fa-trash-alt"></i> Clear Old Logs
                </button>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Log ID</th>
                        <th>Action</th>
                        <th>User</th>
                        <th>Details</th>
                        <th>Timestamp</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody id="logsTableBody">
                    <!-- Logs will be loaded dynamically -->
                </tbody>
            </table>
        </div>

        <!-- Reports Page -->
        <div id="reports-page" class="page-content">
            <h2><i class="fas fa-chart-bar"></i> Reports Center</h2>
            
            <div class="dashboard-sections">
                <div class="section">
                    <h3><i class="fas fa-money-bill-wave"></i> Financial Reports</h3>
                    <div class="chart-container">
                        <canvas id="financialChart"></canvas>
                    </div>
                    <div style="margin-top: 20px;">
                        <button class="btn" onclick="generateFinancialReport()">
                            <i class="fas fa-file-pdf"></i> Generate PDF
                        </button>
                        <button class="btn btn-success" onclick="exportFinancialReport()">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </button>
                    </div>
                </div>
                
                <div class="section">
                    <h3><i class="fas fa-chart-line"></i> Usage Analytics</h3>
                    <div class="chart-container">
                        <canvas id="usageChart"></canvas>
                    </div>
                    <div style="margin-top: 20px;">
                        <button class="btn" onclick="generateUsageReport()">
                            <i class="fas fa-file-pdf"></i> Generate PDF
                        </button>
                        <button class="btn btn-success" onclick="exportUsageReport()">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h3><i class="fas fa-boxes"></i> Inventory Overview</h3>
                <div class="chart-container">
                    <canvas id="inventoryChart"></canvas>
                </div>
                <div style="margin-top: 20px;">
                    <button class="btn" onclick="generateInventoryReport()">
                        <i class="fas fa-file-pdf"></i> Generate PDF
                    </button>
                    <button class="btn btn-success" onclick="exportInventoryReport()">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                </div>
            </div>
            
            <div class="section">
                <h3><i class="fas fa-database"></i> Export Full System Data</h3>
                <div style="display: flex; gap: 15px; margin-top: 20px;">
                    <button class="btn" onclick="exportSystemData('csv')">
                        <i class="fas fa-file-csv"></i> Export as CSV
                    </button>
                    <button class="btn" onclick="exportSystemData('json')">
                        <i class="fas fa-file-code"></i> Export as JSON
                    </button>
                    <button class="btn" onclick="exportSystemData('pdf')">
                        <i class="fas fa-file-pdf"></i> Export as PDF
                    </button>
                </div>
            </div>
            
            <div class="section">
                <h3><i class="fas fa-tasks"></i> CRUD Operations Summary</h3>
                <table class="crud-table">
                    <thead>
                        <tr>
                            <th>Feature</th>
                            <th>Create</th>
                            <th>Read</th>
                            <th>Update</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Users</td>
                            <td class="crud-success">✅</td>
                            <td class="crud-success">✅</td>
                            <td class="crud-success">✅</td>
                            <td class="crud-success">✅</td>
                        </tr>
                        <tr>
                            <td>Roles</td>
                            <td class="crud-success">✅</td>
                            <td class="crud-success">✅</td>
                            <td class="crud-success">✅</td>
                            <td class="crud-success">✅</td>
                        </tr>
                        <tr>
                            <td>System Settings</td>
                            <td class="crud-success">✅</td>
                            <td class="crud-success">✅</td>
                            <td class="crud-success">✅</td>
                            <td class="crud-error">❌</td>
                        </tr>
                        <tr>
                            <td>Audit Logs</td>
                            <td class="crud-error">❌</td>
                            <td class="crud-success">✅</td>
                            <td class="crud-error">❌</td>
                            <td class="crud-success">✅ (Old logs)</td>
                        </tr>
                        <tr>
                            <td>Reports</td>
                            <td class="crud-success">✅</td>
                            <td class="crud-success">✅</td>
                            <td class="crud-success">✅</td>
                            <td class="crud-success">✅</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user-plus"></i> Add New User</h2>
                <button class="close-btn" onclick="closeModal('addUserModal')">&times;</button>
            </div>
            
            <form id="addUserForm">
                <div class="form-group">
                    <label for="newUserName">Full Name</label>
                    <input type="text" id="newUserName" required>
                </div>
                
                <div class="form-group">
                    <label for="newUserEmail">Email</label>
                    <input type="email" id="newUserEmail" required>
                </div>
                
                <div class="form-group">
                    <label for="newUserRole">Role</label>
                    <select id="newUserRole" required>
                        <option value="">-- Select Role --</option>
                        <option value="admin">Admin</option>
                        <option value="manager">Manager</option>
                        <option value="tailor">Tailor</option>
                        <option value="cashier">Cashier</option>
                        <option value="customer">Customer</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="newUserPassword">Password</label>
                    <input type="password" id="newUserPassword" required>
                </div>
                
                <div class="form-group">
                    <label for="newUserConfirmPassword">Confirm Password</label>
                    <input type="password" id="newUserConfirmPassword" required>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-success">Create User</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addUserModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user-edit"></i> Edit User</h2>
                <button class="close-btn" onclick="closeModal('editUserModal')">&times;</button>
            </div>
            
            <form id="editUserForm">
                <input type="hidden" id="editUserId">
                
                <div class="form-group">
                    <label for="editUserName">Full Name</label>
                    <input type="text" id="editUserName" required>
                </div>
                
                <div class="form-group">
                    <label for="editUserEmail">Email</label>
                    <input type="email" id="editUserEmail" required>
                </div>
                
                <div class="form-group">
                    <label for="editUserRole">Role</label>
                    <select id="editUserRole" required>
                        <option value="">-- Select Role --</option>
                        <option value="admin">Admin</option>
                        <option value="manager">Manager</option>
                        <option value="tailor">Tailor</option>
                        <option value="cashier">Cashier</option>
                        <option value="customer">Customer</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="editUserStatus">Status</label>
                    <select id="editUserStatus" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-success">Save Changes</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editUserModal')">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="confirmDeleteUser()">Delete User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Role Modal -->
    <div id="addRoleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-plus-circle"></i> Create New Role</h2>
                <button class="close-btn" onclick="closeModal('addRoleModal')">&times;</button>
            </div>
            
            <form id="addRoleForm">
                <div class="form-group">
                    <label for="newRoleName">Role Name</label>
                    <input type="text" id="newRoleName" required>
                </div>
                
                <div class="form-group">
                    <label for="newRoleDescription">Description</label>
                    <textarea id="newRoleDescription" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Permissions</label>
                    <div class="permissions-grid" id="newRolePermissions">
                        <!-- Permissions will be loaded here -->
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-success">Create Role</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addRoleModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View User Modal -->
    <div id="viewUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user"></i> User Details</h2>
                <button class="close-btn" onclick="closeModal('viewUserModal')">&times;</button>
            </div>
            
            <div id="viewUserContent">
                <!-- User details will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h2>
                <button class="close-btn" onclick="closeModal('deleteModal')">&times;</button>
            </div>
            
            <div id="deleteModalContent">
                <p>Are you sure you want to delete this item?</p>
                <p>This action cannot be undone.</p>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button class="btn btn-danger" onclick="confirmDelete()">Delete</button>
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
        
        // Sample data
        const users = [
            { id: "U1021", name: "John Doe", email: "john@tailorsuite.com", role: "manager", status: "active", lastLogin: "Apr 4, 2025 – 9:03 AM" },
            { id: "U1034", name: "Sarah Lee", email: "sarah@tailorsuite.com", role: "tailor", status: "active", lastLogin: "Apr 3, 2025 – 2:45 PM" },
            { id: "U1045", name: "Michael Brown", email: "michael@tailorsuite.com", role: "cashier", status: "active", lastLogin: "Apr 2, 2025 – 11:20 AM" },
            { id: "U1056", name: "Linda Johnson", email: "linda@tailorsuite.com", role: "tailor", status: "inactive", lastLogin: "Mar 28, 2025 – 4:15 PM" }
        ];

        const roles = [
            { name: "admin", description: "Full system access", users: 1, permissions: ["all"] },
            { name: "manager", description: "Manage orders and staff", users: 3, permissions: ["manage_orders", "manage_staff", "view_reports"] },
            { name: "tailor", description: "Create and modify garments", users: 5, permissions: ["manage_garments", "view_orders"] },
            { name: "cashier", description: "Process payments", users: 2, permissions: ["process_payments", "view_orders"] },
            { name: "customer", description: "Place and track orders", users: 117, permissions: ["place_orders", "view_own_orders"] }
        ];

        const permissions = [
            "manage_users", "manage_roles", "manage_settings", 
            "manage_orders", "manage_garments", "manage_staff",
            "process_payments", "view_reports", "place_orders",
            "view_own_orders", "export_data", "backup_restore"
        ];

        const auditLogs = [
            { id: "A301", action: "User Updated", user: "Admin", details: "Updated user U1021", timestamp: "Apr 4, 2025 – 9:03 AM", ip: "192.168.1.1" },
            { id: "A302", action: "Order Deleted", user: "Manager", details: "Deleted order ORD2045", timestamp: "Apr 3, 2025 – 9:43 PM", ip: "192.168.1.15" },
            { id: "A303", action: "Role Created", user: "Admin", details: "Created new role 'Auditor'", timestamp: "Apr 2, 2025 – 3:22 PM", ip: "192.168.1.1" },
            { id: "A304", action: "Settings Updated", user: "Admin", details: "Updated payment settings", timestamp: "Apr 1, 2025 – 10:15 AM", ip: "192.168.1.1" }
        ];

        // Initialize the dashboard
        document.addEventListener('DOMContentLoaded', function() {
            // Load initial data
            loadUsersTable();
            loadRolesTable();
            loadPermissions();
            loadAuditLogs();
            
            // Initialize charts
            initializeCharts();
            
            // Set up form submissions
            setupFormHandlers();
            
            // Update stats
            updateStats();
        });

        function loadUsersTable() {
            const tbody = document.getElementById('usersTableBody');
            tbody.innerHTML = '';
            
            users.forEach(user => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="user-id">${user.id}</td>
                    <td>${user.name}</td>
                    <td>${user.email}</td>
                    <td>${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</td>
                    <td><span class="status status-${user.status}">${user.status.charAt(0).toUpperCase() + user.status.slice(1)}</span></td>
                    <td>
                        <button class="btn btn-sm" onclick="editUser('${user.id}')">Edit</button>
                        <button class="btn btn-sm btn-secondary" onclick="viewUser('${user.id}')">View</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteUser('${user.id}')">Delete</button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        function loadRolesTable() {
            const tbody = document.getElementById('rolesTableBody');
            tbody.innerHTML = '';
            
            roles.forEach(role => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${role.name.charAt(0).toUpperCase() + role.name.slice(1)}</td>
                    <td>${role.users} user(s)</td>
                    <td>${role.permissions.length} permission(s)</td>
                    <td>
                        <button class="btn btn-sm" onclick="editRole('${role.name}')">Edit</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteRole('${role.name}')">Delete</button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        function loadPermissions() {
            const container = document.getElementById('defaultPermissions');
            container.innerHTML = '';
            
            permissions.forEach(permission => {
                const item = document.createElement('div');
                item.className = 'permission-item';
                item.innerHTML = `
                    <input type="checkbox" id="perm_${permission}" checked>
                    <label for="perm_${permission}">${permission.replace(/_/g, ' ')}</label>
                `;
                container.appendChild(item);
            });
            
            // Also load permissions for new role modal
            const newRolePerms = document.getElementById('newRolePermissions');
            newRolePerms.innerHTML = '';
            
            permissions.forEach(permission => {
                const item = document.createElement('div');
                item.className = 'permission-item';
                item.innerHTML = `
                    <input type="checkbox" id="new_perm_${permission}">
                    <label for="new_perm_${permission}">${permission.replace(/_/g, ' ')}</label>
                `;
                newRolePerms.appendChild(item);
            });
        }

        function loadAuditLogs(filter = 'all') {
            const tbody = document.getElementById('logsTableBody');
            tbody.innerHTML = '';
            
            let filteredLogs = [...auditLogs];
            
            if (filter === 'today') {
                const today = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
                filteredLogs = auditLogs.filter(log => log.timestamp.includes(today.split(',')[0]));
            } else if (filter === 'week') {
                // Simplified week filter for demo
                filteredLogs = auditLogs.slice(0, 7);
            } else if (filter === 'month') {
                // Simplified month filter for demo
                filteredLogs = auditLogs.slice(0, 30);
            }
            
            filteredLogs.forEach(log => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${log.id}</td>
                    <td>${log.action}</td>
                    <td>${log.user}</td>
                    <td>${log.details}</td>
                    <td>${log.timestamp}</td>
                    <td>${log.ip}</td>
                `;
                tbody.appendChild(row);
            });
        }

        function initializeCharts() {
            // Financial Chart
            const financialCtx = document.getElementById('financialChart').getContext('2d');
            const financialChart = new Chart(financialCtx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Revenue (KSh)',
                        data: [120000, 150000, 180000, 140000, 160000, 200000],
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
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Usage Chart
            const usageCtx = document.getElementById('usageChart').getContext('2d');
            const usageChart = new Chart(usageCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [
                        {
                            label: 'Active Users',
                            data: [45, 60, 75, 80, 90, 110],
                            backgroundColor: 'rgba(46, 204, 113, 0.2)',
                            borderColor: 'rgba(46, 204, 113, 1)',
                            borderWidth: 2,
                            tension: 0.4
                        },
                        {
                            label: 'New Orders',
                            data: [120, 150, 180, 140, 160, 200],
                            backgroundColor: 'rgba(241, 196, 15, 0.2)',
                            borderColor: 'rgba(241, 196, 15, 1)',
                            borderWidth: 2,
                            tension: 0.4
                        }
                    ]
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
            
            // Inventory Chart
            const inventoryCtx = document.getElementById('inventoryChart').getContext('2d');
            const inventoryChart = new Chart(inventoryCtx, {
                type: 'pie',
                data: {
                    labels: ['Cotton', 'Silk', 'Linen', 'Wool', 'Polyester'],
                    datasets: [{
                        data: [45, 30, 15, 25, 20],
                        backgroundColor: [
                            'rgba(52, 152, 219, 0.7)',
                            'rgba(46, 204, 113, 0.7)',
                            'rgba(241, 196, 15, 0.7)',
                            'rgba(155, 89, 182, 0.7)',
                            'rgba(231, 76, 60, 0.7)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        function setupFormHandlers() {
            // Add user form
            document.getElementById('addUserForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const newUser = {
                    id: "U" + Math.floor(1000 + Math.random() * 9000),
                    name: document.getElementById('newUserName').value,
                    email: document.getElementById('newUserEmail').value,
                    role: document.getElementById('newUserRole').value,
                    status: "active",
                    lastLogin: new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) + " – " + 
                              new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
                };
                
                users.push(newUser);
                loadUsersTable();
                updateStats();
                showNotification(`User ${newUser.name} created successfully!`, 'success');
                closeModal('addUserModal');
            });
            
            // Edit user form
            document.getElementById('editUserForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const userId = document.getElementById('editUserId').value;
                const user = users.find(u => u.id === userId);
                
                if (user) {
                    user.name = document.getElementById('editUserName').value;
                    user.email = document.getElementById('editUserEmail').value;
                    user.role = document.getElementById('editUserRole').value;
                    user.status = document.getElementById('editUserStatus').value;
                    
                    loadUsersTable();
                    updateStats();
                    showNotification(`User ${user.name} updated successfully!`, 'success');
                    closeModal('editUserModal');
                }
            });
            
            // Add role form
            document.getElementById('addRoleForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const selectedPermissions = [];
                document.querySelectorAll('#newRolePermissions input:checked').forEach(checkbox => {
                    selectedPermissions.push(checkbox.id.replace('new_perm_', ''));
                });
                
                const newRole = {
                    name: document.getElementById('newRoleName').value.toLowerCase(),
                    description: document.getElementById('newRoleDescription').value,
                    users: 0,
                    permissions: selectedPermissions
                };
                
                roles.push(newRole);
                loadRolesTable();
                updateStats();
                showNotification(`Role ${newRole.name} created successfully!`, 'success');
                closeModal('addRoleModal');
            });
            
            // Business details form
            document.getElementById('businessDetailsForm').addEventListener('submit', function(e) {
                e.preventDefault();
                showNotification('Business details updated successfully!', 'success');
            });
            
            // Payment settings form
            document.getElementById('paymentSettingsForm').addEventListener('submit', function(e) {
                e.preventDefault();
                showNotification('Payment settings updated successfully!', 'success');
            });
        }

        function updateStats() {
            document.getElementById('total-users').textContent = users.length;
            document.getElementById('active-roles').textContent = roles.length;
            
            // Update logs badge
            const today = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            const todaysLogs = auditLogs.filter(log => log.timestamp.includes(today.split(',')[0])).length;
            document.getElementById('logs-badge').textContent = todaysLogs;
            document.getElementById('logs-badge').style.display = todaysLogs > 0 ? 'block' : 'none';
        }

        // Show page function
        function showPage(pageId) {
            // Hide all pages
            document.querySelectorAll('.page-content').forEach(page => {
                page.classList.remove('active');
            });
            
            // Show selected page
            document.getElementById(`${pageId}-page`).classList.add('active');
            
            // Update page title
            const pageTitles = {
                'dashboard': 'Admin Control Center',
                'users': 'User Management',
                'roles': 'Role Management',
                'settings': 'System Settings',
                'logs': 'Audit Logs',
                'reports': 'Reports Center'
            };
            document.getElementById('page-title').textContent = pageTitles[pageId] || 'Admin Control Center';
            
            // Update active nav items
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            
            document.querySelectorAll('.header-nav a').forEach(link => {
                link.classList.remove('active');
            });
            
            // Set the appropriate nav item as active
            if (pageId === 'dashboard') {
                document.querySelector('.nav-item[onclick="showPage(\'dashboard\')"]').classList.add('active');
                document.querySelector('.header-nav a[onclick="showPage(\'dashboard\')"]').classList.add('active');
            } else if (pageId === 'users') {
                document.querySelector('.nav-item[onclick="showPage(\'users\')"]').classList.add('active');
                document.querySelector('.header-nav a[onclick="showPage(\'users\')"]').classList.add('active');
            } else if (pageId === 'roles') {
                document.querySelector('.nav-item[onclick="showPage(\'roles\')"]').classList.add('active');
            } else if (pageId === 'settings') {
                document.querySelector('.nav-item[onclick="showPage(\'settings\')"]').classList.add('active');
                document.querySelector('.header-nav a[onclick="showPage(\'settings\')"]').classList.add('active');
            } else if (pageId === 'logs') {
                document.querySelector('.nav-item[onclick="showPage(\'logs\')"]').classList.add('active');
                document.querySelector('.header-nav a[onclick="showPage(\'logs\')"]').classList.add('active');
            } else if (pageId === 'reports') {
                document.querySelector('.nav-item[onclick="showPage(\'reports\')"]').classList.add('active');
                document.querySelector('.header-nav a[onclick="showPage(\'reports\')"]').classList.add('active');
            }
            
            // Special initialization for certain pages
            if (pageId === 'reports') {
                // Reinitialize charts when reports page is shown
                setTimeout(initializeCharts, 100);
            }
        }

        // User management functions
        function showAddUserModal() {
            document.getElementById('addUserModal').style.display = 'flex';
        }

        function editUser(userId) {
            const user = users.find(u => u.id === userId);
            if (user) {
                document.getElementById('editUserId').value = user.id;
                document.getElementById('editUserName').value = user.name;
                document.getElementById('editUserEmail').value = user.email;
                document.getElementById('editUserRole').value = user.role;
                document.getElementById('editUserStatus').value = user.status;
                document.getElementById('editUserModal').style.display = 'flex';
            }
        }

        function viewUser(userId) {
            const user = users.find(u => u.id === userId);
            if (user) {
                const content = document.getElementById('viewUserContent');
                content.innerHTML = `
                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <div>
                                <strong>User ID:</strong> ${user.id}
                            </div>
                            <div>
                                <span class="status status-${user.status}">${user.status.charAt(0).toUpperCase() + user.status.slice(1)}</span>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                            <div>
                                <strong>Name:</strong> ${user.name}
                            </div>
                            <div>
                                <strong>Email:</strong> ${user.email}
                            </div>
                            <div>
                                <strong>Role:</strong> ${user.role.charAt(0).toUpperCase() + user.role.slice(1)}
                            </div>
                            <div>
                                <strong>Last Login:</strong> ${user.lastLogin}
                            </div>
                        </div>
                        
                        <div style="margin-top: 20px;">
                            <button class="btn" onclick="editUser('${user.id}')">
                                <i class="fas fa-edit"></i> Edit User
                            </button>
                        </div>
                    </div>
                `;
                document.getElementById('viewUserModal').style.display = 'flex';
            }
        }

        function deleteUser(userId) {
            const user = users.find(u => u.id === userId);
            if (user) {
                document.getElementById('deleteModalContent').innerHTML = `
                    <p>Are you sure you want to delete user <strong>${user.name}</strong> (${user.email})?</p>
                    <p>This action cannot be undone.</p>
                `;
                document.getElementById('deleteModal').style.display = 'flex';
                
                // Store the user ID in the delete button for later reference
                document.querySelector('#deleteModal button.btn-danger').setAttribute('data-user-id', userId);
            }
        }

        function confirmDeleteUser() {
            const userId = document.getElementById('editUserId').value;
            deleteUser(userId);
        }

        // Role management functions
        function showAddRoleModal() {
            document.getElementById('addRoleModal').style.display = 'flex';
        }

        function editRole(roleName) {
            // In a real app, this would open the edit role modal
            showNotification(`Editing role: ${roleName}`, 'info');
        }

        function deleteRole(roleName) {
            document.getElementById('deleteModalContent').innerHTML = `
                <p>Are you sure you want to delete the <strong>${roleName}</strong> role?</p>
                <p>This action will affect ${roles.find(r => r.name === roleName).users} user(s).</p>
            `;
            document.getElementById('deleteModal').style.display = 'flex';
            
            // Store the role name in the delete button for later reference
            document.querySelector('#deleteModal button.btn-danger').setAttribute('data-role-name', roleName);
        }

        // Logs functions
        function filterLogs(filter) {
            loadAuditLogs(filter);
        }

        function clearOldLogs() {
            showNotification('Old logs cleared successfully!', 'success');
            // In a real app, this would actually clear old logs
        }

        // Reports functions
        function generateFinancialReport() {
            showNotification('Financial report generated successfully!', 'success');
        }

        function exportFinancialReport() {
            showNotification('Financial report exported to Excel!', 'success');
        }

        function generateUsageReport() {
            showNotification('Usage report generated successfully!', 'success');
        }

        function exportUsageReport() {
            showNotification('Usage report exported to Excel!', 'success');
        }

        function generateInventoryReport() {
            showNotification('Inventory report generated successfully!', 'success');
        }

        function exportInventoryReport() {
            showNotification('Inventory report exported to Excel!', 'success');
        }

        function exportSystemData(format) {
            showNotification(`System data exported as ${format.toUpperCase()}!`, 'success');
        }

        // Modal functions
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function confirmDelete() {
            const deleteBtn = document.querySelector('#deleteModal button.btn-danger');
            
            if (deleteBtn.hasAttribute('data-user-id')) {
                // Delete user
                const userId = deleteBtn.getAttribute('data-user-id');
                const index = users.findIndex(u => u.id === userId);
                
                if (index !== -1) {
                    const userName = users[index].name;
                    users.splice(index, 1);
                    loadUsersTable();
                    updateStats();
                    showNotification(`User ${userName} deleted successfully!`, 'success');
                }
            } else if (deleteBtn.hasAttribute('data-role-name')) {
                // Delete role
                const roleName = deleteBtn.getAttribute('data-role-name');
                const index = roles.findIndex(r => r.name === roleName);
                
                if (index !== -1) {
                    roles.splice(index, 1);
                    loadRolesTable();
                    updateStats();
                    showNotification(`Role ${roleName} deleted successfully!`, 'success');
                }
            }
            
            closeModal('deleteModal');
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

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            });
        }

        function loadAdminStats() {
    fetch('api.php?action=dashboard_data')
        .then(res => res.json())
        .then(data => {
            document.getElementById("total-users").textContent = data.users;
            document.getElementById("total-orders").textContent = data.orders;
            document.getElementById("total-revenue").textContent = "KES " + data.revenue;
        });
}

setInterval(loadAdminStats, 5000); // every 5 seconds
loadAdminStats();
    </script>
</body>
</html>