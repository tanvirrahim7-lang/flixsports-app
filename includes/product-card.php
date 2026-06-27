<div class="product-card">
    <?php if ($p['discount'] > 0): ?>
    <span class="sale-badge">-<?= $p['discount'] ?>%</span>
    <?php endif; ?>
    <a href="<?= SITE_URL ?>/product.php?id=<?= $p['id'] ?>">
        <div class="product-image">
            <img src="<?= $p['primary_image'] ? UPLOAD_URL . $p['primary_image'] : 'https://placehold.co/300x300/f0f0f0/999?text=No+Image' ?>" alt="<?= sanitize($p['title']) ?>" loading="lazy">
        </div>
    </a>
    <div class="product-info">
        <a href="<?= SITE_URL ?>/product.php?id=<?= $p['id'] ?>">
            <div class="product-title"><?= sanitize($p['title']) ?></div>
        </a>
        <div class="product-price">
            ৳<?= number_format($p['price']) ?>
            <?php if ($p['original_price']): ?>
            <span class="original-price">৳<?= number_format($p['original_price']) ?></span>
            <?php endif; ?>
        </div>
        <div class="product-meta">
            <span class="product-rating">
                <?= str_repeat('<i class="fas fa-star"></i>', (int)$p['rating']) ?> <?= $p['rating'] ?>
            </span>
            <span><?= $p['total_sold'] ?> sold</span>
        </div>
    </div>
</div>
