<?php
session_start();
require_once '../includes/db.php';

// Initialize response array
$response = [
    'success' => false,
    'cart_count' => 0,
    'message' => '',
    'product_name' => ''
];

// Initialize cart if not exists
if(!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Check database connection
if(isset($db_connection_error) && $db_connection_error) {
    $response['message'] = 'Database connection error. Please try again later.';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Handle both database and demo products
if(isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'] ?? '';
    $product_price = floatval($_POST['product_price'] ?? 0);
    $product_image = $_POST['product_image'] ?? '';
    
    // Try to get from database first
    $product = null;
    if(is_numeric($product_id) && isset($conn)) {
        $product_query = "SELECT * FROM inventory WHERE id = ?";
        $stmt = $conn->prepare($product_query);
        if($stmt) {
            $product_id_int = intval($product_id);
            $stmt->bind_param("i", $product_id_int);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows > 0) {
                $product = $result->fetch_assoc();
            }
        }
    }
    
    // Use database product if found, otherwise use form data
    if($product) {
        // Check stock availability for database products
        if($product['stock'] <= 0) {
            $response['message'] = 'Product is out of stock';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        
        // Fix image path for database products - match ProductCatalog.php logic exactly
        $image_path = 'Assets/placeholder.svg';
        if (!empty($product['image'])) {
            // Check if image exists with admin/ prefix (one level up since we're in api/)
            if (file_exists(__DIR__ . '/../admin/' . $product['image'])) {
                $image_path = 'admin/' . $product['image'];
            } 
            // Check without admin prefix (one level up)
            elseif (file_exists(__DIR__ . '/../' . $product['image'])) {
                $image_path = $product['image'];
            } 
            else {
                // Fallback to placeholder
                $image_path = 'Assets/placeholder.svg';
            }
        }
        
        $final_product = [
            'id' => $product['id'],
            'name' => $product['product_name'],
            'price' => floatval($product['price']),
            'image' => $image_path,
            'quantity' => 1,
            'stock' => $product['stock'],
            'is_database' => true
        ];
    } else {
        // Use form data for demo/hardcoded products
        // Make sure image path is absolute
        if (!empty($product_image) && !str_starts_with($product_image, '/')) {
            $product_image = '/' . ltrim($product_image, '/');
        }
        
        $final_product = [
            'id' => $product_id,
            'name' => $product_name,
            'price' => $product_price,
            'image' => $product_image,
            'quantity' => 1,
            'stock' => 999, // Demo products have unlimited stock
            'is_database' => false
        ];
    }
    
    // Validate product data
    if(empty($final_product['name']) || $final_product['price'] <= 0) {
        $response['message'] = 'Invalid product data';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // Check if product already in cart
    $product_found = false;
    foreach($_SESSION['cart'] as $key => $item) {
        if($item['id'] == $product_id) {
            // Check stock before increasing quantity
            $new_quantity = $item['quantity'] + 1;
            if($final_product['is_database'] && $new_quantity > $final_product['stock']) {
                $response['message'] = 'Not enough stock available. Only ' . $final_product['stock'] . ' items in stock.';
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }
            $_SESSION['cart'][$key]['quantity'] = $new_quantity;
            $product_found = true;
            break;
        }
    }
    
    // If product not in cart, add it using product_id as key
    if(!$product_found) {
        $_SESSION['cart'][$product_id] = $final_product;
    }
    
    $response['success'] = true;
    $response['product_name'] = $final_product['name'];
    
    // Count items in cart
    $cart_count = 0;
    foreach($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
    
    $response['cart_count'] = $cart_count;
} else {
    $response['message'] = 'No product ID provided';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>