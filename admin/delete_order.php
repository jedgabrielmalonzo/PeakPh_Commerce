<?php
require_once('auth_helper.php');
requireAdminAuth();
require_once('../includes/db.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$orderId = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

if ($orderId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Get order details before deletion for logging
    $orderQuery = "SELECT order_id, customer_name, customer_email, total_amount, status FROM orders WHERE id = ?";
    $stmt = $conn->prepare($orderQuery);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $orderResult = $stmt->get_result();
    
    if ($orderResult->num_rows === 0) {
        throw new Exception('Order not found');
    }
    
    $orderData = $orderResult->fetch_assoc();
    $stmt->close();
    
    // Delete related records in proper order (respecting foreign keys)
    
    // 1. Delete payment logs (if they reference payments)
    $deleteLogsQuery = "DELETE pl FROM payment_logs pl 
                        INNER JOIN payments p ON pl.payment_id = p.id 
                        WHERE p.order_id = ?";
    $stmt = $conn->prepare($deleteLogsQuery);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $stmt->close();
    
    // 2. Delete payments
    $deletePaymentsQuery = "DELETE FROM payments WHERE order_id = ?";
    $stmt = $conn->prepare($deletePaymentsQuery);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $stmt->close();
    
    // 3. Delete order items
    $deleteItemsQuery = "DELETE FROM order_items WHERE order_id = ?";
    $stmt = $conn->prepare($deleteItemsQuery);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $stmt->close();
    
    // 4. Delete the order itself
    $deleteOrderQuery = "DELETE FROM orders WHERE id = ?";
    $stmt = $conn->prepare($deleteOrderQuery);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $stmt->close();
    
    // Log the deletion (you can create an admin_logs table for this)
    $logMessage = sprintf(
        "Order #%s permanently deleted by admin. Customer: %s (%s), Amount: ₱%.2f, Status: %s",
        $orderData['order_id'],
        $orderData['customer_name'],
        $orderData['customer_email'],
        $orderData['total_amount'],
        $orderData['status']
    );
    
    // Create log entry if admin_logs table exists
    $checkLogTable = "SHOW TABLES LIKE 'admin_logs'";
    $logTableResult = $conn->query($checkLogTable);
    
    if ($logTableResult->num_rows > 0) {
        $adminId = $_SESSION['admin_id'] ?? null;
        $adminEmail = $_SESSION['admin_email'] ?? 'unknown';
        
        $logQuery = "INSERT INTO admin_logs (admin_id, admin_email, action, details, created_at) 
                     VALUES (?, ?, 'order_deleted', ?, NOW())";
        $stmt = $conn->prepare($logQuery);
        $stmt->bind_param("iss", $adminId, $adminEmail, $logMessage);
        $stmt->execute();
        $stmt->close();
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Order permanently deleted successfully',
        'order_id' => $orderData['order_id']
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to delete order: ' . $e->getMessage()
    ]);
}

$conn->close();
