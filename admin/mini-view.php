<?php
require_once('auth_helper.php');
requireAdminAuth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mini View - PeakPH</title>
  <!-- Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
  <!-- Admin Styles -->
  <link rel="stylesheet" href="../Css/admin.css">
</head>
<body>
  <!-- HEADER -->
  <header>
    <h2>Mini View Editor</h2>
    <button onclick="logout()">Logout</button>
  </header>

  <!-- SIDEBAR -->
  <div class="sidebar">
    <h3>Menu</h3>
    <a href="admin.php" class="menu-link"><i class="bi bi-house"></i> Admin Home</a>
    <a href="dashboard.php" class="menu-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="mini-view.php" class="menu-link active"><i class="bi bi-pencil-square"></i> Mini View</a>
    <a href="inventory/inventory.php" class="menu-link"><i class="bi bi-box"></i> Inventory</a>
    <a href="orders.php" class="menu-link"><i class="bi bi-bag"></i> Orders</a>
    <a href="users/users.php" class="menu-link"><i class="bi bi-people"></i> Users</a>
    <!-- Collapsible Content Manager (expanded by default) -->
    <button class="collapsible" onclick="toggleContentManager()">
      <i class="bi bi-folder"></i> Content Manager
      <span id="arrow" style="float:right;">&#9660;</span>
    </button>
    <div class="content-manager-links" id="contentManagerLinks" style="display:block; margin-left: 15px;">
      <a href="content/carousel.php" class="menu-link"><i class="bi bi-images"></i> Carousel</a>
      <a href="content/bestseller.php" class="menu-link"><i class="bi bi-star"></i> Best Seller</a>
      <a href="content/new_arrivals.php" class="menu-link"><i class="bi bi-lightning"></i> New Arrivals</a>
      <a href="content/footer.php" class="menu-link"><i class="bi bi-layout-text-window-reverse"></i> Footer</a>
    </div>
  </div>
  
  <!-- CONTENT -->
  <div class="content">
    <h2>Edit Homepage Mini View</h2>

    <!-- Banner Title Editor -->
    <div class="editor-controls">
      <label>Banner Title:</label>
      <input type="text" id="bannerTitle" placeholder="Enter banner title"/>
      <button onclick="updateBanner()">Update</button>
    </div>

    <!-- Homepage Preview -->
    <iframe id="homepagePreview" src="../index.php" width="100%" height="500px" style="border:1px solid #ccc; border-radius:8px; margin-top:15px;"></iframe>
  </div>

  <!-- JS -->
  <script src="../Js/admin.js"></script>
</body>
</html>
