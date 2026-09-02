<?php
require_once 'includes/user_auth.php';
require_once 'includes/db.php';

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Calculate total items in cart
$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['quantity'];
}

// Get current user info
$current_user = getCurrentUser();
$is_logged_in = isUserLoggedIn();

// Load Best Seller products from database
$bestSellerProducts = [];
if (isDatabaseConnected()) {
    try {
        $query = "SELECT id, product_name as name, price, image, tag, stock, label, created_at 
                  FROM inventory 
                  WHERE label LIKE '%Best%' OR label LIKE '%🏆%' OR label LIKE '%Bestseller%'
                  ORDER BY created_at DESC
                  LIMIT 10";
        $result = executeQuery($query);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $reviewCount = rand(100, 500);
                $rating = number_format(rand(35, 50) / 10, 1);
                $stars = str_repeat('⭐', floor($rating)) . (($rating - floor($rating)) >= 0.5 ? '☆' : '');
                
                $image_path = 'Assets/placeholder.svg';
                if (!empty($row['image'])) {
                    if (file_exists('admin/' . $row['image'])) {
                        $image_path = 'admin/' . $row['image'];
                    } elseif (file_exists($row['image'])) {
                        $image_path = $row['image'];
                    }
                }
                
                $bestSellerProducts[] = [
                    'link' => 'ProductView.php?id=' . $row['id'],
                    'image' => $image_path,
                    'alt' => $row['name'],
                    'badge' => $row['label'] ?? 'Best Seller',
                    'name' => $row['name'],
                    'desc' => $row['tag'] ? ucfirst($row['tag']) : 'Premium Product',
                    'rating' => $rating,
                    'reviews' => $reviewCount,
                    'price' => number_format($row['price'], 2),
                    'stock' => $row['stock']
                ];
            }
        }
    } catch (Exception $e) {
        error_log('Best Seller loading error: ' . $e->getMessage());
    }
}

// Load New Arrivals from database
$newArrivals = [];
if (isDatabaseConnected()) {
    try {
        $query = "SELECT id, product_name as name, price, image, tag, stock, label, created_at 
                  FROM inventory 
                  WHERE label LIKE '%New%' OR label LIKE '%🆕%' OR label LIKE '%Arrival%'
                  ORDER BY created_at DESC
                  LIMIT 10";
        $result = executeQuery($query);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $image_path = 'Assets/placeholder.svg';
                if (!empty($row['image'])) {
                    if (file_exists('admin/' . $row['image'])) {
                        $image_path = 'admin/' . $row['image'];
                    } elseif (file_exists($row['image'])) {
                        $image_path = $row['image'];
                    }
                }
                
                $newArrivals[] = [
                    'link' => 'ProductView.php?id=' . $row['id'],
                    'image' => $image_path,
                    'alt' => $row['name'],
                    'name' => $row['name'],
                    'price' => number_format($row['price'], 2)
                ];
            }
        }
    } catch (Exception $e) {
        error_log('New Arrivals loading error: ' . $e->getMessage());
    }
}

// Fallback to old data files if database is not available
if (empty($bestSellerProducts)) {
    $promoCard = @include __DIR__ . '/admin/content/bestseller_data.php';
    if (!empty($promoCard['products'])) {
        $bestSellerProducts = $promoCard['products'];
    }
}

if (empty($newArrivals)) {
    $arrivalsData = @include __DIR__ . '/admin/content/new_arrivals_data.php';
    if (!empty($arrivalsData['arrivals'])) {
        $newArrivals = $arrivalsData['arrivals'];
    }
}

// Promo card data (still using file for promo content)
$promoCard = @include __DIR__ . '/admin/content/bestseller_data.php';
if (!$promoCard) {
    $promoCard = [
        'title' => 'Adventure Awaits!',
        'subtitle' => 'Gear up for your next outdoor expedition',
        'offer_tag' => 'Special Offer',
        'offer' => '20% OFF',
        'offer_desc' => 'on all camping gear',
        'details' => 'Limited time offer',
        'button_link' => 'ProductCatalog.php',
        'button_text' => 'Shop Now →'
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PeakPH: Camping Gears and More</title>

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="Assets/Carousel_Picts/Logo.png" />

  <!-- Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

  <!-- Global Styles -->
  <link rel="stylesheet" href="Css/Global.css" />
  <link rel="stylesheet" href="Css/landingcomponents.css" />
  <link rel="stylesheet" href="Css/carousel.css" />

  <!-- Google API -->
  <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>

<body>
  <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
    <div class="alert alert-success" id="logoutMessage">
      You have been successfully logged out.
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['login']) && $_GET['login'] === 'success'): ?>
    <div class="alert alert-success" id="loginMessage">
      Welcome back! You have been successfully logged in.
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['login']) && $_GET['login'] === 'failed'): ?>
    <div class="alert alert-error" id="loginErrorMessage">
      <?php echo htmlspecialchars($_GET['error'] ?? 'Login failed. Please try again.'); ?>
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['signup']) && $_GET['signup'] === 'success'): ?>
    <div class="alert alert-success" id="signupMessage">
      Account created successfully! Welcome to PeakPH!
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['signup']) && $_GET['signup'] === 'failed'): ?>
    <div class="alert alert-error" id="signupErrorMessage">
      <?php echo htmlspecialchars($_GET['error'] ?? 'Signup failed. Please try again.'); ?>
    </div>
  <?php endif; ?>

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
        <a href="ProductCatalog.php">Shop</a>
        <a href="pages/climbers-community.php">Community</a>
        <a href="pages/contact-us.php">Contact Us</a>
        <a href="#deals" class="best-deals">Best Deals</a>
        <a href="pages/about-us.php">About us</a>
      </nav>
    </div>
  </header>

  <!-- HERO -->
