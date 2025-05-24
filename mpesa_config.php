
<?php
// M-Pesa Daraja API credentials
define('MPESA_CONSUMER_KEY', 'YOUR_CONSUMER_KEY');
define('MPESA_CONSUMER_SECRET', 'YOUR_CONSUMER_SECRET');
define('MPESA_SHORTCODE', 'YOUR_SHORTCODE');
define('MPESA_PASSKEY', 'YOUR_PASSKEY');

// M-Pesa API URLs
define('MPESA_AUTH_URL', 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
define('MPESA_STK_PUSH_URL', 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest');
define('MPESA_CALLBACK_URL', 'https://yourdomain.com/mpesa_callback.php');
?>
