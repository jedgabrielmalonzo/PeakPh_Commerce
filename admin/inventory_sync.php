<?php
// Real-time inventory sync endpoint
require_once('auth_helper.php');
requireAdminAuth();
require_once '../includes/db.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$product_id = $_POST['product_id'] ?? '';

switch ($action) {
    case 'sync_stock':
        // Get current stock levels for all products
        if (isDatabaseConnected()) {
            $query = "SELECT id, product_name, price, stock, image, tag, label FROM inventory WHERE stock >= 0";
            $result = executeQuery($query);
            
            $products = [];
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $products[] = [
                        'id' => $row['id'],
                        'name' => $row['product_name'],
                        'stock' => $row['stock'],
                        'price' => $row['price'],
                        'status' => $row['stock'] > 0 ? 'available' : 'out_of_stock'
                    ];
                }
            }
            
            echo json_encode([
                'success' => true,
                'products' => $products,
                'timestamp' => time()
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database not available']);
        }
        break;
        
    case 'update_stock':
        // Update stock for a specific product
        if (!empty($product_id) && isset($_POST['new_stock'])) {
            $new_stock = intval($_POST['new_stock']);
            
            if (isDatabaseConnected()) {
                $query = "UPDATE inventory SET stock = ? WHERE id = ?";
                $params = [$new_stock, $product_id];
                $types = "ii";
                
                $result = executeQuery($query, $params, $types);
                
                if ($result === true) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Stock updated successfully',
                        'product_id' => $product_id,
                        'new_stock' => $new_stock
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update stock']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Database not available']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>