<?php
require_once 'includes/user_auth.php';

// Check if order confirmation data exists
if (!isset($_SESSION['order_confirmation'])) {
    header('Location: index.php');
    exit();
}

$order_data = $_SESSION['order_confirmation'];
$order_id = $order_data['order_id'];
$customer_name = $order_data['customer_name'];
$customer_email = $order_data['customer_email'];
$total = $order_data['total'];
$items = $order_data['items'];
$order_date = $order_data['order_date'];
$map_location = isset($order_data['map_location']) ? $order_data['map_location'] : null;
$shipping_address = $order_data['shipping_address'];

// Clear the confirmation data from session after displaying
unset($_SESSION['order_confirmation']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - PeakPH Commerce</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="Css/Global.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f8fffe, #f0f9f7);
            min-height: 100vh;
        }
        
        .confirmation-hero {
            background: linear-gradient(135deg, #2e765e, #3da180);
            color: white;
            padding: 80px 0;
            text-align: center;
            box-shadow: 0 4px 20px rgba(46, 118, 94, 0.3);
        }
        
        .confirmation-hero h1 {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .confirmation-hero p {
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
        }
        
        .confirmation-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(46, 118, 94, 0.15);
            margin: -40px auto 0;
            max-width: 900px;
            overflow: hidden;
            border: 2px solid rgba(46, 118, 94, 0.1);
        }
        
        .order-summary {
            background: linear-gradient(135deg, #f8fffe, #f0f9f7);
            border-radius: 15px;
            padding: 25px;
            margin: 25px 0;
            border: 2px solid rgba(46, 118, 94, 0.1);
            box-shadow: 0 4px 15px rgba(46, 118, 94, 0.1);
        }
        
        .order-summary h5 {
            color: #2e765e;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 1.2rem;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 2px solid rgba(46, 118, 94, 0.1);
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 12px;
            margin-right: 20px;
            border: 2px solid rgba(46, 118, 94, 0.2);
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-weight: 600;
            margin-bottom: 8px;
            color: #2e765e;
            font-family: 'Poppins', sans-serif;
        }
        
        .item-quantity {
            color: #3da180;
            font-size: 0.9em;
            font-weight: 500;
            font-family: 'Poppins', sans-serif;
        }
        
        .item-price {
            font-weight: 600;
            color: #2e765e;
            font-size: 1.1rem;
            font-family: 'Poppins', sans-serif;
        }
        
        .total-section {
            background: linear-gradient(135deg, #2e765e, #3da180);
            border-radius: 15px;
            padding: 25px;
            margin-top: 25px;
            color: white;
            box-shadow: 0 6px 20px rgba(46, 118, 94, 0.3);
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
        }
        
        .total-row.final {
            font-size: 1.3em;
            font-weight: 700;
            border-top: 2px solid rgba(255,255,255,0.3);
            padding-top: 20px;
            margin-top: 20px;
            font-family: 'Poppins', sans-serif;
        }
        
        .action-buttons {
            text-align: center;
            padding: 40px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #2e765e, #3da180);
            border: none;
            padding: 15px 35px;
            font-weight: 600;
            margin: 0 15px;
            border-radius: 25px;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 4px 15px rgba(46, 118, 94, 0.3);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(46, 118, 94, 0.4);
        }
        
        .btn-outline-primary {
            color: #2e765e;
            border: 2px solid #2e765e;
            background: transparent;
            padding: 15px 35px;
            font-weight: 600;
            margin: 0 15px;
            border-radius: 25px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }
        
        .btn-outline-primary:hover {
            background: linear-gradient(135deg, #2e765e, #3da180);
            border-color: #2e765e;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(46, 118, 94, 0.4);
        }
        
        .order-info {
            background: linear-gradient(135deg, #fff8e1, #f3e5ab);
            border: 2px solid rgba(46, 118, 94, 0.2);
            border-radius: 15px;
            padding: 25px;
            margin: 25px 0;
            box-shadow: 0 4px 15px rgba(46, 118, 94, 0.1);
        }
        
        .order-info h5 {
            color: #2e765e;
            margin-bottom: 20px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        .order-info p {
            color: #2e765e;
            margin-bottom: 8px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            line-height: 1.5;
        }
        
        .order-info strong {
            font-weight: 600;
        }
        
        /* Map Styles - PeakPH Theme */
        .delivery-map {
            background: linear-gradient(135deg, #f8fffe, #f0f9f7);
            border: 2px solid rgba(46, 118, 94, 0.2);
            border-radius: 15px;
            padding: 25px;
            margin: 25px 0;
            box-shadow: 0 6px 20px rgba(46, 118, 94, 0.1);
        }
        
        .delivery-map h5 {
            color: #2e765e;
            margin-bottom: 20px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .map-container-small {
            height: 250px;
            border-radius: 12px;
            overflow: hidden;
            border: 3px solid #2e765e;
            margin-bottom: 15px;
            box-shadow: 0 4px 15px rgba(46, 118, 94, 0.2);
        }
        
        .location-details {
            background: white;
            border-radius: 12px;
            padding: 20px;
            border: 2px solid rgba(46, 118, 94, 0.1);
            box-shadow: 0 3px 10px rgba(46, 118, 94, 0.1);
        }
        
        .location-details p {
            margin: 8px 0;
            color: #2e765e;
            font-family: 'Poppins', sans-serif;
            line-height: 1.5;
        }
        
        .location-details strong {
            color: #2e765e;
            font-weight: 600;
        }
        
        .coordinates-display {
            font-family: 'Courier New', monospace;
            background: linear-gradient(135deg, #2e765e, #3da180);
            color: white;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.85em;
            font-weight: 500;
            text-align: center;
            margin-top: 10px;
        }
        
        /* Header Styles */
        header {
            background: linear-gradient(135deg, #2e765e, #3da180);
            color: white;
            box-shadow: 0 4px 12px rgba(46, 118, 94, 0.2);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .top-navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
        }
        
        .brand {
            display: flex;
            align-items: center;
        }
        
        .logo-btn {
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0;
        }
        
        .logo-btn img {
            height: 50px;
            width: auto;
        }
        
        .top-icons {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }
        
        .wishlist-link, .cart-link {
            position: relative;
            color: white;
            text-decoration: none;
            font-size: 1.5rem;
            transition: color 0.3s;
        }
        
        .wishlist-link:hover, .cart-link:hover {
            color: #ffd700;
        }
        
        .wishlist-count, .cart-count {
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
        
        /* Wishlist Modal Styles */
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
    </style>
</head>
<body>
    <?php
    require_once 'includes/user_auth.php';
    $cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
    ?>
    
    <!-- HEADER -->
    <header>
        <div class="top-navbar">
            <div class="brand">
                <a href="index.php" class="logo-btn">
                    <img src="Assets/Carousel_Picts/Logo.png" alt="Brand Logo" />
                </a>
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
    </header>

    <!-- Confirmation Hero Section -->
    <div class="confirmation-hero">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <i class="fas fa-check-circle fa-5x mb-3"></i>
                    <h1 class="display-4 mb-3">Order Confirmed!</h1>
                    <p class="lead">Thank you for your purchase. Your order has been successfully placed.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Details Card -->
    <div class="container">
        <div class="confirmation-card">
            <div class="card-body p-4">
                <!-- Order Information -->
                <div class="order-info">
                    <h5><i class="fas fa-info-circle me-2"></i>Order Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order_id); ?></p>
                            <p><strong>Order Date:</strong> <?php echo date('F j, Y \a\t g:i A', strtotime($order_date)); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Customer:</strong> <?php echo htmlspecialchars($customer_name); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($customer_email); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Delivery Location -->
                <?php if ($map_location): ?>
                <div class="delivery-map">
                    <h5><i class="fas fa-map-marker-alt me-2"></i>Delivery Location</h5>
                    <div class="row">
                        <div class="col-md-8">
                            <div id="delivery-map" class="map-container-small"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="location-details">
                                <p><strong>Pinned Address:</strong></p>
                                <p><?php echo htmlspecialchars($map_location['address']); ?></p>
                                <p class="coordinates-display">
                                    Lat: <?php echo number_format($map_location['latitude'], 6); ?><br>
                                    Lng: <?php echo number_format($map_location['longitude'], 6); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Shipping Address -->
                <div class="order-info" style="background: linear-gradient(135deg, #e8f5f0, #d4edda); border-color: rgba(46, 118, 94, 0.3);">
                    <h5 style="color: #2e765e;"><i class="fas fa-shipping-fast me-2"></i>Shipping Address</h5>
                    <p style="color: #2e765e;"><?php echo htmlspecialchars($shipping_address); ?></p>
                </div>

                <!-- Order Summary -->
                <div class="order-summary">
                    <h5 class="mb-3"><i class="fas fa-shopping-bag me-2"></i>Order Summary</h5>
                    
                    <?php foreach ($items as $item): ?>
                    <div class="order-item">
                        <div class="d-flex align-items-center">
                            <?php if (!empty($item['image'])): ?>
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="item-image">
                            <?php else: ?>
                                <div class="item-image bg-light d-flex align-items-center justify-content-center">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div class="item-details">
                                <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="item-quantity">Quantity: <?php echo $item['quantity']; ?></div>
                            </div>
                        </div>
                        <div class="item-price">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Total Section -->
                <div class="total-section">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span>₱<?php echo number_format($total - 50 - ($total * 0.12), 2); ?></span>
                    </div>
                    <div class="total-row">
                        <span>Shipping Fee:</span>
                        <span>₱50.00</span>
                    </div>
                    <div class="total-row">
                        <span>Tax (12% VAT):</span>
                        <span>₱<?php echo number_format(($total - 50) * 0.12, 2); ?></span>
                    </div>
                    <div class="total-row final">
                        <span>Total:</span>
                        <span>₱<?php echo number_format($total, 2); ?></span>
                    </div>
                </div>

                <!-- Next Steps -->
                <div class="order-info" style="background: linear-gradient(135deg, #f0f9f7, #e8f5f0); border-color: rgba(46, 118, 94, 0.3);">
                    <h5 style="color: #2e765e;"><i class="fas fa-clock me-2"></i>What's Next?</h5>
                    <p style="color: #2e765e;">• You will receive an order confirmation email shortly</p>
                    <p style="color: #2e765e;">• We will process your order within 1-2 business days</p>
                    <p style="color: #2e765e;">• You will receive a tracking number once your order ships</p>
                    <p style="color: #2e765e;">• Estimated delivery time: 3-7 business days</p>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i>Continue Shopping
                    </a>
                    <a href="mailto:<?php echo htmlspecialchars($customer_email); ?>" class="btn btn-outline-primary">
                        <i class="fas fa-envelope me-2"></i>Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Spacing -->
    <div style="height: 100px;"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-hide any Bootstrap alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Print order functionality
        function printOrder() {
            window.print();
        }

        // Email order functionality
        function emailOrder() {
            const subject = encodeURIComponent('Order Confirmation - ' + '<?php echo $order_id; ?>');
            const body = encodeURIComponent('Thank you for your order! Order ID: <?php echo $order_id; ?>');
            window.location.href = 'mailto:<?php echo htmlspecialchars($customer_email); ?>?subject=' + subject + '&body=' + body;
        }

        <?php if ($map_location): ?>
        // Initialize delivery location map
        document.addEventListener('DOMContentLoaded', function() {
            const lat = <?php echo $map_location['latitude']; ?>;
            const lng = <?php echo $map_location['longitude']; ?>;
            
            // Create map
            const deliveryMap = L.map('delivery-map').setView([lat, lng], 15);
            
            // Add tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(deliveryMap);
            
            // Add marker for delivery location
            const deliveryMarker = L.marker([lat, lng]).addTo(deliveryMap);
            deliveryMarker.bindPopup('<b>Delivery Location</b><br><?php echo addslashes($map_location['address']); ?>').openPopup();
            
            // Disable interactions for a cleaner look
            deliveryMap.dragging.disable();
            deliveryMap.touchZoom.disable();
            deliveryMap.doubleClickZoom.disable();
            deliveryMap.scrollWheelZoom.disable();
            deliveryMap.boxZoom.disable();
            deliveryMap.keyboard.disable();
            
            // Add click handler to enable interactions
            deliveryMap.on('click', function() {
                deliveryMap.dragging.enable();
                deliveryMap.touchZoom.enable();
                deliveryMap.doubleClickZoom.enable();
                deliveryMap.scrollWheelZoom.enable();
                deliveryMap.boxZoom.enable();
                deliveryMap.keyboard.enable();
            });
        });
        <?php endif; ?>
    </script>
    
    <!-- Leaflet.js Library -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
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
    
    <script src="Js/wishlist.js"></script>
    <script src="Js/user_dropdown.js"></script>
    <script src="components/auth_modal_otp.js"></script>
    
    <script>
        // Initialize wishlist
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof updateWishlistCount === 'function') {
                updateWishlistCount();
            }
        });
    </script>
</body>
</html>