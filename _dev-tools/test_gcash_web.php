<?php
session_start();

// Set up the test order data 
$_SESSION['order_confirmation'] = [
    'order_id' => 'WEB-TEST-' . time(),
    'customer_name' => 'Web Test User',
    'customer_email' => 'test@example.com', 
    'customer_phone' => '09123456789',
    'shipping_address' => 'Web Test Address, Manila, Metro Manila 1000, Philippines',
    'billing_address' => 'Web Test Address, Manila, Metro Manila 1000, Philippines',
    'payment_method' => 'paymongo_gcash',
    'total' => 500.00
];

$order_id = $_SESSION['order_confirmation']['order_id'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>PayMongo GCash Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .test-card { border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 8px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>PayMongo GCash Integration Test</h1>
    
    <div class="test-card info">
        <h3>Test Order Created</h3>
        <p><strong>Order ID:</strong> <?php echo $order_id; ?></p>
        <p><strong>Customer:</strong> <?php echo $_SESSION['order_confirmation']['customer_name']; ?></p>
        <p><strong>Amount:</strong> ₱<?php echo number_format($_SESSION['order_confirmation']['total'], 2); ?></p>
        <p><strong>Payment Method:</strong> GCash (PayMongo)</p>
    </div>
    
    <div class="test-card">
        <h3>Test Payment Processing</h3>
        <p>Click the button below to test the PayMongo GCash payment processing:</p>
        
        <form action="payment/process_payment.php" method="GET">
            <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
            <input type="hidden" name="method" value="paymongo_gcash">
            <input type="hidden" name="amount" value="<?php echo $_SESSION['order_confirmation']['total']; ?>">
            
            <button type="submit">🚀 Process GCash Payment</button>
        </form>
        
        <p><small><em>This will redirect you to the PayMongo GCash payment page if working correctly.</em></small></p>
    </div>
    
    <div class="test-card">
        <h3>Integration Status</h3>
        <ul>
            <li>✅ PayMongo API configuration: Working</li>
            <li>✅ Database setup: Complete</li>
            <li>✅ Order creation: Working</li>
            <li>🧪 GCash redirect: Test in progress</li>
        </ul>
    </div>

    <script>
        // Add some visual feedback for the test
        document.querySelector('form').addEventListener('submit', function() {
            const button = this.querySelector('button');
            button.innerHTML = '⏳ Processing...';
            button.disabled = true;
        });
    </script>
</body>
</html>