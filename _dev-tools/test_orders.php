<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Database Test - PeakPH Commerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container my-5">
        <h1 class="text-center mb-4">Order Database Verification</h1>
        
        <?php
        require_once 'includes/db.php';
        
        if (isDatabaseConnected()) {
            echo '<div class="alert alert-success"><i class="bi bi-check-circle"></i> Database Connected</div>';
            
            try {
                // Check if orders table exists and get recent orders
                $result = $conn->query("SELECT COUNT(*) as count FROM orders");
                $order_count = $result->fetch_assoc()['count'];
                
                echo "<div class='card'>";
                echo "<div class='card-header bg-primary text-white'>";
                echo "<h5 class='mb-0'><i class='bi bi-list-check'></i> Orders Status</h5>";
                echo "</div>";
                echo "<div class='card-body'>";
                echo "<p><strong>Total Orders in Database:</strong> {$order_count}</p>";
                
                if ($order_count > 0) {
                    // Get recent orders
                    $recent_orders = $conn->query("SELECT order_id, customer_name, customer_email, total_amount, payment_method, status, payment_status, order_date FROM orders ORDER BY order_date DESC LIMIT 10");
                    
                    echo "<h6>Recent Orders:</h6>";
                    echo "<div class='table-responsive'>";
                    echo "<table class='table table-striped table-sm'>";
                    echo "<thead><tr><th>Order ID</th><th>Customer</th><th>Email</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th></tr></thead>";
                    echo "<tbody>";
                    
                    while ($order = $recent_orders->fetch_assoc()) {
                        $status_class = match($order['payment_status']) {
                            'Paid' => 'success',
                            'Pending' => 'warning',
                            'Failed' => 'danger',
                            default => 'secondary'
                        };
                        
                        echo "<tr>";
                        echo "<td><strong>" . htmlspecialchars($order['order_id']) . "</strong></td>";
                        echo "<td>" . htmlspecialchars($order['customer_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($order['customer_email']) . "</td>";
                        echo "<td>₱" . number_format($order['total_amount'], 2) . "</td>";
                        echo "<td>" . htmlspecialchars($order['payment_method']) . "</td>";
                        echo "<td><span class='badge bg-{$status_class}'>" . htmlspecialchars($order['payment_status']) . "</span></td>";
                        echo "<td>" . date('M j, Y g:i A', strtotime($order['order_date'])) . "</td>";
                        echo "</tr>";
                    }
                    
                    echo "</tbody></table>";
                    echo "</div>";
                } else {
                    echo "<p class='text-muted'>No orders found in database.</p>";
                }
                
                echo "</div></div>";
                
                // Check payments table
                $payment_result = $conn->query("SELECT COUNT(*) as count FROM payments");
                $payment_count = $payment_result->fetch_assoc()['count'];
                
                echo "<div class='card mt-3'>";
                echo "<div class='card-header bg-info text-white'>";
                echo "<h5 class='mb-0'><i class='bi bi-credit-card'></i> Payments Status</h5>";
                echo "</div>";
                echo "<div class='card-body'>";
                echo "<p><strong>Total Payments in Database:</strong> {$payment_count}</p>";
                
                if ($payment_count > 0) {
                    $recent_payments = $conn->query("SELECT p.*, o.order_id FROM payments p JOIN orders o ON p.order_id = o.id ORDER BY p.created_at DESC LIMIT 5");
                    
                    echo "<h6>Recent Payments:</h6>";
                    echo "<div class='table-responsive'>";
                    echo "<table class='table table-striped table-sm'>";
                    echo "<thead><tr><th>Order ID</th><th>Method</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>";
                    echo "<tbody>";
                    
                    while ($payment = $recent_payments->fetch_assoc()) {
                        $status_class = match($payment['status']) {
                            'Completed' => 'success',
                            'Pending' => 'warning',
                            'Failed' => 'danger',
                            default => 'secondary'
                        };
                        
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($payment['order_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($payment['payment_method']) . "</td>";
                        echo "<td>₱" . number_format($payment['amount'], 2) . "</td>";
                        echo "<td><span class='badge bg-{$status_class}'>" . htmlspecialchars($payment['status']) . "</span></td>";
                        echo "<td>" . date('M j, Y g:i A', strtotime($payment['created_at'])) . "</td>";
                        echo "</tr>";
                    }
                    
                    echo "</tbody></table>";
                    echo "</div>";
                }
                
                echo "</div></div>";
                
            } catch (Exception $e) {
                echo '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Database Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            
        } else {
            echo '<div class="alert alert-danger"><i class="bi bi-x-circle"></i> Database Connection Failed</div>';
            echo '<p>Please ensure:</p>';
            echo '<ul>';
            echo '<li>XAMPP is running</li>';
            echo '<li>MySQL service is started</li>';
            echo '<li>Database "peakph_db" exists</li>';
            echo '<li>Database tables are created</li>';
            echo '</ul>';
        }
        ?>
        
        <div class="row mt-4">
            <div class="col-12 text-center">
                <h5>Test Actions</h5>
                <div class="btn-group" role="group">
                    <a href="test_integration.html" class="btn btn-primary">
                        <i class="bi bi-arrow-left"></i> Back to Testing
                    </a>
                    <a href="test_store.php" class="btn btn-success">
                        <i class="bi bi-shop"></i> Test Store
                    </a>
                    <a href="admin/orders.php" class="btn btn-info">
                        <i class="bi bi-list-check"></i> Admin Orders
                    </a>
                    <button onclick="location.reload()" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>