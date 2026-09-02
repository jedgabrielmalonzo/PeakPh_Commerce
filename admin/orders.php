<?php
require_once('auth_helper.php');
requireAdminAuth();
require_once('../includes/db.php');

// Handle filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$date = isset($_GET['date']) ? trim($_GET['date']) : '';

// Enhanced query to include payment details
$query = "
  SELECT o.*, 
         p.payment_method, 
         p.amount as payment_amount, 
         p.gateway_fee, 
         p.status as payment_status, 
         p.paymongo_payment_intent_id,
         p.paymongo_source_id,
         p.paid_at,
         COUNT(pl.id) as payment_logs_count
  FROM orders o 
  LEFT JOIN payments p ON o.id = p.order_id 
  LEFT JOIN payment_logs pl ON p.id = pl.payment_id
  WHERE 1=1";

if ($search !== '') {
  $search_safe = mysqli_real_escape_string($conn, $search);
  $query .= " AND (o.order_id LIKE '%$search_safe%' OR o.customer_email LIKE '%$search_safe%' OR o.customer_name LIKE '%$search_safe%')";
}
if ($status !== '') {
  $status_safe = mysqli_real_escape_string($conn, $status);
  $query .= " AND o.status = '$status_safe'";
}
if ($date !== '') {
  $date_safe = mysqli_real_escape_string($conn, $date);
  $query .= " AND DATE(o.order_date) = '$date_safe'";
}
$query .= " GROUP BY o.id ORDER BY o.order_date DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Orders - PeakPH</title>
  <!-- Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <!-- Flatpickr (Calendar) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_green.css">
  <!-- Admin Styles -->
  <link rel="stylesheet" href="../Css/admin.css"/>
  
  <style>
    /* Orders-specific styles */
    .status-badge {
      padding: 5px 12px; border-radius: 20px;
      font-size: 0.85rem; font-weight: 500;
    }
    .status-pending { background-color: #fff3cd; color: #856404; }
    .status-cancelled { background-color: #f8d7da; color: #842029; }
    .status-completed { background-color: #d1e7dd; color: #0f5132; }
    .status-processing { background-color: #cfe2ff; color: #084298; }
    .status-shipped { background-color: #e7d4ff; color: #6f42c1; }
    .status-delivered { background-color: #d1e7dd; color: #0f5132; }
    
    /* Payment status styles */
    .payment-unpaid { background-color: #f8d7da; color: #842029; }
    .payment-pending { background-color: #fff3cd; color: #856404; }
    .payment-paid { background-color: #d1e7dd; color: #0f5132; }
    .payment-failed { background-color: #f8d7da; color: #842029; }
    .payment-refunded { background-color: #e2e3e5; color: #495057; }
    
    /* PayMongo indicators */
    .paymongo-indicator {
      font-size: 0.75rem;
      padding: 2px 6px;
      border-radius: 12px;
      background-color: #e3f2fd;
      color: #1976d2;
      font-weight: 500;
    }
    
    .order-details {
      cursor: pointer;
      transition: background-color 0.2s ease;
    }
    
    .order-details:hover {
      background-color: #f8f9fa;
    }

    .btn-custom {
      border: none; border-radius: 8px;
      font-weight: 500; padding: 6px 12px;
      color: #fff; display: inline-flex; align-items: center; gap: 4px;
      transition: all 0.2s ease;
    }
    .btn-custom:hover { opacity: 0.85; transform: translateY(-1px); }
    .btn-edit { background-color: #0d6efd; }
    .btn-cancel { background-color: #dc3545; }

    .filter-bar input, .filter-bar select {
      border-radius: 8px; padding: 6px 10px; font-size: 0.9rem;
    }
    .filter-bar button {
      border-radius: 8px; font-weight: 500;
    }

    .flatpickr-day.selected { 
      background-color: #198754 !important; 
      border-color: #198754 !important; 
      color: white !important; 
    }

    .table thead { 
      background-color: #1b2430; 
      color: white; 
    }
  </style>
</head>
<body>
  <!-- HEADER -->
  <header>
    <h2>Orders Management</h2>
    <button onclick="logout()">Logout</button>
  </header>

  <!-- SIDEBAR -->
  <div class="sidebar">
    <h3>Menu</h3>
    <a href="admin.php" class="menu-link"><i class="bi bi-house"></i> Admin Home</a>
    <a href="dashboard.php" class="menu-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="mini-view.php" class="menu-link"><i class="bi bi-pencil-square"></i> Mini View</a>
    <a href="inventory/inventory.php" class="menu-link"><i class="bi bi-box"></i> Inventory</a>
    <a href="orders.php" class="menu-link active"><i class="bi bi-bag"></i> Orders</a>
    <a href="users/users.php" class="menu-link"><i class="bi bi-people"></i> Users</a>
    <!-- Collapsible Content Manager (expanded by default) -->
    <button class="collapsible" onclick="toggleContentManager()">
      <i class="bi bi-folder"></i> Content Manager
      <span id="arrow" style="float:right;">&#9660;</span>
    </button>
    <div class="content-manager-links" id="contentManagerLinks" style="display:block; margin-left: 15px;">
      <a href="content/carousel.php" class="menu-link"><i class="bi bi-images"></i> Carousel</a>
      <a href="content/bestseller.php" class="menu-link"><i class="bi bi-star"></i> Best Seller</a>
      <a href="content/new_arrivals.php" class="menu-link"><i class="bi bi-lightning"></i> New Arrivals</a>
      <a href="content/footer.php" class="menu-link"><i class="bi bi-layout-text-window-reverse"></i> Footer</a>
    </div>
  </div>

  <!-- CONTENT -->
  <div class="content">
    <h2>Orders Management</h2>
    <p>Manage customer orders and fulfillment.</p>

    <!-- Filter Bar -->
    <div class="filter-controls mb-4">
      <form class="row g-2 filter-bar" method="GET">
        <div class="col-md-4">
          <input type="text" name="search" class="form-control" placeholder="🔍 Search Orders" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
          <select name="status" class="form-select">
            <option value="">All Status</option>
            <option value="Pending" <?= $status=='Pending'?'selected':'' ?>>Pending</option>
            <option value="Processing" <?= $status=='Processing'?'selected':'' ?>>Processing</option>
            <option value="Shipped" <?= $status=='Shipped'?'selected':'' ?>>Shipped</option>
            <option value="Delivered" <?= $status=='Delivered'?'selected':'' ?>>Delivered</option>
            <option value="Cancelled" <?= $status=='Cancelled'?'selected':'' ?>>Cancelled</option>
          </select>
        </div>
        <div class="col-md-3">
          <input type="text" id="dateFilter" name="date" class="form-control" placeholder="📅 Select Date" value="<?= htmlspecialchars($date) ?>">
        </div>
        <div class="col-md-2 text-end">
          <button class="btn btn-success w-100" type="submit">Filter</button>
        </div>
      </form>
    </div>

    <!-- Orders Table -->
    <div class="table-responsive">
      <table class="table table-hover align-middle bg-white border">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Payment</th>
            <th>Order Date</th>
            <th>Total</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr class="order-details" onclick="viewOrderDetails('<?= $row['id'] ?>')">
                <td>
                  <div><strong>#<?= htmlspecialchars($row['order_id'] ?? $row['id']) ?></strong></div>
                  <?php if ($row['payment_logs_count'] > 0): ?>
                    <small class="text-info">
                      <i class="bi bi-info-circle"></i> <?= $row['payment_logs_count'] ?> logs
                    </small>
                  <?php endif; ?>
                </td>
                <td>
                  <div><?= htmlspecialchars($row['customer_name']) ?></div>
                  <small class="text-muted"><?= htmlspecialchars($row['customer_email']) ?></small>
                </td>
                <td>
                  <div>
                    <?php if ($row['payment_method']): ?>
                      <?php if (strpos($row['payment_method'], 'paymongo') !== false): ?>
                        <span class="paymongo-indicator">
                          <i class="bi bi-credit-card"></i> PayMongo
                        </span>
                        <br>
                        <small class="text-muted">
                          <?= $row['payment_method'] === 'paymongo_gcash' ? 'GCash' : 'Card' ?>
                          <?php if ($row['gateway_fee'] > 0): ?>
                            <br>Fee: ₱<?= number_format($row['gateway_fee'], 2) ?>
                          <?php endif; ?>
                        </small>
                      <?php else: ?>
                        <span class="badge bg-secondary"><?= strtoupper($row['payment_method']) ?></span>
                      <?php endif; ?>
                    <?php else: ?>
                      <span class="text-muted">No payment</span>
                    <?php endif; ?>
                  </div>
                  <?php if ($row['payment_status']): ?>
                    <span class="status-badge payment-<?= strtolower($row['payment_status']) ?>">
                      <?= htmlspecialchars($row['payment_status']) ?>
                    </span>
                  <?php endif; ?>
                </td>
                <td>
                  <?= date('M d, Y', strtotime($row['order_date'])) ?>
                  <br>
                  <small class="text-muted"><?= date('g:i A', strtotime($row['order_date'])) ?></small>
                </td>
                <td>
                  <div><strong>₱<?= number_format($row['total_amount'], 2) ?></strong></div>
                  <?php if ($row['payment_amount'] && $row['payment_amount'] != $row['total_amount']): ?>
                    <small class="text-info">Paid: ₱<?= number_format($row['payment_amount'], 2) ?></small>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="status-badge 
                    <?= strtolower($row['status']) === 'pending' ? 'status-pending' : 
                        (strtolower($row['status']) === 'processing' ? 'status-processing' : 
                        (strtolower($row['status']) === 'shipped' ? 'status-shipped' :
                        (strtolower($row['status']) === 'delivered' ? 'status-delivered' :
                        'status-cancelled'))) ?>">
                    <?= htmlspecialchars($row['status']) ?>
                  </span>
                  <?php if ($row['paid_at']): ?>
                    <br><small class="text-success">Paid: <?= date('M d, g:i A', strtotime($row['paid_at'])) ?></small>
                  <?php endif; ?>
                </td>
                <td onclick="event.stopPropagation();">
                  <div class="d-flex gap-2">
                    <a href="#" class="btn-custom btn-edit" data-id="<?= $row['id'] ?>" title="Edit Order">
                      <i class="bi bi-pencil-square"></i>
                    </a>
                    <a href="#" class="btn-custom" style="background-color: #17a2b8;" onclick="viewOrderDetails('<?= $row['id'] ?>', true)" title="View Details">
                      <i class="bi bi-eye"></i>
                    </a>
                    <?php if (strtolower($row['status']) !== 'cancelled' && strtolower($row['status']) !== 'delivered'): ?>
                      <a href="cancel_order.php?id=<?= $row['id'] ?>" class="btn-custom btn-cancel" onclick="return confirm('Cancel this order?');" title="Cancel Order">
                        <i class="bi bi-x-circle"></i>
                      </a>
                    <?php endif; ?>
                    <a href="#" class="btn-custom" style="background-color: #dc3545;" 
                       onclick="confirmDeleteOrder(event, '<?= $row['id'] ?>', '<?= htmlspecialchars($row['order_id'] ?? $row['id'], ENT_QUOTES) ?>')" 
                       title="Permanently Delete Order">
                      <i class="bi bi-trash3-fill"></i>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="7" class="text-center text-muted">No orders found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    </div>
  </div>

  <!-- Enhanced Order Details Modal -->
  <div class="modal fade" id="editOrderModal" tabindex="-1" aria-labelledby="editOrderModalLabel" aria-hidden="true" style="z-index: 2000;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 95vw !important; width: 95vw !important; margin: 1.75rem auto;">
      <div class="modal-content border-0 rounded-4 shadow" style="z-index: 2001; height: 85vh !important; min-height: 700px; width: 100% !important;">
        <div class="modal-header py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 1rem 1rem 0 0;">
          <h5 class="modal-title" id="editOrderModalLabel">
            <i class="bi bi-receipt me-2"></i>Order Details
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4" id="editOrderContent" style="height: calc(85vh - 120px); overflow-y: auto; overflow-x: hidden; width: 100% !important; box-sizing: border-box;">
          <div class="text-center p-3 text-muted">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3">Loading order details...</p>
          </div>
        </div>
        <div class="modal-footer py-3" style="background: #f8f9fa; border-radius: 0 0 1rem 1rem;">
          <div class="d-flex gap-2 w-100 justify-content-between">
            <button type="button" class="btn btn-outline-primary" onclick="window.location.href='orders.php'">
              <i class="bi bi-arrow-left-circle"></i> Go Back to Orders
            </button>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                <i class="bi bi-x-circle"></i> Close
              </button>
              <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer"></i> Print
              </button>
              <button type="button" class="btn btn-success" onclick="exportOrderToPDF()">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Order Confirmation Modal -->
  <div class="modal fade" id="deleteOrderModal" tabindex="-1" aria-labelledby="deleteOrderModalLabel" aria-hidden="true" data-bs-backdrop="true" data-bs-keyboard="true" style="z-index: 9999;">
    <div class="modal-dialog modal-dialog-centered" style="z-index: 10000;">
      <div class="modal-content border-0 shadow-lg" style="z-index: 10001;">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="deleteOrderModalLabel">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>Permanently Delete Order
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <div class="alert alert-warning border-0" style="background-color: #fff3cd;">
            <div class="d-flex align-items-start">
              <i class="bi bi-exclamation-circle-fill text-warning me-3" style="font-size: 2rem;"></i>
              <div>
                <h6 class="alert-heading fw-bold mb-2">⚠️ Critical Action - Read Carefully!</h6>
                <p class="mb-2">You are about to <strong>permanently delete</strong> order <span id="deleteOrderId" class="badge bg-danger"></span></p>
                <ul class="mb-0 small">
                  <li>This action <strong>CANNOT be undone</strong></li>
                  <li>All order data, payment records, and transaction logs will be permanently removed</li>
                  <li>Customer will lose access to this order information</li>
                  <li>This deletion will be logged for audit purposes</li>
                </ul>
              </div>
            </div>
          </div>
          
          <div class="alert alert-info border-0 mb-0" style="background-color: #cff4fc;">
            <i class="bi bi-info-circle-fill me-2"></i>
            <strong>Recommendation:</strong> Consider canceling the order instead of deleting it to maintain records.
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i>Cancel
          </button>
          <button type="button" class="btn btn-danger" id="confirmDeleteBtn" onclick="executeDeleteOrder()">
            <i class="bi bi-trash3-fill me-1"></i>Yes, Permanently Delete
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Notification Container -->
  <div id="notificationContainer"></div>

  <!-- JS -->
  <script src="../Js/admin.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

  <script>
    // Calendar
    flatpickr("#dateFilter", {
      dateFormat: "Y-m-d",
      allowInput: true,
      altInput: true,
      altFormat: "F j, Y",
      maxDate: "today"
    });

    // Edit Modal Loader
    const editOrderModalEl = document.getElementById('editOrderModal');
    let editOrderModal = null;
    document.querySelectorAll('.btn-edit').forEach(button => {
      button.addEventListener('click', function(e) {
        e.preventDefault();
        const orderId = this.getAttribute('data-id');
        const modalBody = document.getElementById('editOrderContent');
        modalBody.innerHTML = '<div class="text-center p-4 text-muted">Loading...</div>';

        if (editOrderModal) {
          editOrderModal.hide();
        }

        fetch('edit_order.php?id=' + orderId)
          .then(res => res.text())
          .then(html => {
            modalBody.innerHTML = html;
            editOrderModal = new bootstrap.Modal(editOrderModalEl);
            editOrderModal.show();
          })
          .catch(err => {
            modalBody.innerHTML = '<div class="text-danger p-3">Failed to load order details.</div>';
          });
      });
    });

    editOrderModalEl.addEventListener('hidden.bs.modal', function () {
      document.getElementById('editOrderContent').innerHTML = '<div class="text-center p-4 text-muted">Loading...</div>';
    });

    // Enhanced modal functionality
    function toggleDetails(button, encodedData) {
      const decoded = atob(encodedData);
      const formatted = JSON.stringify(JSON.parse(decoded), null, 2);
      
      if (button.nextElementSibling && button.nextElementSibling.classList.contains('response-details')) {
        // Toggle existing details
        const details = button.nextElementSibling;
        if (details.style.display === 'none') {
          details.style.display = 'block';
          button.innerHTML = '<i class="bi bi-eye-slash"></i> Hide';
        } else {
          details.style.display = 'none';
          button.innerHTML = '<i class="bi bi-eye"></i> View Response';
        }
      } else {
        // Create new details element
        const details = document.createElement('pre');
        details.className = 'response-details mt-2 p-2 bg-light border rounded';
        details.style.fontSize = '0.75rem';
        details.style.maxHeight = '200px';
        details.style.overflow = 'auto';
        details.textContent = formatted;
        
        button.parentNode.appendChild(details);
        button.innerHTML = '<i class="bi bi-eye-slash"></i> Hide';
      }
    }
    
    function exportOrderToPDF() {
      const orderContent = document.getElementById('editOrderContent');
      if (orderContent) {
        window.print();
      }
    }

    // View Order Details Function
    function viewOrderDetails(orderId, openModal = false) {
      if (openModal) {
        // Open in modal
        const modalBody = document.getElementById('editOrderContent');
        modalBody.innerHTML = '<div class="text-center p-4 text-muted">Loading order details...</div>';

        if (editOrderModal) {
          editOrderModal.hide();
        }

        fetch('view_order_details.php?id=' + orderId)
          .then(res => res.text())
          .then(html => {
            modalBody.innerHTML = html;
            document.getElementById('editOrderModalLabel').innerHTML = '<i class="bi bi-eye me-2"></i>Order Details';
            editOrderModal = new bootstrap.Modal(editOrderModalEl);
            editOrderModal.show();
          })
          .catch(err => {
            modalBody.innerHTML = '<div class="text-danger p-3">Failed to load order details.</div>';
          });
      } else {
        // Navigate to dedicated page (you can implement this later)
        window.location.href = 'view_order_details.php?id=' + orderId + '&page=true';
      }
    }

    // Delete Order Function
    let deleteOrderModal = null;
    let orderToDelete = { id: null, displayId: null };

    function confirmDeleteOrder(event, orderId, displayId) {
      event.preventDefault();
      event.stopPropagation();
      
      orderToDelete.id = orderId;
      orderToDelete.displayId = displayId;
      
      // Initialize or get the modal
      const modalElement = document.getElementById('deleteOrderModal');
      if (!deleteOrderModal) {
        deleteOrderModal = new bootstrap.Modal(modalElement, {
          backdrop: 'static',
          keyboard: true,
          focus: true
        });
      }
      
      // Update modal content
      document.getElementById('deleteOrderId').textContent = '#' + displayId;
      
      // Show the modal
      deleteOrderModal.show();
    }

    function executeDeleteOrder() {
      const deleteBtn = document.getElementById('confirmDeleteBtn');
      const originalText = deleteBtn.innerHTML;
      
      // Disable button and show loading
      deleteBtn.disabled = true;
      deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
      
      // Send delete request
      fetch('delete_order.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'order_id=' + orderToDelete.id
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Show success message
          showNotification('success', 'Order #' + orderToDelete.displayId + ' has been permanently deleted.');
          
          // Close modal
          deleteOrderModal.hide();
          
          // Reload page after short delay
          setTimeout(() => {
            window.location.reload();
          }, 1500);
        } else {
          // Show error message
          showNotification('error', 'Failed to delete order: ' + data.message);
          deleteBtn.disabled = false;
          deleteBtn.innerHTML = originalText;
        }
      })
      .catch(error => {
        showNotification('error', 'An error occurred while deleting the order.');
        deleteBtn.disabled = false;
        deleteBtn.innerHTML = originalText;
      });
    }

    // Notification System
    function showNotification(type, message) {
      const notificationContainer = document.getElementById('notificationContainer');
      
      const notification = document.createElement('div');
      notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
      notification.style.cssText = 'position: fixed; top: 80px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
      notification.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}-fill me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      `;
      
      notificationContainer.appendChild(notification);
      
      // Auto remove after 5 seconds
      setTimeout(() => {
        notification.remove();
      }, 5000);
    }
  </script>
</body>
</html>
