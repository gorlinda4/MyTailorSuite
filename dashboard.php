<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

switch ($_SESSION['role']) {
    case 'admin':
        header("Location: admin.php");
        break;
    case 'manager':
        header("Location: manager.php");
        break;
    case 'tailor':
        header("Location: tailor.php");
        break;
    case 'cashier':
        header("Location: cashier.php");
        break;
    case 'customer':
        header("Location: customer_dashboard.php");
        break;
    default:
        echo "Unknown role.";
}
exit();
?>
