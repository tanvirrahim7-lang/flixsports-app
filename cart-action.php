<?php
require_once 'config.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = (int)$_POST['product_id'];
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));
    $model = sanitize($_POST['model'] ?? '');
    $size = sanitize($_POST['size'] ?? '');
    $color = sanitize($_POST['color'] ?? '');
    
    // Get product info
    $stmt = $pdo->prepare("SELECT p.*, (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image FROM products p WHERE id = ? AND status = 1");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if ($product) {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        
        // Create unique key for this variant
        $cartKey = $productId . '_' . $model . '_' . $size . '_' . $color;
        
        if (isset($_SESSION['cart'][$cartKey])) {
            $_SESSION['cart'][$cartKey]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$cartKey] = [
                'product_id' => $productId,
                'title' => $product['title'],
                'price' => $product['price'],
                'image' => $product['image'],
                'model' => $model,
                'size' => $size,
                'color' => $color,
                'quantity' => $quantity
            ];
        }
        
        $submitType = $_POST['submit_type'] ?? 'cart';
        if ($submitType === 'buy') {
            redirect(SITE_URL . '/checkout.php');
        }
    }
    
    redirect(SITE_URL . '/cart.php');
}

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $key = $_POST['key'] ?? '';
    $quantity = max(1, min(10, (int)($_POST['quantity'] ?? 1)));
    
    if (isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key]['quantity'] = $quantity;
    }
    redirect(SITE_URL . '/cart.php');
}

if ($action === 'remove') {
    $key = $_GET['key'] ?? '';
    if (isset($_SESSION['cart'][$key])) {
        unset($_SESSION['cart'][$key]);
    }
    redirect(SITE_URL . '/cart.php');
}

if ($action === 'clear') {
    $_SESSION['cart'] = [];
    redirect(SITE_URL . '/cart.php');
}

redirect(SITE_URL);
