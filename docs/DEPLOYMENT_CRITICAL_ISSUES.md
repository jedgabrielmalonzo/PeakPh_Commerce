# ⚠️ CRITICAL DEPLOYMENT ISSUES - Must Fix Before Going Live

## 🔴 CRITICAL SECURITY ISSUES

### 1. **Webhook Signature Verification Missing**
**Location:** `webhooks/paymongo.php`  
**Risk Level:** CRITICAL

Your webhook handler does NOT verify PayMongo signatures! This means anyone can send fake payment notifications to your server.

**Current Code Problem:**
```php
$sig_header = $_SERVER['HTTP_PAYMONGO_SIGNATURE'] ?? '';
// BUT IT'S NEVER VALIDATED!
```

**Required Fix:**
```php
// Add this function to webhooks/paymongo.php
function verifyWebhookSignature($payload, $signature, $secret) {
    $computed_signature = hash_hmac('sha256', $payload, $secret);
    return hash_equals($computed_signature, $signature);
}

// Then use it:
$sig_header = $_SERVER['HTTP_PAYMONGO_SIGNATURE'] ?? '';
$config = require_once('../config/paymongo.php');

if (!verifyWebhookSignature($payload, $sig_header, $config['webhook_signature_key'])) {
    error_log('Invalid webhook signature!');
    http_response_code(401);
    exit(json_encode(['error' => 'Invalid signature']));
}
```

---

### 2. **Debug Files Exposed in Production**
**Risk Level:** CRITICAL

These files WILL be accessible on your production server:
- ❌ `debug_cart.php` - Exposes session data
- ❌ `admin/session_debug.php` - Exposes ALL session variables
- ❌ All files in `_dev-tools/` (even though guide says exclude, they're still in root)

**Required Actions:**
1. DELETE these files before deployment
2. Add to `.htaccess`:
```apache
# Block debug files
<FilesMatch "^(debug_|test_).*\.php$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

---

### 3. **Error Logging Exposes Sensitive Data**
**Location:** Throughout codebase  
**Risk Level:** HIGH

Your code logs sensitive information:
```php
error_log('PayMongo Webhook Received: ' . $payload); // Contains payment data!
error_log('Database connection failed: ' . $e->getMessage()); // Exposes DB structure
```

**Required Fix:**
Create `includes/logger.php`:
```php
<?php
function logSafely($message, $context = []) {
    // Remove sensitive data
    $safe_context = array_diff_key($context, array_flip([
        'password', 'secret_key', 'api_key', 'card_number', 'cvv'
    ]));
    
    if (defined('PRODUCTION_MODE') && PRODUCTION_MODE) {
        error_log($message . ' | ' . json_encode($safe_context));
    } else {
        error_log($message . ' | ' . print_r($safe_context, true));
    }
}
```

---

### 4. **Database Credentials Hardcoded**
**Location:** `includes/db.php`  
**Risk Level:** CRITICAL

Your DB credentials are directly in the code file, which will be in your Git repository and file manager.

**Required Fix:**
Create a configuration system:

**config/database.php** (NOT in Git):
```php
<?php
return [
    'production' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'username' => getenv('DB_USER') ?: 'u123456789_dbuser',
        'password' => getenv('DB_PASS') ?: 'your_secure_password',
        'database' => getenv('DB_NAME') ?: 'u123456789_peakph',
    ],
    'development' => [
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'peakph_db',
    ]
];
```

**Updated includes/db.php:**
```php
<?php
$environment = getenv('APP_ENV') ?: 'development';
$db_config = require_once(__DIR__ . '/../config/database.php');
$config = $db_config[$environment];

