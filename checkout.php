<?php
require_once 'includes/user_auth.php';
require_once 'includes/db.php';

// Redirect if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// Calculate totals
$subtotal = 0;
$item_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $item_count += $item['quantity'];
}

$shipping_fee = 50.00;
$tax_rate = 0.12; // 12% VAT
$tax_amount = $subtotal * $tax_rate;
$total = $subtotal + $shipping_fee + $tax_amount;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - PeakPH</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="Assets/Carousel_Picts/Logo.png" />
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="Css/Global.css">
    <style>
        .checkout-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .checkout-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .checkout-header h1 {
            color: #2e765e;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .checkout-content {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
        }
        
        .checkout-form {
            background: #fff;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .form-section {
            margin-bottom: 2rem;
        }
        
        .form-section h3 {
            color: #2e765e;
            font-size: 1.3rem;
            margin-bottom: 1rem;
            border-bottom: 2px solid #2e765e;
            padding-bottom: 0.5rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2e765e;
            box-shadow: 0 0 0 3px rgba(46, 118, 94, 0.1);
        }
        
        .required {
            color: #e74c3c;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            margin: 1rem 0;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin-right: 0.5rem;
        }
        
        .order-summary {
            background: #fff;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }
        
        .order-summary h3 {
            color: #2e765e;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #2e765e;
            padding-bottom: 0.5rem;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .order-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 1rem;
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .item-details {
            font-size: 0.9rem;
            color: #666;
        }
        
        .item-price {
            font-weight: 600;
            color: #2e765e;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
        }
        
        .summary-divider {
            border-top: 1px solid #eee;
            margin: 1rem 0;
        }
        
        .total-row {
            font-weight: 700;
            font-size: 1.2rem;
            color: #2e765e;
            border-top: 2px solid #2e765e;
            padding-top: 1rem;
            margin-top: 1rem;
        }
        
        .payment-methods {
            margin: 1rem 0;
        }
        
        .payment-option {
            display: flex;
            align-items: center;
            padding: 1rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }
        
        .payment-option:hover {
            border-color: #2e765e;
        }
        
        .payment-option.selected {
            border-color: #2e765e;
            background: rgba(46, 118, 94, 0.05);
        }
        
        .payment-option input[type="radio"] {
            margin-right: 0.75rem;
        }
        
        .payment-icon {
            font-size: 1.5rem;
            margin-right: 0.5rem;
        }
        
        .place-order-btn {
            width: 100%;
            background: #2e765e;
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1rem;
            transition: background-color 0.3s ease;
        }
        
        .place-order-btn:hover {
            background: #245d4b;
        }
        
        .place-order-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .back-to-cart {
            display: inline-flex;
            align-items: center;
            color: #2e765e;
            text-decoration: none;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        
        .back-to-cart:hover {
            color: #245d4b;
            text-decoration: none;
        }
        
        .back-to-cart i {
            margin-right: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .checkout-content {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .order-summary {
                position: static;
            }
        }
        
        .security-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            margin: 1rem 0;
        }
        
        .security-info i {
            color: #28a745;
            margin-right: 0.5rem;
        }
        
        /* Map Styles - PeakPH Theme */
        .map-section {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(46, 118, 94, 0.1);
            padding: 2rem;
            margin-bottom: 1.5rem;
            border: 2px solid rgba(46, 118, 94, 0.1);
            transition: all 0.3s ease;
        }
        
        .map-section:hover {
            box-shadow: 0 8px 25px rgba(46, 118, 94, 0.15);
            border-color: rgba(46, 118, 94, 0.2);
        }
        
        .map-container {
            height: 400px;
            border-radius: 12px;
            overflow: hidden;
            border: 3px solid #2e765e;
            margin-bottom: 1rem;
            box-shadow: 0 4px 15px rgba(46, 118, 94, 0.2);
        }
        
        .map-controls {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        
        .map-btn {
            background: linear-gradient(135deg, #2e765e, #3da180);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(46, 118, 94, 0.3);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .map-btn:hover {
            background: linear-gradient(135deg, #245a47, #2e765e);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(46, 118, 94, 0.4);
        }
        
        .map-btn.secondary {
            background: linear-gradient(135deg, #6c757d, #8a9196);
        }
        
        .map-btn.secondary:hover {
            background: linear-gradient(135deg, #545b62, #6c757d);
        }
        
        .location-info {
            background: linear-gradient(135deg, #f8fffe, #f0f9f7);
            border: 2px solid rgba(46, 118, 94, 0.2);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1rem;
            box-shadow: 0 3px 10px rgba(46, 118, 94, 0.1);
        }
        
        .location-info h4 {
            color: #2e765e;
            margin-bottom: 1rem;
            font-size: 1.1rem;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .location-info p {
            margin: 0.5rem 0;
            font-size: 0.95rem;
            color: #2e765e;
            line-height: 1.5;
        }
        
        .coordinates {
            font-family: 'Courier New', monospace;
            background: linear-gradient(135deg, #2e765e, #3da180);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            text-align: center;
            margin-top: 0.5rem;
        }
        
        .map-toggle {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #f8fffe, #f0f9f7);
            padding: 1rem 1.5rem;
            border-radius: 12px;
            border: 2px solid rgba(46, 118, 94, 0.1);
        }
        
        .map-toggle input[type="checkbox"] {
            margin-right: 1rem;
            transform: scale(1.2);
            accent-color: #2e765e;
        }
        
        .map-toggle label {
            cursor: pointer;
            color: #2e765e;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .map-toggle:hover {
            border-color: rgba(46, 118, 94, 0.3);
            background: linear-gradient(135deg, #f0f9f7, #e8f5f0);
        }
        
        #search-input {
            margin-bottom: 1rem;
        }
        
        #location-search {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid rgba(46, 118, 94, 0.3);
            border-radius: 25px;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            background: #f8fffe;
        }
        
        #location-search:focus {
            outline: none;
            border-color: #2e765e;
            background: white;
            box-shadow: 0 0 0 3px rgba(46, 118, 94, 0.1);
        }
        
        #location-search::placeholder {
            color: #8a9196;
            font-style: italic;
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
                        <span class="cart-count"><?php echo $item_count; ?></span>
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

    <div class="checkout-container">
        <div class="checkout-header">
            <a href="cart.php" class="back-to-cart">
                <i class="bi bi-arrow-left"></i> Back to Cart
            </a>
            <h1>Checkout</h1>
            <p>Complete your order - <?php echo $item_count; ?> item(s) in your cart</p>
        </div>

        <div class="checkout-content">
            <div class="checkout-form">
                <form id="checkoutForm" action="api/process_checkout.php" method="POST">
                    <!-- Contact Information -->
                    <div class="form-section">
                        <h3><i class="bi bi-person"></i> Contact Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name <span class="required">*</span></label>
                                <input type="text" id="first_name" name="first_name" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name <span class="required">*</span></label>
                                <input type="text" id="last_name" name="last_name" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email Address <span class="required">*</span></label>
                                <input type="email" id="email" name="email" value="<?= $current_user['email'] ?? '' ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number <span class="required">*</span></label>
                                <input type="tel" id="phone" name="phone" required>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Address -->
                    <div class="form-section">
                        <h3><i class="bi bi-truck"></i> Shipping Address</h3>
                        <div class="form-group">
                            <label for="shipping_address">Street Address <span class="required">*</span></label>
                            <input type="text" id="shipping_address" name="shipping_address" placeholder="House number, street name" required>
                        </div>
                        <div class="form-group">
                            <label for="shipping_address_2">Apartment, suite, etc. (optional)</label>
                            <input type="text" id="shipping_address_2" name="shipping_address_2" placeholder="Apartment, suite, unit, building, floor, etc.">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="shipping_city">City <span class="required">*</span></label>
                                <input type="text" id="shipping_city" name="shipping_city" required>
                            </div>
                            <div class="form-group">
                                <label for="shipping_province">Province <span class="required">*</span></label>
                                <select id="shipping_province" name="shipping_province" required>
                                    <option value="">Select Province</option>
                                    <option value="Metro Manila">Metro Manila</option>
                                    <option value="Cebu">Cebu</option>
                                    <option value="Davao">Davao</option>
                                    <option value="Laguna">Laguna</option>
                                    <option value="Cavite">Cavite</option>
                                    <option value="Bulacan">Bulacan</option>
                                    <option value="Rizal">Rizal</option>
                                    <option value="Batangas">Batangas</option>
                                    <option value="Pampanga">Pampanga</option>
                                    <option value="Nueva Ecija">Nueva Ecija</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="shipping_postal_code">Postal Code <span class="required">*</span></label>
                                <input type="text" id="shipping_postal_code" name="shipping_postal_code" required>
                            </div>
                            <div class="form-group">
                                <label for="shipping_country">Country</label>
                                <input type="text" id="shipping_country" name="shipping_country" value="Philippines" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Map Location Selector -->
                    <div class="form-section map-section">
                        <div class="map-toggle">
                            <input type="checkbox" id="use_map_location" name="use_map_location" onchange="toggleMapLocation()">
                            <label for="use_map_location">
                                <i class="bi bi-geo-alt"></i> Pin my exact delivery location on map
                            </label>
                        </div>
                        
                        <div id="map-content" style="display: none;">
                            <div class="map-controls">
                                <button type="button" class="map-btn" onclick="getCurrentLocation()">
                                    <i class="bi bi-crosshair"></i> Use My Location
                                </button>
                                <button type="button" class="map-btn secondary" onclick="searchLocation()">
                                    <i class="bi bi-search"></i> Search Address
                                </button>
                                <button type="button" class="map-btn secondary" onclick="clearMapSelection()">
                                    <i class="bi bi-x-circle"></i> Clear Selection
                                </button>
                            </div>
                            
                            <div id="search-input" style="display: none; margin-bottom: 1rem;">
                                <input type="text" id="location-search" placeholder="Search for a location..." style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                                <button type="button" class="map-btn" onclick="performSearch()" style="margin-top: 0.5rem;">Search</button>
                            </div>
                            
                            <div id="map" class="map-container"></div>
                            
                            <div id="location-info" class="location-info" style="display: none;">
                                <h4><i class="bi bi-pin-map"></i> Selected Location</h4>
                                <p id="selected-address">No location selected</p>
                                <p class="coordinates" id="selected-coordinates">Lat: -, Lng: -</p>
                                <input type="hidden" id="map_latitude" name="map_latitude">
                                <input type="hidden" id="map_longitude" name="map_longitude">
                                <input type="hidden" id="map_address" name="map_address">
                            </div>
                        </div>
                    </div>

                    <!-- Billing Address -->
                    <div class="form-section">
                        <h3><i class="bi bi-credit-card"></i> Billing Address</h3>
                        <div class="checkbox-group">
                            <input type="checkbox" id="same_as_shipping" name="same_as_shipping" checked onchange="toggleBillingAddress()">
                            <label for="same_as_shipping">Same as shipping address</label>
                        </div>
                        
                        <div id="billing_address_section" style="display: none;">
                            <div class="form-group">
                                <label for="billing_address">Street Address <span class="required">*</span></label>
                                <input type="text" id="billing_address" name="billing_address" placeholder="House number, street name">
                            </div>
                            <div class="form-group">
                                <label for="billing_address_2">Apartment, suite, etc. (optional)</label>
                                <input type="text" id="billing_address_2" name="billing_address_2" placeholder="Apartment, suite, unit, building, floor, etc.">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="billing_city">City <span class="required">*</span></label>
                                    <input type="text" id="billing_city" name="billing_city">
                                </div>
                                <div class="form-group">
                                    <label for="billing_province">Province <span class="required">*</span></label>
                                    <select id="billing_province" name="billing_province">
                                        <option value="">Select Province</option>
                                        <option value="Metro Manila">Metro Manila</option>
                                        <option value="Cebu">Cebu</option>
                                        <option value="Davao">Davao</option>
                                        <option value="Laguna">Laguna</option>
                                        <option value="Cavite">Cavite</option>
                                        <option value="Bulacan">Bulacan</option>
                                        <option value="Rizal">Rizal</option>
                                        <option value="Batangas">Batangas</option>
                                        <option value="Pampanga">Pampanga</option>
                                        <option value="Nueva Ecija">Nueva Ecija</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="billing_postal_code">Postal Code <span class="required">*</span></label>
                                    <input type="text" id="billing_postal_code" name="billing_postal_code">
                                </div>
                                <div class="form-group">
                                    <label for="billing_country">Country</label>
                                    <input type="text" id="billing_country" name="billing_country" value="Philippines" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="form-section">
                        <h3><i class="bi bi-credit-card-2-front"></i> Payment Method</h3>
                        <div class="payment-methods">
                            <div class="payment-option selected" onclick="selectPayment(this, 'cod')">
                                <input type="radio" name="payment_method" value="cod" checked>
                                <span class="payment-icon">💵</span>
                                <div>
                                    <strong>Cash on Delivery</strong>
                                    <small style="display: block; color: #666;">Pay when your order arrives</small>
                                </div>
                            </div>
                            <div class="payment-option" onclick="selectPayment(this, 'paymongo_gcash')">
                                <input type="radio" name="payment_method" value="paymongo_gcash">
                                <span class="payment-icon">📱</span>
                                <div>
                                    <strong>GCash (PayMongo)</strong>
                                    <small style="display: block; color: #666;">Pay instantly with GCash - 3.5% + ₱15 fee</small>
                                </div>
                            </div>
                            <div class="payment-option" onclick="selectPayment(this, 'paymongo_card')">
                                <input type="radio" name="payment_method" value="paymongo_card">
                                <span class="payment-icon">💳</span>
                                <div>
                                    <strong>Credit/Debit Card</strong>
                                    <small style="display: block; color: #666;">Visa, Mastercard, JCB - 3.5% + ₱15 fee</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Payment Fee Notice -->
                        <div id="payment-fee-notice" class="payment-fee-notice" style="display: none;">
                            <div class="alert" style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 1rem; margin-top: 1rem;">
                                <i class="bi bi-info-circle" style="color: #856404; margin-right: 0.5rem;"></i>
                                <strong style="color: #856404;">Payment Gateway Fee:</strong>
                                <span id="fee-calculation" style="color: #856404;"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Order Notes -->
                    <div class="form-section">
                        <h3><i class="bi bi-chat-left-text"></i> Order Notes (Optional)</h3>
                        <div class="form-group">
                            <label for="order_notes">Special instructions for your order</label>
                            <textarea id="order_notes" name="order_notes" rows="3" placeholder="Any special delivery instructions or notes about your order..."></textarea>
                        </div>
                    </div>

                    <!-- Security Info -->
                    <div class="security-info">
                        <i class="bi bi-shield-check"></i>
                        <strong>Secure Checkout:</strong> Your personal information is protected with SSL encryption.
                    </div>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="order-summary">
                <h3>Order Summary</h3>
                
                <?php foreach ($_SESSION['cart'] as $product_id => $item): ?>
                    <div class="order-item">
                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <div class="item-info">
                            <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="item-details">Qty: <?php echo $item['quantity']; ?> × ₱<?php echo number_format($item['price'], 2); ?></div>
                        </div>
                        <div class="item-price">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                    </div>
                <?php endforeach; ?>

                <div class="summary-divider"></div>
                
                <div class="summary-row">
                    <span>Subtotal (<?php echo $item_count; ?> items):</span>
                    <span>₱<?php echo number_format($subtotal, 2); ?></span>
                </div>
                
                <div class="summary-row">
                    <span>Shipping Fee:</span>
                    <span>₱<?php echo number_format($shipping_fee, 2); ?></span>
                </div>
                
                <div class="summary-row">
                    <span>VAT (12%):</span>
                    <span>₱<?php echo number_format($tax_amount, 2); ?></span>
                </div>
                
                <div class="summary-row" id="gateway-fee-row" style="display: none;">
                    <span>Gateway Fee:</span>
                    <span id="gateway-fee-amount">₱0.00</span>
                </div>
                
                <div class="summary-row total-row">
                    <span>Total:</span>
                    <span id="final-total">₱<?php echo number_format($total, 2); ?></span>
                </div>
                
                <button type="submit" form="checkoutForm" class="place-order-btn" id="place-order-btn">
                    <i class="bi bi-lock"></i> <span id="order-btn-text">Place Order - ₱<?php echo number_format($total, 2); ?></span>
                </button>
                
                <div style="text-align: center; margin-top: 1rem; font-size: 0.9rem; color: #666;">
                    <i class="bi bi-shield-check"></i> Secure 256-bit SSL encryption
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleBillingAddress() {
            const checkbox = document.getElementById('same_as_shipping');
            const billingSection = document.getElementById('billing_address_section');
            const billingInputs = billingSection.querySelectorAll('input, select');
            
            if (checkbox.checked) {
                billingSection.style.display = 'none';
                billingInputs.forEach(input => {
                    if (input.hasAttribute('required')) {
                        input.removeAttribute('required');
                        input.dataset.wasRequired = 'true';
                    }
                });
            } else {
                billingSection.style.display = 'block';
                billingInputs.forEach(input => {
                    if (input.dataset.wasRequired === 'true') {
                        input.setAttribute('required', '');
                    }
                });
            }
        }
        
        function selectPayment(element, method) {
            // Remove selected class from all payment options
            document.querySelectorAll('.payment-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            element.classList.add('selected');
            
            // Check the radio button
            element.querySelector('input[type="radio"]').checked = true;
            
            // Calculate and display fees
            calculatePaymentFees(method);
        }
        
        function calculatePaymentFees(paymentMethod) {
            const subtotal = <?php echo $subtotal; ?>;
            const shipping = <?php echo $shipping_fee; ?>;
            const tax = <?php echo $tax_amount; ?>;
            const baseTotal = subtotal + shipping + tax;
            
            const feeNotice = document.getElementById('payment-fee-notice');
            const feeRow = document.getElementById('gateway-fee-row');
            const feeAmount = document.getElementById('gateway-fee-amount');
            const finalTotal = document.getElementById('final-total');
            const orderBtnText = document.getElementById('order-btn_text');
            const feeCalculation = document.getElementById('fee-calculation');
            
            if (paymentMethod === 'paymongo_gcash' || paymentMethod === 'paymongo_card') {
                // Calculate gateway fee: 3.5% + ₱15
                const percentageFee = baseTotal * 0.035;
                const fixedFee = 15;
                const totalFee = percentageFee + fixedFee;
                const finalAmount = baseTotal + totalFee;
                
                // Show fee information
                feeNotice.style.display = 'block';
                feeRow.style.display = 'flex';
                feeAmount.textContent = '₱' + totalFee.toFixed(2);
                finalTotal.textContent = '₱' + finalAmount.toFixed(2);
                orderBtnText.textContent = 'Place Order - ₱' + finalAmount.toFixed(2);
                
                const methodName = paymentMethod === 'paymongo_gcash' ? 'GCash' : 'Card';
                feeCalculation.textContent = `${methodName} fee: ₱${percentageFee.toFixed(2)} (3.5%) + ₱${fixedFee.toFixed(2)} (fixed) = ₱${totalFee.toFixed(2)}`;
            } else {
                // No fees for COD
                feeNotice.style.display = 'none';
                feeRow.style.display = 'none';
                finalTotal.textContent = '₱' + baseTotal.toFixed(2);
                orderBtnText.textContent = 'Place Order - ₱' + baseTotal.toFixed(2);
            }
        }
        
        // Handle form submission
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.querySelector('.place-order-btn');
            const originalText = submitBtn.innerHTML;
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            
            // Show appropriate loading message
            submitBtn.disabled = true;
            if (paymentMethod === 'paymongo_gcash') {
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Redirecting to GCash...';
            } else if (paymentMethod === 'paymongo_card') {
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing Payment...';
            } else {
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Creating Order...';
            }
            
            const formData = new FormData(this);
            
            fetch('api/process_checkout.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.redirect_url) {
                        // For PayMongo payments, redirect to payment gateway
                        window.location.href = data.redirect_url;
                    } else {
                        // For COD, go to order confirmation
                        window.location.href = `order_confirmation.php?order_id=${data.order_id}`;
                    }
                } else {
                    alert('Error: ' + data.message);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Checkout error:', error);
                alert('An error occurred during checkout. Please try again.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
        
        // Auto-fill billing address when shipping changes (if same as shipping is checked)
        document.addEventListener('DOMContentLoaded', function() {
            const shippingInputs = ['address', 'address_2', 'city', 'province', 'postal_code'];
            const sameAsShippingCheckbox = document.getElementById('same_as_shipping');
            
            shippingInputs.forEach(field => {
                const shippingInput = document.getElementById(`shipping_${field}`);
                if (shippingInput) {
                    shippingInput.addEventListener('input', function() {
                        if (sameAsShippingCheckbox.checked) {
                            const billingInput = document.getElementById(`billing_${field}`);
                            if (billingInput) {
                                billingInput.value = this.value;
                            }
                        }
                    });
                }
            });
        });

        // Map functionality
        let map;
        let currentMarker;
        let isMapInitialized = false;

        function toggleMapLocation() {
            const checkbox = document.getElementById('use_map_location');
            const mapContent = document.getElementById('map-content');
            
            if (checkbox.checked) {
                mapContent.style.display = 'block';
                if (!isMapInitialized) {
                    initializeMap();
                }
            } else {
                mapContent.style.display = 'none';
                clearMapSelection();
            }
        }

        function initializeMap() {
            // Initialize map centered on Philippines
            map = L.map('map').setView([14.5995, 120.9842], 11); // Manila coordinates
            
            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            
            // Add click event to map
            map.on('click', function(e) {
                setMapLocation(e.latlng.lat, e.latlng.lng);
            });
            
            isMapInitialized = true;
        }

        function getCurrentLocation() {
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by this browser.');
                return;
            }

            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                map.setView([lat, lng], 15);
                setMapLocation(lat, lng);
            }, function(error) {
                alert('Error getting your location: ' + error.message);
            });
        }

        function searchLocation() {
            const searchInput = document.getElementById('search-input');
            if (searchInput.style.display === 'none') {
                searchInput.style.display = 'block';
                document.getElementById('location-search').focus();
            } else {
                searchInput.style.display = 'none';
            }
        }

        function performSearch() {
            const query = document.getElementById('location-search').value.trim();
            if (!query) return;

            // Use Nominatim API for geocoding
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=ph&limit=1`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const result = data[0];
                        const lat = parseFloat(result.lat);
                        const lng = parseFloat(result.lon);
                        
                        map.setView([lat, lng], 15);
                        setMapLocation(lat, lng, result.display_name);
                        document.getElementById('search-input').style.display = 'none';
                    } else {
                        alert('Location not found. Please try a different search term.');
                    }
                })
                .catch(error => {
                    console.error('Search error:', error);
                    alert('Error searching for location. Please try again.');
                });
        }

        function setMapLocation(lat, lng, address = null) {
            // Remove existing marker
            if (currentMarker) {
                map.removeLayer(currentMarker);
            }
            
            // Add new marker
            currentMarker = L.marker([lat, lng]).addTo(map);
            
            // Update form fields
            document.getElementById('map_latitude').value = lat.toFixed(6);
            document.getElementById('map_longitude').value = lng.toFixed(6);
            
            // Update display
            const coordsDisplay = document.getElementById('selected-coordinates');
            coordsDisplay.textContent = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
            
            const locationInfo = document.getElementById('location-info');
            locationInfo.style.display = 'block';
            
            // If address is provided, use it; otherwise, reverse geocode
            if (address) {
                document.getElementById('selected-address').textContent = address;
                document.getElementById('map_address').value = address;
            } else {
                // Reverse geocode to get address
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                    .then(response => response.json())
                    .then(data => {
                        const address = data.display_name || `Location at ${lat.toFixed(4)}, ${lng.toFixed(4)}`;
                        document.getElementById('selected-address').textContent = address;
                        document.getElementById('map_address').value = address;
                    })
                    .catch(error => {
                        console.error('Reverse geocoding error:', error);
                        const fallbackAddress = `Location at ${lat.toFixed(4)}, ${lng.toFixed(4)}`;
                        document.getElementById('selected-address').textContent = fallbackAddress;
                        document.getElementById('map_address').value = fallbackAddress;
                    });
            }
        }

        function clearMapSelection() {
            if (currentMarker) {
                map.removeLayer(currentMarker);
                currentMarker = null;
            }
            
            document.getElementById('map_latitude').value = '';
            document.getElementById('map_longitude').value = '';
            document.getElementById('map_address').value = '';
            document.getElementById('selected-address').textContent = 'No location selected';
            document.getElementById('selected-coordinates').textContent = 'Lat: -, Lng: -';
            document.getElementById('location-info').style.display = 'none';
        }

        // Handle Enter key in search input
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('location-search');
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        performSearch();
                    }
                });
            }
        });
    </script>
    
    <!-- Leaflet.js Library -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
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
                // Handle Enter key press
                headerSearch.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const searchTerm = this.value.trim();
                        if (searchTerm.length > 0) {
                            // Redirect to product catalog with search query
                            window.location.href = `ProductCatalog.php?search=${encodeURIComponent(searchTerm)}`;
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>