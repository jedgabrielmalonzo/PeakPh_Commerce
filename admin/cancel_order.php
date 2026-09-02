<?php
require_once('auth_helper.php');
requireAdminAuth();
require_once('../includes/db.php');

if (isset($_GET['id'])) {
  $orderId = intval($_GET['id']);
  $updateQuery = "UPDATE orders SET status = 'Cancelled' WHERE id = $orderId";
  
  if ($conn->query($updateQuery)) {
    header("Location: orders.php?msg=Order cancelled successfully");
  } else {
    header("Location: orders.php?error=Failed to cancel order");
  }
} else {
  header("Location: orders.php?error=Invalid request");
}
exit;
?>
