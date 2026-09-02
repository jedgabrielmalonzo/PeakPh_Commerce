<?php
require_once 'includes/db.php';

if (isDatabaseConnected()) {
    echo "=== Payments Table Structure ===\n";
    $result = mysqli_query($GLOBALS['conn'], 'DESCRIBE payments');
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo $row['Field'] . ' - ' . $row['Type'] . "\n";
        }
    } else {
        echo "Payments table does not exist\n";
    }
} else {
    echo "Database not connected\n";
}
?>