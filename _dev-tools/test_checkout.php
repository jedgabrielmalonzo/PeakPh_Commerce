<?php
session_start();

// Simulate a cart with demo products
$_SESSION['cart'] = [
    [
        'id' => 'demo1',
        'name' => 'Test Product',
        'price' => 500.00,
        'quantity' => 1,
        'image' => 'test.jpg',
        'is_database' => false
    ]
];

echo "=== Checkout Test ===\n";
echo "Cart contents:\n";
print_r($_SESSION['cart']);

// Simulate checkout form data
$_POST = [
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john.doe@example.com',
    'phone' => '09123456789',
    'shipping_address' => '123 Test Street',
    'shipping_city' => 'Manila',
    'shipping_province' => 'Metro Manila',
    'shipping_postal_code' => '1000',
    'payment_method' => 'paymongo_gcash',
    'same_as_shipping' => 'on'
];

echo "\nSimulating checkout with GCash payment...\n";

// Set up output buffering to capture the JSON response
ob_start();

// Include the checkout processor
$_SERVER['REQUEST_METHOD'] = 'POST';
include 'process_checkout.php';

$output = ob_get_clean();
echo "\nCheckout response:\n";
echo $output . "\n";

$response = json_decode($output, true);
if ($response && $response['success'] && isset($response['redirect_url'])) {
    echo "\n✅ Checkout successful! Would redirect to: " . $response['redirect_url'] . "\n";
    
    // Test the payment processing
    echo "\nTesting payment processing...\n";
    
    // Extract parameters from redirect URL
    $url_parts = parse_url($response['redirect_url']);
    parse_str($url_parts['query'] ?? '', $params);
    
    $_GET = $params;
    $_SERVER['REQUEST_METHOD'] = 'GET';
    
    echo "Payment parameters:\n";
    print_r($params);
    
} else {
    echo "\n❌ Checkout failed\n";
    if ($response) {
        echo "Error: " . ($response['message'] ?? 'Unknown error') . "\n";
    }
}
?>