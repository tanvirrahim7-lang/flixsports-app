<?php
require_once 'auth.php';
$pageTitle = 'Order Detail';

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('orders.php');

$order = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$order->execute([$id]);
$order = $order->fetch();
if (!$order) redirect('orders.php');

$items = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$items->execute([$id]);
$items = $items->fetchAll();

// Update note
if (isset($_POST['save_note'])) {
    $pdo->prepare("UPDATE orders SET admin_note = ? WHERE id = ?")->execute([sanitize($_POST['admin_note']), $id]);
    redirect("order-detail.php?id=$id&msg=saved");
}

require_once 'includes/admin-header.php';
?>

<?php if (isset($_GET['msg'])): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> Saved!</div>
<?php endif; ?>

<div class="order-detail-grid">
    <div class="admin-card">
        <h3><i class="fas fa-shopping-bag"></i> Order #<?= $order['order_number'] ?></h3>
        <p>Date: <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></p>
        <p>Status: <span class="status-badge status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></p>
        
        <hr style="margin: 15px 0;">
        
        <h4>Customer Info</h4>
        <p><strong>Name:</strong> <?= sanitize($order['customer_name']) ?></p>
        <p><strong>Phone:</strong> <a href="tel:<?= $order['customer_phone'] ?>"><?= $order['customer_phone'] ?></a></p>
        <?php if ($order['customer_email']): ?>
        <p><strong>Email:</strong> <?= $order['customer_email'] ?></p>
        <?php endif; ?>
        <p><strong>Address:</strong> <?= sanitize($order['shipping_address']) ?></p>
        <p><strong>City:</strong> <?= sanitize($order['city']) ?></p>
        <?php if ($order['area']): ?>
        <p><strong>Area:</strong> <?= sanitize($order['area']) ?></p>
        <?php endif; ?>
        
        <hr style="margin: 15px 0;">
        
        <h4>Payment</h4>
        <p><strong>Method:</strong> <?= ucfirst($order['payment_method']) ?></p>
        <?php if ($order['payment_number']): ?>
        <p><strong>Sender Number:</strong> <?= $order['payment_number'] ?></p>
        <?php endif; ?>
        <?php if ($order['transaction_id']): ?>
        <p><strong>Transaction ID:</strong> <?= $order['transaction_id'] ?></p>
        <?php endif; ?>
    </div>
    
    <div class="admin-card">
        <h3><i class="fas fa-list"></i> Order Items</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Variant</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= sanitize($item['product_title']) ?></td>
                    <td>
                        <?php if ($item['variant_model']): ?><small>Model: <?= $item['variant_model'] ?></small><br><?php endif; ?>
                        <?php if ($item['variant_color']): ?><small>Color: <?= $item['variant_color'] ?></small><br><?php endif; ?>
                        <?php if ($item['variant_size']): ?><small>Size: <?= $item['variant_size'] ?></small><?php endif; ?>
                    </td>
                    <td>৳<?= number_format($item['price']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td>৳<?= number_format($item['subtotal']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr><td colspan="4" style="text-align:right;"><strong>Subtotal:</strong></td><td>৳<?= number_format($order['subtotal']) ?></td></tr>
                <tr><td colspan="4" style="text-align:right;"><strong>Delivery:</strong></td><td>৳<?= number_format($order['delivery_charge']) ?></td></tr>
                <tr><td colspan="4" style="text-align:right;"><strong style="font-size:18px;">Total:</strong></td><td><strong style="font-size:18px; color: var(--secondary);">৳<?= number_format($order['total']) ?></strong></td></tr>
            </tfoot>
        </table>
        
        <hr style="margin: 20px 0;">
        
        <h4>Admin Note</h4>
        <form method="POST">
            <textarea name="admin_note" rows="3" placeholder="Add internal note..."><?= sanitize($order['admin_note'] ?? '') ?></textarea>
            <button type="submit" name="save_note" class="btn btn-primary btn-sm" style="margin-top: 10px;"><i class="fas fa-save"></i> Save Note</button>
        </form>
    </div>
</div>

<a href="orders.php" class="btn btn-secondary" style="margin-top: 20px;"><i class="fas fa-arrow-left"></i> Back to Orders</a>

<?php require_once 'includes/admin-footer.php'; ?>
