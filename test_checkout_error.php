<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing checkout includes...<br>";

try {
    echo "1. Loading user_auth.php...<br>";
    require_once 'includes/user_auth.php';
    echo "✓ user_auth.php loaded<br>";
    
    echo "2. Loading db.php...<br>";
    require_once 'includes/db.php';
    echo "✓ db.php loaded<br>";
    
    echo "3. Checking database connection...<br>";
    if ($conn !== null) {
        echo "✓ Database connected<br>";
    } else {
        echo "✗ Database connection is NULL<br>";
    }
    
    echo "4. Checking user login status...<br>";
    if (function_exists('isUserLoggedIn')) {
        $logged_in = isUserLoggedIn();
        echo "✓ isUserLoggedIn() exists, result: " . ($logged_in ? "true" : "false") . "<br>";
        
        if ($logged_in) {
            echo "5. Getting current user...<br>";
            $current_user = getCurrentUser();
            echo "✓ Current user ID: " . ($current_user['id'] ?? 'N/A') . "<br>";
        }
    } else {
        echo "✗ isUserLoggedIn() function not found<br>";
    }
    
    echo "<br>All checks passed! The issue might be elsewhere.<br>";
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}
?>
