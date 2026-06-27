<?php
require_once 'auth.php';
$pageTitle = 'Edit Product';

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('products.php');

$product = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$product->execute([$id]);
$product = $product->fetch();
if (!$product) redirect('products.php');

$categories = $pdo->query("SELECT * FROM categories WHERE status = 1 ORDER BY sort_order")->fetchAll();
$images = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order");
$images->execute([$id]);
$images = $images->fetchAll();

$variants = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY variant_type, sort_order");
$variants->execute([$id]);
$allVariants = $variants->fetchAll();

$models = array_filter($allVariants, fn($v) => $v['variant_type'] === 'model');
$sizes = array_filter($allVariants, fn($v) => $v['variant_type'] === 'size');
$colors = array_filter($allVariants, fn($v) => $v['variant_type'] === 'color');

$features = $pdo->prepare("SELECT * FROM product_features WHERE product_id = ?");
$features->execute([$id]);
$features = $features->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $category_id = (int)$_POST['category_id'];
    $description = sanitize($_POST['description']);
    $price = (float)$_POST['price'];
    $original_price = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
    $discount = $original_price ? round((($original_price - $price) / $original_price) * 100) : 0;
    $stock = (int)$_POST['stock'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    $flash_sale = isset($_POST['flash_sale']) ? 1 : 0;
    $status = isset($_POST['status']) ? 1 : 0;
    
    $stmt = $pdo->prepare("UPDATE products SET category_id=?, title=?, description=?, price=?, original_price=?, discount=?, stock=?, featured=?, flash_sale=?, status=? WHERE id=?");
    $stmt->execute([$category_id, $title, $description, $price, $original_price, $discount, $stock, $featured, $flash_sale, $status, $id]);
    
    // Upload new images
    if (!empty($_FILES['images']['name'][0])) {
        $maxSort = $pdo->prepare("SELECT MAX(sort_order) FROM product_images WHERE product_id = ?");
        $maxSort->execute([$id]);
        $sortStart = ($maxSort->fetchColumn() ?? -1) + 1;
        
        foreach ($_FILES['images']['name'] as $key => $name) {
            if ($_FILES['images']['error'][$key] === 0) {
                $file = [
                    'name' => $_FILES['images']['name'][$key],
                    'type' => $_FILES['images']['type'][$key],
                    'tmp_name' => $_FILES['images']['tmp_name'][$key],
                    'size' => $_FILES['images']['size'][$key]
                ];
                $result = uploadImage($file);
                if (isset($result['filename'])) {
                    $hasPrimary = $pdo->prepare("SELECT COUNT(*) FROM product_images WHERE product_id = ? AND is_primary = 1");
                    $hasPrimary->execute([$id]);
                    $isPrimary = $hasPrimary->fetchColumn() == 0 ? 1 : 0;
                    $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)")
                        ->execute([$id, $result['filename'], $isPrimary, $sortStart + $key]);
                }
            }
        }
    }
    
    // Delete selected images
    if (!empty($_POST['delete_images'])) {
        foreach ($_POST['delete_images'] as $imgId) {
            $img = $pdo->prepare("SELECT image_path FROM product_images WHERE id = ?");
            $img->execute([$imgId]);
            $imgData = $img->fetch();
            if ($imgData) {
                @unlink(UPLOAD_PATH . $imgData['image_path']);
                $pdo->prepare("DELETE FROM product_images WHERE id = ?")->execute([$imgId]);
            }
        }
    }
    
    // Update variants - delete old and insert new
    $pdo->prepare("DELETE FROM product_variants WHERE product_id = ?")->execute([$id]);
    
    // Models
    if (!empty($_POST['models'])) {
        $modelList = array_filter(array_map('trim', explode("\n", $_POST['models'])));
        foreach ($modelList as $i => $model) {
            $pdo->prepare("INSERT INTO product_variants (product_id, variant_type, variant_value, sort_order) VALUES (?, 'model', ?, ?)")
                ->execute([$id, $model, $i]);
        }
    }
    
    // Sizes
    if (!empty($_POST['sizes'])) {
        $sizeList = array_filter(array_map('trim', explode(",", $_POST['sizes'])));
        foreach ($sizeList as $i => $size) {
            $pdo->prepare("INSERT INTO product_variants (product_id, variant_type, variant_value, sort_order) VALUES (?, 'size', ?, ?)")
                ->execute([$id, trim($size), $i]);
        }
    }
    
    // Colors
    if (!empty($_POST['color_names'])) {
        foreach ($_POST['color_names'] as $i => $colorName) {
            if (!empty($colorName)) {
                $colorCode = $_POST['color_codes'][$i] ?? '#000000';
                $pdo->prepare("INSERT INTO product_variants (product_id, variant_type, variant_value, variant_code, sort_order) VALUES (?, 'color', ?, ?, ?)")
                    ->execute([$id, trim($colorName), $colorCode, $i]);
            }
        }
    }
    
    // Features
    $pdo->prepare("DELETE FROM product_features WHERE product_id = ?")->execute([$id]);
    if (!empty($_POST['features'])) {
        $featureList = array_filter(array_map('trim', explode("\n", $_POST['features'])));
        foreach ($featureList as $feature) {
            $pdo->prepare("INSERT INTO product_features (product_id, feature_text) VALUES (?, ?)")
                ->execute([$id, $feature]);
        }
    }
    
    redirect('products.php?msg=updated');
}

