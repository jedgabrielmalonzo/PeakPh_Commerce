<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

// Get product IDs from request
$productIds = isset($_GET['ids']) ? $_GET['ids'] : '';

if (empty($productIds)) {
    echo json_encode(['success' => false, 'message' => 'No product IDs provided']);
    exit;
}

// Convert comma-separated string to array
$ids = explode(',', $productIds);
$ids = array_filter(array_map('intval', $ids)); // Convert to integers and remove invalid values

if (empty($ids)) {
    echo json_encode(['success' => false, 'message' => 'No valid product IDs']);
    exit;
}

try {
    // Create placeholders for prepared statement
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    
    // Query to get product details
    $query = "SELECT id, product_name, price, image, stock 
              FROM inventory 
              WHERE id IN ($placeholders)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        // Determine the correct image path
        $imagePath = '';
        if (!empty($row['image'])) {
            // Check if image exists in admin folder
            if (file_exists(__DIR__ . '/../admin/' . $row['image'])) {
                $imagePath = 'admin/' . $row['image'];
            } else {
                $imagePath = 'Assets/placeholder.svg';
            }
        } else {
            $imagePath = 'Assets/placeholder.svg';
        }
        
        $products[] = [
            'id' => $row['id'],
            'name' => $row['product_name'],
            'price' => $row['price'],
            'image' => $imagePath,
            'stock' => $row['stock']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'products' => $products
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
