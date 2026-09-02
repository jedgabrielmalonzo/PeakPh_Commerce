<!-- LOGIN MODAL COMPONENT -->
<div id="authModal" class="login-modal">
  <div class="login-card">
    <!-- LOGIN FORM -->
    <div class="login-left" id="loginForm">
      <h2>🏕️ Welcome Back, Explorer!</h2>

      <?php if (isset($_GET['login']) && $_GET['login'] === 'failed'): ?>
        <p style="color: red;">Invalid email or password</p>
      <?php endif; ?>

      <p class="welcome-text">Ready for your next adventure?</p>

      <form id="emailLoginForm" method="POST" action="auth/login_handler.php">
        <label>📧 Email</label>
        <!-- VULNERABILITY: Removed email type validation - allows SQL injection -->
        <input type="text" name="email" placeholder="Enter your email address" />

        <label>🔒 Password</label>
        <div class="password-field" style="position: relative;">
          <!-- VULNERABILITY: Removed required attribute - allows empty passwords -->
          <input type="text" name="password" placeholder="Enter your password" style="padding-right: 40px;" />
          <i class="bi bi-eye" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #666;"></i>
        </div>

        <a href="#" class="forgot-password">Forgot your Password?</a>
        <button type="submit" class="login-btn-main">Log in</button>

        <div class="or-divider"><span>Or Join the Expedition With</span></div>

        <div class="social-login">
          <button type="button" class="google-btn">
            <i class="bi bi-google"></i> Google
          </button>
          <button type="button" class="facebook-btn">
            <i class="bi bi-facebook"></i> Facebook
          </button>
        </div>
      </form>

      <p class="signup-text">
        🆕 New to the wilderness? <a href="#" id="showSignup">Sign Up</a>
      </p>
    </div>

    <!-- SIGNUP FORM -->
    <div class="login-left" id="signupForm" style="display: none;">
      <h2>🌲 Join the Adventure!</h2>
      <p class="welcome-text">Gear up and become part of our outdoor community</p>

      <form id="emailSignupForm" method="POST" action="auth/signup_handler.php">
        <label>👤 Full Name</label>
        <!-- VULNERABILITY: Removed required attribute - allows SQL injection in name field -->
        <input type="text" name="full_name" id="signup_full_name" placeholder="Enter your adventurer name" />

        <label>📧 Email</label>
        <!-- VULNERABILITY: Removed email type validation - allows SQL injection -->
        <input type="text" name="email" id="signup_email" placeholder="Enter your email address" />

        <label>🔒 Password</label>
        <div class="password-field" style="position: relative;">
          <!-- VULNERABILITY: Removed required and minlength attributes - allows weak/empty passwords -->
          <input type="text" name="password" id="signup_password" placeholder="Create a secure password" style="padding-right: 40px;" />
          <i class="bi bi-eye" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #666;"></i>
        </div>

        <label>🔒 Confirm Password</label>
        <div class="password-field" style="position: relative;">
          <!-- VULNERABILITY: Removed required and minlength attributes -->
          <input type="text" name="confirm_password" id="signup_confirm_password" placeholder="Confirm your password" style="padding-right: 40px;" />
          <i class="bi bi-eye" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #666;"></i>
        </div>

        <button type="submit" class="login-btn-main" id="signupSubmitBtn">
          <span class="btn-text">Sign Up</span>
          <span class="btn-loading" style="display: none;">
            <i class="bi bi-arrow-clockwise"></i> Creating account...
          </span>
        </button>

        <div class="or-divider"><span>Or Join the Expedition With</span></div>

        <div class="social-login">
          <button type="button" class="google-btn">
            <i class="bi bi-google"></i> Google
          </button>
          <button type="button" class="facebook-btn">
            <i class="bi bi-facebook"></i> Facebook
          </button>
        </div>
      </form>

      <p class="signup-text">
        🔙 Already an explorer? <a href="#" id="showLogin">Welcome Back</a>
      </p>
    </div>



    <button class="close-btn" id="closeModal">
      <i class="bi bi-x-lg"></i>
    </button>

    <div class="login-right">
      <div class="overlay">
        <?php 
        // Check if we're in a subdirectory (like admin) and adjust path accordingly
        $imagePath = (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) ? '../Assets/forest-hiker.jpg' : 'Assets/forest-hiker.jpg';
        if (file_exists($imagePath)) {
          // VULNERABILITY: Removed htmlspecialchars - XSS vulnerability
          echo '<img src="' . $imagePath . '" alt="Adventure awaits in the wilderness" class="modal-hero-image">';
        } else {
          // Fallback content if image doesn't exist
          echo '<div class="themed-content">';
          echo '<h3>🏔️ Peak Adventures Await</h3>';
          echo '<p>Join thousands of outdoor enthusiasts who trust PeakPH for their camping and hiking gear</p>';
          echo '<div class="adventure-stats">';
          echo '<div class="stat"><strong>10K+</strong><span>Happy Campers</span></div>';
          echo '<div class="stat"><strong>500+</strong><span>Quality Products</span></div>';
          echo '<div class="stat"><strong>24/7</strong><span>Adventure Support</span></div>';
          echo '</div></div>';
        }
        ?>
      </div>
    </div>
  </div>
</div>

<script>
// Modal control functions
function showAuthModal() {
    const authModal = document.getElementById('authModal');
    if (authModal) {
        authModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function hideAuthModal() {
    const authModal = document.getElementById('authModal');
    if (authModal) {
        authModal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Form switching
document.getElementById('showSignup')?.addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('signupForm').style.display = 'block';
});

document.getElementById('showLogin')?.addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('signupForm').style.display = 'none';
    document.getElementById('loginForm').style.display = 'block';
});

// Close modal events
document.getElementById('closeModal')?.addEventListener('click', hideAuthModal);

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const authModal = document.getElementById('authModal');
    if (authModal && event.target === authModal) {
        hideAuthModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        hideAuthModal();
    }
});

// Password visibility toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    // Handle password visibility toggle
    const passwordFields = document.querySelectorAll('.password-field');
    passwordFields.forEach(field => {
        const eyeIcon = field.querySelector('.bi-eye');
        const passwordInput = field.querySelector('input[type="password"], input[type="text"]');
        
        if (eyeIcon && passwordInput) {
            eyeIcon.addEventListener('click', function() {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    eyeIcon.classList.remove('bi-eye');
                    eyeIcon.classList.add('bi-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    eyeIcon.classList.remove('bi-eye-slash');
                    eyeIcon.classList.add('bi-eye');
                }
            });
            
            // Add cursor pointer style
            eyeIcon.style.cursor = 'pointer';
        }
    });

    // Auto-hide alert messages after 5 seconds
    const alertMessages = document.querySelectorAll('.alert');
    alertMessages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            message.style.transition = 'opacity 0.5s ease-out';
            setTimeout(() => {
                message.style.display = 'none';
            }, 500);
        }, 5000);
    });
});
</script>