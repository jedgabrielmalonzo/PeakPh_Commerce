<?php
require_once 'includes/user_auth.php';
require_once 'includes/db.php';

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Calculate total items in cart
$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['quantity'];
}

// Get product ID from URL parameter
$product_id = isset($_GET['id']) ? $_GET['id'] : null;
$product = null;
$related_products = [];

if ($product_id && isDatabaseConnected()) {
    // Get main product from database
    $query = "SELECT * FROM inventory WHERE id = ? AND stock > 0";
    $result = executeQuery($query, [$product_id]);
    
    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
        
        // Fix image path
        if (!empty($product['image'])) {
            if (file_exists('admin/' . $product['image'])) {
                $product['image'] = 'admin/' . $product['image'];
            } elseif (!file_exists($product['image'])) {
                $product['image'] = 'Assets/placeholder.svg';
            }
        } else {
            $product['image'] = 'Assets/placeholder.svg';
        }
        
        // Get related products (same category, excluding current product)
        $related_query = "SELECT id, product_name, price, image, stock FROM inventory WHERE tag = ? AND id != ? AND stock > 0 LIMIT 4";
        $related_result = executeQuery($related_query, [$product['tag'], $product_id]);
        
        if ($related_result) {
            while ($row = $related_result->fetch_assoc()) {
                // Fix image path for related products
                if (!empty($row['image']) && file_exists('admin/' . $row['image'])) {
                    $row['image'] = 'admin/' . $row['image'];
                } else {
                    $row['image'] = 'Assets/placeholder.svg';
                }
                $related_products[] = $row;
            }
        }
    }
}

// Fallback to demo product if not found or no ID provided
if (!$product) {
    $product = [
        'id' => 'demo_1',
        'product_name' => 'Large Camping Folding Armchair - XL',
        'price' => 1290.00,
        'stock' => 15,
        'tag' => 'camping',
        'label' => 'Popular',
        'image' => 'Assets/Gallery_Images/TentSample.jpg',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Demo related products
    $related_products = [
        ['id' => 'demo_2', 'product_name' => '4-Person Camping Tent', 'price' => 1200.00, 'image' => 'Assets/Gallery_Images/TentSample.jpg', 'stock' => 25],
        ['id' => 'demo_3', 'product_name' => 'Portable Cooking Set', 'price' => 750.00, 'image' => 'Assets/Gallery_Images/CookingGearSample.png', 'stock' => 30],
        ['id' => 'demo_4', 'product_name' => 'Camping Stove', 'price' => 450.00, 'image' => 'Assets/Gallery_Images/Camping Stove Sample.png', 'stock' => 40]
    ];
}

// ==========================================
// CUSTOMER REVIEWS BACKEND LOGIC
// ==========================================
$review_error = '';

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $rating = intval($_POST['rating'] ?? 5);
    $user_name = trim($_POST['user_name'] ?? '');
    $review_title = trim($_POST['review_title'] ?? '');
    $review_text = trim($_POST['review_text'] ?? '');
    $user_id = $_SESSION['user_id'] ?? null;

    if (empty($user_name)) {
        if (!empty($_SESSION['user_name'])) {
            $user_name = $_SESSION['user_name'];
        } else {
            $user_name = 'Outdoor Adventurer';
        }
    }

    if ($rating < 1 || $rating > 5) {
        $review_error = 'Please select a rating between 1 and 5 stars.';
    } elseif (empty($review_text)) {
        $review_error = 'Please write a review comment.';
    } else {
        if (isDatabaseConnected()) {
            try {
                $conn->query("CREATE TABLE IF NOT EXISTS product_reviews (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    product_id VARCHAR(50) NOT NULL,
                    user_id INT NULL,
                    user_name VARCHAR(150) NOT NULL,
                    rating TINYINT NOT NULL,
                    review_title VARCHAR(255) NULL,
                    review_text TEXT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX (product_id)
                )");

                $stmt = $conn->prepare("INSERT INTO product_reviews (product_id, user_id, user_name, rating, review_title, review_text) VALUES (?, ?, ?, ?, ?, ?)");
                $pid_str = (string)($product['id'] ?? $product_id);
                $stmt->bind_param("sisiss", $pid_str, $user_id, $user_name, $rating, $review_title, $review_text);
                $stmt->execute();

                header("Location: ProductView.php?id=" . urlencode($pid_str) . "&review_submitted=1#reviewsSection");
                exit;
            } catch (Exception $e) {
                $review_error = 'Failed to submit review: ' . $e->getMessage();
            }
        } else {
            $review_error = 'Database connection error. Please try again.';
        }
    }
}

// Fetch Reviews & Calculate Statistics
$reviews = [];
$reviews_count = 0;
$avg_rating = 0;
$rating_breakdown = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

if (isDatabaseConnected() && !empty($product['id'])) {
    try {
        $conn->query("CREATE TABLE IF NOT EXISTS product_reviews (
            id INT PRIMARY KEY AUTO_INCREMENT,
            product_id VARCHAR(50) NOT NULL,
            user_id INT NULL,
            user_name VARCHAR(150) NOT NULL,
            rating TINYINT NOT NULL,
            review_title VARCHAR(255) NULL,
            review_text TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (product_id)
        )");

        $stmt = $conn->prepare("SELECT * FROM product_reviews WHERE product_id = ? ORDER BY created_at DESC");
        $pid_str = (string)$product['id'];
        $stmt->bind_param("s", $pid_str);
        $stmt->execute();
        $r_res = $stmt->get_result();

        $total_score = 0;
        if ($r_res) {
            while ($row = $r_res->fetch_assoc()) {
                $reviews[] = $row;
                $star = max(1, min(5, intval($row['rating'])));
                $rating_breakdown[$star]++;
                $total_score += $star;
            }
        }

        $reviews_count = count($reviews);
        if ($reviews_count > 0) {
            $avg_rating = round($total_score / $reviews_count, 1);
        }
    } catch (Exception $e) {
        error_log("Reviews fetch error: " . $e->getMessage());
    }
}