<?php
$carouselSlides = include __DIR__ . '/admin/content/carousel_data.php';
?>
<div class="hero">
    <div class="slides" id="slides">
        <?php foreach ($carouselSlides as $i => $slide): ?>
            <div class="slide <?= htmlspecialchars($slide['class']) ?>" style="background-image: url('<?= htmlspecialchars($slide['image']) ?>')">
                <?php if (!empty($slide['link']) && !empty($slide['button'])): ?>
                    <a href="<?= htmlspecialchars($slide['link']) ?>" class="shop-btn"><?= htmlspecialchars($slide['button']) ?></a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <button class="arrow prev" onclick="moveSlide(-1)">‹</button>
    <button class="arrow next" onclick="moveSlide(1)">›</button>
</div>
                  
  <!-- MID CONTAINER -->
  <section class="best-seller">
  <h2>Best Sellers</h2>
  <div class="best-seller-flex">
    <!-- Promotional Content -->
    <div class="promo-content">
      <h3><?= htmlspecialchars($promoCard['title'] ?? 'Adventure Awaits!') ?></h3>
      <p class="promo-text"><?= htmlspecialchars($promoCard['subtitle'] ?? 'Gear up for your next outdoor expedition with our top-rated camping essentials.') ?></p>
      <div class="special-offer">
        <span class="offer-tag"><?= htmlspecialchars($promoCard['offer_tag'] ?? 'Special Offer') ?></span>
        <h4><?= htmlspecialchars($promoCard['offer'] ?? '20% OFF') ?></h4>
        <p><?= htmlspecialchars($promoCard['offer_desc'] ?? 'on all camping gear') ?></p>
      </div>
      <p class="promo-details"><?= htmlspecialchars($promoCard['details'] ?? 'Limited time offer for outdoor enthusiasts. Quality equipment for unforgettable adventures.') ?></p>
      <a href="<?= htmlspecialchars($promoCard['button_link'] ?? 'ProductCatalog.php') ?>" class="promo-cta">
        <?= htmlspecialchars($promoCard['button_text'] ?? 'Shop Now →') ?>
      </a>
    </div>

    <!-- Best Seller Grid Slider -->
    <div class="seller-slider-container" style="position:relative; flex:1; min-width:0;">
      <button class="slider-arrow left" id="sellerSliderLeft" style="position:absolute; left:-18px; top:50%; transform:translateY(-50%); background:#fff; border:none; border-radius:50%; box-shadow:0 2px 8px rgba(0,0,0,0.08); width:36px; height:36px; display:none; align-items:center; justify-content:center; z-index:2; cursor:pointer;"><i class="bi bi-chevron-left"></i></button>
      <div class="seller-grid" id="sellerGrid" style="overflow-x:auto; display:flex; gap:20px; scroll-behavior:smooth; padding-bottom:8px; scrollbar-width:none; -ms-overflow-style:none;">
        <?php if (!empty($bestSellerProducts)): ?>
          <?php foreach ($bestSellerProducts as $product): 
            $productId = $product['link'] ? str_replace('ProductView.php?id=', '', $product['link']) : '0';
          ?>
            <div class="seller-card" 
                 data-product-id="<?= htmlspecialchars($productId) ?>"
                 data-product-name="<?= htmlspecialchars($product['name']) ?>"
                 data-product-price="<?= htmlspecialchars($product['price']) ?>"
                 data-product-image="<?= htmlspecialchars($product['image']) ?>"
                 style="min-width:270px; max-width:300px; flex:0 0 270px; position: relative;">
              <a href="<?= htmlspecialchars($product['link']) ?>" class="card-link">
                <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['alt']) ?>" />
                <span class="badge"><?= htmlspecialchars($product['badge']) ?></span>
                <p class="product-name"><?= htmlspecialchars($product['name']) ?></p>
                <p class="product-desc"><?= htmlspecialchars($product['desc']) ?></p>
                <div class="rating">
                  <?php
                    $rating = isset($product['rating']) ? (float)$product['rating'] : 0;
                    $fullStars = floor($rating);
                    $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0;
                    $emptyStars = 5 - $fullStars - $halfStar;
                    echo str_repeat('⭐', $fullStars);
                    if ($halfStar) echo '☆';
                    echo str_repeat('☆', $emptyStars);
                  ?>
                  <span class="review-count">(<?= htmlspecialchars($product['reviews']) ?>)</span>
                </div>
                <span class="price">₱<?= htmlspecialchars($product['price']) ?></span>
              </a>
              
              <!-- Action Icons -->
              <div class="card-actions">
                <button class="wishlist-btn" onclick="event.preventDefault(); event.stopPropagation(); toggleWishlist(<?= $product['link'] ? str_replace('ProductView.php?id=', '', $product['link']) : '0' ?>);" title="Add to Wishlist">
                  <i class="bi bi-heart"></i>
                </button>
                <button class="cart-btn" onclick="event.preventDefault(); event.stopPropagation(); addToCartIndex(<?= $product['link'] ? str_replace('ProductView.php?id=', '', $product['link']) : '0' ?>);" title="Add to Cart">
                  <i class="bi bi-bag-plus"></i>
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div style="color:#888; font-size:1.1em; padding:30px 0;">
            No best seller products available. Go to <a href="admin/inventory/inventory.php">Inventory</a> to label products.
          </div>
        <?php endif; ?>
      </div>
      <button class="slider-arrow right" id="sellerSliderRight" style="position:absolute; right:-18px; top:50%; transform:translateY(-50%); background:#fff; border:none; border-radius:50%; box-shadow:0 2px 8px rgba(0,0,0,0.08); width:36px; height:36px; display:none; align-items:center; justify-content:center; z-index:2; cursor:pointer;"><i class="bi bi-chevron-right"></i></button>
    </div>
  <style>
    .seller-slider-container .slider-arrow { transition: background 0.2s; }
    .seller-slider-container .slider-arrow:active { background: #e0e0e0; }
    .seller-grid::-webkit-scrollbar { display: none; }
    @media (max-width: 900px) {
      .seller-slider-container .slider-arrow { display:none!important; }
    }
    
    /* Card Actions Styles */
    .card-actions {
      position: absolute;
      bottom: 10px;
      right: 10px;
      display: flex;
      flex-direction: row;
      gap: 8px;
      opacity: 1 !important;
      transform: translateX(0) !important;
      transition: all 0.3s ease;
      z-index: 10;
    }
    
    /* Always show icons for better visibility */
    .seller-card .card-actions,
    .arrival-card .card-actions {
      opacity: 1 !important;
      transform: translateX(0) !important;
    }
    
    .wishlist-btn,
    .cart-btn {
      width: 36px !important;
      height: 36px !important;
      border: none !important;
      border-radius: 50% !important;
      background: rgba(255, 255, 255, 0.95) !important;
      color: #666 !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      cursor: pointer !important;
      transition: all 0.3s ease !important;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15) !important;
      backdrop-filter: blur(10px) !important;
      font-size: 16px !important;
      position: relative !important;
    }
    
    .wishlist-btn:hover {
      background: #ff6b6b !important;
      color: white !important;
      transform: scale(1.05) !important;
    }
    
    .wishlist-btn.active {
      background: #ff6b6b !important;
      color: white !important;
    }
    
    .cart-btn:hover {
      background: #2e765e !important;
      color: white !important;
      transform: scale(1.05) !important;
    }
    
    .cart-btn.added {
      background: #28a745 !important;
      color: white !important;
    }
    
    .card-link {
      display: block;
      text-decoration: none;
      color: inherit;
      padding-bottom: 50px; /* Make space for bottom icons */
    }
    
    /* Ensure cards have proper positioning */
    .seller-card,
    .arrival-card {
      position: relative !important;
      overflow: hidden !important;
      min-width: 270px !important;
      max-width: 300px !important;
      flex: 0 0 270px !important;
    }
    
    /* Adjust card content to make space for icons */
    .seller-card .price,
    .arrival-card .price {
      margin-bottom: 40px !important;
    }
    
    @media (max-width: 768px) {
      .card-actions {
        opacity: 1;
        transform: translateX(0);
        bottom: 5px;
        right: 5px;
      }
      
      .wishlist-btn,
      .cart-btn {
        width: 32px !important;
        height: 32px !important;
        font-size: 14px !important;
      }
    }
  </style>
  <script>
    // Card slider logic for best sellers
    document.addEventListener('DOMContentLoaded', function() {
      const grid = document.getElementById('sellerGrid');
      const leftBtn = document.getElementById('sellerSliderLeft');
      const rightBtn = document.getElementById('sellerSliderRight');
      function updateArrows() {
        if (!grid) return;
        // Show arrows only if more than 4 cards
        const cardCount = grid.querySelectorAll('.seller-card').length;
        if (cardCount > 4) {
          leftBtn.style.display = 'flex';
          rightBtn.style.display = 'flex';
        } else {
          leftBtn.style.display = 'none';
          rightBtn.style.display = 'none';
        }
      }
      function scrollGrid(dir) {
        if (!grid) return;
        // Scroll by the width of one card (plus gap)
        const card = grid.querySelector('.seller-card');
        if (card) {
          const scrollAmount = card.offsetWidth + 20; // 20px gap
          grid.scrollBy({ left: dir * scrollAmount, behavior: 'smooth' });
        }
      }
      if (leftBtn && rightBtn) {
        leftBtn.addEventListener('click', () => scrollGrid(-1));
        rightBtn.addEventListener('click', () => scrollGrid(1));
      }
      updateArrows();
      window.addEventListener('resize', updateArrows);
    });
  </script>
  </div>
