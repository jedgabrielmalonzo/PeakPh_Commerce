<?php
// Debug Payment Processing
session_start();

// Set up test data
$_SESSION['order_confirmation'] = [
    'order_id' => 'ORD-2025-DEBUG',
    'customer_name' => 'John Doe',
    'customer_email' => 'john.doe@example.com',
    'customer_phone' => '09123456789',
    'shipping_address' => '123 Test Street, Manila, Metro Manila 1000, Philippines',
    'billing_address' => '123 Test Street, Manila, Metro Manila 1000, Philippines',
    'payment_method' => 'paymongo_gcash',
    'items' => [
        [
            'id' => 'demo1',
            'name' => 'Test Product',
            'price' => 500.00,
            'quantity' => 1,
            'image' => 'test.jpg',
            'is_database' => false
        ]
    ],
    'subtotal' => 500.00,
    'shipping_fee' => 50.00,
    'tax_amount' => 60.00,
    'total' => 610.00,
    'order_date' => date('Y-m-d H:i:s'),
    'order_notes' => 'Test order'
];

require_once 'includes/db.php';
require_once 'includes/PayMongoHelper.php';

echo "=== Debug Payment Processing ===\n";

// Test variables
$order_id = 'ORD-2025-DEBUG';
$payment_method = 'paymongo_gcash';
$total_amount = 610.0;
$user_id = null;

echo "Order ID: $order_id\n";
echo "Payment Method: $payment_method\n";
echo "Total Amount: $total_amount\n";
echo "User ID: " . ($user_id ?? 'null') . "\n\n";

// Get order data from session if available (for new orders)
$order = null;
if (isset($_SESSION['order_confirmation']) && $_SESSION['order_confirmation']['order_id'] === $order_id) {
    $order = $_SESSION['order_confirmation'];
    echo "✅ Order found in session\n";
    echo "Order data: " . json_encode($order, JSON_PRETTY_PRINT) . "\n\n";
} else {
    echo "❌ Order not found in session\n";
}

if (!$order) {
    echo "❌ No order available for payment processing\n";
    exit;
}

try {
    if (in_array($payment_method, ['paymongo_gcash', 'paymongo_card'])) {
        $paymongo = new PayMongoHelper();
        echo "✅ PayMongo helper created\n";
        
        // Calculate fees
        $method = str_replace('paymongo_', '', $payment_method);
        $gateway_fee = $paymongo->calculateGatewayFee($total_amount, $method);
        $final_amount = $total_amount + $gateway_fee;
        
        echo "Gateway fee: ₱$gateway_fee\n";
        echo "Final amount: ₱$final_amount\n\n";
        
        // Check if order exists in database
        echo "Checking if order exists in database...\n";
        $stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
        $stmt->bind_param("s", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $existing_order = $result->fetch_assoc();
            echo "✅ Order already exists in database with ID: " . $existing_order['id'] . "\n";
            $order['id'] = $existing_order['id'];
        } else {
            echo "Order doesn't exist, creating new one...\n";
            
            // Create order in database
            $stmt = $conn->prepare("INSERT INTO orders (order_id, user_id, customer_name, customer_email, customer_phone, total_amount, shipping_address, billing_address, payment_method, status, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'Unpaid')");
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
                $order['id'] = $conn->insert_id; // Get the database ID
                echo "✅ Order created with database ID: " . $order['id'] . "\n";
            } else {
                throw new Exception("Failed to create order: " . $stmt->error);
            }
        }
        
        // Create payment record
        $order_db_id = $order['id'];
        echo "Creating payment record with order_db_id: $order_db_id\n";
        
        $stmt = $conn->prepare("INSERT INTO payments (order_id, user_id, payment_method, amount, gateway_fee, status, created_at) VALUES (?, ?, ?, ?, ?, 'Pending', NOW())");
        $stmt->bind_param("iisdd", $order_db_id, $user_id, $payment_method, $final_amount, $gateway_fee);
        
        if ($stmt->execute()) {
            $payment_id = $conn->insert_id;
            echo "✅ Payment record created with ID: $payment_id\n";
        } else {
            throw new Exception("Failed to create payment: " . $stmt->error);
        }
        
        echo "\n✅ Payment processing setup complete!\n";
        echo "Next step would be PayMongo API calls...\n";
        
    } else {
        echo "❌ Invalid payment method: $payment_method\n";
    }
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}
?>