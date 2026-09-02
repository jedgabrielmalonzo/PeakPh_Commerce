# Quick Start Guide: Enhanced Product Details

## 🚀 Getting Started

### Step 1: Run Database Migration
```bash
# Navigate to dev-tools folder
cd c:\xampp\htdocs\PeakPH_Commerce\_dev-tools

# Run migration script
php migrate_product_details.php
```

**Expected Output:**
```
✅ Column 'description' added successfully
✅ Column 'specifications' added successfully
✅ Column 'additional_images' added successfully
✅ Column 'video_url' added successfully
✅ Column 'dimensions' added successfully
✅ Column 'weight' added successfully
✅ Column 'category_details' added successfully
```

---

## 📦 Adding a Complete Product

### 1. Access Admin Panel
- Go to: `http://localhost/PeakPH_Commerce/admin/`
- Navigate to **Inventory** section
- Click **"+ Add Product"** button

### 2. Fill Basic Information
```
Product Name: 4-Person Camping Tent
Price: 2,499.00
Stock: 25
Category: Tents
```

### 3. Add Rich Description
```
Experience ultimate comfort with our premium 4-person camping tent! 
Perfect for family adventures and weekend getaways.

This spacious tent features:
- Durable waterproof construction
- Easy 15-minute setup
- Multiple storage pockets
- Full mesh windows for ventilation
- Fits 4 sleeping bags comfortably

Whether you're camping in the mountains or at the beach, 
this tent provides reliable shelter and comfort.
```

### 4. Add Specifications
```
Material: 190T Polyester with PU coating
Waterproof Rating: 3000mm
Capacity: 4 persons
Floor Area: 9.5 sq meters
Peak Height: 6.5 feet
Pole Material: Fiberglass
Setup Time: 15 minutes
Season Rating: 3-season
```

### 5. Add Dimensions & Weight
```
Dimensions: 10 x 9 x 6.5 ft
Weight: 12.5 kg
```

### 6. Add Video URL (Optional)
```
YouTube: https://www.youtube.com/watch?v=VIDEO_ID
Direct: https://your-domain.com/videos/tent-demo.mp4
```

### 7. Upload Images

**Main Image (Required):**
- Drag & drop your main product photo
- Or click the area to browse files
- Best size: 800x800px
- Format: JPG, PNG, GIF
- Max size: 5MB

**Additional Images (Optional, up to 5):**
- Tent interior view
- Setup process
- Storage pockets
- Packed size
- In-use camping scene

**Tip:** Select multiple files at once by holding Ctrl/Cmd

### 8. Save Product
Click **"Save Product"** button. You'll see a success message!

---

## ✏️ Editing Existing Products

### 1. Find Your Product
- Go to **Admin > Inventory**
- Use search to find product quickly
- Click **"Edit"** button

### 2. Update Fields
- Modify any text fields
- Update specifications as needed
- Change video URL

### 3. Manage Images

**View Existing Additional Images:**
- Thumbnails show at top of edit form
- See all uploaded additional images

**Remove Unwanted Images:**
- Click **X** button on any thumbnail
- Image will be removed on save

**Add More Images:**
- Use "Additional Images" drag & drop area
- New images will be added to existing ones
- Not replacing, just adding more!

### 4. Save Changes
Click **"Update Product"** to save all changes.

---

## 🎨 Best Practices

### Image Guidelines:
- **Main Image:** Product on white/plain background
- **Additional Images:**
  - Different angles (front, side, top)
  - Product in use
  - Detail shots (zippers, pockets, etc.)
  - Size comparison (next to person/object)
  - Packaging/carrying case

### Description Tips:
- Start with a compelling headline
- Use bullet points for features
- Include 2-3 paragraphs
- Mention target audience
- Add use cases/scenarios

### Specifications Format:
```
Label: Value
Label: Value

or

Just plain text
Multiple lines supported
```

**Examples:**
```
✅ Material: Polyester
✅ Size: 10 x 10 ft
✅ Weight: 5 kg

❌ Material Polyester (missing colon)
❌ Size=10x10ft (use colon, not equals)
```

### Video Recommendations:
- **YouTube:** Best option (fast loading)
- Length: 1-3 minutes ideal
- Show product features
- Demonstrate setup/usage
- Quality: 720p minimum, 1080p recommended

---

## 🔍 Customer View Features

When customers visit your product page, they'll see:

### Image Gallery
- Main image (large)
- Thumbnail strip below (clickable)
- Click thumbnail → changes main image
- Green border shows active image

### Watch Video Button
- Appears on main image (if video added)
- Click → Opens full-screen video player
- YouTube embeds automatically
- Close with X or ESC key

### Product Description
- Full text with formatting
- Bullet points
- Multiple paragraphs

### Specifications Table
- Organized in rows
- Label-value pairs
- Dimensions and weight
- Easy to scan

---

## 📊 Inventory Table View

The inventory table now shows:
- **ID:** Product ID number
- **Image:** Thumbnail
- **Name:** Product name
- **Description:** First 50 characters (hover for full text)
- **Price:** ₱ formatted
- **Stock:** With color indicators
- **In Carts:** How many users have it
- **Tag:** Category
- **Label:** Best Seller, Popular, etc.
- **Actions:** Edit, Delete, Label buttons

---

## 🐛 Common Issues

### Problem: Images not uploading
**Solutions:**
- Check file size (must be under 5MB)
- Use supported formats: JPG, PNG, GIF
- Ensure `admin/uploads/` folder has write permissions

### Problem: Video not showing
**Solutions:**
- Verify YouTube URL is correct
- Check if video is public (not private/unlisted)
- Try different YouTube URL format:
  - `https://www.youtube.com/watch?v=VIDEO_ID`
  - `https://youtu.be/VIDEO_ID`

### Problem: Description not saving
**Solutions:**
- Check for special characters (', ", <, >)
- Don't exceed reasonable length (5000 chars)
- Use plain text (no HTML tags)

### Problem: Additional images not appearing
**Solutions:**
- Refresh the page after editing
- Check browser console for errors (F12)
- Verify images were uploaded (check uploads folder)

---

## 💡 Pro Tips

### 1. SEO-Friendly Descriptions
```
✅ Include keywords naturally
✅ Write for humans, not robots
✅ Be specific and detailed
✅ Mention product benefits
```

### 2. Image Optimization
- Compress images before upload
- Use descriptive filenames
- Maintain consistent aspect ratio
- Show product from multiple angles

### 3. Specifications Strategy
- List most important specs first
- Use consistent formatting
- Include units of measurement
- Add technical details customers need

### 4. Video Strategy
- Create brand channel on YouTube
- Organize videos in playlists
- Add captions for accessibility
- Include product name in video title

---

## 📞 Need Help?

### Documentation:
- Full Documentation: `docs/PRODUCT_DETAILS_ENHANCEMENT.md`
- Database Schema: `database/add_product_details_columns.sql`

### Support:
- Check browser console (F12) for errors
- Review PHP error logs
- Test with sample products first

---

## ✅ Quick Checklist

Before launching to customers:

- [ ] Database migration completed successfully
- [ ] Added at least 3 products with full details
- [ ] Tested image gallery (thumbnails work)
- [ ] Tested video playback (YouTube embeds)
- [ ] Verified specifications display correctly
- [ ] Checked mobile responsive view
- [ ] Tested edit/delete functionality
- [ ] Reviewed descriptions for typos
- [ ] Optimized all images (file size)
- [ ] Tested add to cart from detailed pages

---

**Happy Selling! 🎉**

*Last Updated: November 4, 2025*
