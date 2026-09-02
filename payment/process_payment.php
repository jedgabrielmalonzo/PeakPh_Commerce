<?php
require_once(__DIR__ . '/../includes/db.php');
require_once(__DIR__ . '/../includes/PayMongoHelper.php');
session_start();

// Handle both GET and POST parameters for flexible integration
$order_id = null;
$payment_method = 'cod';
$total_amount = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'] ?? null;
    $payment_method = $_POST['payment_method'] ?? 'cod';
    $total_amount = floatval($_POST['total_amount'] ?? 0);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $order_id = $_GET['order_id'] ?? null;
    $payment_method = $_GET['method'] ?? 'cod';
    $total_amount = floatval($_GET['amount'] ?? 0);
}

// If no direct parameters, check session data
if (!$order_id && isset($_SESSION['payment_data'])) {
    $payment_data = $_SESSION['payment_data'];
    $order_id = $payment_data['order_id'];
    $payment_method = $payment_data['payment_method'];
    $total_amount = $payment_data['total_amount'];
}

// Get user ID (allow guest orders)
$user_id = $_SESSION['user_id'] ?? null;

// Validate input
if (!$order_id || $total_amount <= 0) {
    header('Location: ../checkout.php?error=invalid_data');
    exit;
}

// Get order data from session if available (for new orders)
$order = null;
if (isset($_SESSION['order_confirmation']) && $_SESSION['order_confirmation']['order_id'] === $order_id) {
    $order = $_SESSION['order_confirmation'];
} else {
    // Try to get from database
    if ($user_id) {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
        $stmt->bind_param("si", $order_id, $user_id);
    } else {
        // For guest orders, match by order_id pattern
        $stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
        $stmt->bind_param("s", $order_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
    }
}

if (!$order) {
    // Check if order exists in database
    $stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
    $stmt->bind_param("s", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Order exists in database
        $order = $result->fetch_assoc();
    } elseif (isset($_SESSION['order_confirmation'])) {
        // Create order from session data (fallback for old flow)
        $order_session = $_SESSION['order_confirmation'];
        
        // Create order in database
        $stmt = $conn->prepare("INSERT INTO orders (order_id, user_id, customer_name, customer_email, customer_phone, total_amount, shipping_address, billing_address, payment_method, status, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'Unpaid')");
        $stmt->bind_param("sisssdsss", 
            $order_session['order_id'],
            $user_id,
            $order_session['customer_name'],
            $order_session['customer_email'], 
            $order_session['customer_phone'],
            $order_session['total'],
            $order_session['shipping_address'],
            $order_session['billing_address'],
            $order_session['payment_method']
        );
        
        if ($stmt->execute()) {
            $order = $order_session;
            $order['id'] = $conn->insert_id; // Get the database ID
        } else {
            error_log("Failed to create order from session: " . $conn->error);
            header('Location: ../checkout.php?error=order_creation_failed');
            exit;
        }
    } else {
        error_log("Order not found and no session data available for order: " . $order_id);
        header('Location: ../checkout.php?error=order_not_found');
        exit;
    }
}

try {
    if (in_array($payment_method, ['paymongo_gcash', 'paymongo_card'])) {
        $paymongo = new PayMongoHelper();
        
        // Calculate fees
        $method = str_replace('paymongo_', '', $payment_method);
        $gateway_fee = $paymongo->calculateGatewayFee($total_amount, $method);
        $final_amount = $total_amount + $gateway_fee;
        
        // Create payment record
        $order_db_id = $order['id']; // Use the database order ID
        $stmt = $conn->prepare("INSERT INTO payments (order_id, user_id, payment_method, amount, gateway_fee, status, created_at) VALUES (?, ?, ?, ?, ?, 'Pending', NOW())");
        $stmt->bind_param("iisdd", $order_db_id, $user_id, $payment_method, $final_amount, $gateway_fee);
        $stmt->execute();
        $payment_id = $conn->insert_id;
        
        // Log payment attempt
        $stmt = $conn->prepare("INSERT INTO payment_logs (payment_id, order_id, action, status, ip_address, user_agent) VALUES (?, ?, 'initiate', 'pending', ?, ?)");
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $stmt->bind_param("iiss", $payment_id, $order_db_id, $ip_address, $user_agent);
        $stmt->execute();        // Create PayMongo Payment Intent
        $description = "Order #{$order_id} - PeakPH Commerce";
        $metadata = [
            'order_id' => $order_id,
            'payment_id' => $payment_id,
            'user_id' => $user_id
        ];
        
        $payment_intent = $paymongo->createPaymentIntent($final_amount, $method, $description, $metadata);
        $paymongo->logTransaction('create_intent', ['amount' => $final_amount, 'method' => $method], $payment_intent);
        
        if ($payment_intent && isset($payment_intent['data']['id'])) {
            $intent_id = $payment_intent['data']['id'];
            
            // Update payment with PayMongo intent ID
            $stmt = $conn->prepare("UPDATE payments SET paymongo_payment_intent_id = ? WHERE id = ?");
            $stmt->bind_param("si", $intent_id, $payment_id);
            $stmt->execute();
            
            if ($payment_method === 'paymongo_gcash') {
                // Create GCash source
                $redirect_urls = [
                    'success' => "http://localhost/PeakPH_Commerce/payment/success.php?payment_id={$payment_id}",
                    'failed' => "http://localhost/PeakPH_Commerce/payment/failed.php?payment_id={$payment_id}"
                ];
                
                $source = $paymongo->createGCashSource($final_amount, $redirect_urls);
                $paymongo->logTransaction('create_source', ['amount' => $final_amount], $source);
                
                if ($source && isset($source['data']['id'])) {
                    $source_id = $source['data']['id'];
                    
                    // Update payment with source ID
                    $stmt = $conn->prepare("UPDATE payments SET paymongo_source_id = ? WHERE id = ?");
                    $stmt->bind_param("si", $source_id, $payment_id);
                    $stmt->execute();
                    
                    // Attach source to payment intent
                    $attached = $paymongo->attachPaymentMethod($intent_id, $source_id);
                    $paymongo->logTransaction('attach_source', ['intent_id' => $intent_id, 'source_id' => $source_id], $attached);
                    
                    // Update order payment status
                    $stmt = $conn->prepare("UPDATE orders SET payment_status = 'Pending' WHERE id = ?");
                    $stmt->bind_param("i", $order_id);
                    $stmt->execute();
                    
                    // Redirect to GCash
                    if (isset($source['data']['attributes']['redirect']['checkout_url'])) {
                        // Log redirect
                        $stmt = $conn->prepare("INSERT INTO payment_logs (payment_id, order_id, action, status, gateway_response) VALUES (?, ?, 'redirect_gcash', 'pending', ?)");
                        $checkout_url = $source['data']['attributes']['redirect']['checkout_url'];
                        $stmt->bind_param("iis", $payment_id, $order_id, $checkout_url);
                        $stmt->execute();
                        
                        header('Location: ' . $checkout_url);
                        exit;
                    }
                }
            } else {
                // For card payments, redirect to card form
                header("Location: ../payment/card_form.php?payment_id={$payment_id}");
                exit;
            }
        }
        
        throw new Exception('Failed to create PayMongo payment');
        
    } else {
        // COD Payment
        $stmt = $conn->prepare("INSERT INTO payments (order_id, user_id, payment_method, amount, status, created_at) VALUES (?, ?, 'cod', ?, 'Pending', NOW())");
        $stmt->bind_param("iid", $order_id, $user_id, $total_amount);
        $stmt->execute();
        $payment_id = $conn->insert_id;
        
        // Update order payment status
        $stmt = $conn->prepare("UPDATE orders SET payment_status = 'Unpaid', status = 'Pending' WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        
        // Log COD payment
        $stmt = $conn->prepare("INSERT INTO payment_logs (payment_id, order_id, action, status) VALUES (?, ?, 'cod_selected', 'pending')");
        $stmt->bind_param("ii", $payment_id, $order_id);
        $stmt->execute();
        
        header('Location: ../order_confirmation.php?order_id=' . $order_id);
        exit;
    }
    
} catch (Exception $e) {
    // Log error
    if (isset($payment_id)) {
        $error_message = $e->getMessage();
        $stmt = $conn->prepare("INSERT INTO payment_logs (payment_id, order_id, action, status, error_message) VALUES (?, ?, 'error', 'failed', ?)");
        $stmt->bind_param("iis", $payment_id, $order_id, $error_message);
        $stmt->execute();
    }
    
    error_log('Payment processing error: ' . $e->getMessage());
    header('Location: ../checkout.php?error=payment_failed&message=' . urlencode($e->getMessage()));
    exit;
}
?>