$host = $config['host'];
$user = $config['username'];
$pass = $config['password'];
$dbname = $config['database'];
// ... rest of connection code
```

---

### 5. **No SQL Injection Protection in Webhooks**
**Location:** `webhooks/paymongo.php`  
**Risk Level:** HIGH

While you use prepared statements in most places, the webhook handler could be exploited if signature verification is bypassed.

**Required Fix:**
Add input validation:
```php
// Validate payment_intent_id format
if ($payment_intent_id && !preg_match('/^pi_[a-zA-Z0-9]+$/', $payment_intent_id)) {
    error_log('Invalid payment_intent_id format');
    http_response_code(400);
    exit;
}
```

---

## 🟡 HIGH-PRIORITY ISSUES

### 6. **Session Security Not Configured for Production**
**Risk Level:** HIGH

Your `.htaccess` sets session security, but PHP code overrides it:
```php
ini_set('session.cookie_secure', 1); // This won't work if not HTTPS yet!
```

**Required Fix in includes/session_config.php:**
```php
<?php
// Detect if HTTPS is available
$is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
            || $_SERVER['SERVER_PORT'] == 443;

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', $is_https ? 1 : 0);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.gc_maxlifetime', 28800); // 8 hours
    
    session_name('PEAKPH_SESSION');
    session_start();
}
```

---

### 7. **No Rate Limiting on Critical Endpoints**
**Risk Level:** HIGH

Your login, checkout, and webhook endpoints have NO rate limiting. Attackers can:
- Brute force admin passwords
- Spam fake checkout requests
- Overwhelm webhook handler

**Required Fix:**
Create `includes/rate_limiter.php`:
```php
<?php
function checkRateLimit($action, $identifier, $max_attempts = 5, $window = 300) {
    // Use file-based storage (or Redis in production)
    $cache_file = sys_get_temp_dir() . "/rate_limit_{$action}_{$identifier}.json";
    
    $data = file_exists($cache_file) ? json_decode(file_get_contents($cache_file), true) : [];
    $now = time();
    
    // Clean old attempts
    $data = array_filter($data, function($timestamp) use ($now, $window) {
        return ($now - $timestamp) < $window;
    });
    
    if (count($data) >= $max_attempts) {
        return false; // Rate limit exceeded
    }
    
    $data[] = $now;
    file_put_contents($cache_file, json_encode($data));
    return true;
}

// Usage in login_handler.php:
$ip = $_SERVER['REMOTE_ADDR'];
if (!checkRateLimit('login', $ip, 5, 300)) {
    die(json_encode(['error' => 'Too many attempts. Try again in 5 minutes.']));
}
```

---

### 8. **Missing Content Security Policy (CSP)**
**Risk Level:** MEDIUM-HIGH

No CSP headers means XSS attacks can easily inject malicious scripts.

**Required Addition to .htaccess:**
```apache
<IfModule mod_headers.c>
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://js.paymongo.com; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self' https://api.paymongo.com;"
</IfModule>
```

---

### 9. **No Database Backup Verification**
**Risk Level:** HIGH

Your guide mentions backups but doesn't verify they work!

**Required Addition:**
```php
<?php
// backup_verify.php - Run weekly
$backup_file = 'backups/db_' . date('Y-m-d') . '.sql';
if (!file_exists($backup_file)) {
    mail('admin@peakph.com', 'BACKUP FAILED', 'Database backup missing!');
    exit(1);
}

// Test backup integrity
$test_db = 'test_restore_' . uniqid();
exec("mysql -u user -p'pass' -e 'CREATE DATABASE $test_db'");
exec("mysql -u user -p'pass' $test_db < $backup_file", $output, $return);

if ($return !== 0) {
    mail('admin@peakph.com', 'BACKUP CORRUPT', 'Cannot restore backup!');
}

