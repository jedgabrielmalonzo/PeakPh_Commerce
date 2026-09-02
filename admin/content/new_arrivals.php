<?php
// New Arrivals Content Manager (admin) - Connected to Inventory
require_once('../auth_helper.php');
require_once('../../includes/db.php');
requireAdminAuth();
$message = '';

// Fetch New Arrival products directly from inventory
$arrivals = [];
if (isDatabaseConnected()) {
    try {
        // Get products with "New Arrival" or similar labels
        $query = "SELECT id, product_name as name, price, image, tag, stock, label, created_at 
                  FROM inventory 
                  WHERE label LIKE '%New%' OR label LIKE '%🆕%' OR label LIKE '%Arrival%'
                  ORDER BY created_at DESC";
        $result = executeQuery($query);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Calculate random reviews and rating for display
                $reviewCount = rand(50, 300);
                $rating = number_format(rand(40, 50) / 10, 1); // 4.0 to 5.0
                $stars = str_repeat('⭐', floor($rating)) . (($rating - floor($rating)) >= 0.5 ? '☆' : '');
                
                // Fix image path
                $image_path = 'Assets/placeholder.svg';
                if (!empty($row['image'])) {
                    if (file_exists('../../admin/' . $row['image'])) {
                        $image_path = 'admin/' . $row['image'];
                    } elseif (file_exists('../../' . $row['image'])) {
                        $image_path = $row['image'];
                    }
                }
                
                $arrivals[] = [
                    'id' => $row['id'],
                    'link' => 'ProductView.php?id=' . $row['id'],
                    'image' => $image_path,
                    'alt' => $row['name'],
                    'name' => $row['name'],
                    'price' => number_format($row['price'], 2),
                    'label' => $row['label'] ?? 'New Arrival',
                    'stock' => $row['stock'],
                    'rating' => $stars,
                    'reviews' => $reviewCount
                ];
            }
        }
    } catch (Exception $e) {
        $message = "Error loading products: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>New Arrivals Content Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="../../Css/admin.css" />
    <style>
        .form-section { background: #f8f8f8; padding: 18px 24px; border-radius: 10px; margin-bottom: 30px; }
        .arrival-grid { display: flex; flex-wrap: wrap; gap: 20px; }
        .arrival-card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            padding: 15px;
            width: 220px;
            text-align: center;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .arrival-card img {
            width: 120px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
            background: #f5f5f5;
            box-shadow: 0 1px 4px rgba(0,0,0,0.07);
            border: 1px solid #e0e0e0;
        }
        .arrival-card .actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }
        .arrival-card .delete-btn {
            background: #e74c3c;
            color: #fff;
            border: none;
            padding: 5px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.95em;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .arrival-card .delete-btn:hover { background: #c0392b; }
        .arrival-card .edit-btn {
            background: #3498db;
            color: #fff;
            border: none;
            padding: 5px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.95em;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .arrival-card .edit-btn:hover { background: #217dbb; }
        .btn-add { background: #27ae60; color: #fff; margin-top: 10px; border: none; padding: 7px 18px; border-radius: 5px; font-weight: 600; }
        .btn-add:hover { background: #219150; }
    </style>
</head>
<body>
    <header>
        <h2>Content Manager &gt; New Arrivals</h2>
        <button onclick="logout()">Logout</button>
    </header>
    <div class="sidebar">
        <h3>Menu</h3>
        <a href="../admin.php" class="menu-link"><i class="bi bi-house"></i> Admin Home</a>
        <a href="../dashboard.php" class="menu-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="../mini-view.php" class="menu-link"><i class="bi bi-pencil-square"></i> Mini View</a>
        <a href="../inventory/inventory.php" class="menu-link"><i class="bi bi-box"></i> Inventory</a>
        <a href="../orders.php" class="menu-link"><i class="bi bi-bag"></i> Orders</a>
        <a href="../users/users.php" class="menu-link"><i class="bi bi-people"></i> Users</a>
        <button class="collapsible" onclick="toggleContentManager()">
            <i class="bi bi-folder"></i> Content Manager
            <span id="arrow" style="float:right;">&#9660;</span>
        </button>
        <div class="content-manager-links" id="contentManagerLinks" style="display:block; margin-left: 15px;">
            <a href="carousel.php" class="menu-link"><i class="bi bi-images"></i> Carousel</a>
            <a href="bestseller.php" class="menu-link"><i class="bi bi-star"></i> Best Seller</a>
            <a href="new_arrivals.php" class="menu-link active"><i class="bi bi-lightning"></i> New Arrivals</a>
            <a href="footer.php" class="menu-link"><i class="bi bi-layout-text-window-reverse"></i> Footer</a>
        </div>
    </div>
    <div class="content">
        <?php if ($message): ?>
            <div style="color: green; margin-bottom: 18px; font-weight: 600; font-size: 1.1em;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        <div class="form-section">
            <h3>New Arrivals (Live from Inventory)</h3>
            <div style="background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <strong><i class="bi bi-info-circle"></i> 🔗 Connected to Inventory Database</strong><br>
                New Arrivals are automatically pulled from your <a href="../inventory/inventory.php" style="color: #0c5460; font-weight: bold;">Inventory</a>!<br>
                <strong>How it works:</strong><br>
                • Go to <a href="../inventory/inventory.php" style="color: #0c5460; font-weight: bold;">Inventory Management</a><br>
                • Click "Label" on any product<br>
                • Select "🆕 New Arrival" or "New"<br>
                • Products will automatically appear here and on the homepage<br>
                • All prices, stock, and images are synced in real-time!
            </div>
            
            <?php if (empty($arrivals)): ?>
                <div style="background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <strong><i class="bi bi-exclamation-triangle"></i> No New Arrival Products Found</strong><br>
                    Go to <a href="../inventory/inventory.php" style="color: #856404; font-weight: bold;">Inventory</a> and label some products as "New Arrival" to see them here.
                </div>
            <?php endif; ?>
            
            <div class="arrival-grid">
                <?php foreach ($arrivals as $a): ?>
                <div class="arrival-card">
                    <img src="../../<?= htmlspecialchars($a['image']) ?>" alt="<?= htmlspecialchars($a['alt']) ?>">
                    <div style="background: #17a2b8; color: white; padding: 3px 8px; border-radius: 4px; font-size: 0.85em; margin-bottom: 8px;">
                        <i class="bi bi-link-45deg"></i> Live from Inventory
                    </div>
                    <div style="font-weight:600; font-size:1.1em; margin-bottom:2px;"> <?= htmlspecialchars($a['name']) ?> </div>
                    <div style="color:#888; font-size:0.9em; margin-bottom:4px;"> <?= htmlspecialchars($a['label']) ?> </div>
                    <div style="font-size:0.9em; color:#555; margin-bottom:4px;">
                        <?= htmlspecialchars($a['rating']) ?> (<?= htmlspecialchars($a['reviews']) ?> reviews)
                    </div>
                    <div style="font-size:1.05em; font-weight:600; color:#27ae60; margin-bottom:6px;">₱<?= htmlspecialchars($a['price']) ?></div>
                    <div style="font-size:0.9em; color:#666; margin-bottom:8px;">Stock: <?= htmlspecialchars($a['stock']) ?> units</div>
                    <div class="actions">
                        <a href="../inventory/inventory.php" class="edit-btn" title="Edit in Inventory">
                            <i class="bi bi-box"></i> View in Inventory
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div style="margin-top: 20px; padding: 15px; background: #e7f3ff; border: 1px solid #b3d9ff; border-radius: 8px;">
                <strong><i class="bi bi-lightbulb"></i> Pro Tip:</strong> 
                To add or edit New Arrival products, go to <a href="../inventory/inventory.php" style="color: #0066cc; font-weight: bold;">Inventory Management</a> and manage product labels there. 
                All changes are reflected automatically!
            </div>
        </div>
    </div>
    <script>
        function toggleContentManager() {
            var links = document.getElementById('contentManagerLinks');
            var arrow = document.getElementById('arrow');
            if (links.style.display === 'none') {
                links.style.display = 'block';
                arrow.innerHTML = '&#9660;';
            } else {
                links.style.display = 'none';
                arrow.innerHTML = '&#9654;';
            }
        }
    </script>
    <script src="../../Js/admin.js"></script>
</body>
</html>
