<?php
session_start();

// Add a test item to cart for checkout testing
$_SESSION['cart'] = [
    [
        'id' => 1,
        'name' => 'Test Product',
        'price' => 100.00,
        'quantity' => 1,
        'image' => 'test.jpg'
    ]
];

echo "Test cart item added!<br>";
echo "<a href='checkout.php'>Go to Checkout</a>";
?>
