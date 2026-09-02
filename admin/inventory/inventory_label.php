<?php
require_once('../auth_helper.php');
requireAdminAuth();
require_once("../../includes/db.php");

// Check database connection
if (!isDatabaseConnected()) {
    die("Database connection error. Please try again later.");
}

// Update label
if (isset($_POST['id'], $_POST['label'])) {
    $id = intval($_POST['id']);
    $label = trim($_POST['label']);

    // Update label using safe query execution
    $query = "UPDATE inventory SET label = ? WHERE id = ?";
    $params = [$label, $id];
    $types = "si";
    
    $result = executeQuery($query, $params, $types);
    
    if ($result === true) {
        header("Location: inventory.php?status=label-updated");
        exit;
    } else {
        error_log("Inventory label update error: Database query failed for ID $id");
        die("Error updating label. Please try again.");
    }
} else {
    die("Invalid request. Missing required data.");
}
?>