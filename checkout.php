<?php
require_once 'config.php';
$pageTitle = 'Checkout';

$cartItems = $_SESSION['cart'] ?? [];
if (empty($cartItems)) redirect(SITE_URL . '/cart.php');

$settings = getAllSettings();
$deliveryDhaka = (int)($settings['delivery_dhaka'] ?? 80);
$deliveryOutside = (int)($settings['delivery_outside'] ?? 150);

$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $area = sanitize($_POST['area'] ?? '');
    $paymentMethod = sanitize($_POST['payment_method'] ?? '');
    $paymentNumber = sanitize($_POST['payment_number'] ?? '');
    $transactionId = sanitize($_POST['transaction_id'] ?? '');
    
    // Validation
    if (empty($name) || empty($phone) || empty($address) || empty($city) || empty($paymentMethod)) {
        $error = 'Please fill all required fields!';
    } else {
        $deliveryCharge = (strtolower($city) === 'dhaka') ? $deliveryDhaka : $deliveryOutside;
        
        // Free delivery check
        $freeMin = (int)($settings['free_delivery_min'] ?? 0);
        if ($freeMin > 0 && $subtotal >= $freeMin) {
            $deliveryCharge = 0;
        }
        
        $total = $subtotal + $deliveryCharge;
        $orderNumber = generateOrderNumber();
        
        // Insert order
        $stmt = $pdo->prepare("INSERT INTO orders (order_number, customer_name, customer_phone, customer_email, shipping_address, city, area, payment_method, payment_number, transaction_id, subtotal, delivery_charge, total, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$orderNumber, $name, $phone, $email, $address, $city, $area, $paymentMethod, $paymentNumber, $transactionId, $subtotal, $deliveryCharge, $total]);
        $orderId = $pdo->lastInsertId();
        
        // Insert order items
        foreach ($cartItems as $item) {
            $itemSubtotal = $item['price'] * $item['quantity'];
            $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_title, variant_model, variant_color, variant_size, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
                ->execute([$orderId, $item['product_id'], $item['title'], $item['model'], $item['color'], $item['size'], $item['price'], $item['quantity'], $itemSubtotal]);
            
            // Update sold count
            $pdo->prepare("UPDATE products SET total_sold = total_sold + ? WHERE id = ?")->execute([$item['quantity'], $item['product_id']]);
        }
        
        // Clear cart
        $_SESSION['cart'] = [];
        
        // Redirect to success
        redirect(SITE_URL . '/order-success.php?order=' . $orderNumber);
    }
}

