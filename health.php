<?php
/**
 * Health Check Endpoint
 * Used for monitoring system status
 * Access: yourdomain.com/health.php
 */

header('Content-Type: application/json');

// Prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$checks = [
    'timestamp' => date('c'),
    'environment' => 'unknown',
    'checks' => []
];

// 1. Check PHP version
$checks['checks']['php_version'] = [
    'status' => version_compare(PHP_VERSION, '7.4.0', '>='),
    'value' => PHP_VERSION,
    'required' => '7.4.0+'
];

// 2. Check environment detection
try {
    if (file_exists(__DIR__ . '/includes/environment.php')) {
        require_once(__DIR__ . '/includes/environment.php');
        $checks['environment'] = PRODUCTION_MODE ? 'production' : 'development';
        $checks['checks']['environment'] = [
            'status' => true,
            'value' => $checks['environment']
        ];
    }
} catch (Exception $e) {
    $checks['checks']['environment'] = [
        'status' => false,
        'error' => 'Failed to load environment config'
    ];
}

// 3. Check database connection
try {
    require_once(__DIR__ . '/includes/db.php');
    
    if ($conn && $conn->ping()) {
        $checks['checks']['database'] = [
            'status' => true,
            'message' => 'Connected'
        ];
        
        // Check critical tables
        $required_tables = ['users', 'orders', 'inventory', 'paymongo_payments'];
        $result = $conn->query("SHOW TABLES");
        $existing_tables = [];
        
        while ($row = $result->fetch_array()) {
            $existing_tables[] = $row[0];
        }
        
        $missing_tables = array_diff($required_tables, $existing_tables);
        
        if (empty($missing_tables)) {
            $checks['checks']['database_tables'] = [
                'status' => true,
                'message' => 'All required tables exist'
            ];
        } else {
            $checks['checks']['database_tables'] = [
                'status' => false,
                'missing' => $missing_tables
            ];
        }
    } else {
        $checks['checks']['database'] = [
            'status' => false,
            'error' => 'Connection failed'
        ];
    }
} catch (Exception $e) {
    $checks['checks']['database'] = [
        'status' => false,
        'error' => 'Exception: ' . $e->getMessage()
    ];
}

// 4. Check uploads directory
$uploads_dir = __DIR__ . '/uploads';
$checks['checks']['uploads_directory'] = [
    'status' => is_dir($uploads_dir) && is_writable($uploads_dir),
    'path' => $uploads_dir,
    'writable' => is_writable($uploads_dir)
];

// 5. Check session functionality
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $checks['checks']['session'] = [
        'status' => session_status() === PHP_SESSION_ACTIVE,
        'session_id' => session_id() ? 'active' : 'inactive'
    ];
} catch (Exception $e) {
    $checks['checks']['session'] = [
        'status' => false,
        'error' => $e->getMessage()
    ];
}

// 6. Check critical files
$critical_files = [
    'includes/db.php',
    'config/paymongo.php',
    'includes/PayMongoHelper.php',
    'webhooks/paymongo.php'
];

$missing_files = [];
foreach ($critical_files as $file) {
    if (!file_exists(__DIR__ . '/' . $file)) {
        $missing_files[] = $file;
    }
}

$checks['checks']['critical_files'] = [
    'status' => empty($missing_files),
    'missing' => $missing_files
];

// 7. Check PHP extensions
$required_extensions = ['mysqli', 'json', 'curl', 'mbstring', 'session'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

$checks['checks']['php_extensions'] = [
    'status' => empty($missing_extensions),
    'missing' => $missing_extensions
];

// 8. Check disk space
$free_space = disk_free_space(__DIR__);
$total_space = disk_total_space(__DIR__);
$free_percent = ($free_space / $total_space) * 100;

$checks['checks']['disk_space'] = [
    'status' => $free_percent > 10, // Alert if less than 10% free
    'free_space_mb' => round($free_space / 1024 / 1024, 2),
    'total_space_mb' => round($total_space / 1024 / 1024, 2),
    'free_percent' => round($free_percent, 2)
];

// 9. Check HTTPS (in production)
if (PRODUCTION_MODE ?? false) {
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
                || $_SERVER['SERVER_PORT'] == 443;
    
    $checks['checks']['https'] = [
        'status' => $is_https,
        'message' => $is_https ? 'Enabled' : 'Not enabled'
    ];
}

// Determine overall health
$all_checks_passed = true;
foreach ($checks['checks'] as $check) {
    if (!$check['status']) {
        $all_checks_passed = false;
        break;
    }
}

$checks['overall_status'] = $all_checks_passed ? 'healthy' : 'unhealthy';

// Set HTTP status code
http_response_code($all_checks_passed ? 200 : 503);

// Output JSON
echo json_encode($checks, JSON_PRETTY_PRINT);
?>
