<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/PayMongoHelper.php';

// Set content type to JSON
header('Content-Type: application/json');

// Initialize response
$response = [
    'success' => false,
    'message' => '',
    'order_id' => null
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

// Check if cart exists and has items
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $response['message'] = 'Cart is empty';
    echo json_encode($response);
    exit;
}

// Validate required fields
$required_fields = ['first_name', 'last_name', 'email', 'phone', 'shipping_address', 'shipping_city', 'shipping_province', 'shipping_postal_code', 'payment_method'];
$missing_fields = [];

foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        $missing_fields[] = $field;
    }
}

if (!empty($missing_fields)) {
    $response['message'] = 'Missing required fields: ' . implode(', ', $missing_fields);
    echo json_encode($response);
    exit;
}

// Get customer information
$customer_name = trim($_POST['first_name']) . ' ' . trim($_POST['last_name']);
$customer_email = trim($_POST['email']);
$customer_phone = trim($_POST['phone']);

// Prepare shipping address
$shipping_address = trim($_POST['shipping_address']);
if (!empty($_POST['shipping_address_2'])) {
    $shipping_address .= ', ' . trim($_POST['shipping_address_2']);
}
$shipping_address .= ', ' . trim($_POST['shipping_city']);
$shipping_address .= ', ' . trim($_POST['shipping_province']);
$shipping_address .= ' ' . trim($_POST['shipping_postal_code']);
$shipping_address .= ', Philippines';

// Prepare billing address
$billing_address = $shipping_address; // Default to shipping address
if (!isset($_POST['same_as_shipping']) || $_POST['same_as_shipping'] !== 'on') {
    // Use separate billing address if provided
    if (!empty($_POST['billing_address'])) {
        $billing_address = trim($_POST['billing_address']);
        if (!empty($_POST['billing_address_2'])) {
            $billing_address .= ', ' . trim($_POST['billing_address_2']);
        }
        $billing_address .= ', ' . trim($_POST['billing_city']);
        $billing_address .= ', ' . trim($_POST['billing_province']);
        $billing_address .= ' ' . trim($_POST['billing_postal_code']);
        $billing_address .= ', Philippines';
    }
}

$payment_method = $_POST['payment_method'];
$order_notes = isset($_POST['order_notes']) ? trim($_POST['order_notes']) : '';

// Handle map location data
$map_latitude = isset($_POST['map_latitude']) && !empty($_POST['map_latitude']) ? floatval($_POST['map_latitude']) : null;
$map_longitude = isset($_POST['map_longitude']) && !empty($_POST['map_longitude']) ? floatval($_POST['map_longitude']) : null;
$map_address = isset($_POST['map_address']) && !empty($_POST['map_address']) ? trim($_POST['map_address']) : null;
$use_map_location = isset($_POST['use_map_location']) && $_POST['use_map_location'] === 'on';

// Calculate order totals
$subtotal = 0;
$item_count = 0;
$order_items = [];

foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $item_count += $item['quantity'];
    $order_items[] = [
        'id' => $item['id'] ?? 'demo',
        'name' => $item['name'],
        'price' => $item['price'],
        'quantity' => $item['quantity'],
        'image' => $item['image'] ?? '',
        'is_database' => $item['is_database'] ?? false
    ];
}

$shipping_fee = 50.00;
$tax_rate = 0.12; // 12% VAT
$tax_amount = $subtotal * $tax_rate;
$order_total = $subtotal + $shipping_fee + $tax_amount;

