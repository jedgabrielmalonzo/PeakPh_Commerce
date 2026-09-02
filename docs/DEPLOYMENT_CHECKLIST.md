# ✅ PRODUCTION DEPLOYMENT CHECKLIST
## PeakPH Commerce - Pre-Launch Verification

**Date:** _______________  
**Deployer:** _______________  
**Environment:** Production

---

## 🔴 CRITICAL SECURITY (MUST COMPLETE)

### Database Security
- [ ] Database credentials moved to environment config
- [ ] Strong database password set (16+ characters, mixed)
- [ ] Database user has minimal required permissions only
- [ ] Test database backup and restore works
- [ ] Backup verification script running

### File Security
- [ ] All debug files deleted (`debug_*.php`, `test_*.php`)
- [ ] `_dev-tools/` directory deleted
- [ ] `admin/session_debug.php` deleted
- [ ] `.htaccess` uploaded to root
- [ ] `uploads/.htaccess` uploaded and tested
- [ ] File permissions: folders 755, files 644
- [ ] Upload directory CANNOT execute PHP (tested)

### Authentication & Sessions
- [ ] Admin password changed from default
- [ ] Session security configured (httponly, secure)
- [ ] Rate limiting enabled on login endpoint
- [ ] CSRF protection implemented
- [ ] Session timeout configured (8 hours)

### PayMongo Integration
- [ ] LIVE API keys configured (not test keys)
- [ ] Webhook signature verification implemented
- [ ] Webhook URL registered in PayMongo dashboard
- [ ] Webhook secret key configured
- [ ] Test transaction completed successfully

### Code Security
- [ ] All `error_log()` calls reviewed (no sensitive data)
- [ ] `display_errors` set to OFF
- [ ] All SQL queries use prepared statements
- [ ] Input validation on all user inputs
- [ ] Output sanitization (htmlspecialchars) used

---

## 🟡 HIGH PRIORITY

### SSL/HTTPS
- [ ] SSL certificate installed and active
- [ ] Test HTTPS access works
- [ ] Force HTTPS enabled in .htaccess
- [ ] Mixed content warnings resolved
- [ ] Security headers configured

### Email Configuration
- [ ] Email account created (noreply@domain.com)
- [ ] SMTP settings configured
- [ ] Test email sending works
- [ ] Registration verification email tested
- [ ] Order confirmation email tested

### Error Handling
- [ ] Error logging enabled
- [ ] Log file location created and writable
- [ ] Error log rotation configured
- [ ] Critical error email alerts configured
- [ ] 404 error page created

### Performance
- [ ] Browser caching enabled
- [ ] Gzip compression enabled
- [ ] Images optimized (compressed)
- [ ] CSS/JS minified
- [ ] PHP OPCache enabled (verify with host)

---

## 🟢 IMPORTANT

### Monitoring & Health
- [ ] `health.php` endpoint tested
- [ ] Uptime monitoring configured (UptimeRobot, Pingdom)
- [ ] PayMongo dashboard bookmarked
- [ ] Database size monitoring setup
- [ ] Disk space alerts configured

### Backup System
- [ ] Automated daily database backup script
- [ ] Backup location secured (outside public_html)
- [ ] Backup retention policy (7-30 days)
- [ ] File backup strategy documented
- [ ] Restore procedure tested

### Admin Panel
- [ ] Admin login works
- [ ] Dashboard displays correctly
- [ ] Add product functionality tested
- [ ] Image upload tested
- [ ] Order management tested
- [ ] Inventory sync tested

### User Flow Testing
- [ ] Homepage loads correctly
- [ ] Product browsing works
- [ ] Search functionality works
- [ ] User registration works
- [ ] Email verification received
- [ ] Login works
- [ ] Add to cart works
- [ ] Checkout process works
- [ ] Payment (small amount) successful
- [ ] Order confirmation received
- [ ] Order appears in admin panel

### Static Pages
- [ ] About Us page loads
- [ ] Contact Us page loads
- [ ] Contact form submits
- [ ] Privacy Policy page loads
- [ ] Terms & Conditions page loads
- [ ] Footer links all work
- [ ] Navigation menu works on mobile

---

## 📱 MOBILE TESTING

- [ ] Homepage mobile responsive
- [ ] Product pages mobile responsive
- [ ] Cart mobile responsive
- [ ] Checkout mobile responsive
- [ ] Payment form mobile responsive
- [ ] Admin panel mobile accessible

---

## 🔍 VERIFICATION TESTS

