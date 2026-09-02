<?php
require_once '../includes/user_auth.php';
require_once '../includes/db.php';

requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$current_user = getCurrentUser();
$user_id = $current_user['id'];

// Get form data
$phone = trim($_POST['phone'] ?? '');
$shipping_address = trim($_POST['shipping_address'] ?? '');
$shipping_address_2 = trim($_POST['shipping_address_2'] ?? '');
$shipping_city = trim($_POST['shipping_city'] ?? '');
$shipping_province = trim($_POST['shipping_province'] ?? '');
$shipping_postal_code = trim($_POST['shipping_postal_code'] ?? '');
$shipping_country = trim($_POST['shipping_country'] ?? 'Philippines');

$map_latitude = !empty($_POST['map_latitude']) ? floatval($_POST['map_latitude']) : null;
$map_longitude = !empty($_POST['map_longitude']) ? floatval($_POST['map_longitude']) : null;
$map_address = trim($_POST['map_address'] ?? '');

$billing_same_as_shipping = isset($_POST['same_as_shipping']) ? 1 : 0;
$billing_address = trim($_POST['billing_address'] ?? '');
$billing_address_2 = trim($_POST['billing_address_2'] ?? '');
$billing_city = trim($_POST['billing_city'] ?? '');
$billing_province = trim($_POST['billing_province'] ?? '');
$billing_postal_code = trim($_POST['billing_postal_code'] ?? '');
$billing_country = trim($_POST['billing_country'] ?? 'Philippines');

// Validate required fields
if (empty($phone) || empty($shipping_address) || empty($shipping_city) || empty($shipping_province) || empty($shipping_postal_code)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
    exit;
}

try {
    // Check if profile exists
    $check_query = "SELECT id FROM user_profiles WHERE user_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing profile
        $update_query = "
            UPDATE user_profiles SET 
                phone = ?,
                shipping_address = ?,
                shipping_address_2 = ?,
                shipping_city = ?,
                shipping_province = ?,
                shipping_postal_code = ?,
                shipping_country = ?,
                map_latitude = ?,
                map_longitude = ?,
                map_address = ?,
                billing_same_as_shipping = ?,
                billing_address = ?,
                billing_address_2 = ?,
                billing_city = ?,
                billing_province = ?,
                billing_postal_code = ?,
                billing_country = ?,
                updated_at = NOW()
            WHERE user_id = ?
        ";
        
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param(
            "ssssssddssisssssi",
            $phone,
            $shipping_address,
            $shipping_address_2,
            $shipping_city,
            $shipping_province,
            $shipping_postal_code,
            $shipping_country,
            $map_latitude,
            $map_longitude,
            $map_address,
            $billing_same_as_shipping,
            $billing_address,
            $billing_address_2,
            $billing_city,
            $billing_province,
            $billing_postal_code,
            $billing_country,
            $user_id
        );
    } else {
        // Insert new profile
        $insert_query = "
            INSERT INTO user_profiles (
                user_id, phone, shipping_address, shipping_address_2, 
                shipping_city, shipping_province, shipping_postal_code, shipping_country,
                map_latitude, map_longitude, map_address,
                billing_same_as_shipping, billing_address, billing_address_2,
                billing_city, billing_province, billing_postal_code, billing_country
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param(
            "isssssssddsissssss",
            $user_id,
            $phone,
            $shipping_address,
            $shipping_address_2,
            $shipping_city,
            $shipping_province,
            $shipping_postal_code,
            $shipping_country,
            $map_latitude,
            $map_longitude,
            $map_address,
            $billing_same_as_shipping,
            $billing_address,
            $billing_address_2,
            $billing_city,
            $billing_province,
            $billing_postal_code,
            $billing_country
        );
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile: ' . $stmt->error]);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
