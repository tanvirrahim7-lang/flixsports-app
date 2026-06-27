<?php
require_once 'config.php';

$slug = sanitize($_GET['slug'] ?? '');
if (!$slug) redirect(SITE_URL);

$catStmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ? AND status = 1");
$catStmt->execute([$slug]);
$category = $catStmt->fetch();
if (!$category) redirect(SITE_URL);

$pageTitle = $category['name'];

// Get filters from URL
$colorFilter = $_GET['color'] ?? '';
$priceMin = (int)($_GET['price_min'] ?? 0);
$priceMax = (int)($_GET['price_max'] ?? 0);
$sort = $_GET['sort'] ?? 'popular';

// Build query
$where = "WHERE p.category_id = ? AND p.status = 1";
$params = [$category['id']];

if ($priceMin > 0) {
    $where .= " AND p.price >= ?";
    $params[] = $priceMin;
}
if ($priceMax > 0) {
    $where .= " AND p.price <= ?";
    $params[] = $priceMax;
}

$orderBy = match($sort) {
    'price-low' => 'p.price ASC',
    'price-high' => 'p.price DESC',
    'rating' => 'p.rating DESC',
    'newest' => 'p.created_at DESC',
    default => 'p.total_sold DESC'
};

$stmt = $pdo->prepare("
    SELECT p.*,
    (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
    FROM products p
    $where
    ORDER BY $orderBy
");
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get available variants for filter sidebar
$variantStmt = $pdo->prepare("
    SELECT DISTINCT pv.variant_type, pv.variant_value, pv.variant_code
    FROM product_variants pv
    JOIN products p ON pv.product_id = p.id
    WHERE p.category_id = ? AND p.status = 1
    ORDER BY pv.variant_type, pv.sort_order
");
$variantStmt->execute([$category['id']]);
$availableVariants = $variantStmt->fetchAll();

$availableModels = array_values(array_filter($availableVariants, fn($v) => $v['variant_type'] === 'model'));
$availableSizes = array_values(array_filter($availableVariants, fn($v) => $v['variant_type'] === 'size'));
$availableColors = array_values(array_filter($availableVariants, fn($v) => $v['variant_type'] === 'color'));

// Remove duplicates
$uniqueModels = array_unique(array_column($availableModels, 'variant_value'));
$uniqueSizes = array_unique(array_column($availableSizes, 'variant_value'));

require_once 'includes/header.php';
?>

    <!-- Breadcrumb -->
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>">Home</a> <span>/</span> <strong><?= $category['name'] ?></strong>
        </div>
    </div>

    <section class="page-content">
        <div class="container">
            <div class="page-layout">
                <!-- Sidebar -->
                <aside class="sidebar">
                    <form method="GET" action="">
                        <input type="hidden" name="slug" value="<?= $slug ?>">
                        
                        <?php if (!empty($uniqueModels)): ?>
                        <div class="filter-group">
                            <h3><i class="fas fa-mobile-alt"></i> Phone Model</h3>
                            <?php foreach ($uniqueModels as $model): ?>
                            <label><input type="checkbox" name="models[]" value="<?= $model ?>"> <?= $model ?></label>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($uniqueSizes)): ?>
                        <div class="filter-group">
                            <h3><i class="fas fa-ruler"></i> Size</h3>
                            <?php foreach ($uniqueSizes as $size): ?>
                            <label><input type="checkbox" name="sizes[]" value="<?= $size ?>"> <?= $size ?></label>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($availableColors)): ?>
                        <div class="filter-group">
                            <h3><i class="fas fa-palette"></i> Color</h3>
                            <div class="color-options">
                                <?php 
                                $seenColors = [];
                                foreach ($availableColors as $c): 
                                    if (in_array($c['variant_value'], $seenColors)) continue;
                                    $seenColors[] = $c['variant_value'];
                                ?>
                                <div class="color-swatch" style="background: <?= $c['variant_code'] ?>;" title="<?= $c['variant_value'] ?>"></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="filter-group">
                            <h3><i class="fas fa-tag"></i> Price Range</h3>
                            <div class="price-range">
                                <input type="number" name="price_min" placeholder="Min" value="<?= $priceMin ?: '' ?>">
                                <span>-</span>
                                <input type="number" name="price_max" placeholder="Max" value="<?= $priceMax ?: '' ?>">
                            </div>
                        </div>

                        <button type="submit" class="filter-btn"><i class="fas fa-filter"></i> Apply Filter</button>
                        <a href="<?= SITE_URL ?>/category.php?slug=<?= $slug ?>" class="filter-btn" style="background: #666; display: block; text-align: center; margin-top: 5px; color: #fff; padding: 10px; border-radius: 5px;"><i class="fas fa-times"></i> Clear</a>
                    </form>
                </aside>

                <!-- Products -->
                <div class="main-content">
                    <div class="sort-bar">
                        <span class="results-count"><?= count($products) ?> products found</span>
                        <select onchange="window.location.href='?slug=<?= $slug ?>&sort='+this.value">
                            <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Most Popular</option>
                            <option value="price-low" <?= $sort === 'price-low' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price-high" <?= $sort === 'price-high' ? 'selected' : '' ?>>Price: High to Low</option>
                            <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Highest Rating</option>
                            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
                        </select>
                    </div>
                    
                    <?php if (empty($products)): ?>
                    <div style="text-align: center; padding: 60px 20px; color: #999;">
                        <i class="fas fa-box-open" style="font-size: 50px; display: block; margin-bottom: 15px;"></i>
                        <h3>No products found</h3>
                        <p>Try adjusting your filters or check back later</p>
                    </div>
                    <?php else: ?>
                    <div class="product-grid">
                        <?php foreach ($products as $p): ?>
                        <?php include 'includes/product-card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>