// Fallback sample reviews if no reviews submitted yet
if ($reviews_count === 0 && !isset($_GET['review_submitted'])) {
    $reviews = [
        [
            'id' => 'sample_1',
            'user_name' => 'Marc Alcantara',
            'rating' => 5,
            'review_title' => 'Tested on Mt. Pulag - Excellent Quality!',
            'review_text' => 'Brought this along for our 3-day trek. Super reliable, lightweight, and very durable. Highly recommended for any hiker in the Philippines!',
            'created_at' => date('Y-m-d H:i:s', strtotime('-3 days'))
        ],
        [
            'id' => 'sample_2',
            'user_name' => 'Bea Ramos',
            'rating' => 4,
            'review_title' => 'Very satisfied with the purchase',
            'review_text' => 'Fast delivery from PeakPH. Product was packed securely with bubble wrap. Tried it out last weekend and it works as advertised.',
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 week'))
        ]
    ];
    $reviews_count = count($reviews);
    $rating_breakdown[5] = 1;
    $rating_breakdown[4] = 1;
    $avg_rating = 4.5;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PeakPH - Product Detail</title>
  
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="Assets/Carousel_Picts/Logo.png" />

  <!-- Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

  <!-- Custom CSS -->
  <link rel="stylesheet" href="Css/Global.css">
  <link rel="stylesheet" href="Css/productview.css">

  <!-- Google Identity Services -->
  <script src="https://accounts.google.com/gsi/client" async defer></script>
  
  <style>
    /* Wishlist Styles */
    .wishlist-link {
      position: relative;
      color: white;
      text-decoration: none;
      font-size: 1.5rem;
      transition: color 0.3s;
    }
    
    .wishlist-link:hover {
      color: #ffd700;
    }
    
    .wishlist-count {
      position: absolute;
      top: -8px;
      right: -10px;
      background: #e74c3c;
      color: white;
      border-radius: 50%;
      width: 20px;
      height: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.7rem;
      font-weight: bold;
    }
    
    .wishlist-modal {
      display: none;
      position: fixed;
      top: 0;
      right: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 10000;
      animation: fadeIn 0.3s ease;
    }
    
    .wishlist-modal.active {
      display: block;
    }
    
    .wishlist-modal-content {
      position: fixed;
      right: 0;
      top: 0;
      height: 100%;
      width: 450px;
      max-width: 90%;
      background: white;
      box-shadow: -4px 0 20px rgba(0, 0, 0, 0.2);
      animation: slideInRight 0.3s ease;
      display: flex;
      flex-direction: column;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    
    @keyframes slideInRight {
      from { transform: translateX(100%); }
      to { transform: translateX(0); }
    }
    
    .wishlist-header {
      background: linear-gradient(135deg, #2e765e, #3da180);
      color: white;
      padding: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .wishlist-header h2 {
      margin: 0;
      font-size: 1.5rem;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .close-wishlist {
      background: none;
      border: none;
      color: white;
      font-size: 2rem;
      cursor: pointer;
      line-height: 1;
      transition: transform 0.2s;
    }
    
    .close-wishlist:hover {
      transform: scale(1.2);
    }
    
    .wishlist-body {
      flex: 1;
      overflow-y: auto;
      padding: 20px;
    }
    
    .wishlist-empty {
      text-align: center;
      padding: 60px 20px;
      color: #999;
    }
    
    .wishlist-empty i {
      font-size: 4rem;
      color: #ddd;
      margin-bottom: 20px;
    }
    
    .wishlist-item {
      display: flex;
      gap: 15px;
      padding: 15px;
      border: 1px solid #eee;
      border-radius: 12px;
      margin-bottom: 15px;
      transition: all 0.3s;
      background: white;
    }
    
    .wishlist-item:hover {
      box-shadow: 0 4px 12px rgba(46, 118, 94, 0.1);
      border-color: #2e765e;
    }
    
    .wishlist-item-image {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 8px;
      background: #f8f8f8;
    }
    
    .wishlist-item-details {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 5px;
    }
    
    .wishlist-item-name {
      font-weight: 600;
      color: #333;
      font-size: 0.95rem;
      line-height: 1.3;
    }
    
    .wishlist-item-price {
      color: #2e765e;
      font-weight: 700;
      font-size: 1.1rem;
    }
    
    .wishlist-item-actions {
      display: flex;
      gap: 8px;
      margin-top: 8px;
    }
    
    .wishlist-add-to-cart {
      background: linear-gradient(135deg, #2e765e, #3da180);
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 0.85rem;
      font-weight: 500;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    
    .wishlist-add-to-cart:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(46, 118, 94, 0.3);
    }
    
    .wishlist-remove {
      background: #f8f9fa;
      color: #e74c3c;
      border: 1px solid #e74c3c;
      padding: 8px 16px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 0.85rem;
      font-weight: 500;
      transition: all 0.3s;
    }
    
    .wishlist-remove:hover {
      background: #e74c3c;
      color: white;
    }
    
    @media (max-width: 768px) {
      .wishlist-modal-content {
        width: 100%;
        max-width: 100%;
      }
    }

    /* Product Specifications Styles */
    .product-specifications {
      margin-top: 30px;
      padding: 20px;
      background: #f8f9fa;
      border-radius: 12px;
      border-left: 4px solid #2e765e;
    }

    .product-specifications h3 {
      color: #2e765e;
      margin-bottom: 15px;
      font-size: 1.3rem;
    }

    .specs-table {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .spec-row {
      display: flex;
      padding: 10px;
      background: white;
      border-radius: 8px;
      align-items: center;
    }

    .spec-label {
      font-weight: 600;
      color: #2e765e;
      min-width: 150px;
    }

    .spec-value {
      color: #333;
    }

    /* Video Modal Styles */
    .video-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.9);
      z-index: 10001;
      animation: fadeIn 0.3s ease;
    }

    .video-modal.active {
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .video-modal-content {
      position: relative;
      width: 90%;
      max-width: 900px;
      background: #000;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
    }

    .close-video {
      position: absolute;
      top: -40px;
      right: 0;
      background: none;
      border: none;
      color: white;
      font-size: 3rem;
      cursor: pointer;
      z-index: 10;
      transition: transform 0.2s;
    }

    .close-video:hover {
      transform: scale(1.2);
    }

    .video-container {
      position: relative;
      width: 100%;
      padding-bottom: 56.25%; /* 16:9 aspect ratio */
      height: 0;
      overflow: hidden;
    }

    .video-container iframe,
    .video-container video {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
    }

    .view-video-btn {
      position: absolute;
      bottom: 20px;
      right: 20px;
      background: linear-gradient(135deg, #2e765e, #3da180);
      color: white;
      border: none;
      padding: 12px 24px;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: all 0.3s;
      box-shadow: 0 4px 12px rgba(46, 118, 94, 0.3);
    }

    .view-video-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(46, 118, 94, 0.4);
    }

    .view-video-btn i {
      font-size: 1.5rem;
    }

    .main-image {
      position: relative;
    }

    .thumbnail-images {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      justify-content: center;
    }

    .thumbnail {
      cursor: pointer;
      border: 3px solid transparent;
      transition: all 0.3s;
    }

    .thumbnail.active {
      border-color: #2e765e;
      box-shadow: 0 4px 12px rgba(46, 118, 94, 0.3);
    }

    .thumbnail:hover {
      border-color: #3da180;
      transform: scale(1.05);
    }

    /* ==========================================================================
       CUSTOMER REVIEWS SECTION
       ========================================================================== */
    .reviews-section {
      max-width: 1200px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .reviews-section-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      padding-bottom: 15px;
      border-bottom: 2px solid #eef2f5;
    }

    .reviews-section-header h2 {
      color: #2e765e;
      font-size: 1.8rem;
      font-weight: 700;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .review-alert {
      padding: 14px 20px;
      border-radius: 8px;
      margin-bottom: 25px;
      font-size: 0.95rem;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .review-alert.success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .review-alert.error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .reviews-overview-card {
      background: white;
      border-radius: 12px;
      padding: 30px;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
      margin-bottom: 30px;
      display: grid;
      grid-template-columns: 240px 1fr 240px;
      gap: 30px;
      align-items: center;
    }

    .rating-summary-box {
      text-align: center;
      padding-right: 20px;
      border-right: 1px solid #eee;
    }

    .rating-big-number {
      font-size: 3.5rem;
      font-weight: 800;
      color: #2e765e;
      line-height: 1;
      margin-bottom: 8px;
    }

    .rating-stars-gold {
      color: #f39c12;
      font-size: 1.3rem;
      margin-bottom: 6px;
    }

    .rating-total-count {
      color: #777;
      font-size: 0.9rem;
    }

    .rating-bars-container {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .rating-bar-row {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 0.88rem;
    }

    .rating-bar-label {
      width: 45px;
      color: #555;
      font-weight: 500;
    }

    .rating-bar-track {
      flex: 1;
      height: 10px;
      background: #eaeff2;
      border-radius: 10px;
      overflow: hidden;
    }

    .rating-bar-fill {
      height: 100%;
      background: linear-gradient(90deg, #2e765e, #3da180);
      border-radius: 10px;
      transition: width 0.6s ease;
    }

    .rating-bar-percent {
      width: 38px;
      text-align: right;
      color: #777;
      font-size: 0.85rem;
    }

    .rating-cta-box {
      text-align: center;
      padding-left: 20px;
      border-left: 1px solid #eee;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }

    .rating-cta-box p {
      color: #666;
      font-size: 0.9rem;
      margin: 0;
    }

    .write-review-btn {
      background: linear-gradient(135deg, #2e765e, #3da180);
      color: white;
      border: none;
      padding: 12px 24px;
      border-radius: 8px;
      font-weight: 600;
      font-size: 0.95rem;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.3s;
      box-shadow: 0 4px 12px rgba(46, 118, 94, 0.25);
    }

    .write-review-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(46, 118, 94, 0.35);
    }

    /* Review Form Card */
    .review-form-card {
      display: none;
      background: white;
      border-radius: 12px;
      padding: 30px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      margin-bottom: 35px;
      border: 1px solid #e1e8ed;
      animation: fadeIn 0.3s ease;
    }

    .review-form-card.active {
      display: block;
    }

    .review-form-card h3 {
      color: #2e765e;
      font-size: 1.4rem;
      font-weight: 600;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .star-rating-picker {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 18px;
    }

    .star-rating-picker label {
      font-weight: 600;
      color: #333;
      margin-right: 10px;
    }

    .stars-select {
      display: inline-flex;
      gap: 6px;
      cursor: pointer;
    }

    .stars-select i {
      font-size: 1.6rem;
      color: #ccc;
      transition: color 0.2s, transform 0.2s;
    }

    .stars-select i.active,
    .stars-select i.hover {
      color: #f39c12;
      transform: scale(1.1);
    }

    .rating-feedback-text {
      color: #2e765e;
      font-weight: 600;
      font-size: 0.95rem;
      margin-left: 10px;
    }

    .review-form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-bottom: 18px;
    }

    .review-form-group {
      display: flex;
      flex-direction: column;
      gap: 6px;
      margin-bottom: 18px;
    }

    .review-form-group label {
      font-size: 0.9rem;
      font-weight: 600;
      color: #444;
    }

    .review-form-group input,
    .review-form-group textarea {
      padding: 11px 15px;
      border: 1px solid #ced4da;
      border-radius: 8px;
      font-family: inherit;
      font-size: 0.95rem;
      outline: none;
      transition: border-color 0.2s;
    }

    .review-form-group input:focus,
    .review-form-group textarea:focus {
      border-color: #2e765e;
      box-shadow: 0 0 0 3px rgba(46, 118, 94, 0.15);
    }

    .review-form-actions {
      display: flex;
      gap: 12px;
      justify-content: flex-end;
    }

    .btn-secondary {
      background: #f1f3f5;
      color: #495057;
      border: 1px solid #ced4da;
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s;
    }

    .btn-secondary:hover {
      background: #e9ecef;
    }

    /* Review Cards List */
    .reviews-list {
      display: flex;
      flex-direction: column;
      gap: 18px;
    }

    .review-card {
      background: white;
      border-radius: 12px;
      padding: 24px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
      border: 1px solid #edf2f7;
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .review-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.07);
    }

    .review-card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 12px;
    }

    .reviewer-profile {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .reviewer-avatar {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      background: linear-gradient(135deg, #2e765e, #3da180);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 1.1rem;
    }

    .reviewer-meta h4 {
      margin: 0;
      font-size: 1rem;
      font-weight: 600;
      color: #333;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .verified-badge {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      background: #e6f4ea;
      color: #137333;
      font-size: 0.72rem;
      font-weight: 600;
      padding: 2px 8px;
      border-radius: 20px;
    }

    .review-date {
      color: #888;
      font-size: 0.85rem;
    }

    .review-card-stars {
      color: #f39c12;
      font-size: 1.05rem;
      margin-bottom: 8px;
    }

    .review-card-title {
      font-size: 1.05rem;
      font-weight: 700;
      color: #222;
      margin: 0 0 8px 0;
    }

    .review-card-text {
      color: #555;
      font-size: 0.95rem;
      line-height: 1.6;
      margin: 0;
    }

    .empty-reviews-card {
      background: white;
      border-radius: 12px;
      padding: 50px 20px;
      text-align: center;
      border: 1px dashed #ced4da;
    }

    .empty-reviews-card i {
      font-size: 3.5rem;
      color: #b0bec5;
      margin-bottom: 15px;
      display: block;
    }

    .empty-reviews-card h3 {
      font-size: 1.3rem;
      color: #333;
      margin-bottom: 8px;
    }

    .empty-reviews-card p {
      color: #777;
      margin-bottom: 20px;
    }

    @media (max-width: 900px) {
      .reviews-overview-card {
        grid-template-columns: 1fr;
        text-align: center;
        gap: 24px;
      }

      .rating-summary-box {
        padding-right: 0;
        border-right: none;
        border-bottom: 1px solid #eee;
        padding-bottom: 20px;
      }

      .rating-cta-box {
        padding-left: 0;
        border-left: none;
        border-top: 1px solid #eee;
        padding-top: 20px;
      }

      .review-form-row {
        grid-template-columns: 1fr;
        gap: 0;
      }
    }
  </style>
</head>

<body>
  <!-- HEADER -->
  <header>
    <div class="top-navbar">
      <div class="brand">
        <a href="index.php" class="logo-btn">
          <img src="Assets/Carousel_Picts/Logo.png" alt="Brand Logo" />
        </a>
      </div>

      <div class="search-wrapper">
        <i class="bi bi-search"></i>
        <input type="search" id="headerSearch" placeholder="Search products..." />
      </div>

      <div class="top-icons">
        <?php echo getAuthNavigationHTML(); ?>
        <a href="#" class="wishlist-link" onclick="toggleWishlistModal(); return false;">
          <i class="bi bi-heart">
            <span class="wishlist-count">0</span>
          </i>
        </a>
        <a href="cart.php" class="cart-link">
          <i class="bi bi-cart">
            <span class="cart-count"><?php echo $cart_count; ?></span>
          </i>
        </a>
      </div>
    </div>

    <!-- BOTTOM NAVBAR -->
    <div class="bottom-navbar">
      <nav>
        <a href="ProductCatalog.php">Shop</a>
        <a href="#contact">Contact Us</a>
        <a href="#deals" class="best-deals">Best Deals</a>
        <a href="#about">About us</a>
      </nav>
    </div>
  </header>

  <!-- MAIN PRODUCT DETAIL -->
  <main>
    <div class="product-container">
      <div class="product-gallery">
        <div class="main-image">
          <img id="mainProductImage" src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" />
          
          <?php if (!empty($product['video_url'])): ?>
            <button class="view-video-btn" onclick="showVideoModal()">
              <i class="bi bi-play-circle"></i> Watch Video
            </button>
          <?php endif; ?>
        </div>
        <div class="thumbnail-images">
          <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="Main View" class="thumbnail active" onclick="changeImage(this.src)" />
          
          <?php 
          // Display additional images if available
          if (!empty($product['additional_images'])) {
              $additional_images = json_decode($product['additional_images'], true);
              if (is_array($additional_images)) {
                  foreach ($additional_images as $index => $img_path) {
                      $full_path = (strpos($img_path, 'admin/') === 0) ? $img_path : 'admin/' . $img_path;
                      echo '<img src="' . htmlspecialchars($full_path) . '" alt="View ' . ($index + 2) . '" class="thumbnail" onclick="changeImage(this.src)" />';
                  }
              }
          }
          ?>
        </div>
      </div>

      <div class="product-info">
        <nav class="breadcrumb">
          <a href="index.php">Home</a> > 
          <a href="ProductCatalog.php">Products</a> > 
          <span><?php echo htmlspecialchars($product['product_name']); ?></span>
        </nav>
        
        <h1><?php echo htmlspecialchars($product['product_name']); ?></h1>
        
        <div class="product-rating">
          <span class="stars" style="color: #f39c12; font-size: 1.15rem;">
            <?php 
            for ($s = 1; $s <= 5; $s++) {
                if ($s <= floor($avg_rating)) {
                    echo '<i class="bi bi-star-fill"></i>';
                } elseif ($s - $avg_rating < 1 && $s - $avg_rating > 0) {
                    echo '<i class="bi bi-star-half"></i>';
                } else {
                    echo '<i class="bi bi-star" style="color: #ccc;"></i>';
                }
            }
            ?>
          </span>
          <span class="rating-text">(<?php echo number_format($avg_rating, 1); ?> / 5)</span>
          <a href="#reviewsSection" class="review-count" style="color: #2e765e; text-decoration: none;">- <?php echo $reviews_count; ?> <?php echo $reviews_count === 1 ? 'review' : 'reviews'; ?></a>
        </div>
        
        <div class="price-section">
          <span class="current-price">₱<?php echo number_format($product['price'], 2); ?></span>
          <?php if (rand(0, 1)): ?>
            <span class="old-price">₱<?php echo number_format($product['price'] * 1.3, 2); ?></span>
            <span class="discount">-<?php echo rand(15, 35); ?>%</span>
          <?php endif; ?>
        </div>

        <div class="product-meta">
          <p><strong>Category:</strong> <?php echo ucfirst($product['tag'] ?? 'General'); ?></p>
          <p><strong>SKU:</strong> PK-<?php echo str_pad($product['id'], 6, '0', STR_PAD_LEFT); ?></p>
          <p class="stock-info <?php echo ($product['stock'] < 10) ? 'low-stock' : 'in-stock'; ?>">
            <strong>Stock:</strong> 
            <?php if ($product['stock'] > 0): ?>
              <?php echo $product['stock']; ?> items available
              <?php if ($product['stock'] < 10): ?>
                <span class="low-stock-warning">⚠️ Low Stock!</span>
              <?php endif; ?>
            <?php else: ?>
              <span class="out-of-stock">❌ Out of Stock</span>
            <?php endif; ?>
          </p>
        </div>

        <div class="product-description">
          <h3>Product Description</h3>
          <?php if (!empty($product['description'])): ?>
            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
          <?php else: ?>
            <p>Experience the ultimate in outdoor comfort with our premium <?php echo htmlspecialchars($product['product_name']); ?>. 
            Designed for adventurers who demand quality and durability, this product combines functionality with comfort.</p>
          <?php endif; ?>
          
          <ul class="features">
            <li>✅ High-quality materials for long-lasting durability</li>
            <li>✅ Ergonomic design for maximum comfort</li>
            <li>✅ Easy to use and maintain</li>
            <li>✅ Perfect for outdoor activities and camping</li>
            <li>✅ Compact and portable design</li>
          </ul>
        </div>

        <?php if (!empty($product['specifications'])): ?>
        <div class="product-specifications">
          <h3>Specifications</h3>
          <div class="specs-table">
            <?php
            // Parse specifications - can be plain text or formatted
            $specs_lines = explode("\n", $product['specifications']);
            foreach ($specs_lines as $spec) {
              $spec = trim($spec);
              if (!empty($spec)) {
                // Try to split by colon or dash
                if (strpos($spec, ':') !== false) {
                  list($label, $value) = explode(':', $spec, 2);
                  echo '<div class="spec-row"><span class="spec-label">' . htmlspecialchars(trim($label)) . ':</span> <span class="spec-value">' . htmlspecialchars(trim($value)) . '</span></div>';
                } else {
                  echo '<div class="spec-row"><span class="spec-value">' . htmlspecialchars($spec) . '</span></div>';
                }
              }
            }
            ?>
            
            <?php if (!empty($product['dimensions'])): ?>
              <div class="spec-row">
                <span class="spec-label">Dimensions:</span>
                <span class="spec-value"><?php echo htmlspecialchars($product['dimensions']); ?></span>
              </div>
            <?php endif; ?>
            
            <?php if (!empty($product['weight'])): ?>
              <div class="spec-row">
                <span class="spec-label">Weight:</span>
                <span class="spec-value"><?php echo htmlspecialchars($product['weight']); ?></span>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>

        <div class="purchase-section">
          <div class="quantity-selector">
            <label>Quantity:</label>
            <div class="quantity-controls">
              <button type="button" id="decreaseQty" onclick="updateQuantity(-1)">-</button>
              <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" readonly />
              <button type="button" id="increaseQty" onclick="updateQuantity(1)">+</button>
            </div>
          </div>

          <div class="action-buttons">
            <button class="add-to-cart-btn" 
                    data-product-id="<?php echo htmlspecialchars($product['id']); ?>"
                    data-product-name="<?php echo htmlspecialchars($product['product_name']); ?>"
                    data-product-price="<?php echo $product['price']; ?>"
                    data-product-image="<?php echo htmlspecialchars($product['image']); ?>"
                    <?php echo ($product['stock'] <= 0) ? 'disabled' : ''; ?>>
              <i class="bi bi-cart-plus"></i>
              <?php echo ($product['stock'] <= 0) ? 'Out of Stock' : 'Add to Cart'; ?>
            </button>
            
            <button class="buy-now-btn"
                    data-product-id="<?php echo htmlspecialchars($product['id']); ?>"
                    data-product-name="<?php echo htmlspecialchars($product['product_name']); ?>"
                    data-product-price="<?php echo $product['price']; ?>"
                    data-product-image="<?php echo htmlspecialchars($product['image']); ?>"
                    <?php echo ($product['stock'] <= 0) ? 'disabled' : ''; ?>>
              <i class="bi bi-lightning-fill"></i>
              <?php echo ($product['stock'] <= 0) ? 'Unavailable' : 'Buy Now'; ?>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ==========================================================================
         CUSTOMER REVIEWS SECTION
         ========================================================================== -->
    <section class="reviews-section" id="reviewsSection">
      <div class="reviews-section-header">
        <h2><i class="bi bi-chat-square-heart-fill"></i> Customer Reviews & Ratings</h2>
      </div>

      <?php if (isset($_GET['review_submitted'])): ?>
        <div class="review-alert success">
          <i class="bi bi-check-circle-fill" style="font-size: 1.3rem;"></i>
          <span>Thank you! Your product review has been submitted successfully.</span>
        </div>
      <?php endif; ?>

      <?php if (!empty($review_error)): ?>
        <div class="review-alert error">
          <i class="bi bi-exclamation-triangle-fill" style="font-size: 1.3rem;"></i>
          <span><?php echo htmlspecialchars($review_error); ?></span>
        </div>
      <?php endif; ?>

      <!-- Reviews Overview Card -->
      <div class="reviews-overview-card">
        <div class="rating-summary-box">
          <div class="rating-big-number"><?php echo number_format($avg_rating, 1); ?></div>
          <div class="rating-stars-gold">
            <?php 
            for ($s = 1; $s <= 5; $s++) {
                if ($s <= floor($avg_rating)) {
                    echo '<i class="bi bi-star-fill"></i>';
                } elseif ($s - $avg_rating < 1 && $s - $avg_rating > 0) {
                    echo '<i class="bi bi-star-half"></i>';
                } else {
                    echo '<i class="bi bi-star" style="color: #ccc;"></i>';
                }
            }
            ?>
          </div>
          <div class="rating-total-count">Based on <?php echo $reviews_count; ?> <?php echo $reviews_count === 1 ? 'review' : 'reviews'; ?></div>
        </div>

        <div class="rating-bars-container">
          <?php for ($star = 5; $star >= 1; $star--): 
            $count = $rating_breakdown[$star] ?? 0;
            $percent = $reviews_count > 0 ? round(($count / $reviews_count) * 100) : 0;
          ?>
            <div class="rating-bar-row">
              <span class="rating-bar-label"><?php echo $star; ?> <i class="bi bi-star-fill" style="color: #f39c12; font-size: 0.8rem;"></i></span>
              <div class="rating-bar-track">
                <div class="rating-bar-fill" style="width: <?php echo $percent; ?>%;"></div>
              </div>
              <span class="rating-bar-percent"><?php echo $percent; ?>%</span>
            </div>
          <?php endfor; ?>
        </div>

        <div class="rating-cta-box">
          <p>Share your thoughts with other adventurers!</p>
          <button type="button" class="write-review-btn" onclick="toggleReviewForm()">
            <i class="bi bi-pencil-square"></i> Write a Review
          </button>
        </div>
      </div>

      <!-- Write a Review Form Card -->
      <div class="review-form-card" id="reviewFormContainer">
        <h3><i class="bi bi-pencil-fill"></i> Write Your Review</h3>
        <form method="POST" action="ProductView.php?id=<?php echo urlencode($product['id'] ?? $product_id); ?>#reviewsSection" id="reviewSubmitForm">
          <div class="star-rating-picker">
            <label>Your Rating: *</label>
            <div class="stars-select" id="starRatingSelector">
              <i class="bi bi-star-fill active" data-rating="1"></i>
              <i class="bi bi-star-fill active" data-rating="2"></i>
              <i class="bi bi-star-fill active" data-rating="3"></i>
              <i class="bi bi-star-fill active" data-rating="4"></i>
              <i class="bi bi-star-fill active" data-rating="5"></i>
            </div>
            <span class="rating-feedback-text" id="ratingFeedback">5 stars - Excellent!</span>
            <input type="hidden" name="rating" id="reviewRatingInput" value="5">
          </div>

          <div class="review-form-row">
            <div class="review-form-group">
              <label for="reviewUserName">Your Name *</label>
              <input type="text" id="reviewUserName" name="user_name" 
                     value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>" 
                     placeholder="e.g. John Doe" required>
            </div>
            <div class="review-form-group">
              <label for="reviewTitle">Review Title (Optional)</label>
              <input type="text" id="reviewTitle" name="review_title" 
                     placeholder="e.g. Highly durable and compact!">
            </div>
          </div>

          <div class="review-form-group">
            <label for="reviewText">Your Review *</label>
            <textarea id="reviewText" name="review_text" rows="4" 
                      placeholder="What did you like or dislike about this product? How did it perform during your adventures?" required></textarea>
          </div>

          <div class="review-form-actions">
            <button type="button" class="btn-secondary" onclick="toggleReviewForm()">Cancel</button>
            <button type="submit" name="submit_review" class="write-review-btn">
              <i class="bi bi-send-fill"></i> Submit Review
            </button>
          </div>
        </form>
      </div>

      <!-- Reviews Cards List -->
      <div class="reviews-list">
        <?php if (!empty($reviews)): ?>
          <?php foreach ($reviews as $rev): 
            $initial = strtoupper(substr($rev['user_name'] ?? 'U', 0, 1));
            $rev_rating = intval($rev['rating'] ?? 5);
          ?>
            <div class="review-card">
              <div class="review-card-header">
                <div class="reviewer-profile">
                  <div class="reviewer-avatar"><?php echo htmlspecialchars($initial); ?></div>
                  <div class="reviewer-meta">
                    <h4>
                      <?php echo htmlspecialchars($rev['user_name']); ?>
                      <span class="verified-badge"><i class="bi bi-patch-check-fill"></i> Verified Purchase</span>
                    </h4>
                    <span class="review-date"><?php echo date('F j, Y', strtotime($rev['created_at'])); ?></span>
                  </div>
                </div>
                <div class="review-card-stars">
                  <?php for ($s = 1; $s <= 5; $s++): ?>
                    <?php if ($s <= $rev_rating): ?>
                      <i class="bi bi-star-fill"></i>
                    <?php else: ?>
                      <i class="bi bi-star" style="color: #ccc;"></i>
                    <?php endif; ?>
                  <?php endfor; ?>
                </div>
              </div>

              <?php if (!empty($rev['review_title'])): ?>
                <h5 class="review-card-title"><?php echo htmlspecialchars($rev['review_title']); ?></h5>
              <?php endif; ?>

              <p class="review-card-text"><?php echo nl2br(htmlspecialchars($rev['review_text'])); ?></p>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-reviews-card">
            <i class="bi bi-chat-heart"></i>
            <h3>No Reviews Yet</h3>
            <p>Be the very first customer to review the <?php echo htmlspecialchars($product['product_name']); ?>!</p>
            <button type="button" class="write-review-btn" onclick="toggleReviewForm()">
              <i class="bi bi-pencil-square"></i> Leave a Review
            </button>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <!-- RELATED PRODUCTS SECTION -->
    <?php if (!empty($related_products)): ?>
    <section class="related-products">
      <h2>You Might Also Like</h2>
      <div class="related-grid">
        <?php foreach ($related_products as $related): ?>
          <div class="related-card">
            <a href="ProductView.php?id=<?php echo $related['id']; ?>">
              <img src="<?php echo htmlspecialchars($related['image']); ?>" alt="<?php echo htmlspecialchars($related['product_name']); ?>">
              <h3><?php echo htmlspecialchars($related['product_name']); ?></h3>
              <p class="related-price">₱<?php echo number_format($related['price'], 2); ?></p>
              <p class="related-stock">Stock: <?php echo $related['stock']; ?></p>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>
  </main>

  <!-- AUTH MODAL -->
  <?php include 'components/auth_modal.php'; ?>

  <!-- WISHLIST MODAL -->
  <div id="wishlistModal" class="wishlist-modal">
    <div class="wishlist-modal-content">
      <div class="wishlist-header">
        <h2><i class="bi bi-heart-fill"></i> My Wishlist</h2>
        <button class="close-wishlist" onclick="toggleWishlistModal()">&times;</button>
      </div>
      <div class="wishlist-body" id="wishlistBody">
        <!-- Wishlist items will be populated here -->
      </div>
    </div>
  </div>

  <!-- VIDEO MODAL -->
  <?php if (!empty($product['video_url'])): ?>
  <div id="videoModal" class="video-modal">
    <div class="video-modal-content">
      <button class="close-video" onclick="hideVideoModal()">&times;</button>
      <div class="video-container">
        <?php
        $video_url = $product['video_url'];
        // Convert YouTube URLs to embed format
        if (strpos($video_url, 'youtube.com') !== false || strpos($video_url, 'youtu.be') !== false) {
            // Extract video ID
            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/', $video_url, $matches);
            if (!empty($matches[1])) {
                $video_id = $matches[1];
                echo '<iframe id="productVideo" width="100%" height="500" src="https://www.youtube.com/embed/' . $video_id . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
            }
        } else {
            // For other video URLs, use video tag
            echo '<video id="productVideo" width="100%" height="500" controls><source src="' . htmlspecialchars($video_url) . '" type="video/mp4">Your browser does not support the video tag.</video>';
        }
        ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- FOOTER -->
  <footer class="site-footer">
    <!-- footer content here (unchanged) -->
  </footer>

  <!-- JAVASCRIPT -->
  <script src="Js/wishlist.js"></script>
  <script src="Js/JavaScript.js"></script>
  <script>
    // PRODUCT VIEW FUNCTIONALITY
    const maxStock = <?php echo $product['stock']; ?>;
    
    // Image gallery functionality
    function changeImage(newSrc) {
      document.getElementById('mainProductImage').src = newSrc;
      
      // Update active thumbnail
      document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
      });
      event.target.classList.add('active');
    }
    
    // Quantity controls
    function updateQuantity(change) {
      const quantityInput = document.getElementById('quantity');
      let currentQty = parseInt(quantityInput.value);
      let newQty = currentQty + change;
      
      if (newQty < 1) newQty = 1;
      if (newQty > maxStock) newQty = maxStock;
      
      quantityInput.value = newQty;
      
      // Update button states
      document.getElementById('decreaseQty').disabled = (newQty <= 1);
      document.getElementById('increaseQty').disabled = (newQty >= maxStock);
    }
    
    // Add to Cart functionality
    document.addEventListener('DOMContentLoaded', function() {
      const addToCartBtn = document.querySelector('.add-to-cart-btn');
      const buyNowBtn = document.querySelector('.buy-now-btn');
      
      // Add to cart functionality is now handled by the global cart.js file
      
      if (buyNowBtn) {
        buyNowBtn.addEventListener('click', function() {
          const productId = this.getAttribute('data-product-id');
          const productName = this.getAttribute('data-product-name');
          const productPrice = this.getAttribute('data-product-price');
          const productImage = this.getAttribute('data-product-image');
          const quantity = parseInt(document.getElementById('quantity').value);
          
          buyNow(productId, productName, productPrice, productImage, quantity);
        });
      }
    });
    
    // addToCart function is now handled by the global cart.js file
    
    function buyNow(productId, productName, productPrice, productImage, quantity) {
      // Add to cart first using the global function, then redirect to checkout
      if (quantity > 1) {
        bulkAddToCart(productId, productName, productPrice, productImage, quantity);
      } else {
        addToCart(productId, productName, productPrice, productImage, quantity);
      }
      
      // Wait a moment for cart to update, then redirect
      setTimeout(() => {
        window.location.href = 'cart.php?checkout=1';
      }, 1500);
    }

    // MODAL FUNCTIONALITY (if login modal exists)
    const loginIcon = document.getElementById("loginIcon");
    const authModal = document.getElementById("authModal");
    const closeModalBtn = document.getElementById("closeModal");

    if (loginIcon && authModal) {
      loginIcon.addEventListener("click", () => {
        authModal.classList.add("active");
      });

      if (closeModalBtn) {
        closeModalBtn.addEventListener("click", () => {
          authModal.classList.remove("active");
        });
      }

      window.addEventListener("click", (e) => {
        if (e.target === authModal) authModal.classList.remove("active");
      });

      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") authModal.classList.remove("active");
      });
    }

    // Initialize quantity controls
    document.getElementById('decreaseQty').disabled = true; // Start disabled at qty 1
    if (maxStock <= 1) {
      document.getElementById('increaseQty').disabled = true;
    }

    // Video modal functions
    function showVideoModal() {
      const videoModal = document.getElementById('videoModal');
      if (videoModal) {
        videoModal.classList.add('active');
        document.body.style.overflow = 'hidden';
      }
    }

    function hideVideoModal() {
      const videoModal = document.getElementById('videoModal');
      if (videoModal) {
        videoModal.classList.remove('active');
        document.body.style.overflow = 'auto';
        
        // Pause video if playing
        const video = document.getElementById('productVideo');
        if (video) {
          if (video.tagName === 'VIDEO') {
            video.pause();
          } else if (video.tagName === 'IFRAME') {
            // For YouTube videos, reload iframe to stop playback
            const src = video.src;
            video.src = '';
            video.src = src;
          }
        }
      }
    }

    // Close video modal on click outside
    document.getElementById('videoModal')?.addEventListener('click', function(e) {
      if (e.target === this) {
        hideVideoModal();
      }
    });

    // Close video modal on Escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        hideVideoModal();
      }
    });
  </script>
  
  <!-- Scripts -->
  <script src="Js/cart.js"></script>
  <script src="components/auth_modal_otp.js"></script>
  
  <script>
    // Header search functionality
    document.addEventListener('DOMContentLoaded', function() {
      const headerSearch = document.getElementById('headerSearch');
      
      if (headerSearch) {
        // Handle Enter key press
        headerSearch.addEventListener('keypress', function(e) {
          if (e.key === 'Enter') {
            e.preventDefault();
            const searchTerm = this.value.trim();
            if (searchTerm.length > 0) {
              // Redirect to product catalog with search query
              window.location.href = `ProductCatalog.php?search=${encodeURIComponent(searchTerm)}`;
            }
          }
        });
      }
    });

    // ==========================================
    // CUSTOMER REVIEWS INTERACTION SCRIPT
    // ==========================================
    function toggleReviewForm() {
      const formCard = document.getElementById('reviewFormContainer');
      if (!formCard) return;

      if (formCard.classList.contains('active')) {
        formCard.classList.remove('active');
      } else {
        formCard.classList.add('active');
        formCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
        const nameInput = document.getElementById('reviewUserName');
        if (nameInput && !nameInput.value) {
          nameInput.focus();
        } else {
          document.getElementById('reviewText')?.focus();
        }
      }
    }

    // Interactive Star Rating Selector
    document.addEventListener('DOMContentLoaded', function() {
      const starIcons = document.querySelectorAll('#starRatingSelector i');
      const ratingInput = document.getElementById('reviewRatingInput');
      const feedbackText = document.getElementById('ratingFeedback');

      const ratingDescriptions = {
        1: '1 star - Poor',
        2: '2 stars - Fair',
        3: '3 stars - Good',
        4: '4 stars - Very Good',
        5: '5 stars - Excellent!'
      };

      function updateStars(rating) {
        starIcons.forEach(icon => {
          const val = parseInt(icon.getAttribute('data-rating'));
          if (val <= rating) {
            icon.classList.remove('bi-star');
            icon.classList.add('bi-star-fill', 'active');
          } else {
            icon.classList.remove('bi-star-fill', 'active');
            icon.classList.add('bi-star');
          }
        });
        if (ratingInput) ratingInput.value = rating;
        if (feedbackText) feedbackText.textContent = ratingDescriptions[rating] || `${rating} stars`;
      }

      starIcons.forEach(icon => {
        icon.addEventListener('click', function() {
          const rating = parseInt(this.getAttribute('data-rating'));
          updateStars(rating);
        });

        icon.addEventListener('mouseenter', function() {
          const hoverRating = parseInt(this.getAttribute('data-rating'));
          starIcons.forEach(s => {
            const val = parseInt(s.getAttribute('data-rating'));
            if (val <= hoverRating) {
              s.classList.add('hover');
            } else {
              s.classList.remove('hover');
            }
          });
        });

        icon.addEventListener('mouseleave', function() {
          starIcons.forEach(s => s.classList.remove('hover'));
        });
      });
    });
  </script>
</body>
</html>
