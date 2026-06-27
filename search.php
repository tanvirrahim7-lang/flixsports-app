<?php
require_once 'config.php';
$query = sanitize($_GET['q'] ?? '');
$pageTitle = "Search: $query";

$products = [];
if ($query) {
    $stmt = $pdo->prepare("
        SELECT p.*, c.slug as category_slug,
        (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 1 AND (p.title LIKE ? OR p.description LIKE ?)
        ORDER BY p.total_sold DESC
    ");
    $stmt->execute(["%$query%", "%$query%"]);
    $products = $stmt->fetchAll();
}

require_once 'includes/header.php';
?>

    <div class="container">
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>">Home</a> <span>/</span> <strong>Search: "<?= $query ?>"</strong>
        </div>
    </div>

    <section class="page-content">
        <div class="container">
            <div class="sort-bar">
                <span class="results-count"><?= count($products) ?> products found for "<?= $query ?>"</span>
            </div>
            
            <?php if (empty($products)): ?>
            <div style="text-align: center; padding: 60px 20px; color: #999;">
                <i class="fas fa-search" style="font-size: 50px; display: block; margin-bottom: 15px;"></i>
                <h3>No products found</h3>
                <p>Try searching with different keywords</p>
            </div>
            <?php else: ?>
            <div class="product-grid">
                <?php foreach ($products as $p): ?>
                <?php include 'includes/product-card.php'; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>
