<?php
// PayMongo Configuration
// Load environment config for dynamic URLs
require_once __DIR__ . '/../includes/environment.php';

// Detect current domain
$current_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
$protocol = $is_https ? 'https' : 'http';

// Build base URL dynamically
$app_base_url = $protocol . '://' . $current_host;

return [
    'secret_key' => defined('PAYMONGO_SECRET_KEY') ? PAYMONGO_SECRET_KEY : 'sk_test_YOUR_PAYMONGO_SECRET_KEY',
    'public_key' => defined('PAYMONGO_PUBLIC_KEY') ? PAYMONGO_PUBLIC_KEY : 'pk_test_YOUR_PAYMONGO_PUBLIC_KEY',
    'base_url' => 'https://api.paymongo.com/v1',
    'webhook_signature_key' => defined('WEBHOOK_SECRET') ? WEBHOOK_SECRET : 'whsec_your_webhook_secret',
    
    // Fee Configuration
    'gcash_fee_rate' => 0.035, // 3.5%
    'card_fee_rate' => 0.035, // 3.5%
    'fixed_fee' => 15, // ₱15 fixed fee
    
    // Environment
    'test_mode' => DEVELOPMENT_MODE, // Auto-detect based on environment
    
    // Dynamic Redirect URLs - Works on both localhost and production
    'success_url' => $app_base_url . '/PeakPH_Commerce/payment/success.php',
    'failed_url' => $app_base_url . '/PeakPH_Commerce/payment/failed.php',
    'webhook_url' => $app_base_url . '/PeakPH_Commerce/webhooks/paymongo.php',
];
?>