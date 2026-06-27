<?php
require_once 'auth.php';
$pageTitle = 'Orders';

// Update status
if (isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $newStatus = sanitize($_POST['new_status']);
    $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$newStatus, $orderId]);
    redirect('orders.php?msg=updated');
}

// Filter
$statusFilter = $_GET['status'] ?? '';
$where = '';
$params = [];
if ($statusFilter && in_array($statusFilter, ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])) {
    $where = "WHERE status = ?";
    $params[] = $statusFilter;
}

$stmt = $pdo->prepare("SELECT * FROM orders $where ORDER BY created_at DESC");
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Status counts
$counts = $pdo->query("SELECT status, COUNT(*) as cnt FROM orders GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);

require_once 'includes/admin-header.php';
?>

<?php if (isset($_GET['msg'])): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> Order status updated!</div>
<?php endif; ?>

<!-- Status Filter Tabs -->
<div class="order-filter-tabs">
    <a href="orders.php" class="<?= !$statusFilter ? 'active' : '' ?>">All (<?= array_sum($counts) ?>)</a>
    <a href="orders.php?status=pending" class="<?= $statusFilter === 'pending' ? 'active' : '' ?>">Pending (<?= $counts['pending'] ?? 0 ?>)</a>
    <a href="orders.php?status=confirmed" class="<?= $statusFilter === 'confirmed' ? 'active' : '' ?>">Confirmed (<?= $counts['confirmed'] ?? 0 ?>)</a>
    <a href="orders.php?status=processing" class="<?= $statusFilter === 'processing' ? 'active' : '' ?>">Processing (<?= $counts['processing'] ?? 0 ?>)</a>
    <a href="orders.php?status=shipped" class="<?= $statusFilter === 'shipped' ? 'active' : '' ?>">Shipped (<?= $counts['shipped'] ?? 0 ?>)</a>
    <a href="orders.php?status=delivered" class="<?= $statusFilter === 'delivered' ? 'active' : '' ?>">Delivered (<?= $counts['delivered'] ?? 0 ?>)</a>
    <a href="orders.php?status=cancelled" class="<?= $statusFilter === 'cancelled' ? 'active' : '' ?>">Cancelled (<?= $counts['cancelled'] ?? 0 ?>)</a>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>City</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                <tr><td colspan="9" style="text-align: center; padding: 40px; color: #999;">No orders found</td></tr>
                <?php else: ?>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><strong><?= $order['order_number'] ?></strong></td>
                    <td><?= sanitize($order['customer_name']) ?></td>
                    <td><a href="tel:<?= $order['customer_phone'] ?>"><?= $order['customer_phone'] ?></a></td>
                    <td><?= sanitize($order['city']) ?></td>
                    <td><strong>৳<?= number_format($order['total']) ?></strong></td>
                    <td>
                        <?= ucfirst($order['payment_method']) ?>
                        <?php if ($order['transaction_id']): ?>
                        <br><small>TxID: <?= $order['transaction_id'] ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <input type="hidden" name="update_status" value="1">
                            <select name="new_status" onchange="this.form.submit()" class="status-select status-<?= $order['status'] ?>">
                                <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="confirmed" <?= $order['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </form>
                    </td>
                    <td><?= date('d M, h:i A', strtotime($order['created_at'])) ?></td>
                    <td><a href="order-detail.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
