<?php
require_once 'auth.php';
$pageTitle = 'Products';

// Delete product
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Delete images from server
    $images = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ?");
    $images->execute([$id]);
    while ($img = $images->fetch()) {
        @unlink(UPLOAD_PATH . $img['image_path']);
    }
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    redirect('products.php?msg=deleted');
}

// Get products with category
$stmt = $pdo->query("
    SELECT p.*, c.name as category_name, 
    (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC
");
$products = $stmt->fetchAll();

require_once 'includes/admin-header.php';
?>

<?php if (isset($_GET['msg'])): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> 
    <?php
    switch($_GET['msg']) {
        case 'added': echo 'Product added successfully!'; break;
        case 'updated': echo 'Product updated successfully!'; break;
        case 'deleted': echo 'Product deleted successfully!'; break;
    }
    ?>
</div>
<?php endif; ?>

<div class="admin-card">
    <div class="card-header">
        <h2><i class="fas fa-box"></i> All Products (<?= count($products) ?>)</h2>
        <a href="product-add.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Product</a>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                <tr><td colspan="7" style="text-align: center; padding: 40px; color: #999;">
                    <i class="fas fa-box-open" style="font-size: 40px; display: block; margin-bottom: 10px;"></i>
                    No products yet. <a href="product-add.php">Add your first product</a>
                </td></tr>
                <?php else: ?>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td>
                        <img src="<?= $p['primary_image'] ? UPLOAD_URL . $p['primary_image'] : 'https://placehold.co/60x60/eee/999?text=No+Img' ?>" 
                             alt="" style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                    </td>
                    <td><strong><?= sanitize(mb_substr($p['title'], 0, 50)) ?></strong></td>
                    <td><span class="badge"><?= $p['category_name'] ?></span></td>
                    <td>
                        <strong>৳<?= number_format($p['price']) ?></strong>
                        <?php if ($p['original_price']): ?>
                        <br><small style="text-decoration: line-through; color: #999;">৳<?= number_format($p['original_price']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?= $p['stock'] ?></td>
                    <td>
                        <span class="status-badge status-<?= $p['status'] ? 'active' : 'inactive' ?>">
                            <?= $p['status'] ? 'Active' : 'Hidden' ?>
                        </span>
                        <?php if ($p['featured']): ?><span class="badge badge-gold">Featured</span><?php endif; ?>
                    </td>
                    <td>
                        <a href="product-edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-info" title="Edit"><i class="fas fa-edit"></i></a>
                        <a href="products.php?delete=<?= $p['id'] ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this product?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
