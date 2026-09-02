<?php
require_once('../auth_helper.php');
requireAdminAuth();
require_once("../../includes/db.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check database connection
    if (!isDatabaseConnected()) {
        die("Database connection error. Please try again later.");
    }
    
    $id = intval($_POST['id']);
    $product_name = trim($_POST['product_name']);
    $price = floatval($_POST['price']);
    
    // Validate and set stock - default to 1 if empty or 0
    $stock = isset($_POST['stock']) && $_POST['stock'] !== '' ? intval($_POST['stock']) : 1;
    if ($stock <= 0) {
        $stock = 1; // Force minimum stock of 1 to ensure visibility
    }
    
    $tag = !empty($_POST['tag']) ? trim($_POST['tag']) : NULL;
    $description = !empty($_POST['description']) ? trim($_POST['description']) : NULL;
    $specifications = !empty($_POST['specifications']) ? trim($_POST['specifications']) : NULL;
    $video_url = !empty($_POST['video_url']) ? trim($_POST['video_url']) : NULL;
    $dimensions = !empty($_POST['dimensions']) ? trim($_POST['dimensions']) : NULL;
    $weight = !empty($_POST['weight']) ? trim($_POST['weight']) : NULL;
    $category_details = !empty($_POST['category_details']) ? trim($_POST['category_details']) : NULL;

    // Handle main image upload
    $image_path = NULL;
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Create uploads folder if missing
        }

        $file_name = time() . "_" . basename($_FILES['image']['name']);
        $target_file = $target_dir . $file_name;

        // Validate file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_types)) {
            die("Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.");
        }

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = "uploads/" . $file_name; // store relative path
        } else {
            die("Error uploading main image.");
        }
    }

    // Handle additional images upload
    $additional_images_new = [];
    if (!empty($_FILES['additional_images']['name'][0])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        foreach ($_FILES['additional_images']['name'] as $key => $filename) {
            if ($_FILES['additional_images']['error'][$key] === UPLOAD_ERR_OK) {
                $file_name = time() . "_" . $key . "_" . basename($filename);
                $target_file = $target_dir . $file_name;

                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                if (in_array($file_extension, $allowed_types)) {
                    if (move_uploaded_file($_FILES['additional_images']['tmp_name'][$key], $target_file)) {
                        $additional_images_new[] = "uploads/" . $file_name;
                    }
                }
            }
        }
    }

    // Get existing additional images from database
    $existingImagesQuery = "SELECT additional_images FROM inventory WHERE id=?";
    $existingResult = executeQuery($existingImagesQuery, [$id], "i");
    $existing_images = [];
    
    if ($existingResult && $existingResult->num_rows > 0) {
        $row = $existingResult->fetch_assoc();
        if (!empty($row['additional_images'])) {
            $existing_images = json_decode($row['additional_images'], true) ?? [];
        }
    }

    // Merge existing and new images
    $all_additional_images = array_merge($existing_images, $additional_images_new);
    $additional_images_json = !empty($all_additional_images) ? json_encode($all_additional_images) : NULL;

    // Update database with all fields
    if ($image_path) {
        $query = "UPDATE inventory SET product_name=?, price=?, stock=?, tag=?, image=?, description=?, specifications=?, additional_images=?, video_url=?, dimensions=?, weight=?, category_details=? WHERE id=?";
        $params = [$product_name, $price, $stock, $tag, $image_path, $description, $specifications, $additional_images_json, $video_url, $dimensions, $weight, $category_details, $id];
        $types = "sdisssssssssi"; // s=string, d=double, i=integer (13 total: 9 strings, 1 double, 3 integers)
    } else {
        $query = "UPDATE inventory SET product_name=?, price=?, stock=?, tag=?, description=?, specifications=?, additional_images=?, video_url=?, dimensions=?, weight=?, category_details=? WHERE id=?";
        $params = [$product_name, $price, $stock, $tag, $description, $specifications, $additional_images_json, $video_url, $dimensions, $weight, $category_details, $id];
        $types = "sdissssssssi"; // s=string, d=double, i=integer (12 total: 8 strings, 1 double, 3 integers)
    }

    $result = executeQuery($query, $params, $types);
    
    if ($result === true) {
        header("Location: inventory.php?status=updated");
        exit;
    } else {
        error_log("Inventory update error: Database query failed for ID $id");
        die("Error updating product. Please try again.");
    }
} else {
    header("Location: inventory.php");
    exit;
}
