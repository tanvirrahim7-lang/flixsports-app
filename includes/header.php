<?php
if (!isset($pdo)) {
    require_once __DIR__ . '/../config.php';
}
$siteSettings = getAllSettings();
$siteName = $siteSettings['site_name'] ?? 'STYRIN';
$primaryColor = $siteSettings['primary_color'] ?? '#000000';
$secondaryColor = $siteSettings['secondary_color'] ?? '#ff6b35';

// Get categories for nav
$catStmt = $pdo->query("SELECT * FROM categories WHERE status = 1 ORDER BY sort_order");
$navCategories = $catStmt->fetchAll();

// Cart count from session
$cartCount = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += $item['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' . $siteName : $siteName ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: <?= $primaryColor ?>;
            --secondary: <?= $secondaryColor ?>;
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-left">
                <span><i class="fas fa-truck"></i> ঢাকায় ৳<?= $siteSettings['delivery_dhaka'] ?? '80' ?> | ঢাকার বাইরে ৳<?= $siteSettings['delivery_outside'] ?? '150' ?></span>
            </div>
            <div class="top-bar-right">
                <a href="<?= SITE_URL ?>/track-order.php"><i class="fas fa-map-marker-alt"></i> Track Order</a>
                <?php if (!empty($siteSettings['whatsapp_number'])): ?>
                <a href="https://wa.me/<?= $siteSettings['whatsapp_number'] ?>" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="<?= SITE_URL ?>" class="logo">
                    <?php if (!empty($siteSettings['site_logo'])): ?>
                        <img src="<?= SITE_URL ?>/uploads/<?= $siteSettings['site_logo'] ?>" alt="<?= $siteName ?>" class="logo-img">
                    <?php else: ?>
                        <span class="logo-text"><?= $siteName ?></span>
                    <?php endif; ?>
                </a>
                <div class="search-bar">
                    <form action="<?= SITE_URL ?>/search.php" method="GET">
                        <input type="text" name="q" placeholder="Search products..." value="<?= isset($_GET['q']) ? sanitize($_GET['q']) : '' ?>">
                        <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                <div class="header-actions">
                    <a href="<?= SITE_URL ?>/cart.php" class="cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count"><?= $cartCount ?></span>
                    </a>
                </div>
                <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Category Navigation -->
    <nav class="category-nav" id="mainNav">
        <div class="container">
            <ul class="nav-list">
                <li><a href="<?= SITE_URL ?>" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>"><i class="fas fa-home"></i> Home</a></li>
                <?php foreach ($navCategories as $cat): ?>
                <li><a href="<?= SITE_URL ?>/category.php?slug=<?= $cat['slug'] ?>" class="<?= (isset($_GET['slug']) && $_GET['slug'] == $cat['slug']) ? 'active' : '' ?>"><i class="<?= $cat['icon'] ?>"></i> <?= $cat['name'] ?></a></li>
                <?php endforeach; ?>
                <li><a href="<?= SITE_URL ?>/cart.php" class="<?= basename($_SERVER['PHP_SELF']) == 'cart.php' ? 'active' : '' ?>"><i class="fas fa-shopping-cart"></i> Cart</a></li>
                <li><a href="<?= SITE_URL ?>/track-order.php"><i class="fas fa-truck"></i> Track Order</a></li>
            </ul>
        </div>
    </nav>
