<?php
require_once 'includes/user_auth.php';
require_once 'includes/db.php';

// Require login for this page
requireLogin();

$current_user = getCurrentUser();

// Helper functions for status icons
function getStatusIcon($status) {
    $icons = [
        'Pending' => 'hourglass-split',
        'Processing' => 'arrow-repeat',
        'Shipped' => 'truck',
        'Delivered' => 'check-circle',
        'Cancelled' => 'x-circle'
    ];
    return $icons[$status] ?? 'circle';
}

function getPaymentIcon($payment_status) {
    $icons = [
        'Unpaid' => 'x-circle',
        'Pending' => 'clock',
        'Paid' => 'check-circle',
        'Failed' => 'exclamation-circle',
        'Refunded' => 'arrow-counterclockwise'
    ];
    return $icons[$payment_status] ?? 'circle';
}

// Initialize cart count
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['quantity'];
}

// Fetch user's orders from database - ONLY for the logged-in user
$user_orders = [];

if (isset($conn) && $conn && isset($current_user['id'])) {
    $user_id = $current_user['id'];
    
    // Get all orders for this user with order items
    $query = "SELECT o.*, 
              COUNT(oi.id) as total_items,
              GROUP_CONCAT(oi.product_name SEPARATOR ', ') as products
              FROM orders o
              LEFT JOIN order_items oi ON o.id = oi.order_id
              WHERE o.user_id = ?
              GROUP BY o.id
              ORDER BY o.order_date DESC";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $user_orders[] = $row;
    }
    
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - PeakPH Commerce</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="Css/Global.css">
    <style>
        .orders-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .orders-header {
            background: linear-gradient(135deg, #2e765e, #3da180);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .orders-content {
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 2rem;
        }
        
        .orders-sidebar {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            height: fit-content;
        }
        
        .orders-main {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .profile-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .profile-nav li {
            margin-bottom: 0.5rem;
        }
        
        .profile-nav a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: #333;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .profile-nav a:hover, .profile-nav a.active {
            background: linear-gradient(135deg, #2e765e, #3da180);
            color: white;
        }
        
        /* Order Card Styles */
        .order-card {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            background: #fafafa;
        }
        
        .order-card:hover {
            box-shadow: 0 6px 20px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }
        
        .order-header-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 1rem;
        }
        
        .order-info-block {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .order-info-label {
            font-size: 0.75rem;
            color: #666;
            text-transform: uppercase;
            font-weight: 600;
        }
        
        .order-info-value {
            font-size: 1rem;
            color: #333;
            font-weight: 600;
        }
        
        .order-id-value {
            color: #2e765e;
            font-family: monospace;
        }
        
        .order-body {
            padding: 1rem 0;
        }
        
        .order-products {
            color: #555;
            font-size: 0.95rem;
            margin-bottom: 0.75rem;
            line-height: 1.6;
        }
        
        .order-products strong {
            color: #2e765e;
        }
        
        .order-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid #e0e0e0;
        }
        
        .view-details-btn {
            background: linear-gradient(135deg, #2e765e, #3da180);
            color: white;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .view-details-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(46, 118, 94, 0.3);
            color: white;
        }
        
        /* Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.9rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        /* Order Status Colors */
        .status-pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        
        .status-processing {
            background: #cfe2ff;
            color: #084298;
            border: 1px solid #b6d4fe;
        }
        
        .status-shipped {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .status-delivered {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Payment Status Colors */
        .payment-unpaid {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .payment-pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        
        .payment-paid {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .payment-failed {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .payment-refunded {
            background: #e2e3e5;
            color: #383d41;
            border: 1px solid #d6d8db;
        }
        
        /* Empty State */
        .empty-orders {
            text-align: center;
            padding: 3rem 2rem;
            color: #666;
        }
        
        .empty-orders i {
            font-size: 4rem;
            color: #ccc;
            margin-bottom: 1rem;
        }
        
        .empty-orders h3 {
            color: #333;
            margin-bottom: 1rem;
        }
        
        .shop-now-btn {
            display: inline-block;
            background: linear-gradient(135deg, #2e765e, #3da180);
            color: white;
            padding: 0.75rem 2rem;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 1rem;
            transition: transform 0.3s ease;
        }
        
        .shop-now-btn:hover {
            transform: translateY(-2px);
            color: white;
        }
        
        .order-summary {
            background: #f0f8f6;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            border-left: 4px solid #2e765e;
        }
        
        .order-summary-row {
            display: flex;
            justify-content: space-between;
            margin: 0.5rem 0;
            font-size: 0.95rem;
        }
        
        .order-summary-row.total {
            font-weight: 700;
            font-size: 1.1rem;
            color: #2e765e;
            padding-top: 0.5rem;
            border-top: 2px solid #2e765e;
            margin-top: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .orders-content {
                grid-template-columns: 1fr;
            }
            
            .order-header-row {
                grid-template-columns: 1fr;
            }
        }
        
        /* Wishlist Modal Styles */
        .wishlist-link {
            position: relative;
            color: white;
            text-decoration: none;
            font-size: 1.5rem;
            transition: color 0.3s;
        }
        
        .wishlist-link:hover {
            color: #ffd700;
        }
        
        .wishlist-count {
            position: absolute;
            top: -8px;
            right: -10px;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        .wishlist-modal {
            display: none;
            position: fixed;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 10000;
            animation: fadeIn 0.3s ease;
        }
        
        .wishlist-modal.active {
            display: block;
        }
        
        .wishlist-modal-content {
            position: fixed;
            right: 0;
            top: 0;
            height: 100%;
            width: 450px;
            max-width: 90%;
            background: white;
            box-shadow: -4px 0 20px rgba(0, 0, 0, 0.2);
            animation: slideInRight 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideInRight {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }
        
        .wishlist-header {
            background: linear-gradient(135deg, #2e765e, #3da180);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .wishlist-header h2 {
            margin: 0;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .close-wishlist {
            background: none;
            border: none;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            line-height: 1;
            transition: transform 0.2s;
        }
        
        .close-wishlist:hover {
            transform: scale(1.2);
        }
        
        .wishlist-body {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }
        
        .wishlist-empty {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .wishlist-empty i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .wishlist-item {
            display: flex;
            gap: 15px;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 12px;
            margin-bottom: 15px;
            transition: all 0.3s;
            background: white;
        }
        
        .wishlist-item:hover {
            box-shadow: 0 4px 12px rgba(46, 118, 94, 0.1);
            border-color: #2e765e;
        }
        
        .wishlist-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            background: #f8f8f8;
        }
        
        .wishlist-item-details {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .wishlist-item-name {
            font-weight: 600;
            color: #333;
            font-size: 0.95rem;
            line-height: 1.3;
        }
        
        .wishlist-item-price {
            color: #2e765e;
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        .wishlist-item-actions {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }
        
        .wishlist-add-to-cart {
            background: linear-gradient(135deg, #2e765e, #3da180);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .wishlist-add-to-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(46, 118, 94, 0.3);
        }
        
        .wishlist-remove {
            background: #f8f9fa;
            color: #e74c3c;
            border: 1px solid #e74c3c;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .wishlist-remove:hover {
            background: #e74c3c;
            color: white;
        }
        
        @media (max-width: 768px) {
            .wishlist-modal-content {
                width: 100%;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <header>
        <div class="top-navbar">
            <div class="brand">
                <a href="index.php" class="logo-btn">
                    <img src="Assets/Carousel_Picts/Logo.png" alt="Brand Logo" />
                </a>
            </div>

            <div class="search-wrapper">
                <i class="bi bi-search"></i>
                <input type="search" id="headerSearch" placeholder="Search products..." />
            </div>

            <div class="top-icons">
                <?php echo getAuthNavigationHTML(); ?>
                <a href="#" class="wishlist-link" onclick="toggleWishlistModal(); return false;">
                    <i class="bi bi-heart">
                        <span class="wishlist-count">0</span>
                    </i>
                </a>
                <a href="cart.php" class="cart-link">
                    <i class="bi bi-cart">
                        <span class="cart-count"><?php echo $cart_count; ?></span>
                    </i>
                </a>
            </div>
        </div>

        <!-- Bottom Navbar -->
        <div class="bottom-navbar">
            <nav>
                <a href="index.php">Home</a>
                <a href="ProductCatalog.php">Shop</a>
                <a href="#contact">Contact Us</a>
                <a href="#deals" class="best-deals">Best Deals</a>
                <a href="#about">About us</a>
            </nav>
        </div>
    </header>

    <div class="orders-container">
        <div class="orders-header">
            <h1><i class="bi bi-box"></i> My Orders</h1>
            <p>Track and manage your orders</p>
        </div>

        <div class="orders-content">
            <div class="orders-sidebar">
                <ul class="profile-nav">
                    <li><a href="profile.php"><i class="bi bi-person"></i> Profile Information</a></li>
                    <li><a href="orders.php" class="active"><i class="bi bi-box"></i> My Orders</a></li>
                    <li><a href="settings.php"><i class="bi bi-gear"></i> Account Settings</a></li>
                </ul>
                <div style="margin-top:2rem;">
                  <?php echo getAuthNavigationHTML(); ?>
                </div>
            </div>

            <div class="orders-main">
                <h2><i class="bi bi-clock-history"></i> Order History</h2>
                
                <?php if (empty($user_orders)): ?>
                    <!-- Empty State -->
                    <div class="empty-orders">
                        <i class="bi bi-box"></i>
                        <h3>No Orders Found</h3>
                        <p>You haven't placed any orders yet. Start shopping to see your orders here!</p>
                        <a href="ProductCatalog.php" class="shop-now-btn">
                            <i class="bi bi-bag-plus"></i> Start Shopping
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Orders List -->
                    <?php foreach ($user_orders as $order): 
                        // Get status badge class
                        $status_class = 'status-' . strtolower($order['status']);
                        $payment_class = 'payment-' . strtolower($order['payment_status']);
                        
                        // Format date
                        $order_date = date('M d, Y', strtotime($order['order_date']));
                        $order_time = date('h:i A', strtotime($order['order_date']));
                    ?>
                    <div class="order-card">
                        <div class="order-header-row">
                            <div class="order-info-block">
                                <span class="order-info-label">Order ID</span>
                                <span class="order-info-value order-id-value">
                                    #<?= htmlspecialchars($order['order_id'] ?? 'ORD-' . str_pad($order['id'], 6, '0', STR_PAD_LEFT)) ?>
                                </span>
                            </div>
                            
                            <div class="order-info-block">
                                <span class="order-info-label">Order Date</span>
                                <span class="order-info-value"><?= $order_date ?></span>
                                <span style="font-size: 0.8rem; color: #666;"><?= $order_time ?></span>
                            </div>
                            
                            <div class="order-info-block">
                                <span class="order-info-label">Order Status</span>
                                <span class="status-badge <?= $status_class ?>">
                                    <i class="bi bi-<?= getStatusIcon($order['status']) ?>"></i>
                                    <?= htmlspecialchars($order['status']) ?>
                                </span>
                            </div>
                            
                            <div class="order-info-block">
                                <span class="order-info-label">Payment</span>
                                <span class="status-badge <?= $payment_class ?>">
                                    <i class="bi bi-<?= getPaymentIcon($order['payment_status']) ?>"></i>
                                    <?= htmlspecialchars($order['payment_status']) ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="order-body">
                            <div class="order-products">
                                <strong><i class="bi bi-bag-check"></i> Items (<?= $order['total_items'] ?>):</strong>
                                <?= htmlspecialchars($order['products'] ?? 'No items') ?>
                            </div>
                            
                            <div class="order-summary">
                                <div class="order-summary-row">
                                    <span><i class="bi bi-truck"></i> Shipping Address:</span>
                                    <span><?= htmlspecialchars(substr($order['shipping_address'], 0, 50)) ?>...</span>
                                </div>
                                <?php if ($order['payment_method']): ?>
                                <div class="order-summary-row">
                                    <span><i class="bi bi-credit-card"></i> Payment Method:</span>
                                    <span><?= htmlspecialchars($order['payment_method']) ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="order-summary-row total">
                                    <span>Total Amount:</span>
                                    <span>₱<?= number_format($order['total_amount'], 2) ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="order-footer">
                            <div>
                                <small style="color: #666;">
                                    <i class="bi bi-info-circle"></i> 
                                    Last updated: <?= date('M d, Y h:i A', strtotime($order['updated_at'])) ?>
                                </small>
                            </div>
                            <a href="order_details.php?order_id=<?= $order['id'] ?>" class="view-details-btn">
                                <i class="bi bi-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- AUTH MODAL -->
    <?php include 'components/auth_modal.php'; ?>

    <!-- WISHLIST MODAL -->
    <div id="wishlistModal" class="wishlist-modal">
        <div class="wishlist-modal-content">
            <div class="wishlist-header">
                <h2><i class="bi bi-heart-fill"></i> My Wishlist</h2>
                <button class="close-wishlist" onclick="toggleWishlistModal()">&times;</button>
            </div>
            <div class="wishlist-body" id="wishlistBody">
                <!-- Wishlist items will be populated here -->
            </div>
        </div>
    </div>

    <script src="Js/user_dropdown.js"></script>
    <script src="Js/wishlist.js"></script>
    <script src="components/auth_modal_otp.js"></script>
    <script src="Js/JavaScript.js"></script>
    
    <script>
        // Header search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const headerSearch = document.getElementById('headerSearch');
            
            if (headerSearch) {
                headerSearch.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const searchTerm = this.value.trim();
                        if (searchTerm.length > 0) {
                            window.location.href = `ProductCatalog.php?search=${encodeURIComponent(searchTerm)}`;
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>