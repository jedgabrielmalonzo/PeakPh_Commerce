# Examples and Demos

This folder contains example files and demonstrations of specific features from the PeakPH Commerce platform.

## 📁 Contents

### **Map Integration Demo**
- `map_demo.php` - Standalone demonstration of the map location selection feature

## 🎯 Purpose

These examples serve as:
- **Documentation** - Show how features work in isolation
- **Testing** - Standalone testing of specific functionality
- **Reference** - Code examples for developers
- **Demos** - Showcase features to stakeholders

## 🚀 Using the Examples

### Map Demo
Access the map demonstration at:
```
http://localhost/PeakPH_Commerce/docs/examples/map_demo.php
```

**Features demonstrated:**
- Interactive map with Leaflet.js
- GPS-based location detection
- Address search functionality
- Coordinate display and storage
- Philippines-focused geocoding

## 🔗 Integration

The code from these examples is integrated into the main application:
- Map functionality is built into `checkout.php`
- Order confirmation shows selected locations in `order_confirmation.php`
- All styling matches the PeakPH theme

## 💡 Development Notes

When modifying the main application's map features, refer to these examples to understand the isolated functionality before making changes to the integrated version.