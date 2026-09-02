<?php
session_start();
require_once 'includes/db.php';

header('Content-Type: application/json');

$search_term = $_GET['q'] ?? '';
$category = $_GET['category'] ?? 'all';
$min_price = floatval($_GET['min_price'] ?? 0);
$max_price = floatval($_GET['max_price'] ?? 99999);

$products = [];
$search_performed = false;

if (isDatabaseConnected() && !empty($search_term)) {
    // Search in database
    $query = "SELECT id, product_name as name, price, image, tag, label, stock, created_at 
              FROM inventory 
              WHERE stock > 0 
              AND (product_name LIKE ? OR tag LIKE ? OR label LIKE ?) 
              AND price BETWEEN ? AND ?";
    
    $search_param = "%$search_term%";
    $params = [$search_param, $search_param, $search_param, $min_price, $max_price];
    $types = "sssdd";
    
    if ($category !== 'all') {
        $query .= " AND LOWER(tag) = ?";
        $params[] = strtolower($category);
        $types .= "s";
    }
    
    $query .= " ORDER BY 
                CASE 
                    WHEN product_name LIKE ? THEN 1
                    WHEN tag LIKE ? THEN 2
                    ELSE 3
                END, created_at DESC";
    
    $params[] = "$search_term%"; // Starts with search term gets priority
    $params[] = "$search_term%";
    $types .= "ss";
    
    $result = executeQuery($query, $params, $types);
    
    if ($result && $result->num_rows > 0) {
        $search_performed = true;
        while ($row = $result->fetch_assoc()) {
            // Fix image path
            $image_path = 'Assets/placeholder.svg';
            if (!empty($row['image'])) {
                if (file_exists('admin/' . $row['image'])) {
                    $image_path = 'admin/' . $row['image'];
                } elseif (file_exists($row['image'])) {
                    $image_path = $row['image'];
                }
            }
            
            $products[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'price' => number_format($row['price'], 2),
                'price_raw' => $row['price'],
                'image' => $image_path,
                'category' => strtolower($row['tag'] ?? 'other'),
                'badge' => $row['label'] ?? 'In Stock',
                'rating' => '⭐⭐⭐⭐☆',
                'reviews' => '(' . rand(50, 500) . ')',
                'stock' => $row['stock'],
                'is_database' => true
            ];
        }
    }
}

// Also search demo products if no database results or as fallback
if (empty($products) && !empty($search_term) && !$search_performed) {
    $demo_products = [
        [
            'id' => 'demo_1',
            'name' => 'Emergency First Aid Kit',
            'price' => '950.00',
            'price_raw' => 950.00,
            'image' => 'Assets/Gallery_Images/Survival Kit Sample.png',
            'category' => 'emergency',
            'badge' => 'Popular',
            'rating' => '⭐⭐⭐⭐☆',
            'reviews' => '(4.0k)',
            'stock' => 50
        ],
        [
            'id' => 'demo_2',
            'name' => '4-Person Camping Tent',
            'price' => '1,200.00',
            'price_raw' => 1200.00,
            'image' => 'Assets/Gallery_Images/TentSample.jpg',
            'category' => 'tents',
            'badge' => 'Best Seller',
            'rating' => '⭐⭐⭐⭐⭐',
            'reviews' => '(3.2k)',
            'stock' => 25
        ],
        [
            'id' => 'demo_3',
            'name' => 'Portable Cooking Set',
            'price' => '750.00',
            'price_raw' => 750.00,
            'image' => 'Assets/Gallery_Images/CookingGearSample.png',
            'category' => 'cooking',
            'badge' => 'Popular',
            'rating' => '⭐⭐⭐⭐☆',
            'reviews' => '(1.8k)',
            'stock' => 30
        ],
        [
            'id' => 'demo_4',
            'name' => 'Camping Backpack 50L',
            'price' => '1,450.00',
            'price_raw' => 1450.00,
            'image' => 'Assets/placeholder.svg',
            'category' => 'backpacks',
            'badge' => 'New Arrival',
            'rating' => '⭐⭐⭐⭐⭐',
            'reviews' => '(2.5k)',
            'stock' => 20
        ]
    ];
    
    // Filter demo products based on search term
    foreach ($demo_products as $product) {
        $name_match = stripos($product['name'], $search_term) !== false;
        $category_match = stripos($product['category'], $search_term) !== false;
        $badge_match = stripos($product['badge'], $search_term) !== false;
        
        if ($name_match || $category_match || $badge_match) {
            if ($category === 'all' || $product['category'] === $category) {
                if ($product['price_raw'] >= $min_price && $product['price_raw'] <= $max_price) {
                    $products[] = $product;
                }
            }
        }
    }
}

echo json_encode([
    'success' => true,
    'products' => $products,
    'count' => count($products),
    'search_term' => $search_term,
    'message' => empty($products) ? 'No products found matching your search.' : 'Search completed successfully.'
]);
?>