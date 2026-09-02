<?php
require_once 'includes/db.php';

echo "<h2>Database Tables and Product Data</h2>";

// Check all tables
echo "<h3>All Tables:</h3>";
$result = $conn->query("SHOW TABLES");
echo "<ul>";
while ($row = $result->fetch_array()) {
    echo "<li>" . $row[0] . "</li>";
}
echo "</ul>";

// Check inventory table
echo "<h3>Inventory Table (used by ProductCatalog.php):</h3>";
$result = $conn->query("SELECT COUNT(*) as count FROM inventory");
$count = $result->fetch_assoc();
echo "<p><strong>Total items in inventory:</strong> " . $count['count'] . "</p>";

$result = $conn->query("SELECT id, product_name, price, stock, tag, image, created_at FROM inventory ORDER BY created_at DESC LIMIT 10");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Product Name</th><th>Price</th><th>Stock</th><th>Tag</th><th>Image</th><th>Created At</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['product_name'] . "</td>";
    echo "<td>₱" . number_format($row['price'], 2) . "</td>";
    echo "<td>" . $row['stock'] . "</td>";
    echo "<td>" . $row['tag'] . "</td>";
    echo "<td>" . $row['image'] . "</td>";
    echo "<td>" . $row['created_at'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Check products table if exists
echo "<h3>Products Table (alternative):</h3>";
$result = $conn->query("SELECT COUNT(*) as count FROM products");
if ($result) {
    $count = $result->fetch_assoc();
    echo "<p><strong>Total items in products:</strong> " . $count['count'] . "</p>";
    
    $result = $conn->query("SELECT id, name, price, stock, category, image_path, created_at FROM products ORDER BY created_at DESC LIMIT 10");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>Price</th><th>Stock</th><th>Category</th><th>Image</th><th>Created At</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>₱" . number_format($row['price'], 2) . "</td>";
        echo "<td>" . $row['stock'] . "</td>";
        echo "<td>" . $row['category'] . "</td>";
        echo "<td>" . $row['image_path'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<hr>";
echo "<p><strong>Note:</strong> ProductCatalog.php reads from the <code>inventory</code> table, not the <code>products</code> table.</p>";
echo "<p>If your groupmates are adding to the <code>products</code> table, the items won't show in ProductCatalog.php</p>";

$conn->close();
?>
