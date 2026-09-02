<?php
// PayMongo Test Script
require_once('includes/PayMongoHelper.php');

echo "=== PayMongo Integration Test ===\n\n";

try {
    $paymongo = new PayMongoHelper();
    
    echo "✅ PayMongo helper loaded successfully\n";
    
    // Test fee calculation
    $test_amount = 1000;
    $gcash_fee = $paymongo->calculateGatewayFee($test_amount, 'gcash');
    $card_fee = $paymongo->calculateGatewayFee($test_amount, 'card');
    
    echo "\n📊 Fee Calculation Test:\n";
    echo "Amount: ₱" . number_format($test_amount, 2) . "\n";
    echo "GCash Fee: ₱" . number_format($gcash_fee, 2) . "\n";
    echo "Card Fee: ₱" . number_format($card_fee, 2) . "\n";
    echo "Total with GCash: ₱" . number_format($paymongo->getTotalWithFees($test_amount, 'gcash'), 2) . "\n";
    echo "Total with Card: ₱" . number_format($paymongo->getTotalWithFees($test_amount, 'card'), 2) . "\n";
    
    // Test API connectivity (this will fail if keys are not configured)
    echo "\n🔑 Testing API Keys...\n";
    
    $test_intent = $paymongo->createPaymentIntent(10, 'gcash', 'Test payment intent');
    
    if ($test_intent && isset($test_intent['data']['id'])) {
        echo "✅ PayMongo API connection successful!\n";
        echo "Test Payment Intent ID: " . $test_intent['data']['id'] . "\n";
    } else {
        echo "❌ PayMongo API test failed - check your API keys in config/paymongo.php\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\n📝 To fix this:\n";
    echo "1. Update your API keys in config/paymongo.php\n";
    echo "2. Make sure your secret key starts with 'sk_test_' for test mode\n";
    echo "3. Make sure your public key starts with 'pk_test_' for test mode\n";
}

echo "\n=== Test Complete ===\n";
?>