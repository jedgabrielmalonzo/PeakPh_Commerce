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

// Get search query from URL parameter
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get products from database
$products = [];
$use_database = false;

if (isDatabaseConnected()) {
    try {
        // Build query based on search
        if (!empty($search_query)) {
            $query = "SELECT id, product_name as name, price, image, tag, label, stock FROM inventory 
                      WHERE stock > 0 AND (product_name LIKE ? OR tag LIKE ?) 
                      ORDER BY RAND()";
            $search_param = "%$search_query%";
            $result = executeQuery($query, [$search_param, $search_param], "ss");
        } else {
            $query = "SELECT id, product_name as name, price, image, tag, label, stock FROM inventory WHERE stock > 0 ORDER BY RAND()";
            $result = executeQuery($query);
        }
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Fix image path for database products
                $image_path = 'Assets/placeholder.svg';
                if (!empty($row['image'])) {
                    // Check if image exists, if not use placeholder
                    if (file_exists('admin/' . $row['image'])) {
                        $image_path = 'admin/' . $row['image'];
                    } elseif (file_exists($row['image'])) {
                        $image_path = $row['image'];
                    }
                }
                
                $products[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'price' => number_format($row['price'], 2),
                    'price_raw' => $row['price'],
                    'image' => $image_path,
                    'category' => strtolower($row['tag'] ?? 'other'),
                    'badge' => $row['label'] ?? 'In Stock',
                    'rating' => '⭐⭐⭐⭐☆',
                    'reviews' => '(' . rand(50, 500) . ')',
                    'stock' => $row['stock'],
                    'is_database' => true
                ];
            }
            $use_database = true;
        }
    } catch (Exception $e) {
        error_log('ProductCatalog database error: ' . $e->getMessage());
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>PeakPH: Browse Items</title>
   <!-- Favicon -->
  <link rel="icon" type="image/png" href="Assets/Carousel_Picts/Logo.png" />
  
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="Css/Global.css" />
  <link rel="stylesheet" href="Css/prod.css" />
</head>
<body>
   <header>
  <div class="top-navbar">
  <div class="brand">
    <a href="index.php" class="logo-btn">
      <img src="Assets/Carousel_Picts/Logo.png" alt="Brand Logo">
    </a>
  </div>

  <div class="search-wrapper">
    <i class="bi bi-search"></i>
    <input type="search" id="productSearch" placeholder="Search products..." value="<?php echo htmlspecialchars($search_query); ?>">
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

<!-- BOTTOM NAVBAR -->
<div class="bottom-navbar">
  <nav>
    <a href="index.php"><i class="bi bi-house-door"></i> Home</a>
    <a href="ProductCatalog.php"><i class="bi bi-shop"></i> Shop</a>
    <a href="pages/climbers-community.php"><i class="bi bi-people"></i> Community</a>
    <a href="pages/contact-us.php"><i class="bi bi-envelope"></i> Contact Us</a>
    <a href="#deals" class="best-deals"><i class="bi bi-fire"></i> Best Deals</a>
    <a href="pages/about-us.php"><i class="bi bi-info-circle"></i> About Us</a>
  </nav>
</div>
</header>
	

  <main class="main-content">
    <div class="catalog-container">
      <!-- Filter Sidebar -->
      <aside class="filter-sidebar">
        <div class="filter-section">
          <h3>Filter by Category</h3>
          <div class="filter-options">
            <label class="filter-option">
              <input type="radio" name="category" value="all" checked>
              <span class="checkmark"></span>
              All Products
            </label>
            <label class="filter-option">
              <input type="radio" name="category" value="tents">
              <span class="checkmark"></span>
              Tents
            </label>
            <label class="filter-option">
              <input type="radio" name="category" value="cooking">
              <span class="checkmark"></span>
              Cooking Equipment
            </label>
            <label class="filter-option">
              <input type="radio" name="category" value="emergency">
              <span class="checkmark"></span>
              Emergency Kits/Tools
            </label>
          </div>
        </div>
        
        <div class="filter-section">
          <h3>Price Range</h3>
          <div class="price-range">
            <input type="range" id="priceRange" min="0" max="2000" value="2000" step="50">
            <div class="price-display">
              <span>₱0 - ₱<span id="priceValue">2000</span></span>
            </div>
          </div>
        </div>
        
        <button class="clear-filters">Clear All Filters</button>
      </aside>

      <!-- Products Section -->
      <section class="products-section">
        <div class="section-title">
          <h2>
            <?php if (!empty($search_query)): ?>
              Search Results for "<?php echo htmlspecialchars($search_query); ?>"
            <?php else: ?>
              Product Catalog
            <?php endif; ?>
          </h2>
          <?php if (!$use_database && !empty($products)): ?>
            <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; border-radius: 5px; margin: 10px 0; color: #856404;">
              <i class="bi bi-info-circle"></i> <strong>Demo Mode:</strong> Showing sample products. Add real products in the <a href="admin/inventory/inventory.php" target="_blank">admin panel</a>.
            </div>
          <?php endif; ?>
          <?php if (!empty($search_query) && empty($products)): ?>
            <div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 10px 0; color: #721c24;">
              <i class="bi bi-exclamation-circle"></i> <strong>No products found</strong> matching "<?php echo htmlspecialchars($search_query); ?>". Try different keywords or <a href="ProductCatalog.php">browse all products</a>.
            </div>
          <?php endif; ?>
          <p class="results-count"><span id="resultsCount"><?php echo count($products); ?></span> products found</p>
        </div>
        
        <!-- Sorting Options -->
        <div class="sorting-bar">
          <div class="sort-label">
            <i class="bi bi-sort-down"></i>
            <span>Sort by:</span>
          </div>
          <select id="sortSelect" class="sort-dropdown">
            <option value="clear">🧹 Clear Sorting</option>
            <option value="default">⭐ Featured</option>
            <option value="name-asc">🔤 Name (A-Z)</option>
            <option value="name-desc">🔤 Name (Z-A)</option>
            <option value="price-asc">💰 Price: Low to High</option>
            <option value="price-desc">💰 Price: High to Low</option>
            <option value="popularity">🔥 Popularity</option>
            <option value="newest">🕐 Newest First</option>
          </select>
        </div>
        
        <div class="products-grid">
          <?php foreach ($products as $product): ?>
            <div class="product-card" data-category="<?php echo htmlspecialchars($product['category']); ?>" data-price="<?php echo $product['price_raw']; ?>">
              <a href="ProductView.php?id=<?php echo urlencode($product['id']); ?>" class="product-link">
                <div class="product-image">
                  <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                  
                  <?php 
                  // Add product badges
                  $badge_class = '';
                  $badge_text = '';
                  
                  if (isset($product['badge']) && strtolower($product['badge']) === 'new arrival') {
                    $badge_class = 'new';
                    $badge_text = 'New';
                  } elseif (isset($product['category']) && strpos(strtolower($product['name']), 'blue') !== false) {
                    $badge_class = 'blue-product';
                    $badge_text = 'BLUE PRODUCT';
                  }
                  
                  if ($badge_text): ?>
                    <div class="product-badge <?php echo $badge_class; ?>">
                      <?php echo $badge_text; ?>
                    </div>
                  <?php endif; ?>
                  
                  <?php if ($product['stock'] < 10): ?>
                    <div style="position: absolute; top: 5px; right: 5px; background: #e74c3c; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.8em;">
                      Low Stock!
                    </div>
                  <?php endif; ?>
                </div>
                <div class="product-info">
                  <div class="product-rating">
                    <span style="color: #ffc107;"><?php echo $product['rating']; ?></span>
                    <span class="count"><?php echo $product['reviews']; ?></span>
                  </div>
                  <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                  <div class="product-price">
                    <span class="current-price">₱ <?php echo $product['price']; ?></span>
                  </div>
                  <div class="product-meta">
                    <?php echo htmlspecialchars($product['badge']); ?>
                    Stock: <?php echo $product['stock']; ?>
                  </div>
                </div>
              </a>
              
              <!-- Product Action Icons -->
              <div class="product-actions">
                <button class="add-to-wishlist" 
                        data-product-id="<?php echo htmlspecialchars($product['id']); ?>"
                        title="Add to Wishlist">
                  <i class="bi bi-heart"></i>
                </button>
                <button class="add-to-cart" 
                        data-product-id="<?php echo htmlspecialchars($product['id']); ?>" 
                        data-product-name="<?php echo htmlspecialchars($product['name']); ?>" 
                        data-product-price="<?php echo $product['price_raw']; ?>" 
                        data-product-image="<?php echo htmlspecialchars($product['image']); ?>"
                        <?php echo ($product['stock'] <= 0) ? 'disabled' : ''; ?>
                        title="<?php echo ($product['stock'] <= 0) ? 'Out of Stock' : 'Add to Cart'; ?>">
                  <i class="fa-solid fa-cart-shopping"></i>
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    </div>
  </main>

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

<script>
// JavaScript for add to cart functionality and filtering
document.addEventListener('DOMContentLoaded', function() {
  const addToCartButtons = document.querySelectorAll('.add-to-cart');
  const categoryFilters = document.querySelectorAll('input[name="category"]');
  const priceRange = document.getElementById('priceRange');
  const priceValue = document.getElementById('priceValue');
  const clearFiltersBtn = document.querySelector('.clear-filters');
  const resultsCount = document.getElementById('resultsCount');
  const searchInput = document.getElementById('productSearch');
  
  // Add to cart functionality is now handled by the global cart.js file
  
  // Filter functionality
  function filterProducts() {
    const selectedCategory = document.querySelector('input[name="category"]:checked').value;
    const maxPrice = parseInt(priceRange.value);
    const productCards = document.querySelectorAll('.product-card');
    let visibleCount = 0;
    
    productCards.forEach(card => {
      const category = card.getAttribute('data-category');
      const price = parseInt(card.getAttribute('data-price'));
      
      const categoryMatch = selectedCategory === 'all' || category === selectedCategory;
      const priceMatch = price <= maxPrice;
      
      if (categoryMatch && priceMatch) {
        card.style.display = 'block';
        visibleCount++;
      } else {
        card.style.display = 'none';
      }
    });
    
    resultsCount.textContent = visibleCount;
  }
  
  // Category filter listeners
  categoryFilters.forEach(filter => {
    filter.addEventListener('change', filterProducts);
  });
  
  // Price range listener
  priceRange.addEventListener('input', function() {
    priceValue.textContent = this.value;
    filterProducts();
  });
  
  // Clear filters
  clearFiltersBtn.addEventListener('click', function() {
    document.querySelector('input[value="all"]').checked = true;
    priceRange.value = 2000;
    priceValue.textContent = 2000;
    filterProducts();
  });
  
  // Sorting functionality
  const sortSelect = document.getElementById('sortSelect');
  let currentSort = 'featured';
  
  sortSelect.addEventListener('change', function() {
    // Get sort type from selected option
    currentSort = this.value;
      if (currentSort === 'clear') {
        // Reset to default sorting (featured)
        sortProducts('featured');
        sortSelect.value = 'featured';
        return;
      }
    
    // Sort products
    sortProducts(currentSort);
  });
  
  function sortProducts(sortType) {
    const productsGrid = document.querySelector('.products-grid');
    const productCards = Array.from(document.querySelectorAll('.product-card'));
    
    // Store current display status to maintain filter
    const visibleCards = productCards.filter(card => card.style.display !== 'none');
    
    let sortedCards;
    
    switch(sortType) {
      case 'name-asc':
        sortedCards = visibleCards.sort((a, b) => {
          const nameA = a.querySelector('h3').textContent.toLowerCase();
          const nameB = b.querySelector('h3').textContent.toLowerCase();
          return nameA.localeCompare(nameB);
        });
        break;
        
      case 'name-desc':
        sortedCards = visibleCards.sort((a, b) => {
          const nameA = a.querySelector('h3').textContent.toLowerCase();
          const nameB = b.querySelector('h3').textContent.toLowerCase();
          return nameB.localeCompare(nameA);
        });
        break;
        
      case 'price-asc':
        sortedCards = visibleCards.sort((a, b) => {
          const priceA = parseFloat(a.getAttribute('data-price'));
          const priceB = parseFloat(b.getAttribute('data-price'));
          return priceA - priceB;
        });
        break;
        
      case 'price-desc':
        sortedCards = visibleCards.sort((a, b) => {
          const priceA = parseFloat(a.getAttribute('data-price'));
          const priceB = parseFloat(b.getAttribute('data-price'));
          return priceB - priceA;
        });
        break;
        
      case 'popularity':
        sortedCards = visibleCards.sort((a, b) => {
          // Extract review count from text like "(234)"
          const reviewsA = parseInt(a.querySelector('.count').textContent.replace(/[()]/g, '')) || 0;
          const reviewsB = parseInt(b.querySelector('.count').textContent.replace(/[()]/g, '')) || 0;
          return reviewsB - reviewsA; // Higher reviews = more popular
        });
        break;
        
      case 'newest':
        sortedCards = visibleCards.sort((a, b) => {
          // Check for "New Arrival" badge
          const badgeA = a.querySelector('.product-badge.new') ? 1 : 0;
          const badgeB = b.querySelector('.product-badge.new') ? 1 : 0;
          if (badgeA !== badgeB) return badgeB - badgeA;
          // If both have or don't have badge, sort by ID (newer = higher ID)
          const idA = parseInt(a.querySelector('.add-to-cart').getAttribute('data-product-id'));
          const idB = parseInt(b.querySelector('.add-to-cart').getAttribute('data-product-id'));
          return idB - idA;
        });
        break;
        
      case 'default':
      default:
        // Return to original order (database order)
        sortedCards = visibleCards.sort((a, b) => {
          const idA = parseInt(a.querySelector('.add-to-cart').getAttribute('data-product-id'));
          const idB = parseInt(b.querySelector('.add-to-cart').getAttribute('data-product-id'));
          return idA - idB;
        });
        break;
    }
    
    // Clear grid
    productsGrid.innerHTML = '';
    
    // Re-append sorted cards
    sortedCards.forEach(card => {
      productsGrid.appendChild(card);
    });
    
    // Re-append hidden cards at the end
    productCards.filter(card => card.style.display === 'none').forEach(card => {
      productsGrid.appendChild(card);
    });
    
    // Add animation
    sortedCards.forEach((card, index) => {
      card.style.opacity = '0';
      card.style.transform = 'translateY(20px)';
      setTimeout(() => {
        card.style.transition = 'all 0.3s ease';
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
      }, index * 30);
    });
  }
  
  // Search functionality
  let searchTimeout;
  if (searchInput) {
    // Handle Enter key press
    searchInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        const searchTerm = this.value.trim();
        if (searchTerm.length > 0) {
          // Redirect to same page with search parameter
          window.location.href = `ProductCatalog.php?search=${encodeURIComponent(searchTerm)}`;
        } else {
          // Clear search and reload
          window.location.href = 'ProductCatalog.php';
        }
      }
    });
    
    // Real-time search with debounce
    searchInput.addEventListener('input', function() {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        const searchTerm = this.value.trim();
        if (searchTerm.length >= 2) {
          performSearch(searchTerm);
        } else if (searchTerm.length === 0) {
          // Reset to show all products based on current filters
          filterProducts();
        }
      }, 300);
    });
  }
  
  function performSearch(searchTerm) {
    const selectedCategory = document.querySelector('input[name="category"]:checked').value;
    const maxPrice = parseInt(priceRange.value);
    
    fetch(`search_products.php?q=${encodeURIComponent(searchTerm)}&category=${selectedCategory}&max_price=${maxPrice}`)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          updateProductGrid(data.products);
          resultsCount.textContent = data.count;
        }
      })
      .catch(error => {
        console.error('Search error:', error);
      });
  }
  
  function updateProductGrid(products) {
    const productsGrid = document.querySelector('.products-grid');
    productsGrid.innerHTML = '';
    
    products.forEach(product => {
      const productCard = createProductCard(product);
      productsGrid.appendChild(productCard);
    });
    
    // Reattach event listeners to new add-to-cart buttons
    refreshCartButtons();
  }
  
  function createProductCard(product) {
    const card = document.createElement('div');
    card.className = 'product-card';
    card.setAttribute('data-category', product.category);
    card.setAttribute('data-price', product.price_raw);
    
    card.innerHTML = `
      <a href="ProductView.php?id=${encodeURIComponent(product.id)}" class="product-link">
        <div class="product-image">
          <img src="${product.image}" alt="${product.name}">
          ${product.stock < 10 ? '<div style="position: absolute; top: 5px; right: 5px; background: #e74c3c; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.8em;">Low Stock!</div>' : ''}
        </div>
        <div class="product-info">
          <div class="product-rating">
            <span style="color: #ffc107;">${product.rating}</span>
            <span class="count">${product.reviews}</span>
          </div>
          <h3>${product.name}</h3>
          <div class="product-price">
            <span class="current-price">₱ ${product.price}</span>
          </div>
          <div class="product-meta">
            ${product.badge} Stock: ${product.stock}
          </div>
        </div>
      </a>
      <div class="product-actions">
        <button class="add-to-wishlist" 
                data-product-id="${product.id}"
                title="Add to Wishlist">
          <i class="bi bi-heart"></i>
        </button>
        <button class="add-to-cart" 
                data-product-id="${product.id}" 
                data-product-name="${product.name}" 
                data-product-price="${product.price_raw}" 
                data-product-image="${product.image}"
                ${product.stock <= 0 ? 'disabled' : ''}
                title="${product.stock <= 0 ? 'Out of Stock' : 'Add to Cart'}">
          <i class="fa-solid fa-cart-shopping"></i>
        </button>
      </div>
      <div class="card-toast" style="display:none;"></div>
    `;
    
    return card;
  }
  
  // Cart functionality is now handled by the global cart.js file
  
  // Initial filter
  filterProducts();
  
  // Update initial count
  resultsCount.textContent = document.querySelectorAll('.product-card').length;
  
  // Wishlist functionality
  function initWishlistButtons() {
    const wishlistButtons = document.querySelectorAll('.add-to-wishlist');
    const wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
    
    // Mark already wishlisted items
    wishlistButtons.forEach(btn => {
      const productId = btn.getAttribute('data-product-id');
      if (wishlist.includes(productId)) {
        btn.classList.add('active');
        btn.querySelector('i').classList.remove('bi-heart');
        btn.querySelector('i').classList.add('bi-heart-fill');
      }
      
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        toggleWishlist(this);
      });
    });
  }
  
  function toggleWishlist(button) {
    const productId = button.getAttribute('data-product-id');
    let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
    const icon = button.querySelector('i');
    
    if (wishlist.includes(productId)) {
      // Remove from wishlist
      wishlist = wishlist.filter(id => id !== productId);
      button.classList.remove('active');
      icon.classList.remove('bi-heart-fill');
      icon.classList.add('bi-heart');
      showNotification('Removed from wishlist', 'info');
    } else {
      // Add to wishlist
      wishlist.push(productId);
      button.classList.add('active');
      icon.classList.remove('bi-heart');
      icon.classList.add('bi-heart-fill');
      showNotification('Added to wishlist!', 'success');
    }
    
    localStorage.setItem('wishlist', JSON.stringify(wishlist));
  }
  
  function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      background: ${type === 'success' ? '#10b981' : '#6b7280'};
      color: white;
      padding: 12px 20px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      z-index: 10000;
      font-size: 14px;
      animation: slideIn 0.3s ease;
    `;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Remove after 2 seconds
    setTimeout(() => {
      notification.style.animation = 'slideOut 0.3s ease';
      setTimeout(() => notification.remove(), 300);
    }, 2000);
  }
  
  // Initialize wishlist buttons
  initWishlistButtons();
  
  // Re-initialize wishlist buttons after filtering/searching
  const originalRefreshCartButtons = window.refreshCartButtons || function() {};
  window.refreshCartButtons = function() {
    originalRefreshCartButtons();
    initWishlistButtons();
    // Attach improved add-to-cart notification
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    addToCartButtons.forEach(btn => {
      btn.removeEventListener('click', handleAddToCartToast);
      btn.addEventListener('click', handleAddToCartToast);
    });
  };

  function handleAddToCartToast(e) {
    const button = e.target.closest('button');
    if (button.disabled) return;
    const card = button.closest('.product-card');
    const toast = card.querySelector('.card-toast');
    // Call global addToCart
    const productId = button.getAttribute('data-product-id');
    const productName = button.getAttribute('data-product-name');
    const productPrice = button.getAttribute('data-product-price');
    const productImage = button.getAttribute('data-product-image');
    window.addToCart(productId, productName, productPrice, productImage, 1);
    // Show toast
    if (toast) {
      toast.innerHTML = '<span style="display:flex;align-items:center;gap:8px;"><i class="bi bi-check-circle-fill" style="color:#27ae60;font-size:1.2em;"></i> <span>Added to cart!</span></span>';
      toast.style.display = 'block';
      toast.style.position = 'absolute';
      toast.style.top = '-32px';
      toast.style.right = '10px';
      toast.style.background = 'linear-gradient(135deg, #27ae60, #2ecc71)';
      toast.style.color = 'white';
      toast.style.padding = '8px 16px';
      toast.style.borderRadius = '8px';
      toast.style.boxShadow = '0 2px 8px rgba(0,0,0,0.12)';
      toast.style.fontWeight = '500';
      toast.style.zIndex = '10';
      toast.style.opacity = '0';
      toast.style.transition = 'opacity 0.3s';
      setTimeout(() => { toast.style.opacity = '1'; }, 50);
      setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => { toast.style.display = 'none'; }, 300);
      }, 2000);
    }
    // Cart button feedback
    button.disabled = true;
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="bi bi-check"></i>';
    setTimeout(() => {
      button.disabled = false;
      button.innerHTML = originalHTML;
    }, 1200);
  }
});
</script>

<!-- Scripts -->
<script src="Js/user_dropdown.js"></script>
<script src="Js/cart.js"></script>
<script src="components/auth_modal_otp.js"></script>

<!-- Wishlist Styles -->
<style>
  /* Sorting Bar Styles */
  .sorting-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 15px;
  padding: 20px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  margin-bottom: 25px;
  }
  
  .sort-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
  color: #2e765e;
  font-size: 1rem;
  white-space: nowrap;
  flex: none;
  }
  
  .sort-label i {
    font-size: 1.2rem;
  }
  
  .sort-dropdown {
  flex: none;
  max-width: 350px;
  margin-left: auto;
  padding: 12px 16px;
  font-size: 1rem;
  font-weight: 500;
  color: #495057;
  background: linear-gradient(135deg, #f8f9fa, #fff);
  border: 2px solid #e9ecef;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
  outline: none;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%232e765e' viewBox='0 0 16 16'%3E%3Cpath d='M8 11.5l-5-5h10l-5 5z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  padding-right: 40px;
  }
  
  .sort-dropdown:hover {
    border-color: #2e765e;
    box-shadow: 0 4px 12px rgba(46, 118, 94, 0.15);
    background: white;
  }
  
  .sort-dropdown:focus {
    border-color: #2e765e;
    box-shadow: 0 0 0 3px rgba(46, 118, 94, 0.1);
    background: white;
  }
  
  .sort-dropdown option {
    padding: 10px;
    font-size: 1rem;
  }
  
  @media (max-width: 768px) {
    .sorting-bar {
      flex-direction: column;
      align-items: stretch;
      gap: 12px;
    }
    .sort-dropdown {
      max-width: 100%;
      margin-left: 0;
    }
  }
  
  .wishlist-link { position: relative; color: white; text-decoration: none; font-size: 1.5rem; transition: color 0.3s; }
  .wishlist-link:hover { color: #ffd700; }
  .wishlist-count { position: absolute; top: -8px; right: -10px; background: #e74c3c; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: bold; }
  .wishlist-modal { display: none; position: fixed; top: 0; right: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 10000; animation: fadeIn 0.3s ease; }
  .wishlist-modal.active { display: block; }
  .wishlist-modal-content { position: fixed; right: 0; top: 0; height: 100%; width: 450px; max-width: 90%; background: white; box-shadow: -4px 0 20px rgba(0, 0, 0, 0.2); animation: slideInRight 0.3s ease; display: flex; flex-direction: column; }
  @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
  @keyframes slideInRight { from { transform: translateX(100%); } to { transform: translateX(0); } }
  .wishlist-header { background: linear-gradient(135deg, #2e765e, #3da180); color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
  .wishlist-header h2 { margin: 0; font-size: 1.5rem; display: flex; align-items: center; gap: 10px; }
  .close-wishlist { background: none; border: none; color: white; font-size: 2rem; cursor: pointer; line-height: 1; transition: transform 0.2s; }
  .close-wishlist:hover { transform: scale(1.2); }
  .wishlist-body { flex: 1; overflow-y: auto; padding: 20px; }
  .wishlist-empty { text-align: center; padding: 60px 20px; color: #999; }
  .wishlist-empty i { font-size: 4rem; color: #ddd; margin-bottom: 20px; }
  .wishlist-item { display: flex; gap: 15px; padding: 15px; border: 1px solid #eee; border-radius: 12px; margin-bottom: 15px; transition: all 0.3s; background: white; }
  .wishlist-item:hover { box-shadow: 0 4px 12px rgba(46, 118, 94, 0.1); border-color: #2e765e; }
  .wishlist-item-image { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; background: #f8f8f8; }
  .wishlist-item-details { flex: 1; display: flex; flex-direction: column; gap: 5px; }
  .wishlist-item-name { font-weight: 600; color: #333; font-size: 0.95rem; line-height: 1.3; }
  .wishlist-item-price { color: #2e765e; font-weight: 700; font-size: 1.1rem; }
  .wishlist-item-actions { display: flex; gap: 8px; margin-top: 8px; }
  .wishlist-add-to-cart { background: linear-gradient(135deg, #2e765e, #3da180); color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 0.85rem; font-weight: 500; transition: all 0.3s; display: flex; align-items: center; gap: 6px; }
  .wishlist-add-to-cart:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(46, 118, 94, 0.3); }
  .wishlist-remove { background: #f8f9fa; color: #e74c3c; border: 1px solid #e74c3c; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 0.85rem; font-weight: 500; transition: all 0.3s; }
  .wishlist-remove:hover { background: #e74c3c; color: white; }
  @media (max-width: 768px) { .wishlist-modal-content { width: 100%; max-width: 100%; } }
  @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>

<script src="Js/wishlist.js"></script>

</body>
</html>