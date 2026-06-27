<?php
require_once 'auth.php';
$pageTitle = 'Add Product';

$categories = $pdo->query("SELECT * FROM categories WHERE status = 1 ORDER BY sort_order")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $slug = slugify($title) . '-' . uniqid();
    $category_id = (int)$_POST['category_id'];
    $description = sanitize($_POST['description']);
    $price = (float)$_POST['price'];
    $original_price = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
    $discount = $original_price ? round((($original_price - $price) / $original_price) * 100) : 0;
    $stock = (int)$_POST['stock'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    $flash_sale = isset($_POST['flash_sale']) ? 1 : 0;
    $status = isset($_POST['status']) ? 1 : 0;
    
    // Insert product
    $stmt = $pdo->prepare("INSERT INTO products (category_id, title, slug, description, price, original_price, discount, stock, featured, flash_sale, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$category_id, $title, $slug, $description, $price, $original_price, $discount, $stock, $featured, $flash_sale, $status]);
    $productId = $pdo->lastInsertId();
    
    // Upload images
    if (!empty($_FILES['images']['name'][0])) {
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
                    $isPrimary = ($key === 0) ? 1 : 0;
                    $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)")
                        ->execute([$productId, $result['filename'], $isPrimary, $key]);
                }
            }
        }
    }
    
    // Add variants - Models
    if (!empty($_POST['models'])) {
        $models = array_filter(array_map('trim', explode("\n", $_POST['models'])));
        foreach ($models as $i => $model) {
            $pdo->prepare("INSERT INTO product_variants (product_id, variant_type, variant_value, sort_order) VALUES (?, 'model', ?, ?)")
                ->execute([$productId, $model, $i]);
        }
    }
    
    // Add variants - Sizes
    if (!empty($_POST['sizes'])) {
        $sizes = array_filter(array_map('trim', explode(",", $_POST['sizes'])));
        foreach ($sizes as $i => $size) {
            $pdo->prepare("INSERT INTO product_variants (product_id, variant_type, variant_value, sort_order) VALUES (?, 'size', ?, ?)")
                ->execute([$productId, trim($size), $i]);
        }
    }
    
    // Add variants - Colors
    if (!empty($_POST['color_names'])) {
        foreach ($_POST['color_names'] as $i => $colorName) {
            if (!empty($colorName)) {
                $colorCode = $_POST['color_codes'][$i] ?? '#000000';
                $pdo->prepare("INSERT INTO product_variants (product_id, variant_type, variant_value, variant_code, sort_order) VALUES (?, 'color', ?, ?, ?)")
                    ->execute([$productId, trim($colorName), $colorCode, $i]);
            }
        }
    }
    
    // Add features
    if (!empty($_POST['features'])) {
        $features = array_filter(array_map('trim', explode("\n", $_POST['features'])));
        foreach ($features as $feature) {
            $pdo->prepare("INSERT INTO product_features (product_id, feature_text) VALUES (?, ?)")
                ->execute([$productId, $feature]);
        }
    }
    
    redirect('products.php?msg=added');
}

require_once 'includes/admin-header.php';
?>

<div class="admin-card">
    <form method="POST" enctype="multipart/form-data" class="product-form">
        <div class="form-section">
            <h3><i class="fas fa-info-circle"></i> Basic Info</h3>
            <div class="form-group">
                <label>Product Title *</label>
                <input type="text" name="title" required placeholder="e.g. Premium Silicone Case - Matte Finish">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Stock</label>
                    <input type="number" name="stock" value="100" min="0">
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="4" placeholder="Product description..."></textarea>
            </div>
        </div>

        <div class="form-section">
            <h3><i class="fas fa-tag"></i> Pricing</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Selling Price (৳) *</label>
                    <input type="number" name="price" required min="0" step="0.01" placeholder="250">
                </div>
                <div class="form-group">
                    <label>Original Price (৳) - for showing discount</label>
                    <input type="number" name="original_price" min="0" step="0.01" placeholder="500">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3><i class="fas fa-images"></i> Images (Max 5)</h3>
            <div class="form-group">
                <input type="file" name="images[]" multiple accept="image/*" id="imageInput">
                <small>First image will be the main/primary image. Max 5MB each. JPG, PNG, WEBP supported.</small>
                <div id="imagePreview" class="image-preview-grid"></div>
            </div>
        </div>

        <div class="form-section">
            <h3><i class="fas fa-mobile-alt"></i> Phone Models (for Phone Covers)</h3>
            <div class="form-group">
                <label>Models (one per line)</label>
                <textarea name="models" rows="5" placeholder="iPhone 15&#10;iPhone 15 Pro&#10;iPhone 14&#10;Samsung S24&#10;Samsung A54"></textarea>
                <small>Leave empty if not applicable</small>
            </div>
        </div>

        <div class="form-section">
            <h3><i class="fas fa-ruler"></i> Sizes (for Dresses)</h3>
            <div class="form-group">
                <label>Sizes (comma separated)</label>
                <input type="text" name="sizes" placeholder="S, M, L, XL, XXL">
                <small>Leave empty if not applicable</small>
            </div>
        </div>

        <div class="form-section">
            <h3><i class="fas fa-palette"></i> Colors</h3>
            <div id="colorVariants">
                <div class="color-variant-row">
                    <input type="color" name="color_codes[]" value="#000000">
                    <input type="text" name="color_names[]" placeholder="Color name (e.g. Black)">
                    <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <button type="button" class="btn btn-sm" onclick="addColorRow()"><i class="fas fa-plus"></i> Add Color</button>
        </div>

        <div class="form-section">
            <h3><i class="fas fa-list"></i> Features</h3>
            <div class="form-group">
                <label>Features (one per line)</label>
                <textarea name="features" rows="4" placeholder="Anti-fingerprint matte finish&#10;Raised camera protection&#10;Wireless charging compatible"></textarea>
            </div>
        </div>

        <div class="form-section">
            <h3><i class="fas fa-cog"></i> Settings</h3>
            <div class="form-group">
                <label class="switch-label">
                    <input type="checkbox" name="status" checked>
                    <span class="switch-slider"></span> Active (visible on website)
                </label>
            </div>
            <div class="form-group">
                <label class="switch-label">
                    <input type="checkbox" name="featured">
                    <span class="switch-slider"></span> Featured Product (show on homepage)
                </label>
            </div>
            <div class="form-group">
                <label class="switch-label">
                    <input type="checkbox" name="flash_sale">
                    <span class="switch-slider"></span> Flash Sale
                </label>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Save Product</button>
            <a href="products.php" class="btn btn-secondary btn-lg">Cancel</a>
        </div>
    </form>
</div>

<script>
function addColorRow() {
    const div = document.getElementById('colorVariants');
    const row = document.createElement('div');
    row.className = 'color-variant-row';
    row.innerHTML = `
        <input type="color" name="color_codes[]" value="#000000">
        <input type="text" name="color_names[]" placeholder="Color name (e.g. Red)">
        <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
    `;
    div.appendChild(row);
}

// Image preview
document.getElementById('imageInput').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    Array.from(e.target.files).slice(0, 5).forEach(file => {
        const reader = new FileReader();
        reader.onload = function(ev) {
            preview.innerHTML += `<img src="${ev.target.result}" style="width:80px;height:80px;object-fit:cover;border-radius:5px;">`;
        };
        reader.readAsDataURL(file);
    });
});
</script>

<?php require_once 'includes/admin-footer.php'; ?>
