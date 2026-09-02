<?php
require_once('../auth_helper.php');
requireAdminAuth();
$message = '';

// Function to get carousel data
function getCarouselData() {
    $carouselDataPath = __DIR__ . '/carousel_data.php';
    if (file_exists($carouselDataPath)) {
        return include $carouselDataPath;
    }
    return [];
}

// Function to update carousel data
function updateCarouselData($preserveData = true) {
    $images = [];
    $carouselDir = "../../uploads/carousel/";
    if (is_dir($carouselDir)) {
        $images = array_diff(scandir($carouselDir), ['.', '..']);
    }
    
    $carouselDataPath = __DIR__ . '/carousel_data.php';
    $carouselArray = [];
    
    // If we want to preserve existing data
    if ($preserveData && file_exists($carouselDataPath)) {
        $existingData = include $carouselDataPath;
        
        // Create a lookup for existing slides
        $existingSlides = [];
        foreach ($existingData as $slide) {
            $key = basename($slide['image']);
            $existingSlides[$key] = $slide;
        }
        
        // Build updated array preserving existing data
        foreach ($images as $img) {
            $imgPath = 'uploads/carousel/' . $img;
            if (isset($existingSlides[$img])) {
                // Keep existing data
                $carouselArray[] = $existingSlides[$img];
            } else {
                // Add new slide with default values
                $carouselArray[] = [
                    'image' => $imgPath,
                    'link' => '',
                    'button' => '',
                    'class' => ''
                ];
            }
        }
    } else {
        // Create completely new data
        foreach ($images as $img) {
            $carouselArray[] = [
                'image' => 'uploads/carousel/' . $img,
                'link' => '',
                'button' => '',
                'class' => ''
            ];
        }
    }
    
    return file_put_contents(
        $carouselDataPath,
        "<?php\nreturn " . var_export($carouselArray, true) . ";\n"
    );
}

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['carousel_image'])) {
    $targetDir = "../../uploads/carousel/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    
    // Generate filename
    $fileName = basename($_FILES["carousel_image"]["name"]);
    $targetFile = $targetDir . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($imageFileType, $allowed)) {
        if (move_uploaded_file($_FILES["carousel_image"]["tmp_name"], $targetFile)) {
            $message = "Image uploaded successfully!";
            
            // Get the data we will update
            $carouselData = getCarouselData();
            
            // Add the new slide with form data
            $newSlide = [
                'image' => 'uploads/carousel/' . $fileName,
                'link' => $_POST['link'] ?? '',
                'button' => $_POST['button'] ?? '',
                'class' => $_POST['class'] ?? ''
            ];
            
            // Append the new slide
            $carouselData[] = $newSlide;
            
            // Save the updated data
            $carouselDataPath = __DIR__ . '/carousel_data.php';
            file_put_contents(
                $carouselDataPath,
                "<?php\nreturn " . var_export($carouselData, true) . ";\n"
            );
        } else {
            $message = "Error uploading image.";
        }
    } else {
        $message = "Invalid file type.";
    }
    // Redirect to avoid form resubmission
    header("Location: carousel.php");
    exit;
}

// Handle delete image
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $deleteFile = basename($_GET['delete']);
    $filePath = "../../uploads/carousel/" . $deleteFile;
    
    // Get current data to preserve it after deletion
    $carouselData = getCarouselData();
    
    // Filter out the slide to delete
    $filteredData = array_filter($carouselData, function($slide) use ($deleteFile) {
        return basename($slide['image']) !== $deleteFile;
    });
    
    // Reindex array
    $filteredData = array_values($filteredData);
    
    // Update carousel data
    $carouselDataPath = __DIR__ . '/carousel_data.php';
    file_put_contents(
        $carouselDataPath,
        "<?php\nreturn " . var_export($filteredData, true) . ";\n"
    );
    
    // Delete the file
    if (file_exists($filePath)) {
        unlink($filePath);
        $message = "Image deleted successfully!";
    }
    
    // Redirect to avoid URL parameters staying
    header("Location: carousel.php");
    exit;
}

