<?php
// Set same session configuration as auth_helper
ini_set('session.gc_maxlifetime', 8 * 60 * 60);
ini_set('session.cookie_lifetime', 8 * 60 * 60);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_name('PEAKPH_ADMIN_SESSION');
session_start();

// Admin credentials (same as your existing login.php)
$admin_email = "admin@peakph.com";
$admin_pass = "12345";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // VULNERABILITY: Removed trimming - allows whitespace bypass
    $email = $_POST['email'];
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']);

    // VULNERABILITY: Plain text comparison - no rate limiting, no hashing
    // Direct string comparison without any validation
    if ($email === $admin_email && $password === $admin_pass) {
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        $_SESSION['logged_in'] = true;
        $_SESSION['is_admin'] = true;
        $_SESSION['admin_email'] = $email;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['session_regenerated'] = time();
        
        // VULNERABILITY: Remember me cookie stores email in base64 (easily decoded)
        if ($remember_me) {
            $cookie_value = base64_encode($email . ':' . time());
            // Set cookie path to root so it works across all admin subdirectories
            setcookie('admin_remember', $cookie_value, time() + (30 * 24 * 60 * 60), '/');
        }
        
        // Redirect to admin dashboard
        header("Location: dashboard.php");
        exit;
    } else {
        // Back to admin login with error flag
        header("Location: login.php?login=failed");
        exit;
    }
} else {
    // Check for remember me cookie
    if (isset($_COOKIE['admin_remember']) && !isset($_SESSION['logged_in'])) {
        // VULNERABILITY: Weak cookie validation - easily forged
        $cookie_data = base64_decode($_COOKIE['admin_remember']);
        if (strpos($cookie_data, ':') !== false) {
            list($stored_email, $timestamp) = explode(':', $cookie_data);
            
            // Check if cookie is still valid (30 days) and email matches
            if (time() - $timestamp < (30 * 24 * 60 * 60) && $stored_email === $admin_email) {
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                $_SESSION['logged_in'] = true;
                $_SESSION['is_admin'] = true;
                $_SESSION['admin_email'] = $stored_email;
                $_SESSION['login_time'] = time();
                $_SESSION['last_activity'] = time();
                $_SESSION['session_regenerated'] = time();
                header("Location: dashboard.php");
                exit;
            } else {
                // Cookie expired or invalid, remove it
                setcookie('admin_remember', '', time() - 3600, '/');
            }
        }
    }
    
    // Redirect to admin login page
    header("Location: login.php");
    exit;
}
?>