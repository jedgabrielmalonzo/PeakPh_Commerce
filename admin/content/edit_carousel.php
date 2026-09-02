<?php
require_once('../auth_helper.php');
requireAdminAuth();
$message = '';

// Get the image parameter from URL
if (!isset($_GET['img']) || empty($_GET['img'])) {
    header('Location: carousel.php');
    exit;
}

$imgFile = basename($_GET['img']);
$carouselDir = '../../uploads/carousel/';
$imagePath = 'uploads/carousel/' . $imgFile;

// Check if file exists
if (!file_exists($carouselDir . $imgFile)) {
    header('Location: carousel.php?error=file_not_found');
    exit;
}

// Load the current carousel data
$carouselDataPath = __DIR__ . '/carousel_data.php';
$carouselData = include $carouselDataPath;

// Find the current slide data
$currentSlide = null;
$slideIndex = -1;
foreach ($carouselData as $index => $slide) {
    if ($slide['image'] === $imagePath) {
        $currentSlide = $slide;
        $slideIndex = $index;
        break;
    }
}

if ($slideIndex === -1) {
    // If slide not found in data, create a new entry with defaults
    $currentSlide = [
        'image' => $imagePath,
        'link' => '',
        'button' => '',
        'class' => ''
    ];
    $carouselData[] = $currentSlide;
    $slideIndex = count($carouselData) - 1;
    
    // Save the updated data
    file_put_contents(
        $carouselDataPath,
        "<?php\nreturn " . var_export($carouselData, true) . ";\n"
    );
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_carousel'])) {
    // Update the slide data
    $carouselData[$slideIndex]['link'] = $_POST['link'];
    $carouselData[$slideIndex]['button'] = $_POST['button'];
    $carouselData[$slideIndex]['class'] = $_POST['class'];
    
    // Save the updated data
    if (file_put_contents(
        $carouselDataPath,
        "<?php\nreturn " . var_export($carouselData, true) . ";\n"
    )) {
        $message = "Carousel slide updated successfully!";
        
        // Update current slide for display
        $currentSlide = $carouselData[$slideIndex];
    } else {
        $message = "Error saving carousel data!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Carousel Slide</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="../../Css/admin.css" />
    <style>
        .edit-carousel-container {
            display: flex;
            flex-direction: column;
            max-width: 800px;
            margin: 0 auto;
        }

        .edit-carousel-form {
            background: #f8f8f8;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .preview-container {
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
            font-size: 1em;
        }

        .preview-image {
            width: 100%;
            height: auto;
            max-height: 300px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
        }

        .btn-save {
            background: #27ae60;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-save:hover {
            background: #219150;
        }

        .btn-cancel {
            background: #7f8c8d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            margin-right: 10px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-cancel:hover {
            background: #6c7879;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            font-weight: 600;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>

<body>
    <header>
        <h2>Content Manager &gt; Carousel &gt; Edit Slide</h2>
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
            <a href="carousel.php" class="menu-link active"><i class="bi bi-images"></i> Carousel</a>
            <a href="bestseller.php" class="menu-link"><i class="bi bi-star"></i> Best Seller</a>
            <a href="new_arrivals.php" class="menu-link"><i class="bi bi-lightning"></i> New Arrivals</a>
            <a href="footer.php" class="menu-link"><i class="bi bi-layout-text-window-reverse"></i> Footer</a>
        </div>
    </div>
    <div class="content">
        <h2>Edit Carousel Slide</h2>
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="edit-carousel-container">
            <div class="preview-container">
                <h3>Preview</h3>
                <img src="../../<?= htmlspecialchars($currentSlide['image']) ?>" alt="Carousel Slide" class="preview-image">
            </div>

            <form method="POST" class="edit-carousel-form">
                <div class="form-group">
                    <label for="link">Button Link (URL):</label>
                    <input type="text" id="link" name="link" class="form-control" 
                           value="<?= htmlspecialchars($currentSlide['link']) ?>" 
                           placeholder="e.g., https://example.com or product.php">
                    <small>Leave empty to hide button</small>
                </div>

                <div class="form-group">
                    <label for="button">Button Text:</label>
                    <input type="text" id="button" name="button" class="form-control" 
                           value="<?= htmlspecialchars($currentSlide['button']) ?>" 
                           placeholder="e.g., Shop Now">
                    <small>Leave empty to hide button</small>
                </div>

                <div class="form-group">
                    <label for="class">Custom CSS Class:</label>
                    <input type="text" id="class" name="class" class="form-control" 
                           value="<?= htmlspecialchars($currentSlide['class']) ?>" 
                           placeholder="e.g., dark-slide or light-slide">
                    <small>Add custom CSS classes for styling (optional)</small>
                </div>

                <div class="form-group" style="margin-top: 20px;">
                    <a href="carousel.php" class="btn-cancel"><i class="bi bi-arrow-left"></i> Cancel</a>
                    <button type="submit" name="save_carousel" class="btn-save"><i class="bi bi-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    <script src="../../Js/admin.js"></script>
</body>

</html>