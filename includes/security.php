<?php
/**
 * Security Helper Functions
 * Critical security utilities for PeakPH Commerce
 */

/**
 * Rate Limiting Cache Directory Helper
 */
function getRateLimitCacheDir() {
    $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'peakph_ratelimit';
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
    return $dir;
}

/**
 * Advanced Rate Limiter
 * 
 * Provides defense against Brute Force attacks (including Burp Suite Cluster Bomb /
 * Intruder / Hydra / automated dictionary attacks).
 * 
 * Dual storage architecture:
 * 1. Primary: MySQL database table `login_rate_limits`
 * 2. Fallback: File-based lock storage in system temp directory if MySQL is offline
 * 
 * @param string $action       Action identifier (e.g., 'login_ip', 'login_account')
 * @param string $identifier   Client IP address or username/email
 * @param int    $max_attempts Maximum allowed attempts (default: 5)
 * @param int    $window       Lockout/observation window in seconds (default: 900 = 15 minutes)
 * @return array               ['allowed' => bool, 'attempts' => int, 'max_attempts' => int, 'retry_after' => int, 'reset_time' => int]
 */
function checkRateLimitWithDetails($action, $identifier, $max_attempts = 5, $window = 900) {
    global $conn;
    $now = time();
    $rate_key = hash('sha256', $action . '_' . strtolower(trim($identifier)));

    // 1. Try MySQL Database Persistence
    if ($conn instanceof mysqli && !$conn->connect_error) {
        try {
            static $table_ensured = false;
            if (!$table_ensured) {
                $conn->query("CREATE TABLE IF NOT EXISTS login_rate_limits (
                    rate_key VARCHAR(64) PRIMARY KEY,
                    action VARCHAR(50) NOT NULL,
                    identifier VARCHAR(191) NOT NULL,
                    attempts INT NOT NULL DEFAULT 1,
                    first_attempt INT NOT NULL,
                    last_attempt INT NOT NULL,
                    INDEX idx_action_id (action, identifier),
                    INDEX idx_last_attempt (last_attempt)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                $table_ensured = true;
            }

            $stmt = $conn->prepare("SELECT attempts, first_attempt, last_attempt FROM login_rate_limits WHERE rate_key = ?");
            if ($stmt) {
                $stmt->bind_param("s", $rate_key);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($row = $result->fetch_assoc()) {
                    $first_attempt = (int)$row['first_attempt'];
                    $attempts = (int)$row['attempts'];

                    // If window expired, reset attempts counter
                    if (($now - $first_attempt) > $window) {
                        $stmt_reset = $conn->prepare("UPDATE login_rate_limits SET attempts = 1, first_attempt = ?, last_attempt = ? WHERE rate_key = ?");
                        $stmt_reset->bind_param("iis", $now, $now, $rate_key);
                        $stmt_reset->execute();
                        $stmt_reset->close();
                        $stmt->close();
                        return [
                            'allowed' => true,
                            'attempts' => 1,
                            'max_attempts' => $max_attempts,
                            'retry_after' => 0,
                            'reset_time' => $now + $window
                        ];
                    }

                    // If already exceeded max attempts (1-5 entries allowed, 6+ blocked)
                    if ($attempts >= $max_attempts) {
                        $retry_after = max(1, $window - ($now - $first_attempt));
                        $stmt->close();
                        return [
                            'allowed' => false,
                            'attempts' => $attempts,
                            'max_attempts' => $max_attempts,
                            'retry_after' => $retry_after,
                            'reset_time' => $first_attempt + $window
                        ];
                    }

                    // Increment attempts
                    $new_attempts = $attempts + 1;
                    $stmt_inc = $conn->prepare("UPDATE login_rate_limits SET attempts = ?, last_attempt = ? WHERE rate_key = ?");
                    $stmt_inc->bind_param("iis", $new_attempts, $now, $rate_key);
                    $stmt_inc->execute();
                    $stmt_inc->close();
                    $stmt->close();
                    return [
                        'allowed' => true,
                        'attempts' => $new_attempts,
                        'max_attempts' => $max_attempts,
                        'retry_after' => 0,
                        'reset_time' => $first_attempt + $window
                    ];
                } else {
                    // First attempt recorded in database
                    $stmt_ins = $conn->prepare("INSERT INTO login_rate_limits (rate_key, action, identifier, attempts, first_attempt, last_attempt) VALUES (?, ?, ?, 1, ?, ?)");
                    $stmt_ins->bind_param("sssii", $rate_key, $action, $identifier, $now, $now);
                    $stmt_ins->execute();
                    $stmt_ins->close();
                    $stmt->close();
                    return [
                        'allowed' => true,
                        'attempts' => 1,
                        'max_attempts' => $max_attempts,
                        'retry_after' => 0,
                        'reset_time' => $now + $window
                    ];
                }
            }
        } catch (Exception $e) {
            error_log("Rate limiting DB fallback: " . $e->getMessage());
        }
    }

    // 2. Fallback: File-based Lock Storage (independent of DB & session, blocks Burp Suite Intruder)
    $cache_dir = getRateLimitCacheDir();
    $cache_file = $cache_dir . DIRECTORY_SEPARATOR . $rate_key . '.json';
    $fp = @fopen($cache_file, 'c+');

    if ($fp && flock($fp, LOCK_EX)) {
        $filesize = filesize($cache_file);
        $data = null;
        if ($filesize > 0) {
            $raw = fread($fp, $filesize);
            $data = json_decode($raw, true);
        }

        if (is_array($data) && isset($data['first_attempt'], $data['attempts'])) {
            $first_attempt = (int)$data['first_attempt'];
            $attempts = (int)$data['attempts'];

            if (($now - $first_attempt) > $window) {
                $data = ['attempts' => 1, 'first_attempt' => $now, 'last_attempt' => $now];
                ftruncate($fp, 0);
                rewind($fp);
                fwrite($fp, json_encode($data));
                fflush($fp);
                flock($fp, LOCK_UN);
                fclose($fp);
                return [
                    'allowed' => true,
                    'attempts' => 1,
                    'max_attempts' => $max_attempts,
                    'retry_after' => 0,
                    'reset_time' => $now + $window
                ];
            }

            if ($attempts >= $max_attempts) {
                $retry_after = max(1, $window - ($now - $first_attempt));
                flock($fp, LOCK_UN);
                fclose($fp);
                return [
                    'allowed' => false,
                    'attempts' => $attempts,
                    'max_attempts' => $max_attempts,
                    'retry_after' => $retry_after,
                    'reset_time' => $first_attempt + $window
                ];
            }

            $new_attempts = $attempts + 1;
            $data['attempts'] = $new_attempts;
            $data['last_attempt'] = $now;
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($data));
            fflush($fp);
            flock($fp, LOCK_UN);
            fclose($fp);
            return [
                'allowed' => true,
                'attempts' => $new_attempts,
                'max_attempts' => $max_attempts,
                'retry_after' => 0,
                'reset_time' => $first_attempt + $window
            ];
        } else {
            $data = ['attempts' => 1, 'first_attempt' => $now, 'last_attempt' => $now];
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($data));
            fflush($fp);
            flock($fp, LOCK_UN);
            fclose($fp);
            return [
                'allowed' => true,
                'attempts' => 1,
                'max_attempts' => $max_attempts,
                'retry_after' => 0,
                'reset_time' => $now + $window
            ];
        }
    }

    if ($fp) {
        fclose($fp);
    }

    // 3. Fallback: Session-based Rate Limiting
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    $skey = "ratelimit_{$rate_key}";
    if (!isset($_SESSION[$skey]) || ($now - $_SESSION[$skey]['first_attempt']) > $window) {
        $_SESSION[$skey] = ['attempts' => 1, 'first_attempt' => $now];
        return [
            'allowed' => true,
            'attempts' => 1,
            'max_attempts' => $max_attempts,
            'retry_after' => 0,
            'reset_time' => $now + $window
        ];
    }
    if ($_SESSION[$skey]['attempts'] >= $max_attempts) {
        $retry_after = max(1, $window - ($now - $_SESSION[$skey]['first_attempt']));
        return [
            'allowed' => false,
            'attempts' => $_SESSION[$skey]['attempts'],
            'max_attempts' => $max_attempts,
            'retry_after' => $retry_after,
            'reset_time' => $_SESSION[$skey]['first_attempt'] + $window
        ];
    }
    $_SESSION[$skey]['attempts']++;
    return [
        'allowed' => true,
        'attempts' => $_SESSION[$skey]['attempts'],
        'max_attempts' => $max_attempts,
        'retry_after' => 0,
        'reset_time' => $_SESSION[$skey]['first_attempt'] + $window
    ];
}

