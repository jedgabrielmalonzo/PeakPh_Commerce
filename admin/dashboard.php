<?php
require_once('auth_helper.php');
requireAdminAuth();
require_once("../includes/db.php");

/* -------------------------
   TOTAL STOCK (inventory)
   ------------------------- */
$totalStock = 0;
$totalQuery = $conn->query("SELECT SUM(stock) AS total_stock FROM inventory");
if ($totalQuery && $data = $totalQuery->fetch_assoc()) {
    $totalStock = (int)($data['total_stock'] ?? 0);
}

/* -------------------------
   LOW STOCK PRODUCTS (11-49)
   ------------------------- */
$lowStockProducts = [];
$lowStockQuery = $conn->query("SELECT id, product_name, stock FROM inventory WHERE stock BETWEEN 11 AND 49 ORDER BY stock ASC");
if ($lowStockQuery && $lowStockQuery->num_rows > 0) {
    while ($r = $lowStockQuery->fetch_assoc()) {
        $lowStockProducts[] = $r;
    }
}

/* -------------------------
   CRITICAL STOCK PRODUCTS (0-10)
   ------------------------- */
$criticalStockProducts = [];
$criticalStockQuery = $conn->query("SELECT id, product_name, stock FROM inventory WHERE stock BETWEEN 0 AND 10 ORDER BY stock ASC");
if ($criticalStockQuery && $criticalStockQuery->num_rows > 0) {
    while ($r = $criticalStockQuery->fetch_assoc()) {
        $criticalStockProducts[] = $r;
    }
}


/* -------------------------
   LATEST UPDATE (updated_at)
   ------------------------- */
$lastUpdate = "No records yet";
$updateQuery = $conn->query("SELECT MAX(updated_at) AS last_update FROM inventory");
if ($updateQuery && $u = $updateQuery->fetch_assoc()) {
    if (!empty($u['last_update'])) {
        $lastUpdate = date("M d, Y H:i", strtotime($u['last_update']));
    }
}

/* -------------------------
   TOTAL USERS (users table)
   ------------------------- */
$totalUsers = 0;
$userQuery = $conn->query("SELECT COUNT(*) AS total_users FROM users");
if ($userQuery && $urow = $userQuery->fetch_assoc()) {
    $totalUsers = (int)($urow['total_users'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard - PeakPH</title>
  
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="../Assets/Carousel_Picts/Logo.png" />
  
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
  <link rel="stylesheet" href="../Css/admin.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <!-- HEADER -->
  <header>
    <h2>Dashboard Overview</h2>
    <button onclick="logout()">Logout</button>
  </header>

  <!-- LEFT SIDEBAR -->
  <div class="sidebar">
    <h3>Menu</h3>
    <a href="admin.php" class="menu-link"><i class="bi bi-house"></i> Admin Home</a>
    <a href="dashboard.php" class="menu-link active"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="mini-view.php" class="menu-link"><i class="bi bi-pencil-square"></i> Mini View</a>
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

  <!-- MAIN WRAPPER -->
  <div class="dashboard-container" style="margin-left:240px; margin-top:70px; display:flex; gap:20px;">

    <!-- MAIN CONTENT -->
    <div class="content" style="flex:3;">
      <h2>OVERVIEW</h2>
      <span class="last-update">Last updated <?= htmlspecialchars($lastUpdate) ?></span>

      <!-- CARDS -->
      <div class="cards">
        <div class="card stat">
          <h3>₱85,000</h3>
          <p>Total Sales</p>
        </div>

        <!-- Total Inventory -->
        <div class="card stat">
          <h3><?= number_format($totalStock); ?></h3>
          <p>Total Inventory</p>
        </div>

        <!-- Total Users (clickable) -->
        <div class="card stat" style="cursor:pointer;" onclick="window.location.href='users/users.php'">
          <h3><?= number_format($totalUsers); ?></h3>
          <p>Total Users</p>
        </div>

        <div class="card stat">
          <h3>1,050</h3>
          <p>Total Orders</p>
        </div>
      </div>

      <!-- CHART -->
      <div class="chart-container">
        <div class="chart-tabs">
          <button class="active">Week</button>
          <button>Month</button>
          <button>Year</button>
        </div>
        <canvas id="salesChart"></canvas>
      </div>
    </div>

    <!-- RIGHT SIDEBAR -->
    <aside class="sidebar-right">
      <div class="clock">
        <h3 id="day"></h3>
        <p class="date" id="date"></p>
        <h1 id="time"></h1>
      </div>

      <div class="notifications">
    <h3>Notifications</h3>

    <?php if (!empty($criticalStockProducts)): ?>
    <div class="notif">
      <span class="tag red">⛔ Critical Stock</span>
      <p><?= count($criticalStockProducts); ?> product(s) are critically low (less than 10 left)!</p>
    </div>
  <?php endif; ?>

  <?php if (!empty($lowStockProducts)): ?>
    <div class="notif">
      <span class="tag orange">⚠️ Low Stock</span>
      <p><?= count($lowStockProducts); ?> product(s) have low stock (less than 50)!</p>
    </div>
  <?php endif; ?>

  <?php if (empty($lowStockProducts) && empty($criticalStockProducts)): ?>
    <div class="notif">
      <span class="tag green">✔</span>
      <p>All products are sufficiently stocked.</p>
    </div>
  <?php endif; ?>
</div>
    </aside>
  </div>

  <!-- JS -->
  <script src="../Js/admin.js"></script>
</body>
</html>