<?php
require_once 'includes/db.php';

echo "<h2>Product Visibility Diagnostic</h2>";

// Check all products in inventory
echo "<h3>All Products in Inventory Table:</h3>";
$result = $conn->query("SELECT id, product_name, price, stock, tag, label, created_at FROM inventory ORDER BY created_at DESC");
$total = 0;
$visible = 0;
$hidden = 0;

echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr style='background: #333; color: white;'><th>ID</th><th>Product Name</th><th>Price</th><th>Stock</th><th>Tag</th><th>Label</th><th>Visible?</th><th>Created</th></tr>";

while ($row = $result->fetch_assoc()) {
    $total++;
    $is_visible = $row['stock'] > 0;
    
    if ($is_visible) {
        $visible++;
        $bg_color = "#d4edda"; // green
        $status = "✓ VISIBLE";
    } else {
        $hidden++;
        $bg_color = "#f8d7da"; // red
        $status = "✗ HIDDEN (stock = " . ($row['stock'] ?? 'NULL') . ")";
    }
    
    echo "<tr style='background: $bg_color;'>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td><strong>" . htmlspecialchars($row['product_name']) . "</strong></td>";
    echo "<td>₱" . number_format($row['price'], 2) . "</td>";
    echo "<td><strong>" . ($row['stock'] ?? 'NULL') . "</strong></td>";
    echo "<td>" . htmlspecialchars($row['tag'] ?? 'none') . "</td>";
    echo "<td>" . htmlspecialchars($row['label'] ?? 'none') . "</td>";
    echo "<td style='font-weight: bold;'>" . $status . "</td>";
    echo "<td>" . date('Y-m-d H:i', strtotime($row['created_at'])) . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<div style='margin-top: 20px; padding: 15px; background: #e9ecef; border-left: 4px solid #007bff;'>";
echo "<h3>Summary:</h3>";
echo "<ul>";
echo "<li><strong>Total products:</strong> $total</li>";
echo "<li><strong>Visible in catalog:</strong> <span style='color: green; font-weight: bold;'>$visible</span></li>";
echo "<li><strong>Hidden (stock = 0 or NULL):</strong> <span style='color: red; font-weight: bold;'>$hidden</span></li>";
echo "</ul>";
echo "</div>";

echo "<div style='margin-top: 20px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107;'>";
echo "<h3>⚠️ Issue Found:</h3>";
echo "<p><strong>ProductCatalog.php only shows products with stock > 0</strong></p>";
echo "<p>If your groupmates added products with stock = 0 or didn't set a stock value, those products won't appear in the catalog.</p>";
echo "<h4>Solutions:</h4>";
echo "<ol>";
echo "<li><strong>Quick Fix:</strong> Tell your groupmates to set stock > 0 when adding products</li>";
echo "<li><strong>Edit Products:</strong> Go to admin panel and update stock values for hidden products</li>";
echo "<li><strong>Change Code:</strong> Remove the 'stock > 0' condition from ProductCatalog.php (not recommended)</li>";
echo "</ol>";
echo "</div>";

echo "<div style='margin-top: 20px;'>";
echo "<a href='ProductCatalog.php' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>View Product Catalog</a> ";
echo "<a href='admin/inventory/index.php' style='padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;'>Edit Inventory (Admin)</a>";
echo "</div>";

$conn->close();
?>
