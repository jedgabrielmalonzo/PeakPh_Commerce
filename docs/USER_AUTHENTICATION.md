# User Authentication System Documentation

## Overview
This document describes the complete user authentication system implemented for PeakPH Commerce, including login, signup, OTP verification, and user session management.

## System Components

### 1. Backend Files

#### Authentication Handlers
- **`auth/login_handler.php`**: Processes user login requests
- **`auth/signup_handler.php`**: Handles user registration
- **`auth/verify_otp.php`**: Verifies email OTP codes
- **`auth/resend_otp.php`**: Resends OTP verification codes

#### Core Utilities
- **`includes/user_auth.php`**: Authentication helper functions
- **`logout.php`**: Handles user logout

### 2. Frontend Components

#### Modal System
- **`components/auth_modal.php`**: Authentication modal HTML
- **`components/auth_modal_otp.js`**: JavaScript for modal functionality

#### User Interface
- **Updated Navigation**: User dropdown menu with profile access
- **Profile Pages**: 
  - `profile.php`: User profile information
  - `orders.php`: Order history (placeholder)
  - `settings.php`: Account settings (placeholder)

## Features

### 1. User Registration
- **Fields**: Full name, email, password, confirm password
- **Validation**: 
  - Email format validation
  - Password length (minimum 6 characters)
  - Password confirmation matching
  - Duplicate email checking
- **OTP Verification**: 6-digit email verification code
- **Auto-login**: Users are automatically logged in after verification

### 2. User Login
- **Fields**: Email and password
- **Security**: Password hashing with PHP's `password_verify()`
- **Session Management**: Secure session handling
- **Activity Logging**: Login/logout events tracked in audit trail

### 3. Session Management
- **Session Variables**:
  - `user_logged_in`: Boolean authentication status
  - `user_id`: Database user ID
  - `user_name`: User's display name
  - `user_email`: User's email address
  - `user_role`: User role (User/Admin)
- **Session Timeout**: 60-minute inactivity timeout
- **CSRF Protection**: Token-based form protection

### 4. User Interface Features
- **Responsive Design**: Mobile-friendly authentication forms
- **User Dropdown**: Profile menu with navigation options
- **Password Visibility**: Toggle password visibility
- **Real-time Validation**: Client-side form validation
- **Loading States**: Visual feedback during operations

## Database Integration

### Tables Used
```sql
users (
    id, username, email, password, role, status, 
    created_at, updated_at
)

audit_trail (
    id, table_name, record_id, action, old_values, 
    new_values, user_id, user_email, timestamp, ip_address
)
```

### Security Features
- **Password Hashing**: `password_hash()` with default algorithm
- **SQL Injection Protection**: Prepared statements
- **Input Sanitization**: `htmlspecialchars()` for output
- **Session Security**: Secure session configuration

## API Endpoints

### POST /auth/login_handler.php
**Request Body:**
```json
{
    "email": "user@example.com",
    "password": "userpassword"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com"
    }
}
```

### POST /auth/signup_handler.php
**Request Body:**
```json
{
    "full_name": "John Doe",
    "email": "user@example.com",
    "password": "userpassword",
    "confirm_password": "userpassword"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Verification code sent to your email",
    "email": "user@example.com",
    "debug_code": "123456"
}
```

### POST /auth/verify_otp.php
**Request Body:**
```json
{
    "otp_code": "123456"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Account created successfully! Welcome to PeakPH!",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com"
    }
}
```

## Helper Functions

### Authentication Helpers (`includes/user_auth.php`)

#### `isUserLoggedIn()`
- **Returns**: `bool`
- **Purpose**: Check if user is authenticated

#### `getCurrentUser()`
- **Returns**: `array|null`
- **Purpose**: Get current user data

#### `requireLogin($redirect_to = null)`
- **Purpose**: Redirect to login if not authenticated
- **Parameters**: Optional redirect URL after login

#### `getAuthNavigationHTML()`
- **Returns**: `string`
- **Purpose**: Generate authentication navigation HTML

#### `generateCSRFToken()` / `verifyCSRFToken($token)`
- **Purpose**: CSRF protection for forms

## Page Integration

### Updated Pages
All user-facing pages now include:
- Authentication-aware navigation
- Login modal integration
- Session management
- User dropdown menu

**Pages Updated:**
- `index.php`: Homepage with auth integration
- `ProductCatalog.php`: Product catalog with user context
- `cart.php`: Shopping cart with auth
- `checkout.php`: Checkout process with user data
- `ProductView.php`: Product details with auth
- `order_confirmation.php`: Order confirmation with user context

### New Protected Pages
- **`profile.php`**: User profile information
- **`orders.php`**: Order history (placeholder)
- **`settings.php`**: Account settings (placeholder)

## Security Considerations

### Implemented
- Password hashing with secure algorithms
- Session management with timeout
- SQL injection protection
- XSS prevention with output escaping
- CSRF token protection
- Input validation and sanitization

### Future Enhancements
- Email verification (currently using debug codes)
- Password reset functionality
- Two-factor authentication
- Rate limiting for login attempts
- Enhanced session security
- Account lockout after failed attempts

## Usage Examples

### Check if User is Logged In
```php
<?php
require_once 'includes/user_auth.php';

if (isUserLoggedIn()) {
    $user = getCurrentUser();
    echo "Welcome, " . htmlspecialchars($user['name']);
} else {
    echo "Please log in";
}
?>
```

### Protect a Page
```php
<?php
require_once 'includes/user_auth.php';

// Require login for this page
requireLogin();

// Page content for authenticated users only
?>
```

### Generate Navigation
```php
<div class="top-icons">
    <?php echo getAuthNavigationHTML(); ?>
    <!-- Other navigation items -->
</div>
```

## Troubleshooting

### Common Issues
1. **Modal not appearing**: Check if auth_modal.php is included
2. **JavaScript errors**: Verify auth_modal_otp.js is loaded
3. **Session issues**: Check session configuration and database connection
4. **OTP not working**: Currently using debug codes (check server logs)

### Debug Mode
- OTP codes are logged to PHP error log
- Debug codes returned in API responses (remove in production)
- Session data can be inspected with `var_dump($_SESSION)`

## Production Deployment

### Before Going Live
1. Remove debug OTP codes from responses
2. Implement real email sending for OTP
3. Configure secure session settings
4. Set up HTTPS
5. Review and test all security measures
6. Configure proper error logging
7. Test session timeout behavior

### Environment Configuration
- Set secure session cookie flags
- Configure proper error reporting
- Set up email service for OTP delivery
- Configure database connection security
- Set appropriate file permissions

---

**Version**: 1.0.0  
**Last Updated**: October 2, 2025  
**Status**: Development Complete - Ready for Production Setup