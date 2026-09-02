<?php
// Direct debug of payment processing
require_once 'includes/db.php';
require_once 'includes/PayMongoHelper.php';

echo "=== Direct Payment Debug ===\n";

// Simulate exact conditions
$order_id = 'ORD-2025-88600';
$payment_method = 'paymongo_gcash';
$total_amount = 610.0;
$user_id = null;

// Set up session data
session_start();
$_SESSION['order_confirmation'] = [
    'order_id' => 'ORD-2025-88600',
    'customer_name' => 'Jane Doe',
    'customer_email' => 'jane.doe@example.com', 
    'customer_phone' => '09123456789',
    'shipping_address' => '456 Main St, Quezon City, Metro Manila 1100, Philippines',
    'billing_address' => '456 Main St, Quezon City, Metro Manila 1100, Philippines',
    'payment_method' => 'paymongo_gcash',
    'total' => 610.00
];

echo "Order ID: $order_id\n";
echo "Payment Method: $payment_method\n";
echo "Total Amount: $total_amount\n";
echo "User ID: " . ($user_id ?? 'null') . "\n\n";

// Get order data from session
$order = null;
if (isset($_SESSION['order_confirmation']) && $_SESSION['order_confirmation']['order_id'] === $order_id) {
    $order = $_SESSION['order_confirmation'];
    echo "✅ Order found in session\n";
} else {
    // Try to get from database
    echo "Checking database for order...\n";
    if ($user_id) {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
        $stmt->bind_param("si", $order_id, $user_id);
    } else {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
        $stmt->bind_param("s", $order_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        echo "✅ Order found in database\n";
    } else {
        echo "❌ Order not found in database\n";
    }
}

if (!$order) {
    echo "❌ No order found - this is the problem!\n";
    exit;
}

echo "Order data found: " . json_encode($order, JSON_PRETTY_PRINT) . "\n\n";

// Create order in database if not exists
$stmt = $conn->prepare("SELECT id FROM orders WHERE order_id = ?");
$stmt->bind_param("s", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $existing = $result->fetch_assoc();
    $order['id'] = $existing['id'];
    echo "✅ Order already in database with ID: " . $order['id'] . "\n";
} else {
    echo "Creating order in database...\n";
    
    $stmt = $conn->prepare("INSERT INTO orders (order_id, user_id, customer_name, customer_email, customer_phone, total_amount, shipping_address, billing_address, payment_method, status, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'Unpaid')");
    
    // Debug the values being inserted
    echo "Inserting values:\n";
    echo "  order_id: " . $order['order_id'] . "\n";
    echo "  user_id: " . ($user_id ?? 'NULL') . "\n";
    echo "  customer_name: " . $order['customer_name'] . "\n";
    echo "  customer_email: " . $order['customer_email'] . "\n";
    echo "  customer_phone: " . $order['customer_phone'] . "\n";
    echo "  total_amount: " . $order['total'] . "\n";
    echo "  shipping_address: " . $order['shipping_address'] . "\n";
    echo "  billing_address: " . $order['billing_address'] . "\n";
    echo "  payment_method: " . $order['payment_method'] . "\n";
    
    $stmt->bind_param("sisssdsss", 
        $order['order_id'],
        $user_id,
        $order['customer_name'],
        $order['customer_email'], 
        $order['customer_phone'],
        $order['total'],
        $order['shipping_address'],
        $order['billing_address'],
        $order['payment_method']
    );
    
    if ($stmt->execute()) {
        $order['id'] = $conn->insert_id;
        echo "✅ Order created with database ID: " . $order['id'] . "\n";
    } else {
        echo "❌ Error creating order: " . $stmt->error . "\n";
        exit;
    }
}

// Now test payment creation
$paymongo = new PayMongoHelper();
$method = str_replace('paymongo_', '', $payment_method);
$gateway_fee = $paymongo->calculateGatewayFee($total_amount, $method);
$final_amount = $total_amount + $gateway_fee;

echo "\nCreating payment record...\n";
echo "Order DB ID: " . $order['id'] . "\n";
echo "Final amount: $final_amount\n";

$order_db_id = $order['id'];
$stmt = $conn->prepare("INSERT INTO payments (order_id, user_id, payment_method, amount, gateway_fee, status, created_at) VALUES (?, ?, ?, ?, ?, 'Pending', NOW())");

echo "Payment insertion values:\n";
echo "  order_id (DB ID): $order_db_id\n";
echo "  user_id: " . ($user_id ?? 'NULL') . "\n";
echo "  payment_method: $payment_method\n";
echo "  amount: $final_amount\n";
echo "  gateway_fee: $gateway_fee\n";

$stmt->bind_param("iisdd", $order_db_id, $user_id, $payment_method, $final_amount, $gateway_fee);

if ($stmt->execute()) {
    $payment_id = $conn->insert_id;
    echo "✅ Payment record created with ID: $payment_id\n";
} else {
    echo "❌ Error creating payment: " . $stmt->error . "\n";
}

echo "\n=== Debug Complete ===\n";
?>