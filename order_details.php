<?php
require_once 'includes/user_auth.php';
require_once 'includes/db.php';

// Require login for this page
requireLogin();

$current_user = getCurrentUser();

// Initialize cart count
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['quantity'];
}

// Get order ID from URL
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// Fetch order details - SECURITY: Only if it belongs to the logged-in user
$order = null;
$order_items = [];

if ($order_id > 0 && isset($conn) && $conn && isset($current_user['id'])) {
    $user_id = $current_user['id'];
    
    // Get order details with security check
    $query = "SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    // If order exists and belongs to user, get order items
    if ($order) {
        $query = "SELECT oi.*, i.image as product_image 
                  FROM order_items oi
                  LEFT JOIN inventory i ON oi.product_id = i.id
                  WHERE oi.order_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $order_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $order_items[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
}

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

// If order not found or doesn't belong to user, redirect
if (!$order) {
    header('Location: orders.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - PeakPH Commerce</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="Css/Global.css">
    <style>
        .order-details-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #2e765e;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            gap: 0.75rem;
            color: #3da180;
        }
        
        .order-header {
            background: linear-gradient(135deg, #2e765e, #3da180);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }
        
        .order-header h1 {
            margin: 0 0 0.5rem 0;
            font-size: 1.8rem;
        }
        
        .order-header-info {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        
        .order-header-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .order-header-label {
            font-size: 0.85rem;
            opacity: 0.9;
        }
        
        .order-header-value {
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        
        .section-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .section-title {
            color: #2e765e;
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #2e765e;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        /* Status Timeline */
        .status-timeline {
            position: relative;
            padding-left: 2rem;
        }
        
        .timeline-item {
            position: relative;
            padding-bottom: 2rem;
        }
        
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        
        .timeline-dot {
            position: absolute;
            left: -2rem;
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }
        
        .timeline-dot.active {
            background: #2e765e;
            color: white;
        }
        
        .timeline-dot.inactive {
            background: #e0e0e0;
            color: #999;
        }
        
        .timeline-line {
            position: absolute;
            left: -1.5rem;
            top: 2rem;
            width: 2px;
            height: calc(100% - 2rem);
            background: #e0e0e0;
        }
        
        .timeline-content h4 {
            margin: 0 0 0.25rem 0;
            color: #333;
        }
        
        .timeline-content p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        /* Order Items */
        .order-item {
            display: grid;
            grid-template-columns: 80px 1fr auto;
            gap: 1rem;
            padding: 1rem;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 1rem;
            align-items: center;
        }
        
        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            background: #f5f5f5;
        }
        
        .item-info h4 {
            margin: 0 0 0.5rem 0;
            color: #333;
            font-size: 1rem;
        }
        
        .item-details {
            color: #666;
            font-size: 0.9rem;
        }
        
        .item-price {
            text-align: right;
            font-weight: 600;
            color: #2e765e;
        }
        
        .item-total {
            font-size: 1.2rem;
        }
        
        /* Order Summary */
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .summary-row.total {
            border-bottom: none;
            border-top: 2px solid #2e765e;
            font-size: 1.3rem;
            font-weight: 700;
            color: #2e765e;
            margin-top: 0.5rem;
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
        
        .status-pending { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .status-processing { background: #cfe2ff; color: #084298; border: 1px solid #b6d4fe; }
        .status-shipped { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .status-delivered { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .status-cancelled { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .payment-unpaid { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .payment-pending { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .payment-paid { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .payment-failed { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .payment-refunded { background: #e2e3e5; color: #383d41; border: 1px solid #d6d8db; }
        
        .info-row {
            padding: 0.75rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
        }
        
        .info-value {
            color: #333;
        }
        
        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .order-item {
                grid-template-columns: 60px 1fr;
            }
            
            .item-price {
                grid-column: 2;
                text-align: left;
                margin-top: 0.5rem;
            }
        }
        
        /* Wishlist Styles */
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

    <div class="order-details-container">
        <a href="orders.php" class="back-link">
            <i class="bi bi-arrow-left"></i> Back to My Orders
        </a>

        <div class="order-header">
            <h1><i class="bi bi-receipt"></i> Order Details</h1>
            <div class="order-header-info">
                <div class="order-header-item">
                    <span class="order-header-label">Order ID</span>
                    <span class="order-header-value">#<?= htmlspecialchars($order['order_id'] ?? 'ORD-' . str_pad($order['id'], 6, '0', STR_PAD_LEFT)) ?></span>
                </div>
                <div class="order-header-item">
                    <span class="order-header-label">Order Date</span>
                    <span class="order-header-value"><?= date('F d, Y', strtotime($order['order_date'])) ?></span>
                </div>
                <div class="order-header-item">
                    <span class="order-header-label">Status</span>
                    <span class="status-badge status-<?= strtolower($order['status']) ?>">
                        <i class="bi bi-<?= getStatusIcon($order['status']) ?>"></i>
                        <?= htmlspecialchars($order['status']) ?>
                    </span>
                </div>
                <div class="order-header-item">
                    <span class="order-header-label">Payment Status</span>
                    <span class="status-badge payment-<?= strtolower($order['payment_status']) ?>">
                        <i class="bi bi-<?= getPaymentIcon($order['payment_status']) ?>"></i>
                        <?= htmlspecialchars($order['payment_status']) ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="content-grid">
            <div>
                <!-- Order Items -->
                <div class="section-card">
                    <h2 class="section-title">
                        <i class="bi bi-bag-check"></i> Order Items
                    </h2>
                    
                    <?php foreach ($order_items as $item): ?>
                    <div class="order-item">
                        <?php if ($item['product_image']): ?>
                            <img src="<?= htmlspecialchars($item['product_image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" class="item-image">
                        <?php else: ?>
                            <div class="item-image" style="display: flex; align-items: center; justify-content: center; color: #999;">
                                <i class="bi bi-image" style="font-size: 2rem;"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="item-info">
                            <h4><?= htmlspecialchars($item['product_name']) ?></h4>
                            <div class="item-details">
                                <span>Price: ₱<?= number_format($item['price'], 2) ?></span> × 
                                <span>Qty: <?= $item['quantity'] ?></span>
                            </div>
                        </div>
                        
                        <div class="item-price">
                            <div class="item-total">₱<?= number_format($item['total'], 2) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Shipping & Billing Info -->
                <div class="section-card">
                    <h2 class="section-title">
                        <i class="bi bi-truck"></i> Shipping & Billing Information
                    </h2>
                    
                    <div class="info-row">
                        <div class="info-label"><i class="bi bi-geo-alt"></i> Shipping Address</div>
                        <div class="info-value"><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></div>
                    </div>
                    
                    <?php if ($order['billing_address']): ?>
                    <div class="info-row">
                        <div class="info-label"><i class="bi bi-receipt"></i> Billing Address</div>
                        <div class="info-value"><?= nl2br(htmlspecialchars($order['billing_address'])) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="info-row">
                        <div class="info-label"><i class="bi bi-person"></i> Customer Name</div>
                        <div class="info-value"><?= htmlspecialchars($order['customer_name']) ?></div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label"><i class="bi bi-envelope"></i> Email</div>
                        <div class="info-value"><?= htmlspecialchars($order['customer_email']) ?></div>
                    </div>
                    
                    <?php if ($order['customer_phone']): ?>
                    <div class="info-row">
                        <div class="info-label"><i class="bi bi-telephone"></i> Phone</div>
                        <div class="info-value"><?= htmlspecialchars($order['customer_phone']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <!-- Order Status Timeline -->
                <div class="section-card">
                    <h2 class="section-title">
                        <i class="bi bi-clock-history"></i> Order Tracking
                    </h2>
                    
                    <div class="status-timeline">
                        <?php
                        $statuses = ['Pending', 'Processing', 'Shipped', 'Delivered'];
                        $current_status = $order['status'];
                        $current_index = array_search($current_status, $statuses);
                        
                        if ($current_status === 'Cancelled') {
                            $current_index = -1;
                        }
                        
                        foreach ($statuses as $index => $status):
                            $is_active = $index <= $current_index;
                            $icon = getStatusIcon($status);
                        ?>
                        <div class="timeline-item">
                            <?php if ($index < count($statuses) - 1): ?>
                            <div class="timeline-line"></div>
                            <?php endif; ?>
                            
                            <div class="timeline-dot <?= $is_active ? 'active' : 'inactive' ?>">
                                <i class="bi bi-<?= $icon ?>"></i>
                            </div>
                            
                            <div class="timeline-content">
                                <h4><?= $status ?></h4>
                                <?php if ($is_active): ?>
                                    <p><?= $status === $current_status ? 'Current Status' : 'Completed' ?></p>
                                <?php else: ?>
                                    <p>Pending</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if ($current_status === 'Cancelled'): ?>
                        <div class="timeline-item">
                            <div class="timeline-dot" style="background: #dc3545; color: white;">
                                <i class="bi bi-x-circle"></i>
                            </div>
                            <div class="timeline-content">
                                <h4>Order Cancelled</h4>
                                <p>This order has been cancelled</p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="section-card">
                    <h2 class="section-title">
                        <i class="bi bi-calculator"></i> Order Summary
                    </h2>
                    
                    <?php
                    $subtotal = 0;
                    foreach ($order_items as $item) {
                        $subtotal += $item['total'];
                    }
                    $shipping = 0; // Can be dynamic
                    $total = $order['total_amount'];
                    ?>
                    
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>₱<?= number_format($subtotal, 2) ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span><?= $shipping > 0 ? '₱' . number_format($shipping, 2) : 'FREE' ?></span>
                    </div>
                    
                    <?php if ($order['payment_method']): ?>
                    <div class="summary-row">
                        <span>Payment Method</span>
                        <span><?= htmlspecialchars($order['payment_method']) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>₱<?= number_format($total, 2) ?></span>
                    </div>
                </div>
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
        // Initialize wishlist button states
        document.addEventListener('DOMContentLoaded', function() {
            initWishlistButtons();
            
            // Header search functionality
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
        
        // Wishlist functionality - Initialize buttons
        function initWishlistButtons() {
            const wishlistButtons = document.querySelectorAll('.wishlist-btn');
            const wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
            
            // Mark already wishlisted items
            wishlistButtons.forEach(btn => {
                const productId = btn.getAttribute('onclick')?.match(/\d+/)?.[0];
                if (productId && wishlist.includes(productId)) {
                    btn.classList.add('active');
                    const icon = btn.querySelector('i');
                    if (icon) {
                        icon.classList.remove('bi-heart');
                        icon.classList.add('bi-heart-fill');
                    }
                }
            });
        }
        
        // Wishlist functionality
        function toggleWishlist(productId) {
            const btn = event.target.closest('.wishlist-btn');
            const icon = btn.querySelector('i');
            let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
            const productIdStr = String(productId);
            
            if (wishlist.includes(productIdStr)) {
                wishlist = wishlist.filter(id => id !== productIdStr);
                btn.classList.remove('active');
                icon.classList.remove('bi-heart-fill');
                icon.classList.add('bi-heart');
                showMessage('Removed from wishlist', 'info');
            } else {
                wishlist.push(productIdStr);
                btn.classList.add('active');
                icon.classList.remove('bi-heart');
                icon.classList.add('bi-heart-fill');
                showMessage('Added to wishlist!', 'success');
            }
            
            localStorage.setItem('wishlist', JSON.stringify(wishlist));
            if (typeof updateWishlistCount === 'function') {
                updateWishlistCount();
            }
        }
        
        // Show message function
        function showMessage(message, type = 'info') {
            const existingMessages = document.querySelectorAll('.temp-message');
            existingMessages.forEach(msg => msg.remove());
            
            const messageDiv = document.createElement('div');
            messageDiv.className = `temp-message temp-message-${type}`;
            messageDiv.textContent = message;
            
            messageDiv.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 12px 20px;
                border-radius: 8px;
                color: white;
                font-weight: 500;
                z-index: 10000;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                transform: translateX(100%);
                transition: transform 0.3s ease;
                max-width: 300px;
            `;
            
            if (type === 'success') {
                messageDiv.style.background = 'linear-gradient(135deg, #27ae60, #2ecc71)';
            } else if (type === 'error') {
                messageDiv.style.background = 'linear-gradient(135deg, #e74c3c, #c0392b)';
            } else {
                messageDiv.style.background = 'linear-gradient(135deg, #3498db, #2980b9)';
            }
            
            document.body.appendChild(messageDiv);
            
            setTimeout(() => {
                messageDiv.style.transform = 'translateX(0)';
            }, 100);
            
            setTimeout(() => {
                messageDiv.style.transform = 'translateX(100%)';
                setTimeout(() => messageDiv.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>
