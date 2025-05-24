
<?php
require 'mpesa_config.php';

function getMpesaAccessToken() {
    $credentials = base64_encode(MPESA_CONSUMER_KEY . ':' . MPESA_CONSUMER_SECRET);
    $ch = curl_init(MPESA_AUTH_URL);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($response);
    return $result->access_token;
}

function initiateStkPush($phoneNumber, $amount, $accountReference, $transactionDesc) {
    $accessToken = getMpesaAccessToken();
    $timestamp = date('YmdHis');
    $password = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);

    $stkPushData = [
        'BusinessShortCode' => MPESA_SHORTCODE,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => $amount,
        'PartyA' => $phoneNumber,
        'PartyB' => MPESA_SHORTCODE,
        'PhoneNumber' => $phoneNumber,
        'CallBackURL' => MPESA_CALLBACK_URL,
        'AccountReference' => $accountReference,
        'TransactionDesc' => $transactionDesc
    ];

    $ch = curl_init(MPESA_STK_PUSH_URL);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken, 'Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($stkPushData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response);
}

// Example usage
$phoneNumber = $_POST['phoneNumber'];
$amount = $_POST['amount'];
$accountReference = $_POST['accountReference'];
$transactionDesc = $_POST['transactionDesc'];

$response = initiateStkPush($phoneNumber, $amount, $accountReference, $transactionDesc);
echo json_encode($response);
?>
