<?php
require_once('auth_helper.php');
requireAdminAuth();
require_once('../includes/db.php');

$order_id = intval($_GET['id'] ?? 0);

if (!$order_id) {
    echo '<div class="alert alert-danger">Invalid order ID</div>';
    exit;
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
    echo '<div class="alert alert-danger">Order not found</div>';
    exit;
}

// Get payment logs
$logs_query = "SELECT * FROM payment_logs WHERE order_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($logs_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$payment_logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get order items
$items_query = "SELECT * FROM order_items WHERE order_id = ?";
$stmt = $conn->prepare($items_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!-- Clean Order Details Modal -->
<style>
    .order-modal-container {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #333;
        width: 100%;
        max-width: 100%;
    }
    
    .order-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 2.5rem;
        margin-bottom: 2.5rem;
        width: 100%;
    }
    
    .order-section {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
        border: 1px solid #e8e8e8;
        width: 100%;
        min-width: 0;
    }
    
    .section-title {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem;
        font-size: 1.2rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .section-title i {
        font-size: 1.4rem;
    }
    
    .section-title.green {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    
    .section-title.blue {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    
    .section-title.pink {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }
    
    .section-body {
        padding: 2rem;
    }
    
    .info-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .info-field {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .field-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .field-value {
        font-size: 1.15rem;
        font-weight: 600;
        color: #222;
    }
    
    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1.2rem;
        border-radius: 50px;
        font-size: 1rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    
    .badge-pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .badge-paid {
        background: #d4edda;
        color: #155724;
    }
    
    .badge-failed {
        background: #f8d7da;
        color: #721c24;
    }
    
    .badge-processing {
        background: #d1ecf1;
        color: #0c5460;
    }
    
    .badge-completed {
        background: #d4edda;
        color: #155724;
    }
    
    .badge-cancelled {
        background: #f8d7da;
        color: #721c24;
    }
    
    .payment-method {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.8rem 1.5rem;
        border-radius: 50px;
        font-size: 1.1rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .total-amount {
        text-align: center;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 10px;
        margin-top: 1.5rem;
    }
    
    .total-label {
        font-size: 1rem;
        color: #888;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .total-value {
        font-size: 2.5rem;
        font-weight: 800;
        color: #059669;
    }
    
    .address-box {
        background: #f8f9fa;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 1.5rem;
        font-size: 1.1rem;
        line-height: 1.8;
        color: #495057;
    }
    
    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }
    
    .items-table thead {
        background: #f8f9fa;
    }
    
    .items-table th {
        padding: 1.2rem;
        text-align: left;
        font-weight: 700;
        color: #495057;
        border-bottom: 2px solid #dee2e6;
        font-size: 1rem;
    }
    
    .items-table td {
        padding: 1.2rem;
        border-bottom: 1px solid #f0f0f0;
        font-size: 1.05rem;
    }
    
    .items-table tr:last-child td {
        border-bottom: none;
    }
    
    .item-name {
        font-weight: 600;
        color: #333;
        margin-bottom: 0.25rem;
    }
    
    .item-id {
        font-size: 0.9rem;
        color: #999;
    }
    
    .logs-container {
        max-height: 300px;
        overflow-y: auto;
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1rem;
    }
    
    .log-item {
        background: white;
        border-left: 4px solid #dee2e6;
        border-radius: 6px;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .log-item:last-child {
        margin-bottom: 0;
    }
    
    .log-item.success {
        border-left-color: #28a745;
    }
    
    .log-item.error {
        border-left-color: #dc3545;
    }
    
    .log-item.pending {
        border-left-color: #ffc107;
    }
    
    .log-time {
        font-size: 0.9rem;
        color: #999;
        margin-bottom: 0.5rem;
    }
    
    .log-message {
        font-size: 1rem;
        color: #333;
        margin-bottom: 0.5rem;
    }
    
    .no-data {
        text-align: center;
        padding: 2rem;
        color: #999;
        font-size: 1.1rem;
    }
    
    .no-data i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }
    
    @media (max-width: 900px) {
        .order-grid {
            grid-template-columns: 1fr !important;
        }
        .info-row {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<div class="order-modal-container" style="width: 100%; max-width: 100%;">
    <!-- Top Section: Order Details & Payment Info -->
    <div class="order-grid">
        <!-- Order Details -->
        <div class="order-section">
            <div class="section-title">
                <i class="bi bi-receipt-cutoff"></i>
                <span>Order Information</span>
            </div>
            <div class="section-body">
                <div class="info-row">
                    <div class="info-field">
                        <div class="field-label">Order ID</div>
                        <div class="field-value">#<?= htmlspecialchars($order['order_id'] ?? $order['id']) ?></div>
                    </div>
                    <div class="info-field">
                        <div class="field-label">Order Status</div>
                        <div>
                            <span class="badge-status badge-<?= strtolower($order['status']) ?>">
                                <i class="bi bi-<?= $order['status'] == 'Pending' ? 'clock' : ($order['status'] == 'Completed' ? 'check-circle' : 'x-circle') ?>"></i>
                                <?= htmlspecialchars($order['status']) ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="info-row">
                    <div class="info-field">
                        <div class="field-label">Customer Name</div>
                        <div class="field-value"><?= htmlspecialchars($order['customer_name']) ?></div>
                    </div>
                    <div class="info-field">
                        <div class="field-label">Email Address</div>
                        <div class="field-value"><?= htmlspecialchars($order['customer_email']) ?></div>
                    </div>
                </div>
                
                <div class="info-row">
                    <div class="info-field">
                        <div class="field-label">Phone Number</div>
                        <div class="field-value"><?= htmlspecialchars($order['customer_phone']) ?></div>
                    </div>
                    <div class="info-field">
                        <div class="field-label">Order Date</div>
                        <div class="field-value"><?= date('M d, Y - g:i A', strtotime($order['order_date'])) ?></div>
                    </div>
                </div>
                
                <div class="total-amount">
                    <div class="total-label">Total Order Amount</div>
                    <div class="total-value">₱<?= number_format($order['total_amount'], 2) ?></div>
                </div>
            </div>
        </div>
        
        <!-- Payment Information -->
        <div class="order-section">
            <div class="section-title green">
                <i class="bi bi-credit-card-2-front"></i>
                <span>Payment Information</span>
            </div>
            <div class="section-body">
                <?php if ($order['payment_id']): ?>
                    <div class="info-row">
                        <div class="info-field">
                            <div class="field-label">Payment Method</div>
                            <div>
                                <?php if (strpos($order['payment_method'], 'paymongo') !== false): ?>
                                    <span class="payment-method">
                                        <i class="bi bi-credit-card"></i>
                                        <?= $order['payment_method'] == 'paymongo_gcash' ? 'GCash via PayMongo' : 'Card via PayMongo' ?>
                                    </span>
                                <?php else: ?>
                                    <span class="payment-method">
                                        <i class="bi bi-cash-coin"></i>
                                        <?= strtoupper($order['payment_method']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="info-field">
                            <div class="field-label">Payment Status</div>
                            <div>
                                <span class="badge-status badge-<?= strtolower($order['payment_status']) ?>">
                                    <i class="bi bi-<?= $order['payment_status'] == 'paid' ? 'check-circle-fill' : 'clock' ?>"></i>
                                    <?= htmlspecialchars($order['payment_status']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-field">
                            <div class="field-label">Payment Amount</div>
                            <div class="field-value">₱<?= number_format($order['payment_amount'], 2) ?></div>
                        </div>
                        <?php if ($order['gateway_fee'] > 0): ?>
                        <div class="info-field">
                            <div class="field-label">Gateway Fee</div>
                            <div class="field-value">₱<?= number_format($order['gateway_fee'], 2) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($order['paymongo_payment_intent_id']): ?>
                    <div class="info-row">
                        <div class="info-field">
                            <div class="field-label">PayMongo Payment ID</div>
                            <div class="field-value" style="font-size: 0.9rem; word-break: break-all;">
                                <?= htmlspecialchars($order['paymongo_payment_intent_id']) ?>
                            </div>
                        </div>
                        <?php if ($order['transaction_reference']): ?>
                        <div class="info-field">
                            <div class="field-label">Transaction Reference</div>
                            <div class="field-value" style="font-size: 0.9rem;">
                                <?= htmlspecialchars($order['transaction_reference']) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($order['paid_at']): ?>
                    <div class="info-row">
                        <div class="info-field">
                            <div class="field-label">Paid At</div>
                            <div class="field-value"><?= date('M d, Y - g:i A', strtotime($order['paid_at'])) ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="no-data">
                        <i class="bi bi-exclamation-circle"></i>
                        <div>No payment information available</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Bottom Section: Shipping & Order Items -->
    <div class="order-grid">
        <!-- Shipping Information -->
        <div class="order-section">
            <div class="section-title blue">
                <i class="bi bi-geo-alt-fill"></i>
                <span>Shipping Information</span>
            </div>
            <div class="section-body">
                <div class="info-field">
                    <div class="field-label">Delivery Address</div>
                    <div class="address-box">
                        <?= nl2br(htmlspecialchars($order['shipping_address'])) ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Order Items -->
        <div class="order-section" style="grid-column: span 2;">
            <div class="section-title pink">
                <i class="bi bi-bag-check-fill"></i>
                <span>Order Items (<?= count($order_items) ?> <?= count($order_items) == 1 ? 'item' : 'items' ?>)</span>
            </div>
            <div class="section-body">
                <?php if (!empty($order_items)): ?>
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th style="text-align: center; width: 120px;">Quantity</th>
                                <th style="text-align: right; width: 150px;">Unit Price</th>
                                <th style="text-align: right; width: 150px;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td>
                                    <div class="item-name"><?= htmlspecialchars($item['product_name']) ?></div>
                                    <?php if ($item['product_id']): ?>
                                        <div class="item-id">Product ID: <?= htmlspecialchars($item['product_id']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center; font-weight: 700; font-size: 1.1rem;">
                                    <?= htmlspecialchars($item['quantity']) ?>
                                </td>
                                <td style="text-align: right; font-weight: 600;">
                                    ₱<?= number_format($item['price'], 2) ?>
                                </td>
                                <td style="text-align: right; font-weight: 700; color: #059669; font-size: 1.15rem;">
                                    ₱<?= number_format($item['total'], 2) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-data">
                        <i class="bi bi-inbox"></i>
                        <div>No order items found</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Payment Logs (if available) -->
    <?php if (!empty($payment_logs)): ?>
    <div class="order-section" style="margin-top: 2.5rem;">
        <div class="section-title" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <i class="bi bi-journal-text"></i>
            <span>Payment Transaction Logs (<?= count($payment_logs) ?>)</span>
        </div>
        <div class="section-body">
            <div class="logs-container">
                <?php foreach ($payment_logs as $log): ?>
                <div class="log-item <?= strtolower($log['status']) ?>">
                    <div class="log-time">
                        <i class="bi bi-clock"></i> 
                        <?= date('M d, Y - g:i:s A', strtotime($log['created_at'])) ?>
                    </div>
                    <div class="log-message">
                        <strong><?= htmlspecialchars($log['event_type']) ?></strong>
                        <?php if ($log['message']): ?>
                        <div style="margin-top: 0.5rem; color: #666;">
                            <?= htmlspecialchars($log['message']) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($log['gateway_response']): ?>
                    <button class="btn btn-sm btn-outline-secondary mt-2" onclick="toggleResponse(this, '<?= base64_encode($log['gateway_response']) ?>')">
                        <i class="bi bi-eye"></i> View Response
                    </button>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function toggleResponse(button, encodedData) {
    const parent = button.parentElement;
    const existing = parent.querySelector('.response-data');
    
    if (existing) {
        existing.remove();
        button.innerHTML = '<i class="bi bi-eye"></i> View Response';
    } else {
        const decoded = atob(encodedData);
        const formatted = JSON.stringify(JSON.parse(decoded), null, 2);
        
        const pre = document.createElement('pre');
        pre.className = 'response-data';
        pre.style.cssText = 'background: #f8f9fa; padding: 1rem; border-radius: 6px; margin-top: 1rem; font-size: 0.85rem; overflow-x: auto; max-height: 300px;';
        pre.textContent = formatted;
        
        parent.appendChild(pre);
        button.innerHTML = '<i class="bi bi-eye-slash"></i> Hide Response';
    }
}
</script>
