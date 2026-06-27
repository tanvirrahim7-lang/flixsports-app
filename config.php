<?php
/**
 * STYRIN E-Commerce - Configuration File
 * Update these settings for your hosting
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'styrin_db');      // Change to your database name
define('DB_USER', 'root');           // Change to your database username
define('DB_PASS', '');               // Change to your database password

// Site Configuration
define('SITE_URL', 'https://styrin.shop');  // Change to your domain (no trailing slash)
define('ADMIN_URL', SITE_URL . '/admin');
define('UPLOAD_PATH', __DIR__ . '/uploads/products/');
define('UPLOAD_URL', SITE_URL . '/uploads/products/');

// Session
session_start();

// Timezone
date_default_timezone_set('Asia/Dhaka');

// Error reporting (turn off in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Database Connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed. Please check config.php settings.");
}

// Helper Functions
function getSetting($key, $default = '') {
    global $pdo;
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    return $result ? $result['setting_value'] : $default;
}

function updateSetting($key, $value) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
    return $stmt->execute([$value, $key]);
}

function getAllSettings($group = null) {
    global $pdo;
    if ($group) {
        $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_group = ?");
        $stmt->execute([$group]);
    } else {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    }
    $settings = [];
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

function generateOrderNumber() {
    return 'STR' . date('ymd') . strtoupper(substr(uniqid(), -4));
}

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = strtolower(trim($text, '-'));
    return $text;
}

function formatPrice($price) {
    return '৳' . number_format($price, 0);
}

function isAdmin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function uploadImage($file, $folder = '') {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['error' => 'Invalid file type. Only JPG, PNG, WEBP, GIF allowed.'];
    }
    
    if ($file['size'] > 5 * 1024 * 1024) { // 5MB max
        return ['error' => 'File too large. Maximum 5MB allowed.'];
    }
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_') . '.' . $ext;
    $path = UPLOAD_PATH . ($folder ? $folder . '/' : '') . $filename;
    
    // Create folder if not exists
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $path)) {
        return ['success' => true, 'filename' => ($folder ? $folder . '/' : '') . $filename];
    }
    
    return ['error' => 'Failed to upload file.'];
}
