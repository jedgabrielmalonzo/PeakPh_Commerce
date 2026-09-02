<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Product ID required']);
    exit;
}

$product_id = $_GET['id'];
$product = null;

// Check if it's a database product (numeric ID)
if (is_numeric($product_id) && isDatabaseConnected()) {
    $query = "SELECT * FROM inventory WHERE id = ?";
    $params = [$product_id];
    $types = "i";
    
    $result = executeQuery($query, $params, $types);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Fix image path
        $image_path = 'Assets/placeholder.svg';
        if (!empty($row['image'])) {
            if (file_exists('admin/' . $row['image'])) {
                $image_path = 'admin/' . $row['image'];
            } elseif (file_exists($row['image'])) {
                $image_path = $row['image'];
            }
        }
        
        $product = [
            'id' => $row['id'],
            'name' => $row['product_name'],
            'price' => number_format($row['price'], 2),
            'price_raw' => $row['price'],
            'image' => $image_path,
            'category' => strtolower($row['tag'] ?? 'other'),
            'badge' => $row['label'] ?? 'In Stock',
            'stock' => $row['stock'],
            'description' => $row['description'] ?? 'High-quality product from PeakPH Commerce.',
            'rating' => '⭐⭐⭐⭐☆',
            'reviews' => '(' . rand(50, 500) . ')',
            'is_database' => true,
            'created_at' => $row['created_at'] ?? null
        ];
    }
}

// Fallback to demo products for demo IDs
if (!$product && strpos($product_id, 'demo_') === 0) {
    $demo_products = [
        'demo_1' => [
            'id' => 'demo_1',
            'name' => 'Emergency First Aid Kit',
            'price' => '950.00',
            'price_raw' => 950.00,
            'image' => 'Assets/Gallery_Images/Survival Kit Sample.png',
            'category' => 'emergency',
            'badge' => 'Popular',
            'stock' => 50,
            'description' => 'Complete emergency first aid kit with all essential medical supplies for outdoor adventures.',
            'rating' => '⭐⭐⭐⭐☆',
            'reviews' => '(4.0k)',
            'is_database' => false
        ],
        'demo_2' => [
            'id' => 'demo_2',
            'name' => '4-Person Camping Tent',
            'price' => '1,200.00',
            'price_raw' => 1200.00,
            'image' => 'Assets/Gallery_Images/TentSample.jpg',
            'category' => 'tents',
            'badge' => 'Best Seller',
            'stock' => 25,
            'description' => 'Spacious 4-person camping tent with waterproof material and easy setup design.',
            'rating' => '⭐⭐⭐⭐⭐',
            'reviews' => '(3.2k)',
            'is_database' => false
        ]
        // Add more as needed
    ];
    
    $product = $demo_products[$product_id] ?? null;
}

if ($product) {
    echo json_encode(['success' => true, 'product' => $product]);
} else {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
}
?>