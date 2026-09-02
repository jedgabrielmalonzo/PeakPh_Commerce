/**
 * Global Cart Functionality
 * Unified cart functions for the entire site
 */

// Global add to cart function
function addToCart(productId, productName, productPrice, productImage, quantity = 1) {
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
            updateCartCount(data.cart_count);
            
            // Show success message
            showCartMessage(`${data.product_name} added to cart!`, 'success');
            
            // Optional: Update button state
            const btn = document.querySelector(`[data-product-id="${productId}"]`);
            if (btn) {
                updateButtonState(btn, 'added');
            }
            
        } else {
            showCartMessage('Failed to add product to cart: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showCartMessage('Error adding product to cart', 'error');
    });
}

// Bulk add to cart (for quantity > 1)
function bulkAddToCart(productId, productName, productPrice, productImage, quantity) {
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('product_name', productName);
    formData.append('product_price', productPrice);
    formData.append('product_image', productImage);

    // Add items one by one to handle stock properly
    let addedCount = 0;
    
    for (let i = 0; i < quantity; i++) {
        fetch('api/add_to_cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            addedCount++;
            
            if (data.success) {
                // Only show message and update UI after last item is added
                if (addedCount === quantity) {
                    updateCartCount(data.cart_count);
                    showCartMessage(`${quantity} x ${productName} added to cart!`, 'success');
                    
                    const btn = document.querySelector(`[data-product-id="${productId}"]`);
                    if (btn) {
                        updateButtonState(btn, 'added');
                    }
                }
            } else {
                showCartMessage('Failed to add product to cart: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (addedCount === quantity - 1) {
                showCartMessage('Error adding product to cart', 'error');
            }
        });
    }
}

// Update cart count in navigation
function updateCartCount(count) {
    const cartCounts = document.querySelectorAll('.cart-count');
    cartCounts.forEach(element => {
        element.textContent = count;
    });
}

// Update button state temporarily
function updateButtonState(button, state) {
    const originalHTML = button.innerHTML;
    const originalDisabled = button.disabled;
    
    if (state === 'added') {
        button.disabled = true;
        button.innerHTML = '<i class="bi bi-check"></i> Added!';
        
        // Reset after 2 seconds
        setTimeout(() => {
            button.disabled = originalDisabled;
            button.innerHTML = originalHTML;
        }, 2000);
    }
}

// Show cart-related messages
function showCartMessage(message, type = 'info') {
    // Remove existing cart messages
    const existingMessages = document.querySelectorAll('.cart-message');
    existingMessages.forEach(msg => msg.remove());
    
    // Create message element
    const messageDiv = document.createElement('div');
    messageDiv.className = `cart-message cart-message-${type}`;
    messageDiv.textContent = message;
    
    // Style the message
    messageDiv.style.cssText = `
        position: fixed;
        top: 80px;
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
        messageDiv.style.background = 'linear-gradient(135deg, #2e765e, #3da180)';
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

// Initialize cart functionality for buttons
function initCartButtons() {
    const addToCartButtons = document.querySelectorAll('.add-to-cart, .add-to-cart-btn');
    
    addToCartButtons.forEach(button => {
        // Remove existing listeners to prevent duplicates
        button.removeEventListener('click', handleAddToCartClick);
        button.addEventListener('click', handleAddToCartClick);
    });
}

// Handle add to cart button clicks
function handleAddToCartClick(e) {
    e.preventDefault();
    
    const button = e.target.closest('button');
    if (button.disabled) return;
    
    const productId = button.getAttribute('data-product-id');
    const productName = button.getAttribute('data-product-name');
    const productPrice = button.getAttribute('data-product-price');
    const productImage = button.getAttribute('data-product-image');
    
    // Get quantity if available (for product view pages)
    const quantityInput = document.getElementById('quantity');
    const quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;
    
    if (quantity > 1) {
        bulkAddToCart(productId, productName, productPrice, productImage, quantity);
    } else {
        addToCart(productId, productName, productPrice, productImage, quantity);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initCartButtons();
});

// Re-initialize cart buttons when new content is loaded (for dynamic content)
function refreshCartButtons() {
    initCartButtons();
}

// Export functions for global use
window.addToCart = addToCart;
window.bulkAddToCart = bulkAddToCart;
window.updateCartCount = updateCartCount;
window.showCartMessage = showCartMessage;
window.refreshCartButtons = refreshCartButtons;