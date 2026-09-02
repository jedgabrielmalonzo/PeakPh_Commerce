# Development Tools Folder

This folder contains test, debug, and setup files used during development.

## ⚠️ Important

**These files are NOT for production use!**

They are kept for:
- Testing new features
- Debugging issues
- Database setup/migration
- Development purposes only

## Files in this Folder

### Test Files
- `test_checkout.php` - Test checkout flow
- `test_complete_flow.php` - End-to-end testing
- `test_gcash_web.php` - GCash payment testing
- `test_integration.html` - Integration test page
- `test_orders.php` - Order system testing
- `test_payment.php` - Payment testing
- `test_payment_manual.php` - Manual payment tests
- `test_paymongo.php` - PayMongo API testing
- `test_store.php` - Store functionality testing

### Debug Files
- `debug_paymongo.php` - PayMongo debugging
- `debug_payment_detailed.php` - Detailed payment debugging
- `debug_payment_detailed2.php` - Payment debugging v2

### Database Check Files
- `check_database.php` - Database connectivity check
- `check_orders_table.php` - Orders table verification
- `check_payments_table.php` - Payments table verification

### Setup Files
- `setup_database.php` - Database setup script
- `setup_paymongo_db.php` - PayMongo tables setup
- `fix_database.php` - Database fix script
- `export_database_schema.php` - Schema export utility
- `update_database_schema.php` - Schema update script

## Usage

To use any test file:
```
http://localhost/PeakPH_Commerce/_dev-tools/test_checkout.php
```

## Security Note

**Never deploy this folder to production!**

Add to `.gitignore` or delete before deploying:
```
_dev-tools/
```
