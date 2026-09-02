<?php
require_once 'includes/db.php';

echo "<h2>Stock Values Debug</h2>";

// Get recent products with their actual stock values
$query = "SELECT 
    id, 
    product_name, 
    stock,
    CASE 
        WHEN stock IS NULL THEN 'NULL'
        WHEN stock = 0 THEN 'ZERO'
        WHEN stock > 0 THEN 'POSITIVE'
        ELSE 'UNKNOWN'
    END as stock_status,
    created_at,
    updated_at
FROM inventory 
ORDER BY created_at DESC 
LIMIT 20";

$result = $conn->query($query);

echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #333; color: white;'>";
echo "<th>ID</th><th>Product Name</th><th>Stock Value</th><th>Stock Status</th><th>Will Show?</th><th>Created</th><th>Updated</th>";
echo "</tr>";

while ($row = $result->fetch_assoc()) {
    $will_show = ($row['stock'] > 0) ? 'YES' : 'NO';
    $bg_color = ($row['stock'] > 0) ? '#d4edda' : '#f8d7da';
    
    echo "<tr style='background: $bg_color;'>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td><strong>" . htmlspecialchars($row['product_name']) . "</strong></td>";
    echo "<td><strong style='font-size: 18px;'>" . ($row['stock'] === null ? 'NULL' : $row['stock']) . "</strong></td>";
    echo "<td>" . $row['stock_status'] . "</td>";
    echo "<td><strong>" . $will_show . "</strong></td>";
    echo "<td>" . $row['created_at'] . "</td>";
    echo "<td>" . $row['updated_at'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<h3>Testing intval() behavior:</h3>";
echo "<pre>";
echo "intval('') = " . intval('') . " (empty string becomes 0)\n";
echo "intval(null) = " . intval(null) . " (null becomes 0)\n";
echo "intval('0') = " . intval('0') . "\n";
echo "intval('5') = " . intval('5') . "\n";
echo "intval('abc') = " . intval('abc') . " (invalid string becomes 0)\n";
echo "</pre>";

echo "<div style='padding: 15px; background: #fff3cd; border: 1px solid #ffc107; margin-top: 20px;'>";
echo "<h3>⚠️ Possible Issues:</h3>";
echo "<ol>";
echo "<li><strong>Empty Form Submission:</strong> If the stock field is submitted empty, intval('') returns 0</li>";
echo "<li><strong>JavaScript/Form Validation:</strong> The 'required' attribute might not be working</li>";
echo "<li><strong>Browser Autofill:</strong> Some browsers might autofill with 0</li>";
echo "<li><strong>Copy-Paste Issue:</strong> If copying product data, stock might be set to 0</li>";
echo "</ol>";
echo "</div>";

echo "<div style='padding: 15px; background: #d1ecf1; border: 1px solid #17a2b8; margin-top: 20px;'>";
echo "<h3>🔧 Quick Fix Options:</h3>";
echo "<ol>";
echo "<li><a href='fix_zero_stock.php' style='color: #0056b3; font-weight: bold;'>Run Auto-Fix Script</a> - Set all stock=0 to stock=10</li>";
echo "<li>Tell groupmates to double-check stock field before saving</li>";
echo "<li>Add default value to stock field in the form</li>";
echo "</ol>";
echo "</div>";

$conn->close();
?>
