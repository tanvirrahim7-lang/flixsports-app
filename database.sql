-- STYRIN E-Commerce Database Schema
-- PHP 8.2 + MySQL
-- Created for cPanel Shared Hosting

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+06:00";

CREATE DATABASE IF NOT EXISTS `styrin_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `styrin_db`;

-- ===== ADMIN USERS TABLE =====
CREATE TABLE `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin: username=admin, password=admin123 (change after first login)
INSERT INTO `admins` (`username`, `password`, `name`, `email`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STYRIN Admin', 'admin@styrin.shop');

-- ===== CATEGORIES TABLE =====
CREATE TABLE `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `icon` VARCHAR(50) DEFAULT 'fas fa-box',
    `image` VARCHAR(255),
    `status` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `categories` (`name`, `slug`, `icon`, `status`, `sort_order`) VALUES
('Phone Covers', 'phone-covers', 'fas fa-mobile-alt', 1, 1),
('Ladies Dress', 'ladies-dress', 'fas fa-tshirt', 1, 2);

-- ===== PRODUCTS TABLE =====
CREATE TABLE `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `price` DECIMAL(10,2) NOT NULL,
    `original_price` DECIMAL(10,2) DEFAULT NULL,
    `discount` INT DEFAULT 0,
    `stock` INT DEFAULT 100,
    `featured` TINYINT(1) DEFAULT 0,
    `flash_sale` TINYINT(1) DEFAULT 0,
    `status` TINYINT(1) DEFAULT 1,
    `total_sold` INT DEFAULT 0,
    `rating` DECIMAL(2,1) DEFAULT 5.0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===== PRODUCT IMAGES TABLE =====
CREATE TABLE `product_images` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `is_primary` TINYINT(1) DEFAULT 0,
    `sort_order` INT DEFAULT 0,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===== PRODUCT VARIANTS (Colors, Models, Sizes) =====
CREATE TABLE `product_variants` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `variant_type` ENUM('color', 'model', 'size') NOT NULL,
    `variant_value` VARCHAR(100) NOT NULL,
    `variant_code` VARCHAR(50) DEFAULT NULL COMMENT 'hex code for colors',
    `extra_price` DECIMAL(10,2) DEFAULT 0,
    `stock` INT DEFAULT 100,
    `sort_order` INT DEFAULT 0,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===== PRODUCT FEATURES =====
CREATE TABLE `product_features` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `feature_text` VARCHAR(255) NOT NULL,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===== ORDERS TABLE =====
CREATE TABLE `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_number` VARCHAR(20) NOT NULL UNIQUE,
    `customer_name` VARCHAR(100) NOT NULL,
    `customer_phone` VARCHAR(20) NOT NULL,
    `customer_email` VARCHAR(100),
    `shipping_address` TEXT NOT NULL,
    `city` VARCHAR(50) NOT NULL,
    `area` VARCHAR(100),
    `payment_method` VARCHAR(50) NOT NULL,
    `payment_number` VARCHAR(20) DEFAULT NULL COMMENT 'bKash/Nagad sender number',
    `transaction_id` VARCHAR(50) DEFAULT NULL,
    `subtotal` DECIMAL(10,2) NOT NULL,
    `delivery_charge` DECIMAL(10,2) NOT NULL,
    `total` DECIMAL(10,2) NOT NULL,
    `status` ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    `admin_note` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===== ORDER ITEMS TABLE =====
CREATE TABLE `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `product_title` VARCHAR(255) NOT NULL,
    `variant_model` VARCHAR(100),
    `variant_color` VARCHAR(100),
    `variant_size` VARCHAR(50),
    `price` DECIMAL(10,2) NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `subtotal` DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===== SITE SETTINGS TABLE =====
CREATE TABLE `settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT,
    `setting_group` VARCHAR(50) DEFAULT 'general'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default Settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
-- General
('site_name', 'STYRIN', 'general'),
('site_tagline', 'Premium Phone Covers & Ladies Fashion', 'general'),
('site_logo', '', 'general'),
('site_email', 'info@styrin.shop', 'general'),
('site_phone', '', 'general'),
('site_address', '', 'general'),
('facebook_url', '', 'general'),
('instagram_url', '', 'general'),
('whatsapp_number', '', 'general'),

-- Delivery
('delivery_dhaka', '80', 'delivery'),
('delivery_outside', '150', 'delivery'),
('free_delivery_min', '0', 'delivery'),

-- Payment
('cod_enabled', '1', 'payment'),
('bkash_enabled', '1', 'payment'),
('bkash_number', '', 'payment'),
('nagad_enabled', '1', 'payment'),
('nagad_number', '', 'payment'),
('bkash_api_enabled', '0', 'payment'),
('bkash_api_username', '', 'payment'),
('bkash_api_password', '', 'payment'),
('bkash_api_key', '', 'payment'),
('bkash_api_secret', '', 'payment'),

-- Theme
('primary_color', '#000000', 'theme'),
('secondary_color', '#ff6b35', 'theme'),
('header_bg', '#ffffff', 'theme'),
('footer_bg', '#111111', 'theme');

-- ===== VISITORS/ANALYTICS (optional) =====
CREATE TABLE `page_views` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `page` VARCHAR(255),
    `ip_address` VARCHAR(50),
    `viewed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
