<?php
require_once 'db.php';

class PaymentSystem {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function processPayment($order_id, $payment_method, $payment_data) {
        $conn = $this->db->connect();
        
        try {
            $conn->beginTransaction();
            
            // Get order amount
            $stmt = $conn->prepare("SELECT o.order_id, o.customer_id, gt.base_price 
                                   FROM orders o
                                   JOIN garment_types gt ON o.garment_type_id = gt.garment_type_id
                                   WHERE o.order_id = ?");
            $stmt->execute([$order_id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(!$order) {
                throw new Exception("Order not found");
            }
            
            // Create payment record
            $stmt = $conn->prepare("INSERT INTO payments 
                (order_id, amount, payment_method, status) 
                VALUES (?, ?, ?, 'pending')");
            $stmt->execute([
                $order_id,
                $order['base_price'],
                $payment_method
            ]);
            
            $payment_id = $conn->lastInsertId();
            
            // Process based on payment method
            switch($payment_method) {
                case 'mpesa':
                    $result = $this->processMpesa($payment_id, $payment_data);
                    break;
                case 'paypal':
                    $result = $this->processPaypal($payment_id, $payment_data);
                    break;
                case 'credit_card':
                    $result = $this->processCreditCard($payment_id, $payment_data);
                    break;
                case 'bank_transfer':
                    $result = $this->processBankTransfer($payment_id, $payment_data);
                    break;
                case 'cash':
                    $result = $this->processCash($payment_id, $payment_data);
                    break;
                default:
                    throw new Exception("Invalid payment method");
            }
            
            // Update order status if payment was successful
            if($result['status'] === 'completed') {
                $stmt = $conn->prepare("UPDATE orders SET status = 'approved' WHERE order_id = ?");
                $stmt->execute([$order_id]);
                
                // Create notification for customer
                $this->createNotification(
                    $order['customer_id'],
                    'Payment Received',
                    "Your payment for order #{$order_id} has been received",
                    'payment',
                    $payment_id
                );
            }
            
            $conn->commit();
            
            return $result;
            
        } catch(Exception $e) {
            $conn->rollBack();
            error_log("Payment processing error: " . $e->getMessage());
            throw new Exception("Payment processing failed");
        }
    }
    
    private function processMpesa($payment_id, $payment_data) {
        $conn = $this->db->connect();
        
        // Validate M-Pesa data
        if(empty($payment_data['phone_number'])) {
            throw new Exception("Phone number is required");
        }
        
        // In a real system, this would call the M-Pesa API
        // For demo, we'll simulate a successful payment
        
        // Simulate STK push
        $checkout_request_id = 'ws_CO_' . date('YmdHis') . '_' . $payment_id;
        $merchant_request_id = 'MP-' . $payment_id . '-' . time();
        
        // Save M-Pesa transaction
        $stmt = $conn->prepare("INSERT INTO mpesa_transactions 
            (payment_id, phone_number, amount, checkout_request_id, merchant_request_id, status) 
            VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([
            $payment_id,
            $payment_data['phone_number'],
            $payment_data['amount'],
            $checkout_request_id,
            $merchant_request_id
        ]);
        
        // Simulate callback after 5 seconds
        $this->simulateMpesaCallback($payment_id, $checkout_request_id);
        
        return [
            'status' => 'pending',
            'message' => 'Please complete payment on your phone',
            'checkout_request_id' => $checkout_request_id
        ];
    }
    
    private function simulateMpesaCallback($payment_id, $checkout_request_id) {
        // In a real system, this would be handled by the M-Pesa callback URL
        // For demo, we'll simulate it after a delay
        
        $data = [
            'payment_id' => $payment_id,
            'checkout_request_id' => $checkout_request_id,
            'mpesa_code' => 'MPE' . rand(1000, 9999),
            'result_code' => '0',
            'result_desc' => 'The service request is processed successfully.'
        ];
        
        // Schedule the callback simulation
        register_shutdown_function(function() use ($data) {
            sleep(3); // Simulate delay
            $this->handleMpesaCallback($data);
        });
    }
    
    public function handleMpesaCallback($callback_data) {
        $conn = $this->db->connect();
        
        try {
            $conn->beginTransaction();
            
            // Find the transaction
            $stmt = $conn->prepare("SELECT * FROM mpesa_transactions 
                                   WHERE checkout_request_id = ?");
            $stmt->execute([$callback_data['checkout_request_id']]);
            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(!$transaction) {
                throw new Exception("Transaction not found");
            }
            
            // Update transaction status
            $stmt = $conn->prepare("UPDATE mpesa_transactions 
                                   SET mpesa_code = ?, 
                                       result_code = ?, 
                                       result_desc = ?, 
                                       transaction_date = NOW(),
                                       status = ?
                                   WHERE transaction_id = ?");
            $status = ($callback_data['result_code'] == '0') ? 'completed' : 'failed';
            $stmt->execute([
                $callback_data['mpesa_code'] ?? null,
                $callback_data['result_code'],
                $callback_data['result_desc'],
                $status,
                $transaction['transaction_id']
            ]);
            
            // Update payment status
            $stmt = $conn->prepare("UPDATE payments 
                                   SET status = ?, 
                                       transaction_id = ?,
                                       payment_date = NOW()
                                   WHERE payment_id = ?");
            $stmt->execute([
                $status,
                $callback_data['mpesa_code'] ?? null,
                $transaction['payment_id']
            ]);
            
            // Update order status if payment was successful
            if($status === 'completed') {
                $stmt = $conn->prepare("SELECT order_id, customer_id FROM payments WHERE payment_id = ?");
                $stmt->execute([$transaction['payment_id']]);
                $payment = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $stmt = $conn->prepare("UPDATE orders SET status = 'approved' WHERE order_id = ?");
                $stmt->execute([$payment['order_id']]);
                
                // Create notification for customer
                $this->createNotification(
                    $payment['customer_id'],
                    'Payment Completed',
                    "Your M-Pesa payment for order #{$payment['order_id']} has been completed",
                    'payment',
                    $transaction['payment_id']
                );
            }
            
            $conn->commit();
            
        } catch(Exception $e) {
            $conn->rollBack();
            error_log("M-Pesa callback error: " . $e->getMessage());
        }
    }
    
    private function processPaypal($payment_id, $payment_data) {
        $conn = $this->db->connect();
        
        // In a real system, this would call the PayPal API
        // For demo, we'll simulate a successful payment
        
        $paypal_id = 'PAY-' . $payment_id . '-' . time();
        
        // Save PayPal transaction
        $stmt = $conn->prepare("INSERT INTO paypal_transactions 
            (payment_id, paypal_id, payer_email, payer_name, currency, status) 
            VALUES (?, ?, ?, ?, ?, 'completed')");
        $stmt->execute([
            $payment_id,
            $paypal_id,
            $payment_data['email'] ?? 'customer@example.com',
            $payment_data['name'] ?? 'Customer',
            'USD',
            'completed'
        ]);
        
        // Update payment status
        $stmt = $conn->prepare("UPDATE payments 
                               SET status = 'completed', 
                                   transaction_id = ?,
                                   payment_date = NOW()
                               WHERE payment_id = ?");
        $stmt->execute([$paypal_id, $payment_id]);
        
        return [
            'status' => 'completed',
            'transaction_id' => $paypal_id
        ];
    }
    
    private function processCreditCard($payment_id, $payment_data) {
        $conn = $this->db->connect();
        
        // Validate credit card data
        $required = ['card_number', 'expiry', 'cvv', 'card_holder'];
        foreach($required as $field) {
            if(empty($payment_data[$field])) {
                throw new Exception("$field is required");
            }
        }
        
        // In a real system, this would call the payment gateway API
        // For demo, we'll simulate a successful payment
        
        $last_four = substr($payment_data['card_number'], -4);
        $auth_code = 'AUTH-' . rand(1000, 9999);
        
        // Save credit card transaction
        $stmt = $conn->prepare("INSERT INTO credit_card_transactions 
            (payment_id, card_last_four, card_type, authorization_code, status) 
            VALUES (?, ?, ?, ?, 'completed')");
        $stmt->execute([
            $payment_id,
            $last_four,
            $this->detectCardType($payment_data['card_number']),
            $auth_code,
            'completed'
        ]);
        
        // Update payment status
        $stmt = $conn->prepare("UPDATE payments 
                               SET status = 'completed', 
                                   transaction_id = ?,
                                   payment_date = NOW()
                               WHERE payment_id = ?");
        $stmt->execute([$auth_code, $payment_id]);
        
        return [
            'status' => 'completed',
            'transaction_id' => $auth_code
        ];
    }
    
    private function detectCardType($card_number) {
        $first_digit = substr($card_number, 0, 1);
        
        switch($first_digit) {
            case '4': return 'Visa';
            case '5': return 'Mastercard';
            case '3': return 'American Express';
            case '6': return 'Discover';
            default: return 'Unknown';
        }
    }
    
    private function processBankTransfer($payment_id, $payment_data) {
        $conn = $this->db->connect();
        
        // Validate bank transfer data
        if(empty($payment_data['bank_name']) || empty($payment_data['account_number'])) {
            throw new Exception("Bank details are required");
        }
        
        // In a real system, this would verify the transfer with the bank
        // For demo, we'll mark it as pending
        
        $transaction_id = 'BANK-' . $payment_id . '-' . time();
        
        // Update payment status
        $stmt = $conn->prepare("UPDATE payments 
                               SET status = 'pending', 
                                   transaction_id = ?
                               WHERE payment_id = ?");
        $stmt->execute([$transaction_id, $payment_id]);
        
        return [
            'status' => 'pending',
            'message' => 'Please complete the bank transfer',
            'transaction_id' => $transaction_id
        ];
    }
    
    private function processCash($payment_id, $payment_data) {
        $conn = $this->db->connect();
        
        // For cash payments, we'll mark it as completed immediately
        $transaction_id = 'CASH-' . $payment_id . '-' . time();
        
        // Update payment status
        $stmt = $conn->prepare("UPDATE payments 
                               SET status = 'completed', 
                                   transaction_id = ?,
                                   payment_date = NOW()
                               WHERE payment_id = ?");
        $stmt->execute([$transaction_id, $payment_id]);
        
        return [
            'status' => 'completed',
            'transaction_id' => $transaction_id
        ];
    }
    
    private function createNotification($user_id, $title, $message, $entity_type, $entity_id) {
        $conn = $this->db->connect();
        
        $stmt = $conn->prepare("INSERT INTO notifications 
            (user_id, title, message, related_entity_type, related_entity_id) 
            VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $user_id,
            $title,
            $message,
            $entity_type,
            $entity_id
        ]);
    }
}
?>