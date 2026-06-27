<?php
require_once 'config.php';
$pageTitle = 'Track Order';

$order = null;
$items = [];

if (isset($_GET['q'])) {
    $query = sanitize($_GET['q']);
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ? OR customer_phone = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$query, $query]);
    $order = $stmt->fetch();
    
    if ($order) {
        $itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $itemStmt->execute([$order['id']]);
        $items = $itemStmt->fetchAll();
    }
}

require_once 'includes/header.php';
?>

    <div class="container">
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>">Home</a> <span>/</span> <strong>Track Order</strong>
        </div>
    </div>

    <section style="padding: 30px 0;">
        <div class="container">
            <div style="max-width: 700px; margin: 0 auto;">
                <div class="admin-card" style="background:#fff; padding: 30px; border-radius: 12px; box-shadow: 0 2px 15px rgba(0,0,0,0.08);">
                    <h2 style="margin-bottom: 20px; text-align: center;"><i class="fas fa-truck"></i> Track Your Order</h2>
                    <form method="GET" action="" style="display: flex; gap: 10px; margin-bottom: 25px;">
                        <input type="text" name="q" placeholder="Enter Order Number or Phone Number" value="<?= $_GET['q'] ?? '' ?>" required style="flex: 1; padding: 12px 20px; border: 2px solid #ddd; border-radius: 8px; font-size: 15px;">
                        <button type="submit" style="padding: 12px 25px; background: var(--secondary, #ff6b35); color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;"><i class="fas fa-search"></i> Track</button>
                    </form>

                    <?php if (isset($_GET['q']) && !$order): ?>
                    <div style="text-align: center; padding: 30px; color: #999;">
                        <i class="fas fa-search" style="font-size: 40px; display: block; margin-bottom: 10px;"></i>
                        <p>No order found with this information</p>
                    </div>
                    <?php endif; ?>

                    <?php if ($order): ?>
                    <div style="border: 1px solid #eee; border-radius: 10px; padding: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <h3>Order #<?= $order['order_number'] ?></h3>
                            <span class="status-badge status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
                        </div>
                        
                        <!-- Status Timeline -->
                        <div style="margin: 20px 0; padding: 20px; background: #f9f9f9; border-radius: 8px;">
                            <?php
                            $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
                            $currentIndex = array_search($order['status'], $statuses);
                            if ($order['status'] === 'cancelled') $currentIndex = -1;
                            ?>
                            <div style="display: flex; justify-content: space-between; position: relative;">
                                <?php foreach ($statuses as $i => $s): ?>
                                <div style="text-align: center; flex: 1;">
                                    <div style="width: 30px; height: 30px; border-radius: 50%; margin: 0 auto 5px; display: flex; align-items: center; justify-content: center; font-size: 12px; 
                                        background: <?= $i <= $currentIndex ? '#4caf50' : '#ddd' ?>; color: <?= $i <= $currentIndex ? '#fff' : '#999' ?>;">
                                        <?= $i <= $currentIndex ? '<i class="fas fa-check"></i>' : ($i + 1) ?>
                                    </div>
                                    <small style="font-size: 10px; color: <?= $i <= $currentIndex ? '#4caf50' : '#999' ?>;"><?= ucfirst($s) ?></small>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if ($order['status'] === 'cancelled'): ?>
                            <p style="text-align: center; color: #c62828; margin-top: 10px;"><i class="fas fa-times-circle"></i> This order has been cancelled</p>
                            <?php endif; ?>
                        </div>

                        <div style="margin-top: 15px;">
                            <p><strong>Date:</strong> <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></p>
                            <p><strong>Total:</strong> ৳<?= number_format($order['total']) ?></p>
                            <p><strong>Payment:</strong> <?= ucfirst($order['payment_method']) ?></p>
                            <p><strong>Delivery:</strong> <?= $order['city'] ?></p>
                        </div>

                        <h4 style="margin-top: 20px; margin-bottom: 10px;">Items:</h4>
                        <?php foreach ($items as $item): ?>
                        <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f0f0f0; font-size: 14px;">
                            <span><?= sanitize($item['product_title']) ?> (x<?= $item['quantity'] ?>)</span>
                            <strong>৳<?= number_format($item['subtotal']) ?></strong>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>
