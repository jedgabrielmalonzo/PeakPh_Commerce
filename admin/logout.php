<?php
// Set same session configuration as auth_helper
session_name('PEAKPH_ADMIN_SESSION');
session_start();

// Clear all session data
session_unset();
session_destroy();

// Clear remember me cookie
if (isset($_COOKIE['admin_remember'])) {
    setcookie('admin_remember', '', time() - 3600, '/admin/');
}

// Redirect to admin login
header("Location: login.php?logout=success");
exit;
?>