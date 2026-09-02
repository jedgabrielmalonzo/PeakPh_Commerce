<?php
session_start();
require_once(__DIR__ . '/../includes/db.php');
require_once(__DIR__ . '/../includes/PayMongoHelper.php');

$payment_id = intval($_GET['payment_id'] ?? 0);
$order_id = $_GET['order_id'] ?? null;

if (!$payment_id && !$order_id) {
    header('Location: ../checkout.php?error=invalid_payment');
    exit;
}

try {
    $paymongo = new PayMongoHelper();
    
    // Get payment record - try by payment_id first, then by order_id
    if ($payment_id) {
        $stmt = $conn->prepare("SELECT p.*, o.order_id as order_string_id, o.customer_name, o.customer_email FROM payments p JOIN orders o ON p.order_id = o.id WHERE p.id = ?");
        $stmt->bind_param("i", $payment_id);
    } else {
        $stmt = $conn->prepare("SELECT p.*, o.order_id as order_string_id, o.customer_name, o.customer_email FROM payments p JOIN orders o ON p.order_id = o.id WHERE o.order_id = ? ORDER BY p.id DESC LIMIT 1");
        $stmt->bind_param("s", $order_id);
    }
    $stmt->execute();
    $payment = $stmt->get_result()->fetch_assoc();
    
    if (!$payment) {
        throw new Exception('Payment not found');
    }
    
    $verified = false;
    
    if ($payment['paymongo_payment_intent_id']) {
        // Verify payment with PayMongo
        $intent = $paymongo->getPaymentIntent($payment['paymongo_payment_intent_id']);
        
        if ($intent && isset($intent['data']['attributes']['status'])) {
            $paymongo_status = $intent['data']['attributes']['status'];
            
            if ($paymongo_status === 'succeeded') {
                $verified = true;
                
                // Update payment status
                $stmt = $conn->prepare("UPDATE payments SET status = 'Completed', paid_at = NOW() WHERE id = ?");
                $stmt->bind_param("i", $payment_id);
                $stmt->execute();
                
                // Update order status
                $stmt = $conn->prepare("UPDATE orders SET status = 'Processing', payment_status = 'Paid' WHERE id = ?");
                $stmt->bind_param("i", $payment['order_id']);
                $stmt->execute();
                
                // Log successful payment
                $stmt = $conn->prepare("INSERT INTO payment_logs (payment_id, order_id, action, status, gateway_response) VALUES (?, ?, 'payment_verified', 'completed', ?)");
                $gateway_response = json_encode($intent['data']['attributes']);
                $stmt->bind_param("iis", $payment_id, $payment['order_id'], $gateway_response);
                $stmt->execute();
            }
        }
    }
    
    if ($verified) {
        // Clear cart on successful payment
        unset($_SESSION['cart']);
        
        // Redirect to order confirmation
        $redirect_order_id = $payment['order_string_id'] ?? $payment['order_id'];
        header('Location: ../order_confirmation.php?order_id=' . $redirect_order_id . '&payment_success=1');
        exit;
    } else {
        // Show payment pending/processing page instead of error
        $redirect_order_id = $payment['order_string_id'] ?? $payment['order_id'];
        header('Location: ../order_confirmation.php?order_id=' . $redirect_order_id . '&payment_pending=1');
        exit;
    }
    
} catch (Exception $e) {
    // Log verification error
    if (isset($payment)) {
        $error_message = $e->getMessage();
        $stmt = $conn->prepare("INSERT INTO payment_logs (payment_id, order_id, action, status, error_message) VALUES (?, ?, 'verification_failed', 'failed', ?)");
        $stmt->bind_param("iis", $payment_id, $payment['order_id'], $error_message);
        $stmt->execute();
    }
    
    error_log('Payment verification error: ' . $e->getMessage());
    header('Location: ../checkout.php?error=payment_verification_failed');
    exit;
}
?>