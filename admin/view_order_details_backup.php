<?php
require_once('auth_helper.php');
requireAdminAuth();
require_once('../includes/db.php');

$order_id = intval($_GET['id'] ?? 0);
$is_page = isset($_GET['page']);

if (!$order_id) {
    if ($is_page) {
        header('Location: orders.php');
        exit;
    } else {
        echo '<div class="alert alert-danger">Invalid order ID</div>';
        exit;
    }
}

// Get comprehensive order data
$order_query = "
    SELECT o.*, 
           p.id as payment_id,
           p.payment_method, 
           p.amount as payment_amount, 
           p.gateway_fee, 
           p.status as payment_status, 
           p.paymongo_payment_intent_id,
           p.paymongo_source_id,
           p.transaction_reference,
           p.paid_at,
           p.created_at as payment_created
    FROM orders o 
    LEFT JOIN payments p ON o.id = p.order_id 
    WHERE o.id = ?
";

$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    if ($is_page) {
        header('Location: orders.php?error=order_not_found');
        exit;
    } else {
        echo '<div class="alert alert-danger">Order not found</div>';
        exit;
    }
}

// Get payment logs
$logs_query = "
    SELECT * FROM payment_logs 
    WHERE order_id = ? 
    ORDER BY created_at DESC
";
$stmt = $conn->prepare($logs_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$payment_logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get order items (if exists)
$items_query = "SELECT * FROM order_items WHERE order_id = ?";
$stmt = $conn->prepare($items_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if ($is_page):
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - PeakPH Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../Css/admin.css">
    <style>
        .info-card { border-left: 4px solid #007bff; }
        .payment-card { border-left: 4px solid #28a745; }
        .logs-card { border-left: 4px solid #ffc107; }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 500; }
        .paymongo-badge { background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 4px 8px; border-radius: 12px; font-size: 0.75rem; }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-receipt"></i> Order Details - #<?= htmlspecialchars($order['order_id'] ?? $order['id']) ?></h2>
                    <a href="orders.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Orders
                    </a>
                </div>
            </div>
        </div>
<?php else: ?>
<!-- Enhanced Modal Design -->
<div class="order-details-modal">
    <style>
        .order-details-modal {
            font-family: 'Inter', 'Segoe UI', sans-serif;
        }
        .modal-section {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            margin-bottom: 1.5rem;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .section-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.25rem 1.5rem;
            margin: 0;
            font-weight: 600;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .section-header i {
            font-size: 1.3rem;
        }
        .section-header.payment {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
        }
        .section-header.shipping {
            background: linear-gradient(135deg, #3a7bd5 0%, #3a6073 100%);
        }
        .section-header.items {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
        }
        .section-content {
            padding: 1.75rem;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
            margin-bottom: 0;
        }
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }
        .info-label {
            font-size: 0.95rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .info-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: #111827;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.95rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .status-paid {
            background: #d1fae5;
            color: #065f46;
        }
        .status-failed {
            background: #fee2e2;
            color: #991b1b;
        }
        .status-processing {
            background: #dbeafe;
            color: #1e40af;
        }
        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }
        .payment-method-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.7rem 1.3rem;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
        }
        .payment-method-badge i {
            font-size: 1.2rem;
        }
        .amount-display {
            font-size: 2rem;
            font-weight: 800;
            color: #059669;
            margin-top: 0.5rem;
        }
        .shipping-address {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1.25rem;
            line-height: 1.7;
            font-size: 1.05rem;
            color: #374151;
        }
        .order-items-table {
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            font-size: 1rem;
        }
        .order-items-table th {
            background: #f9fafb;
            font-weight: 700;
            color: #374151;
            padding: 1.25rem;
            border-bottom: 2px solid #e5e7eb;
            font-size: 1.05rem;
        }
        .order-items-table td {
            padding: 1.25rem;
            border-bottom: 1px solid #f3f4f6;
            font-size: 1rem;
        }
        .order-items-table tr:last-child td {
            border-bottom: none;
        }
        .payment-logs {
            max-height: 300px;
            overflow-y: auto;
            background: #f9fafb;
            border-radius: 10px;
            padding: 1.25rem;
        }
        .log-entry {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            border-left: 5px solid #e5e7eb;
            font-size: 0.95rem;
        }
        .log-entry.success {
            border-left-color: #10b981;
        }
        .log-entry.error {
            border-left-color: #ef4444;
        }
        .log-entry.pending {
            border-left-color: #f59e0b;
        }
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <!-- Order Information and Payment Information in Row -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
            gap: 0.5rem;
        }
        .amount-display {
            font-size: 1.3rem;
            font-weight: 700;
            color: #059669;
        }
        .shipping-address {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 0.75rem;
            line-height: 1.4;
            font-size: 0.9rem;
        }
        .order-items-table {
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .order-items-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .order-items-table td {
            padding: 1rem;
            border-bottom: 1px solid #f3f4f6;
        }
        .order-items-table tr:last-child td {
            border-bottom: none;
        }
        .payment-logs {
            max-height: 200px;
            overflow-y: auto;
            background: #f9fafb;
            border-radius: 8px;
            padding: 1rem;
        }
        .log-entry {
            background: white;
            border-radius: 6px;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            border-left: 4px solid #e5e7eb;
            font-size: 0.875rem;
        }
        .log-entry.success {
            border-left-color: #10b981;
        }
        .log-entry.error {
            border-left-color: #ef4444;
        }
        .log-entry.pending {
            border-left-color: #f59e0b;
        }
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
<?php endif; ?>

    <!-- Order Information and Payment Information in Row -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
        <!-- Order Details Section -->
        <div class="modal-section">
            <h5 class="section-header">
                <i class="bi bi-receipt"></i>
                Order Details
            </h5>
            <div class="section-content">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Order ID</span>
                        <span class="info-value">#<?= htmlspecialchars($order['order_id'] ?? $order['id']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Customer Name</span>
                        <span class="info-value"><?= htmlspecialchars($order['customer_name']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email Address</span>
                        <span class="info-value"><?= htmlspecialchars($order['customer_email']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Phone Number</span>
                        <span class="info-value"><?= htmlspecialchars($order['customer_phone'] ?? 'Not provided') ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Order Date</span>
                        <span class="info-value"><?= date('M j, Y g:i A', strtotime($order['order_date'])) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Order Status</span>
                        <span class="status-badge status-<?= strtolower($order['status']) ?>">
                            <i class="bi bi-<?= strtolower($order['status']) === 'completed' ? 'check-circle' : (strtolower($order['status']) === 'pending' ? 'clock' : 'info-circle') ?>"></i>
                            <?= htmlspecialchars($order['status']) ?>
                        </span>
                    </div>
                </div>
                <div style="margin-top: 1rem; text-align: center;">
                    <div class="info-label">Total Amount</div>
                    <div class="amount-display">₱<?= number_format($order['total_amount'], 2) ?></div>
                </div>
            </div>
        </div>

        <!-- Payment Information Section -->
        <div class="modal-section">
            <h5 class="section-header payment">
                <i class="bi bi-credit-card"></i>
                Payment Information
            </h5>
            <div class="section-content">
                <?php if ($order['payment_id']): ?>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Payment Method</span>
                            <div>
                                <?php if (strpos($order['payment_method'], 'paymongo') !== false): ?>
                                    <span class="payment-method-badge">
                                        <i class="bi bi-phone"></i>
                                        PayMongo <?= $order['payment_method'] === 'paymongo_gcash' ? 'GCash' : 'Card' ?>
                                    </span>
                                <?php else: ?>
                                    <span class="payment-method-badge">
                                        <i class="bi bi-cash-coin"></i>
                                        <?= strtoupper($order['payment_method']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Payment Status</span>
                            <span class="status-badge status-<?= strtolower($order['payment_status']) ?>">
                                <i class="bi bi-<?= strtolower($order['payment_status']) === 'paid' ? 'check-circle' : (strtolower($order['payment_status']) === 'failed' ? 'x-circle' : 'clock') ?>"></i>
                                <?= htmlspecialchars($order['payment_status']) ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Payment Amount</span>
                            <span class="info-value">₱<?= number_format($order['payment_amount'], 2) ?></span>
                        </div>
                        <?php if ($order['gateway_fee'] > 0): ?>
                        <div class="info-item">
                            <span class="info-label">Gateway Fee</span>
                            <span class="info-value">₱<?= number_format($order['gateway_fee'], 2) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($order['paid_at']): ?>
                        <div class="info-item">
                            <span class="info-label">Paid At</span>
                            <span class="info-value"><?= date('M j, Y g:i A', strtotime($order['paid_at'])) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($order['paymongo_payment_intent_id']): ?>
                        <div class="info-item">
                            <span class="info-label">PayMongo Intent ID</span>
                            <span class="info-value" style="font-family: monospace; font-size: 0.875rem;"><?= htmlspecialchars(substr($order['paymongo_payment_intent_id'], 0, 20)) ?>...</span>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted">
                        <i class="bi bi-exclamation-circle" style="font-size: 2rem;"></i>
                        <p class="mt-2">No payment information available</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Shipping and Order Items in Row -->
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; margin-bottom: 2rem;">
        <!-- Shipping Information Section -->
        <div class="modal-section">
            <h5 class="section-header shipping">
                <i class="bi bi-geo-alt"></i>
                Shipping Information
            </h5>
            <div class="section-content">
                <div class="info-item">
                    <span class="info-label">Shipping Address</span>
                    <div class="shipping-address">
                        <?= nl2br(htmlspecialchars($order['shipping_address'])) ?>
                    </div>
                </div>
                <?php if ($order['billing_address'] && $order['billing_address'] !== $order['shipping_address']): ?>
                <div class="info-item" style="margin-top: 1rem;">
                    <span class="info-label">Billing Address</span>
                    <div class="shipping-address">
                        <?= nl2br(htmlspecialchars($order['billing_address'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Order Items Section -->
        <?php if (!empty($order_items)): ?>
        <div class="modal-section">
            <h5 class="section-header items">
                <i class="bi bi-list-ul"></i>
                Order Items (<?= count($order_items) ?> item<?= count($order_items) !== 1 ? 's' : '' ?>)
            </h5>
            <div class="section-content">
                <table class="table order-items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th style="text-align: center;">Qty</th>
                            <th style="text-align: right;">Price</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600;"><?= htmlspecialchars($item['product_name']) ?></div>
                                <?php if ($item['product_id']): ?>
                                    <small style="color: #6b7280;">ID: <?= $item['product_id'] ?></small>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center; font-weight: 600;"><?= $item['quantity'] ?></td>
                            <td style="text-align: right;">₱<?= number_format($item['price'], 2) ?></td>
                            <td style="text-align: right; font-weight: 700; color: #059669;">₱<?= number_format($item['total'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Payment Transaction Logs Section -->
    <?php if (!empty($payment_logs)): ?>
    <div class="modal-section">
        <h5 class="section-header" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
            <i class="bi bi-journal-text"></i>
            Transaction Logs (<?= count($payment_logs) ?> record<?= count($payment_logs) !== 1 ? 's' : '' ?>)
        </h5>
        <div class="section-content">
            <div class="payment-logs">
                <?php foreach ($payment_logs as $log): ?>
                <div class="log-entry <?= $log['status'] === 'completed' ? 'success' : ($log['status'] === 'failed' ? 'error' : 'pending') ?>">
                    <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 0.5rem;">
                        <div style="flex: 1;">
                            <strong style="text-transform: capitalize;"><?= str_replace('_', ' ', htmlspecialchars($log['action'])) ?></strong>
                            <span style="font-size: 0.75rem; color: #6b7280; margin-left: 0.5rem;">
                                <?= date('M j, Y g:i A', strtotime($log['created_at'])) ?>
                            </span>
                        </div>
                        <span class="status-badge status-<?= strtolower($log['status']) ?>" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                            <?= htmlspecialchars($log['status']) ?>
                        </span>
                    </div>
                    <?php if ($log['error_message']): ?>
                        <div style="color: #dc2626; font-size: 0.875rem;">
                            <i class="bi bi-exclamation-triangle"></i>
                            <?= htmlspecialchars($log['error_message']) ?>
                        </div>
                    <?php elseif ($log['gateway_response']): ?>
                        <div style="margin-top: 0.5rem;">
                            <button class="btn btn-sm btn-outline-primary" onclick="toggleDetails(this, '<?= base64_encode($log['gateway_response']) ?>')">
                                <i class="bi bi-eye"></i> View Response
                            </button>
                        </div>
                    <?php endif; ?>
                    <?php if ($log['ip_address'] || $log['user_agent']): ?>
                        <div style="margin-top: 0.5rem; font-size: 0.75rem; color: #6b7280;">
                            <?php if ($log['ip_address']): ?>
                                <i class="bi bi-globe"></i> <?= htmlspecialchars($log['ip_address']) ?>
                            <?php endif; ?>
                            <?php if ($log['user_agent']): ?>
                                <i class="bi bi-device-ssd"></i> <?= htmlspecialchars(substr($log['user_agent'], 0, 50)) ?>...
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

<?php if ($is_page): ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleDetails(button, data) {
            const decoded = atob(data);
            const formatted = JSON.stringify(JSON.parse(decoded), null, 2);
            
            if (button.nextElementSibling && button.nextElementSibling.classList.contains('response-details')) {
                button.nextElementSibling.remove();
                button.innerHTML = '<i class="bi bi-eye"></i> View';
            } else {
                const pre = document.createElement('pre');
                pre.className = 'response-details bg-light p-2 mt-2 small';
                pre.style.fontSize = '11px';
                pre.textContent = formatted;
                button.parentNode.appendChild(pre);
                button.innerHTML = '<i class="bi bi-eye-slash"></i> Hide';
            }
        }
    </script>
</body>
</html>
<?php else: ?>
</div>
<script>
    function toggleDetails(button, data) {
        const decoded = atob(data);
        const formatted = JSON.stringify(JSON.parse(decoded), null, 2);
        
        if (button.nextElementSibling && button.nextElementSibling.classList.contains('response-details')) {
            button.nextElementSibling.remove();
            button.innerHTML = '<i class="bi bi-eye"></i> View';
        } else {
            const pre = document.createElement('pre');
            pre.className = 'response-details bg-light p-2 mt-2 small';
            pre.style.fontSize = '11px';
            pre.textContent = formatted;
            button.parentNode.appendChild(pre);
            button.innerHTML = '<i class="bi bi-eye-slash"></i> Hide';
        }
    }
</script>
<?php endif; ?>