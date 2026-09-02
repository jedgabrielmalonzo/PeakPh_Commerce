<?php
require_once('../auth_helper.php');
requireAdminAuth();
require_once("../../includes/db.php");
require_once("footer_functions.php");

// Get current footer data
$footerData = getFooterData();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Footer Management - PeakPH</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
  <link rel="stylesheet" href="../../Css/admin.css">
</head>
<body>
  <!-- HEADER -->
  <header>
    <h2>Footer Content Management</h2>
    <button onclick="window.location.href='../../logout.php'">Logout</button>
  </header>

  <!-- SIDEBAR -->
  <div class="sidebar">
    <h3>Menu</h3>
    <a href="../admin.php" class="menu-link"><i class="bi bi-house"></i> Admin Home</a>
    <a href="../dashboard.php" class="menu-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="../mini-view.php" class="menu-link"><i class="bi bi-pencil-square"></i> Mini View</a>
    <a href="../inventory/inventory.php" class="menu-link"><i class="bi bi-box"></i> Inventory</a>
    <a href="../orders.php" class="menu-link"><i class="bi bi-bag"></i> Orders</a>
    <a href="../users/users.php" class="menu-link"><i class="bi bi-people"></i> Users</a>
    <!-- Content Manager -->
    <button class="collapsible" onclick="toggleContentManager()">
      <i class="bi bi-folder"></i> Content Manager
      <span id="arrow" style="float:right;">&#9660;</span>
    </button>
    <div class="content-manager-links" id="contentManagerLinks" style="display:block; margin-left: 15px;">
      <a href="carousel.php" class="menu-link"><i class="bi bi-images"></i> Carousel</a>
      <a href="bestseller.php" class="menu-link"><i class="bi bi-star"></i> Best Seller</a>
      <a href="new_arrivals.php" class="menu-link"><i class="bi bi-lightning"></i> New Arrivals</a>
      <a href="footer.php" class="menu-link active"><i class="bi bi-layout-text-window-reverse"></i> Footer</a>
    </div>
  </div>

  <!-- MAIN CONTENT -->
  <div class="content">
    <h2>Footer Content Management</h2>
    
    <div class="info-box">
      <h3><i class="bi bi-info-circle"></i> Footer Management</h3>
      <p>Manage your website's footer content, social media links, and contact information.</p>
    </div>

    <!-- Footer Settings Form -->
    <div class="form-section">
      <h3>Footer Settings</h3>
      <?php if (isset($_GET['status'])): ?>
        <div class="alert <?= $_GET['status'] === 'updated' ? 'alert-success' : 'alert-error' ?>">
          <?= $_GET['status'] === 'updated' ? 'Footer updated successfully!' : 'Error updating footer.' ?>
        </div>
      <?php endif; ?>
      <form method="POST" action="footer_data.php">
        <div class="form-group">
          <label for="company_name">Company Name:</label>
          <input type="text" id="company_name" name="company_name" value="<?= htmlspecialchars($footerData['company_name']) ?>" required>
        </div>

        <div class="form-group">
          <label for="company_description">Company Description:</label>
          <textarea id="company_description" name="company_description" rows="3"><?= htmlspecialchars($footerData['company_description']) ?></textarea>
        </div>

        <div class="form-group">
          <label for="contact_email">Contact Email:</label>
          <input type="email" id="contact_email" name="contact_email" value="<?= htmlspecialchars($footerData['contact_email']) ?>">
        </div>

        <div class="form-group">
          <label for="contact_phone">Contact Phone:</label>
          <input type="text" id="contact_phone" name="contact_phone" value="<?= htmlspecialchars($footerData['contact_phone']) ?>">
        </div>

        <div class="form-group">
          <label for="address">Address:</label>
          <textarea id="address" name="address" rows="2"><?= htmlspecialchars($footerData['address']) ?></textarea>
        </div>

        <h4>Social Media Links</h4>
        
        <div class="form-group">
          <label for="facebook_link">Facebook URL:</label>
          <input type="url" id="facebook_link" name="facebook_link" value="<?= htmlspecialchars($footerData['facebook_link']) ?>">
        </div>

        <div class="form-group">
          <label for="instagram_link">Instagram URL:</label>
          <input type="url" id="instagram_link" name="instagram_link" value="<?= htmlspecialchars($footerData['instagram_link']) ?>">
        </div>

        <div class="form-group">
          <label for="youtube_link">YouTube URL:</label>
          <input type="url" id="youtube_link" name="youtube_link" value="<?= htmlspecialchars($footerData['youtube_link']) ?>">
        </div>

        <div class="form-group">
          <label for="tiktok_link">TikTok URL:</label>
          <input type="url" id="tiktok_link" name="tiktok_link" value="<?= htmlspecialchars($footerData['tiktok_link']) ?>">
        </div>

        <div class="form-group">
          <label for="twitter_link">Twitter URL:</label>
          <input type="url" id="twitter_link" name="twitter_link" value="<?= htmlspecialchars($footerData['twitter_link']) ?>">
        </div>

        <div class="form-group">
          <label for="copyright_text">Copyright Text:</label>
          <input type="text" id="copyright_text" name="copyright_text" value="<?= htmlspecialchars($footerData['copyright_text']) ?>">
        </div>

        <button type="submit" class="btn-primary">
          <i class="bi bi-save"></i> Update Footer
        </button>
      </form>
    </div>

    <!-- Current Footer Preview -->
    <div class="preview-section">
      <h3>Current Footer Preview</h3>
      <div class="footer-preview">
        <div class="footer-top">
          <div class="social-section">
            <p class="follow-text">Follow Us</p>
            <div class="social-icons">
              <?php if (!empty($footerData['facebook_link'])): ?>
                <a href="<?= htmlspecialchars($footerData['facebook_link']) ?>" target="_blank"><i class="bi bi-facebook"></i></a>
              <?php endif; ?>
              <?php if (!empty($footerData['instagram_link'])): ?>
                <a href="<?= htmlspecialchars($footerData['instagram_link']) ?>" target="_blank"><i class="bi bi-instagram"></i></a>
              <?php endif; ?>
              <?php if (!empty($footerData['youtube_link'])): ?>
                <a href="<?= htmlspecialchars($footerData['youtube_link']) ?>" target="_blank"><i class="bi bi-youtube"></i></a>
              <?php endif; ?>
              <?php if (!empty($footerData['tiktok_link'])): ?>
                <a href="<?= htmlspecialchars($footerData['tiktok_link']) ?>" target="_blank"><i class="bi bi-tiktok"></i></a>
              <?php endif; ?>
              <?php if (!empty($footerData['twitter_link'])): ?>
                <a href="<?= htmlspecialchars($footerData['twitter_link']) ?>" target="_blank"><i class="bi bi-twitter"></i></a>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <hr />

        <div class="footer-links">
          <?php foreach ($footerData['footer_links'] as $category => $links): ?>
            <div>
              <h4><?= htmlspecialchars($category) ?></h4>
              <?php foreach ($links as $title => $url): ?>
                <a href="<?= htmlspecialchars($url) ?>"><?= htmlspecialchars($title) ?></a>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
        </div>

        <hr />

        <div class="footer-bottom">
          <small><?= htmlspecialchars($footerData['copyright_text']) ?></small>
        </div>
      </div>
    </div>
  </div>

  <script src="../../Js/admin.js"></script>
  <style>
    .info-box {
      background: #e8f4fd;
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 2rem;
      border-left: 4px solid #007bff;
    }
    
    .form-section {
      background: white;
      padding: 2rem;
      border-radius: 8px;
      margin-bottom: 2rem;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .form-group {
      margin-bottom: 1rem;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
    }
    
    .form-group input, .form-group textarea {
      width: 100%;
      padding: 0.5rem;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-family: inherit;
    }
    
    .preview-section {
      background: white;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .footer-preview {
      background: #f7f7f7;
      padding: 20px 50px;
      border-radius: 8px;
      margin-top: 1rem;
      font-family: Arial, sans-serif;
    }
    
    .footer-top {
      text-align: center;
      margin-bottom: 10px;
    }
    
    .follow-text {
      font-weight: bold;
      margin-bottom: 5px;
    }
    
    .social-icons a {
      font-size: 20px;
      margin: 0 10px;
      color: #000;
      text-decoration: none;
    }
    
    .footer-links {
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      gap: 20px;
      margin: 20px 0;
    }
    
    .footer-links h4 {
      font-size: 14px;
      margin-bottom: 10px;
      border-bottom: 1px solid #ddd;
      padding-bottom: 5px;
    }
    
    .footer-links a {
      display: block;
      font-size: 13px;
      color: #333;
      margin-bottom: 6px;
      text-decoration: none;
    }
    
    .footer-links a:hover {
      text-decoration: underline;
    }
    
    .footer-bottom {
      text-align: center;
      font-size: 12px;
      color: #555;
    }
    
    .alert {
      padding: 1rem;
      border-radius: 4px;
      margin-bottom: 1rem;
    }
    
    .alert-success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    
    .alert-error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    
    .form-section h4 {
      margin-top: 2rem;
      margin-bottom: 1rem;
      color: #2c3e50;
      border-bottom: 2px solid #3498db;
      padding-bottom: 0.5rem;
    }
  </style>
</body>
</html>