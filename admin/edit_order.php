<?php
require_once('auth_helper.php');
requireAdminAuth();
require_once('../includes/db.php');

if (!isset($_GET['id'])) {
  echo '<div class="alert alert-danger">No order ID provided.</div>';
  exit;
}

$orderId = intval($_GET['id']);
$orderQuery = "SELECT * FROM orders WHERE id = $orderId";
$orderResult = $conn->query($orderQuery);

if (!$orderResult || $orderResult->num_rows === 0) {
  echo '<div class="alert alert-danger">Order not found.</div>';
  exit;
}

$order = $orderResult->fetch_assoc();

// Fetch order items
$itemsQuery = "SELECT oi.*, p.product_name FROM order_items oi 
               LEFT JOIN products p ON oi.product_id = p.id 
               WHERE oi.order_id = $orderId";
$itemsResult = $conn->query($itemsQuery);
?>

<div class="container-fluid px-4 py-3">
  <div class="row g-3">
    <!-- Order Info -->
    <div class="col-md-6">
      <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2"></i>Order Information</h6>
      <div class="p-3 bg-light rounded-3">
        <p><strong>Order ID:</strong> #<?= htmlspecialchars($order['id']) ?></p>
        <p><strong>Order Date:</strong> <?= date('F j, Y, g:i a', strtotime($order['order_date'])) ?></p>
        <p><strong>Total Amount:</strong> ₱<?= number_format($order['total_amount'], 2) ?></p>
      </div>
    </div>

    <!-- Customer Info -->
    <div class="col-md-6">
      <h6 class="fw-bold mb-3"><i class="bi bi-person me-2"></i>Customer Details</h6>
      <div class="p-3 bg-light rounded-3">
        <p><strong>Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($order['customer_email']) ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($order['customer_phone']) ?></p>
        <p><strong>Address:</strong> <?= htmlspecialchars($order['customer_address']) ?></p>
      </div>
    </div>
  </div>

  <!-- Order Items -->
  <div class="mt-4">
    <h6 class="fw-bold mb-3"><i class="bi bi-box-seam me-2"></i>Order Items</h6>
    <div class="table-responsive">
      <table class="table table-sm table-bordered">
        <thead class="table-secondary">
          <tr>
            <th>Product</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($itemsResult && $itemsResult->num_rows > 0): ?>
            <?php while ($item = $itemsResult->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($item['product_name'] ?? 'Unknown Product') ?></td>
                <td><?= intval($item['quantity']) ?></td>
                <td>₱<?= number_format($item['price'], 2) ?></td>
                <td>₱<?= number_format($item['quantity'] * $item['price'], 2) ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="4" class="text-center text-muted">No items found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Update Status Form -->
  <div class="mt-4">
    <h6 class="fw-bold mb-3"><i class="bi bi-gear me-2"></i>Update Order Status</h6>
    <form id="updateOrderForm">
      <input type="hidden" name="order_id" value="<?= $orderId ?>">
      <div class="row g-2 align-items-end">
        <div class="col-md-8">
          <label for="orderStatus" class="form-label">Status</label>
          <select name="status" id="orderStatus" class="form-select" required>
            <option value="Pending" <?= $order['status']=='Pending'?'selected':'' ?>>Pending</option>
            <option value="Processing" <?= $order['status']=='Processing'?'selected':'' ?>>Processing</option>
            <option value="Shipped" <?= $order['status']=='Shipped'?'selected':'' ?>>Shipped</option>
            <option value="Delivered" <?= $order['status']=='Delivered'?'selected':'' ?>>Delivered</option>
            <option value="Cancelled" <?= $order['status']=='Cancelled'?'selected':'' ?>>Cancelled</option>
          </select>
        </div>
        <div class="col-md-4">
          <button type="submit" class="btn btn-success w-100">
            <i class="bi bi-check-circle me-2"></i>Update Status
          </button>
        </div>
      </div>
    </form>
    <div id="updateMessage" class="mt-3"></div>
  </div>
</div>

<script>
document.getElementById('updateOrderForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  const messageDiv = document.getElementById('updateMessage');
  
  fetch('update_order_status.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      messageDiv.innerHTML = '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>' + data.message + '</div>';
      setTimeout(() => {
        location.reload(); // Refresh to show updated data
      }, 1500);
    } else {
      messageDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>' + data.message + '</div>';
    }
  })
  .catch(err => {
    messageDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>An error occurred.</div>';
  });
});
</script>
