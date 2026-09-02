<?php
require_once('../auth_helper.php');
requireAdminAuth();
require_once("../../includes/db.php");

// Check if form submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check database connection
    if (!isDatabaseConnected()) {
        die("Database connection error. Please try again later.");
    }
    
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
            // Store the path relative to admin directory
            $image_path = "uploads/" . $file_name;
            
            // Log the file info for debugging
            error_log("Image upload successful:");
            error_log("Target file: " . $target_file);
            error_log("Stored path: " . $image_path);
            error_log("File exists: " . (file_exists($target_file) ? "Yes" : "No"));
        } else {
            error_log("Image upload failed: " . print_r($_FILES['image']['error'], true));
            die("Error uploading main image.");
        }
    }

    // Handle multiple additional images upload
    $additional_images = [];
    if (!empty($_FILES['additional_images']['name'][0])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        foreach ($_FILES['additional_images']['name'] as $key => $filename) {
            if ($_FILES['additional_images']['error'][$key] === UPLOAD_ERR_OK) {
                $file_name = time() . "_" . $key . "_" . basename($filename);
                $target_file = $target_dir . $file_name;

                // Validate file type
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                if (in_array($file_extension, $allowed_types)) {
                    if (move_uploaded_file($_FILES['additional_images']['tmp_name'][$key], $target_file)) {
                        $additional_images[] = "uploads/" . $file_name;
                    }
                }
            }
        }
    }
    
    // Convert additional images array to JSON
    $additional_images_json = !empty($additional_images) ? json_encode($additional_images) : NULL;

    // Insert into DB using safe query execution with new columns
    $query = "INSERT INTO inventory (product_name, price, stock, tag, image, description, specifications, additional_images, video_url, dimensions, weight, category_details) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $params = [$product_name, $price, $stock, $tag, $image_path, $description, $specifications, $additional_images_json, $video_url, $dimensions, $weight, $category_details];
    $types = "sdisssssssss"; // string, double, integer, string x9
    
    $result = executeQuery($query, $params, $types);
    
    if ($result === true) {
        header("Location: inventory.php?status=added");
        exit;
    } else {
        error_log("Inventory add error: Database query failed");
        die("Error adding product. Please try again.");
    }
} else {
    header("Location: inventory.php");
    exit;
}
