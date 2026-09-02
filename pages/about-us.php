<?php
require_once '../includes/user_auth.php';

// Initialize cart count for header
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - PeakPH</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/Global.css">
    <style>
        /* Page-specific styles */
        .about-hero {
            background: linear-gradient(rgba(46, 118, 94, 0.85), rgba(61, 161, 128, 0.95)), url('../Assets/Gallery_Images/about-hero.jpg');
            background-size: cover;
            background-position: center;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            margin-bottom: 3rem;
        }

        .about-hero h1 {
            font-size: 3rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            margin-bottom: 1rem;
        }

        .about-hero p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto;
        }

        .about-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .about-section {
            margin-bottom: 4rem;
        }

        .about-section h2 {
            color: #2e765e;
            font-size: 2rem;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .about-section h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background-color: #2e765e;
        }

        .about-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .value-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .value-card:hover {
            transform: translateY(-5px);
        }

        .value-icon {
            font-size: 3rem;
            color: #2e765e;
            margin-bottom: 1rem;
        }

        .value-card h3 {
            color: #2e765e;
            margin-bottom: 1rem;
            font-size: 1.25rem;
        }

        .value-card p {
            color: #666;
            line-height: 1.6;
        }

        .story-section {
            background: #f8f9fa;
            padding: 3rem;
            border-radius: 12px;
            margin-bottom: 3rem;
        }

        .story-section p {
            color: #444;
            line-height: 1.8;
            margin-bottom: 1rem;
            font-size: 1.05rem;
        }

        .team-section {
            text-align: center;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .team-member {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .team-member img {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }

        .member-info {
            padding: 1.5rem;
        }

        .member-info h3 {
            color: #2e765e;
            margin-bottom: 0.5rem;
        }

        .member-info p {
            color: #666;
            font-size: 0.95rem;
        }

        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
            text-align: center;
        }

        .stat-box {
            background: #2e765e;
            color: white;
            padding: 2rem;
            border-radius: 12px;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            display: block;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            .about-hero {
                height: 300px;
            }

            .about-hero h1 {
                font-size: 2rem;
            }

            .about-grid, .team-grid {
                grid-template-columns: 1fr;
            }

            .about-section h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <header>
        <div class="top-navbar">
            <div class="brand">
                <a href="../index.php" class="logo-btn">
                    <img src="../Assets/Carousel_Picts/Logo.png" alt="Brand Logo" />
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
                <a href="../cart.php" class="cart-link">
                    <i class="bi bi-cart">
                        <span class="cart-count"><?php echo $cart_count; ?></span>
                    </i>
                </a>
            </div>
        </div>

        <!-- Bottom Navbar -->
        <div class="bottom-navbar">
            <nav>
                <a href="../index.php"><i class="bi bi-house-door"></i> Home</a>
                <a href="../ProductCatalog.php"><i class="bi bi-shop"></i> Shop</a>
                <a href="climbers-community.php"><i class="bi bi-people"></i> Community</a>
                <a href="contact-us.php"><i class="bi bi-envelope"></i> Contact Us</a>
                <a href="../index.php#deals" class="best-deals"><i class="bi bi-fire"></i> Best Deals</a>
                <a href="about-us.php"><i class="bi bi-info-circle"></i> About Us</a>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="about-hero">
        <div>
            <h1>About PeakPH</h1>
            <p>Your trusted partner in outdoor adventures. We provide quality gear and equipment for climbers, hikers, and outdoor enthusiasts across the Philippines.</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="about-content">
        <!-- Our Story Section -->
        <section class="about-section">
            <h2>Our Story</h2>
            <div class="story-section">
                <p>PeakPH was founded with a simple mission: to make outdoor adventures accessible to everyone in the Philippines. What started as a small passion project has grown into one of the country's leading outdoor equipment retailers.</p>
                <p>We believe that everyone deserves to experience the beauty of nature. Whether you're scaling mountains, camping under the stars, or exploring hidden trails, we're here to equip you with the best gear for your journey.</p>
                <p>Our team consists of experienced outdoor enthusiasts who have firsthand knowledge of what works in the field. We carefully select each product in our inventory to ensure it meets our high standards of quality, durability, and value.</p>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="stats-section">
            <div class="stat-box">
                <span class="stat-number">5,000+</span>
                <span class="stat-label">Happy Customers</span>
            </div>
            <div class="stat-box">
                <span class="stat-number">500+</span>
                <span class="stat-label">Products</span>
            </div>
            <div class="stat-box">
                <span class="stat-number">3+</span>
                <span class="stat-label">Years Experience</span>
            </div>
            <div class="stat-box">
                <span class="stat-number">100%</span>
                <span class="stat-label">Satisfaction</span>
            </div>
        </section>

        <!-- Our Values Section -->
        <section class="about-section">
            <h2>Our Values</h2>
            <div class="about-grid">
                <div class="value-card">
                    <div class="value-icon"><i class="bi bi-shield-check"></i></div>
                    <h3>Quality First</h3>
                    <p>We only offer products that we would use ourselves. Every item is tested and verified for quality.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><i class="bi bi-people"></i></div>
                    <h3>Customer Focus</h3>
                    <p>Your satisfaction is our priority. We're here to help you find the perfect gear for your adventures.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><i class="bi bi-tree"></i></div>
                    <h3>Sustainability</h3>
                    <p>We're committed to protecting the environment and promoting responsible outdoor practices.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><i class="bi bi-heart"></i></div>
                    <h3>Passion</h3>
                    <p>We love what we do, and it shows in our dedication to the outdoor community.</p>
                </div>
            </div>
        </section>

        <!-- Mission & Vision -->
        <section class="about-section">
            <h2>Our Mission & Vision</h2>
            <div class="about-grid">
                <div class="value-card">
                    <div class="value-icon"><i class="bi bi-flag"></i></div>
                    <h3>Our Mission</h3>
                    <p>To empower outdoor enthusiasts with high-quality, affordable gear while fostering a community of responsible adventurers who respect and protect nature.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><i class="bi bi-binoculars"></i></div>
                    <h3>Our Vision</h3>
                    <p>To be the Philippines' most trusted outdoor equipment provider, inspiring more Filipinos to explore, adventure, and connect with nature.</p>
                </div>
            </div>
        </section>
    </div>

    <!-- Include Auth Modal -->
    <?php include '../components/auth_modal.php'; ?>

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

    <!-- Scripts -->
    <script src="../Js/user_dropdown.js"></script>
    <script src="../Js/cart.js"></script>
    <script src="../Js/wishlist.js"></script>
    <script src="../components/auth_modal_otp.js"></script>
    
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
                            window.location.href = `../ProductCatalog.php?search=${encodeURIComponent(searchTerm)}`;
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
