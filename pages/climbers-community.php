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
    <title>Climbers Community - PeakPH</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/Global.css">
    <style>
        /* Page-specific styles */
        .community-hero {
            background: linear-gradient(rgba(60, 153, 196, 0.85), rgba(49, 145, 114, 0.95)), url('../Assets/Gallery_Images/community-hero.jpg');
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

        .community-hero h1 {
            font-size: 3rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            margin-bottom: 1rem;
        }

        .community-hero p {
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .community-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .community-section {
            margin-bottom: 4rem;
        }

        .community-section h2 {
            color: #2e765e;
            font-size: 2rem;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .community-section h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background-color: #2e765e;
        }

        .community-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .community-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .community-card:hover {
            transform: translateY(-5px);
        }

        .community-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .card-content {
            padding: 1.5rem;
        }

        .card-content h3 {
            color: #2e765e;
            margin-bottom: 1rem;
            font-size: 1.25rem;
        }

        .card-content p {
            color: #666;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .join-btn {
            display: inline-block;
            background: #2e765e;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .join-btn:hover {
            background: #245d4b;
        }

        .benefits-list {
            list-style: none;
            padding: 0;
        }

        .benefits-list li {
            margin-bottom: 1rem;
            padding-left: 2rem;
            position: relative;
            color: #444;
        }

        .benefits-list li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #2e765e;
            font-weight: bold;
        }

        .cta-section {
            background: #f8f9fa;
            padding: 3rem 0;
            text-align: center;
            margin-top: 3rem;
        }

        .cta-content {
            max-width: 600px;
            margin: 0 auto;
        }

        .cta-content h2 {
            color: #2e765e;
            margin-bottom: 1.5rem;
        }

        .social-links {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
            gap: 1.5rem;
        }

        .social-links a {
            color: #2e765e;
            font-size: 1.5rem;
            transition: color 0.3s ease;
        }

        .social-links a:hover {
            color: #245d4b;
        }

        @media (max-width: 768px) {
            .community-hero {
                height: 300px;
            }

            .community-hero h1 {
                font-size: 2rem;
            }

            .community-grid {
                grid-template-columns: 1fr;
            }

            .community-section h2 {
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
    <section class="community-hero">
        <div>
            <h1>Climbers Community</h1>
            <p>Join a vibrant community of outdoor enthusiasts, share experiences, and embark on new adventures together.</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="community-content">
        <!-- About Section -->
        <section class="community-section">
            <h2>Welcome to Our Community</h2>
            <p>The PeakPH Climbers Community is a gathering place for outdoor enthusiasts, adventurers, and nature lovers. Whether you're a beginner or an experienced climber, our community offers support, knowledge sharing, and exciting opportunities to connect with like-minded individuals.</p>
            
            <div class="community-grid">
                <div class="community-card">
                    <img src="../Assets/static pages picts/Group_Adventures.jpg" alt="Group Hiking">
                    <div class="card-content">
                        <h3>Group Adventures</h3>
                        <p>Join organized climbing and hiking trips with experienced guides and fellow enthusiasts.</p>
                        <a href="#join" class="join-btn">Join an Adventure</a>
                    </div>
                </div>
                <div class="community-card">
                    <img src="../Assets/static pages picts/TrainingANDWorkshops.png" alt="Training Session">
                    <div class="card-content">
                        <h3>Training & Workshops</h3>
                        <p>Learn essential skills through our regular training sessions and workshops.</p>
                        <a href="#workshops" class="join-btn">View Schedule</a>
                    </div>
                </div>
                <div class="community-card">
                    <img src="../Assets/static pages picts/CommunityMeets.jpg" alt="Community Meet">
                    <div class="card-content">
                        <h3>Community Meets</h3>
                        <p>Regular meetups to share stories, plan trips, and make new friends.</p>
                        <a href="#meets" class="join-btn">Find Events</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Benefits Section -->
        <section class="community-section">
            <h2>Membership Benefits</h2>
            <ul class="benefits-list">
                <li>Access to exclusive community events and meetups</li>
                <li>Special discounts on PeakPH gear and equipment</li>
                <li>Free entry to monthly workshops and training sessions</li>
                <li>Priority registration for guided trips and expeditions</li>
                <li>Connect with experienced climbers and mentors</li>
                <li>Share your experiences and learn from others</li>
                <li>Regular newsletters with climbing tips and community updates</li>
                <li>Members-only social media groups and forums</li>
            </ul>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="cta-content">
                <h2>Ready to Join Our Community?</h2>
                <p>Take the first step towards amazing adventures and lasting friendships.</p>
                <a href="#signup" class="join-btn">Become a Member</a>
                
                <div class="social-links">
                    <a href="#facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#twitter"><i class="bi bi-twitter"></i></a>
                    <a href="#youtube"><i class="bi bi-youtube"></i></a>
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