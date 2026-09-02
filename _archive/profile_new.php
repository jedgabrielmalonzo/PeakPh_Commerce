<?php
require_once 'includes/user_auth.php';
require_once 'includes/db.php';

// Require login for this page
requireLogin();

$current_user = getCurrentUser();
$user_id = $current_user['id'];

// Get user profile data
$profile_query = "SELECT * FROM user_profiles WHERE user_id = ?";
$stmt = $conn->prepare($profile_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

// Initialize cart count
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - PeakPH Commerce</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="Css/Global.css">
    <style>
        .profile-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .profile-header {
            background: linear-gradient(135deg, #2e765e, #3da180);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .profile-header h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
        }
        
        .profile-header p {
            margin: 0;
            opacity: 0.9;
        }
        
        .profile-form {
            background: #fff;
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .form-section {
            margin-bottom: 2.5rem;
        }
        
        .form-section h3 {
            color: #2e765e;
            font-size: 1.4rem;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #2e765e;
            padding-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
            font-size: 0.95rem;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.85rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2e765e;
            box-shadow: 0 0 0 3px rgba(46, 118, 94, 0.1);
        }
        
        .required {
            color: #e74c3c;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin-right: 0.75rem;
            cursor: pointer;
        }
        
        .checkbox-group label {
            cursor: pointer;
            user-select: none;
        }
        
        .save-button {
            background: linear-gradient(135deg, #2e765e, #3da180);
            color: white;
            border: none;
            padding: 1rem 3rem;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(46, 118, 94, 0.3);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 2rem auto 0;
        }
        
        .save-button:hover {
            background: linear-gradient(135deg, #245a47, #2e765e);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(46, 118, 94, 0.4);
        }
        
        .save-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        /* Map Styles */
        .map-section {
            background: #fff;
            border-radius: 12px;
            padding: 1.5rem;
            border: 2px solid rgba(46, 118, 94, 0.1);
            margin-bottom: 1.5rem;
        }
        
        .map-toggle {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .map-toggle input[type="checkbox"] {
            width: auto;
            margin-right: 0.75rem;
            cursor: pointer;
        }
        
        .map-toggle label {
            cursor: pointer;
            font-weight: 600;
            color: #2e765e;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .map-container {
            height: 400px;
            border-radius: 12px;
            overflow: hidden;
            border: 3px solid #2e765e;
            margin-bottom: 1rem;
        }
        
        .map-controls {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        
        .map-btn {
            background: linear-gradient(135deg, #2e765e, #3da180);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .map-btn:hover {
            background: linear-gradient(135deg, #245a47, #2e765e);
            transform: translateY(-2px);
        }
        
        .map-btn.secondary {
            background: linear-gradient(135deg, #6c757d, #8a9196);
        }
        
        .map-btn.secondary:hover {
            background: linear-gradient(135deg, #545b62, #6c757d);
        }
        
        .location-info {
            background: linear-gradient(135deg, #f8fffe, #f0f9f7);
            border: 2px solid rgba(46, 118, 94, 0.2);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1rem;
        }
        
        .location-info h4 {
            color: #2e765e;
            margin-bottom: 1rem;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .location-info p {
            margin: 0.5rem 0;
            font-size: 0.95rem;
            color: #2e765e;
        }
        
        .coordinates {
            font-family: 'Courier New', monospace;
            background: #2e765e;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            display: inline-block;
            margin-top: 0.5rem;
        }
        
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert i {
            font-size: 1.5rem;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav>
        <div class="logo">
            <a href="index.php">PeakPH</a>
        </div>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="ProductCatalog.php">Products</a></li>
            <li><a href="about.php">About</a></li>
        </ul>
        <div class="nav-icons">
            <a href="cart.php" class="cart-icon">
                <i class="bi bi-cart"></i>
                <?php if ($cart_count > 0): ?>
                    <span class="cart-count"><?= $cart_count ?></span>
                <?php endif; ?>
            </a>
            <a href="profile.php" class="user-icon" title="<?= htmlspecialchars($current_user['username']) ?>">
                <i class="bi bi-person-circle"></i>
            </a>
            <a href="logout.php" class="logout-icon" title="Logout">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </nav>

    <div class="profile-container">
        <div class="profile-header">
            <h1><i class="bi bi-person-circle"></i> My Profile</h1>
            <p>Welcome back, <?= htmlspecialchars($current_user['username']) ?>!</p>
            <p style="font-size: 0.9rem; margin-top: 0.5rem;">
                <i class="bi bi-envelope"></i> <?= htmlspecialchars($current_user['email']) ?>
            </p>
        </div>

        <div id="alert-container"></div>

        <form id="profileForm" class="profile-form">
            <!-- Contact Information -->
            <div class="form-section">
                <h3><i class="bi bi-telephone"></i> Contact Information</h3>
                <div class="form-group">
                    <label for="phone">Phone Number <span class="required">*</span></label>
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($profile['phone'] ?? '') ?>" placeholder="+63 912 345 6789" required>
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="form-section">
                <h3><i class="bi bi-house"></i> Shipping Address</h3>
                <div class="form-group">
                    <label for="shipping_address">Street Address <span class="required">*</span></label>
                    <input type="text" id="shipping_address" name="shipping_address" value="<?= htmlspecialchars($profile['shipping_address'] ?? '') ?>" placeholder="House number, street name" required>
                </div>
                <div class="form-group">
                    <label for="shipping_address_2">Apartment, suite, etc. (optional)</label>
                    <input type="text" id="shipping_address_2" name="shipping_address_2" value="<?= htmlspecialchars($profile['shipping_address_2'] ?? '') ?>" placeholder="Apartment, suite, unit, building, floor, etc.">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="shipping_city">City <span class="required">*</span></label>
                        <input type="text" id="shipping_city" name="shipping_city" value="<?= htmlspecialchars($profile['shipping_city'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="shipping_province">Province <span class="required">*</span></label>
                        <select id="shipping_province" name="shipping_province" required>
                            <option value="">Select Province</option>
                            <option value="Metro Manila" <?= ($profile['shipping_province'] ?? '') == 'Metro Manila' ? 'selected' : '' ?>>Metro Manila</option>
                            <option value="Cebu" <?= ($profile['shipping_province'] ?? '') == 'Cebu' ? 'selected' : '' ?>>Cebu</option>
                            <option value="Davao" <?= ($profile['shipping_province'] ?? '') == 'Davao' ? 'selected' : '' ?>>Davao</option>
                            <option value="Laguna" <?= ($profile['shipping_province'] ?? '') == 'Laguna' ? 'selected' : '' ?>>Laguna</option>
                            <option value="Cavite" <?= ($profile['shipping_province'] ?? '') == 'Cavite' ? 'selected' : '' ?>>Cavite</option>
                            <option value="Bulacan" <?= ($profile['shipping_province'] ?? '') == 'Bulacan' ? 'selected' : '' ?>>Bulacan</option>
                            <option value="Rizal" <?= ($profile['shipping_province'] ?? '') == 'Rizal' ? 'selected' : '' ?>>Rizal</option>
                            <option value="Batangas" <?= ($profile['shipping_province'] ?? '') == 'Batangas' ? 'selected' : '' ?>>Batangas</option>
                            <option value="Pampanga" <?= ($profile['shipping_province'] ?? '') == 'Pampanga' ? 'selected' : '' ?>>Pampanga</option>
                            <option value="Nueva Ecija" <?= ($profile['shipping_province'] ?? '') == 'Nueva Ecija' ? 'selected' : '' ?>>Nueva Ecija</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="shipping_postal_code">Postal Code <span class="required">*</span></label>
                        <input type="text" id="shipping_postal_code" name="shipping_postal_code" value="<?= htmlspecialchars($profile['shipping_postal_code'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="shipping_country">Country</label>
                        <input type="text" id="shipping_country" name="shipping_country" value="Philippines" readonly>
                    </div>
                </div>
            </div>

            <!-- Map Location Selector -->
            <div class="form-section map-section">
                <div class="map-toggle">
                    <input type="checkbox" id="use_map_location" name="use_map_location" <?= !empty($profile['map_latitude']) ? 'checked' : '' ?> onchange="toggleMapLocation()">
                    <label for="use_map_location">
                        <i class="bi bi-geo-alt"></i> Pin my exact delivery location on map
                    </label>
                </div>
                
                <div id="map-content" style="display: <?= !empty($profile['map_latitude']) ? 'block' : 'none' ?>;">
                    <div class="map-controls">
                        <button type="button" class="map-btn" onclick="getCurrentLocation()">
                            <i class="bi bi-crosshair"></i> Use My Location
                        </button>
                        <button type="button" class="map-btn secondary" onclick="searchLocation()">
                            <i class="bi bi-search"></i> Search Address
                        </button>
                        <button type="button" class="map-btn secondary" onclick="clearMapSelection()">
                            <i class="bi bi-x-circle"></i> Clear Selection
                        </button>
                    </div>
                    
                    <div id="search-input" style="display: none; margin-bottom: 1rem;">
                        <input type="text" id="location-search" placeholder="Search for a location..." style="width: 100%; padding: 0.75rem; border: 2px solid #ddd; border-radius: 8px; font-family: 'Poppins', sans-serif;">
                        <button type="button" class="map-btn" onclick="performSearch()" style="margin-top: 0.75rem;">Search</button>
                    </div>
                    
                    <div id="map" class="map-container"></div>
                    
                    <div id="location-info" class="location-info" style="display: <?= !empty($profile['map_latitude']) ? 'block' : 'none' ?>;">
                        <h4><i class="bi bi-pin-map"></i> Selected Location</h4>
                        <p id="selected-address"><?= htmlspecialchars($profile['map_address'] ?? 'No location selected') ?></p>
                        <p class="coordinates" id="selected-coordinates">
                            Lat: <?= $profile['map_latitude'] ?? '-' ?>, Lng: <?= $profile['map_longitude'] ?? '-' ?>
                        </p>
                        <input type="hidden" id="map_latitude" name="map_latitude" value="<?= $profile['map_latitude'] ?? '' ?>">
                        <input type="hidden" id="map_longitude" name="map_longitude" value="<?= $profile['map_longitude'] ?? '' ?>">
                        <input type="hidden" id="map_address" name="map_address" value="<?= htmlspecialchars($profile['map_address'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Billing Address -->
            <div class="form-section">
                <h3><i class="bi bi-credit-card"></i> Billing Address</h3>
                <div class="checkbox-group">
                    <input type="checkbox" id="same_as_shipping" name="same_as_shipping" <?= ($profile['billing_same_as_shipping'] ?? 1) ? 'checked' : '' ?> onchange="toggleBillingAddress()">
                    <label for="same_as_shipping">Same as shipping address</label>
                </div>
                
                <div id="billing_address_section" style="display: <?= ($profile['billing_same_as_shipping'] ?? 1) ? 'none' : 'block' ?>;">
                    <div class="form-group">
                        <label for="billing_address">Street Address <span class="required">*</span></label>
                        <input type="text" id="billing_address" name="billing_address" value="<?= htmlspecialchars($profile['billing_address'] ?? '') ?>" placeholder="House number, street name">
                    </div>
                    <div class="form-group">
                        <label for="billing_address_2">Apartment, suite, etc. (optional)</label>
                        <input type="text" id="billing_address_2" name="billing_address_2" value="<?= htmlspecialchars($profile['billing_address_2'] ?? '') ?>" placeholder="Apartment, suite, unit, building, floor, etc.">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="billing_city">City <span class="required">*</span></label>
                            <input type="text" id="billing_city" name="billing_city" value="<?= htmlspecialchars($profile['billing_city'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="billing_province">Province <span class="required">*</span></label>
                            <select id="billing_province" name="billing_province">
                                <option value="">Select Province</option>
                                <option value="Metro Manila" <?= ($profile['billing_province'] ?? '') == 'Metro Manila' ? 'selected' : '' ?>>Metro Manila</option>
                                <option value="Cebu" <?= ($profile['billing_province'] ?? '') == 'Cebu' ? 'selected' : '' ?>>Cebu</option>
                                <option value="Davao" <?= ($profile['billing_province'] ?? '') == 'Davao' ? 'selected' : '' ?>>Davao</option>
                                <option value="Laguna" <?= ($profile['billing_province'] ?? '') == 'Laguna' ? 'selected' : '' ?>>Laguna</option>
                                <option value="Cavite" <?= ($profile['billing_province'] ?? '') == 'Cavite' ? 'selected' : '' ?>>Cavite</option>
                                <option value="Bulacan" <?= ($profile['billing_province'] ?? '') == 'Bulacan' ? 'selected' : '' ?>>Bulacan</option>
                                <option value="Rizal" <?= ($profile['billing_province'] ?? '') == 'Rizal' ? 'selected' : '' ?>>Rizal</option>
                                <option value="Batangas" <?= ($profile['billing_province'] ?? '') == 'Batangas' ? 'selected' : '' ?>>Batangas</option>
                                <option value="Pampanga" <?= ($profile['billing_province'] ?? '') == 'Pampanga' ? 'selected' : '' ?>>Pampanga</option>
                                <option value="Nueva Ecija" <?= ($profile['billing_province'] ?? '') == 'Nueva Ecija' ? 'selected' : '' ?>>Nueva Ecija</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="billing_postal_code">Postal Code <span class="required">*</span></label>
                            <input type="text" id="billing_postal_code" name="billing_postal_code" value="<?= htmlspecialchars($profile['billing_postal_code'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="billing_country">Country</label>
                            <input type="text" id="billing_country" name="billing_country" value="Philippines" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="save-button">
                <i class="bi bi-check-circle"></i> Save Profile Information
            </button>
        </form>
    </div>

    <!-- Leaflet Map JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map = null;
        let marker = null;
        const defaultLat = <?= $profile['map_latitude'] ?? '14.5995' ?>;
        const defaultLng = <?= $profile['map_longitude'] ?? '120.9842' ?>;

        function toggleMapLocation() {
            const mapContent = document.getElementById('map-content');
            const checkbox = document.getElementById('use_map_location');
            
            if (checkbox.checked) {
                mapContent.style.display = 'block';
                if (!map) {
                    initMap();
                }
            } else {
                mapContent.style.display = 'none';
                clearMapSelection();
            }
        }

        function initMap() {
            if (!map) {
                map = L.map('map').setView([defaultLat, defaultLng], 13);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);
                
                // Add existing marker if coordinates exist
                if (defaultLat && defaultLng && defaultLat !== 14.5995) {
                    marker = L.marker([defaultLat, defaultLng]).addTo(map);
                }
                
                map.on('click', function(e) {
                    updateMarker(e.latlng.lat, e.latlng.lng);
                });
            }
        }

        function getCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        map.setView([lat, lng], 15);
                        updateMarker(lat, lng);
                    },
                    function(error) {
                        alert('Error getting location: ' + error.message);
                    }
                );
            } else {
                alert('Geolocation is not supported by your browser');
            }
        }

        function searchLocation() {
            const searchInput = document.getElementById('search-input');
            searchInput.style.display = searchInput.style.display === 'none' ? 'block' : 'none';
        }

        function performSearch() {
            const query = document.getElementById('location-search').value;
            if (query) {
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.length > 0) {
                            const lat = parseFloat(data[0].lat);
                            const lng = parseFloat(data[0].lon);
                            map.setView([lat, lng], 15);
                            updateMarker(lat, lng, data[0].display_name);
                        } else {
                            alert('Location not found');
                        }
                    })
                    .catch(error => {
                        alert('Error searching location');
                    });
            }
        }

        function updateMarker(lat, lng, address = null) {
            if (marker) {
                map.removeLayer(marker);
            }
            
            marker = L.marker([lat, lng]).addTo(map);
            
            if (!address) {
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                    .then(response => response.json())
                    .then(data => {
                        displayLocationInfo(lat, lng, data.display_name);
                    })
                    .catch(() => {
                        displayLocationInfo(lat, lng, 'Address not available');
                    });
            } else {
                displayLocationInfo(lat, lng, address);
            }
        }

        function displayLocationInfo(lat, lng, address) {
            document.getElementById('selected-address').textContent = address;
            document.getElementById('selected-coordinates').textContent = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
            document.getElementById('map_latitude').value = lat;
            document.getElementById('map_longitude').value = lng;
            document.getElementById('map_address').value = address;
            document.getElementById('location-info').style.display = 'block';
        }

        function clearMapSelection() {
            if (marker && map) {
                map.removeLayer(marker);
                marker = null;
            }
            document.getElementById('map_latitude').value = '';
            document.getElementById('map_longitude').value = '';
            document.getElementById('map_address').value = '';
            document.getElementById('location-info').style.display = 'none';
            document.getElementById('selected-address').textContent = 'No location selected';
            document.getElementById('selected-coordinates').textContent = 'Lat: -, Lng: -';
        }

        function toggleBillingAddress() {
            const billingSection = document.getElementById('billing_address_section');
            const sameAsShipping = document.getElementById('same_as_shipping').checked;
            billingSection.style.display = sameAsShipping ? 'none' : 'block';
        }

        // Form submission
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('.save-button');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';
            
            const formData = new FormData(this);
            
            fetch('save_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showAlert(data.success ? 'success' : 'error', data.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                
                if (data.success) {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            })
            .catch(error => {
                showAlert('error', 'An error occurred while saving your profile');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });

        function showAlert(type, message) {
            const container = document.getElementById('alert-container');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.innerHTML = `
                <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'}"></i>
                <span>${message}</span>
            `;
            
            container.innerHTML = '';
            container.appendChild(alert);
            
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }

        // Initialize map if checkbox is checked on load
        if (document.getElementById('use_map_location').checked) {
            setTimeout(initMap, 100);
        }
    </script>
</body>
</html>
