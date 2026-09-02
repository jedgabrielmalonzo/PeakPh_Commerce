<?php
require_once('auth_helper.php');

// Don't require auth for this debug page, just start session
if (session_status() === PHP_SESSION_NONE) {
    session_name('PEAKPH_ADMIN_SESSION');
    session_start();
}

// Get debug info
$debugInfo = debugSessionInfo();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Debug - PeakPH Admin</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .debug-box { background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
    </style>
</head>
<body>
    <h1>Admin Session Debug Information</h1>
    
    <div class="debug-box">
        <h3>Session Status</h3>
        <p><strong>Session Status:</strong> <?= session_status() === PHP_SESSION_ACTIVE ? '<span class="success">ACTIVE</span>' : '<span class="error">NOT ACTIVE</span>' ?></p>
        <p><strong>Session Name:</strong> <?= session_name() ?></p>
        <p><strong>Session ID:</strong> <?= session_id() ?></p>
    </div>
    
    <div class="debug-box">
        <h3>Authentication Status</h3>
        <?php foreach ($debugInfo as $key => $value): ?>
            <p><strong><?= ucfirst(str_replace('_', ' ', $key)) ?>:</strong> <?= htmlspecialchars($value) ?></p>
        <?php endforeach; ?>
    </div>
    
    <div class="debug-box">
        <h3>Session Data</h3>
        <pre><?= htmlspecialchars(print_r($_SESSION, true)) ?></pre>
    </div>
    
    <div class="debug-box">
        <h3>Cookie Information</h3>
        <p><strong>Cookie Params:</strong></p>
        <pre><?= htmlspecialchars(print_r(session_get_cookie_params(), true)) ?></pre>
        
        <p><strong>Admin Remember Cookie:</strong> <?= isset($_COOKIE['admin_remember']) ? 'SET' : 'NOT SET' ?></p>
    </div>
    
    <div class="debug-box">
        <h3>Server Information</h3>
        <p><strong>Current URL:</strong> <?= $_SERVER['REQUEST_URI'] ?></p>
        <p><strong>Document Root:</strong> <?= $_SERVER['DOCUMENT_ROOT'] ?></p>
        <p><strong>Script Name:</strong> <?= $_SERVER['SCRIPT_NAME'] ?></p>
    </div>
    
    <div class="debug-box">
        <h3>Test Navigation</h3>
        <p>Try navigating to these pages to test session persistence:</p>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="orders.php">Orders</a></li>
            <li><a href="inventory/inventory.php">Inventory</a></li>
            <li><a href="content/carousel.php">Carousel</a></li>
            <li><a href="users/users.php">Users</a></li>
        </ul>
    </div>
    
    <p><a href="mini-view.php">← Back to Mini View</a></p>
</body>
</html>