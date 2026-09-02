<?php
require_once 'includes/user_auth.php';

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - PeakPH Commerce</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="Css/Global.css">
    <style>
        .profile-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .profile-header {
            background: linear-gradient(135deg, #2e765e, #3da180);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .profile-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
        }
        
        .profile-sidebar {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            height: fit-content;
        }
        
        .profile-main {
            background: white;
            padding: 1.5rem;
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
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .info-card {
            padding: 1.5rem;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background: #f9f9f9;
        }
        
        .info-card h3 {
            color: #2e765e;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #eee;
        }
        
        .info-row:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
        }
        
        .info-value {
            color: #333;
        }
        
        @media (max-width: 768px) {
            .profile-content {
                grid-template-columns: 1fr;
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
                <input type="search" placeholder="Search..." />
            </div>

            <div class="top-icons">
                <?php echo getAuthNavigationHTML(); ?>
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

    <div class="profile-container">
        <div class="profile-header">
            <h1><i class="bi bi-person-circle"></i> My Profile</h1>
            <p>Welcome back, <?php echo htmlspecialchars($current_user['name']); ?>!</p>
        </div>

        <div class="profile-content">
            <div class="profile-sidebar">
                <ul class="profile-nav">
                    <li><a href="profile.php" class="active"><i class="bi bi-person"></i> Profile Information</a></li>
                    <li><a href="orders.php"><i class="bi bi-box"></i> My Orders</a></li>
                    <li><a href="settings.php"><i class="bi bi-gear"></i> Account Settings</a></li>
                    <li><a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                </ul>
            </div>

            <div class="profile-main">
                <h2>Profile Information</h2>
                <div class="info-grid">
                    <div class="info-card">
                        <h3><i class="bi bi-person-fill"></i> Personal Details</h3>
                        <div class="info-row">
                            <span class="info-label">Full Name:</span>
                            <span class="info-value"><?php echo htmlspecialchars($current_user['name']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?php echo htmlspecialchars($current_user['email']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Account Type:</span>
                            <span class="info-value"><?php echo htmlspecialchars($current_user['role']); ?></span>
                        </div>
                    </div>

                    <div class="info-card">
                        <h3><i class="bi bi-shield-check"></i> Account Status</h3>
                        <div class="info-row">
                            <span class="info-label">Status:</span>
                            <span class="info-value" style="color: #28a745; font-weight: 600;">Active</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Member Since:</span>
                            <span class="info-value">Account creation date</span>
                        </div>
                        <div class="info-row">
    <script src="Js/user_dropdown.js"></script>
    <script src="components/auth_modal_otp.js"></script>
    <script src="Js/JavaScript.js"></script>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 2rem; text-align: center;">
                    <p style="color: #666; font-style: italic;">
                        <i class="bi bi-info-circle"></i> 
                        Profile editing functionality coming soon! Contact support if you need to update your information.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- AUTH MODAL -->
    <?php include 'components/auth_modal.php'; ?>

    <script src="components/auth_modal_otp.js"></script>
    <script src="Js/JavaScript.js"></script>
</body>
</html>