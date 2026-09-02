# Hostinger Deployment Guide for PeakPH Commerce

## Prerequisites
- Active Hostinger hosting account (Shared/Cloud/VPS)
- Domain name (optional, can use Hostinger subdomain)
- FTP client (FileZilla) or use Hostinger File Manager
- Your project files ready

---

## Step 1: Prepare Your Local Files

### 1.1 Create a Clean Copy
```powershell
# Navigate to your project
cd c:\xampp\htdocs\PeakPH_Commerce

# Create a deployment folder
mkdir deployment
```

### 1.2 Files to Include
Copy these folders/files to `deployment`:
- ✅ `admin/` (entire folder)
- ✅ `api/` (entire folder)
- ✅ `Assets/` (entire folder)
- ✅ `auth/` (entire folder)
- ✅ `components/` (entire folder)
- ✅ `config/` (entire folder)
- ✅ `Css/` (entire folder)
- ✅ `database/` (schema files only)
- ✅ `includes/` (entire folder)
- ✅ `Js/` (entire folder)
- ✅ `pages/` (entire folder)
- ✅ `payment/` (entire folder)
- ✅ `uploads/` (create empty folder)
- ✅ `webhooks/` (entire folder)
- ✅ All root PHP files (index.php, cart.php, etc.)
- ✅ `.htaccess` (if exists)

### 1.3 Files to EXCLUDE
- ❌ `_dev-tools/` (development only)
- ❌ `_archive/` (old backups)
- ❌ `temp/` (temporary files)
- ❌ `docs/` (documentation, optional)
- ❌ `.git/` (if exists)
- ❌ `node_modules/` (if exists)

### 1.4 Create Archive
```powershell
# Compress the deployment folder
Compress-Archive -Path deployment\* -DestinationPath PeakPH_Deploy.zip
```

---

## Step 2: Set Up Database on Hostinger

### 2.1 Access Hostinger Control Panel (hPanel)
1. Log in to Hostinger: https://www.hostinger.com/
2. Go to **Hosting** → Select your hosting plan
3. Click **Manage** beside your domain

### 2.2 Create MySQL Database
1. In hPanel, find **Databases** section
2. Click **MySQL Databases**
3. Click **Create New Database**
   - **Database Name**: `u123456789_peakph` (Hostinger adds prefix automatically)
   - **Username**: Create new or use existing
   - **Password**: Use strong password (save this!)
   - Click **Create**

4. **Save These Details:**
   ```
   Database Host: localhost
   Database Name: u532428036_Peakph_db
   Database User: u532428036_MrPeak
   Database Password: PeakPH_2025
   Database Port: 3306
   ```

### 2.3 Import Database Schema
1. In hPanel, click **phpMyAdmin** (under Databases section)
2. Select your database from left sidebar
3. Click **Import** tab
4. Click **Choose File** → Select `database_setup.sql` from your project
5. Alternative files to import (in order):
   - `database/paymongo_schema.sql`
   - `database/add_product_details_columns.sql`
   - `admin/create_admin_logs_table.sql`
6. Click **Go** to import
7. Verify tables are created (check left sidebar)

**Expected Tables:**
- `inventory`
- `users`
- `orders`
- `order_items`
- `paymongo_payments`
- `paymongo_webhooks`
- `admin_logs`
- `user_profiles`

---

## Step 3: Upload Files to Hostinger

### Method A: Using File Manager (Easier)

1. In hPanel, go to **Files** → **File Manager**
2. Navigate to `public_html/` folder
3. Click **Upload** button (top right)
4. Upload `PeakPH_Deploy.zip`
5. Right-click the ZIP file → **Extract**
6. Delete the ZIP file after extraction
7. Make sure all files are in `public_html/` root (not in a subfolder)

### Method B: Using FileZilla (More Control)

1. **Download FileZilla**: https://filezilla-project.org/
2. **Get FTP Credentials** from Hostinger:
   - In hPanel → **Files** → **FTP Accounts**
   - Click **Configure FTP Client**
   - Note: Host, Username, Password, Port

3. **Connect via FileZilla:**
   ```
   Host: ftp.yourdomain.com
   Username: u123456789 (or specific FTP user)
   Password: [your_ftp_password]
   Port: 21
   ```

4. **Upload Files:**
   - Left panel: Local files (your deployment folder)
   - Right panel: Navigate to `/public_html/`
   - Drag all files from left to right
   - Wait for upload to complete (may take 10-30 minutes)

---

## Step 4: Configure Database Connection

### 4.1 Update db.php
1. In File Manager, navigate to: `public_html/includes/db.php`
2. Click **Edit** (or use FileZilla to edit)
3. Update these lines:

