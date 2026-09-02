<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - PeakPH Commerce</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../Assets/Carousel_Picts/Logo.png" />
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="../Css/Global.css">
    <link rel="stylesheet" href="../Css/admin_login.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="top-navbar">
            <div class="brand">
                <a href="../index.php" class="logo-btn">
                    <img src="../Assets/Carousel_Picts/Logo.png" alt="PeakPH Logo">
                </a>
            </div>
            <div class="admin-title">
                <h1>🏔️ Admin Portal</h1>
            </div>
            <div class="nav-actions">
                <a href="../index.php" class="back-link-nav">
                    <i class="bi bi-arrow-left"></i>
                    Back to Store
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="admin-login-container">
        <div class="login-wrapper">
            <!-- Left Side - Login Form -->
            <div class="login-form-section">
                <div class="login-header">
                    <i class="bi bi-shield-lock admin-icon"></i>
                    <h2>🏕️ Welcome Back, Administrator!</h2>
                    <p class="admin-subtitle">Manage your outdoor adventure empire</p>
                </div>

                <?php if (isset($_GET['login']) && $_GET['login'] === 'failed'): ?>
                    <div class="error-message">
                        <i class="bi bi-exclamation-triangle"></i>
                        <span>Invalid credentials. Please try again.</span>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
                    <div class="success-message">
                        <i class="bi bi-check-circle"></i>
                        <span>You have been successfully logged out.</span>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['timeout']) && $_GET['timeout'] === '1'): ?>
                    <div class="warning-message">
                        <i class="bi bi-clock"></i>
                        <span>Your session has expired. Please log in again.</span>
                    </div>
                <?php endif; ?>

                <form class="admin-login-form" method="POST" action="login_handler.php">
                    <div class="input-group">
                        <label for="email">
                            <i class="bi bi-person-badge"></i>
                            Administrator Email
                        </label>
                        <!-- VULNERABILITY: Removed email type and required attribute - allows SQL injection -->
                        <input type="text" id="email" name="email" placeholder="Enter your admin email">
                    </div>

                    <div class="input-group">
                        <label for="password">
                            <i class="bi bi-key"></i>
                            Password
                        </label>
                        <div class="password-field">
                            <!-- VULNERABILITY: Removed required attribute - allows empty passwords -->
                            <input type="text" id="password" name="password" placeholder="Enter your password">
                            <i class="bi bi-eye toggle-password" onclick="togglePassword()"></i>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-container">
                            <input type="checkbox" name="remember_me">
                            <span class="checkmark"></span>
                            Remember me for 30 days
                        </label>
                        <a href="reset_password.php" class="forgot-link">Forgot Password?</a>
                    </div>

                    <button type="submit" class="admin-login-btn">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Access Admin Panel
                    </button>

                    <div class="security-info">
                        <i class="bi bi-shield-check"></i>
                        <small>Your session is secured with enterprise-level encryption</small>
                    </div>
                </form>
            </div>

            <!-- Right Side - Hero Image/Content -->
            <div class="hero-section">
                <div class="hero-overlay">
                    <div class="hero-content">
                        <h3>🌲 Command Center</h3>
                        <p>Manage your camping gear empire from one powerful dashboard</p>
                        
                        <div class="admin-stats">
                            <div class="stat-card">
                                <i class="bi bi-graph-up"></i>
                                <div>
                                    <strong>Analytics</strong>
                                    <span>Sales & Performance</span>
                                </div>
                            </div>
                            <div class="stat-card">
                                <i class="bi bi-boxes"></i>
                                <div>
                                    <strong>Inventory</strong>
                                    <span>Product Management</span>
                                </div>
                            </div>
                            <div class="stat-card">
                                <i class="bi bi-people"></i>
                                <div>
                                    <strong>Customers</strong>
                                    <span>User Management</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="admin-footer">
        <div class="footer-content">
            <p>&copy; 2025 PeakPH Commerce - Admin Portal</p>
            <div class="footer-links">
                <a href="#" class="footer-link">Privacy Policy</a>
                <a href="#" class="footer-link">Terms of Service</a>
                <a href="#" class="footer-link">Support</a>
            </div>
        </div>
    </footer>

    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-password');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }

        // Form animation on load
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.login-form-section').style.opacity = '0';
            document.querySelector('.hero-section').style.opacity = '0';
            
            setTimeout(() => {
                document.querySelector('.login-form-section').style.transition = 'opacity 0.8s ease';
                document.querySelector('.hero-section').style.transition = 'opacity 0.8s ease';
                document.querySelector('.login-form-section').style.opacity = '1';
                document.querySelector('.hero-section').style.opacity = '1';
            }, 200);
        });
    </script>
</body>
</html>