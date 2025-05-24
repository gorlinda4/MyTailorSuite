
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_role = $_SESSION['role'];

switch ($user_role) {
    case 'customer':
        header("Location: customer_dashboard.php");
        break;
    case 'tailor':
        header("Location: tailor.php");
        break;
    case 'manager':
        header("Location: manager.php");
        break;
    case 'admin':
        header("Location: admin.php");
        break;
    case 'cashier':
        header("Location: cashier.php");
        break;
    default:
        header("Location: login.php");
        break;
}
exit;
?>
