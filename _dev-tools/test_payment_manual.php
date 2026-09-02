<?php
// Test the flow by manually setting the parameters based on the successful checkout
session_start();

echo "=== Manual Payment Test ===\n";

// Set up the order confirmation data (as it would be from a successful checkout)
$_SESSION['order_confirmation'] = [
    'order_id' => 'ORD-2025-88600',
    'customer_name' => 'Jane Doe',
    'customer_email' => 'jane.doe@example.com', 
    'customer_phone' => '09123456789',
    'shipping_address' => '456 Main St, Quezon City, Metro Manila 1100, Philippines',
    'billing_address' => '456 Main St, Quezon City, Metro Manila 1100, Philippines',
    'payment_method' => 'paymongo_gcash',
    'items' => [
        [
            'id' => 'test1',
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
    'order_notes' => ''
];

// Simulate the GET parameters from the redirect URL
$_GET = [
    'order_id' => 'ORD-2025-88600',
    'method' => 'paymongo_gcash',
    'amount' => '610'
];

$_POST = [];
$_SERVER['REQUEST_METHOD'] = 'GET';

echo "Testing payment with:\n";
echo "Order ID: " . $_GET['order_id'] . "\n";
echo "Method: " . $_GET['method'] . "\n"; 
echo "Amount: ₱" . $_GET['amount'] . "\n\n";

try {
    // Start output buffering
    ob_start();
    
    // Include the payment processor
    include 'payment/process_payment.php';
    
    // Get the output
    $output = ob_get_clean();
    
    echo "Payment processing output:\n";
    echo $output . "\n";
    
    // Check if there was a redirect header in the output
    if (strpos($output, 'Location:') !== false) {
        preg_match('/Location: (.+)/', $output, $matches);
        if (isset($matches[1])) {
            $redirect_url = trim($matches[1]);
            echo "✅ SUCCESS: Would redirect to PayMongo GCash payment:\n";
            echo "$redirect_url\n\n";
            
            // Check if it's a valid PayMongo URL
            if (strpos($redirect_url, 'paymongo') !== false || strpos($redirect_url, 'pm_') !== false) {
                echo "✅ This is a valid PayMongo checkout URL!\n";
                echo "The integration is working correctly.\n";
                echo "\nIn a real scenario, the user would be redirected to this URL to complete payment with GCash.\n";
            } else {
                echo "⚠️  URL doesn't appear to be a PayMongo URL - might be a local redirect\n";
            }
        }
    } else {
        echo "❌ No redirect found in output\n";
    }
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
?>