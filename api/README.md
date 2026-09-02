# API Endpoints

This folder contains backend API endpoints that handle AJAX requests and form submissions.

## Endpoints

### 1. `save_profile.php`
**Purpose:** Save user profile information

**Method:** POST  
**Authentication:** Required (user must be logged in)  
**Request:** Form data with profile fields  
**Response:** JSON

```json
{
  "success": true|false,
  "message": "Success/error message"
}
```

**Used by:** `profile.php`

---

### 2. `process_checkout.php`
**Purpose:** Process checkout and payment

**Method:** POST  
**Authentication:** Optional (guest checkout supported)  
**Request:** Form data with checkout information  
**Response:** JSON

```json
{
  "success": true|false,
  "message": "Success/error message",
  "redirect": "order_confirmation.php"
}
```

**Payment Integration:** PayMongo API

**Used by:** `checkout.php`

---

### 3. `add_to_cart.php`
**Purpose:** Add product to shopping cart

**Method:** POST  
**Authentication:** None (uses session)  
**Request Parameters:**
- `product_id` - Product ID
- `product_name` - Product name
- `product_price` - Product price
- `product_image` - Product image path
- `quantity` - Quantity (default: 1)

**Response:** JSON

```json
{
  "success": true|false,
  "message": "Success/error message",
  "cart_count": 5,
  "product_name": "Product Name"
}
```

**Used by:** `ProductCatalog.php`, `ProductView.php`, `cart.js`

---

### 4. `get_product.php`
**Purpose:** Retrieve product information

**Method:** GET  
**Authentication:** None  
**Request Parameters:**
- `id` - Product ID (required)

**Response:** JSON

```json
{
  "success": true,
  "product": {
    "id": 1,
    "product_name": "Product Name",
    "price": "1000.00",
    "stock": 50,
    "image": "path/to/image.jpg",
    "tag": "Category",
    "label": "New Arrival"
  }
}
```

**Used by:** AJAX product queries

---

## Usage Examples

### From JavaScript
```javascript
fetch('api/add_to_cart.php', {
    method: 'POST',
    body: formData
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        console.log(data.message);
    }
});
```

### From Form
```html
<form action="api/process_checkout.php" method="POST">
    <!-- form fields -->
</form>
```

## Security

All API endpoints:
- ✅ Use prepared statements (SQL injection prevention)
- ✅ Validate input data
- ✅ Return JSON responses
- ✅ Include authentication checks where needed
- ✅ Handle errors gracefully

## Error Handling

All endpoints return consistent error format:
```json
{
  "success": false,
  "message": "Error description"
}
```

## Path Configuration

These files use relative paths to includes:
```php
require_once '../includes/db.php';
require_once '../includes/user_auth.php';
```

This is because they are in the `api/` subdirectory.

## Testing

Test endpoints using tools like:
- Browser DevTools (Network tab)
- Postman
- cURL

Example cURL:
```bash
curl -X POST http://localhost/PeakPH_Commerce/api/add_to_cart.php \
  -F "product_id=1" \
  -F "product_name=Test Product" \
  -F "product_price=1000"
```
