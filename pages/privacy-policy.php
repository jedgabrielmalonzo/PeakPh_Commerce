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
    <title>Privacy Policy - PeakPH</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/Global.css">
    <style>
        /* Page-specific styles */
        .privacy-hero {
            background: linear-gradient(rgba(46, 118, 94, 0.85), rgba(61, 161, 128, 0.95)), url('../Assets/Gallery_Images/privacy-hero.jpg');
            background-size: cover;
            background-position: center;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            margin-bottom: 3rem;
        }

        .privacy-hero h1 {
            font-size: 2.5rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .privacy-content {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 3rem;
        }

        .last-updated {
            color: #666;
            font-style: italic;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e0e0e0;
        }

        .privacy-section {
            margin-bottom: 2.5rem;
        }

        .privacy-section h2 {
            color: #2e765e;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #2e765e;
        }

        .privacy-section h3 {
            color: #333;
            font-size: 1.2rem;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }

        .privacy-section p {
            color: #444;
            line-height: 1.8;
            margin-bottom: 1rem;
        }

        .privacy-section ul, .privacy-section ol {
            margin-left: 2rem;
            margin-bottom: 1rem;
        }

        .privacy-section li {
            color: #444;
            line-height: 1.8;
            margin-bottom: 0.5rem;
        }

        .highlight-box {
            background: #f8f9fa;
            padding: 1.5rem;
            border-left: 4px solid #2e765e;
            border-radius: 8px;
            margin: 1.5rem 0;
        }

        .highlight-box strong {
            color: #2e765e;
        }

        .contact-box {
            background: #2e765e;
            color: white;
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            margin-top: 3rem;
        }

        .contact-box h3 {
            margin-bottom: 1rem;
        }

        .contact-box a {
            color: white;
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .privacy-hero {
                height: 250px;
            }

            .privacy-hero h1 {
                font-size: 2rem;
            }

            .privacy-content {
                padding: 1.5rem;
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
                <a href="../index.php">Home</a>
                <a href="../ProductCatalog.php">Shop</a>
                <a href="contact-us.php">Contact Us</a>
                <a href="../index.php#deals" class="best-deals">Best Deals</a>
                <a href="about-us.php">About us</a>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="privacy-hero">
        <div>
            <h1>Privacy Policy</h1>
        </div>
    </section>

    <!-- Main Content -->
    <div class="privacy-content">
        <p class="last-updated">Last Updated: November 4, 2025</p>

        <div class="privacy-section">
            <h2>1. Introduction</h2>
            <p>Welcome to PeakPH ("we," "our," or "us"). We respect your privacy and are committed to protecting your personal data. This privacy policy will inform you about how we look after your personal data when you visit our website and tell you about your privacy rights.</p>
            
            <div class="highlight-box">
                <strong>Important:</strong> This website is not intended for children, and we do not knowingly collect data relating to children. By using our website, you confirm that you are at least 18 years old or have parental consent.
            </div>
        </div>

        <div class="privacy-section">
            <h2>2. Information We Collect</h2>
            
            <h3>Personal Information</h3>
            <p>We may collect, use, store and transfer different kinds of personal data about you:</p>
            <ul>
                <li><strong>Identity Data:</strong> First name, last name, username</li>
                <li><strong>Contact Data:</strong> Billing address, delivery address, email address, telephone numbers</li>
                <li><strong>Financial Data:</strong> Payment card details (processed securely through third-party payment processors)</li>
                <li><strong>Transaction Data:</strong> Details about payments and products you have purchased from us</li>
                <li><strong>Technical Data:</strong> Internet protocol (IP) address, browser type and version, time zone setting, browser plug-in types and versions, operating system</li>
                <li><strong>Profile Data:</strong> Your username and password, purchases made by you, your interests, preferences, feedback</li>
                <li><strong>Usage Data:</strong> Information about how you use our website and products</li>
                <li><strong>Marketing Data:</strong> Your preferences in receiving marketing from us</li>
            </ul>
        </div>

        <div class="privacy-section">
            <h2>3. How We Use Your Information</h2>
            <p>We will only use your personal data when the law allows us to. Most commonly, we will use your personal data in the following circumstances:</p>
            <ul>
                <li>To process and deliver your orders</li>
                <li>To manage payments, fees and charges</li>
                <li>To collect and recover money owed to us</li>
                <li>To provide customer service and support</li>
                <li>To send you marketing communications (with your consent)</li>
                <li>To improve our website, products, and services</li>
                <li>To protect our business and website from fraud</li>
                <li>To comply with legal obligations</li>
            </ul>
        </div>

        <div class="privacy-section">
            <h2>4. Data Security</h2>
            <p>We have put in place appropriate security measures to prevent your personal data from being accidentally lost, used or accessed in an unauthorized way, altered or disclosed. We limit access to your personal data to those employees, agents, contractors and other third parties who have a business need to know.</p>
            
            <div class="highlight-box">
                <strong>Security Measures Include:</strong>
                <ul>
                    <li>SSL encryption for data transmission</li>
                    <li>Secure payment processing through trusted providers</li>
                    <li>Regular security audits and updates</li>
                    <li>Restricted access to personal data</li>
                    <li>Employee training on data protection</li>
                </ul>
            </div>
        </div>

        <div class="privacy-section">
            <h2>5. Data Retention</h2>
            <p>We will only retain your personal data for as long as necessary to fulfill the purposes we collected it for, including for the purposes of satisfying any legal, accounting, or reporting requirements.</p>
            <p>When we no longer need to use your personal data, we will remove it from our systems and records and/or take steps to anonymize it so that you can no longer be identified from it.</p>
        </div>

        <div class="privacy-section">
            <h2>6. Your Rights</h2>
            <p>Under data protection laws, you have rights including:</p>
            <ul>
                <li><strong>Right to Access:</strong> Request access to your personal data</li>
                <li><strong>Right to Correction:</strong> Request correction of inaccurate personal data</li>
                <li><strong>Right to Erasure:</strong> Request deletion of your personal data</li>
                <li><strong>Right to Object:</strong> Object to processing of your personal data</li>
                <li><strong>Right to Restriction:</strong> Request restriction of processing your personal data</li>
                <li><strong>Right to Data Portability:</strong> Request transfer of your personal data</li>
                <li><strong>Right to Withdraw Consent:</strong> Withdraw consent at any time</li>
            </ul>
        </div>

        <div class="privacy-section">
            <h2>7. Cookies</h2>
            <p>Our website uses cookies to distinguish you from other users. This helps us provide you with a good experience and allows us to improve our site. A cookie is a small file of letters and numbers that we store on your browser or the hard drive of your computer.</p>
            
            <h3>Types of Cookies We Use:</h3>
            <ul>
                <li><strong>Strictly Necessary Cookies:</strong> Required for the operation of our website</li>
                <li><strong>Analytical/Performance Cookies:</strong> Allow us to recognize and count visitors</li>
                <li><strong>Functionality Cookies:</strong> Used to recognize you when you return to our website</li>
                <li><strong>Targeting Cookies:</strong> Record your visit to our website and the pages you visit</li>
            </ul>
        </div>

        <div class="privacy-section">
            <h2>8. Third-Party Links</h2>
            <p>Our website may include links to third-party websites, plug-ins and applications. Clicking on those links or enabling those connections may allow third parties to collect or share data about you. We do not control these third-party websites and are not responsible for their privacy statements.</p>
        </div>

        <div class="privacy-section">
            <h2>9. Changes to This Policy</h2>
            <p>We may update this privacy policy from time to time. We will notify you of any changes by posting the new privacy policy on this page and updating the "Last Updated" date.</p>
        </div>

        <div class="privacy-section">
            <h2>10. Data Protection Officer</h2>
            <p>We have appointed a data protection officer (DPO) who is responsible for overseeing questions in relation to this privacy policy. If you have any questions about this privacy policy, including any requests to exercise your legal rights, please contact the DPO.</p>
        </div>

        <div class="contact-box">
            <h3><i class="bi bi-envelope"></i> Contact Us About Privacy</h3>
            <p>If you have any questions about this Privacy Policy or our data practices, please contact us:</p>
            <p>Email: <a href="mailto:privacy@peakph.com">privacy@peakph.com</a></p>
            <p>Phone: +63 (2) 1234-5678</p>
            <p>Address: 123 Mountain View Street, Quezon City, Metro Manila, Philippines 1100</p>
        </div>
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