```php
<?php
// Database configuration for Hostinger
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'u532428036_MrPeak');
define('DB_PASSWORD', 'PeakPH_2025');
define('DB_NAME', 'u532428036_Peakph_db');

// Create connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");
?>
```

4. **Save the file**

### 4.2 Test Database Connection
Create a test file to verify connection:

1. Create `public_html/test_db.php`:
```php
<?php
require_once 'includes/db.php';

echo "<!DOCTYPE html><html><body>";
echo "<h1>Database Connection Test</h1>";

if ($conn) {
    echo "<p style='color: green;'>✓ Connected successfully!</p>";
    
    // Test query
    $result = $conn->query("SHOW TABLES");
    echo "<p>Tables found: " . $result->num_rows . "</p>";
    echo "<ul>";
    while ($row = $result->fetch_array()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>✗ Connection failed!</p>";
}

echo "</body></html>";
?>
```

2. Visit: `http://yourdomain.com/test_db.php`
3. Should show all database tables
4. **Delete this file after testing!**

---

## Step 5: Configure File Permissions

### 5.1 Set Folder Permissions
In File Manager, set these permissions (right-click → **Permissions**):

```
uploads/               → 755 or 777 (write access)
admin/uploads/         → 755 or 777
admin/inventory/uploads/ → 755 or 777
admin/content/         → 755 (write access for JSON files)
```

### 5.2 Set File Permissions
```
*.php files           → 644
*.json files          → 644
*.css, *.js files     → 644
```

---

## Step 6: Configure .htaccess (Optional but Recommended)

Create/edit `public_html/.htaccess`:

```apache
# Enable PHP error reporting (disable in production)
# php_flag display_errors off
# php_flag log_errors on

# Set default file
DirectoryIndex index.php index.html

# Protect sensitive files
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

# Protect database files
<FilesMatch "\.sql$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Protect JSON config files (optional)
<FilesMatch "\.json$">
    Order allow,deny
    Deny from all
    # Allow specific access if needed
    # Allow from 127.0.0.1
</FilesMatch>

# Enable mod_rewrite (for clean URLs if needed)
RewriteEngine On

# Force HTTPS (uncomment when SSL is active)
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Remove .php extension (optional)
# RewriteCond %{REQUEST_FILENAME} !-d
# RewriteCond %{REQUEST_FILENAME}\.php -f
# RewriteRule ^(.*)$ $1.php [L]

# Deny access to dev-tools folder
RedirectMatch 404 /_dev-tools/.*
RedirectMatch 404 /_archive/.*
</apache>
```

---

## Step 7: Configure PayMongo (Payment Gateway)

### 7.1 Update config/paymongo.php
```php
<?php
// PayMongo API Configuration for Production
define('PAYMONGO_SECRET_KEY', 'sk_live_YOUR_LIVE_SECRET_KEY'); // Use LIVE key!
define('PAYMONGO_PUBLIC_KEY', 'pk_live_YOUR_LIVE_PUBLIC_KEY'); // Use LIVE key!
define('PAYMONGO_API_URL', 'https://api.paymongo.com/v1');

// Webhook configuration
define('WEBHOOK_SECRET', 'whsec_YOUR_WEBHOOK_SECRET');
define('SUCCESS_URL', 'https://yourdomain.com/order_confirmation.php');
define('FAILED_URL', 'https://yourdomain.com/checkout.php?error=payment_failed');

return [
    'secret_key' => PAYMONGO_SECRET_KEY,
    'public_key' => PAYMONGO_PUBLIC_KEY,
    'api_url' => PAYMONGO_API_URL,
    'webhook_secret' => WEBHOOK_SECRET,
    'success_url' => SUCCESS_URL,
    'failed_url' => FAILED_URL
];
?>
```

### 7.2 Set Up Webhooks
1. Go to PayMongo Dashboard: https://dashboard.paymongo.com/
2. Navigate to **Developers** → **Webhooks**
3. Create webhook:
   - **URL**: `https://yourdomain.com/webhooks/paymongo_webhook.php`
   - **Events**: Select `payment.paid`, `payment.failed`, `source.chargeable`
   - Copy the **Signing Secret** and update `WEBHOOK_SECRET` in config

---

## Step 8: SSL Certificate (HTTPS)

