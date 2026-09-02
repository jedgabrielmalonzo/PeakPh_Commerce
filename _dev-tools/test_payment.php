<?php
// Test Payment Processing Directly
session_start();

// Set up test data
$_SESSION['order_confirmation'] = [
    'order_id' => 'ORD-2025-TEST',
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

// Set up GET parameters
$_GET = [
    'order_id' => 'ORD-2025-TEST',
    'method' => 'paymongo_gcash',
    'amount' => '610'
];

$_SERVER['REQUEST_METHOD'] = 'GET';

echo "=== Payment Processing Test ===\n";
echo "Order ID: " . $_GET['order_id'] . "\n";
echo "Method: " . $_GET['method'] . "\n";
echo "Amount: ₱" . $_GET['amount'] . "\n\n";

echo "Testing payment processing...\n";

// Capture output and errors
ob_start();
error_reporting(E_ALL);

try {
    include 'payment/process_payment.php';
    echo "✅ Payment processing completed without fatal errors\n";
} catch (Exception $e) {
    echo "❌ Exception caught: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "❌ Error caught: " . $e->getMessage() . "\n";
}

$output = ob_get_clean();
echo "\nOutput:\n";
echo $output;
?>