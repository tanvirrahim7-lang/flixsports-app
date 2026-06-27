<?php
require_once 'auth.php';
$pageTitle = 'Categories';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $name = sanitize($_POST['name']);
        $slug = slugify($name);
        $icon = sanitize($_POST['icon'] ?? 'fas fa-box');
        $pdo->prepare("INSERT INTO categories (name, slug, icon) VALUES (?, ?, ?)")->execute([$name, $slug, $icon]);
    }
    if (isset($_POST['update'])) {
        $id = (int)$_POST['id'];
        $name = sanitize($_POST['name']);
        $icon = sanitize($_POST['icon']);
        $status = isset($_POST['status']) ? 1 : 0;
        $pdo->prepare("UPDATE categories SET name=?, icon=?, status=? WHERE id=?")->execute([$name, $icon, $status, $id]);
    }
    redirect('categories.php');
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([(int)$_GET['delete']]);
    redirect('categories.php');
}

$categories = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count FROM categories c ORDER BY sort_order")->fetchAll();

require_once 'includes/admin-header.php';
?>

<div class="admin-card">
    <div class="card-header">
        <h2><i class="fas fa-tags"></i> Categories</h2>
    </div>
    
    <!-- Add Category -->
    <form method="POST" class="inline-form" style="margin-bottom: 20px; padding: 15px; background: #f9f9f9; border-radius: 8px;">
        <div class="form-row">
            <div class="form-group">
                <input type="text" name="name" placeholder="Category Name" required>
            </div>
            <div class="form-group">
                <input type="text" name="icon" placeholder="Icon (e.g. fas fa-box)" value="fas fa-box">
            </div>
            <div class="form-group">
                <button type="submit" name="add" class="btn btn-primary"><i class="fas fa-plus"></i> Add</button>
            </div>
        </div>
    </form>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>Icon</th>
                <th>Name</th>
                <th>Slug</th>
                <th>Products</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $cat): ?>
            <tr>
                <td><i class="<?= $cat['icon'] ?>" style="font-size: 20px;"></i></td>
                <td><strong><?= sanitize($cat['name']) ?></strong></td>
                <td><code><?= $cat['slug'] ?></code></td>
                <td><?= $cat['product_count'] ?></td>
                <td><span class="status-badge status-<?= $cat['status'] ? 'active' : 'inactive' ?>"><?= $cat['status'] ? 'Active' : 'Hidden' ?></span></td>
                <td>
                    <a href="categories.php?delete=<?= $cat['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category? All products in it will also be deleted!')"><i class="fas fa-trash"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
