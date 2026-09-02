<?php
session_start();
require_once 'includes/db.php';

// Get products from inventory for testing
$products = [];
if (isDatabaseConnected()) {
    $result = mysqli_query($GLOBALS['conn'], "SELECT * FROM inventory WHERE stock > 0 ORDER BY id LIMIT 6");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
    }
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle add to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = max(1, intval($_POST['quantity']));
    
    // Find product in inventory
    foreach ($products as $product) {
        if ($product['id'] == $product_id) {
            $cart_item = [
                'id' => $product['id'],
                'name' => $product['product_name'],
                'price' => floatval($product['price']),
                'quantity' => $quantity,
                'image' => $product['image'],
                'is_database' => true,
                'stock' => $product['stock']
            ];
            
            // Check if item already in cart
            $found = false;
            foreach ($_SESSION['cart'] as &$existing_item) {
                if ($existing_item['id'] == $product_id) {
                    $existing_item['quantity'] += $quantity;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $_SESSION['cart'][] = $cart_item;
            }
            break;
        }
    }
    
    header('Location: ' . $_SERVER['PHP_SELF'] . '?added=1');
    exit;
}

// Handle remove from cart
if (isset($_GET['remove'])) {
    $remove_id = $_GET['remove'];
    $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($remove_id) {
        return $item['id'] != $remove_id;
    });
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Handle clear cart
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Calculate cart totals
$cart_total = 0;
$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_total += $item['price'] * $item['quantity'];
    $cart_count += $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayMongo GCash Test - PeakPH Commerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        .product-card {
            transition: transform 0.2s ease-in-out;
            border: 1px solid #e0e0e0;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .product-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        .cart-sidebar {
            position: fixed;
            right: 0;
            top: 0;
            height: 100vh;
            width: 350px;
            background: white;
            box-shadow: -2px 0 10px rgba(0,0,0,0.1);
            transform: translateX(100%);
            transition: transform 0.3s ease;
            z-index: 1050;
            overflow-y: auto;
        }
        .cart-sidebar.open {
            transform: translateX(0);
        }
        .cart-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1040;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        .cart-overlay.open {
            opacity: 1;
            visibility: visible;
        }
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
            min-width: 20px;
            text-align: center;
        }
        .payment-method-card {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .payment-method-card:hover {
            border-color: #007bff;
            background-color: #f8f9fa;
        }
        .payment-method-card.selected {
            border-color: #007bff;
            background-color: #e3f2fd;
        }
        .success-alert {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            border: none;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-mountain"></i> PeakPH Commerce
            </a>
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-light position-relative me-3" onclick="toggleCart()">
                    <i class="bi bi-cart3"></i>
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-badge"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </button>
                <span class="text-light">Cart: ₱<?php echo number_format($cart_total, 2); ?></span>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Success Message -->
        <?php if (isset($_GET['added'])): ?>
        <div class="alert success-alert alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> Product added to cart successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="display-6 mb-3">
                    <i class="bi bi-credit-card text-primary"></i>
                    PayMongo GCash Test Store
                </h1>
                <p class="lead">Add products to cart and test GCash payment integration</p>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="row">
            <?php if (empty($products)): ?>
            <div class="col-12">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    No products available. Please add products to your inventory first.
                </div>
            </div>
            <?php else: ?>
            <?php foreach ($products as $product): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card product-card h-100">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                         class="card-img-top product-image" 
                         alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                         onerror="this.src='Assets/Gallery_Images/placeholder.jpg'">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($product['product_name']); ?></h5>
                        <p class="text-muted mb-2">
                            <span class="badge bg-secondary"><?php echo htmlspecialchars($product['tag']); ?></span>
                            <span class="badge bg-info"><?php echo htmlspecialchars($product['label']); ?></span>
                        </p>
                        <p class="h5 text-primary mb-3">₱<?php echo number_format($product['price'], 2); ?></p>
                        <p class="text-muted mb-3">Stock: <?php echo $product['stock']; ?> available</p>
                        
                        <form method="POST" class="mt-auto">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" name="quantity" value="1" min="1" 
                                           max="<?php echo $product['stock']; ?>" class="form-control form-control-sm">
                                </div>
                                <div class="col-6">
                                    <button type="submit" name="add_to_cart" class="btn btn-primary btn-sm w-100">
                                        <i class="bi bi-cart-plus"></i> Add
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Cart Overlay -->
    <div class="cart-overlay" onclick="toggleCart()"></div>

    <!-- Cart Sidebar -->
    <div class="cart-sidebar" id="cartSidebar">
        <div class="p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4><i class="bi bi-cart3"></i> Shopping Cart</h4>
                <button class="btn-close" onclick="toggleCart()"></button>
            </div>

            <?php if (empty($_SESSION['cart'])): ?>
            <div class="text-center py-5">
                <i class="bi bi-cart-x display-1 text-muted"></i>
                <p class="text-muted mt-3">Your cart is empty</p>
            </div>
            <?php else: ?>
            <!-- Cart Items -->
            <div class="cart-items mb-4">
                <?php foreach ($_SESSION['cart'] as $item): ?>
                <div class="card mb-3">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                 class="me-3" style="width: 50px; height: 50px; object-fit: cover;"
                                 onerror="this.src='Assets/Gallery_Images/placeholder.jpg'">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                                <div class="fw-bold text-primary">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                            </div>
                            <a href="?remove=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Cart Summary -->
            <div class="border-top pt-3 mb-4">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <span>₱<?php echo number_format($cart_total, 2); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Shipping:</span>
                    <span>₱50.00</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Tax (12%):</span>
                    <span>₱<?php echo number_format($cart_total * 0.12, 2); ?></span>
                </div>
                <hr>
                <div class="d-flex justify-content-between h5">
                    <span>Total:</span>
                    <span class="text-primary">₱<?php echo number_format($cart_total + 50 + ($cart_total * 0.12), 2); ?></span>
                </div>
            </div>

            <!-- Cart Actions -->
            <div class="d-grid gap-2">
                <a href="checkout.php" class="btn btn-success btn-lg">
                    <i class="bi bi-credit-card"></i> Checkout Now
                </a>
                <a href="?clear=1" class="btn btn-outline-secondary" 
                   onclick="return confirm('Clear all items from cart?')">
                    <i class="bi bi-cart-x"></i> Clear Cart
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleCart() {
            const sidebar = document.getElementById('cartSidebar');
            const overlay = document.querySelector('.cart-overlay');
            
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
        }

        // Auto-hide success alert
        setTimeout(function() {
            const alert = document.querySelector('.success-alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 3000);
    </script>
</body>
</html>