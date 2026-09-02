<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/db.php';

echo "<h2>Database Migration: Add first_name and last_name to user_profiles</h2>";

// Check if columns already exist
$check_query = "SHOW COLUMNS FROM user_profiles LIKE 'first_name'";
$result = $conn->query($check_query);

if ($result->num_rows > 0) {
    echo "<p style='color: orange;'>✓ Column 'first_name' already exists</p>";
} else {
    echo "<p>Adding 'first_name' column...</p>";
    $sql1 = "ALTER TABLE user_profiles ADD COLUMN first_name VARCHAR(100) DEFAULT NULL AFTER user_id";
    if ($conn->query($sql1)) {
        echo "<p style='color: green;'>✓ Successfully added 'first_name' column</p>";
    } else {
        echo "<p style='color: red;'>✗ Error: " . $conn->error . "</p>";
    }
}

$check_query = "SHOW COLUMNS FROM user_profiles LIKE 'last_name'";
$result = $conn->query($check_query);

if ($result->num_rows > 0) {
    echo "<p style='color: orange;'>✓ Column 'last_name' already exists</p>";
} else {
    echo "<p>Adding 'last_name' column...</p>";
    $sql2 = "ALTER TABLE user_profiles ADD COLUMN last_name VARCHAR(100) DEFAULT NULL AFTER first_name";
    if ($conn->query($sql2)) {
        echo "<p style='color: green;'>✓ Successfully added 'last_name' column</p>";
    } else {
        echo "<p style='color: red;'>✗ Error: " . $conn->error . "</p>";
    }
}

// Show updated structure
echo "<h3>Updated user_profiles table structure:</h3>";
$result = $conn->query("DESCRIBE user_profiles");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<br><p><strong>Migration completed!</strong></p>";
echo "<p><a href='checkout.php'>Test Checkout Page</a></p>";

$conn->close();
?>
