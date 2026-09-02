/**
 * User Dropdown Functionality
 * Handles user dropdown menu interactions and logout
 */

// Toggle user dropdown visibility
function toggleUserDropdown() {
    const dropdown = document.querySelector('.user-dropdown');
    const dropdownMenu = document.getElementById('userDropdown');
    
    if (!dropdown || !dropdownMenu) return;
    
    dropdown.classList.toggle('active');
    dropdownMenu.classList.toggle('show');
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        if (!dropdown.contains(event.target)) {
            dropdown.classList.remove('active');
            dropdownMenu.classList.remove('show');
        }
    });
}

// Handle user logout
function handleLogout() {
    if (confirm('Are you sure you want to logout?')) {
        // Show loading state
        const logoutLink = document.querySelector('.logout-link');
        if (logoutLink) {
            const originalText = logoutLink.innerHTML;
            logoutLink.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Logging out...';
            logoutLink.style.pointerEvents = 'none';
        }
        
        // Perform logout
        fetch('logout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'logout=1'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear any local storage data
                localStorage.removeItem('cart');
                localStorage.removeItem('wishlist');
                
                // Show success message
                showMessage('Successfully logged out!', 'success');
                
                // Redirect after a short delay
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 1000);
            } else {
                showMessage('Logout failed. Please try again.', 'error');
                // Restore logout link
                if (logoutLink) {
                    logoutLink.innerHTML = originalText;
                    logoutLink.style.pointerEvents = 'auto';
                }
            }
        })
        .catch(error => {
            console.error('Logout error:', error);
            showMessage('Logout failed. Please try again.', 'error');
            // Restore logout link
            if (logoutLink) {
                logoutLink.innerHTML = originalText;
                logoutLink.style.pointerEvents = 'auto';
            }
        });
    }
}

// Show temporary message
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

// Initialize dropdown functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Close dropdown when pressing Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const dropdown = document.querySelector('.user-dropdown.active');
            const dropdownMenu = document.querySelector('.user-dropdown-menu.show');
            
            if (dropdown) dropdown.classList.remove('active');
            if (dropdownMenu) dropdownMenu.classList.remove('show');
        }
    });
    
    // Prevent dropdown from closing when clicking inside the menu
    const dropdownMenu = document.getElementById('userDropdown');
    if (dropdownMenu) {
        dropdownMenu.addEventListener('click', function(event) {
            event.stopPropagation();
        });
    }
});

// Export functions for global use
window.toggleUserDropdown = toggleUserDropdown;
window.handleLogout = handleLogout;
window.showMessage = showMessage;