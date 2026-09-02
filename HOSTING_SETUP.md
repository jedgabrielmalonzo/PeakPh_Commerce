# 🚀 InfinityFree Hosting Setup Guide

## Pre-Deployment Checklist ✅

Your application is **NOW READY** for InfinityFree hosting with the following configurations:

### 1. Database Configuration ✅
- **File**: `includes/db.php`
- **Status**: Auto-detects environment
- **Production Settings**:
  - Hostname: `sql308.infinityfree.com`
  - Username: `if0_42814827`
  - Password: `PeakPh2026`
  - Database: `if0_42814827_peakph_db`

### 2. Environment Detection ✅
- **File**: `includes/environment.php`
- **Features**:
  - Auto-switches between XAMPP (localhost) and InfinityFree (hosting)
  - Dynamically configures error reporting
  - Sets correct timezone (Asia/Manila)
  - Manages upload and log directories

### 3. PayMongo Configuration ✅
- **File**: `config/paymongo.php`
- **Status**: FIXED - Now uses dynamic URLs
- **Features**:
  - Auto-detects HTTP/HTTPS protocol
  - Works on both localhost and production
  - Environment-aware test mode
  - Callback URLs properly configured

### 4. Security Headers ✅
- **File**: `.htaccess`
- **Status**: Configured
- **Features**:
  - MIME type sniffing prevention
  - Clickjacking protection (X-Frame-Options)
  - XSS protection headers
  - Content Security Policy configured
  - PayMongo domain whitelisted

---

## Step-by-Step Deployment

