/**
 * Wishlist Management System
 * Handles wishlist functionality across the site
 */

let wishlistData = [];

// Initialize wishlist on page load
document.addEventListener('DOMContentLoaded', function() {
  updateWishlistCount();
  
  // Close modal when clicking outside
  const modal = document.getElementById('wishlistModal');
  if (modal) {
    modal.addEventListener('click', function(e) {
      if (e.target === this) {
        toggleWishlistModal();
      }
    });
  }
  
  // Update wishlist count periodically
  setInterval(updateWishlistCount, 1000);
  
  // Listen for localStorage changes from other tabs/pages
  window.addEventListener('storage', function(e) {
    if (e.key === 'wishlist') {
      updateWishlistCount();
      // If modal is open, reload the wishlist
      const modal = document.getElementById('wishlistModal');
      if (modal && modal.classList.contains('active')) {
        loadWishlist();
      }
    }
  });
});

/**
 * Toggle wishlist modal visibility
 */
function toggleWishlistModal() {
  const modal = document.getElementById('wishlistModal');
  if (!modal) return;
  
  if (modal.classList.contains('active')) {
    modal.classList.remove('active');
  } else {
    modal.classList.add('active');
    loadWishlist();
  }
}

/**
 * Load wishlist items and display them
 */
function loadWishlist() {
  const wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
  const wishlistBody = document.getElementById('wishlistBody');
  
  if (!wishlistBody) return;
  
  if (wishlist.length === 0) {
    wishlistBody.innerHTML = `
      <div class="wishlist-empty">
        <i class="bi bi-heart"></i>
        <h3>Your wishlist is empty</h3>
        <p>Start adding products you love!</p>
      </div>
    `;
    return;
  }
  
  // Show loading state
  wishlistBody.innerHTML = `
    <div style="text-align: center; padding: 20px;">
      <i class="bi bi-arrow-repeat" style="font-size: 2rem; animation: spin 1s linear infinite;"></i>
      <p>Loading...</p>
    </div>
  `;
  
  // Fetch product details from API
  fetch(`api/get_wishlist_products.php?ids=${wishlist.join(',')}`)
    .then(response => response.json())
    .then(data => {
      if (data.success && data.products) {
        wishlistData = data.products;
        renderWishlistItems(data.products);
      } else {
        wishlistBody.innerHTML = `
          <div class="wishlist-empty">
            <i class="bi bi-exclamation-circle"></i>
            <h3>Error loading wishlist</h3>
            <p>${data.message || 'Please try again later'}</p>
          </div>
        `;
      }
    })
    .catch(error => {
      console.error('Error fetching wishlist:', error);
      // Fallback to page-based method if API fails
      fetchWishlistProducts(wishlist);
    });
}

/**
 * Fetch product details from the current page
 */
function fetchWishlistProducts(wishlist) {
  const wishlistBody = document.getElementById('wishlistBody');
  if (!wishlistBody) return;
  
  wishlistBody.innerHTML = `
    <div style="text-align: center; padding: 20px;">
      <i class="bi bi-arrow-repeat" style="font-size: 2rem; animation: spin 1s linear infinite;"></i>
      <p>Loading...</p>
    </div>
  `;
  
  const products = [];
  
  // Get product details from product cards on the page
  wishlist.forEach(productId => {
    // Try multiple selectors to find the product
    let productElement = document.querySelector(`[data-product-id="${productId}"]`);
    
    if (productElement) {
      // Find the parent card or link
      const card = productElement.closest('.seller-card, .arrival-card, .product-card');
      const link = card?.querySelector('a.card-link, a.product-link');
      
      let productName = 'Product';
      let productPrice = '0';
      let productImage = '/Assets/placeholder.svg';
      
      // Try to get name
      const nameElement = card?.querySelector('.product-name, h3, p.product-name');
      if (nameElement) productName = nameElement.textContent.trim();
      
      // Try to get price
      const priceElement = card?.querySelector('.price, .current-price, span.price');
      if (priceElement) {
        productPrice = priceElement.textContent.replace(/[^0-9.]/g, '');
      }
      
      // Try to get image
      const imgElement = card?.querySelector('img');
      if (imgElement && imgElement.src) {
        productImage = imgElement.src;
      }
      
      // Also check data attributes
      if (productElement.hasAttribute('data-product-name')) {
        productName = productElement.getAttribute('data-product-name');
      }
      if (productElement.hasAttribute('data-product-price')) {
        productPrice = productElement.getAttribute('data-product-price');
      }
      if (productElement.hasAttribute('data-product-image')) {
        productImage = productElement.getAttribute('data-product-image');
      }
      
      products.push({
        id: productId,
        name: productName,
        price: productPrice,
        image: productImage
      });
    }
  });
  
  wishlistData = products;
  renderWishlistItems(products);
}

/**
 * Render wishlist items in the modal
 */
