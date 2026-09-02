<?php
require_once(__DIR__ . '/../includes/db.php');

$payment_id = intval($_GET['payment_id'] ?? 0);
$order_id = $_GET['order_id'] ?? null;

if ($payment_id) {
    try {
        // Get payment record
        if ($payment_id) {
            $stmt = $conn->prepare("SELECT p.*, o.order_id as order_string_id, o.customer_name FROM payments p JOIN orders o ON p.order_id = o.id WHERE p.id = ?");
            $stmt->bind_param("i", $payment_id);
        } else if ($order_id) {
            $stmt = $conn->prepare("SELECT p.*, o.order_id as order_string_id, o.customer_name FROM payments p JOIN orders o ON p.order_id = o.id WHERE o.order_id = ? ORDER BY p.id DESC LIMIT 1");
            $stmt->bind_param("s", $order_id);
        }
        $stmt->execute();
        $payment = $stmt->get_result()->fetch_assoc();
        
        if ($payment) {
            // Update payment status to failed
            $stmt = $conn->prepare("UPDATE payments SET status = 'Failed' WHERE id = ?");
            $stmt->bind_param("i", $payment_id);
            $stmt->execute();
            
            // Update order status
            $stmt = $conn->prepare("UPDATE orders SET payment_status = 'Failed' WHERE id = ?");
            $stmt->bind_param("i", $payment['order_id']);
            $stmt->execute();
            
            // Log failed payment
            $stmt = $conn->prepare("INSERT INTO payment_logs (payment_id, order_id, action, status, error_message) VALUES (?, ?, 'payment_failed', 'failed', 'Payment cancelled or failed')");
            $stmt->bind_param("ii", $payment_id, $payment['order_id']);
            $stmt->execute();
        }
        
    } catch (Exception $e) {
        error_log('Payment failure handling error: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment Failed - PeakPH</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/Global.css">
</head>
<body>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card border-0 shadow-lg">
          <div class="card-body text-center p-5">
            <div class="mb-4">
              <i class="bi bi-x-circle-fill text-danger" style="font-size: 4rem;"></i>
            </div>
            
            <h2 class="fw-bold text-danger mb-3">Payment Failed</h2>
            <p class="text-muted mb-4">
              Unfortunately, your payment could not be processed. This may be due to:
            </p>
            
            <ul class="list-unstyled text-start mb-4">
              <li class="mb-2"><i class="bi bi-dash text-muted me-2"></i>Payment was cancelled</li>
              <li class="mb-2"><i class="bi bi-dash text-muted me-2"></i>Insufficient funds</li>
              <li class="mb-2"><i class="bi bi-dash text-muted me-2"></i>Network connection issues</li>
              <li class="mb-2"><i class="bi bi-dash text-muted me-2"></i>Payment gateway timeout</li>
            </ul>
            
            <?php if ($payment_id && isset($payment)): ?>
            <div class="alert alert-info">
              <strong>Order #<?= htmlspecialchars($payment['order_id']) ?></strong><br>
              Amount: ₱<?= number_format($payment['amount'], 2) ?><br>
              Status: Payment Failed
            </div>
            <?php endif; ?>
            
            <div class="d-grid gap-2">
              <a href="../checkout.php<?= $payment_id ? '?retry_payment=' . $payment_id : '' ?>" class="btn btn-primary btn-lg">
                <i class="bi bi-arrow-clockwise me-2"></i>Try Again
              </a>
              <a href="../cart.php" class="btn btn-outline-secondary">
                <i class="bi bi-cart me-2"></i>Back to Cart
              </a>
              <a href="../index.php" class="btn btn-outline-primary">
                <i class="bi bi-house me-2"></i>Continue Shopping
              </a>
            </div>
            
            <hr class="my-4">
            
            <div class="text-muted">
              <small>
                <strong>Need Help?</strong><br>
                Contact our support team at <a href="mailto:support@peakph.com">support@peakph.com</a><br>
                or call us at (02) 8XXX-XXXX
              </small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>