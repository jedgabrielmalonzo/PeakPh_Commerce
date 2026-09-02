<?php
// Generate complete database schema from current database
require_once 'includes/db.php';

if (!isDatabaseConnected()) {
    die("Database not connected\n");
}

$conn = $GLOBALS['conn'];

echo "-- =====================================================\n";
echo "-- PeakPH Commerce - Complete Database Schema Export\n";
echo "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
echo "-- =====================================================\n\n";

echo "CREATE DATABASE IF NOT EXISTS peakph_db;\n";
echo "USE peakph_db;\n\n";

// Get all tables
$tables_result = mysqli_query($conn, "SHOW TABLES");
$tables = [];

while ($row = mysqli_fetch_array($tables_result)) {
    $tables[] = $row[0];
}

// Generate CREATE TABLE statements for each table
foreach ($tables as $table) {
    echo "-- =====================================================\n";
    echo "-- TABLE: $table\n";
    echo "-- =====================================================\n";
    
    $create_result = mysqli_query($conn, "SHOW CREATE TABLE `$table`");
    if ($create_result) {
        $create_row = mysqli_fetch_array($create_result);
        echo $create_row[1] . ";\n\n";
    }
    
    // Show table info
    $info_result = mysqli_query($conn, "SELECT COUNT(*) as row_count FROM `$table`");
    if ($info_result) {
        $info_row = mysqli_fetch_assoc($info_result);
        echo "-- Rows: " . $info_row['row_count'] . "\n\n";
    }
}

echo "-- =====================================================\n";
echo "-- INDEXES AND CONSTRAINTS SUMMARY\n";
echo "-- =====================================================\n";

foreach ($tables as $table) {
    echo "-- Indexes for table: $table\n";
    $index_result = mysqli_query($conn, "SHOW INDEX FROM `$table`");
    if ($index_result) {
        while ($index_row = mysqli_fetch_assoc($index_result)) {
            if ($index_row['Key_name'] != 'PRIMARY') {
                echo "-- " . $index_row['Key_name'] . " on " . $index_row['Column_name'] . "\n";
            }
        }
    }
    echo "\n";
}

echo "-- =====================================================\n";
echo "-- SETUP COMPLETE\n";
echo "-- Total Tables: " . count($tables) . "\n";
echo "-- =====================================================\n";
?>