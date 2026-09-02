<?php
require_once('../includes/db.php');
require_once('../includes/PayMongoHelper.php');
require_once('../includes/security.php');
require_once('../includes/environment.php');

// Set content type to JSON for webhook
header('Content-Type: application/json');

// Get webhook payload
$payload = file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_PAYMONGO_SIGNATURE'] ?? '';

// Rate limiting - prevent webhook spam
$client_ip = getClientIP();
if (!checkRateLimit('webhook', $client_ip, 100, 60)) {
    http_response_code(429);
    logSafely('Webhook rate limit exceeded', ['ip' => $client_ip]);
    exit(json_encode(['error' => 'Rate limit exceeded']));
}

// CRITICAL: Verify webhook signature
$config = require_once('../config/paymongo.php');
if (!verifyWebhookSignature($payload, $sig_header, $config['webhook_signature_key'])) {
    http_response_code(401);
    logSafely('Invalid webhook signature', [
        'ip' => $client_ip,
        'signature_provided' => !empty($sig_header)
    ]);
    exit(json_encode(['error' => 'Invalid signature']));
}

// Log webhook safely (without sensitive data)
logSafely('PayMongo Webhook Received', [
    'ip' => $client_ip,
    'signature_valid' => true
]);

try {
    // Check database connection
    if (!$conn || $db_connection_error) {
        throw new Exception('Database connection unavailable');
    }
    
    // Parse webhook data
    $event = json_decode($payload, true);
    
    if (!$event || !isset($event['data'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid payload']);
        exit;
    }
    
    $webhook_id = $event['data']['id'] ?? uniqid();
    $event_type = $event['data']['attributes']['type'] ?? 'unknown';
    $event_data = $event['data']['attributes']['data'] ?? [];
    
    // Extract relevant IDs with validation
    $payment_intent_id = null;
    $source_id = null;
    $status = 'unknown';
    
    if (isset($event_data['id'])) {
        if ($event_type === 'source.chargeable') {
            $source_id = $event_data['id'];
            // Validate source ID format
            if (!validateSourceId($source_id)) {
                throw new Exception('Invalid source_id format');
            }
            $status = $event_data['attributes']['status'] ?? 'unknown';
        } else {
            $payment_intent_id = $event_data['id'];
            // Validate payment intent ID format
            if ($payment_intent_id && !validatePaymentIntentId($payment_intent_id)) {
                throw new Exception('Invalid payment_intent_id format');
            }
            $status = $event_data['attributes']['status'] ?? 'unknown';
        }
    }
    
    // Store webhook in database
    $stmt = $conn->prepare("INSERT INTO paymongo_webhooks (webhook_id, event_type, payment_intent_id, source_id, status, payload, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }
    $stmt->bind_param("ssssss", $webhook_id, $event_type, $payment_intent_id, $source_id, $status, $payload);
    $stmt->execute();
    $webhook_db_id = $conn->insert_id;
    
    // Process specific webhook events
    switch ($event_type) {
        case 'payment_intent.payment.paid':
        case 'payment_paid':
            handlePaymentPaid($conn, $payment_intent_id, $event_data);
            break;
            
        case 'payment_intent.payment.failed':
        case 'payment_failed':
            handlePaymentFailed($conn, $payment_intent_id, $event_data);
            break;
            
        case 'source.chargeable':
            handleSourceChargeable($conn, $source_id, $event_data);
            break;
            
        default:
            error_log("Unhandled webhook event: {$event_type}");
    }
    
    // Mark webhook as processed
    $stmt = $conn->prepare("UPDATE paymongo_webhooks SET processed = 1, processed_at = NOW() WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $webhook_db_id);
        $stmt->execute();
    }
    
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Webhook processed']);
    
} catch (Exception $e) {
    // Log safely without exposing sensitive data
    logSafely('Webhook processing error', [
        'error' => $e->getMessage(),
        'event_type' => $event_type ?? 'unknown'
    ]);
    
    http_response_code(500);
    echo json_encode(['error' => 'Processing failed']);
}

/**
 * Handle successful payment webhook
 */
function handlePaymentPaid($conn, $payment_intent_id, $event_data) {
    if (!$payment_intent_id) return;
    
    // Find payment by intent ID
    $stmt = $conn->prepare("SELECT * FROM payments WHERE paymongo_payment_intent_id = ? AND status != 'Completed'");
    $stmt->bind_param("s", $payment_intent_id);
    $stmt->execute();
    $payment = $stmt->get_result()->fetch_assoc();
    
    if ($payment) {
        // Update payment status
        $stmt = $conn->prepare("UPDATE payments SET status = 'Completed', paid_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $payment['id']);
        $stmt->execute();
        
        // Update order status
        $stmt = $conn->prepare("UPDATE orders SET status = 'Processing', payment_status = 'Paid' WHERE id = ?");
        $stmt->bind_param("i", $payment['order_id']);
        $stmt->execute();
        
        // Log successful payment
        $gateway_response = json_encode($event_data);
        $stmt = $conn->prepare("INSERT INTO payment_logs (payment_id, order_id, action, status, gateway_response) VALUES (?, ?, 'webhook_payment_paid', 'completed', ?)");
        $stmt->bind_param("iis", $payment['id'], $payment['order_id'], $gateway_response);
        $stmt->execute();
        
        error_log("Payment completed via webhook: Payment ID {$payment['id']}, Order ID {$payment['order_id']}");
    }
}

/**
 * Handle failed payment webhook
 */
function handlePaymentFailed($conn, $payment_intent_id, $event_data) {
    if (!$payment_intent_id) return;
    
    // Find payment by intent ID
    $stmt = $conn->prepare("SELECT * FROM payments WHERE paymongo_payment_intent_id = ? AND status = 'Pending'");
    $stmt->bind_param("s", $payment_intent_id);
    $stmt->execute();
    $payment = $stmt->get_result()->fetch_assoc();
    
    if ($payment) {
        // Update payment status
        $stmt = $conn->prepare("UPDATE payments SET status = 'Failed' WHERE id = ?");
        $stmt->bind_param("i", $payment['id']);
        $stmt->execute();
        
        // Update order status
        $stmt = $conn->prepare("UPDATE orders SET payment_status = 'Failed' WHERE id = ?");
        $stmt->bind_param("i", $payment['order_id']);
        $stmt->execute();
        
        // Log failed payment
        $error_details = json_encode($event_data);
        $stmt = $conn->prepare("INSERT INTO payment_logs (payment_id, order_id, action, status, error_message) VALUES (?, ?, 'webhook_payment_failed', 'failed', ?)");
        $stmt->bind_param("iis", $payment['id'], $payment['order_id'], $error_details);
        $stmt->execute();
        
        error_log("Payment failed via webhook: Payment ID {$payment['id']}, Order ID {$payment['order_id']}");
    }
}

/**
 * Handle source chargeable webhook (for GCash)
 */
function handleSourceChargeable($conn, $source_id, $event_data) {
    if (!$source_id) return;
    
    // Find payment by source ID
    $stmt = $conn->prepare("SELECT * FROM payments WHERE paymongo_source_id = ? AND status = 'Pending'");
    $stmt->bind_param("s", $source_id);
    $stmt->execute();
    $payment = $stmt->get_result()->fetch_assoc();
    
    if ($payment) {
        // Log source chargeable event
        $source_data = json_encode($event_data);
        $stmt = $conn->prepare("INSERT INTO payment_logs (payment_id, order_id, action, status, gateway_response) VALUES (?, ?, 'source_chargeable', 'processing', ?)");
        $stmt->bind_param("iis", $payment['id'], $payment['order_id'], $source_data);
        $stmt->execute();
        
        error_log("Source chargeable: Payment ID {$payment['id']}, Source ID {$source_id}");
    }
}
?>