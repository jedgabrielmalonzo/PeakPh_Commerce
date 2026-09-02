<?php
/**
 * DELIBERATELY VULNERABLE - FOR ETHICAL HACKING COURSE
 * Demonstrates Stored XSS and Reflected XSS vulnerabilities
 */
require_once '../includes/user_auth.php';
require_once '../includes/environment.php';

// Initialize cart count for header
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}

// VULNERABILITY: Reflected XSS in query parameters
$reflected_message = isset($_GET['msg']) ? $_GET['msg'] : '';

// Handle form submission - STORED XSS VULNERABILITY
$message_sent = false;
$success_name = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    // VULNERABILITY 1: NO INPUT VALIDATION
    // VULNERABILITY 2: NO DATA SANITIZATION
    // VULNERABILITY 3: NO PREPARED STATEMENTS - Direct concatenation
    
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Store success name for reflected XSS demonstration
    $success_name = $name;
    
    // VULNERABLE DATABASE INSERT - No escaping, no prepared statements
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // CRITICAL VULNERABILITY: Using string concatenation instead of prepared statements
    $sql = "INSERT INTO contact_messages (name, email, phone, subject, message, ip_address, user_agent) 
            VALUES ('$name', '$email', '$phone', '$subject', '$message', '$ip', '$user_agent')";
    
    if ($conn->query($sql) === TRUE) {
        $message_sent = true;
    } else {
        echo "<!-- Database Error (visible in vulnerable mode): " . $conn->error . " -->";
    }
    
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - PeakPH</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/Global.css">
    <style>
        /* Page-specific styles */
        .contact-hero {
            background: linear-gradient(rgba(46, 118, 94, 0.85), rgba(61, 161, 128, 0.95)), url('../Assets/Gallery_Images/contact-hero.jpg');
            background-size: cover;
            background-position: center;
            height: 350px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            margin-bottom: 3rem;
        }

        .contact-hero h1 {
            font-size: 3rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            margin-bottom: 1rem;
        }

        .contact-hero p {
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .contact-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin-top: 2rem;
        }

        .contact-info {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .contact-info h2 {
            color: #2e765e;
            margin-bottom: 1.5rem;
            font-size: 1.75rem;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }

        .info-icon {
            font-size: 1.5rem;
            color: #2e765e;
            margin-right: 1rem;
            min-width: 30px;
        }

        .info-text h3 {
            color: #333;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .info-text p {
            color: #666;
            line-height: 1.6;
            margin: 0;
        }

        .contact-form {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .contact-form h2 {
            color: #2e765e;
            margin-bottom: 1.5rem;
            font-size: 1.75rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #2e765e;
        }

        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }

        .submit-btn {
            background: #2e765e;
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            width: 100%;
        }

        .submit-btn:hover {
            background: #245d4b;
            transform: translateY(-2px);
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #c3e6cb;
        }

        .map-section {
            margin-top: 3rem;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .map-section iframe {
            width: 100%;
            height: 400px;
            border: 0;
        }

        .hours-section {
            margin-top: 2rem;
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 12px;
        }

        .hours-section h3 {
            color: #2e765e;
            margin-bottom: 1rem;
        }

        .hours-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .hours-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem;
            background: white;
            border-radius: 6px;
        }

        .day {
            font-weight: 600;
            color: #333;
        }

        .time {
            color: #666;
        }

        @media (max-width: 968px) {
            .contact-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .contact-hero {
                height: 300px;
            }

            .contact-hero h1 {
                font-size: 2rem;
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
    <section class="contact-hero">
        <div>
            <h1>Get In Touch</h1>
            <p>Have questions? We're here to help! Reach out to us and we'll respond as soon as possible.</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="contact-content">
        <div class="contact-grid">
            <!-- Contact Information -->
            <div class="contact-info">
                <h2>Contact Information</h2>
                
                <div class="info-item">
                    <i class="bi bi-geo-alt info-icon"></i>
                    <div class="info-text">
                        <h3>Visit Us</h3>
                        <p>123 Mountain View Street<br>Quezon City, Metro Manila<br>Philippines 1100</p>
                    </div>
                </div>

                <div class="info-item">
                    <i class="bi bi-telephone info-icon"></i>
                    <div class="info-text">
                        <h3>Call Us</h3>
                        <p>Phone: +63 (2) 1234-5678<br>Mobile: +63 917 123 4567</p>
                    </div>
                </div>

                <div class="info-item">
                    <i class="bi bi-envelope info-icon"></i>
                    <div class="info-text">
                        <h3>Email Us</h3>
                        <p>General: info@peakph.com<br>Support: support@peakph.com</p>
                    </div>
                </div>

                <div class="info-item">
                    <i class="bi bi-clock info-icon"></i>
                    <div class="info-text">
                        <h3>Business Hours</h3>
                        <p>Monday - Friday: 9:00 AM - 6:00 PM<br>Saturday: 10:00 AM - 5:00 PM<br>Sunday: Closed</p>
                    </div>
                </div>

                <div class="hours-section">
                    <h3>Follow Us</h3>
                    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                        <a href="#" style="color: #2e765e; font-size: 1.5rem;"><i class="bi bi-facebook"></i></a>
                        <a href="#" style="color: #2e765e; font-size: 1.5rem;"><i class="bi bi-instagram"></i></a>
                        <a href="#" style="color: #2e765e; font-size: 1.5rem;"><i class="bi bi-twitter"></i></a>
                        <a href="#" style="color: #2e765e; font-size: 1.5rem;"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form">
                <h2>Send Us a Message</h2>
                
                <?php if ($message_sent): ?>
                    <div class="success-message">
                        <!-- VULNERABILITY: Reflected XSS - User input echoed without escaping -->
                        <i class="bi bi-check-circle"></i> Thank you <strong><?php echo $success_name; ?></strong> for your message! We'll get back to you soon.
                    </div>
                <?php endif; ?>
                
                <?php if ($reflected_message): ?>
                    <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; color: #856404;">
                        <!-- VULNERABILITY: Reflected XSS from GET parameter -->
                        Message: <?php echo $reflected_message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone">
                    </div>

                    <div class="form-group">
                        <label for="subject">Subject *</label>
                        <select id="subject" name="subject" required>
                            <option value="">Select a subject</option>
                            <option value="product">Product Inquiry</option>
                            <option value="order">Order Status</option>
                            <option value="support">Technical Support</option>
                            <option value="partnership">Partnership</option>
                            <option value="feedback">Feedback</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="message">Message *</label>
                        <textarea id="message" name="message" required></textarea>
                    </div>

                    <button type="submit" name="submit_contact" class="submit-btn">
                        <i class="bi bi-send"></i> Send Message
                    </button>
                </form>
            </div>
        </div>

        <!-- Map Section -->
        <div class="map-section">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3860.9007616091944!2d121.04965731483017!3d14.651732989757914!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397b7edcc23d011%3A0x1c5ef3e74b30c762!2sQuezon%20City%2C%20Metro%20Manila!5e0!3m2!1sen!2sph!4v1635000000000!5m2!1sen!2sph" 
                allowfullscreen="" 
                loading="lazy">
            </iframe>
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
