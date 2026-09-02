# 🏔️ PeakPH Commerce - E-commerce Platform

**Version:** 1.0.0 (Stable)  
**Last Updated:** October 2, 2025  
**Author:** KingAb04  

A comprehensive e-commerce platform built with PHP, MySQL, and modern web technologies, specializing in camping and outdoor gear.

## 🌟 Features

### **Frontend Features**
- **Modern UI/UX**: Poppins font, responsive design, PeakPH brand colors (#2e765e, #3da180)
- **User Authentication**: Complete login/signup system with OTP verification
- **Product Catalog**: Dynamic product display with search and filtering
- **Shopping Cart**: Session-based cart management with real-time updates
- **Checkout System**: Comprehensive checkout with billing/shipping addresses
- **Map Integration**: Pin location feature for precise delivery coordinates
- **Order Confirmation**: Professional order confirmation with tracking details
- **User Profiles**: Personal dashboard with order history and settings

### **Backend Features**
- **Admin Dashboard**: Complete inventory and content management
- **User Management**: Admin and customer authentication with role-based access
- **Inventory System**: Stock management with automated updates
- **Content Management**: Carousel, bestsellers, new arrivals management
- **Database Integration**: MySQL with prepared statements and transactions
- **Audit Trail**: Complete activity logging for security and compliance

### **Security & Performance**
- **User Authentication**: Secure login/signup with OTP email verification
- **Password Security**: bcrypt hashing with salt
- **SQL Injection Protection**: Prepared statements throughout
- **Session Management**: Secure session handling with timeout
- **CSRF Protection**: Token-based form security
- **Input Validation**: Comprehensive form validation
- **Error Handling**: Graceful error handling and logging
- **Database Transactions**: ACID compliance for critical operations

## 📁 Project Structure

```
PeakPH_Commerce/
├── 📄 Core Pages
│   ├── index.php                 # Homepage with carousel and featured products
│   ├── ProductCatalog.php        # Product catalog with search/filter
│   ├── ProductView.php           # Individual product detail page
│   ├── cart.php                  # Shopping cart management
│   ├── checkout.php              # Integrated checkout system
│   ├── order_confirmation.php    # Order confirmation page
│   ├── about.php                 # About page
│   └── search_products.php       # Product search functionality
│
├── 🔐 Authentication System
│   ├── auth/
│   │   ├── login_handler.php     # User login processing
│   │   ├── signup_handler.php    # User registration
│   │   ├── verify_otp.php        # OTP verification
│   │   └── resend_otp.php        # Resend OTP codes
│   ├── profile.php               # User profile page
│   ├── orders.php                # User order history
│   ├── settings.php              # Account settings
│   └── logout.php                # Logout handler
│
├── 🛠️ Backend Processing
│   ├── add_to_cart.php          # Cart item management
│   ├── process_checkout.php     # Order processing logic
│   └── get_product.php          # Product data retrieval

PeakPH_Commerce/├── Css/                           # Stylesheets

├── 📄 Core Pages│   ├── Global.css                  # Global styles

│   ├── index.php                 # Homepage with carousel and featured products│   ├── admin.css                   # Admin panel styles

│   ├── ProductCatalog.php        # Product catalog with search/filter│   ├── landingcomponents.css       # Landing page styles

│   ├── ProductView.php           # Individual product detail page│   ├── prod.css                    # Product catalog styles

│   ├── cart.php                  # Shopping cart management│   └── productview.css             # Product view styles

│   ├── checkout.php              # Integrated checkout system├── Js/                            # JavaScript files

│   ├── order_confirmation.php    # Order confirmation page│   ├── JavaScript.js               # Main JS

│   ├── about.php                 # About page│   ├── admin.js                    # Admin panel JS

│   └── search_products.php       # Product search functionality│   └── chatbot.js                  # Chatbot functionality

│├── uploads/                        # User uploaded files

├── 🛠️ Backend Processing├── index.php                       # Homepage

│   ├── add_to_cart.php          # Cart item management├── ProductCatalog.php              # Product catalog page

│   ├── process_checkout.php     # Order processing logic├── cart.php                        # Shopping cart

│   └── get_product.php          # Product data retrieval├── add_to_cart.php                 # Add to cart handler

│├── about.php                       # About page

├── 🔧 Admin Panel├── login.php                       # Login handler

│   ├── admin/└── logout.php                      # Logout handler

│   │   ├── dashboard.php        # Admin dashboard```

│   │   ├── orders.php          # Order management

│   │   ├── login.php           # Admin authentication## Access URLs

│   │   ├── auth_helper.php     # Authentication helpers

│   │   ├── inventory/          # Inventory management### User Side (Frontend)

│   │   ├── content/            # Content management (carousel, etc.)- Homepage: `http://localhost/PeakPH_Commerce/`

│   │   └── users/              # User management- Product Catalog: `http://localhost/PeakPH_Commerce/ProductCatalog.php`

│- Shopping Cart: `http://localhost/PeakPH_Commerce/cart.php`

├── 🎨 Frontend Assets- About: `http://localhost/PeakPH_Commerce/about.php`

│   ├── Css/

│   │   ├── Global.css          # Main stylesheet### Admin Side (Backend)

│   │   ├── landingcomponents.css # Homepage components- Admin Dashboard: `http://localhost/PeakPH_Commerce/admin/`

│   │   ├── productview.css     # Product page styling- Analytics Dashboard: `http://localhost/PeakPH_Commerce/admin/dashboard.php`

│   │   ├── admin.css           # Admin panel styling- Inventory Management: `http://localhost/PeakPH_Commerce/admin/inventory/inventory.php`

│   │   └── carousel.css        # Carousel styling- User Management: `http://localhost/PeakPH_Commerce/admin/users/`

│   │- Orders: `http://localhost/PeakPH_Commerce/admin/orders.php`

│   ├── Js/

│   │   ├── JavaScript.js       # Main frontend logic## Features

│   │   ├── admin.js           # Admin panel interactions

│   │   └── chatbot.js         # Chatbot functionality### User Features

│   │- Product browsing with category filters

│   └── Assets/- Shopping cart functionality

│       ├── Carousel_Picts/     # Carousel images- Responsive design

│       ├── Gallery_Images/     # Product images- Product search and filtering

│       └── Main_Category/      # Category images

│### Admin Features

├── 🔗 Configuration- Complete inventory management

│   ├── includes/- User management

│   │   └── db.php              # Database configuration- Order tracking

│   ├── components/             # Reusable components- Content management (carousel, etc.)

│   └── uploads/               # User uploaded content- Dashboard analytics

│

├── 📚 Documentation## Setup Requirements

│   ├── docs/- XAMPP or similar PHP server

│   │   └── examples/          # Code examples and demos- MySQL database

│   ├── README.md              # This file- PHP 7.0 or higher

│   ├── DATABASE_SETUP_GUIDE.md # Database setup instructions

│   └── database_setup.sql     # Database schema## Recent Changes

│- Separated user-side and admin-side into distinct folder structures

└── 🗂️ Development- Moved all admin functionality to `/admin/` directory

    └── temp/                  # Temporary/development files- Centralized database connection in `/includes/` directory

```- Updated all file paths and navigation links

- Maintained responsive design and cart functionality
## 🚀 Installation & Setup

### **Prerequisites**
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

### **Quick Setup**

1. **Clone Repository**
   ```bash
   git clone https://github.com/KingAb04/PeakPH_Commerce.git
   cd PeakPH_Commerce
   ```

2. **Database Setup**
   ```bash
   mysql -u root -p < database_setup.sql
   ```

3. **Configure Database**
   ```php
   // Edit includes/db.php
   $host = "localhost";
   $user = "root";
   $pass = "your_password";
   $dbname = "peakph_db";
   ```

4. **Set Permissions**
   ```bash
   chmod 755 uploads/
   chmod 755 admin/uploads/
   ```

5. **Access Application**
   - Frontend: `http://localhost/PeakPH_Commerce/`
   - Admin: `http://localhost/PeakPH_Commerce/admin/`
   - Default Admin: `admin@peakph.com` / `password`

## 💡 Key Features Explained

### **🛒 Shopping Cart System**
- Session-based cart storage
- Real-time quantity updates
- Persistent across page refreshes
- Support for both database and demo products

### **🗺️ Map Integration**
- Interactive delivery location selection
- GPS-based current location detection
- Address search functionality (Philippines-focused)
- Coordinates storage for precise delivery

### **💳 Checkout Process**
1. Contact information collection
2. Shipping address with map pinning option
3. Billing address (same as shipping or separate)
4. Payment method selection (COD, GCash, Bank Transfer)
5. Order confirmation with tracking details

### **🔐 Admin Features**
- **Inventory Management**: Add, edit, delete products with stock tracking
- **Content Management**: Update carousel, bestsellers, new arrivals
- **User Management**: Admin user creation and management
- **Order Processing**: View and manage customer orders (planned)

## 🎨 Design System

### **Colors**
- **Primary**: `#2e765e` (Dark Green)
- **Secondary**: `#3da180` (Light Green)
- **Background**: `#f8fffe` to `#f0f9f7` (Gradient)

### **Typography**
- **Font Family**: Poppins
- **Weights**: 400 (Regular), 500 (Medium), 600 (Semi-Bold), 700 (Bold)

### **Components**
- **Buttons**: Rounded corners (25px), gradient backgrounds, hover effects
- **Cards**: 15px border radius, subtle shadows with brand color tints
- **Forms**: Consistent styling across all input elements

## 🔧 Configuration Options

### **Payment Methods**
```php
// Available in checkout.php
$payment_methods = [
    'cod' => 'Cash on Delivery',
    'gcash' => 'GCash',
    'bank_transfer' => 'Bank Transfer'
];
```

### **Shipping Settings**
```php
// In process_checkout.php
$shipping_fee = 50.00;
$tax_rate = 0.12; // 12% VAT
```

## 🐛 Troubleshooting

### **Common Issues**

1. **Database Connection Failed**
   - Check `includes/db.php` configuration
   - Verify MySQL service is running
   - Ensure database exists

2. **Image Upload Issues**
   - Check folder permissions (755)
   - Verify file size limits
   - Ensure upload directory exists

3. **Cart Not Working**
   - Check session configuration
   - Verify JavaScript is enabled
   - Clear browser cookies/cache

### **Debug Mode**
Enable error reporting in development:
```php
// Add to top of any PHP file
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 📊 Database Schema

### **Key Tables**
- `inventory` - Product catalog
- `users` - User accounts
- `orders` - Customer orders (schema ready, processing planned)
- `order_items` - Order line items
- `carousel` - Homepage carousel
- `bestsellers` - Featured products
- `new_arrivals` - New products

See `database_setup.sql` for complete schema.

## 🔮 Future Enhancements

### **Planned Features**
- [ ] Payment gateway integration (PayPal, Stripe)
- [ ] Email notifications for orders
- [ ] Advanced order tracking
- [ ] Customer accounts and login
- [ ] Wishlist functionality
- [ ] Product reviews and ratings
- [ ] Inventory alerts and reporting

### **Technical Improvements**
- [ ] API endpoints for mobile app
- [ ] Caching system implementation
- [ ] Image optimization
- [ ] SEO enhancements
- [ ] Performance monitoring

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

## 📄 License

This project is licensed under the MIT License.

## 👨‍💻 Developer Information

**Developer**: KingAb04  
**Contact**: [GitHub Profile](https://github.com/KingAb04)  
**Project**: PeakPH Commerce E-commerce Platform  

## 🙏 Acknowledgments

- Bootstrap Icons for UI icons
- Leaflet.js for map functionality  
- Poppins font from Google Fonts
- OpenStreetMap for mapping services

---

**⭐ Star this repository if you find it useful!**