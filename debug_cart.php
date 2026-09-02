<?php
session_start();
echo "<pre>";
echo "Cart Contents:\n";
print_r($_SESSION['cart']);
echo "\n\nChecking Physical Files:\n";
foreach ($_SESSION['cart'] as $item) {
    echo "\nProduct: {$item['name']}\n";
    echo "Image Path: {$item['image']}\n";
    $physical_path = $_SERVER['DOCUMENT_ROOT'] . $item['image'];
    echo "Physical Path: {$physical_path}\n";
    echo "File Exists: " . (file_exists($physical_path) ? "Yes" : "No") . "\n";
    if (!file_exists($physical_path)) {
        echo "Directory contents of parent folder:\n";
        $parent_dir = dirname($physical_path);
        if (is_dir($parent_dir)) {
            $files = scandir($parent_dir);
            print_r($files);
        } else {
            echo "Parent directory does not exist: {$parent_dir}\n";
        }
    }
}
?>