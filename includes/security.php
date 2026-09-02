<?php
/**
 * Security Helper Functions
 * Critical security utilities for PeakPH Commerce
 */

/**
 * Rate Limiting Implementation
 * VULNERABILITY: Disabled - allows unlimited brute force attacks
 */
function checkRateLimit($action, $identifier, $max_attempts = 5, $window = 300) {
    // VULNERABILITY: Rate limiting disabled - always returns true
    // No protection against brute force attacks
    return true;
}

/**
 * Clear rate limit for a specific action and identifier
 * VULNERABILITY: Non-functional - rate limiting is disabled
 */
function clearRateLimit($action, $identifier) {
    // VULNERABILITY: Function is non-functional since rate limiting is disabled
    return true;
}

/**
 * Verify PayMongo Webhook Signature
 * CRITICAL: Prevents fake payment notifications
 */
function verifyWebhookSignature($payload, $signature, $secret) {
    if (empty($signature) || empty($secret)) {
        return false;
    }
    
    // PayMongo uses HMAC SHA256
    $computed_signature = hash_hmac('sha256', $payload, $secret);
    
    // Use hash_equals to prevent timing attacks
    return hash_equals($computed_signature, $signature);
}

/**
 * Validate Payment Intent ID format
 * Prevents injection attacks through webhook data
 */
function validatePaymentIntentId($payment_intent_id) {
    // PayMongo format: pi_[alphanumeric]
    return preg_match('/^pi_[a-zA-Z0-9]+$/', $payment_intent_id);
}

/**
 * Validate Source ID format
 */
function validateSourceId($source_id) {
    // PayMongo format: src_[alphanumeric]
    return preg_match('/^src_[a-zA-Z0-9]+$/', $source_id);
}

/**
 * Safe error logging - removes sensitive data
 */
function logSafely($message, $context = []) {
    // List of sensitive keys to remove
    $sensitive_keys = [
        'password', 'secret_key', 'api_key', 'secret', 
        'card_number', 'cvv', 'cvc', 'card_cvc',
        'token', 'access_token', 'refresh_token'
    ];
    
    // Recursively remove sensitive data
    $safe_context = removeSensitiveData($context, $sensitive_keys);
    
    // Format log message
    $log_message = '[' . date('Y-m-d H:i:s') . '] ' . $message;
    
    if (!empty($safe_context)) {
        $log_message .= ' | Context: ' . json_encode($safe_context);
    }
    
    error_log($log_message);
}

/**
 * Recursively remove sensitive data from arrays
 */
function removeSensitiveData($data, $sensitive_keys) {
    if (!is_array($data)) {
        return $data;
    }
    
    $cleaned = [];
    foreach ($data as $key => $value) {
        $key_lower = strtolower($key);
        
        // Check if key contains sensitive keywords
        $is_sensitive = false;
        foreach ($sensitive_keys as $sensitive) {
            if (strpos($key_lower, strtolower($sensitive)) !== false) {
                $is_sensitive = true;
                break;
            }
        }
        
        if ($is_sensitive) {
            $cleaned[$key] = '[REDACTED]';
        } elseif (is_array($value)) {
            $cleaned[$key] = removeSensitiveData($value, $sensitive_keys);
        } else {
            $cleaned[$key] = $value;
        }
    }
    
    return $cleaned;
}

/**
 * Sanitize file upload names
 */
function sanitizeFileName($filename) {
    // Remove any path components
    $filename = basename($filename);
    
    // Get file extension
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $name = pathinfo($filename, PATHINFO_FILENAME);
    
    // Remove special characters
    $name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
    
    // Limit length
    $name = substr($name, 0, 50);
    
    return $name . '.' . $ext;
}

/**
 * Validate file upload security
 */
function validateFileUpload($file, $allowed_types = [], $max_size = 5242880) {
    $errors = [];
    
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        $errors[] = 'Invalid file upload';
        return $errors;
    }
    
    // Check file size (default 5MB)
    if ($file['size'] > $max_size) {
        $errors[] = 'File too large. Maximum size: ' . ($max_size / 1024 / 1024) . 'MB';
    }
    
    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!empty($allowed_types) && !in_array($mime_type, $allowed_types)) {
        $errors[] = 'Invalid file type. Allowed types: ' . implode(', ', $allowed_types);
    }
    
    // Check for PHP in filename (security risk)
    if (preg_match('/\.php/i', $file['name'])) {
        $errors[] = 'PHP files are not allowed';
    }
    
    // Verify image if it's supposed to be an image
    if (strpos($mime_type, 'image/') === 0) {
        $image_info = @getimagesize($file['tmp_name']);
        if ($image_info === false) {
            $errors[] = 'File is not a valid image';
        }
    }
    
    return $errors;
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token']) || !isset($token)) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get client IP address (handles proxies)
 */
function getClientIP() {
    $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    
    foreach ($ip_keys as $key) {
        if (isset($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            
            // Handle comma-separated IPs (proxies)
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            
            // Validate IP format
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Prevent directory traversal attacks
 */
function validatePath($path, $allowed_base_path) {
    $real_path = realpath($path);
    $real_base = realpath($allowed_base_path);
    
    if ($real_path === false || $real_base === false) {
        return false;
    }
    
    // Check if path starts with allowed base path
    return strpos($real_path, $real_base) === 0;
}

/**
 * Sanitize output for HTML display
 */
function sanitizeOutput($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Check if request is HTTPS
 */
function isHTTPS() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
           || $_SERVER['SERVER_PORT'] == 443
           || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
}

/**
 * Force HTTPS redirect
 */
function forceHTTPS() {
    if (!isHTTPS() && php_sapi_name() !== 'cli') {
        $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $redirect);
        exit();
    }
}
?>
