<?php
require_once __DIR__ . '/../../includes/db.php';

// Dynamic arrivals from inventory with "New Arrival" label
$arrivals = array();

if (isDatabaseConnected()) {
    try {
        $query = "SELECT id, product_name, price, image, tag, stock FROM inventory WHERE label = 'New Arrival' AND stock > 0 ORDER BY created_at DESC LIMIT 8";
        $result = executeQuery($query);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Fix image path
                $image_path = 'Assets/Gallery_Images/placeholder.jpg';
                if (!empty($row['image'])) {
                    if (file_exists('admin/' . $row['image'])) {
                        $image_path = 'admin/' . $row['image'];
                    } elseif (file_exists($row['image'])) {
                        $image_path = $row['image'];
                    }
                }
                
                $arrivals[] = array(
                    'link' => 'ProductView.php?id=' . $row['id'],
                    'image' => $image_path,
                    'alt' => $row['product_name'],
                    'name' => $row['product_name'],
                    'price' => number_format($row['price'], 0)
                );
            }
        }
    } catch (Exception $e) {
        error_log('New Arrivals data error: ' . $e->getMessage());
    }
}

// Fallback static arrivals if no database products or connection fails
if (empty($arrivals)) {
    $arrivals = array(
        array(
            'link' => 'ProductCatalog.php',
            'image' => 'Assets/Gallery_Images/TravelBootsSample.png',
            'alt' => 'Travelling Boots',
            'name' => 'Travelling Boots',
            'price' => '1250',
        ),
        array(
            'link' => 'ProductCatalog.php',
            'image' => 'Assets/Gallery_Images/CookingGearSample.png',
            'alt' => 'Cooking Gear',
            'name' => 'Cooking Gear',
            'price' => '1200',
        ),
        array(
            'link' => 'ProductCatalog.php',
            'image' => 'Assets/Gallery_Images/TentSample.jpg',
            'alt' => 'Camping Tent',
            'name' => 'Camping Tent',
            'price' => '2500',
        ),
        array(
            'link' => 'ProductCatalog.php',
            'image' => 'Assets/Gallery_Images/HikingBackpackSample.png',
            'alt' => 'Hiking Backpack',
            'name' => 'Hiking Backpack',
            'price' => '1800',
        ),
    );
}

return array(
    'arrivals' => $arrivals
);
