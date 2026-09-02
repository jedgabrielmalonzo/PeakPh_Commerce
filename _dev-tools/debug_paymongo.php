<?php
/**
 * PayMongo API Debug and Test Page
 * PeakPH Commerce - PayMongo Integration
 */

require_once 'config/paymongo.php';
require_once 'includes/PayMongoHelper.php';
require_once 'includes/db.php';

// Initialize PayMongo helper
$paymongo = new PayMongoHelper();

$test_results = [];
$api_status = 'unknown';

// Test API Connection
try {
    // Test API by creating a test payment method
    $test_payment = [
        'type' => 'gcash',
        'billing' => [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '09123456789'
        ]
    ];
    
    $result = $paymongo->createPaymentMethod($test_payment);
    
    if ($result && isset($result['data'])) {
        $api_status = 'connected';
        $test_results['api_test'] = [
            'status' => 'success',
            'message' => 'PayMongo API connection successful',
            'data' => $result['data']
        ];
    } else {
        $api_status = 'error';
        $test_results['api_test'] = [
            'status' => 'error',
            'message' => 'Failed to connect to PayMongo API',
            'data' => $result
        ];
    }
} catch (Exception $e) {
    $api_status = 'error';
    $test_results['api_test'] = [
        'status' => 'error',
        'message' => 'API Error: ' . $e->getMessage(),
        'data' => null
    ];
}

// Test Database Connection
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM inventory LIMIT 1");
    $inventory_count = $stmt->fetch()['count'];
    
    $test_results['database_test'] = [
        'status' => 'success',
        'message' => "Database connected. Found {$inventory_count} products in inventory.",
        'data' => ['inventory_count' => $inventory_count]
    ];
} catch (Exception $e) {
    $test_results['database_test'] = [
        'status' => 'error',
        'message' => 'Database Error: ' . $e->getMessage(),
        'data' => null
    ];
}

// Check Required Tables
$required_tables = ['inventory', 'orders', 'payments', 'payment_logs', 'paymongo_webhooks'];
$table_status = [];

foreach ($required_tables as $table) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
        $exists = $stmt->rowCount() > 0;
        $table_status[$table] = $exists ? 'exists' : 'missing';
    } catch (Exception $e) {
        $table_status[$table] = 'error';
    }
}

$test_results['table_check'] = [
    'status' => 'info',
    'message' => 'Database table status check',
    'data' => $table_status
];

// Check Configuration
$config_status = [
    'public_key' => !empty(PAYMONGO_PUBLIC_KEY) ? 'configured' : 'missing',
    'secret_key' => !empty(PAYMONGO_SECRET_KEY) ? 'configured' : 'missing',
    'test_mode' => PAYMONGO_TEST_MODE ? 'enabled' : 'disabled',
    'webhook_url' => !empty(PAYMONGO_WEBHOOK_URL) ? 'configured' : 'missing'
];

$test_results['config_check'] = [
    'status' => 'info',
    'message' => 'Configuration status',
    'data' => $config_status
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayMongo API Debug - PeakPH Commerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        .debug-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .status-success { color: #28a745; }
        .status-error { color: #dc3545; }
        .status-warning { color: #ffc107; }
        .status-info { color: #17a2b8; }
        .code-block {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            padding: 15px;
            margin: 10px 0;
        }
        .api-indicator {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 10px;
        }
        .api-connected { background-color: #28a745; }
        .api-error { background-color: #dc3545; }
        .api-unknown { background-color: #6c757d; }
    </style>
</head>
<body class="bg-light">
    <div class="container my-5">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h1 class="display-5 fw-bold">PayMongo API Debug</h1>
                <p class="lead text-muted">Test and verify PayMongo integration status</p>
                <div class="d-inline-flex align-items-center">
                    <span class="api-indicator api-<?php echo $api_status; ?>"></span>
                    <span class="fw-semibold">
                        API Status: 
                        <?php 
                        echo match($api_status) {
                            'connected' => '<span class="status-success">Connected</span>',
                            'error' => '<span class="status-error">Error</span>',
                            default => '<span class="status-warning">Unknown</span>'
                        };
                        ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Test Results -->
        <div class="row g-4">
            <?php foreach ($test_results as $test_name => $result): ?>
            <div class="col-md-6">
                <div class="card debug-card h-100">
                    <div class="card-header bg-<?php 
                        echo match($result['status']) {
                            'success' => 'success',
                            'error' => 'danger',
                            'warning' => 'warning',
                            default => 'info'
                        };
                    ?> text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-<?php 
                                echo match($result['status']) {
                                    'success' => 'check-circle',
                                    'error' => 'x-circle',
                                    'warning' => 'exclamation-triangle',
                                    default => 'info-circle'
                                };
                            ?>"></i>
                            <?php echo ucfirst(str_replace('_', ' ', $test_name)); ?>
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-3"><?php echo htmlspecialchars($result['message']); ?></p>
                        
                        <?php if ($result['data']): ?>
                        <div class="code-block">
                            <small class="text-muted d-block mb-2">Response Data:</small>
                            <pre class="mb-0 small"><?php echo htmlspecialchars(json_encode($result['data'], JSON_PRETTY_PRINT)); ?></pre>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Configuration Details -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card debug-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-gear"></i> Current Configuration</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>PayMongo Settings</h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Public Key</span>
                                        <span class="status-<?php echo $config_status['public_key'] === 'configured' ? 'success' : 'error'; ?>">
                                            <?php echo $config_status['public_key'] === 'configured' ? 'Configured' : 'Missing'; ?>
                                        </span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Secret Key</span>
                                        <span class="status-<?php echo $config_status['secret_key'] === 'configured' ? 'success' : 'error'; ?>">
                                            <?php echo $config_status['secret_key'] === 'configured' ? 'Configured' : 'Missing'; ?>
                                        </span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Test Mode</span>
                                        <span class="status-info"><?php echo $config_status['test_mode'] === 'enabled' ? 'Enabled' : 'Disabled'; ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Webhook URL</span>
                                        <span class="status-<?php echo $config_status['webhook_url'] === 'configured' ? 'success' : 'error'; ?>">
                                            <?php echo $config_status['webhook_url'] === 'configured' ? 'Configured' : 'Missing'; ?>
                                        </span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Database Tables</h6>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($table_status as $table => $status): ?>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span><?php echo $table; ?></span>
                                        <span class="status-<?php echo $status === 'exists' ? 'success' : 'error'; ?>">
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Test Actions -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <h4>Test Actions</h4>
                <div class="btn-group mt-3" role="group">
                    <a href="test_integration.html" class="btn btn-primary">
                        <i class="bi bi-arrow-left"></i> Back to Integration Test
                    </a>
                    <a href="test_store.php" class="btn btn-success">
                        <i class="bi bi-shop"></i> Test Store
                    </a>
                    <a href="checkout.php" class="btn btn-warning">
                        <i class="bi bi-credit-card"></i> Test Checkout
                    </a>
                    <a href="admin/orders.php" class="btn btn-info">
                        <i class="bi bi-list-check"></i> Admin Orders
                    </a>
                </div>
            </div>
        </div>

        <!-- Refresh Button -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <button onclick="location.reload()" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Refresh Tests
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>