require_once 'includes/admin-header.php';
?>

<div class="admin-card">
    <form method="POST" enctype="multipart/form-data" class="product-form">
        <div class="form-section">
            <h3><i class="fas fa-info-circle"></i> Basic Info</h3>
            <div class="form-group">
                <label>Product Title *</label>
                <input type="text" name="title" value="<?= sanitize($product['title']) ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category_id" required>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>><?= $cat['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Stock</label>
                    <input type="number" name="stock" value="<?= $product['stock'] ?>" min="0">
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="4"><?= sanitize($product['description']) ?></textarea>
            </div>
        </div>

        <div class="form-section">
            <h3><i class="fas fa-tag"></i> Pricing</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Selling Price (৳) *</label>
                    <input type="number" name="price" value="<?= $product['price'] ?>" required min="0" step="0.01">
                </div>
                <div class="form-group">
                    <label>Original Price (৳)</label>
                    <input type="number" name="original_price" value="<?= $product['original_price'] ?>" min="0" step="0.01">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3><i class="fas fa-images"></i> Current Images</h3>
            <div class="current-images">
                <?php foreach ($images as $img): ?>
                <div class="image-thumb">
                    <img src="<?= UPLOAD_URL . $img['image_path'] ?>" alt="">
                    <label><input type="checkbox" name="delete_images[]" value="<?= $img['id'] ?>"> Delete</label>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="form-group" style="margin-top: 15px;">
                <label>Add More Images</label>
                <input type="file" name="images[]" multiple accept="image/*">
            </div>
        </div>

        <div class="form-section">
            <h3><i class="fas fa-mobile-alt"></i> Phone Models</h3>
            <div class="form-group">
                <textarea name="models" rows="5"><?= implode("\n", array_column($models, 'variant_value')) ?></textarea>
            </div>
        </div>

        <div class="form-section">
            <h3><i class="fas fa-ruler"></i> Sizes</h3>
            <div class="form-group">
                <input type="text" name="sizes" value="<?= implode(", ", array_column($sizes, 'variant_value')) ?>">
            </div>
        </div>

        <div class="form-section">
            <h3><i class="fas fa-palette"></i> Colors</h3>
            <div id="colorVariants">
                <?php foreach ($colors as $color): ?>
                <div class="color-variant-row">
                    <input type="color" name="color_codes[]" value="<?= $color['variant_code'] ?>">
                    <input type="text" name="color_names[]" value="<?= sanitize($color['variant_value']) ?>">
                    <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm" onclick="addColorRow()"><i class="fas fa-plus"></i> Add Color</button>
        </div>

        <div class="form-section">
            <h3><i class="fas fa-list"></i> Features</h3>
            <div class="form-group">
                <textarea name="features" rows="4"><?= implode("\n", array_column($features, 'feature_text')) ?></textarea>
            </div>
        </div>

        <div class="form-section">
            <h3><i class="fas fa-cog"></i> Settings</h3>
            <div class="form-group">
                <label class="switch-label"><input type="checkbox" name="status" <?= $product['status'] ? 'checked' : '' ?>><span class="switch-slider"></span> Active</label>
            </div>
            <div class="form-group">
                <label class="switch-label"><input type="checkbox" name="featured" <?= $product['featured'] ? 'checked' : '' ?>><span class="switch-slider"></span> Featured</label>
            </div>
            <div class="form-group">
                <label class="switch-label"><input type="checkbox" name="flash_sale" <?= $product['flash_sale'] ? 'checked' : '' ?>><span class="switch-slider"></span> Flash Sale</label>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Update Product</button>
            <a href="products.php" class="btn btn-secondary btn-lg">Cancel</a>
        </div>
    </form>
</div>

<script>
function addColorRow() {
    const div = document.getElementById('colorVariants');
    const row = document.createElement('div');
    row.className = 'color-variant-row';
    row.innerHTML = `<input type="color" name="color_codes[]" value="#000000"><input type="text" name="color_names[]" placeholder="Color name"><button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>`;
    div.appendChild(row);
}
</script>

<?php require_once 'includes/admin-footer.php'; ?>
