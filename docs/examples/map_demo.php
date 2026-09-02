<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map Feature Demo - PeakPH Commerce</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f8fffe, #f0f9f7);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        
        .demo-container {
            max-width: 850px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(46, 118, 94, 0.15);
            padding: 40px;
            border: 2px solid rgba(46, 118, 94, 0.1);
        }
        
        .demo-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .demo-header h1 {
            color: #2e765e;
            margin-bottom: 15px;
            font-weight: 700;
            font-size: 2.5rem;
            text-shadow: 2px 2px 4px rgba(46, 118, 94, 0.1);
        }
        
        .demo-header p {
            color: #3da180;
            margin: 0;
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        .map-section {
            border: 3px solid #2e765e;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #f8fffe, #f0f9f7);
            box-shadow: 0 6px 20px rgba(46, 118, 94, 0.1);
        }
        
        .map-controls {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .map-btn {
            background: linear-gradient(135deg, #2e765e, #3da180);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 4px 12px rgba(46, 118, 94, 0.3);
        }
        
        .map-btn:hover {
            background: linear-gradient(135deg, #245a47, #2e765e);
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(46, 118, 94, 0.4);
        }
        
        .map-btn.secondary {
            background: linear-gradient(135deg, #6c757d, #8a9196);
        }
        
        .map-btn.secondary:hover {
            background: linear-gradient(135deg, #545b62, #6c757d);
        }
        
        .map-container {
            height: 450px;
            border-radius: 15px;
            overflow: hidden;
            border: 3px solid #2e765e;
            margin-bottom: 20px;
            box-shadow: 0 6px 20px rgba(46, 118, 94, 0.2);
        }
        
        .location-info {
            background: white;
            border: 2px solid rgba(46, 118, 94, 0.2);
            border-radius: 15px;
            padding: 25px;
            margin-top: 20px;
            box-shadow: 0 4px 15px rgba(46, 118, 94, 0.1);
        }
        
        .location-info h4 {
            color: #2e765e;
            margin-bottom: 20px;
            font-weight: 600;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .location-info p {
            margin: 12px 0;
            color: #2e765e;
            font-weight: 500;
            line-height: 1.6;
        }
        
        .coordinates {
            font-family: 'Courier New', monospace;
            background: linear-gradient(135deg, #2e765e, #3da180);
            color: white;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            text-align: center;
            margin-top: 15px;
        }
        
        .search-section {
            margin-bottom: 20px;
        }
        
        .search-input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid rgba(46, 118, 94, 0.3);
            border-radius: 25px;
            font-size: 1rem;
            margin-bottom: 15px;
            font-family: 'Poppins', sans-serif;
            background: #f8fffe;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #2e765e;
            background: white;
            box-shadow: 0 0 0 4px rgba(46, 118, 94, 0.1);
        }
        
        .search-input::placeholder {
            color: #8a9196;
            font-style: italic;
        }
        
        .instructions {
            background: linear-gradient(135deg, #e8f5f0, #d4edda);
            border: 2px solid rgba(46, 118, 94, 0.3);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(46, 118, 94, 0.1);
        }
        
        .instructions h5 {
            color: #2e765e;
            margin-bottom: 15px;
            font-weight: 600;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .instructions ul {
            color: #2e765e;
            margin: 0;
            padding-left: 25px;
        }
        
        .instructions li {
            margin: 8px 0;
            line-height: 1.5;
            font-weight: 500;
        }
        
        .instructions strong {
            color: #245a47;
        }
    </style>
</head>
<body>
    <div class="demo-container">
        <div class="demo-header">
            <h1><i class="bi bi-geo-alt"></i> Map Location Feature Demo</h1>
            <p>Test the interactive map functionality for delivery location selection</p>
        </div>
        
        <div class="instructions">
            <h5><i class="bi bi-info-circle"></i> How to Use:</h5>
            <ul>
                <li>Click <strong>"Use My Location"</strong> to automatically detect your current position</li>
                <li>Click <strong>"Search Address"</strong> to find a specific location in the Philippines</li>
                <li>Click anywhere on the map to pin a delivery location</li>
                <li>The selected location details will appear below the map</li>
            </ul>
        </div>
        
        <div class="map-section">
            <div class="map-controls">
                <button type="button" class="map-btn" onclick="getCurrentLocation()">
                    <i class="bi bi-crosshair"></i> Use My Location
                </button>
                <button type="button" class="map-btn secondary" onclick="toggleSearch()">
                    <i class="bi bi-search"></i> Search Address
                </button>
                <button type="button" class="map-btn secondary" onclick="clearSelection()">
                    <i class="bi bi-x-circle"></i> Clear Selection
                </button>
            </div>
            
            <div id="search-section" class="search-section" style="display: none;">
                <input type="text" id="location-search" class="search-input" placeholder="Search for a location in the Philippines...">
                <button type="button" class="map-btn" onclick="performSearch()">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>
            
            <div id="map" class="map-container"></div>
            
            <div id="location-info" class="location-info" style="display: none;">
                <h4><i class="bi bi-pin-map"></i> Selected Delivery Location</h4>
                <p><strong>Address:</strong> <span id="selected-address">No location selected</span></p>
                <div class="coordinates" id="selected-coordinates">Lat: -, Lng: -</div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map;
        let currentMarker;

        // Initialize map
        function initMap() {
            // Center on Manila, Philippines
            map = L.map('map').setView([14.5995, 120.9842], 11);
            
            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            
            // Add click event
            map.on('click', function(e) {
                setLocation(e.latlng.lat, e.latlng.lng);
            });
        }

        function getCurrentLocation() {
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by this browser.');
                return;
            }

            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                map.setView([lat, lng], 16);
                setLocation(lat, lng);
            }, function(error) {
                let errorMessage = 'Error getting your location: ';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage += 'Location access denied by user.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage += 'Location information is unavailable.';
                        break;
                    case error.TIMEOUT:
                        errorMessage += 'Location request timed out.';
                        break;
                    default:
                        errorMessage += 'An unknown error occurred.';
                        break;
                }
                alert(errorMessage);
            });
        }

        function toggleSearch() {
            const searchSection = document.getElementById('search-section');
            if (searchSection.style.display === 'none') {
                searchSection.style.display = 'block';
                document.getElementById('location-search').focus();
            } else {
                searchSection.style.display = 'none';
            }
        }

        function performSearch() {
            const query = document.getElementById('location-search').value.trim();
            if (!query) {
                alert('Please enter a location to search for.');
                return;
            }

            // Use Nominatim API for geocoding (Philippines only)
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=ph&limit=1`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const result = data[0];
                        const lat = parseFloat(result.lat);
                        const lng = parseFloat(result.lon);
                        
                        map.setView([lat, lng], 15);
                        setLocation(lat, lng, result.display_name);
                        
                        // Hide search section
                        document.getElementById('search-section').style.display = 'none';
                    } else {
                        alert('Location not found in the Philippines. Please try a different search term.');
                    }
                })
                .catch(error => {
                    console.error('Search error:', error);
                    alert('Error searching for location. Please try again.');
                });
        }

        function setLocation(lat, lng, address = null) {
            // Remove existing marker
            if (currentMarker) {
                map.removeLayer(currentMarker);
            }
            
            // Add new marker with custom popup
            currentMarker = L.marker([lat, lng]).addTo(map);
            currentMarker.bindPopup('<b>Selected Delivery Location</b><br>Click to confirm this location').openPopup();
            
            // Update coordinates display
            document.getElementById('selected-coordinates').textContent = 
                `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
            
            // Show location info
            document.getElementById('location-info').style.display = 'block';
            
            // Get or set address
            if (address) {
                document.getElementById('selected-address').textContent = address;
            } else {
                // Reverse geocode to get address
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                    .then(response => response.json())
                    .then(data => {
                        const address = data.display_name || `Location at ${lat.toFixed(4)}, ${lng.toFixed(4)}`;
                        document.getElementById('selected-address').textContent = address;
                    })
                    .catch(error => {
                        console.error('Reverse geocoding error:', error);
                        document.getElementById('selected-address').textContent = 
                            `Location at ${lat.toFixed(4)}, ${lng.toFixed(4)}`;
                    });
            }
        }

        function clearSelection() {
            if (currentMarker) {
                map.removeLayer(currentMarker);
                currentMarker = null;
            }
            
            document.getElementById('selected-address').textContent = 'No location selected';
            document.getElementById('selected-coordinates').textContent = 'Lat: -, Lng: -';
            document.getElementById('location-info').style.display = 'none';
            document.getElementById('search-section').style.display = 'none';
            document.getElementById('location-search').value = '';
        }

        // Handle Enter key in search input
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            
            document.getElementById('location-search').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    performSearch();
                }
            });
        });
    </script>
</body>
</html>