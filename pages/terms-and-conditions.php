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
    <title>Terms and Conditions - PeakPH</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/Global.css">
    <style>
        /* Page-specific styles */
        .terms-hero {
            background: linear-gradient(rgba(46, 118, 94, 0.85), rgba(61, 161, 128, 0.95)), url('../Assets/Gallery_Images/terms-hero.jpg');
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

        .terms-hero h1 {
            font-size: 2.5rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .terms-content {
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

        .terms-section {
            margin-bottom: 2.5rem;
        }

        .terms-section h2 {
            color: #2e765e;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #2e765e;
        }

        .terms-section h3 {
            color: #333;
            font-size: 1.2rem;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }

        .terms-section p {
            color: #444;
            line-height: 1.8;
            margin-bottom: 1rem;
        }

        .terms-section ul, .terms-section ol {
            margin-left: 2rem;
            margin-bottom: 1rem;
        }

        .terms-section li {
            color: #444;
            line-height: 1.8;
            margin-bottom: 0.5rem;
        }

        .important-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1.5rem 0;
        }

        .important-box strong {
            color: #856404;
        }

        .info-box {
            background: #d1ecf1;
            border-left: 4px solid #17a2b8;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1.5rem 0;
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
            .terms-hero {
                height: 250px;
            }

            .terms-hero h1 {
                font-size: 2rem;
            }

            .terms-content {
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
    <section class="terms-hero">
        <div>
            <h1>Terms and Conditions</h1>
        </div>
    </section>

    <!-- Main Content -->
    <div class="terms-content">
        <p class="last-updated">Last Updated: November 4, 2025</p>

        <div class="terms-section">
            <h2>1. Agreement to Terms</h2>
            <p>By accessing and using the PeakPH website ("Service"), you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this Service.</p>
            
            <div class="important-box">
                <strong>Important:</strong> These Terms and Conditions constitute a legally binding agreement between you and PeakPH. Please read them carefully before using our services.
            </div>
        </div>

        <div class="terms-section">
            <h2>2. Use of Our Service</h2>
            
            <h3>2.1 Eligibility</h3>
            <p>You must be at least 18 years of age to use this Service. By using this Service and agreeing to these Terms, you represent and warrant that you are at least 18 years of age.</p>

            <h3>2.2 Account Registration</h3>
            <p>To access certain features of the Service, you may be required to register for an account. You agree to:</p>
            <ul>
                <li>Provide accurate, current, and complete information during registration</li>
                <li>Maintain and promptly update your account information</li>
                <li>Maintain the security of your password and accept all risks of unauthorized access</li>
                <li>Notify us immediately of any unauthorized use of your account</li>
            </ul>

            <h3>2.3 Prohibited Uses</h3>
            <p>You may not use our Service:</p>
            <ul>
                <li>For any unlawful purpose or to solicit others to perform unlawful acts</li>
                <li>To violate any international, federal, provincial or state regulations, rules, laws, or local ordinances</li>
                <li>To infringe upon or violate our intellectual property rights or the intellectual property rights of others</li>
                <li>To harass, abuse, insult, harm, defame, slander, disparage, intimidate, or discriminate</li>
                <li>To submit false or misleading information</li>
                <li>To upload or transmit viruses or any other type of malicious code</li>
                <li>To spam, phish, pharm, pretext, spider, crawl, or scrape</li>
            </ul>
        </div>

        <div class="terms-section">
            <h2>3. Products and Services</h2>
            
            <h3>3.1 Product Information</h3>
            <p>We strive to provide accurate product descriptions and pricing. However, we do not warrant that product descriptions, pricing, or other content is accurate, complete, reliable, current, or error-free.</p>

            <h3>3.2 Pricing</h3>
            <p>All prices are in Philippine Pesos (PHP) and are subject to change without notice. We reserve the right to modify or discontinue products without prior notification.</p>

            <h3>3.3 Availability</h3>
            <p>All products are subject to availability. We reserve the right to limit the quantities of any products or services that we offer.</p>
        </div>

        <div class="terms-section">
            <h2>4. Orders and Payment</h2>
            
            <h3>4.1 Order Acceptance</h3>
            <p>We reserve the right to refuse or cancel any order for any reason, including but not limited to:</p>
            <ul>
                <li>Product or service availability</li>
                <li>Errors in product or pricing information</li>
                <li>Errors in your order</li>
                <li>Suspected fraudulent activity</li>
            </ul>

            <h3>4.2 Payment</h3>
            <p>Payment must be received before your order is processed. We accept the following payment methods:</p>
            <ul>
                <li>Credit/Debit Cards (Visa, Mastercard)</li>
                <li>GCash</li>
                <li>PayMaya</li>
                <li>Bank Transfer</li>
                <li>Cash on Delivery (for eligible orders)</li>
            </ul>

            <div class="info-box">
                <strong>Payment Security:</strong> All payment information is processed securely through encrypted channels. We do not store your complete credit card information on our servers.
            </div>
        </div>

        <div class="terms-section">
            <h2>5. Shipping and Delivery</h2>
            
            <h3>5.1 Shipping</h3>
            <p>We ship to addresses within the Philippines. Shipping costs and delivery times vary based on your location and chosen shipping method.</p>

            <h3>5.2 Delivery</h3>
            <p>Estimated delivery times are provided at checkout but are not guaranteed. We are not responsible for delays caused by:</p>
            <ul>
                <li>Incorrect shipping information provided by you</li>
                <li>Weather conditions or natural disasters</li>
                <li>Courier service delays</li>
                <li>Government restrictions or customs clearance</li>
            </ul>

            <h3>5.3 Risk of Loss</h3>
            <p>All items purchased from PeakPH are made pursuant to a shipment contract. Risk of loss and title for items purchased pass to you upon delivery to the carrier.</p>
        </div>

        <div class="terms-section">
            <h2>6. Returns and Refunds</h2>
            
            <h3>6.1 Return Policy</h3>
            <p>We accept returns within 7 days of delivery for unused items in original packaging. To initiate a return:</p>
            <ol>
                <li>Contact our customer service within 7 days of receiving your order</li>
                <li>Provide your order number and reason for return</li>
                <li>Package the item securely in its original packaging</li>
                <li>Ship the item back to our returns address</li>
            </ol>

            <h3>6.2 Non-Returnable Items</h3>
            <p>The following items cannot be returned:</p>
            <ul>
                <li>Items marked as final sale</li>
                <li>Used or damaged items</li>
                <li>Items without original packaging or tags</li>
                <li>Personalized or customized products</li>
            </ul>

            <h3>6.3 Refunds</h3>
            <p>Refunds will be processed within 7-14 business days after we receive and inspect your returned item. Refunds will be issued to the original payment method.</p>
        </div>

        <div class="terms-section">
            <h2>7. Intellectual Property Rights</h2>
            <p>The Service and its original content, features, and functionality are owned by PeakPH and are protected by international copyright, trademark, patent, trade secret, and other intellectual property laws.</p>
            
            <p>You may not:</p>
            <ul>
                <li>Copy, modify, or distribute content from our website without permission</li>
                <li>Use our trademarks, logos, or brand name without authorization</li>
                <li>Reverse engineer or attempt to extract source code from our Service</li>
            </ul>
        </div>

        <div class="terms-section">
            <h2>8. Limitation of Liability</h2>
            <p>To the maximum extent permitted by law, PeakPH shall not be liable for any indirect, incidental, special, consequential, or punitive damages resulting from:</p>
            <ul>
                <li>Your use or inability to use the Service</li>
                <li>Any unauthorized access to or use of our servers</li>
                <li>Any interruption or cessation of transmission to or from our Service</li>
                <li>Any bugs, viruses, or the like transmitted through the Service by any third party</li>
                <li>Any errors or omissions in any content or for any loss or damage incurred as a result of your use of any content posted, emailed, transmitted, or otherwise made available through the Service</li>
            </ul>
        </div>

        <div class="terms-section">
            <h2>9. Warranty Disclaimer</h2>
            <p>The Service is provided on an "AS IS" and "AS AVAILABLE" basis. PeakPH makes no warranties, expressed or implied, and hereby disclaims all other warranties including, without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property.</p>
        </div>

        <div class="terms-section">
            <h2>10. Indemnification</h2>
            <p>You agree to defend, indemnify, and hold harmless PeakPH and its officers, directors, employees, and agents from and against any claims, liabilities, damages, judgments, awards, losses, costs, expenses, or fees arising out of or relating to your violation of these Terms or your use of the Service.</p>
        </div>

        <div class="terms-section">
            <h2>11. Governing Law</h2>
            <p>These Terms shall be governed by and construed in accordance with the laws of the Republic of the Philippines, without regard to its conflict of law provisions. Any disputes arising from these Terms shall be subject to the exclusive jurisdiction of the courts of the Philippines.</p>
        </div>

        <div class="terms-section">
            <h2>12. Changes to Terms</h2>
            <p>We reserve the right to modify or replace these Terms at any time. We will provide notice of any material changes by posting the new Terms on this page and updating the "Last Updated" date. Your continued use of the Service after any such changes constitutes your acceptance of the new Terms.</p>
        </div>

        <div class="terms-section">
            <h2>13. Contact Information</h2>
            <p>If you have any questions about these Terms and Conditions, please contact us:</p>
        </div>

        <div class="contact-box">
            <h3><i class="bi bi-envelope"></i> Get in Touch</h3>
            <p>Email: <a href="mailto:support@peakph.com">support@peakph.com</a></p>
            <p>Phone: +63 (2) 1234-5678</p>
            <p>Address: 123 Mountain View Street, Quezon City, Metro Manila, Philippines 1100</p>
        </div>

        <div class="terms-section" style="margin-top: 2rem; padding-top: 2rem; border-top: 2px solid #e0e0e0;">
            <p style="text-align: center; color: #666;">By using PeakPH's services, you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions.</p>
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
