<?php
/**
 * DELIBERATELY VULNERABLE - FOR EDUCATIONAL PURPOSES ONLY
 * Creates contact messages table WITHOUT proper sanitization
 * Demonstrates stored XSS vulnerability
 */

require_once '../includes/environment.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Drop table if exists (for testing reset)
$conn->query("DROP TABLE IF EXISTS contact_messages");

// Create VULNERABLE table - no constraints, accepts anything
$sql = "CREATE TABLE contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(500),
    email VARCHAR(500),
    phone VARCHAR(500),
    subject VARCHAR(500),
    message LONGTEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "✓ Vulnerable contact_messages table created successfully!<br>";
    echo "⚠️  This table accepts ANY data without validation<br>";
    echo "🔓 XSS payloads will be stored directly in the database<br>";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