exec("mysql -u user -p'pass' -e 'DROP DATABASE $test_db'");
echo "Backup verified successfully\n";
```

---

### 10. **Upload Directory Security**
**Risk Level:** HIGH

Your guide suggests 777 permissions on uploads folder - this is DANGEROUS!

**Correct Configuration:**
```apache
# In .htaccess inside uploads/
<FilesMatch "\.php$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Prevent execution
php_flag engine off
AddHandler cgi-script .php .php3 .php4 .phtml .pl .py .jsp .asp .htm .shtml .sh .cgi
Options -ExecCGI
```

**Permissions:**
```powershell
# uploads/ should be 755, NOT 777
chmod 755 uploads/
chmod 644 uploads/*.*
```

---

## 🟢 MEDIUM-PRIORITY IMPROVEMENTS

### 11. **Missing Health Check Endpoint**
Create `health.php`:
```php
<?php
header('Content-Type: application/json');

$checks = [
    'database' => false,
    'uploads_writable' => is_writable(__DIR__ . '/uploads'),
    'session_working' => session_status() === PHP_SESSION_ACTIVE || session_start(),
];

// Test DB
try {
    require_once('includes/db.php');
    $checks['database'] = $conn && $conn->ping();
} catch (Exception $e) {
    $checks['database'] = false;
}

$all_ok = !in_array(false, $checks, true);
http_response_code($all_ok ? 200 : 503);
echo json_encode([
    'status' => $all_ok ? 'healthy' : 'unhealthy',
    'checks' => $checks,
    'timestamp' => date('c')
]);
```

---

### 12. **No Logging Rotation**
Your error logs will grow indefinitely and fill disk space.

**Add to cron:**
```bash
# Rotate logs weekly
0 0 * * 0 find /path/to/logs -name "*.log" -mtime +30 -delete
```

---

### 13. **Missing Environment Detection**
Create `config/environment.php`:
```php
<?php
define('PRODUCTION_MODE', strpos($_SERVER['HTTP_HOST'], 'localhost') === false);
define('ENABLE_DEBUG', !PRODUCTION_MODE);

if (PRODUCTION_MODE) {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
```

---

## 📋 PRE-DEPLOYMENT CHECKLIST (Updated)

### MUST DO BEFORE UPLOAD:
- [ ] Implement webhook signature verification
- [ ] Delete ALL debug files (`debug_*.php`, `test_*.php`, `_dev-tools/`)
- [ ] Move DB credentials to separate config file
- [ ] Add rate limiting to login and checkout
- [ ] Set uploads folder to 755 (NOT 777)
- [ ] Add CSP headers to .htaccess
- [ ] Implement proper error logging (no sensitive data)
- [ ] Create health check endpoint
- [ ] Add backup verification script
- [ ] Test backup restoration locally
- [ ] Change ALL default passwords
- [ ] Generate strong session secrets
- [ ] Verify SSL certificate is active before forcing HTTPS

### WITHIN 24 HOURS OF DEPLOYMENT:
- [ ] Monitor error logs continuously
- [ ] Test complete purchase flow with real payment
- [ ] Verify webhook receives actual PayMongo events
- [ ] Test backup script runs successfully
- [ ] Check disk space usage
- [ ] Verify email sending works
- [ ] Test rate limiting by attempting multiple logins

### WITHIN FIRST WEEK:
- [ ] Set up uptime monitoring (UptimeRobot, Pingdom)
- [ ] Configure error alerting (email on critical errors)
- [ ] Review all logs for anomalies
- [ ] Test backup restoration
- [ ] Monitor PayMongo dashboard for failed transactions

---

## 🚨 DEPLOYMENT BLOCKERS

**DO NOT DEPLOY until these are fixed:**
1. ✗ Webhook signature verification
2. ✗ Debug files removed
3. ✗ Database credentials secured
4. ✗ Upload folder permissions corrected
5. ✗ Rate limiting implemented on login

**Your deployment will be INSECURE without these fixes!**

---

## Recommended Order of Implementation

1. **Immediate (Before Deployment):**
   - Webhook signature verification
   - Remove debug files
   - Secure DB credentials
   - Fix upload permissions
   - Add rate limiting

2. **Day 1 (After Deployment):**
   - Health check endpoint
   - Backup verification
   - Error monitoring

3. **Week 1:**
   - CSP headers
   - Logging rotation
   - Uptime monitoring

---

**Last Updated:** November 6, 2025  
**Severity:** CRITICAL - Do not deploy without addressing RED items
