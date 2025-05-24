
<?php
require 'mpesa_config.php';
require 'db.php'; // Include your database connection

// Get the callback data
$callbackData = file_get_contents('php://input');
$callbackData = json_decode($callbackData, true);

// Extract the necessary information
$merchantRequestID = $callbackData['Body']['stkCallback']['MerchantRequestID'];
$checkoutRequestID = $callbackData['Body']['stkCallback']['CheckoutRequestID'];
$resultCode = $callbackData['Body']['stkCallback']['ResultCode'];
$resultDesc = $callbackData['Body']['stkCallback']['ResultDesc'];
$amount = $callbackData['Body']['stkCallback']['CallbackMetadata']['Item'][0]['Value'];
$mpesaReceiptNumber = $callbackData['Body']['stkCallback']['CallbackMetadata']['Item'][1]['Value'];
$transactionDate = $callbackData['Body']['stkCallback']['CallbackMetadata']['Item'][3]['Value'];
$phoneNumber = $callbackData['Body']['stkCallback']['CallbackMetadata']['Item'][4]['Value'];

// Insert the transaction details into the database
$sql = "INSERT INTO mpesa_transactions (merchant_request_id, checkout_request_id, result_code, result_desc, amount, mpesa_receipt_number, transaction_date, phone_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$merchantRequestID, $checkoutRequestID, $resultCode, $resultDesc, $amount, $mpesaReceiptNumber, $transactionDate, $phoneNumber]);

// Update the payment status in the payments table
if ($resultCode == 0) {
    $sql = "UPDATE payments SET status = 'completed', transaction_id = ? WHERE transaction_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$mpesaReceiptNumber, $checkoutRequestID]);
} else {
    $sql = "UPDATE payments SET status = 'failed' WHERE transaction_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$checkoutRequestID]);
}

// Respond to Safaricom with a success message
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Success']);
?>
