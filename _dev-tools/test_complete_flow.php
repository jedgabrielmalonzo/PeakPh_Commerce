<?php
// Test complete payment flow
session_start();

// Clean up previous test data
unset($_SESSION['order_confirmation']);
unset($_SESSION['payment_data']);

echo "=== Complete Payment Flow Test ===\n";

// Step 1: Simulate checkout
echo "Step 1: Simulating checkout...\n";

// Set up cart
$_SESSION['cart'] = [
    [
        'id' => 'test1',
        'name' => 'Test Product',
        'price' => 500.00,
        'quantity' => 1,
        'image' => 'test.jpg',
        'is_database' => false
    ]
];

// Simulate POST data for checkout
$_POST = [
    'first_name' => 'Jane',
    'last_name' => 'Doe',
    'email' => 'jane.doe@example.com',
    'phone' => '09123456789',
    'shipping_address' => '456 Main St',
    'shipping_city' => 'Quezon City',
    'shipping_province' => 'Metro Manila',
    'shipping_postal_code' => '1100',
    'payment_method' => 'paymongo_gcash',
    'same_as_shipping' => 'on'
];

$_SERVER['REQUEST_METHOD'] = 'POST';

// Capture checkout output
ob_start();
include 'process_checkout.php';
$checkout_output = ob_get_clean();

echo "Checkout response:\n$checkout_output\n\n";

$checkout_response = json_decode($checkout_output, true);

if ($checkout_response && $checkout_response['success']) {
    echo "✅ Step 1 complete: Checkout successful\n";
    echo "Order ID: " . $checkout_response['order_id'] . "\n";
    echo "Redirect URL: " . $checkout_response['redirect_url'] . "\n\n";
    
    // Step 2: Test payment processing
    echo "Step 2: Testing payment processing...\n";
    
    // Parse redirect URL
    $url_parts = parse_url($checkout_response['redirect_url']);
    parse_str($url_parts['query'] ?? '', $query_params);
    
    // Set up for payment processing
    $_GET = $query_params;
    $_POST = [];
    $_SERVER['REQUEST_METHOD'] = 'GET';
    
    echo "Payment parameters:\n";
    foreach ($query_params as $key => $value) {
        echo "  $key: $value\n";
    }
    echo "\n";
    
    // Process payment
    echo "Processing payment...\n";
    try {
        // Change to payment directory context
        chdir('payment');
        ob_start();
        include 'process_payment.php';
        $payment_output = ob_get_clean();
        chdir('..');
        
        echo "Payment processing completed.\n";
        if (strpos($payment_output, 'Location:') !== false) {
            // Extract redirect URL from headers
            preg_match('/Location: (.+)/', $payment_output, $matches);
            if (isset($matches[1])) {
                echo "✅ Would redirect to PayMongo: " . trim($matches[1]) . "\n";
            }
        } else {
            echo "Payment output:\n$payment_output\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Payment error: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "❌ Step 1 failed: Checkout error\n";
    if ($checkout_response) {
        echo "Error: " . ($checkout_response['message'] ?? 'Unknown error') . "\n";
    }
}

echo "\n=== Test Complete ===\n";
?>