function renderWishlistItems(products) {
  const wishlistBody = document.getElementById('wishlistBody');
  if (!wishlistBody) return;
  
  if (products.length === 0) {
    wishlistBody.innerHTML = `
      <div class="wishlist-empty">
        <i class="bi bi-heart"></i>
        <h3>Your wishlist is empty</h3>
        <p>Start adding products you love!</p>
      </div>
    `;
    return;
  }
  
  wishlistBody.innerHTML = products.map(product => {
    return `
      <div class="wishlist-item" data-wishlist-id="${product.id}">
        <img src="${product.image}" alt="${product.name}" class="wishlist-item-image" 
             onerror="this.onerror=null; this.src='Assets/placeholder.svg'">
        <div class="wishlist-item-details">
          <div class="wishlist-item-name">${product.name}</div>
          <div class="wishlist-item-price">₱${parseFloat(product.price).toFixed(2)}</div>
          <div class="wishlist-item-actions">
            <button class="wishlist-add-to-cart" 
                    data-product-id="${product.id}"
                    data-product-name="${product.name}"
                    data-product-price="${product.price}"
                    data-product-image="${product.image}">
              <i class="bi bi-cart-plus"></i> Add to Cart
            </button>
            <button class="wishlist-remove" data-product-id="${product.id}">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
      </div>
    `;
  }).join('');
  
  // Attach event listeners to buttons
  attachWishlistButtonListeners();
}

/**
 * Attach event listeners to wishlist buttons
 */
function attachWishlistButtonListeners() {
  // Add to cart buttons
  document.querySelectorAll('.wishlist-add-to-cart').forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      
      const productId = this.getAttribute('data-product-id');
      const productName = this.getAttribute('data-product-name');
      const productPrice = this.getAttribute('data-product-price');
      const productImage = this.getAttribute('data-product-image');
      
      if (!productId || !productName || !productPrice) {
        console.error('Missing product data:', { productId, productName, productPrice, productImage });
        return;
      }
      
      addWishlistItemToCart(productId, productName, productPrice, productImage);
    });
  });
  
  // Remove buttons
  document.querySelectorAll('.wishlist-remove').forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      
      const productId = this.getAttribute('data-product-id');
      
      if (!productId) {
        console.error('Missing product ID for remove button');
        return;
      }
      
      removeFromWishlistModal(productId);
    });
  });
}

/**
 * Add wishlist item to cart and remove from wishlist
 */
function addWishlistItemToCart(productId, productName, productPrice, productImage) {
  console.log('Adding to cart from wishlist:', productId, productName, productPrice, productImage);
  
  // Check if cart.js addToCart function is available
  if (typeof window.addToCart !== 'function') {
    console.error('addToCart function not found. Make sure cart.js is loaded.');
    
    // Fallback: use API directly
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('product_name', productName);
    formData.append('product_price', productPrice);
    formData.append('product_image', productImage);

    fetch('api/add_to_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart count
            const cartCounts = document.querySelectorAll('.cart-count');
            cartCounts.forEach(el => el.textContent = data.cart_count);
            
            // Remove from wishlist after successful add
            removeFromWishlistModal(productId);
            
            // Show success message
            if (typeof showCartMessage === 'function') {
                showCartMessage('Item moved to cart!', 'success');
            } else if (typeof showMessage === 'function') {
                showMessage('Item moved to cart!', 'success');
            }
        } else {
            if (typeof showCartMessage === 'function') {
                showCartMessage('Failed to add to cart: ' + (data.message || 'Unknown error'), 'error');
            } else {
                console.error('Failed to add to cart:', data.message);
            }
        }
    })
    .catch(error => {
        console.error('Error adding to cart:', error);
        if (typeof showCartMessage === 'function') {
            showCartMessage('Error adding to cart', 'error');
        }
    });
    
    return;
  }
  
  // Use the global addToCart function from cart.js
  window.addToCart(productId, productName, productPrice, productImage, 1);
  
  // Remove from wishlist after adding to cart
  setTimeout(() => {
    removeFromWishlistModal(productId);
  }, 500);
}

/**
 * Remove item from wishlist
 */
function removeFromWishlistModal(productId) {
  if (!productId) {
    console.error('Cannot remove item: productId is null or undefined');
    return;
  }
  
  let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
  wishlist = wishlist.filter(id => id !== String(productId));
  localStorage.setItem('wishlist', JSON.stringify(wishlist));
  
  // Animate removal
  const item = document.querySelector(`[data-wishlist-id="${productId}"]`);
  if (item) {
    item.style.transition = 'all 0.3s';
    item.style.opacity = '0';
    item.style.transform = 'translateX(100%)';
    setTimeout(() => {
      loadWishlist();
      updateWishlistCount();
    }, 300);
  } else {
    loadWishlist();
    updateWishlistCount();
  }
  
  // Update heart icon on the page
  const buttons = document.querySelectorAll(`[data-product-id="${productId}"]`);
  buttons.forEach(button => {
    if (button.classList.contains('add-to-wishlist') || button.classList.contains('wishlist-btn')) {
      button.classList.remove('active');
      const icon = button.querySelector('i');
      if (icon) {
        icon.classList.remove('bi-heart-fill');
        icon.classList.add('bi-heart');
      }
    }
  });
}

/**
 * Update wishlist count badge
 */
function updateWishlistCount() {
  const wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
  const countElements = document.querySelectorAll('.wishlist-count');
  countElements.forEach(el => {
    el.textContent = wishlist.length;
    el.style.display = wishlist.length > 0 ? 'flex' : 'none';
  });
}

/**
 * Override existing toggleWishlist to update count
 */
if (typeof window.toggleWishlist !== 'undefined') {
  const originalToggleWishlist = window.toggleWishlist;
  window.toggleWishlist = function(button) {
    originalToggleWishlist(button);
    setTimeout(updateWishlistCount, 100);
  };
}

// Export functions for global use
window.toggleWishlistModal = toggleWishlistModal;
window.loadWishlist = loadWishlist;
window.addWishlistItemToCart = addWishlistItemToCart;
window.removeFromWishlistModal = removeFromWishlistModal;
window.updateWishlistCount = updateWishlistCount;
