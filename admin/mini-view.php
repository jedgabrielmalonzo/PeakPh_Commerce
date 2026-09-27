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
  <style>
    .preview-toolbar {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      background: #fff;
      padding: 12px 18px;
      border-radius: 8px;
      border: 1px solid #e0e0e0;
      margin-top: 15px;
      margin-bottom: 15px;
    }
    .preview-group {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .preview-btn {
      background: #f0fdf4;
      border: 1px solid #27ae60;
      color: #14532d;
      padding: 6px 12px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 500;
      font-size: 13px;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      transition: 0.2s;
    }
    .preview-btn:hover, .preview-btn.active {
      background: #27ae60;
      color: #fff;
    }
    .preview-select {
      padding: 6px 10px;
      border: 1px solid #27ae60;
      border-radius: 6px;
      font-size: 13px;
      font-family: inherit;
      outline: none;
    }
    .preview-container {
      background: #333;
      padding: 20px;
      border-radius: 10px;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      overflow-x: auto;
      min-height: 650px;
      box-shadow: inset 0 2px 8px rgba(0,0,0,0.3);
    }
    .iframe-wrapper {
      width: 100%;
      transition: width 0.3s ease;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.4);
      overflow: hidden;
    }
    .iframe-wrapper iframe {
      width: 100%;
      height: 700px;
      border: none;
      display: block;
    }
  </style>
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
    <h2>Live Website Mini View</h2>

    <!-- Preview Toolbar -->
    <div class="preview-toolbar">
      <div class="preview-group">
        <label for="pageSelector" style="font-weight:600; font-size:13px; color:#333;">Page:</label>
        <select id="pageSelector" class="preview-select" onchange="changePreviewPage(this.value)">
          <option value="../index.php">🏠 Homepage (index.php)</option>
          <option value="../ProductCatalog.php">🛍️ Product Catalog</option>
          <option value="../pages/contact-us.php">📞 Contact Us</option>
        </select>
      </div>

      <div class="preview-group">
        <button class="preview-btn active" id="btnDesktop" onclick="setDevice('100%', this)">
          <i class="bi bi-display"></i> Desktop
        </button>
        <button class="preview-btn" id="btnTablet" onclick="setDevice('768px', this)">
          <i class="bi bi-tablet"></i> Tablet (768px)
        </button>
        <button class="preview-btn" id="btnMobile" onclick="setDevice('375px', this)">
          <i class="bi bi-phone"></i> Mobile (375px)
        </button>
      </div>

      <div class="preview-group">
        <button class="preview-btn" onclick="reloadPreview()" title="Reload Preview">
          <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
        <button class="preview-btn" onclick="openLivePage()" title="Open In New Window">
          <i class="bi bi-box-arrow-up-right"></i> Open Live
        </button>
      </div>
    </div>

    <!-- Preview Container -->
    <div class="preview-container">
      <div class="iframe-wrapper" id="iframeWrapper">
        <iframe id="homepagePreview" src="../index.php"></iframe>
      </div>
    </div>
  </div>

  <!-- JS -->
  <script src="../Js/admin.js"></script>
  <script>
    function setDevice(width, btn) {
      document.getElementById('iframeWrapper').style.width = width;
      document.querySelectorAll('.preview-group .preview-btn').forEach(b => {
        if (b.id && b.id.startsWith('btn')) b.classList.remove('active');
      });
      if (btn) btn.classList.add('active');
    }

    function changePreviewPage(url) {
      document.getElementById('homepagePreview').src = url;
    }

    function reloadPreview() {
      const iframe = document.getElementById('homepagePreview');
      iframe.src = iframe.src;
    }

    function openLivePage() {
      const iframe = document.getElementById('homepagePreview');
      window.open(iframe.src, '_blank');
    }
  </script>
</body>
</html>
