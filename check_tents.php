<?php
require_once 'includes/db.php';

echo "<h2>🔍 Checking Tent Products in Database</h2>";
echo "<style>table{border-collapse:collapse;width:100%;margin:20px 0;}th,td{border:1px solid #ddd;padding:12px;text-align:left;}th{background:#2e765e;color:white;}</style>";

if (!isDatabaseConnected()) {
    die("❌ Database connection failed!");
}

echo "✅ Database connected successfully<br><br>";

// Check all products with 'tent' in tag or name
$query = "SELECT id, product_name, tag, stock, image FROM inventory WHERE tag LIKE ? OR product_name LIKE ?";
$result = executeQuery($query, ['%tent%', '%tent%'], 'ss');

if ($result && $result->num_rows > 0) {
    echo "<h3>✅ Found " . $result->num_rows . " tent product(s):</h3>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th><th>Tag (Exact Value)</th><th>Stock</th><th>Image Path</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $stockColor = $row['stock'] > 0 ? 'green' : 'red';
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
        echo "<td><strong style='color:blue;'>" . htmlspecialchars($row['tag'] ?? 'NULL') . "</strong></td>";
        echo "<td style='color:$stockColor;font-weight:bold;'>" . $row['stock'] . "</td>";
        echo "<td style='font-size:0.9em;'>" . htmlspecialchars($row['image'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if ($result->num_rows > 0) {
        mysqli_data_seek($result, 0);
        $firstRow = $result->fetch_assoc();
        if ($firstRow['stock'] <= 0) {
            echo "<p style='background:#ffebee;padding:15px;border-left:4px solid #f44336;'><strong>⚠️ WARNING:</strong> Tent products exist but have ZERO stock! They won't display in ProductCatalog.php because of the WHERE stock > 0 condition.</p>";
        }
        if (strtolower($firstRow['tag']) !== $firstRow['tag']) {
            echo "<p style='background:#fff3cd;padding:15px;border-left:4px solid #ffc107;'><strong>⚠️ WARNING:</strong> Tag is capitalized ('" . htmlspecialchars($firstRow['tag']) . "'). The filter expects lowercase 'tents'.</p>";
        }
    }
} else {
    echo "<p style='background:#ffebee;padding:20px;border-left:4px solid #f44336;'><strong>❌ No tent products found!</strong><br>You need to add tent products through the admin panel.</p>";
}

echo "<hr style='margin:30px 0;'>";

// Check all unique tags
echo "<h3>📋 All Unique Tags in Database:</h3>";
$query2 = "SELECT DISTINCT tag, COUNT(*) as count FROM inventory WHERE tag IS NOT NULL AND tag != '' GROUP BY tag";
$result2 = executeQuery($query2);

if ($result2 && $result2->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>Tag (Exact Value)</th><th>Product Count</th><th>Expected Filter Value</th></tr>";
    while ($row = $result2->fetch_assoc()) {
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($row['tag']) . "</strong></td>";
        echo "<td>" . $row['count'] . "</td>";
        echo "<td style='color:green;'>" . strtolower($row['tag']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No tags found in database.</p>";
}

echo "<hr style='margin:30px 0;'>";

// Check products with stock > 0
echo "<h3>📦 All Products with Stock &gt; 0:</h3>";
$query3 = "SELECT id, product_name, tag, stock FROM inventory WHERE stock > 0 ORDER BY tag, product_name";
$result3 = executeQuery($query3);

if ($result3 && $result3->num_rows > 0) {
    echo "<p>Total products available: <strong style='color:green;'>" . $result3->num_rows . "</strong></p>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th><th>Tag</th><th>Stock</th></tr>";
    
    while ($row = $result3->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
        echo "<td><strong>" . htmlspecialchars($row['tag'] ?? 'N/A') . "</strong></td>";
        echo "<td>" . $row['stock'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='background:#ffebee;padding:20px;'><strong>❌ No products with stock found!</strong></p>";
}

echo "<hr style='margin:30px 0;'>";
echo "<p style='color:#666;'><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>If tent products don't exist → Add them via admin panel</li>";
echo "<li>If stock is 0 → Update stock in admin/inventory/inventory.php</li>";
echo "<li>If tag is capitalized → Either fix in database OR update JavaScript filter to be case-insensitive</li>";
echo "</ol>";
?>