require_once 'includes/header.php';
?>

    <div class="container">
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>">Home</a> <span>/</span> <a href="<?= SITE_URL ?>/cart.php">Cart</a> <span>/</span> <strong>Checkout</strong>
        </div>
    </div>

    <section class="cart-page">
        <div class="container">
            <?php if ($error): ?>
            <div class="alert alert-danger" style="background:#fce4ec; color:#c62828; padding:12px 20px; border-radius:8px; margin-bottom:20px;">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
            <?php endif; ?>

            <form method="POST" id="checkoutForm">
                <div class="checkout-grid">
                    <!-- Shipping Info -->
                    <div class="admin-card">
                        <h2 style="margin-bottom: 20px;"><i class="fas fa-map-marker-alt"></i> Shipping Information</h2>
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="name" required placeholder="আপনার নাম" value="<?= $_POST['name'] ?? '' ?>">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Phone Number *</label>
                                <input type="tel" name="phone" required placeholder="01XXXXXXXXX" value="<?= $_POST['phone'] ?? '' ?>">
                            </div>
                            <div class="form-group">
                                <label>Email (optional)</label>
                                <input type="email" name="email" placeholder="email@example.com" value="<?= $_POST['email'] ?? '' ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Full Address *</label>
                            <textarea name="address" required rows="3" placeholder="House/Flat, Road, Area..."><?= $_POST['address'] ?? '' ?></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>City *</label>
                                <select name="city" id="citySelect" required onchange="updateDelivery()">
                                    <option value="">Select City</option>
                                    <option value="Dhaka" <?= ($_POST['city'] ?? '') === 'Dhaka' ? 'selected' : '' ?>>Dhaka (৳<?= $deliveryDhaka ?>)</option>
                                    <option value="Outside Dhaka" <?= ($_POST['city'] ?? '') === 'Outside Dhaka' ? 'selected' : '' ?>>Outside Dhaka (৳<?= $deliveryOutside ?>)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Area</label>
                                <input type="text" name="area" placeholder="Mirpur, Gulshan, etc." value="<?= $_POST['area'] ?? '' ?>">
                            </div>
                        </div>

                        <hr style="margin: 25px 0;">

                        <h2 style="margin-bottom: 20px;"><i class="fas fa-credit-card"></i> Payment Method *</h2>
                        
                        <?php if (($settings['bkash_enabled'] ?? '0') == '1'): ?>
                        <div class="payment-option">
                            <label>
                                <input type="radio" name="payment_method" value="bkash" required>
                                <span style="background: #e2136e; color:#fff; padding: 3px 10px; border-radius: 4px; font-weight: 600;">bKash</span>
                                - Send Money to: <strong><?= $settings['bkash_number'] ?? '' ?></strong>
                            </label>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (($settings['nagad_enabled'] ?? '0') == '1'): ?>
                        <div class="payment-option">
                            <label>
                                <input type="radio" name="payment_method" value="nagad" required>
                                <span style="background: #f26522; color:#fff; padding: 3px 10px; border-radius: 4px; font-weight: 600;">Nagad</span>
                                - Send Money to: <strong><?= $settings['nagad_number'] ?? '' ?></strong>
                            </label>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (($settings['cod_enabled'] ?? '0') == '1'): ?>
                        <div class="payment-option">
                            <label>
                                <input type="radio" name="payment_method" value="cod" required>
                                <span style="background: #333; color:#fff; padding: 3px 10px; border-radius: 4px; font-weight: 600;">COD</span>
                                - Cash on Delivery
                            </label>
                        </div>
                        <?php endif; ?>

                        <div id="paymentDetails" style="display: none; margin-top: 15px; padding: 15px; background: #f9f9f9; border-radius: 8px;">
                            <div class="form-group">
                                <label>Sender Number (যে নম্বর থেকে পাঠিয়েছেন)</label>
                                <input type="text" name="payment_number" placeholder="01XXXXXXXXX">
                            </div>
                            <div class="form-group">
                                <label>Transaction ID (TxID)</label>
                                <input type="text" name="transaction_id" placeholder="Transaction ID">
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="admin-card">
                        <h2 style="margin-bottom: 20px;"><i class="fas fa-receipt"></i> Order Summary</h2>
                        <?php foreach ($cartItems as $item): ?>
                        <div style="display: flex; gap: 10px; padding: 10px 0; border-bottom: 1px solid #f0f0f0; align-items: center;">
                            <img src="<?= $item['image'] ? UPLOAD_URL . $item['image'] : 'https://placehold.co/50x50/eee/999?text=Img' ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                            <div style="flex: 1;">
                                <div style="font-size: 13px; color: #333;"><?= sanitize(mb_substr($item['title'], 0, 35)) ?>...</div>
                                <small style="color: #888;">
                                    <?= $item['model'] ? $item['model'] . ' | ' : '' ?>
                                    <?= $item['color'] ? $item['color'] . ' | ' : '' ?>
                                    <?= $item['size'] ? $item['size'] . ' | ' : '' ?>
                                    x<?= $item['quantity'] ?>
                                </small>
                            </div>
                            <strong>৳<?= number_format($item['price'] * $item['quantity']) ?></strong>
                        </div>
                        <?php endforeach; ?>

                        <div style="margin-top: 20px; padding-top: 15px; border-top: 2px solid #f0f0f0;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span>Subtotal</span>
                                <span>৳<?= number_format($subtotal) ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span>Delivery</span>
                                <span id="deliveryCharge">Select city</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 20px; font-weight: 700; margin-top: 10px; padding-top: 10px; border-top: 1px solid #eee;">
                                <span>Total</span>
                                <span style="color: var(--secondary);" id="totalAmount">-</span>
                            </div>
                        </div>

                        <button type="submit" class="checkout-btn" style="width: 100%; margin-top: 20px;">
                            <i class="fas fa-check-circle"></i> Place Order
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <script>
    const deliveryDhaka = <?= $deliveryDhaka ?>;
    const deliveryOutside = <?= $deliveryOutside ?>;
    const subtotal = <?= $subtotal ?>;

    function updateDelivery() {
        const city = document.getElementById('citySelect').value;
        let delivery = 0;
        if (city === 'Dhaka') delivery = deliveryDhaka;
        else if (city === 'Outside Dhaka') delivery = deliveryOutside;
        
        document.getElementById('deliveryCharge').textContent = delivery > 0 ? '৳' + delivery : 'Select city';
        document.getElementById('totalAmount').textContent = delivery > 0 ? '৳' + (subtotal + delivery).toLocaleString() : '-';
    }

    // Show/hide payment details
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const details = document.getElementById('paymentDetails');
            details.style.display = (this.value === 'bkash' || this.value === 'nagad') ? 'block' : 'none';
        });
    });
    </script>

<?php require_once 'includes/footer.php'; ?>