### Security Verification
```bash
# Test 1: Try to access debug file
curl https://yourdomain.com/debug_cart.php
# Expected: 403 Forbidden or 404 Not Found

# Test 2: Try to access dev-tools
curl https://yourdomain.com/_dev-tools/test_payment.php
# Expected: 404 Not Found

# Test 3: Test uploads execution
curl https://yourdomain.com/uploads/test.php
# Expected: 403 Forbidden

# Test 4: Check security headers
curl -I https://yourdomain.com
# Expected: X-Frame-Options, X-Content-Type-Options, CSP headers present

# Test 5: Health check
curl https://yourdomain.com/health.php
# Expected: {"overall_status":"healthy", ...}
```

### Database Verification
```sql
-- Test 1: Check all tables exist
SHOW TABLES;
-- Expected: users, orders, order_items, inventory, paymongo_payments, paymongo_webhooks, admin_logs

-- Test 2: Verify admin user exists
SELECT email, role FROM users WHERE role = 'admin';
-- Expected: At least one admin user

-- Test 3: Check PayMongo tables structure
DESCRIBE paymongo_payments;
DESCRIBE paymongo_webhooks;
-- Expected: All columns present
```

### Performance Verification
- [ ] Page load time < 3 seconds (homepage)
- [ ] Product page load < 2 seconds
- [ ] Checkout page load < 2 seconds
- [ ] Admin dashboard load < 3 seconds
- [ ] Image loading optimized

---

## 🚨 LAUNCH DAY MONITORING

### First 1 Hour
- [ ] Monitor error logs continuously
- [ ] Test complete purchase with real card (small amount)
- [ ] Verify PayMongo webhook received
- [ ] Check order created in database
- [ ] Verify email notifications sent

### First 24 Hours
- [ ] Check error logs every 2 hours
- [ ] Monitor PayMongo dashboard for transactions
- [ ] Monitor server resources (CPU, memory, disk)
- [ ] Test from multiple devices/browsers
- [ ] Collect user feedback

### First Week
- [ ] Daily error log review
- [ ] Daily backup verification
- [ ] Review all orders manually
- [ ] Check for payment discrepancies
- [ ] Monitor uptime reports
- [ ] Address any reported issues immediately

---

## 📊 METRICS TO TRACK

- [ ] Uptime percentage
- [ ] Page load times
- [ ] Error rate
- [ ] Successful vs failed payments
- [ ] Order completion rate
- [ ] User registration count
- [ ] Average order value
- [ ] Payment gateway fees

---

## 🆘 EMERGENCY CONTACTS

| Contact | Purpose | Details |
|---------|---------|---------|
| Hostinger Support | Hosting issues | 24/7 Live Chat |
| PayMongo Support | Payment issues | support@paymongo.com |
| Domain Registrar | Domain issues | _______________ |
| Developer | Technical issues | _______________ |
| Database Admin | DB issues | _______________ |

---

## 🔄 ROLLBACK PLAN

If critical issues arise after deployment:

1. **Immediate Actions:**
   - [ ] Enable maintenance mode (create maintenance.html)
   - [ ] Notify users via social media/email
   - [ ] Document the issue

2. **Rollback Steps:**
   - [ ] Restore previous file backup
   - [ ] Restore previous database backup
   - [ ] Test restored version
   - [ ] Remove maintenance mode

3. **Post-Rollback:**
   - [ ] Identify root cause
   - [ ] Fix issues in development
   - [ ] Test thoroughly
   - [ ] Schedule re-deployment

---

## 📝 DEPLOYMENT SIGN-OFF

| Role | Name | Signature | Date |
|------|------|-----------|------|
| Developer | _____________ | _____________ | _____ |
| Tester | _____________ | _____________ | _____ |
| Stakeholder | _____________ | _____________ | _____ |

---

## ⚠️ KNOWN LIMITATIONS

Document any known issues that will be addressed post-launch:

1. _______________________________
2. _______________________________
3. _______________________________

---

## 🎯 POST-LAUNCH ENHANCEMENTS (30 Days)

- [ ] Add Google Analytics
- [ ] Implement SEO optimizations
- [ ] Add social media integration
- [ ] Set up customer support system
- [ ] Implement advanced reporting
- [ ] Add product reviews/ratings
- [ ] Optimize mobile experience further

---

**This checklist must be 100% complete before going live!**

**Last Updated:** November 6, 2025  
**Version:** 2.0 - Enhanced Security Edition
