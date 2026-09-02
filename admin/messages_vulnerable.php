<?php
/**
 * DELIBERATELY VULNERABLE ADMIN MESSAGES PAGE - FOR EDUCATIONAL PURPOSES ONLY
 * Demonstrates Stored XSS vulnerability
 * 
 * VULNERABILITIES:
 * 1. No authentication check
 * 2. Database data displayed without escaping (Stored XSS)
 * 3. No SQL injection protection
 */

require_once '../includes/environment.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$messages = [];
$result = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Contact Messages (VULNERABLE)</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #d32f2f; }
        .warning { background: #ffebee; border: 1px solid #d32f2f; padding: 15px; border-radius: 5px; margin-bottom: 20px; color: #c62828; }
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #2e765e; color: white; }
        tr:hover { background: #f5f5f5; }
        .xss-alert { background: #fff3cd; padding: 10px; border-radius: 3px; margin: 5px 0; }
        .message-content { max-width: 400px; word-wrap: break-word; }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚠️ Admin - Contact Messages (VULNERABLE VERSION)</h1>
        
        <div class="warning">
            <strong>⚠️ SECURITY WARNING:</strong> This page is INTENTIONALLY VULNERABLE for educational purposes.<br>
            • No input sanitization<br>
            • Data displayed without HTML escaping (Stored XSS)<br>
            • No authentication required<br>
            • DO NOT USE IN PRODUCTION
        </div>
        
        <?php if (empty($messages)): ?>
            <p>No messages received yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>IP Address</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $msg): ?>
                        <tr>
                            <td><?php echo $msg['id']; ?></td>
                            
                            <!-- VULNERABILITY: Name displayed WITHOUT escaping -->
                            <td class="message-content">
                                <?php echo $msg['name']; ?>
                            </td>
                            
                            <!-- VULNERABILITY: Email displayed WITHOUT escaping -->
                            <td class="message-content">
                                <?php echo $msg['email']; ?>
                            </td>
                            
                            <!-- VULNERABILITY: Subject displayed WITHOUT escaping -->
                            <td class="message-content">
                                <?php echo $msg['subject']; ?>
                            </td>
                            
                            <!-- VULNERABILITY: Message displayed WITHOUT escaping (CRITICAL for long text) -->
                            <td class="message-content">
                                <?php echo $msg['message']; ?>
                            </td>
                            
                            <td><?php echo $msg['ip_address']; ?></td>
                            <td><?php echo $msg['created_at']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <div class="warning" style="margin-top: 20px;">
            <strong>Test XSS Payloads:</strong><br>
            Try entering these in the contact form to test stored XSS:<br>
            • <code>&lt;img src=x onerror="alert('XSS')"&gt;</code><br>
            • <code>&lt;script&gt;alert('Stored XSS')&lt;/script&gt;</code><br>
            • <code>&lt;svg/onload=alert('XSS')&gt;</code><br>
            • <code>"/&gt;&lt;script&gt;alert('XSS')&lt;/script&gt;</code>
        </div>
    </div>
</body>
</html>
