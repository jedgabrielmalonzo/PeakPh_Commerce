# Enhanced Carousel Management System

## Overview
The carousel management system allows administrators to easily manage the homepage carousel slides. With this enhanced system, you can now:

1. Upload and manage carousel images
2. Add buttons with custom links to each slide
3. Apply custom CSS classes for styling
4. Reorder slides by dragging and dropping
5. Preview slides directly in the admin panel

## How to Use

### Adding New Slides
1. Click the "Add New Slide" button
2. Upload an image by dragging and dropping or clicking to select
3. Add an optional button link (URL) and button text
4. Select a custom CSS class if desired
5. Click "Add Slide" to save

### Editing Slides
1. Click the "Edit" button on any slide
2. Modify the link URL, button text, or CSS class
3. Click "Save Changes" to update

### Reordering Slides
Simply drag and drop slides to reorder them. Changes save automatically.

### Deleting Slides
Click the "Delete" button on any slide. Confirm when prompted.

## Available CSS Classes

The following custom CSS classes can be applied to slides:

### Button Style Classes
- `dark-slide`: Dark button style (good for light backgrounds)
- `light-slide`: Light button style (good for dark backgrounds)
- `blue-slide`: Blue button style
- `red-slide`: Red button style

### Button Position Classes
- `btn-top-left`: Places button at top left
- `btn-top-right`: Places button at top right
- `btn-bottom-left`: Places button at bottom left
- `btn-bottom-right`: Places button at bottom right
- `btn-center`: Places button in center of slide

### Text Overlay Classes
- `text-overlay`: Adds a text overlay area for additional content
- `dark-text`: Changes text overlay to dark color (for light backgrounds)

## Examples

### Example 1: Shop Now button in bottom right (default)
```
CSS Class: blue-slide
Button Text: Shop Now
Link: /products.php
```

### Example 2: Light button in top left
```
CSS Class: light-slide btn-top-left
Button Text: View Collection
Link: /collection.php
```

### Example 3: Centered button with overlay
```
CSS Class: dark-slide btn-center text-overlay
Button Text: Learn More
Link: /about.php
```