### Step 1: Create InfinityFree Hosting Account
1. Go to [InfinityFree.net](https://www.infinityfree.net/)
2. Sign up for free hosting
3. Create a new domain/subdomain

### Step 2: Create Database
1. Go to InfinityFree Control Panel
2. Navigate to **MySQL Databases**
3. Create a new database: `if0_42100970_peakph_db`
4. Note the credentials (provided to you)

### Step 3: Export Your Local Database
```bash
# On your local machine with XAMPP running
mysqldump -u root peakph_db > database_backup.sql
```

### Step 4: Upload Files to Hosting

**Using File Manager (FTP)**:
1. Log into InfinityFree Control Panel
2. Go to **File Manager**
3. Upload all files from `PeakPH_Commerce/` to the public_html folder
4. Ensure folder structure is preserved:
   ```
   public_html/
   ├── index.php
   ├── includes/
   │   ├── db.php ✅ (Environment-aware)
   │   ├── environment.php ✅ (Environment-aware)
   ├── config/
   │   ├── paymongo.php ✅ (Dynamic URLs)
   ├── admin/
   ├── api/
   ├── assets/
   └── ... (all other files)
   ```

### Step 5: Import Database
1. In InfinityFree Control Panel → **MySQL Databases** → phpMyAdmin
2. Select your database
3. Go to **Import** tab
4. Upload `database_backup.sql`
5. Click Import

### Step 6: Configure PayMongo Keys (If Using Payments)
You have two options:

**Option A: Environment Variables (Recommended)**
1. Create `.env` file in root directory (if hosting supports):
   ```
   PAYMONGO_SECRET_KEY=your_production_key
   PAYMONGO_PUBLIC_KEY=your_production_key
   WEBHOOK_SECRET=your_webhook_secret
   ```

**Option B: Update in Code** (If .env not supported)
1. Edit `includes/environment.php`
2. Update the PayMongo keys under PRODUCTION_MODE:
   ```php
   define('PAYMONGO_SECRET_KEY', 'your_production_key');
   define('PAYMONGO_PUBLIC_KEY', 'your_production_key');
   define('WEBHOOK_SECRET', 'your_webhook_secret');
   ```

### Step 7: Update Admin Credentials (Optional but Important)
1. Access your site at `https://yourdomain.com/admin/`
2. Check if you need to reset admin password
3. Create new admin account if needed via database

### Step 8: Configure PayMongo Webhooks
1. Log into PayMongo Dashboard
2. Go to **Webhooks**
3. Add webhook URL: `https://yourdomain.com/PeakPH_Commerce/webhooks/paymongo.php`
4. Select events: `payment_intent.updated`, `source.chargeable`
5. Copy webhook secret to your `environment.php`

---

## File Structure Requirements for InfinityFree

✅ **Automatic Path Resolution**: The following files handle paths automatically:
- `includes/db.php` - Database connection
- `includes/environment.php` - Configuration
- `config/paymongo.php` - Payment URLs
- `.htaccess` - Security rules

⚠️ **Important**: 
- Do NOT change file paths manually
- The `BASE_PATH` constant auto-detects the installation directory
- URLs auto-detect HTTP/HTTPS protocol

---

## Testing After Deployment

### 1. Test Database Connection
Visit: `https://yourdomain.com/health.php`
- Should show ✅ Database Connected

### 2. Test Homepage
Visit: `https://yourdomain.com/`
- Products should load correctly

### 3. Test Product Catalog
Visit: `https://yourdomain.com/ProductCatalog.php`
- Should display all products from database

### 4. Test Admin Login
Visit: `https://yourdomain.com/admin/`
- Should load without errors

### 5. Test Payment Flow (If configured)
1. Add product to cart
2. Proceed to checkout
3. Select PayMongo payment
4. Test payment creation
5. Check payment status in admin

---

## Troubleshooting

### Issue: "Database Connection Failed"
**Solution**:
1. Verify database credentials in InfinityFree Control Panel
2. Check that database exists
3. Ensure database is imported correctly
4. Check `includes/db.php` error logs

### Issue: "404 Not Found"
**Solution**:
1. Verify all files uploaded correctly
2. Check that `index.php` exists in root
3. Verify `.htaccess` file is uploaded
4. InfinityFree may have .htaccess disabled - contact support

### Issue: "PayMongo URLs Returning Localhost"
**Solution**:
1. Verify `config/paymongo.php` is updated
2. Check that `environment.php` is loaded
3. Verify `$_SERVER['HTTP_HOST']` contains your domain
4. Check payment test flow

### Issue: "File Upload Not Working"
**Solution**:
1. Verify `uploads/` directory has write permissions
2. Check `uploads/.htaccess` is present
3. Test with small image file first
4. Check file size limits in InfinityFree

### Issue: "Session Not Persisting"
**Solution**:
1. Ensure `session.php` or session initialization is included in all pages
2. Check `/temp/` directory exists and is writable
3. Verify session.save_path is correct

---

## Security Checklist Before Going Live

- [ ] Change all test PayMongo keys to production keys
- [ ] Update admin credentials
- [ ] Test HTTPS certificate installation
- [ ] Verify `.htaccess` security headers are active
- [ ] Disable debug mode in admin pages
- [ ] Set proper file permissions (644 for files, 755 for dirs)
- [ ] Remove debug files from `_dev-tools/` (optional)
- [ ] Test SQL injection protection on search
- [ ] Verify user password hashing is working
- [ ] Test upload file type restrictions

---

## Production Keys Setup

When ready for live payments:

1. **Get Production Keys from PayMongo**:
   - Visit PayMongo Dashboard
   - Navigate to API Keys
   - Copy Production Secret Key
   - Copy Production Public Key

2. **Update in `includes/environment.php`**:
   ```php
   if (PRODUCTION_MODE) {
       define('PAYMONGO_SECRET_KEY', 'sk_live_xxxxxxxxxxxx');
       define('PAYMONGO_PUBLIC_KEY', 'pk_live_xxxxxxxxxxxx');
   }
   ```

3. **Enable Test Mode = false** in `config/paymongo.php`

4. **Test first**:
   - Create test transaction
   - Verify payment goes through
   - Check webhook delivery

---

## Support & Monitoring

### Logs to Monitor:
- Database errors: `/logs/db.log`
- Payment errors: `/logs/payment.log`
- Application errors: PHP error log in cPanel

### Regular Maintenance:
- Monitor database size
- Clean up old logs monthly
- Backup database weekly
- Monitor storage usage

---

## Quick Reference URLs

After deployment, your site URLs will be:
- **Homepage**: `https://yourdomain.com/`
- **Product Catalog**: `https://yourdomain.com/ProductCatalog.php`
- **Shopping Cart**: `https://yourdomain.com/cart.php`
- **Admin Dashboard**: `https://yourdomain.com/admin/`
- **Orders**: `https://yourdomain.com/admin/orders.php`
- **Payment Webhooks**: `https://yourdomain.com/webhooks/paymongo.php`

---

**Last Updated**: 2026-06-10
**Configuration Status**: ✅ PRODUCTION READY
