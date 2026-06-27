<?php
require_once 'config.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect(SITE_URL);

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ? AND p.status = 1");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) redirect(SITE_URL);

$pageTitle = $product['title'];

// Images
$imgStmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order");
$imgStmt->execute([$id]);
$images = $imgStmt->fetchAll();

// Variants
$varStmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY variant_type, sort_order");
$varStmt->execute([$id]);
$allVariants = $varStmt->fetchAll();

$models = array_values(array_filter($allVariants, fn($v) => $v['variant_type'] === 'model'));
$sizes = array_values(array_filter($allVariants, fn($v) => $v['variant_type'] === 'size'));
$colors = array_values(array_filter($allVariants, fn($v) => $v['variant_type'] === 'color'));

// Features
$featStmt = $pdo->prepare("SELECT * FROM product_features WHERE product_id = ?");
$featStmt->execute([$id]);
$features = $featStmt->fetchAll();

// Track page view
$pdo->prepare("INSERT INTO page_views (page, ip_address) VALUES (?, ?)")->execute(["product_$id", $_SERVER['REMOTE_ADDR'] ?? '']);

require_once 'includes/header.php';
?>

    <!-- Breadcrumb -->
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>">Home</a> <span>/</span>
            <a href="<?= SITE_URL ?>/category.php?slug=<?= $product['category_slug'] ?>"><?= $product['category_name'] ?></a> <span>/</span>
            <strong><?= sanitize(mb_substr($product['title'], 0, 40)) ?>...</strong>
        </div>
    </div>

    <!-- Product Detail -->
    <section class="product-detail">
        <div class="container">
            <div class="product-detail-layout">
                <!-- Gallery -->
                <div class="product-gallery">
                    <div class="main-image">
                        <img id="mainImage" src="<?= !empty($images) ? UPLOAD_URL . $images[0]['image_path'] : 'https://placehold.co/500x500/f0f0f0/999?text=No+Image' ?>" alt="<?= sanitize($product['title']) ?>">
                    </div>
                    <?php if (count($images) > 1): ?>
                    <div class="thumbnail-list">
                        <?php foreach ($images as $i => $img): ?>
                        <div class="thumbnail <?= $i === 0 ? 'active' : '' ?>" onclick="changeImage('<?= UPLOAD_URL . $img['image_path'] ?>', this)">
                            <img src="<?= UPLOAD_URL . $img['image_path'] ?>" alt="Thumbnail">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Info -->
                <div class="product-detail-info">
                    <h1><?= sanitize($product['title']) ?></h1>
                    
                    <div class="product-rating-detail">
                        <span class="stars"><?= str_repeat('<i class="fas fa-star"></i>', (int)$product['rating']) ?></span>
                        <span><?= $product['rating'] ?> rating</span>
                        <span class="product-sold"><?= $product['total_sold'] ?>+ sold</span>
                    </div>

                    <div class="product-detail-price">
                        <span>৳<?= number_format($product['price']) ?></span>
                        <?php if ($product['original_price']): ?>
                        <span class="original">৳<?= number_format($product['original_price']) ?></span>
                        <span class="discount">-<?= $product['discount'] ?>%</span>
                        <?php endif; ?>
                    </div>

                    <form method="POST" action="<?= SITE_URL ?>/cart-action.php">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <input type="hidden" name="action" value="add">

                        <?php if (!empty($models)): ?>
                        <!-- Phone Model -->
                        <div class="option-section">
                            <h3><i class="fas fa-mobile-alt"></i> Phone Model</h3>
                            <div class="option-list">
                                <?php foreach ($models as $model): ?>
                                <label class="option-item-label">
                                    <input type="radio" name="model" value="<?= sanitize($model['variant_value']) ?>" required hidden>
                                    <span class="option-item"><?= sanitize($model['variant_value']) ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($sizes)): ?>
                        <!-- Size -->
                        <div class="option-section">
                            <h3><i class="fas fa-ruler"></i> Size</h3>
                            <div class="option-list">
                                <?php foreach ($sizes as $size): ?>
                                <label class="option-item-label">
                                    <input type="radio" name="size" value="<?= sanitize($size['variant_value']) ?>" required hidden>
                                    <span class="option-item"><?= sanitize($size['variant_value']) ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($colors)): ?>
                        <!-- Color -->
                        <div class="option-section">
                            <h3><i class="fas fa-palette"></i> Color: <span id="selectedColorName">Select</span></h3>
                            <div class="color-option-list">
                                <?php foreach ($colors as $color): ?>
                                <label class="color-option-label">
                                    <input type="radio" name="color" value="<?= sanitize($color['variant_value']) ?>" required hidden>
                                    <span class="color-option" style="background: <?= $color['variant_code'] ?>;" title="<?= sanitize($color['variant_value']) ?>"></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Quantity -->
                        <div class="quantity-selector">
                            <label>Quantity:</label>
                            <div class="qty-controls">
                                <button type="button" class="qty-btn" onclick="changeQty(-1)">-</button>
                                <input type="number" class="qty-input" name="quantity" id="qtyInput" value="1" min="1" max="10">
                                <button type="button" class="qty-btn" onclick="changeQty(1)">+</button>
                            </div>
                            <span style="font-size: 13px; color: #888;"><?= $product['stock'] ?> available</span>
                        </div>

                        <!-- Buttons -->
                        <div class="action-buttons">
                            <button type="submit" name="submit_type" value="cart" class="btn-add-cart">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                            <button type="submit" name="submit_type" value="buy" class="btn-buy-now">
                                <i class="fas fa-bolt"></i> Buy Now
                            </button>
                        </div>
                    </form>

                    <?php if (!empty($features)): ?>
                    <div class="option-section">
                        <h3><i class="fas fa-check-circle"></i> Features</h3>
                        <ul style="padding-left: 20px; font-size: 13px; color: #555;">
                            <?php foreach ($features as $f): ?>
                            <li style="margin-bottom: 5px;"><?= sanitize($f['feature_text']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <?php if ($product['description']): ?>
                    <div class="option-section">
                        <h3><i class="fas fa-info-circle"></i> Description</h3>
                        <p style="font-size: 14px; color: #555; line-height: 1.8;"><?= nl2br(sanitize($product['description'])) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <script>
    // Option selection
    document.querySelectorAll('.option-item-label').forEach(label => {
        label.addEventListener('click', function() {
            this.closest('.option-list').querySelectorAll('.option-item').forEach(i => i.classList.remove('selected'));
            this.querySelector('.option-item').classList.add('selected');
        });
    });

    document.querySelectorAll('.color-option-label').forEach(label => {
        label.addEventListener('click', function() {
            document.querySelectorAll('.color-option').forEach(c => c.classList.remove('selected'));
            this.querySelector('.color-option').classList.add('selected');
            document.getElementById('selectedColorName').textContent = this.querySelector('input').value;
        });
    });

    function changeImage(src, thumb) {
        document.getElementById('mainImage').src = src;
        document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
        thumb.classList.add('active');
    }

    function changeQty(delta) {
        const input = document.getElementById('qtyInput');
        let val = parseInt(input.value) + delta;
        if (val < 1) val = 1;
        if (val > 10) val = 10;
        input.value = val;
    }
    </script>

<?php require_once 'includes/footer.php'; ?>
