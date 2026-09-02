<?php
require_once('../auth_helper.php');
requireAdminAuth();
require_once("../../includes/db.php");

// Check database connection
if (!isDatabaseConnected()) {
    die("Database connection error. Please try again later.");
}

// Check if product ID is provided via POST
if (isset($_POST['id']) && is_numeric($_POST['id'])) {
    $id = intval($_POST['id']);

    // Delete the product using safe query execution
    $query = "DELETE FROM inventory WHERE id = ?";
    $params = [$id];
    $types = "i";
    
    $result = executeQuery($query, $params, $types);
    
    if ($result === true) {
        header("Location: inventory.php?status=deleted");
        exit;
    } else {
        error_log("Inventory delete error: Database query failed for ID $id");
        die("Error deleting product. Please try again.");
    }
} else {
    die("Invalid request. No product ID provided.");
}
