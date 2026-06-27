# STYRIN E-Commerce Website

## Installation Guide (cPanel Shared Hosting)

### Step 1: Database Setup
1. cPanel → MySQL Databases → Create Database: `styrin_db`
2. Create User & add it to the database with ALL PRIVILEGES
3. cPanel → phpMyAdmin → Select `styrin_db` → Import → Upload `database.sql`

### Step 2: Upload Files
1. cPanel → File Manager → `public_html`
2. Upload all files (or upload as ZIP and Extract)
3. Make sure `uploads/products/` folder has **755** permission

### Step 3: Configure Database
Edit `config.php` and update:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_cpanel_username_styrin_db');
define('DB_USER', 'your_cpanel_username_dbuser');
define('DB_PASS', 'your_database_password');
define('SITE_URL', 'https://styrin.shop');
```

### Step 4: Login to Admin Panel
- URL: `https://styrin.shop/admin`
- Username: `admin`
- Password: `password`
- **IMPORTANT**: Change password immediately after first login!

### Step 5: Configure Settings
Go to Admin → Settings:
1. **General**: Update phone, email, social links
2. **Delivery**: Set delivery charges (default: Dhaka ৳80, Outside ৳150)
3. **Payment**: Enable bKash/Nagad, enter your numbers
4. **Theme**: Set brand colors

### Step 6: Add Products
Go to Admin → Products → Add Product:
- Upload images (first image = main image)
- For Phone Covers: add phone models (one per line)
- For Dresses: add sizes (comma separated)
- Add colors with color picker
- Set price and original price for discount display

---

## Features
- Admin Panel (product/order/settings management)
- Product pages with model/color/size selection
- Shopping cart & checkout
- bKash/Nagad manual payment
- COD option
- Order tracking (by order number or phone)
- Responsive design
- Flash sale section
- Search functionality

## Tech Stack
- PHP 8.2
- MySQL
- Vanilla CSS & JavaScript
- Font Awesome Icons

## File Structure
```
styrin.shop/
├── index.php          (Homepage)
├── product.php        (Product Detail)
├── category.php       (Category Listing)
├── cart.php           (Shopping Cart)
├── checkout.php       (Checkout Page)
├── order-success.php  (Order Confirmation)
├── track-order.php    (Order Tracking)
├── search.php         (Search Results)
├── cart-action.php    (Cart Add/Remove/Update)
├── config.php         (Database Config)
├── database.sql       (Database Schema)
├── .htaccess          (Security & Config)
├── assets/
│   ├── css/style.css
│   └── js/main.js
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── product-card.php
├── uploads/products/  (Product images)
└── admin/
    ├── index.php      (Login)
    ├── dashboard.php  (Stats)
    ├── products.php   (Product List)
    ├── product-add.php
    ├── product-edit.php
    ├── orders.php     (Order Management)
    ├── order-detail.php
    ├── categories.php
    ├── settings.php   (All Settings)
    └── assets/
```