// Start transaction for inventory updates
if (isDatabaseConnected()) {
    try {
        // Begin transaction
        $conn = $GLOBALS['conn'];
        $conn->autocommit(false);
        
        // Check stock availability and update inventory for database products
        foreach ($order_items as $item) {
            if ($item['is_database']) {
                // Check current stock
                $check_query = "SELECT stock FROM inventory WHERE id = ? FOR UPDATE";
                $check_result = executeQuery($check_query, [$item['id']], "i");
                
                if (!$check_result || $check_result->num_rows === 0) {
                    throw new Exception("Product not found: " . $item['name']);
                }
                
                $current_stock = $check_result->fetch_assoc()['stock'];
                
                if ($current_stock < $item['quantity']) {
                    throw new Exception("Insufficient stock for " . $item['name'] . ". Available: " . $current_stock);
                }
                
                // Update stock
                $new_stock = $current_stock - $item['quantity'];
                $update_query = "UPDATE inventory SET stock = ? WHERE id = ?";
                $update_result = executeQuery($update_query, [$new_stock, $item['id']], "ii");
                
                if ($update_result !== true) {
                    throw new Exception("Failed to update stock for " . $item['name']);
                }
            }
        }
        
        // Generate order ID
        $order_id = 'ORD-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
        
        // Get user ID if logged in
        $user_id = $_SESSION['user_id'] ?? null;
        
        // Create order in database
        $order_stmt = $conn->prepare("INSERT INTO orders (order_id, user_id, customer_name, customer_email, customer_phone, total_amount, shipping_address, billing_address, payment_method, status, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'Unpaid')");
        $order_stmt->bind_param("sisssdsss", 
            $order_id,
            $user_id,
            $customer_name,
            $customer_email, 
            $customer_phone,
            $order_total,
            $shipping_address,
            $billing_address,
            $payment_method
        );
        
        if (!$order_stmt->execute()) {
            throw new Exception("Failed to create order in database: " . $conn->error);
        }
        
        $database_order_id = $conn->insert_id;
        
        // Insert order items
        foreach ($order_items as $item) {
            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, total) VALUES (?, ?, ?, ?, ?, ?)");
            $item_total = $item['price'] * $item['quantity'];
            $product_id = $item['is_database'] ? $item['id'] : null;
            $item_stmt->bind_param("iisdii", 
                $database_order_id,
                $product_id,
                $item['name'],
                $item['price'],
                $item['quantity'],
                $item_total
            );
            if (!$item_stmt->execute()) {
                error_log("Failed to insert order item: " . $conn->error);
            }
        }
        
        // Log order creation
        error_log("Order created in database: Order ID {$order_id}, DB ID {$database_order_id}");
        
        // Store order confirmation data in session
        $_SESSION['order_confirmation'] = [
            'order_id' => $order_id,
            'customer_name' => $customer_name,
            'customer_email' => $customer_email,
            'customer_phone' => $customer_phone,
            'shipping_address' => $shipping_address,
            'billing_address' => $billing_address,
            'payment_method' => $payment_method,
            'items' => $order_items,
            'subtotal' => $subtotal,
            'shipping_fee' => $shipping_fee,
            'tax_amount' => $tax_amount,
            'total' => $order_total,
            'order_date' => date('Y-m-d H:i:s'),
            'order_notes' => $order_notes,
            'map_location' => $use_map_location ? [
                'latitude' => $map_latitude,
                'longitude' => $map_longitude,
                'address' => $map_address
            ] : null
        ];
        
        // Commit transaction
        $conn->commit();
        $conn->autocommit(true);
        
        // Handle payment processing
        if (in_array($payment_method, ['paymongo_gcash', 'paymongo_card'])) {
            // Redirect to PayMongo payment processing
            $redirect_url = 'payment/process_payment.php';
            $redirect_data = [
                'order_id' => $order_id,
                'payment_method' => $payment_method,
                'total_amount' => $order_total
            ];
            
            // Store payment data in session
            $_SESSION['payment_data'] = $redirect_data;
            
            $response = [
                'success' => true,
                'message' => 'Redirecting to payment gateway...',
                'order_id' => $order_id,
                'redirect_url' => $redirect_url . '?order_id=' . $order_id . '&method=' . $payment_method . '&amount=' . $order_total,
                'total' => number_format($order_total, 2)
            ];
        } else {
            // COD - Update order status and clear cart
            $cod_stmt = $conn->prepare("UPDATE orders SET payment_status = 'Pending' WHERE order_id = ?");
            $cod_stmt->bind_param("s", $order_id);
            $cod_stmt->execute();
            
            // Create COD payment record
            $cod_payment_stmt = $conn->prepare("INSERT INTO payments (order_id, user_id, payment_method, amount, status, created_at) VALUES (?, ?, ?, ?, 'Pending', NOW())");
            $cod_payment_stmt->bind_param("iisd", $database_order_id, $user_id, $payment_method, $order_total);
            $cod_payment_stmt->execute();
            
            $_SESSION['cart'] = [];
            
            $response = [
                'success' => true,
                'message' => 'Order placed successfully!',
                'order_id' => $order_id,
                'total' => number_format($order_total, 2)
            ];
        }
        
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        $conn->autocommit(true);
        
        $response['message'] = $e->getMessage();
        error_log("Checkout error: " . $e->getMessage());
    }
} else {
    // If database is not available, process demo orders (no persistent storage)
    $order_id = 'DEMO-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
    
    error_log("Database not available - processing demo order: " . $order_id);
    
    // Store order confirmation data in session for demo
    $_SESSION['order_confirmation'] = [
        'order_id' => $order_id,
        'customer_name' => $customer_name,
        'customer_email' => $customer_email,
        'customer_phone' => $customer_phone,
        'shipping_address' => $shipping_address,
        'billing_address' => $billing_address,
        'payment_method' => $payment_method,
        'items' => $order_items,
        'subtotal' => $subtotal,
        'shipping_fee' => $shipping_fee,
        'tax_amount' => $tax_amount,
        'total' => $order_total,
        'order_date' => date('Y-m-d H:i:s'),
        'order_notes' => $order_notes,
        'map_location' => $use_map_location ? [
            'latitude' => $map_latitude,
            'longitude' => $map_longitude,
            'address' => $map_address
        ] : null
    ];
    
    // Handle payment processing for demo
    if (in_array($payment_method, ['paymongo_gcash', 'paymongo_card'])) {
        // Store payment data in session
        $_SESSION['payment_data'] = [
            'order_id' => $order_id,
            'payment_method' => $payment_method,
            'total_amount' => $order_total
        ];
        
        $response = [
            'success' => true,
            'message' => 'Redirecting to payment gateway...',
            'order_id' => $order_id,
            'redirect_url' => 'payment/process_payment.php?order_id=' . $order_id . '&method=' . $payment_method . '&amount=' . $order_total,
            'total' => number_format($order_total, 2)
        ];
    } else {
        // COD - Clear cart
        $_SESSION['cart'] = [];
        
        $response = [
            'success' => true,
            'message' => 'Demo order placed successfully!',
            'order_id' => $order_id,
            'total' => number_format($order_total, 2)
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>