/**
 * Standard Rate Limiter Wrapper (Backward Compatible)
 */
function checkRateLimit($action, $identifier, $max_attempts = 5, $window = 300) {
    $res = checkRateLimitWithDetails($action, $identifier, $max_attempts, $window);
    return $res['allowed'];
}

/**
 * Clear rate limit records for an action and identifier upon successful authentication
 */
function clearRateLimit($action, $identifier) {
    global $conn;
    $rate_key = hash('sha256', $action . '_' . strtolower(trim($identifier)));

    // 1. Clear in MySQL
    if ($conn instanceof mysqli && !$conn->connect_error) {
        try {
            $stmt = $conn->prepare("DELETE FROM login_rate_limits WHERE rate_key = ?");
            if ($stmt) {
                $stmt->bind_param("s", $rate_key);
                $stmt->execute();
                $stmt->close();
            }
        } catch (Exception $e) {
            // Silently continue
        }
    }

    // 2. Clear in file cache
    $cache_file = getRateLimitCacheDir() . DIRECTORY_SEPARATOR . $rate_key . '.json';
    if (file_exists($cache_file)) {
        @unlink($cache_file);
    }

    // 3. Clear in session
    if (session_status() !== PHP_SESSION_NONE) {
        unset($_SESSION["ratelimit_{$rate_key}"]);
        unset($_SESSION["ratelimit_{$action}_{$identifier}"]);
    }

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
    // 1. Cloudflare connecting IP (verified proxy header)
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP']) && filter_var($_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP)) {
        return $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
    
    // 2. Direct TCP connection IP (tamper-proof against client-injected headers)
    if (!empty($_SERVER['REMOTE_ADDR'])) {
        return $_SERVER['REMOTE_ADDR'];
    }
    
    return '127.0.0.1';
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
