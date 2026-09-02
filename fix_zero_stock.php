<?php
require_once 'includes/db.php';

echo "<h2>Fix Zero Stock Products</h2>";

// Get products with stock = 0
$check_query = "SELECT id, product_name, stock FROM inventory WHERE stock = 0 OR stock IS NULL";
$result = $conn->query($check_query);

$affected_products = [];
while ($row = $result->fetch_assoc()) {
    $affected_products[] = $row;
}

if (empty($affected_products)) {
    echo "<p style='color: green; font-size: 18px;'>✓ No products with zero or null stock found!</p>";
    echo "<p>All products have stock > 0 and should be visible.</p>";
} else {
    echo "<p style='font-size: 18px;'>Found <strong>" . count($affected_products) . "</strong> products with zero or null stock:</p>";
    
    if (isset($_POST['confirm_fix'])) {
        // Apply the fix
        $update_query = "UPDATE inventory SET stock = 10 WHERE stock = 0 OR stock IS NULL";
        if ($conn->query($update_query)) {
            echo "<div style='padding: 20px; background: #d4edda; border: 2px solid #28a745; margin: 20px 0;'>";
            echo "<h3 style='color: #155724;'>✓ Success!</h3>";
            echo "<p>Updated <strong>" . $conn->affected_rows . "</strong> products. Stock set to 10 for all previously hidden products.</p>";
            echo "<p><a href='ProductCatalog.php' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>View Product Catalog</a></p>";
            echo "</div>";
            
            // Show what was updated
            echo "<h4>Updated Products:</h4>";
            echo "<ul>";
            foreach ($affected_products as $product) {
                echo "<li><strong>" . htmlspecialchars($product['product_name']) . "</strong> (ID: " . $product['id'] . ") - Stock changed from " . ($product['stock'] ?? 'NULL') . " to 10</li>";
            }
            echo "</ul>";
        } else {
            echo "<div style='padding: 20px; background: #f8d7da; border: 2px solid #dc3545;'>";
            echo "<h3>Error!</h3>";
            echo "<p>Failed to update products: " . $conn->error . "</p>";
            echo "</div>";
        }
    } else {
        // Show preview and confirmation form
        echo "<table border='1' cellpadding='8' style='border-collapse: collapse; margin: 20px 0;'>";
        echo "<tr style='background: #333; color: white;'><th>ID</th><th>Product Name</th><th>Current Stock</th></tr>";
        foreach ($affected_products as $product) {
            echo "<tr>";
            echo "<td>" . $product['id'] . "</td>";
            echo "<td>" . htmlspecialchars($product['product_name']) . "</td>";
            echo "<td><strong style='color: red;'>" . ($product['stock'] ?? 'NULL') . "</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<div style='padding: 20px; background: #fff3cd; border: 2px solid #ffc107;'>";
        echo "<h3>⚠️ Confirm Action</h3>";
        echo "<p>This will update all products listed above and set their stock to <strong>10</strong>.</p>";
        echo "<form method='POST' style='margin-top: 20px;'>";
        echo "<button type='submit' name='confirm_fix' style='padding: 15px 30px; background: #28a745; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;'>✓ Yes, Fix These Products</button> ";
        echo "<a href='diagnose_products.php' style='padding: 15px 30px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px; display: inline-block;'>Cancel</a>";
        echo "</form>";
        echo "</div>";
    }
}

echo "<hr style='margin: 30px 0;'>";
echo "<p><a href='diagnose_products.php'>← Back to Diagnostics</a></p>";

$conn->close();
?>
