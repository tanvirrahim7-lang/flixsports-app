<?php
require_once 'config.php';
$pageTitle = '';

// Get featured products
$featuredStmt = $pdo->query("
    SELECT p.*, c.slug as category_slug,
    (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status = 1 AND p.featured = 1
    ORDER BY p.created_at DESC LIMIT 8
");
$featuredProducts = $featuredStmt->fetchAll();

// Flash sale products
$flashStmt = $pdo->query("
    SELECT p.*, c.slug as category_slug,
    (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status = 1 AND p.flash_sale = 1
    ORDER BY p.discount DESC LIMIT 4
");
$flashProducts = $flashStmt->fetchAll();

// Phone covers
$coversStmt = $pdo->query("
    SELECT p.*,
    (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
    FROM products p
    WHERE p.status = 1 AND p.category_id = (SELECT id FROM categories WHERE slug = 'phone-covers' LIMIT 1)
    ORDER BY p.total_sold DESC LIMIT 4
");
$phoneCovers = $coversStmt->fetchAll();

// Ladies dress
$dressStmt = $pdo->query("
    SELECT p.*,
    (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
    FROM products p
    WHERE p.status = 1 AND p.category_id = (SELECT id FROM categories WHERE slug = 'ladies-dress' LIMIT 1)
    ORDER BY p.total_sold DESC LIMIT 4
");
$ladiesDress = $dressStmt->fetchAll();

// Categories
$catStmt2 = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id AND status = 1) as product_count FROM categories c WHERE status = 1 ORDER BY sort_order");
$homeCategories = $catStmt2->fetchAll();

require_once 'includes/header.php';
?>

    <!-- Hero Banner -->
    <section class="hero-banner">
        <div class="banner-slider">
            <div class="banner-slide active" style="background: linear-gradient(135deg, #000, #333);">
                <div class="banner-content">
                    <h1>Premium Phone Covers</h1>
                    <p>Protect your phone in style</p>
                    <a href="<?= SITE_URL ?>/category.php?slug=phone-covers" class="banner-btn">Shop Now</a>
                </div>
            </div>
            <div class="banner-slide" style="background: linear-gradient(135deg, #e91e63, #9c27b0);">
                <div class="banner-content">
                    <h1>Ladies Fashion Collection</h1>
                    <p>Trendy dresses at amazing prices</p>
                    <a href="<?= SITE_URL ?>/category.php?slug=ladies-dress" class="banner-btn">Explore</a>
                </div>
            </div>
        </div>
        <button class="banner-nav prev"><i class="fas fa-chevron-left"></i></button>
        <button class="banner-nav next"><i class="fas fa-chevron-right"></i></button>
    </section>

    <!-- Categories -->
    <section class="categories-section">
        <div class="container">
            <h2 class="section-title">Shop by Category</h2>
            <div class="category-grid">
                <?php foreach ($homeCategories as $cat): ?>
                <a href="<?= SITE_URL ?>/category.php?slug=<?= $cat['slug'] ?>" class="category-card">
                    <div class="category-icon"><i class="<?= $cat['icon'] ?>"></i></div>
                    <h3><?= $cat['name'] ?></h3>
                    <p><?= $cat['product_count'] ?> Products</p>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php if (!empty($flashProducts)): ?>
    <!-- Flash Sale -->
    <section class="flash-sale">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title"><i class="fas fa-bolt"></i> Flash Sale</h2>
                <div class="countdown">
                    <span>Ends in:</span>
                    <div class="timer"><span id="hours">05</span>:<span id="minutes">30</span>:<span id="seconds">00</span></div>
                </div>
            </div>
            <div class="product-grid">
                <?php foreach ($flashProducts as $p): ?>
                <?php include 'includes/product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($phoneCovers)): ?>
    <!-- Phone Covers -->
    <section class="featured-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Popular Phone Covers</h2>
                <a href="<?= SITE_URL ?>/category.php?slug=phone-covers" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="product-grid">
                <?php foreach ($phoneCovers as $p): ?>
                <?php include 'includes/product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($ladiesDress)): ?>
    <!-- Ladies Dress -->
    <section class="featured-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Trending Ladies Dress</h2>
                <a href="<?= SITE_URL ?>/category.php?slug=ladies-dress" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="product-grid">
                <?php foreach ($ladiesDress as $p): ?>
                <?php include 'includes/product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($featuredProducts)): ?>
    <!-- All Featured -->
    <section class="featured-section">
        <div class="container">
            <h2 class="section-title">Featured Products</h2>
            <div class="product-grid">
                <?php foreach ($featuredProducts as $p): ?>
                <?php include 'includes/product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
