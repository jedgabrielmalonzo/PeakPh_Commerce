<?php
/**
 * User Authentication Helper Functions
 * Provides authentication utilities for user-side pages
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 * @return bool
 */
function isUserLoggedIn() {
    return isset($_SESSION['user_logged_in']) && 
           $_SESSION['user_logged_in'] === true && 
           isset($_SESSION['user_id']) && 
           isset($_SESSION['user_role']) && 
           $_SESSION['user_role'] === 'User';
}

/**
 * Get current logged in user data
 * @return array|null
 */
function getCurrentUser() {
    if (!isUserLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'name' => $_SESSION['user_name'] ?? 'User',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'User'
    ];
}

/**
 * Get user's display name
 * @return string
 */
function getUserDisplayName() {
    $user = getCurrentUser();
    return $user ? $user['name'] : 'Guest';
}

/**
 * Redirect to login if not authenticated (for protected pages)
 * @param string $redirect_to URL to redirect after login
 */
function requireLogin($redirect_to = null) {
    if (!isUserLoggedIn()) {
        $redirect_url = $redirect_to ?? $_SERVER['REQUEST_URI'];
        header('Location: index.php?login_required=1&redirect=' . urlencode($redirect_url));
        exit;
    }
}

/**
 * Generate login/logout links for navigation
 * @return string HTML for auth links
 */
function getAuthNavigationHTML() {
    if (isUserLoggedIn()) {
        $user = getCurrentUser();
        $displayName = !empty($user['first_name']) ? htmlspecialchars($user['first_name']) : 'User';
        
        return '
        <div class="user-dropdown">
            <button class="user-btn" onclick="toggleUserDropdown()">
                <i class="bi bi-person-circle"></i>
                <span>' . $displayName . '</span>
                <i class="bi bi-chevron-down"></i>
            </button>
            <div class="user-dropdown-menu" id="userDropdown">
                <a href="profile.php"><i class="bi bi-person"></i> Profile</a>
                <a href="orders.php"><i class="bi bi-bag"></i> Orders</a>
                <hr>
                <a href="#" onclick="handleLogout(); return false;" class="logout-link">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>';
    } else {
        return '<button class="login-btn" onclick="showAuthModal()">
                    <i class="bi bi-person"></i>
                    <span>Login</span>
                </button>';
    }
}

/**
 * Check if user has specific permission (for future use)
 * @param string $permission
 * @return bool
 */
function userHasPermission($permission) {
    // For now, all logged-in users have basic permissions
    // This can be extended with role-based permissions later
    if (!isUserLoggedIn()) {
        return false;
    }
    
    $basic_permissions = ['view_products', 'add_to_cart', 'place_order', 'view_profile'];
    return in_array($permission, $basic_permissions);
}

/**
 * Generate CSRF token for forms
 * @return string
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token
 * @return bool
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Update user's last activity timestamp
 */
function updateUserActivity() {
    if (isUserLoggedIn()) {
        $_SESSION['last_activity'] = time();
    }
}

/**
 * Check if session has expired
 * @param int $timeout_minutes
 * @return bool
 */
function isSessionExpired($timeout_minutes = 60) {
    if (!isUserLoggedIn()) {
        return true;
    }
    
    $last_activity = $_SESSION['last_activity'] ?? time();
    return (time() - $last_activity) > ($timeout_minutes * 60);
}

/**
 * Initialize user session maintenance
 */
function initUserSession() {
    // Update activity
    updateUserActivity();
    
    // Check for expired sessions
    if (isSessionExpired()) {
        session_unset();
        session_destroy();
        session_start();
    }
}

// Auto-initialize session maintenance
initUserSession();
?>