// Handle slide reordering
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reorder') {
    if (isset($_POST['order']) && is_array($_POST['order'])) {
        $carouselData = getCarouselData();
        $newOrder = [];
        
        // Create a new array with the slides in the new order
        foreach ($_POST['order'] as $index) {
            if (isset($carouselData[$index])) {
                $newOrder[] = $carouselData[$index];
            }
        }
        
        // Save the reordered data if we have all slides
        if (count($newOrder) === count($carouselData)) {
            $carouselDataPath = __DIR__ . '/carousel_data.php';
            file_put_contents(
                $carouselDataPath,
                "<?php\nreturn " . var_export($newOrder, true) . ";\n"
            );
            echo json_encode(['success' => true]);
            exit;
        }
    }
    echo json_encode(['success' => false, 'message' => 'Invalid order data']);
    exit;
}

// Get carousel data
$carouselData = getCarouselData();

// Get all carousel images for display
$images = [];
$carouselDir = "../../uploads/carousel/";
if (is_dir($carouselDir)) {
    $images = array_diff(scandir($carouselDir), ['.', '..']);
}

// Check for missing images in carousel_data.php and update if needed
$update = false;
foreach ($carouselData as $slide) {
    $slideImage = basename($slide['image']);
    if (!in_array($slideImage, $images)) {
        $update = true;
        break;
    }
}
foreach ($images as $img) {
    $found = false;
    foreach ($carouselData as $slide) {
        if (basename($slide['image']) === $img) {
            $found = true;
            break;
        }
    }
    if (!$found) {
        $update = true;
        break;
    }
}

// Update carousel data if needed
if ($update) {
    updateCarouselData();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Carousel Content Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="../../Css/admin.css" />
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <style>
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: 600;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .carousel-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .carousel-card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            padding: 15px;
            width: 250px;
            text-align: center;
            position: relative;
            transition: box-shadow 0.2s;
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: grab;
        }

        .carousel-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.10);
        }

        .carousel-card img {
            width: 220px;
            height: 140px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
            background: #f5f5f5;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.07);
            border: 1px solid #e0e0e0;
        }

        .carousel-card .drag-handle {
            position: absolute;
            top: 5px;
            left: 5px;
            color: #888;
            font-size: 1.2em;
            cursor: grab;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .carousel-card .drag-handle:hover {
            opacity: 1;
        }

        .carousel-card .slide-info {
            width: 100%;
            margin-bottom: 10px;
        }

        .carousel-card .filename {
            font-size: 0.95em;
            color: #555;
            margin-bottom: 8px;
            word-break: break-all;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .carousel-card .link-badge, 
        .carousel-card .class-badge {
            display: inline-block;
            font-size: 0.8em;
            padding: 2px 8px;
            border-radius: 12px;
            margin: 3px;
            color: #fff;
        }
        
        .carousel-card .link-badge {
            background: #3498db;
        }
        
        .carousel-card .class-badge {
            background: #9b59b6;
        }

        .carousel-card .actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: auto;
        }

        .carousel-card .actions form,
        .carousel-card .actions a {
            display: inline-block;
        }

        .carousel-card .delete-btn {
            background: #e74c3c;
            color: #fff;
            border: none;
            padding: 5px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.95em;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .carousel-card .delete-btn:hover {
            background: #c0392b;
        }

        .carousel-card .edit-btn {
            background: #3498db;
            color: #fff;
            border: none;
            padding: 5px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.95em;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .carousel-card .edit-btn:hover {
            background: #217dbb;
        }

        /* Modal styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            position: relative;
            width: 90%;
            max-width: 600px;
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 20px;
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close-btn:hover {
            color: #333;
        }

        /* Form styling */
        .form-group {
            margin-bottom: 20px;
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
            font-size: 1em;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        /* Button styling */
        .btn-add {
            background: #27ae60;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: background 0.2s;
        }

        .btn-add:hover {
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
            transition: background 0.2s;
        }

        .btn-cancel:hover {
            background: #6c7879;
        }

        .btn-save {
            background: #27ae60;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-save:hover {
            background: #219150;
        }

        /* Drag and drop area */
        #drop-area {
            border: 2px dashed #27ae60;
            border-radius: 8px;
            padding: 30px 20px;
            text-align: center;
            background: #f8fff8;
            margin-top: 10px;
            cursor: pointer;
            transition: border-color 0.2s;
        }

        #drop-area:hover {
            border-color: #219150;
        }

        #drop-area.highlight {
            border-color: #219150;
            background: #e8f7e8;
        }

        #drop-area input {
            display: none;
        }

        #drop-text {
            color: #27ae60;
            font-weight: 600;
            font-size: 1.05em;
        }

        #imagePreview {
            display: none;
            max-width: 100%;
            max-height: 200px;
            margin-top: 15px;
            border-radius: 5px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        }
        
        /* Instruction box */
        .instruction-box {
            background: #f0f7fb;
            border-left: 5px solid #3498db;
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: 0 5px 5px 0;
        }
        
        .instruction-box h3 {
            margin-top: 0;
            color: #2980b9;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .instruction-box ol {
            margin: 10px 0 0 20px;
            padding: 0;
        }
        
        .instruction-box li {
            margin-bottom: 5px;
            color: #444;
        }
        
        /* Placeholder for empty state */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background: #f8f8f8;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .empty-state i {
            font-size: 3em;
            color: #bbb;
            margin-bottom: 15px;
        }
        
        .empty-state h4 {
            color: #777;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #999;
        }

        /* Sortable placeholder */
        .ui-sortable-placeholder {
            border: 2px dashed #3498db;
            visibility: visible !important;
            background: #eef7fd !important;
            box-shadow: none !important;
            height: 270px;
        }
        
        .ui-sortable-helper {
            cursor: grabbing;
        }
    </style>
