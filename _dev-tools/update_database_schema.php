<?php
/**
 * Database Update Script - Fix Order Items Table
 * Run this once to update the database schema
 */

require_once 'includes/db.php';

echo "<!DOCTYPE html>";
echo "<html><head><title>Database Update</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head><body class='container my-5'>";

echo "<h2>Database Schema Update</h2>";

if (!isDatabaseConnected()) {
    echo "<div class='alert alert-danger'>❌ Database connection failed!</div>";
    exit;
}

try {
    echo "<div class='alert alert-info'>🔄 Updating order_items table...</div>";
    
    // Check current table structure
    $result = $conn->query("DESCRIBE order_items");
    echo "<h5>Current Table Structure:</h5>";
    echo "<table class='table table-sm'>";
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
    
    // Update the product_id column to allow NULL
    echo "<div class='alert alert-warning'>⚙️ Modifying product_id column to allow NULL...</div>";
    
    // Try to drop the foreign key constraint first
    try {
        $conn->query("ALTER TABLE order_items DROP FOREIGN KEY order_items_ibfk_2");
        echo "<p>✅ Dropped existing foreign key constraint</p>";
    } catch (Exception $e) {
        echo "<p>⚠️ Foreign key constraint not found or already dropped: " . $e->getMessage() . "</p>";
    }
    
    // Modify the column to allow NULL
    $conn->query("ALTER TABLE order_items MODIFY COLUMN product_id int(11) DEFAULT NULL");
    echo "<p>✅ Modified product_id column to allow NULL</p>";
    
    // Re-add the foreign key constraint
    $conn->query("ALTER TABLE order_items ADD CONSTRAINT order_items_product_fk FOREIGN KEY (product_id) REFERENCES inventory(id) ON DELETE SET NULL");
    echo "<p>✅ Added new foreign key constraint with NULL support</p>";
    
    echo "<div class='alert alert-success'>✅ Database update completed successfully!</div>";
    
    // Show updated structure
    $result = $conn->query("DESCRIBE order_items");
    echo "<h5>Updated Table Structure:</h5>";
    echo "<table class='table table-sm'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $highlight = $row['Field'] === 'product_id' ? 'table-warning' : '';
        echo "<tr class='{$highlight}'>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>❌ Error: " . $e->getMessage() . "</div>";
}

echo "<div class='mt-4'>";
echo "<a href='test_orders.php' class='btn btn-primary'>Test Orders</a> ";
echo "<a href='test_store.php' class='btn btn-success'>Test Store</a> ";
echo "<a href='admin/orders.php' class='btn btn-info'>Admin Orders</a>";
echo "</div>";

echo "</body></html>";
?>