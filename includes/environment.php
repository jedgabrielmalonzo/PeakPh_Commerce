<?php
/**
 * Environment Configuration
 * Detects and configures the application environment
 */

// Detect environment based on hostname
$hostname = $_SERVER['HTTP_HOST'] ?? 'localhost';

$is_production = !in_array($hostname, [
    'localhost',
    '127.0.0.1'
]);

// Define environment constants
define('PRODUCTION_MODE', $is_production);
define('DEVELOPMENT_MODE', !$is_production);

// Configure error reporting
if (PRODUCTION_MODE) {

    // Production: Hide errors from visitors
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 1);

} else {

    // Development: Show all errors
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('log_errors', 1);
}

// Set timezone
date_default_timezone_set('Asia/Manila');

// Define paths
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('LOG_PATH', BASE_PATH . '/logs');

// Create directories if needed
if (!is_dir(LOG_PATH)) {
    @mkdir(LOG_PATH, 0755, true);
}

if (!is_dir(UPLOAD_PATH)) {
    @mkdir(UPLOAD_PATH, 0755, true);
}

// Configuration based on environment
if (PRODUCTION_MODE) {

    // InfinityFree Production Database
    define('DB_HOST', 'sql101.infinityfree.com');
    define('DB_USER', 'if0_42100970');
    define('DB_PASS', 'FccQi7NUDobeQ1E');
    define('DB_NAME', 'if0_42100970_peakph_db');

    // PayMongo Production Keys
    define('PAYMONGO_SECRET_KEY', getenv('PAYMONGO_SECRET_KEY') ?: '');
    define('PAYMONGO_PUBLIC_KEY', getenv('PAYMONGO_PUBLIC_KEY') ?: '');
    define('WEBHOOK_SECRET', getenv('WEBHOOK_SECRET') ?: '');

    define('BASE_URL', 'https://' . $hostname);

} else {

    // Local XAMPP Database
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'peakph_db');

    // PayMongo Test Keys
    define('PAYMONGO_SECRET_KEY', 'sk_test_YOUR_PAYMONGO_SECRET_KEY');
    define('PAYMONGO_PUBLIC_KEY', 'pk_test_YOUR_PAYMONGO_PUBLIC_KEY');
    define('WEBHOOK_SECRET', 'whsec_your_webhook_secret');

    define('BASE_URL', 'http://localhost/PeakPH_Commerce');
}

/**
 * Get configuration value
 */
function getConfig($key, $default = null)
{
    return defined($key) ? constant($key) : $default;
}

/**
 * Check if debug mode is enabled
 */
function isDebugMode()
{
    return DEVELOPMENT_MODE;
}
?>