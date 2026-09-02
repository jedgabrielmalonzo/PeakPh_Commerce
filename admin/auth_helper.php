<?php
/**
 * Admin Authentication Helper
 * Provides secure authentication checks for admin pages
 */

function requireAdminAuth() {
    // Configure session settings for admin
    if (session_status() === PHP_SESSION_NONE) {
        // Set session configuration for admin (8 hours)
        ini_set('session.gc_maxlifetime', 8 * 60 * 60); // 8 hours
        ini_set('session.cookie_lifetime', 8 * 60 * 60); // 8 hours
        ini_set('session.cookie_httponly', 1); // Security: HTTP only cookies
        ini_set('session.use_strict_mode', 1); // Security: Strict mode
        
        // Set session name for admin
        session_name('PEAKPH_ADMIN_SESSION');
        
        // Start session with custom settings
        session_start();
        
        // Ensure session cookie has proper settings for all admin paths
        $cookieParams = session_get_cookie_params();
        if ($cookieParams['lifetime'] != 8 * 60 * 60 || $cookieParams['path'] != '/') {
            // Set cookie path to root so it works across all admin subdirectories
            session_set_cookie_params(8 * 60 * 60, '/', '', false, true);
        }
    }
    
    // Debug: Log session info (remove this after debugging)
    error_log("Auth Debug - Page: " . $_SERVER['REQUEST_URI'] . 
              " | Session ID: " . session_id() . 
              " | Logged in: " . (isset($_SESSION['logged_in']) ? $_SESSION['logged_in'] : 'not set') . 
              " | Is admin: " . (isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : 'not set'));
    
    // Check if user is logged in AND is an admin
    if (!isset($_SESSION['logged_in']) || 
        $_SESSION['logged_in'] !== true || 
        !isset($_SESSION['is_admin']) || 
        $_SESSION['is_admin'] !== true) {
        
        // Clear any invalid session data
        session_destroy();
        
        // Redirect to admin login
        header("Location: " . getAdminLoginPath());
        exit;
    }
    
    // Optional: Check session timeout (8 hours for admin work)
    if (isset($_SESSION['login_time'])) {
        $timeout = 8 * 60 * 60; // 8 hours in seconds
        if (time() - $_SESSION['login_time'] > $timeout) {
            session_destroy();
            header("Location: " . getAdminLoginPath() . "?timeout=1");
            exit;
        }
        // Refresh login time on activity (but not too frequently to avoid constant updates)
        if (!isset($_SESSION['last_activity']) || (time() - $_SESSION['last_activity']) > 300) { // 5 minutes
            $_SESSION['last_activity'] = time();
        }
        
        // Regenerate session ID periodically for security (every 30 minutes)
        if (!isset($_SESSION['session_regenerated']) || (time() - $_SESSION['session_regenerated']) > 1800) {
            session_regenerate_id(true);
            $_SESSION['session_regenerated'] = time();
        }
    }
}

function getAdminLoginPath() {
    // Determine the correct path to admin login based on current directory
    $currentScript = $_SERVER['SCRIPT_NAME'];
    
    // Count how many levels deep we are from the admin folder
    if (strpos($currentScript, '/admin/') !== false) {
        $afterAdmin = substr($currentScript, strpos($currentScript, '/admin/') + 7);
        $levels = substr_count($afterAdmin, '/');
        
        if ($levels <= 0) {
            return "login.php";
        } else {
            return str_repeat("../", $levels) . "login.php";
        }
    }
    
    return "login.php";
}

function isAdmin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['logged_in']) && 
           $_SESSION['logged_in'] === true && 
           isset($_SESSION['is_admin']) && 
           $_SESSION['is_admin'] === true;
}

// Legacy function for backward compatibility
function checkAdminAuth() {
    requireAdminAuth();
}

// Get admin email safely
function getAdminEmail() {
    return isset($_SESSION['admin_email']) ? $_SESSION['admin_email'] : 'Unknown';
}

// Generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Sanitize output
function sanitizeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Debug function to check session status (only for development)
function debugSessionInfo() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $info = [
        'session_id' => session_id(),
        'logged_in' => isset($_SESSION['logged_in']) ? $_SESSION['logged_in'] : 'not set',
        'is_admin' => isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : 'not set',
        'login_time' => isset($_SESSION['login_time']) ? date('Y-m-d H:i:s', $_SESSION['login_time']) : 'not set',
        'last_activity' => isset($_SESSION['last_activity']) ? date('Y-m-d H:i:s', $_SESSION['last_activity']) : 'not set',
        'time_since_login' => isset($_SESSION['login_time']) ? (time() - $_SESSION['login_time']) . ' seconds' : 'not available',
        'session_timeout_in' => isset($_SESSION['login_time']) ? (8 * 60 * 60 - (time() - $_SESSION['login_time'])) . ' seconds' : 'not available'
    ];
    
    return $info;
}
?>