</head>

<body>
    <header>
        <h2>Content Manager &gt; Carousel</h2>
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
        <h2>Carousel Content Manager</h2>
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- Button to add new slide -->
        <button class="btn-add" style="margin-bottom:20px;" onclick="openAddModal()">
            <i class="bi bi-plus-circle"></i> Add New Slide
        </button>

        <div class="instruction-box">
            <h3><i class="bi bi-info-circle"></i> How to use the carousel manager</h3>
            <ol>
                <li>Add new slides using the "Add New Slide" button</li>
                <li>Edit slides to add links, button text, and CSS classes</li>
                <li>Drag and drop slides to reorder them (changes save automatically)</li>
                <li>Use custom CSS classes to change text color and position</li>
            </ol>
        </div>
        
        <h3>Current Carousel Slides:</h3>
        
        <!-- Reorderable carousel grid -->
        <div class="carousel-grid" id="sortable-slides">
            <?php foreach ($carouselData as $index => $slide): ?>
                <div class="carousel-card" data-index="<?= $index ?>">
                    <div class="drag-handle"><i class="bi bi-grip-vertical"></i></div>
                    <img src="<?= '../../' . htmlspecialchars($slide['image']) ?>" alt="Carousel Image">
                    <div class="slide-info">
                        <div class="filename"><?= htmlspecialchars(basename($slide['image'])) ?></div>
                        <?php if (!empty($slide['link']) && !empty($slide['button'])): ?>
                            <div class="link-badge">
                                <i class="bi bi-link"></i> <?= htmlspecialchars($slide['button']) ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($slide['class'])): ?>
                            <div class="class-badge">
                                <i class="bi bi-brush"></i> <?= htmlspecialchars($slide['class']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="actions">
                        <form method="get" onsubmit="return confirm('Are you sure you want to delete this slide?');" style="display:inline;">
                            <input type="hidden" name="delete" value="<?= htmlspecialchars(basename($slide['image'])) ?>">
                            <button type="submit" class="delete-btn"><i class="bi bi-trash"></i> Delete</button>
                        </form>
                        <a href="edit_carousel.php?img=<?= urlencode(basename($slide['image'])) ?>" class="edit-btn"><i class="bi bi-pencil"></i> Edit</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Add Slide Modal -->
        <div id="addSlideModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeAddModal()">&times;</span>
                <h3>Add New Carousel Slide</h3>
                
                <form method="POST" enctype="multipart/form-data" id="addSlideForm">
                    <div class="form-group">
                        <label for="carousel_image">Slide Image:</label>
                        <div id="drop-area">
                            <input type="file" name="carousel_image" id="carousel_image" accept="image/*" required>
                            <div id="drop-text">
                                <i class="bi bi-cloud-arrow-up"></i> Drag & drop or click to select image
                            </div>
                            <img id="imagePreview" src="" alt="Preview">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="link">Button Link (URL):</label>
                        <input type="text" name="link" id="link" class="form-control" 
                               placeholder="e.g., https://example.com or product.php">
                        <small>Leave empty to hide button</small>
                    </div>

                    <div class="form-group">
                        <label for="button">Button Text:</label>
                        <input type="text" name="button" id="button" class="form-control" 
                               placeholder="e.g., Shop Now">
                        <small>Leave empty to hide button</small>
                    </div>

                    <div class="form-group">
                        <label for="class">Custom CSS Class:</label>
                        <input type="text" name="class" id="class" class="form-control" 
                               placeholder="e.g., dark-slide or light-slide">
                        <small>Add custom CSS classes for styling (optional)</small>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn-cancel" onclick="closeAddModal()">Cancel</button>
                        <button type="submit" class="btn-save">
                            <i class="bi bi-plus-circle"></i> Add Slide
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        // Initialize sortable for carousel slides
        $(function() {
            $("#sortable-slides").sortable({
                handle: ".drag-handle",
                placeholder: "ui-sortable-placeholder",
                cursor: "move",
                update: function(event, ui) {
                    const newOrder = $(this).sortable("toArray", {attribute: "data-index"});
                    
                    // Send the new order to the server
                    $.ajax({
                        url: "carousel.php",
                        type: "POST",
                        data: {
                            action: "reorder",
                            order: newOrder
                        },
                        dataType: "json",
                        success: function(response) {
                            if (response.success) {
                                // Show a brief success message
                                const message = $("<div>")
                                    .addClass("alert alert-success")
                                    .text("Order updated successfully!")
                                    .css({
                                        position: "fixed",
                                        top: "20px",
                                        right: "20px",
                                        zIndex: 9999,
                                        padding: "10px 20px",
                                        borderRadius: "5px",
                                        opacity: 0
                                    })
                                    .appendTo("body");
                                
                                message.animate({opacity: 1}, 300);
                                
                                setTimeout(function() {
                                    message.animate({opacity: 0}, 300, function() {
                                        $(this).remove();
                                    });
                                }, 2000);
                            }
                        }
                    });
                }
            });
        });

        // Modal functionality
        const addSlideModal = document.getElementById("addSlideModal");
        
        function openAddModal() {
            addSlideModal.style.display = "flex";
        }
        
        function closeAddModal() {
            addSlideModal.style.display = "none";
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target === addSlideModal) {
                closeAddModal();
            }
        }
        
        // Handle drag and drop for file upload
        const dropArea = document.getElementById('drop-area');
        const fileInput = document.getElementById('carousel_image');
        const imagePreview = document.getElementById('imagePreview');
        
        // Prevent default behavior for dragover and drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        // Highlight drop area when dragging over it
        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight() {
            dropArea.classList.add('highlight');
        }
        
        function unhighlight() {
            dropArea.classList.remove('highlight');
        }
        
        // Handle dropped files
        dropArea.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
            
            if (files.length > 0) {
                showImagePreview(files[0]);
            }
        }
        
        // Handle file selection via input
        dropArea.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                showImagePreview(this.files[0]);
            }
        });
        
        // Show image preview
        function showImagePreview(file) {
            if (file.type.match('image.*')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                }
                reader.readAsDataURL(file);
                
                // Show file name in drop area
                document.getElementById('drop-text').innerHTML = `<i class="bi bi-check-circle"></i> ${file.name}`;
            }
        }
    </script>
    <script src="../../Js/admin.js"></script>
</body>

</html>