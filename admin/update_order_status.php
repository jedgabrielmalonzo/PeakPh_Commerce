<?php
require_once('auth_helper.php');
requireAdminAuth();
require_once('../includes/db.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Invalid request method']);
  exit;
}

if (!isset($_POST['order_id']) || !isset($_POST['status'])) {
  echo json_encode(['success' => false, 'message' => 'Missing required fields']);
  exit;
}

$orderId = intval($_POST['order_id']);
$status = $conn->real_escape_string(trim($_POST['status']));

$allowedStatuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
if (!in_array($status, $allowedStatuses)) {
  echo json_encode(['success' => false, 'message' => 'Invalid status value']);
  exit;
}

$updateQuery = "UPDATE orders SET status = '$status' WHERE id = $orderId";

if ($conn->query($updateQuery)) {
  echo json_encode(['success' => true, 'message' => 'Order status updated successfully']);
} else {
  echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}

exit;
?>
