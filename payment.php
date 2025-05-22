<?php
require_once 'db.php';

class Payment {
    private $db;
    private $mpesa_consumer_key = 'YOUR_MPESA_CONSUMER_KEY';
    private $mpesa_consumer_secret = 'YOUR_MPESA_CONSUMER_SECRET';
    private $paypal_client_id = 'YOUR_PAYPAL_CLIENT_ID';
    private $paypal_secret = 'YOUR_PAYPAL_SECRET';

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function processPayment($data) {
        // Validate required fields
        $required = ['order_id', 'amount', 'payment_method'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['status' => 'error', 'message' => "$field is required"];
            }
        }

        // Get order details
        $order = $this->getOrder($data['order_id']);
        if (!$order) {
            return ['status' => 'error', 'message' => 'Order not found'];
        }

        // Check if payment already exists
        $existingPayment = $this->getPaymentByOrder($data['order_id']);
        if ($existingPayment && $existingPayment['status'] == 'completed') {
            return ['status' => 'error', 'message' => 'Payment already processed'];
        }

        // Process based on payment method
        switch ($data['payment_method']) {
            case 'mpesa':
                return $this->processMpesaPayment($data, $order);
            case 'paypal':
                return $this->processPaypalPayment($data, $order);
            case 'credit_card':
                return $this->processCreditCardPayment($data, $order);
            case 'bank_transfer':
                return $this->processBankTransfer($data, $order);
            case 'cash':
                return $this->processCashPayment($data, $order);
            default:
                return ['status' => 'error', 'message' => 'Invalid payment method'];
        }
    }

    private function getOrder($orderId) {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetch();
    }

    private function getPaymentByOrder($orderId) {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE order_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$orderId]);
        return $stmt->fetch();
    }

    private function processMpesaPayment($data, $order) {
        // Validate phone number
        if (empty($data['phone_number'])) {
            return ['status' => 'error', 'message' => 'Phone number is required for M-Pesa'];
        }

        // Generate transaction ID
        $transactionId = 'MPESA' . time() . rand(100, 999);

        // In a real system, this would call the M-Pesa API
        try {
            // Simulate M-Pesa API call
            $mpesaResponse = $this->simulateMpesaStkPush(
                $data['phone_number'], 
                $data['amount'], 
                $transactionId
            );

            if ($mpesaResponse['status'] !== 'success') {
                return ['status' => 'error', 'message' => 'M-Pesa payment failed: ' . $mpesaResponse['message']];
            }

            // Create payment record
            $paymentId = $this->createPaymentRecord([
                'order_id' => $order['id'],
                'amount' => $data['amount'],
                'payment_method' => 'mpesa',
                'transaction_id' => $transactionId,
                'status' => 'pending'
            ]);

            // Create M-Pesa transaction record
            $this->createMpesaTransaction([
                'payment_id' => $paymentId,
                'phone_number' => $data['phone_number'],
                'amount' => $data['amount'],
                'mpesa_code' => $mpesaResponse['mpesa_code'],
                'checkout_request_id' => $mpesaResponse['checkout_request_id'],
                'merchant_request_id' => $mpesaResponse['merchant_request_id'],
                'status' => 'pending'
            ]);

            return [
                'status' => 'success',
                'message' => 'M-Pesa payment initiated. Please complete payment on your phone.',
                'transaction_id' => $transactionId,
                'payment_id' => $paymentId
            ];

        } catch (Exception $e) {
            error_log("M-Pesa Error: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'M-Pesa payment processing failed'];
        }
    }

    private function simulateMpesaStkPush($phone, $amount, $transactionId) {
        // In a real system, this would call the M-Pesa API
        // This is a simulation that always succeeds for demo purposes
        
        return [
            'status' => 'success',
            'message' => 'Request processed successfully',
            'mpesa_code' => 'MPE' . rand(1000, 9999),
            'checkout_request_id' => 'ws_CO_' . date('YmdHis') . rand(100, 999),
            'merchant_request_id' => 'MPESA-' . time() . rand(100, 999)
        ];
    }

    private function processPaypalPayment($data, $order) {
        // In a real system, this would call the PayPal API
        try {
            $transactionId = 'PAYPAL' . time() . rand(100, 999);
            
            // Simulate PayPal API call
            $paypalResponse = $this->simulatePaypalPayment($data, $transactionId);

            if ($paypalResponse['status'] !== 'success') {
                return ['status' => 'error', 'message' => 'PayPal payment failed: ' . $paypalResponse['message']];
            }

            // Create payment record
            $paymentId = $this->createPaymentRecord([
                'order_id' => $order['id'],
                'amount' => $data['amount'],
                'payment_method' => 'paypal',
                'transaction_id' => $transactionId,
                'status' => 'completed'
            ]);

            // Create PayPal transaction record
            $this->createPaypalTransaction([
                'payment_id' => $paymentId,
                'paypal_id' => $paypalResponse['paypal_id'],
                'payer_email' => $data['email'] ?? '',
                'payer_name' => $data['name'] ?? '',
                'status' => 'completed'
            ]);

            // Update order status
            $this->updateOrderStatus($order['id'], 'paid');

            return [
                'status' => 'success',
                'message' => 'PayPal payment completed successfully',
                'transaction_id' => $transactionId,
                'payment_id' => $paymentId
            ];

        } catch (Exception $e) {
            error_log("PayPal Error: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'PayPal payment processing failed'];
        }
    }

    private function simulatePaypalPayment($data, $transactionId) {
        // Simulate PayPal API response
        return [
            'status' => 'success',
            'message' => 'Payment completed',
            'paypal_id' => 'PAYID-' . strtoupper(bin2hex(random_bytes(8)))
        ];
    }

    private function processCreditCardPayment($data, $order) {
        // Validate card details
        $required = ['card_number', 'expiry_date', 'cvv', 'card_holder'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['status' => 'error', 'message' => "$field is required for credit card payment"];
            }
        }

        try {
            $transactionId = 'CC' . time() . rand(100, 999);
            
            // Simulate credit card processing
            $ccResponse = $this->simulateCreditCardPayment($data, $transactionId);

            if ($ccResponse['status'] !== 'success') {
                return ['status' => 'error', 'message' => 'Credit card payment failed: ' . $ccResponse['message']];
            }

            // Create payment record
            $paymentId = $this->createPaymentRecord([
                'order_id' => $order['id'],
                'amount' => $data['amount'],
                'payment_method' => 'credit_card',
                'transaction_id' => $transactionId,
                'status' => 'completed'
            ]);

            // Create credit card transaction record
            $this->createCreditCardTransaction([
                'payment_id' => $paymentId,
                'card_last_four' => substr($data['card_number'], -4),
                'card_type' => $this->detectCardType($data['card_number']),
                'authorization_code' => $ccResponse['auth_code'],
                'status' => 'completed'
            ]);

            // Update order status
            $this->updateOrderStatus($order['id'], 'paid');

            return [
                'status' => 'success',
                'message' => 'Credit card payment completed successfully',
                'transaction_id' => $transactionId,
                'payment_id' => $paymentId
            ];

        } catch (Exception $e) {
            error_log("Credit Card Error: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Credit card payment processing failed'];
        }
    }

    private function detectCardType($cardNumber) {
        $firstDigit = substr($cardNumber, 0, 1);
        switch ($firstDigit) {
            case '3': return 'amex';
            case '4': return 'visa';
            case '5': return 'mastercard';
            case '6': return 'discover';
            default: return 'unknown';
        }
    }

    private function simulateCreditCardPayment($data, $transactionId) {
        // Simulate credit card processing
        // In a real system, this would call a payment gateway like Stripe
        
        // Simple validation for demo
        if (strlen($data['cvv']) < 3) {
            return ['status' => 'error', 'message' => 'Invalid CVV'];
        }

        return [
            'status' => 'success',
            'message' => 'Payment authorized',
            'auth_code' => 'AUTH' . rand(1000, 9999)
        ];
    }

    private function processBankTransfer($data, $order) {
        // Validate bank details
        $required = ['bank_name', 'account_number', 'account_name'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['status' => 'error', 'message' => "$field is required for bank transfer"];
            }
        }

        try {
            $transactionId = 'BANK' . time() . rand(100, 999);
            
            // Create payment record
            $paymentId = $this->createPaymentRecord([
                'order_id' => $order['id'],
                'amount' => $data['amount'],
                'payment_method' => 'bank_transfer',
                'transaction_id' => $transactionId,
                'status' => 'pending'
            ]);

            // In a real system, you might store bank transfer details
            // For this demo, we'll just mark it as pending

            return [
                'status' => 'success',
                'message' => 'Bank transfer initiated. Please complete the transfer.',
                'transaction_id' => $transactionId,
                'payment_id' => $paymentId
            ];

        } catch (Exception $e) {
            error_log("Bank Transfer Error: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Bank transfer processing failed'];
        }
    }

    private function processCashPayment($data, $order) {
        try {
            $transactionId = 'CASH' . time() . rand(100, 999);
            
            // Create payment record
            $paymentId = $this->createPaymentRecord([
                'order_id' => $order['id'],
                'amount' => $data['amount'],
                'payment_method' => 'cash',
                'transaction_id' => $transactionId,
                'status' => 'pending'
            ]);

            return [
                'status' => 'success',
                'message' => 'Cash payment will be collected on delivery',
                'transaction_id' => $transactionId,
                'payment_id' => $paymentId
            ];

        } catch (Exception $e) {
            error_log("Cash Payment Error: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Cash payment processing failed'];
        }
    }

    private function createPaymentRecord($data) {
        $stmt = $this->db->prepare("
            INSERT INTO payments 
            (order_id, amount, payment_method, transaction_id, status, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['order_id'],
            $data['amount'],
            $data['payment_method'],
            $data['transaction_id'],
            $data['status']
        ]);
        return $this->db->lastInsertId();
    }

    private function createMpesaTransaction($data) {
        $stmt = $this->db->prepare("
            INSERT INTO mpesa_transactions 
            (payment_id, phone_number, amount, mpesa_code, checkout_request_id, merchant_request_id, status, transaction_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['payment_id'],
            $data['phone_number'],
            $data['amount'],
            $data['mpesa_code'],
            $data['checkout_request_id'],
            $data['merchant_request_id'],
            $data['status']
        ]);
    }

    private function createPaypalTransaction($data) {
        $stmt = $this->db->prepare("
            INSERT INTO paypal_transactions 
            (payment_id, paypal_id, payer_email, payer_name, status, transaction_date) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['payment_id'],
            $data['paypal_id'],
            $data['payer_email'],
            $data['payer_name'],
            $data['status']
        ]);
    }

    private function createCreditCardTransaction($data) {
        $stmt = $this->db->prepare("
            INSERT INTO credit_card_transactions 
            (payment_id, card_last_four, card_type, authorization_code, status, transaction_date) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['payment_id'],
            $data['card_last_four'],
            $data['card_type'],
            $data['authorization_code'],
            $data['status']
        ]);
    }

    private function updateOrderStatus($orderId, $status) {
        $stmt = $this->db->prepare("
            UPDATE orders 
            SET status = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$status, $orderId]);
    }

    public function verifyMpesaPayment($checkoutRequestId) {
        // In a real system, this would verify with M-Pesa API
        // For demo, we'll simulate a successful verification after 5 seconds
        
        sleep(5);
        
        $stmt = $this->db->prepare("
            SELECT * FROM mpesa_transactions 
            WHERE checkout_request_id = ?
        ");
        $stmt->execute([$checkoutRequestId]);
        $transaction = $stmt->fetch();

        if (!$transaction) {
            return ['status' => 'error', 'message' => 'Transaction not found'];
        }

        // Update transaction status
        $this->updateTransactionStatus($transaction['id'], 'completed');
        
        // Update payment status
        $this->updatePaymentStatus($transaction['payment_id'], 'completed');
        
        // Update order status
        $this->updateOrderByPayment($transaction['payment_id'], 'paid');

        return [
            'status' => 'success',
            'message' => 'Payment verified successfully',
            'transaction_id' => $transaction['id'],
            'payment_id' => $transaction['payment_id']
        ];
    }

    private function updateTransactionStatus($transactionId, $status) {
        $stmt = $this->db->prepare("
            UPDATE mpesa_transactions 
            SET status = ?, transaction_date = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$status, $transactionId]);
    }

    private function updatePaymentStatus($paymentId, $status) {
        $stmt = $this->db->prepare("
            UPDATE payments 
            SET status = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$status, $paymentId]);
    }

    private function updateOrderByPayment($paymentId, $status) {
        $stmt = $this->db->prepare("
            UPDATE orders o
            JOIN payments p ON o.id = p.order_id
            SET o.status = ?, o.updated_at = NOW()
            WHERE p.id = ?
        ");
        $stmt->execute([$status, $paymentId]);
    }

    public function getPaymentHistory($userId, $limit = 10) {
        $stmt = $this->db->prepare("
            SELECT p.*, o.garment_type_id, gt.name as garment_name
            FROM payments p
            JOIN orders o ON p.order_id = o.id
            JOIN garment_types gt ON o.garment_type_id = gt.id
            WHERE o.customer_id = ?
            ORDER BY p.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
}
?>