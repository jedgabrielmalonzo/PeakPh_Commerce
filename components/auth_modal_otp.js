/**
 * Auth Modal Component JavaScript
 * Complete modal functionality with form switching and backend integration
 */

// Auth Modal functionality
function initAuthModal() {
  const loginIcon = document.getElementById("loginIcon");
  const authModal = document.getElementById("authModal");
  const closeModalBtn = document.getElementById("closeModal");
  
  // Form elements
  const loginForm = document.getElementById("loginForm");
  const signupForm = document.getElementById("signupForm");
  
  // Form switching buttons
  const showSignupBtn = document.getElementById("showSignup");
  const showLoginBtn = document.getElementById("showLogin");

  // Check if required elements exist
  if (!loginIcon || !authModal || !closeModalBtn) {
    console.warn('Auth modal elements not found. Modal functionality disabled.');
    return;
  }

  // Open modal (always show login form first)
  loginIcon.addEventListener("click", (e) => {
    e.preventDefault();
    authModal.classList.add("active");
    showLoginForm();
  });

  // Close modal
  closeModalBtn.addEventListener("click", () => {
    authModal.classList.remove("active");
    showLoginForm(); // Reset to login form when closing
  });

  // Close modal when clicking outside
  authModal.addEventListener("click", (e) => {
    if (e.target === authModal) {
      authModal.classList.remove("active");
      showLoginForm(); // Reset to login form when closing
    }
  });

  // Form switching functions
  function showLoginForm() {
    if (loginForm) loginForm.style.display = "block";
    if (signupForm) signupForm.style.display = "none";
  }

  function showSignupForm() {
    if (loginForm) loginForm.style.display = "none";
    if (signupForm) signupForm.style.display = "block";
  }

  // Form switching event listeners
  if (showSignupBtn) {
    showSignupBtn.addEventListener("click", (e) => {
      e.preventDefault();
      showSignupForm();
    });
  }

  if (showLoginBtn) {
    showLoginBtn.addEventListener("click", (e) => {
      e.preventDefault();
      showLoginForm();
    });
  }



  // Password visibility toggle is now handled in auth_modal.php

  // Handle login form submission
  const emailLoginForm = document.getElementById('emailLoginForm');
  if (emailLoginForm) {
    emailLoginForm.addEventListener('submit', handleLogin);
  }

  // Handle signup form submission
  const emailSignupForm = document.getElementById('emailSignupForm');
  if (emailSignupForm) {
    emailSignupForm.addEventListener('submit', handleSignup);
  }



  console.log('Auth modal initialized with form switching and backend integration');
}

// Handle login form submission
async function handleLogin(e) {
  e.preventDefault();
  
  const form = e.target;
  const formData = new FormData(form);
  const submitBtn = form.querySelector('button[type="submit"]');
  
  const email = formData.get('email');
  const password = formData.get('password');
  
  // Basic validation
  if (!email || !password) {
    showMessage('Please fill in all fields', 'error');
    return;
  }
  
  if (!isValidEmail(email)) {
    showMessage('Please enter a valid email address', 'error');
    return;
  }
  
  // Show loading state
  const originalText = submitBtn.textContent;
  submitBtn.textContent = 'Logging in...';
  submitBtn.disabled = true;
  
  try {
    const response = await fetch('auth/login_handler.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        email: email,
        password: password
      })
    });
    
    const result = await response.json();
    
    if (result.success) {
      // Show success message
      showMessage('Login successful! Welcome back!', 'success');
      
      // Close modal and reload page
      setTimeout(() => {
        hideAuthModal();
        window.location.reload();
      }, 1500);
    } else {
      showMessage(result.message || 'Login failed', 'error');
    }
  } catch (error) {
    console.error('Login error:', error);
    showMessage('Network error. Please try again.', 'error');
  } finally {
    // Reset button state
    submitBtn.textContent = originalText;
    submitBtn.disabled = false;
  }
}

// Handle signup form submission
async function handleSignup(e) {
  e.preventDefault();
  
  const form = e.target;
  const formData = new FormData(form);
  const submitBtn = form.querySelector('button[type="submit"]');
  const btnText = submitBtn.querySelector('.btn-text');
  const btnLoading = submitBtn.querySelector('.btn-loading');
  
  const fullName = formData.get('full_name');
  const email = formData.get('email');
  const password = formData.get('password');
  const confirmPassword = formData.get('confirm_password');
  
  // Basic validation
  if (!fullName || !email || !password || !confirmPassword) {
    showMessage('Please fill in all fields', 'error');
    return;
  }
  
  if (!isValidEmail(email)) {
    showMessage('Please enter a valid email address', 'error');
    return;
  }
  
  if (password.length < 6) {
    showMessage('Password must be at least 6 characters long', 'error');
    return;
  }
  
  if (password !== confirmPassword) {
    showMessage('Passwords do not match', 'error');
    return;
  }
  
  // Show loading state
  btnText.style.display = 'none';
  btnLoading.style.display = 'inline-flex';
  submitBtn.disabled = true;
  
  try {
    const response = await fetch('auth/signup_handler.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        full_name: fullName,
        email: email,
        password: password,
        confirm_password: confirmPassword
      })
    });
    
    const result = await response.json();
    
    if (result.success) {
      // Show success message and close modal
      showMessage('Account created successfully! Welcome to PeakPH!', 'success');
      
      // Close modal and reload page
      setTimeout(() => {
        hideAuthModal();
        window.location.reload();
      }, 1500);
    } else {
      showMessage(result.message || 'Signup failed', 'error');
    }
  } catch (error) {
    console.error('Signup error:', error);
    showMessage('Network error. Please try again.', 'error');
  } finally {
    // Reset button state
    btnText.style.display = 'inline';
    btnLoading.style.display = 'none';
    submitBtn.disabled = false;
  }
}



// Show message function
function showMessage(message, type = 'info') {
  // Remove existing messages
  const existingMessages = document.querySelectorAll('.auth-message');
  existingMessages.forEach(msg => msg.remove());
  
  // Create message element
  const messageDiv = document.createElement('div');
  messageDiv.className = `auth-message auth-message-${type}`;
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
  
  // Auto remove after 5 seconds
  setTimeout(() => {
    messageDiv.style.transform = 'translateX(100%)';
    setTimeout(() => {
      if (messageDiv.parentNode) {
        messageDiv.parentNode.removeChild(messageDiv);
      }
    }, 300);
  }, 5000);
}

// Email validation helper
function isValidEmail(email) {
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailPattern.test(email);
}

// Initialize modal when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  initAuthModal();
});
