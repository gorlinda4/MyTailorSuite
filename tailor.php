<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "tailor") {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tailor Dashboard - TailorSuite</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            min-height: 100vh;
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

        /* Dashboard Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        @media (max-width: 1200px) {
            .dashboard-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background-color: var(--card-bg);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 20px;
        }

        .card-icon.orders {
            background-color: rgba(52, 152, 219, 0.1);
            color: #3498db;
        }

        .card-icon.deadlines {
            background-color: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }

        .card-icon.completed {
            background-color: rgba(39, 174, 96, 0.1);
            color: #27ae60;
        }

        .card-icon.issues {
            background-color: rgba(241, 196, 15, 0.1);
            color: #f1c40f;
        }

        .card-info {
            flex: 1;
        }

        .card-title {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 5px;
        }

        .card-value {
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

        .order-status {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-not-started {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-in-progress {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
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

        .btn-info {
            background-color: var(--info-color);
        }

        .btn-info:hover {
            background-color: #138496;
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

        /* Work Schedule */
        .schedule-container {
            overflow-x: auto;
        }

        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        .schedule-table th, 
        .schedule-table td {
            padding: 8px;
            text-align: center;
            border: 1px solid var(--border-color);
        }

        .schedule-table th {
            background-color: #f8f9fa;
            color: var(--text-secondary);
            font-weight: normal;
        }

        .time-slot {
            text-align: right;
            padding-right: 10px;
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .schedule-task {
            background-color: #e2f0fd;
            border-radius: 4px;
            padding: 5px;
            margin: 2px;
            font-size: 12px;
            cursor: pointer;
        }

        .schedule-task.urgent {
            background-color: #f8d7da;
        }

        .schedule-task.completed {
            background-color: #d4edda;
            text-decoration: line-through;
        }

        /* Notes & Issues */
        .notes-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .note-card {
            background-color: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .note-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .note-date {
            font-size: 12px;
            color: var(--text-secondary);
        }

        .note-content {
            margin-bottom: 10px;
        }

        .note-actions {
            display: flex;
            gap: 5px;
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
            max-width: 600px;
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

        /* Measurement Viewer */
        .measurement-viewer {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        @media (max-width: 768px) {
            .measurement-viewer {
                grid-template-columns: 1fr;
            }
        }

        .measurement-details {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
        }

        .measurement-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed var(--border-color);
        }

        .measurement-item:last-child {
            border-bottom: none;
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
            <li class="nav-item" onclick="showPage('assigned-orders')">
                <i class="fas fa-clipboard-list"></i>
                <span>Assigned Orders</span>
                <span class="notification-badge" id="orders-badge">6</span>
            </li>
            <li class="nav-item" onclick="showPage('work-schedule')">
                <i class="fas fa-calendar-alt"></i>
                <span>Work Schedule</span>
            </li>
            <li class="nav-item" onclick="showPage('measurements')">
                <i class="fas fa-ruler-combined"></i>
                <span>Measurements</span>
            </li>
            <li class="nav-item" onclick="showPage('completed-work')">
                <i class="fas fa-check-circle"></i>
                <span>Completed Work</span>
            </li>
            
            <li class="nav-menu-title">Notes & Issues</li>
            <li class="nav-item" onclick="showPage('notes-issues')">
                <i class="fas fa-sticky-note"></i>
                <span>Notes & Issues</span>
                <span class="notification-badge" id="issues-badge">1</span>
            </li>
            
            <li class="nav-menu-title">Profile</li>
            <li class="nav-item" onclick="showPage('profile')">
                <i class="fas fa-user-cog"></i>
                <span>Profile Settings</span>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1 id="page-title">Tailor Dashboard</h1>
            <div class="user-info">
                <img src="https://via.placeholder.com/40" alt="User">
                <span>Tailor 1</span>
                <div class="dropdown-menu">
                    <a href="#" onclick="showPage('profile')"><i class="fas fa-user"></i> Profile</a>
                    <a href="#" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>

        <!-- Dashboard Page -->
        <div id="dashboard-page" class="page-content active">
            <div class="dashboard-cards">
                <div class="card" onclick="showPage('assigned-orders')">
                    <div class="card-icon orders">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="card-info">
                        <div class="card-title">Assigned Orders</div>
                        <div class="card-value" id="assigned-orders-count">6</div>
                    </div>
                </div>
                <div class="card" onclick="showPage('work-schedule')">
                    <div class="card-icon deadlines">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="card-info">
                        <div class="card-title">Deadlines Today</div>
                        <div class="card-value" id="deadlines-count">2</div>
                    </div>
                </div>
                <div class="card" onclick="showPage('completed-work')">
                    <div class="card-icon completed">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="card-info">
                        <div class="card-title">Completed This Week</div>
                        <div class="card-value" id="completed-count">3</div>
                    </div>
                </div>
                <div class="card" onclick="showPage('notes-issues')">
                    <div class="card-icon issues">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="card-info">
                        <div class="card-title">Pending Issues</div>
                        <div class="card-value" id="issues-count">1</div>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2><i class="fas fa-clipboard-list"></i> Recent Assigned Orders</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Garment</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="recent-orders-table">
                        <!-- Orders will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <div class="section">
                <h2><i class="fas fa-sticky-note"></i> Recent Notes & Issues</h2>
                <div class="notes-container" id="recent-notes">
                    <!-- Notes will be populated by JavaScript -->
                </div>
            </div>
        </div>

        <!-- Assigned Orders Page -->
        <div id="assigned-orders-page" class="page-content">
            <h2><i class="fas fa-clipboard-list"></i> Assigned Orders</h2>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Garment</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Customer</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="assigned-orders-table">
                    <!-- Orders will be populated by JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Work Schedule Page -->
        <div id="work-schedule-page" class="page-content">
            <h2><i class="fas fa-calendar-alt"></i> Work Schedule</h2>
            
            <div class="form-group">
                <label for="schedule-week">Select Week</label>
                <input type="week" id="schedule-week" onchange="loadWorkSchedule()">
            </div>
            
            <div class="schedule-container">
                <table class="schedule-table" id="work-schedule-table">
                    <!-- Schedule will be populated by JavaScript -->
                </table>
            </div>
            
            <div style="margin-top: 20px;">
                <button class="btn" onclick="showAddTaskModal()"><i class="fas fa-plus"></i> Add Task</button>
            </div>
        </div>

        <!-- Measurements Page -->
        <div id="measurements-page" class="page-content">
            <h2><i class="fas fa-ruler-combined"></i> Measurements</h2>
            
            <div class="form-group">
                <label for="measurement-order">Select Order</label>
                <select id="measurement-order" onchange="loadMeasurements()">
                    <option value="">-- Select an order --</option>
                    <!-- Options will be populated by JavaScript -->
                </select>
            </div>
            
            <div id="measurement-content" style="display: none;">
                <button class="btn" onclick="downloadMeasurementSheet()" style="margin-bottom: 15px;">
                    <i class="fas fa-download"></i> Download Measurement Sheet
                </button>
                
                <div class="measurement-viewer">
                    <div class="measurement-details">
                        <h3 style="margin-bottom: 15px;">Measurement Details</h3>
                        <div id="measurement-details">
                            <!-- Measurements will be populated by JavaScript -->
                        </div>
                    </div>
                    
                    <div>
                        <h3 style="margin-bottom: 15px;">Measurement Guide</h3>
                        <img src="https://via.placeholder.com/400x300?text=Measurement+Guide" alt="Measurement Guide" 
                             style="width: 100%; border-radius: 8px; border: 1px solid var(--border-color);">
                    </div>
                </div>
            </div>
        </div>

        <!-- Completed Work Page -->
        <div id="completed-work-page" class="page-content">
            <h2><i class="fas fa-check-circle"></i> Completed Work</h2>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Garment</th>
                        <th>Completed Date</th>
                        <th>Customer</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="completed-orders-table">
                    <!-- Completed orders will be populated by JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Notes & Issues Page -->
        <div id="notes-issues-page" class="page-content">
            <h2><i class="fas fa-sticky-note"></i> Notes & Issues</h2>
            
            <div style="margin-bottom: 20px;">
                <button class="btn" onclick="showAddNoteModal()"><i class="fas fa-plus"></i> Add New Note/Issue</button>
            </div>
            
            <div class="notes-container" id="all-notes">
                <!-- Notes will be populated by JavaScript -->
            </div>
        </div>

        <!-- Profile Page -->
        <div id="profile-page" class="page-content">
            <h2><i class="fas fa-user-cog"></i> Profile Settings</h2>
            
            <form id="profile-form">
                <div class="form-group">
                    <label for="profile-name">Full Name</label>
                    <input type="text" id="profile-name" value="Tailor 1" required>
                </div>
                
                <div class="form-group">
                    <label for="profile-email">Email</label>
                    <input type="email" id="profile-email" value="tailor1@tailorsuite.com" required>
                </div>
                
                <div class="form-group">
                    <label for="profile-phone">Phone Number</label>
                    <input type="tel" id="profile-phone" value="+254712345678" required>
                </div>
                
                <div class="form-group">
                    <label for="profile-specialty">Specialty</label>
                    <select id="profile-specialty" required>
                        <option value="formal">Formal Wear</option>
                        <option value="casual" selected>Casual Wear</option>
                        <option value="wedding">Wedding Dresses</option>
                        <option value="children">Children's Wear</option>
                    </select>
                </div>
                
                <button type="submit" class="btn">Save Changes</button>
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
            
            <div id="order-actions" style="margin-top: 20px; display: flex; gap: 10px;">
                <!-- Actions will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Add Task Modal -->
    <div id="add-task-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-plus"></i> Add Task to Schedule</h2>
                <button class="close-btn" onclick="closeModal('add-task-modal')">&times;</button>
            </div>
            
            <form id="add-task-form">
                <div class="form-group">
                    <label for="task-order">Select Order</label>
                    <select id="task-order" required>
                        <option value="">-- Select an order --</option>
                        <!-- Options will be populated by JavaScript -->
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="task-date">Date</label>
                    <input type="date" id="task-date" required>
                </div>
                
                <div class="form-group">
                    <label for="task-time">Time Slot</label>
                    <select id="task-time" required>
                        <option value="">-- Select time slot --</option>
                        <option value="9:00 AM">9:00 AM</option>
                        <option value="10:00 AM">10:00 AM</option>
                        <option value="11:00 AM">11:00 AM</option>
                        <option value="12:00 PM">12:00 PM</option>
                        <option value="1:00 PM">1:00 PM</option>
                        <option value="2:00 PM">2:00 PM</option>
                        <option value="3:00 PM">3:00 PM</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="task-urgent">Mark as Urgent</label>
                    <input type="checkbox" id="task-urgent">
                </div>
                
                <div class="form-group">
                    <label for="task-notes">Notes</label>
                    <textarea id="task-notes" rows="3"></textarea>
                </div>
                
                <button type="submit" class="btn">Add Task</button>
            </form>
        </div>
    </div>

    <!-- Add Note Modal -->
    <div id="add-note-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-plus"></i> Add New Note/Issue</h2>
                <button class="close-btn" onclick="closeModal('add-note-modal')">&times;</button>
            </div>
            
            <form id="add-note-form">
                <div class="form-group">
                    <label for="note-type">Type</label>
                    <select id="note-type" required>
                        <option value="note">Personal Note</option>
                        <option value="issue">Issue (Notify Manager)</option>
                    </select>
                </div>
                
                <div class="form-group" id="note-order-group" style="display: none;">
                    <label for="note-order">Related Order</label>
                    <select id="note-order">
                        <option value="">-- Select order (optional) --</option>
                        <!-- Options will be populated by JavaScript -->
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="note-title">Title</label>
                    <input type="text" id="note-title" required>
                </div>
                
                <div class="form-group">
                    <label for="note-content">Content</label>
                    <textarea id="note-content" rows="5" required></textarea>
                </div>
                
                <button type="submit" class="btn">Save Note</button>
            </form>
        </div>
    </div>

    <!-- Notification Toast -->
    <div id="notification-toast" class="notification">
        <i class="fas fa-check-circle"></i>
        <span id="notification-message">Action completed successfully!</span>
    </div>

    <script>
        // Sample data for the dashboard
        let orders = [
            {
                id: "T1021",
                garment: "Shirt",
                status: "in-progress",
                dueDate: "2025-04-06",
                customer: "John Doe",
                measurements: {
                    chest: "38",
                    waist: "32",
                    hips: "40",
                    length: "30",
                    sleeve: "24",
                    neck: "15"
                },
                notes: "Customer prefers slim fit"
            },
            {
                id: "T1022",
                garment: "Suit",
                status: "not-started",
                dueDate: "2025-04-09",
                customer: "Jane Smith",
                measurements: {
                    chest: "42",
                    waist: "36",
                    hips: "44",
                    length: "42",
                    sleeve: "25",
                    neck: "16"
                },
                notes: "Formal business suit - urgent"
            },
            {
                id: "T1023",
                garment: "Dress",
                status: "in-progress",
                dueDate: "2025-04-08",
                customer: "Sarah Johnson",
                measurements: {
                    chest: "36",
                    waist: "28",
                    hips: "38",
                    length: "45",
                    sleeve: "22"
                },
                notes: "Evening gown with lace details"
            },
            {
                id: "T1024",
                garment: "Pants",
                status: "not-started",
                dueDate: "2025-04-12",
                customer: "Robert Brown",
                measurements: {
                    waist: "34",
                    inseam: "32",
                    length: "42"
                },
                notes: "Casual chino pants"
            },
            {
                id: "T1025",
                garment: "Blouse",
                status: "in-progress",
                dueDate: "2025-04-07",
                customer: "Emily Davis",
                measurements: {
                    chest: "34",
                    waist: "30",
                    hips: "36",
                    length: "28",
                    sleeve: "21"
                },
                notes: "Silk blouse with French cuffs"
            },
            {
                id: "T1026",
                garment: "Jacket",
                status: "not-started",
                dueDate: "2025-04-15",
                customer: "Michael Wilson",
                measurements: {
                    chest: "44",
                    waist: "38",
                    length: "40",
                    sleeve: "26",
                    neck: "17"
                },
                notes: "Leather jacket with lining"
            }
        ];

        let completedOrders = [
            {
                id: "T1019",
                garment: "Shirt",
                completedDate: "2025-04-02",
                customer: "David Miller",
                measurements: {
                    chest: "40",
                    waist: "34",
                    length: "32",
                    sleeve: "25",
                    neck: "16"
                }
            },
            {
                id: "T1018",
                garment: "Dress",
                completedDate: "2025-03-30",
                customer: "Lisa Taylor",
                measurements: {
                    chest: "38",
                    waist: "30",
                    hips: "40",
                    length: "48",
                    sleeve: "23"
                }
            },
            {
                id: "T1017",
                garment: "Pants",
                completedDate: "2025-03-28",
                customer: "James Anderson",
                measurements: {
                    waist: "36",
                    inseam: "34",
                    length: "44"
                }
            }
        ];

        let notes = [
            {
                id: 1,
                type: "issue",
                title: "Material delay - waiting for shipment",
                content: "Silk fabric for order T1025 is delayed from supplier. Expected delivery is 2 days late.",
                date: "2025-04-03",
                orderId: "T1025",
                resolved: false
            },
            {
                id: 2,
                type: "note",
                title: "Customer preference",
                content: "John Doe (T1021) prefers slightly longer sleeves than standard measurement.",
                date: "2025-04-02",
                orderId: "T1021",
                resolved: false
            },
            {
                id: 3,
                type: "note",
                title: "Special thread needed",
                content: "Need to order gold thread for the embroidery on T1023.",
                date: "2025-04-01",
                orderId: "T1023",
                resolved: true
            }
        ];

        let workSchedule = [
            {
                id: 1,
                orderId: "T1021",
                date: "2025-04-05",
                time: "9:00 AM",
                urgent: false,
                completed: false,
                notes: "Start shirt for John Doe"
            },
            {
                id: 2,
                orderId: "T1025",
                date: "2025-04-05",
                time: "1:00 PM",
                urgent: true,
                completed: false,
                notes: "Urgent - blouse for Emily Davis"
            },
            {
                id: 3,
                orderId: "T1023",
                date: "2025-04-06",
                time: "10:00 AM",
                urgent: false,
                completed: false,
                notes: "Dress for Sarah Johnson"
            },
            {
                id: 4,
                orderId: "T1021",
                date: "2025-04-06",
                time: "2:00 PM",
                urgent: false,
                completed: false,
                notes: "Continue shirt for John Doe"
            }
        ];

        // Current user profile
        let profile = {
            name: "Tailor 1",
            email: "tailor1@tailorsuite.com",
            phone: "+254712345678",
            specialty: "casual"
        };

        // Initialize the dashboard when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Load all data
            updateDashboardStats();
            loadRecentOrders();
            loadRecentNotes();
            loadAssignedOrders();
            loadCompletedOrders();
            loadNotes();
            
            // Set current week for schedule
            const currentWeek = getCurrentWeek();
            document.getElementById('schedule-week').value = currentWeek;
            loadWorkSchedule();
            
            // Initialize forms
            initForms();
            
            // Set current date for task modal
            document.getElementById('task-date').valueAsDate = new Date();
        });

        // Helper function to get current week in YYYY-Www format
        function getCurrentWeek() {
            const now = new Date();
            const oneJan = new Date(now.getFullYear(), 0, 1);
            const numberOfDays = Math.floor((now - oneJan) / (24 * 60 * 60 * 1000));
            const weekNumber = Math.ceil((now.getDay() + 1 + numberOfDays) / 7);
            return now.getFullYear() + '-W' + (weekNumber < 10 ? '0' + weekNumber : weekNumber);
        }

        // Update dashboard statistics
        function updateDashboardStats() {
            const assignedCount = orders.length;
            const deadlinesToday = orders.filter(order => {
                const today = new Date().toISOString().split('T')[0];
                return order.dueDate === today;
            }).length;
            
            const completedThisWeek = completedOrders.filter(order => {
                const oneWeekAgo = new Date();
                oneWeekAgo.setDate(oneWeekAgo.getDate() - 7);
                return new Date(order.completedDate) >= oneWeekAgo;
            }).length;
            
            const pendingIssues = notes.filter(note => note.type === 'issue' && !note.resolved).length;
            
            document.getElementById('assigned-orders-count').textContent = assignedCount;
            document.getElementById('deadlines-count').textContent = deadlinesToday;
            document.getElementById('completed-count').textContent = completedThisWeek;
            document.getElementById('issues-count').textContent = pendingIssues;
            
            // Update badge counts
            document.getElementById('orders-badge').textContent = assignedCount;
            document.getElementById('issues-badge').textContent = pendingIssues;
        }

        // Load recent orders for dashboard
        function loadRecentOrders() {
            const tableBody = document.getElementById('recent-orders-table');
            tableBody.innerHTML = '';
            
            // Show only the first 3 orders
            const recentOrders = orders.slice(0, 3);
            
            recentOrders.forEach(order => {
                const row = document.createElement('tr');
                
                let statusClass, statusText;
                switch(order.status) {
                    case 'in-progress':
                        statusClass = 'status-in-progress';
                        statusText = 'In Progress';
                        break;
                    case 'not-started':
                        statusClass = 'status-not-started';
                        statusText = 'Not Started';
                        break;
                    default:
                        statusClass = 'status-not-started';
                        statusText = 'Not Started';
                }
                
                const dueDate = new Date(order.dueDate).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                });
                
                row.innerHTML = `
                    <td class="order-id">${order.id}</td>
                    <td>${order.garment}</td>
                    <td><span class="order-status ${statusClass}">${statusText}</span></td>
                    <td>${dueDate}</td>
                    <td>
                        <button class="btn btn-sm" onclick="viewOrderDetails('${order.id}')">View</button>
                    </td>
                `;
                
                tableBody.appendChild(row);
            });
        }

        // Load recent notes for dashboard
        function loadRecentNotes() {
            const container = document.getElementById('recent-notes');
            container.innerHTML = '';
            
            // Show only the first 2 notes
            const recentNotes = notes.slice(0, 2);
            
            recentNotes.forEach(note => {
                const noteCard = document.createElement('div');
                noteCard.className = 'note-card';
                
                const date = new Date(note.date).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                });
                
                const orderInfo = note.orderId ? ` (Order: ${note.orderId})` : '';
                
                noteCard.innerHTML = `
                    <div class="note-header">
                        <strong>${note.title}${orderInfo}</strong>
                        <span class="note-date">${date}</span>
                    </div>
                    <div class="note-content">${note.content}</div>
                    <div class="note-actions">
                        ${note.resolved ? '' : `<button class="btn btn-sm btn-success" onclick="resolveNote(${note.id})">Mark Resolved</button>`}
                        <button class="btn btn-sm btn-danger" onclick="deleteNote(${note.id})">Delete</button>
                    </div>
                `;
                
                container.appendChild(noteCard);
            });
        }

        // Load all assigned orders
        function loadAssignedOrders() {
            const tableBody = document.getElementById('assigned-orders-table');
            tableBody.innerHTML = '';
            
            // Populate order dropdowns
            const orderSelects = [
                document.getElementById('measurement-order'),
                document.getElementById('task-order'),
                document.getElementById('note-order')
            ];
            
            orderSelects.forEach(select => {
                if (select) {
                    // Clear existing options except the first one
                    while (select.options.length > 1) {
                        select.remove(1);
                    }
                    
                    // Add current orders
                    orders.forEach(order => {
                        const option = document.createElement('option');
                        option.value = order.id;
                        option.textContent = `${order.id} - ${order.garment} (${order.customer})`;
                        select.appendChild(option);
                    });
                }
            });
            
            orders.forEach(order => {
                const row = document.createElement('tr');
                
                let statusClass, statusText;
                switch(order.status) {
                    case 'in-progress':
                        statusClass = 'status-in-progress';
                        statusText = 'In Progress';
                        break;
                    case 'not-started':
                        statusClass = 'status-not-started';
                        statusText = 'Not Started';
                        break;
                    default:
                        statusClass = 'status-not-started';
                        statusText = 'Not Started';
                }
                
                const dueDate = new Date(order.dueDate).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                });
                
                row.innerHTML = `
                    <td class="order-id">${order.id}</td>
                    <td>${order.garment}</td>
                    <td><span class="order-status ${statusClass}">${statusText}</span></td>
                    <td>${dueDate}</td>
                    <td>${order.customer}</td>
                    <td>
                        <button class="btn btn-sm" onclick="viewOrderDetails('${order.id}')">View</button>
                        ${order.status === 'in-progress' ? 
                          `<button class="btn btn-sm btn-success" onclick="completeOrder('${order.id}')">Complete</button>` : 
                          `<button class="btn btn-sm" onclick="startOrder('${order.id}')">Start Work</button>`}
                    </td>
                `;
                
                tableBody.appendChild(row);
            });
        }

        // Load completed orders
        function loadCompletedOrders() {
            const tableBody = document.getElementById('completed-orders-table');
            tableBody.innerHTML = '';
            
            completedOrders.forEach(order => {
                const row = document.createElement('tr');
                
                const completedDate = new Date(order.completedDate).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                });
                
                row.innerHTML = `
                    <td class="order-id">${order.id}</td>
                    <td>${order.garment}</td>
                    <td>${completedDate}</td>
                    <td>${order.customer}</td>
                    <td>
                        <button class="btn btn-sm" onclick="viewOrderDetails('${order.id}')">View</button>
                    </td>
                `;
                
                tableBody.appendChild(row);
            });
        }

        // Load work schedule
        function loadWorkSchedule() {
            const table = document.getElementById('work-schedule-table');
            table.innerHTML = '';
            
            // Create header row
            const headerRow = document.createElement('tr');
            headerRow.innerHTML = `
                <th>Time</th>
                <th>Monday</th>
                <th>Tuesday</th>
                <th>Wednesday</th>
                <th>Thursday</th>
                <th>Friday</th>
                <th>Saturday</th>
            `;
            table.appendChild(headerRow);
            
            // Time slots
            const timeSlots = [
                '9:00 AM', '10:00 AM', '11:00 AM', 
                '12:00 PM', '1:00 PM', '2:00 PM', '3:00 PM'
            ];
            
            // Get the selected week
            const weekInput = document.getElementById('schedule-week').value;
            const [year, week] = weekInput.split('-W');
            
            // Create date for Monday of the selected week
            const monday = new Date(year, 0, 1 + (week - 1) * 7);
            while (monday.getDay() !== 1) {
                monday.setDate(monday.getDate() - 1);
            }
            
            // Create dates for the entire week
            const weekDates = [];
            for (let i = 0; i < 6; i++) {
                const date = new Date(monday);
                date.setDate(monday.getDate() + i);
                weekDates.push(date.toISOString().split('T')[0]);
            }
            
            // Create rows for each time slot
            timeSlots.forEach(timeSlot => {
                const row = document.createElement('tr');
                row.innerHTML = `<td class="time-slot">${timeSlot}</td>`;
                
                // Add cells for each day of the week
                weekDates.forEach(date => {
                    const cell = document.createElement('td');
                    
                    // Find tasks for this time slot and date
                    const tasks = workSchedule.filter(task => 
                        task.date === date && task.time === timeSlot
                    );
                    
                    if (tasks.length > 0) {
                        tasks.forEach(task => {
                            const order = orders.find(o => o.id === task.orderId);
                            const taskDiv = document.createElement('div');
                            taskDiv.className = `schedule-task ${task.urgent ? 'urgent' : ''} ${task.completed ? 'completed' : ''}`;
                            taskDiv.innerHTML = `
                                <strong>${task.orderId}</strong>: ${order ? order.garment : 'Unknown'}
                                ${task.notes ? `<br><small>${task.notes}</small>` : ''}
                            `;
                            taskDiv.onclick = () => editTask(task.id);
                            cell.appendChild(taskDiv);
                        });
                    }
                    
                    row.appendChild(cell);
                });
                
                table.appendChild(row);
            });
        }

        // Load measurements for selected order
        function loadMeasurements() {
            const orderId = document.getElementById('measurement-order').value;
            const contentDiv = document.getElementById('measurement-content');
            
            if (!orderId) {
                contentDiv.style.display = 'none';
                return;
            }
            
            const order = orders.find(o => o.id === orderId) || 
                         completedOrders.find(o => o.id === orderId);
            
            if (order && order.measurements) {
                const detailsDiv = document.getElementById('measurement-details');
                detailsDiv.innerHTML = '';
                
                for (const [key, value] of Object.entries(order.measurements)) {
                    const itemDiv = document.createElement('div');
                    itemDiv.className = 'measurement-item';
                    itemDiv.innerHTML = `
                        <span>${key.charAt(0).toUpperCase() + key.slice(1)}:</span>
                        <span>${value}"</span>
                    `;
                    detailsDiv.appendChild(itemDiv);
                }
                
                if (order.notes) {
                    const notesDiv = document.createElement('div');
                    notesDiv.style.marginTop = '15px';
                    notesDiv.style.paddingTop = '10px';
                    notesDiv.style.borderTop = '1px dashed var(--border-color)';
                    notesDiv.innerHTML = `
                        <strong>Notes:</strong>
                        <p>${order.notes}</p>
                    `;
                    detailsDiv.appendChild(notesDiv);
                }
                
                contentDiv.style.display = 'block';
            } else {
                contentDiv.style.display = 'none';
                showNotification('No measurements found for this order', 'error');
            }
        }

        // Load all notes
        function loadNotes() {
            const container = document.getElementById('all-notes');
            container.innerHTML = '';
            
            notes.forEach(note => {
                const noteCard = document.createElement('div');
                noteCard.className = 'note-card';
                
                const date = new Date(note.date).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                });
                
                const orderInfo = note.orderId ? ` (Order: ${note.orderId})` : '';
                const typeBadge = note.type === 'issue' ? 
                    `<span class="order-status status-in-progress" style="margin-left: 10px;">Issue</span>` : 
                    `<span class="order-status status-not-started" style="margin-left: 10px;">Note</span>`;
                
                noteCard.innerHTML = `
                    <div class="note-header">
                        <div>
                            <strong>${note.title}${orderInfo}</strong>
                            ${typeBadge}
                        </div>
                        <span class="note-date">${date}</span>
                    </div>
                    <div class="note-content">${note.content}</div>
                    <div class="note-actions">
                        ${note.resolved ? 
                          '<span class="order-status status-completed">Resolved</span>' : 
                          `<button class="btn btn-sm btn-success" onclick="resolveNote(${note.id})">Mark Resolved</button>`}
                        <button class="btn btn-sm btn-danger" onclick="deleteNote(${note.id})">Delete</button>
                    </div>
                `;
                
                container.appendChild(noteCard);
            });
        }

        // Initialize form event listeners
        function initForms() {
            // Profile form
            document.getElementById('profile-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                profile.name = document.getElementById('profile-name').value;
                profile.email = document.getElementById('profile-email').value;
                profile.phone = document.getElementById('profile-phone').value;
                profile.specialty = document.getElementById('profile-specialty').value;
                
                showNotification('Profile updated successfully!', 'success');
            });
            
            // Add task form
            document.getElementById('add-task-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const newTask = {
                    id: workSchedule.length > 0 ? Math.max(...workSchedule.map(t => t.id)) + 1 : 1,
                    orderId: document.getElementById('task-order').value,
                    date: document.getElementById('task-date').value,
                    time: document.getElementById('task-time').value,
                    urgent: document.getElementById('task-urgent').checked,
                    completed: false,
                    notes: document.getElementById('task-notes').value
                };
                
                workSchedule.push(newTask);
                loadWorkSchedule();
                closeModal('add-task-modal');
                showNotification('Task added to schedule!', 'success');
            });
            
            // Add note form
            document.getElementById('add-note-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const newNote = {
                    id: notes.length > 0 ? Math.max(...notes.map(n => n.id)) + 1 : 1,
                    type: document.getElementById('note-type').value,
                    title: document.getElementById('note-title').value,
                    content: document.getElementById('note-content').value,
                    date: new Date().toISOString().split('T')[0],
                    orderId: document.getElementById('note-type').value === 'issue' ? 
                             document.getElementById('note-order').value : null,
                    resolved: false
                };
                
                notes.unshift(newNote); // Add to beginning of array
                loadNotes();
                loadRecentNotes();
                updateDashboardStats();
                closeModal('add-note-modal');
                
                if (newNote.type === 'issue') {
                    showNotification('Issue reported to manager!', 'success');
                } else {
                    showNotification('Note added successfully!', 'success');
                }
            });
            
            // Show/hide order field based on note type
            document.getElementById('note-type').addEventListener('change', function() {
                document.getElementById('note-order-group').style.display = 
                    this.value === 'issue' ? 'block' : 'none';
            });
        }

        // View order details
        function viewOrderDetails(orderId) {
            const order = orders.find(o => o.id === orderId) || 
                         completedOrders.find(o => o.id === orderId);
            
            if (!order) {
                showNotification('Order not found', 'error');
                return;
            }
            
            const modalContent = document.getElementById('order-details-content');
            const actionsDiv = document.getElementById('order-actions');
            
            let statusClass, statusText;
            switch(order.status) {
                case 'in-progress':
                    statusClass = 'status-in-progress';
                    statusText = 'In Progress';
                    break;
                case 'not-started':
                    statusClass = 'status-not-started';
                    statusText = 'Not Started';
                    break;
                default:
                    statusClass = 'status-completed';
                    statusText = 'Completed';
            }
            
            const dueDate = order.dueDate ? 
                new Date(order.dueDate).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                }) : 'N/A';
            
            const completedDate = order.completedDate ? 
                new Date(order.completedDate).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                }) : 'N/A';
            
            modalContent.innerHTML = `
                <div style="margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <div>
                            <strong>Order ID:</strong> ${order.id}
                        </div>
                        <div>
                            <span class="order-status ${statusClass}">${statusText}</span>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div>
                            <strong>Garment Type:</strong> ${order.garment}
                        </div>
                        <div>
                            <strong>Customer:</strong> ${order.customer}
                        </div>
                        <div>
                            <strong>${order.completedDate ? 'Completed On' : 'Due Date'}:</strong> 
                            ${order.completedDate ? completedDate : dueDate}
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
            
            // Set up actions based on order status
            actionsDiv.innerHTML = '';
            
            if (!order.completedDate) {
                if (order.status === 'in-progress') {
                    const completeBtn = document.createElement('button');
                    completeBtn.className = 'btn btn-success';
                    completeBtn.innerHTML = '<i class="fas fa-check"></i> Mark as Completed';
                    completeBtn.onclick = () => {
                        completeOrder(order.id);
                        closeModal('order-details-modal');
                    };
                    actionsDiv.appendChild(completeBtn);
                } else {
                    const startBtn = document.createElement('button');
                    startBtn.className = 'btn';
                    startBtn.innerHTML = '<i class="fas fa-play"></i> Start Work';
                    startBtn.onclick = () => {
                        startOrder(order.id);
                        closeModal('order-details-modal');
                    };
                    actionsDiv.appendChild(startBtn);
                }
            }
            
            const closeBtn = document.createElement('button');
            closeBtn.className = 'btn btn-secondary';
            closeBtn.textContent = 'Close';
            closeBtn.onclick = () => closeModal('order-details-modal');
            actionsDiv.appendChild(closeBtn);
            
            actionsDiv.style.display = 'flex';
            openModal('order-details-modal');
        }

        // Start working on an order
        function startOrder(orderId) {
            const order = orders.find(o => o.id === orderId);
            if (order) {
                order.status = 'in-progress';
                loadAssignedOrders();
                loadRecentOrders();
                showNotification(`Started work on order ${orderId}`, 'success');
            }
        }

        // Complete an order
        function completeOrder(orderId) {
            const orderIndex = orders.findIndex(o => o.id === orderId);
            if (orderIndex !== -1) {
                const order = orders[orderIndex];
                
                // Move to completed orders
                completedOrders.unshift({
                    ...order,
                    completedDate: new Date().toISOString().split('T')[0]
                });
                
                // Remove from assigned orders
                orders.splice(orderIndex, 1);
                
                // Update all displays
                loadAssignedOrders();
                loadCompletedOrders();
                loadRecentOrders();
                updateDashboardStats();
                
                showNotification(`Order ${orderId} completed and submitted to manager!`, 'success');
            }
        }

        // Resolve a note/issue
        function resolveNote(noteId) {
            const note = notes.find(n => n.id === noteId);
            if (note) {
                note.resolved = true;
                loadNotes();
                loadRecentNotes();
                updateDashboardStats();
                showNotification('Note/issue marked as resolved!', 'success');
            }
        }

        // Delete a note
        function deleteNote(noteId) {
            const noteIndex = notes.findIndex(n => n.id === noteId);
            if (noteIndex !== -1) {
                notes.splice(noteIndex, 1);
                loadNotes();
                loadRecentNotes();
                updateDashboardStats();
                showNotification('Note deleted!', 'success');
            }
        }

        // Edit a task
        function editTask(taskId) {
            const task = workSchedule.find(t => t.id === taskId);
            if (!task) return;
            
            // For simplicity, we'll just toggle completion status
            task.completed = !task.completed;
            loadWorkSchedule();
            
            const order = orders.find(o => o.id === task.orderId);
            if (order) {
                showNotification(`Task for ${order.garment} (${task.orderId}) marked as ${task.completed ? 'completed' : 'incomplete'}`, 'success');
            }
        }

        // Download measurement sheet
        function downloadMeasurementSheet() {
            const orderId = document.getElementById('measurement-order').value;
            const order = orders.find(o => o.id === orderId) || 
                         completedOrders.find(o => o.id === orderId);
            
            if (order) {
                // In a real app, this would generate and download a PDF
                // For this demo, we'll just show a notification
                showNotification(`Measurement sheet for ${orderId} downloaded!`, 'success');
            }
        }

        // Show add task modal
        function showAddTaskModal() {
            openModal('add-task-modal');
        }

        // Show add note modal
        function showAddNoteModal() {
            document.getElementById('note-title').value = '';
            document.getElementById('note-content').value = '';
            document.getElementById('note-type').value = 'note';
            document.getElementById('note-order-group').style.display = 'none';
            openModal('add-note-modal');
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
                'dashboard': 'Tailor Dashboard',
                'assigned-orders': 'Assigned Orders',
                'work-schedule': 'Work Schedule',
                'measurements': 'Measurements',
                'completed-work': 'Completed Work',
                'notes-issues': 'Notes & Issues',
                'profile': 'Profile Settings'
            };
            document.getElementById('page-title').textContent = pageTitles[pageId] || 'Tailor Dashboard';
            
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

        // Logout function
        function logout() {
            showNotification('Logging out...', 'success');
            setTimeout(() => {
                // In a real app, this would redirect to login page
                window.location.href = '/login';
            }, 1500);
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
    </script>
</body>
</html>