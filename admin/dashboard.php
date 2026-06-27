<?php
require_once 'auth.php';

$pageTitle = 'Dashboard';

// Stats
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$todayOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()")->fetchColumn();
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status != 'cancelled'")->fetchColumn();
$todayRevenue = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE DATE(created_at) = CURDATE() AND status != 'cancelled'")->fetchColumn();

// Recent Orders
$recentOrders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10")->fetchAll();

require_once 'includes/admin-header.php';
?>

<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-icon" style="background: #e3f2fd;"><i class="fas fa-box" style="color: #1976d2;"></i></div>
        <div class="stat-info">
            <h3><?= $totalProducts ?></h3>
            <p>Total Products</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #fff3e0;"><i class="fas fa-shopping-bag" style="color: #f57c00;"></i></div>
        <div class="stat-info">
            <h3><?= $totalOrders ?></h3>
            <p>Total Orders</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #fce4ec;"><i class="fas fa-clock" style="color: #c62828;"></i></div>
        <div class="stat-info">
            <h3><?= $pendingOrders ?></h3>
            <p>Pending Orders</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #e8f5e9;"><i class="fas fa-money-bill" style="color: #2e7d32;"></i></div>
        <div class="stat-info">
            <h3>৳<?= number_format($totalRevenue) ?></h3>
            <p>Total Revenue</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #f3e5f5;"><i class="fas fa-calendar-day" style="color: #7b1fa2;"></i></div>
        <div class="stat-info">
            <h3><?= $todayOrders ?></h3>
            <p>Today's Orders</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #e0f7fa;"><i class="fas fa-coins" style="color: #00838f;"></i></div>
        <div class="stat-info">
            <h3>৳<?= number_format($todayRevenue) ?></h3>
            <p>Today's Revenue</p>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="admin-card">
    <div class="card-header">
        <h2><i class="fas fa-clock"></i> Recent Orders</h2>
        <a href="orders.php" class="btn btn-sm">View All</a>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentOrders)): ?>
                <tr><td colspan="8" style="text-align: center; padding: 30px; color: #999;">No orders yet</td></tr>
                <?php else: ?>
                <?php foreach ($recentOrders as $order): ?>
                <tr>
                    <td><strong><?= $order['order_number'] ?></strong></td>
                    <td><?= sanitize($order['customer_name']) ?></td>
                    <td><?= $order['customer_phone'] ?></td>
                    <td><strong>৳<?= number_format($order['total']) ?></strong></td>
                    <td><?= ucfirst($order['payment_method']) ?></td>
                    <td><span class="status-badge status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                    <td><?= date('d M, h:i A', strtotime($order['created_at'])) ?></td>
                    <td><a href="order-detail.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-info">View</a></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
