<?php
// This would be your main entry point that loads the appropriate dashboard based on user role
require_once 'auth.php';

$auth = new Auth();

if (!$auth->isLoggedIn()) {
    // Redirect to login page if not authenticated
    header('Location: login.php');
    exit();
}

$user = $auth->getCurrentUser();

// Load the appropriate dashboard based on user role
switch ($user['role']) {
    case 'customer':
        include 'customer_dashboard.php';
        break;
    case 'tailor':
        include 'tailor.html';
        break;
    case 'manager':
        include 'manager_dashboard.php';
        break;
    case 'admin':
        include 'admin_dashboard.php';
        break;
    case 'cashier':
        include 'cashier_dashboard.php';
        break;
    default:
        // Handle unknown roles
        header('Location: login.php');
        exit();
}
?>