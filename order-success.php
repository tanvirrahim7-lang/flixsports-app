<?php
require_once 'config.php';
$pageTitle = 'Order Placed!';
$orderNumber = sanitize($_GET['order'] ?? '');

require_once 'includes/header.php';
?>

    <section style="padding: 60px 0; text-align: center;">
        <div class="container">
            <div style="max-width: 500px; margin: 0 auto; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 2px 15px rgba(0,0,0,0.08);">
                <div style="width: 80px; height: 80px; background: #e8f5e9; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <i class="fas fa-check" style="font-size: 35px; color: #4caf50;"></i>
                </div>
                <h1 style="color: #4caf50; margin-bottom: 10px;">Order Placed Successfully!</h1>
                <p style="color: #666; margin-bottom: 20px;">Thank you for your order. We'll process it soon.</p>
                
                <?php if ($orderNumber): ?>
                <div style="background: #f5f5f5; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <p style="font-size: 14px; color: #888;">Order Number</p>
                    <h2 style="color: #333;"><?= $orderNumber ?></h2>
                </div>
                <p style="font-size: 14px; color: #888;">Save this order number to track your delivery</p>
                <?php endif; ?>
                
                <div style="margin-top: 25px; display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                    <a href="<?= SITE_URL ?>/track-order.php" style="padding: 12px 25px; background: var(--secondary, #ff6b35); color: #fff; border-radius: 8px; font-weight: 600;">Track Order</a>
                    <a href="<?= SITE_URL ?>" style="padding: 12px 25px; background: #f0f0f0; color: #333; border-radius: 8px; font-weight: 600;">Continue Shopping</a>
                </div>
            </div>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>
