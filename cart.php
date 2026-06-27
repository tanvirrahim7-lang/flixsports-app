<?php
require_once 'config.php';
$pageTitle = 'Shopping Cart';

$cartItems = $_SESSION['cart'] ?? [];
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

require_once 'includes/header.php';
?>

    <div class="container">
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>">Home</a> <span>/</span> <strong>Shopping Cart</strong>
        </div>
    </div>

    <section class="cart-page">
        <div class="container">
            <div class="cart-container">
                <div class="cart-header">
                    <h1><i class="fas fa-shopping-cart"></i> Shopping Cart (<?= count($cartItems) ?> items)</h1>
                    <?php if (!empty($cartItems)): ?>
                    <a href="<?= SITE_URL ?>/cart-action.php?action=clear" onclick="return confirm('Clear entire cart?')" style="color: #999; font-size: 13px;"><i class="fas fa-trash"></i> Clear All</a>
                    <?php endif; ?>
                </div>
                
                <?php if (empty($cartItems)): ?>
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h2>Your cart is empty</h2>
                    <p>Looks like you haven't added anything yet</p>
                    <a href="<?= SITE_URL ?>"><i class="fas fa-arrow-left"></i> Continue Shopping</a>
                </div>
                <?php else: ?>
                
                <?php foreach ($cartItems as $key => $item): ?>
                <div class="cart-item">
                    <div class="cart-item-image">
                        <img src="<?= $item['image'] ? UPLOAD_URL . $item['image'] : 'https://placehold.co/100x100/eee/999?text=No+Img' ?>" alt="">
                    </div>
                    <div class="cart-item-info">
                        <h3><?= sanitize($item['title']) ?></h3>
                        <div class="item-variant">
                            <?php if ($item['model']): ?><span>Model: <?= $item['model'] ?></span> <?php endif; ?>
                            <?php if ($item['size']): ?><span>| Size: <?= $item['size'] ?></span> <?php endif; ?>
                            <?php if ($item['color']): ?><span>| Color: <?= $item['color'] ?></span> <?php endif; ?>
                        </div>
                    </div>
                    <div class="cart-item-qty">
                        <form method="POST" action="<?= SITE_URL ?>/cart-action.php" style="display: flex; align-items: center;">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="key" value="<?= $key ?>">
                            <div class="qty-controls">
                                <button type="submit" name="quantity" value="<?= max(1, $item['quantity'] - 1) ?>" class="qty-btn">-</button>
                                <span class="qty-input" style="display:flex;align-items:center;justify-content:center;"><?= $item['quantity'] ?></span>
                                <button type="submit" name="quantity" value="<?= min(10, $item['quantity'] + 1) ?>" class="qty-btn">+</button>
                            </div>
                        </form>
                    </div>
                    <div class="cart-item-price">৳<?= number_format($item['price'] * $item['quantity']) ?></div>
                    <a href="<?= SITE_URL ?>/cart-action.php?action=remove&key=<?= urlencode($key) ?>" class="cart-item-remove"><i class="fas fa-trash-alt"></i></a>
                </div>
                <?php endforeach; ?>

                <div class="cart-summary">
                    <div class="cart-total">
                        Subtotal: <span>৳<?= number_format($subtotal) ?></span>
                    </div>
                    <a href="<?= SITE_URL ?>/checkout.php" class="checkout-btn">
                        <i class="fas fa-lock"></i> Proceed to Checkout
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>