### 8.1 Enable SSL in Hostinger
1. In hPanel, go to **Security** → **SSL**
2. For your domain, click **Install SSL**
3. Hostinger provides **FREE SSL** (Let's Encrypt)
4. Wait 10-20 minutes for activation
5. Your site will be accessible via `https://yourdomain.com`

### 8.2 Force HTTPS
After SSL is active, uncomment these lines in `.htaccess`:
```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## Step 9: Create Admin Account

### 9.1 Access phpMyAdmin
1. In hPanel → **Databases** → **phpMyAdmin**
2. Select your database
3. Click **users** table
4. Click **Insert** tab

### 9.2 Insert Admin User
```sql
INSERT INTO users (
    full_name, 
    email, 
    phone, 
    password, 
    role, 
    is_verified, 
    created_at
) VALUES (
    'Admin User',
    'admin@peakph.com',
    '+639123456789',
    '$2y$10$abcdefghijklmnopqrstuvwxyz123456', -- Use hashed password!
    'admin',
    1,
    NOW()
);
```

### 9.3 Generate Password Hash
Create temporary file `public_html/hash_password.php`:
```php
<?php
$password = 'your_admin_password';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Password: $password<br>";
echo "Hash: $hash<br>";
echo "<br><strong>Copy the hash above and use in SQL insert!</strong>";
// DELETE THIS FILE AFTER USE!
?>
```

Visit `yourdomain.com/hash_password.php`, copy the hash, then **DELETE THE FILE**.

---

## Step 10: Test Your Website

### 10.1 Basic Tests
- ✅ Visit: `https://yourdomain.com`
- ✅ Homepage loads correctly
- ✅ Images display properly
- ✅ Navigate to Shop/Products
- ✅ Product pages load
- ✅ Search functionality works

### 10.2 User Flow Tests
- ✅ Sign up new account
- ✅ Verify email (check if emails are sent)
- ✅ Login with new account
- ✅ Add items to cart
- ✅ View cart
- ✅ Proceed to checkout
- ✅ Test payment (use PayMongo test cards)
- ✅ Verify wishlist

### 10.3 Admin Tests
- ✅ Login to admin: `yourdomain.com/admin/login.php`
- ✅ Access admin dashboard
- ✅ Add new product
- ✅ Upload product images
- ✅ View orders
- ✅ Update order status

### 10.4 Static Pages Tests
- ✅ About Us page loads
- ✅ Contact Us page loads
- ✅ Contact form submission
- ✅ Privacy Policy page
- ✅ Terms and Conditions page
- ✅ Footer links work

---

## Step 11: Email Configuration

### 11.1 Configure PHP Mail
Hostinger supports PHP `mail()` function by default, but for better deliverability, use SMTP.

### 11.2 Update Email Settings
Create/edit `includes/email_config.php`:
```php
<?php
// Email configuration
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'noreply@yourdomain.com'); // Your email
define('SMTP_PASSWORD', 'your_email_password');
define('SMTP_FROM_EMAIL', 'noreply@yourdomain.com');
define('SMTP_FROM_NAME', 'PeakPH Commerce');

// For basic PHP mail()
ini_set('sendmail_from', 'noreply@yourdomain.com');
?>
```

### 11.3 Create Email Account
1. In hPanel → **Email** → **Email Accounts**
2. Create: `noreply@yourdomain.com`
3. Set strong password
4. Use these credentials in your email config

---

## Step 12: Performance Optimization

### 12.1 Enable Caching
Add to `.htaccess`:
```apache
# Browser Caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>

# Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
```

### 12.2 Optimize Images
Before uploading:
- Compress images (use TinyPNG or similar)
- Resize to appropriate dimensions
- Use WebP format if possible

### 12.3 Enable PHP OPCache
Contact Hostinger support to enable OPCache for better PHP performance.

---

## Step 13: Security Checklist

### 13.1 Essential Security Steps
- ✅ Change all default passwords
- ✅ Use strong database password
- ✅ Delete test files (test_db.php, hash_password.php)
- ✅ Set proper file permissions
- ✅ Enable HTTPS/SSL
- ✅ Protect admin directory (add .htaccess in admin folder)
- ✅ Disable directory browsing
- ✅ Keep PHP version updated (in hPanel)

### 13.2 Protect Admin Directory
Create `public_html/admin/.htaccess`:
```apache
# Protect admin directory
AuthType Basic
AuthName "Admin Area"
AuthUserFile /home/u532428036/public_html/admin/.htpasswd
Require valid-user

# Or use IP restriction
# Order Deny,Allow
# Deny from all
# Allow from YOUR_IP_ADDRESS
```

Generate .htpasswd:
- Use online generator: https://www.web2generators.com/apache-tools/htpasswd-generator
- Upload to `/home/u532428036/public_html/admin/.htpasswd`

---

## Step 14: Backup Strategy

### 14.1 Automatic Backups
Hostinger includes automatic backups, but set up additional backups:

1. **Database Backup Script** (`cron_backup.php`):
```php
<?php
// Run daily via cron job
$backup_file = 'backups/db_' . date('Y-m-d') . '.sql';
$command = "mysqldump -u u532428036_MrPeak -p'PeakPH_2025' u532428036_Peakph_db > $backup_file";
exec($command);

// Keep only last 7 days
$files = glob('backups/db_*.sql');
if (count($files) > 7) {
    array_map('unlink', array_slice($files, 0, count($files) - 7));
}
?>
```

2. **Set Up Cron Job** (in hPanel):
   - Go to **Advanced** → **Cron Jobs**
   - Add: `0 2 * * * /usr/bin/php /home/u532428036/public_html/cron_backup.php`
   - Runs daily at 2 AM

---

## Step 15: Domain Configuration

### 15.1 If Using Custom Domain
1. Point domain to Hostinger nameservers:
   ```
   ns1.dns-parking.com
   ns2.dns-parking.com
   ```
2. Wait 24-48 hours for DNS propagation
3. In hPanel, add domain under **Domains**

### 15.2 If Using Subdomain
1. In hPanel → **Domains** → **Subdomains**
2. Create: `shop.yourdomain.com`
3. Point to `public_html/` directory

---

## Troubleshooting Common Issues

### Issue 1: White Screen / 500 Error
**Solution:**
- Check PHP version (PHP 7.4+ required)
- Check error logs: hPanel → **Files** → **Error Log**
- Verify file permissions
- Check db.php configuration

### Issue 2: Images Not Loading
**Solution:**
- Check file paths (use relative paths)
- Verify `uploads/` folder permissions (755 or 777)
- Check if images were uploaded correctly
- Update image paths in database

### Issue 3: Database Connection Failed
**Solution:**
- Verify database credentials in `includes/db.php`
- Check database name (includes Hostinger prefix)
- Ensure database user has permissions
- Test in phpMyAdmin

### Issue 4: PayMongo Not Working
**Solution:**
- Use LIVE API keys (not test keys)
- Update webhook URLs to production domain
- Check HTTPS is enabled
- Verify webhook secret in config

### Issue 5: Emails Not Sending
**Solution:**
- Create email account in Hostinger
- Use SMTP instead of PHP mail()
- Check spam folder
- Verify email configuration

---

## Post-Deployment Checklist

- [ ] Database imported successfully
- [ ] All files uploaded to public_html/
- [ ] db.php configured with correct credentials
- [ ] File permissions set correctly
- [ ] SSL certificate installed and active
- [ ] Admin account created
- [ ] Test user registration
- [ ] Test product browsing
- [ ] Test add to cart
- [ ] Test checkout process
- [ ] Test payment (small amount)
- [ ] Admin panel accessible
- [ ] All static pages working
- [ ] Footer links functional
- [ ] Email notifications working
- [ ] PayMongo webhooks configured
- [ ] Backup strategy in place
- [ ] Test files deleted
- [ ] Error reporting disabled in production
- [ ] Analytics added (Google Analytics)
- [ ] Favicon added

---

## Maintenance & Updates

### Weekly Tasks
- Check error logs
- Monitor disk space usage
- Review order reports
- Backup database manually

### Monthly Tasks
- Update PHP version (if available)
- Review security settings
- Test backup restoration
- Check broken links
- Update product inventory

### When Updating Code
1. Test locally first
2. Backup current production files
3. Backup database
4. Upload new files via FTP
5. Test immediately after update
6. Keep old files for 1 week

---

## Support Resources

- **Hostinger Help Center**: https://support.hostinger.com/
- **Hostinger Live Chat**: Available 24/7
- **PayMongo Support**: support@paymongo.com
- **PHP Documentation**: https://www.php.net/docs.php

---

## Quick Command Reference

### FileZilla FTP Upload
```
Host: ftp.yourdomain.com
Username: u532428036_MrPeak (or FTP username from hPanel)
Password: [your_ftp_password]
Port: 21
```

### Database Import via CLI (if SSH access)
```bash
mysql -u u532428036_MrPeak -p u532428036_Peakph_db < database_setup.sql
```

### Check PHP Version
Create `phpinfo.php`:
```php
<?php phpinfo(); ?>
```
Visit, then delete file.

### File Permission Commands (if SSH access)
```bash
chmod 755 uploads/
chmod 644 *.php
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
```

---

**Last Updated:** November 6, 2025  
**Guide Version:** 1.0  
**For:** PeakPH Commerce E-commerce Platform

Good luck with your deployment! 🚀
