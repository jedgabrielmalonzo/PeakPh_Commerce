# Profile Page Update - User Information Management

## Overview
Updated the profile.php page to match the checkout.php form structure and added save functionality for user contact information and addresses.

## Changes Made

### 1. **New Database Table: `user_profiles`**
- Stores user shipping and billing information
- Linked to users table via foreign key
- Includes map coordinates for precise location
- Auto-fills checkout form when available

### 2. **Updated profile.php**
- Complete redesign matching checkout.php form structure
- Interactive map integration (Leaflet/OpenStreetMap)
- Separate sections for:
  - Contact Information (phone)
  - Shipping Address
  - Map Location Selector
  - Billing Address (with "same as shipping" option)
- Real-time save functionality with AJAX
- Success/Error notifications

### 3. **New Files Created**
- `save_profile.php` - Backend handler for saving profile data
- `create_user_profiles_table.sql` - Database migration script
- `profile_backup.php` - Backup of original profile.php

### 4. **Database Schema Updates**
- Added `user_profiles` table to `database_setup.sql`
- Renumbered subsequent tables (now 13 tables total)

## Installation

### Step 1: Run Database Migration
```bash
# Option A: Run the standalone migration
mysql -u root -p peakph_db < create_user_profiles_table.sql

# Option B: Re-run complete setup (safe - uses IF NOT EXISTS)
mysql -u root -p < database_setup.sql
```

### Step 2: Verify Installation
```sql
USE peakph_db;
SHOW TABLES LIKE 'user_profiles';
DESCRIBE user_profiles;
```

## Features

### Profile Management
- **Contact Information**: Save phone number for orders
- **Shipping Address**: Full address with province/city/postal code
- **Map Location**: Pin exact delivery location on interactive map
- **Billing Address**: Optional separate billing address
- **Auto-save**: AJAX form submission with instant feedback

### Map Features
- 📍 **Use My Location**: Get current GPS coordinates
- 🔍 **Search Address**: Find any location by name
- 🗺️ **Click to Select**: Click anywhere on map to pin location
- 🧹 **Clear Selection**: Remove selected location
- 📊 **Coordinates Display**: Show latitude/longitude

### User Experience
- ✅ Form validation with required field indicators
- ✅ Real-time success/error notifications
- ✅ Responsive design (mobile-friendly)
- ✅ Pre-filled with existing data
- ✅ Same styling as checkout.php for consistency
- ✅ Smooth animations and transitions

## Usage

### For Users
1. Navigate to **Profile** page from navigation menu
2. Fill in contact information (phone number required)
3. Enter shipping address details
4. Optionally pin exact location on map:
   - Check "Pin my exact delivery location on map"
   - Use current location OR search for address OR click on map
5. Choose billing address:
   - Check "Same as shipping address" OR
   - Uncheck and enter separate billing address
6. Click **"Save Profile Information"** button
7. See success message and data is saved

### For Developers
The saved profile data will automatically pre-fill the checkout form in future implementations.

**Example: Getting User Profile**
```php
$user_id = getCurrentUser()['id'];
$query = "SELECT * FROM user_profiles WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
```

## Database Structure

### user_profiles Table
```sql
- id (INT) - Primary key
- user_id (INT) - Foreign key to users.id
- phone (VARCHAR) - Phone number
- shipping_address (TEXT) - Street address
- shipping_address_2 (VARCHAR) - Apt/Suite/etc
- shipping_city (VARCHAR) - City
- shipping_province (VARCHAR) - Province
- shipping_postal_code (VARCHAR) - Postal code
- shipping_country (VARCHAR) - Country (default: Philippines)
- map_latitude (DECIMAL) - GPS latitude
- map_longitude (DECIMAL) - GPS longitude
- map_address (TEXT) - Full address from map
- billing_same_as_shipping (TINYINT) - Boolean flag
- billing_address (TEXT) - Billing street address
- billing_address_2 (VARCHAR) - Billing Apt/Suite/etc
- billing_city (VARCHAR) - Billing city
- billing_province (VARCHAR) - Billing province
- billing_postal_code (VARCHAR) - Billing postal code
- billing_country (VARCHAR) - Billing country
- created_at (TIMESTAMP) - Record creation time
- updated_at (TIMESTAMP) - Last update time
```

## API Endpoint

### save_profile.php
**Method**: POST
**Authentication**: Required (user must be logged in)

**Request Parameters**:
- phone (required)
- shipping_address (required)
- shipping_city (required)
- shipping_province (required)
- shipping_postal_code (required)
- shipping_address_2 (optional)
- shipping_country (optional)
- map_latitude (optional)
- map_longitude (optional)
- map_address (optional)
- same_as_shipping (optional checkbox)
- billing_address (conditional)
- billing_city (conditional)
- billing_province (conditional)
- billing_postal_code (conditional)
- billing_address_2 (optional)
- billing_country (optional)

**Response** (JSON):
```json
{
  "success": true,
  "message": "Profile updated successfully"
}
```

## Benefits

### For Customers
- ⚡ Faster checkout (pre-filled information)
- 🎯 Accurate delivery (pinned map location)
- 💾 Saved preferences
- 📱 Mobile-friendly interface

### For Business
- 📊 Better data quality
- 🚚 Accurate deliveries
- 💰 Reduced cart abandonment
- 📈 Improved user experience

## Next Steps

### Recommended Enhancements
1. **Checkout Integration**: Auto-fill checkout form from saved profile
2. **Multiple Addresses**: Allow users to save multiple shipping addresses
3. **Address Validation**: Integrate with postal code validation API
4. **Order History**: Link to orders.php to show past orders
5. **Email Preferences**: Add notification settings
6. **Password Change**: Add password update functionality

## Troubleshooting

### Profile Not Saving
1. Check if `user_profiles` table exists
2. Verify user is logged in (`$_SESSION['user_id']`)
3. Check browser console for JavaScript errors
4. Verify all required fields are filled

### Map Not Loading
1. Check internet connection (requires OpenStreetMap API)
2. Verify Leaflet JS is loading
3. Check browser console for errors
4. Try refreshing the page

### Database Errors
1. Ensure foreign key constraint is valid (user exists in users table)
2. Check decimal precision for lat/lng fields
3. Verify unique constraint on user_id

## Files Modified/Created

### New Files
- `save_profile.php` - Profile save handler
- `create_user_profiles_table.sql` - Database migration
- `profile_new.php` - New profile page (copied to profile.php)
- `profile_backup.php` - Backup of original

### Modified Files
- `profile.php` - Completely rebuilt with form and save functionality
- `database_setup.sql` - Added user_profiles table definition

## Security Notes
- ✅ User authentication required
- ✅ SQL injection protection (prepared statements)
- ✅ XSS protection (htmlspecialchars on output)
- ✅ CSRF protection recommended (add tokens in future)
- ✅ Input validation on both client and server side

## Compatibility
- PHP 7.4+
- MySQL 5.7+ / MariaDB 10.2+
- Modern browsers (Chrome, Firefox, Safari, Edge)
- Mobile responsive

---

**Last Updated**: October 22, 2025
**Version**: 1.0
