    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3><?= $siteName ?></h3>
                    <p><?= $siteSettings['site_tagline'] ?? 'Premium Phone Covers & Ladies Fashion' ?></p>
                    <?php if (!empty($siteSettings['site_phone'])): ?>
                    <p><i class="fas fa-phone"></i> <?= $siteSettings['site_phone'] ?></p>
                    <?php endif; ?>
                    <?php if (!empty($siteSettings['site_email'])): ?>
                    <p><i class="fas fa-envelope"></i> <?= $siteSettings['site_email'] ?></p>
                    <?php endif; ?>
                </div>
                <div class="footer-col">
                    <h4>Categories</h4>
                    <ul>
                        <?php foreach ($navCategories as $cat): ?>
                        <li><a href="<?= SITE_URL ?>/category.php?slug=<?= $cat['slug'] ?>"><?= $cat['name'] ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Customer Service</h4>
                    <ul>
                        <li><a href="<?= SITE_URL ?>/track-order.php">Track Order</a></li>
                        <li><a href="<?= SITE_URL ?>/cart.php">Shopping Cart</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Follow Us</h4>
                    <div class="social-links">
                        <?php if (!empty($siteSettings['facebook_url'])): ?>
                        <a href="<?= $siteSettings['facebook_url'] ?>" target="_blank"><i class="fab fa-facebook"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($siteSettings['instagram_url'])): ?>
                        <a href="<?= $siteSettings['instagram_url'] ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($siteSettings['whatsapp_number'])): ?>
                        <a href="https://wa.me/<?= $siteSettings['whatsapp_number'] ?>" target="_blank"><i class="fab fa-whatsapp"></i></a>
                        <?php endif; ?>
                    </div>
                    <div class="payment-methods" style="margin-top: 15px;">
                        <h4>Payment Methods</h4>
                        <div style="display: flex; gap: 10px; margin-top: 8px; flex-wrap: wrap;">
                            <?php if ($siteSettings['bkash_enabled'] ?? '0'): ?>
                            <span style="background: #e2136e; color: #fff; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 600;">bKash</span>
                            <?php endif; ?>
                            <?php if ($siteSettings['nagad_enabled'] ?? '0'): ?>
                            <span style="background: #f26522; color: #fff; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 600;">Nagad</span>
                            <?php endif; ?>
                            <?php if ($siteSettings['cod_enabled'] ?? '0'): ?>
                            <span style="background: #333; color: #fff; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 600;">COD</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= $siteName ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