</section>

  <!-- NEW ARRIVALS -->
  <section class="new-arrivals">
    <h2 style="color: white">New Arrivals</h2>
    <div class="arrivals-slider-container">
      <button class="arrivals-slider-arrow left" id="arrivalsSliderLeft">
        <i class="bi bi-chevron-left"></i>
      </button>
      <div class="arrivals-grid" id="arrivalsGrid">
        <?php if (!empty($newArrivals)): ?>
          <?php foreach ($newArrivals as $arrival): 
            $productId = $arrival['link'] ? str_replace('ProductView.php?id=', '', $arrival['link']) : '0';
          ?>
            <div class="arrival-card" 
                 data-product-id="<?= htmlspecialchars($productId) ?>"
                 data-product-name="<?= htmlspecialchars($arrival['name']) ?>"
                 data-product-price="<?= htmlspecialchars($arrival['price']) ?>"
                 data-product-image="<?= htmlspecialchars($arrival['image']) ?>"
                 style="position: relative;">
              <a href="<?= htmlspecialchars($arrival['link']) ?>" class="card-link">
                <img src="<?= htmlspecialchars($arrival['image']) ?>" alt="<?= htmlspecialchars($arrival['alt']) ?>" />
                <p class="product-name"><?= htmlspecialchars($arrival['name']) ?></p>
                <span class="price">₱<?= htmlspecialchars($arrival['price']) ?></span>
              </a>
              
              <!-- Action Icons -->
              <div class="card-actions">
                <button class="wishlist-btn" onclick="event.preventDefault(); event.stopPropagation(); toggleWishlist(<?= $arrival['link'] ? str_replace('ProductView.php?id=', '', $arrival['link']) : '0' ?>);" title="Add to Wishlist">
                  <i class="bi bi-heart"></i>
                </button>
                <button class="cart-btn" onclick="event.preventDefault(); event.stopPropagation(); addToCartIndex(<?= $arrival['link'] ? str_replace('ProductView.php?id=', '', $arrival['link']) : '0' ?>);" title="Add to Cart">
                  <i class="bi bi-bag-plus"></i>
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div style="color:#888; font-size:1.1em; padding:30px 0;">
            No new arrivals available. Go to <a href="admin/inventory/inventory.php">Inventory</a> to label products.
          </div>
        <?php endif; ?>
      </div>
      <button class="arrivals-slider-arrow right" id="arrivalsSliderRight">
        <i class="bi bi-chevron-right"></i>
      </button>
    </div>
  </section>

  <script>
    // New Arrivals slider logic
    document.addEventListener('DOMContentLoaded', function() {
      const arrivalsGrid = document.getElementById('arrivalsGrid');
      const leftBtn = document.getElementById('arrivalsSliderLeft');
      const rightBtn = document.getElementById('arrivalsSliderRight');

      function updateArrivalsSlider() {
        if (!arrivalsGrid) return;
        
        const cardCount = arrivalsGrid.querySelectorAll('.arrival-card').length;
        
        if (cardCount <= 4) {
          // Use grid layout for 4 or fewer cards
          arrivalsGrid.classList.add('grid-layout');
          leftBtn.style.display = 'none';
          rightBtn.style.display = 'none';
        } else {
          // Use slider layout for more than 4 cards
          arrivalsGrid.classList.remove('grid-layout');
          leftBtn.style.display = 'flex';
          rightBtn.style.display = 'flex';
        }
      }

      function scrollArrivalsGrid(direction) {
        if (!arrivalsGrid || arrivalsGrid.classList.contains('grid-layout')) return;
        
        const cardWidth = 250 + 32; // card width + gap
        arrivalsGrid.scrollBy({ 
          left: direction * cardWidth, 
          behavior: 'smooth' 
        });
      }

      if (leftBtn && rightBtn) {
        leftBtn.addEventListener('click', () => scrollArrivalsGrid(-1));
        rightBtn.addEventListener('click', () => scrollArrivalsGrid(1));
      }

      updateArrivalsSlider();
      window.addEventListener('resize', updateArrivalsSlider);
    });
  </script>

  
  <!-- NEWSLETTER SIGNUP -->
  <section class="newsletter">
    <div class="newsletter-content">
      <h2>Join Our Adventure Club</h2>
      <p>Get exclusive deals, camping tips, and updates straight to your inbox.</p>
      <form class="newsletter-form">
        <input type="email" placeholder="Enter your email" required />
        <button type="submit">Subscribe</button>
      </form>
    </div>
  </section>

  

  <!-- AUTH MODAL -->
  <?php include 'components/auth_modal.php'; ?>

  <!-- PROMOTIONAL POPUP -->
  <div id="promoPopup" class="promo-popup-overlay">
    <div class="promo-popup-container">
      <button class="promo-close-btn" onclick="closePromoPopup()">&times;</button>
      
      <div class="promo-content">
        <div class="promo-header">
          <div class="promo-badge">LIMITED TIME OFFER!</div>
          <h2 class="promo-title">🎉 Welcome to PeakPH! 🎉</h2>
          <p class="promo-subtitle">Exclusive deals just for you</p>
        </div>
        
        <div class="promo-body">
          <div class="promo-deal">
            <div class="deal-icon">🔥</div>
            <div class="deal-text">
              <h3>Flash Sale</h3>
              <p>Up to <span class="highlight">50% OFF</span> on selected items</p>
            </div>
          </div>
          
          <div class="promo-deal">
            <div class="deal-icon">🚚</div>
            <div class="deal-text">
              <h3>Free Shipping</h3>
              <p>On orders over <span class="highlight">₱1,000</span></p>
            </div>
          </div>
          
          <div class="promo-deal">
            <div class="deal-icon">🎁</div>
            <div class="deal-text">
              <h3>New Customer Bonus</h3>
              <p>Get <span class="highlight">15% OFF</span> your first order</p>
            </div>
          </div>
        </div>
        
        <div class="promo-footer">
          <a href="ProductCatalog.php" class="promo-btn-primary">Shop Now</a>
          <button onclick="closePromoPopup()" class="promo-btn-secondary">Maybe Later</button>
        </div>
        
        <div class="promo-timer">
          <i class="bi bi-clock"></i> Offer ends in: <span id="promoTimer">23:59:59</span>
        </div>
      </div>
    </div>
  </div>

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

  <!-- CHATBOT -->
  <div id="chatbot-icon">💬</div>
  <div id="chatbot-container" class="hidden">
    <div id="chatbot-header">
      <span>Peak Bot</span>
      <button id="close-btn">&times;</button>
    </div>
    <div id="chatbot-body">
      <div id="chatbot-messages"></div>
    </div>
    <div id="chatbot-input-container">
      <input type="text" id="chatbot-input" placeholder="Type a message" />
      <button id="send-btn">Send</button>
    </div>
  </div>

  <!-- FOOTER -->
  <?php
  require_once __DIR__ . '/admin/content/footer_functions.php';
  $footerData = getFooterData();
  ?>
  <footer class="site-footer">
    <div class="footer-top">
      <div class="social-section">
        <p class="follow-text">Follow Us</p>
        <div class="social-icons">
          <?php if (!empty($footerData['facebook_link'])): ?>
            <a href="<?= htmlspecialchars($footerData['facebook_link']) ?>" target="_blank" rel="noopener"><i class="bi bi-facebook"></i></a>
          <?php endif; ?>
          <?php if (!empty($footerData['instagram_link'])): ?>
            <a href="<?= htmlspecialchars($footerData['instagram_link']) ?>" target="_blank" rel="noopener"><i class="bi bi-instagram"></i></a>
          <?php endif; ?>
          <?php if (!empty($footerData['youtube_link'])): ?>
            <a href="<?= htmlspecialchars($footerData['youtube_link']) ?>" target="_blank" rel="noopener"><i class="bi bi-youtube"></i></a>
          <?php endif; ?>
          <?php if (!empty($footerData['tiktok_link'])): ?>
            <a href="<?= htmlspecialchars($footerData['tiktok_link']) ?>" target="_blank" rel="noopener"><i class="bi bi-tiktok"></i></a>
          <?php endif; ?>
          <?php if (!empty($footerData['twitter_link'])): ?>
            <a href="<?= htmlspecialchars($footerData['twitter_link']) ?>" target="_blank" rel="noopener"><i class="bi bi-twitter"></i></a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <hr />

    <div class="footer-links">
      <?php foreach ($footerData['footer_links'] as $category => $links): ?>
        <div>
          <h4><?= htmlspecialchars($category) ?></h4>
          <?php foreach ($links as $title => $url): ?>
            <a href="<?= htmlspecialchars($url) ?>"><?= htmlspecialchars($title) ?></a>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <hr />

    <div class="footer-bottom">
      <small><?= htmlspecialchars($footerData['copyright_text']) ?></small>
    </div>
  </footer>

  <!-- SCRIPTS -->
  <script src="Js/user_dropdown.js"></script>
  <script src="Js/cart.js"></script>
  <script src="Js/JavaScript.js"></script>
  <script src="components/auth_modal_otp.js"></script>
  <script src="Js/chatbot.js"></script>
  <script src="Js/wishlist.js"></script>
  
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
        
        // Optional: Handle search on input with debounce for instant search
        let searchTimeout;
        headerSearch.addEventListener('input', function() {
          clearTimeout(searchTimeout);
          const searchTerm = this.value.trim();
          
          if (searchTerm.length >= 2) {
            searchTimeout = setTimeout(() => {
              // Show search suggestions (optional feature for future)
              console.log('Searching for:', searchTerm);
            }, 300);
          }
        });
      }
      
      // Show promotional popup once per session
      showPromoPopup();
      
      // Start countdown timer
      startPromoTimer();
    });
    
    // Promotional Popup Functions
    function showPromoPopup() {
      // Check if popup has been shown in this session
      const popupShown = sessionStorage.getItem('promoPopupShown');
      
      if (!popupShown) {
        // Show popup after a short delay for better UX
        setTimeout(() => {
          const popup = document.getElementById('promoPopup');
          if (popup) {
            popup.classList.add('active');
            // Mark as shown in session storage
            sessionStorage.setItem('promoPopupShown', 'true');
          }
        }, 1500); // Show after 1.5 seconds
      }
    }
    
    function closePromoPopup() {
      const popup = document.getElementById('promoPopup');
      if (popup) {
        popup.classList.remove('active');
      }
    }
    
    // Close popup when clicking outside
    document.addEventListener('click', function(e) {
      const popup = document.getElementById('promoPopup');
      if (popup && e.target === popup) {
        closePromoPopup();
      }
    });
    
    // Countdown timer for promotional offer
    function startPromoTimer() {
      const timerElement = document.getElementById('promoTimer');
      if (!timerElement) return;
      
      // Set end time to midnight
      const now = new Date();
      const midnight = new Date();
      midnight.setHours(23, 59, 59, 999);
      
      function updateTimer() {
        const now = new Date();
        const timeLeft = midnight - now;
        
        if (timeLeft <= 0) {
          // Reset to next day
          midnight.setDate(midnight.getDate() + 1);
          return;
        }
        
        const hours = Math.floor((timeLeft / (1000 * 60 * 60)) % 24);
        const minutes = Math.floor((timeLeft / (1000 * 60)) % 60);
        const seconds = Math.floor((timeLeft / 1000) % 60);
        
        timerElement.textContent = 
          String(hours).padStart(2, '0') + ':' +
          String(minutes).padStart(2, '0') + ':' +
          String(seconds).padStart(2, '0');
      }
      
      updateTimer();
      setInterval(updateTimer, 1000);
    }
  </script>
  <script>
    // Debug: Check if cards and actions exist
    document.addEventListener('DOMContentLoaded', function() {
      console.log('Cards found:', document.querySelectorAll('.seller-card, .arrival-card').length);
      console.log('Action buttons found:', document.querySelectorAll('.card-actions').length);
      console.log('Wishlist buttons found:', document.querySelectorAll('.wishlist-btn').length);
      console.log('Cart buttons found:', document.querySelectorAll('.cart-btn').length);
      
      // Initialize wishlist button states from localStorage
      initWishlistButtons();
    });
    
    // Wishlist functionality - Initialize buttons
    function initWishlistButtons() {
      const wishlistButtons = document.querySelectorAll('.wishlist-btn');
      const wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
      
      console.log('Current wishlist:', wishlist);
      
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
    
    // Wishlist functionality for index page
    function toggleWishlist(productId) {
      console.log('Wishlist clicked for product:', productId);
      const btn = event.target.closest('.wishlist-btn');
      const icon = btn.querySelector('i');
      
      // Get current wishlist from localStorage
      let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
      const productIdStr = String(productId);
      
      if (wishlist.includes(productIdStr)) {
        // Remove from wishlist
        wishlist = wishlist.filter(id => id !== productIdStr);
        btn.classList.remove('active');
        icon.classList.remove('bi-heart-fill');
        icon.classList.add('bi-heart');
        showMessage('Removed from wishlist', 'info');
      } else {
        // Add to wishlist
        wishlist.push(productIdStr);
        btn.classList.add('active');
        icon.classList.remove('bi-heart');
        icon.classList.add('bi-heart-fill');
        showMessage('Added to wishlist!', 'success');
      }
      
      // Save to localStorage
      localStorage.setItem('wishlist', JSON.stringify(wishlist));
      
      // Update wishlist count
      if (typeof updateWishlistCount === 'function') {
        updateWishlistCount();
      }
    }
    
    // Add to cart functionality for index page (renamed to avoid conflict with cart.js)
    function addToCartIndex(productId) {
      console.log('Add to cart clicked for product:', productId);
      
      if (!event || !event.target) {
        console.error('Event not available, using cart.js addToCart instead');
        return;
      }
      
      const btn = event.target.closest('.cart-btn');
      if (!btn) {
        console.error('Cart button not found');
        return;
      }
      
      const icon = btn.querySelector('i');
      if (!icon) {
        console.error('Icon not found in button');
        return;
      }
      
      // Show adding state
      btn.classList.add('added');
      icon.className = 'bi bi-check-circle-fill';
      showMessage('Added to cart!', 'success');
      
      // Update cart count in header
      const cartCount = document.querySelector('.cart-count');
      if (cartCount) {
        const currentCount = parseInt(cartCount.textContent) || 0;
        cartCount.textContent = currentCount + 1;
      }
      
      // Reset button after 2 seconds
      setTimeout(() => {
        btn.classList.remove('added');
        icon.className = 'bi bi-bag-plus';
      }, 2000);
    }
    
    // Show message function (if not already available)
    function showMessage(message, type = 'info') {
      // Remove existing messages
      const existingMessages = document.querySelectorAll('.temp-message');
      existingMessages.forEach(msg => msg.remove());
      
      // Create message element
      const messageDiv = document.createElement('div');
      messageDiv.className = `temp-message temp-message-${type}`;
      messageDiv.textContent = message;
      
      // Style the message
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
      
      // Set colors based on type
      if (type === 'success') {
        messageDiv.style.background = 'linear-gradient(135deg, #27ae60, #2ecc71)';
      } else if (type === 'error') {
        messageDiv.style.background = 'linear-gradient(135deg, #e74c3c, #c0392b)';
      } else {
        messageDiv.style.background = 'linear-gradient(135deg, #3498db, #2980b9)';
      }
      
      document.body.appendChild(messageDiv);
      
      // Animate in
      setTimeout(() => {
        messageDiv.style.transform = 'translateX(0)';
      }, 100);
      
      // Auto remove after 3 seconds
      setTimeout(() => {
        messageDiv.style.transform = 'translateX(100%)';
        setTimeout(() => {
          if (messageDiv.parentNode) {
            messageDiv.parentNode.removeChild(messageDiv);
          }
        }, 300);
      }, 3000);
    }
  </script>
  <script>
    // Logout functionality
    function handleLogout() {
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = 'logout.php';
        }
    }

    // User dropdown toggle
    function toggleUserDropdown() {
        const dropdown = document.getElementById('userDropdown');
        const userDropdownContainer = document.querySelector('.user-dropdown');
        
        if (dropdown) {
            dropdown.classList.toggle('show');
            if (userDropdownContainer) {
                userDropdownContainer.classList.toggle('active');
            }
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('userDropdown');
        const userDropdown = document.querySelector('.user-dropdown');
        
        if (dropdown && userDropdown && !userDropdown.contains(event.target)) {
            dropdown.classList.remove('show');
            userDropdown.classList.remove('active');
        }
    });

    // Prevent dropdown from closing when clicking inside it
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('userDropdown');
        if (dropdown && dropdown.contains(event.target) && !event.target.matches('a')) {
            event.stopPropagation();
        }
    });

    // Hide logout message after 3 seconds
    const logoutMessage = document.getElementById('logoutMessage');
    if (logoutMessage) {
        setTimeout(() => {
            logoutMessage.style.display = 'none';
        }, 3000);
    }
  </script>
  
  <!-- Wishlist Styles and Functionality -->
  <style>
    /* Wishlist icon in navbar */
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
    
    /* Wishlist Modal */
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
    
    /* Promotional Popup Styles - Enhanced Design */
    .promo-popup-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.75);
      backdrop-filter: blur(5px);
      z-index: 10001;
      justify-content: center;
      align-items: center;
      animation: fadeIn 0.3s ease;
    }
    
    .promo-popup-overlay.active {
      display: flex;
    }
    
    .promo-popup-container {
      position: relative;
      background: linear-gradient(145deg, #2e765e 0%, #3da180 100%);
      border-radius: 24px;
      max-width: 520px;
      width: 90%;
      max-height: 90vh;
      overflow: hidden;
      box-shadow: 0 25px 70px rgba(0, 0, 0, 0.4);
      animation: slideUp 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    
    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(60px) scale(0.85);
      }
      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }
    
    .promo-close-btn {
      position: absolute;
      top: 20px;
      right: 20px;
      background: rgba(255, 255, 255, 0.95);
      border: none;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      font-size: 26px;
      color: #2e765e;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
      z-index: 10;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      font-weight: 300;
      line-height: 1;
    }
    
    .promo-close-btn:hover {
      background: #fff;
      color: #e74c3c;
      transform: rotate(90deg) scale(1.1);
      box-shadow: 0 6px 20px rgba(231, 76, 60, 0.3);
    }
    
    .promo-content {
      padding: 35px 30px 30px 30px;
      overflow-y: auto;
      max-height: 90vh;
    }
    
    .promo-header {
      text-align: center;
      margin-bottom: 30px;
    }
    
    .promo-badge {
      display: inline-block;
      background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
      color: white;
      padding: 10px 24px;
      border-radius: 25px;
      font-size: 0.8rem;
      font-weight: 700;
      letter-spacing: 1.5px;
      margin-bottom: 18px;
      animation: pulse 2s ease-in-out infinite;
      box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
      text-transform: uppercase;
    }
    
    @keyframes pulse {
      0%, 100% {
        transform: scale(1);
        box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
      }
      50% {
        transform: scale(1.08);
        box-shadow: 0 8px 25px rgba(255, 107, 107, 0.6);
      }
    }
    
    .promo-title {
      font-size: 2rem;
      font-weight: 800;
      color: white;
      margin: 10px 0;
      line-height: 1.2;
      text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    }
    
    .promo-subtitle {
      color: rgba(255, 255, 255, 0.95);
      font-size: 1.05rem;
      margin: 8px 0 0 0;
      font-weight: 400;
    }
    
    .promo-body {
      margin: 30px 0;
    }
    
    .promo-deal {
      display: flex;
      align-items: center;
      gap: 18px;
      padding: 18px 20px;
      background: rgba(255, 255, 255, 0.98);
      border-radius: 16px;
      margin-bottom: 14px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
      border: 2px solid transparent;
    }
    
    .promo-deal:hover {
      border-color: rgba(255, 255, 255, 0.5);
      transform: translateX(8px) scale(1.02);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
      background: white;
    }
    
    .deal-icon {
      font-size: 3rem;
      min-width: 60px;
      text-align: center;
      filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
    }
    
    .deal-text h3 {
      margin: 0 0 6px 0;
      font-size: 1.2rem;
      color: #2e765e;
      font-weight: 700;
    }
    
    .deal-text p {
      margin: 0;
      color: #555;
      font-size: 0.95rem;
      font-weight: 500;
    }
    
    .highlight {
      color: #2e765e;
      font-weight: 800;
      font-size: 1.15em;
      text-decoration: none;
      background: linear-gradient(135deg, #2e765e, #3da180);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    .promo-footer {
      display: flex;
      gap: 12px;
      margin-top: 28px;
    }
    
    .promo-btn-primary {
      flex: 1;
      background: white;
      color: #2e765e;
      padding: 16px 30px;
      border: none;
      border-radius: 12px;
      font-size: 1.15rem;
      font-weight: 700;
      cursor: pointer;
      text-decoration: none;
      display: block;
      text-align: center;
      transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
      box-shadow: 0 6px 20px rgba(255, 255, 255, 0.3);
      letter-spacing: 0.5px;
    }
    
    .promo-btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 30px rgba(255, 255, 255, 0.4);
      background: linear-gradient(135deg, #fff, #f8fffe);
    }
    
    .promo-btn-secondary {
      flex: 1;
      background: rgba(255, 255, 255, 0.15);
      color: white;
      padding: 16px 30px;
      border: 2px solid rgba(255, 255, 255, 0.4);
      border-radius: 12px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
      backdrop-filter: blur(10px);
    }
    
    .promo-btn-secondary:hover {
      background: rgba(255, 255, 255, 0.25);
      border-color: rgba(255, 255, 255, 0.6);
      transform: translateY(-2px);
    }
    
    .promo-timer {
      text-align: center;
      margin-top: 22px;
      padding: 14px 18px;
      background: rgba(255, 243, 205, 0.95);
      border-radius: 12px;
      color: #856404;
      font-weight: 700;
      font-size: 1rem;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      border: 2px solid rgba(255, 235, 156, 0.5);
    }
    
    .promo-timer i {
      margin-right: 6px;
      font-size: 1.1em;
    }
    
    .promo-timer #promoTimer {
      font-family: 'Courier New', monospace;
      font-size: 1.1em;
      letter-spacing: 1px;
      color: #d97706;
    }
    
    /* Add sparkle effect */
    .promo-popup-container::before {
      content: '✨';
      position: absolute;
      top: 80px;
      left: 30px;
      font-size: 2rem;
      animation: sparkle 3s ease-in-out infinite;
      opacity: 0.8;
    }
    
    .promo-popup-container::after {
      content: '✨';
      position: absolute;
      top: 80px;
      right: 30px;
      font-size: 2rem;
      animation: sparkle 3s ease-in-out infinite 1.5s;
      opacity: 0.8;
    }
    
    @keyframes sparkle {
      0%, 100% {
        transform: scale(1) rotate(0deg);
        opacity: 0.8;
      }
      50% {
        transform: scale(1.3) rotate(180deg);
        opacity: 1;
      }
    }
    
    @media (max-width: 768px) {
      .promo-popup-container {
        max-width: 95%;
        margin: 10px;
        border-radius: 20px;
      }
      
      .promo-content {
        padding: 30px 20px 25px 20px;
      }
      
      .promo-title {
        font-size: 1.6rem;
      }
      
      .promo-footer {
        flex-direction: column;
      }
      
      .deal-icon {
        font-size: 2.5rem;
        min-width: 55px;
      }
      
      .deal-text h3 {
        font-size: 1.1rem;
      }
      
      .promo-close-btn {
        width: 36px;
        height: 36px;
        font-size: 24px;
      }
    }
    
    @keyframes spin {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
  </style>
</body>
</html>
          