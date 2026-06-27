<?php
require_once 'auth.php';
$pageTitle = 'Settings';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'general') {
        updateSetting('site_name', sanitize($_POST['site_name']));
        updateSetting('site_tagline', sanitize($_POST['site_tagline']));
        updateSetting('site_email', sanitize($_POST['site_email']));
        updateSetting('site_phone', sanitize($_POST['site_phone']));
        updateSetting('site_address', sanitize($_POST['site_address']));
        updateSetting('facebook_url', sanitize($_POST['facebook_url']));
        updateSetting('instagram_url', sanitize($_POST['instagram_url']));
        updateSetting('whatsapp_number', sanitize($_POST['whatsapp_number']));
        
        // Logo upload
        if (!empty($_FILES['site_logo']['name'])) {
            $result = uploadImage($_FILES['site_logo']);
            if (isset($result['filename'])) {
                updateSetting('site_logo', $result['filename']);
            }
        }
        
        $success = 'General settings updated!';
    }
    
    if ($action === 'delivery') {
        updateSetting('delivery_dhaka', sanitize($_POST['delivery_dhaka']));
        updateSetting('delivery_outside', sanitize($_POST['delivery_outside']));
        updateSetting('free_delivery_min', sanitize($_POST['free_delivery_min']));
        $success = 'Delivery settings updated!';
    }
    
    if ($action === 'payment') {
        updateSetting('cod_enabled', isset($_POST['cod_enabled']) ? '1' : '0');
        updateSetting('bkash_enabled', isset($_POST['bkash_enabled']) ? '1' : '0');
        updateSetting('bkash_number', sanitize($_POST['bkash_number']));
        updateSetting('nagad_enabled', isset($_POST['nagad_enabled']) ? '1' : '0');
        updateSetting('nagad_number', sanitize($_POST['nagad_number']));
        updateSetting('bkash_api_enabled', isset($_POST['bkash_api_enabled']) ? '1' : '0');
        updateSetting('bkash_api_username', sanitize($_POST['bkash_api_username'] ?? ''));
        updateSetting('bkash_api_password', sanitize($_POST['bkash_api_password'] ?? ''));
        updateSetting('bkash_api_key', sanitize($_POST['bkash_api_key'] ?? ''));
        updateSetting('bkash_api_secret', sanitize($_POST['bkash_api_secret'] ?? ''));
        $success = 'Payment settings updated!';
    }
    
    if ($action === 'theme') {
        updateSetting('primary_color', sanitize($_POST['primary_color']));
        updateSetting('secondary_color', sanitize($_POST['secondary_color']));
        $success = 'Theme settings updated!';
    }
    
    if ($action === 'password') {
        $currentPass = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';
        
        $admin = $pdo->prepare("SELECT password FROM admins WHERE id = ?");
        $admin->execute([$_SESSION['admin_id']]);
        $adminData = $admin->fetch();
        
        if (!password_verify($currentPass, $adminData['password'])) {
            $error = 'Current password is incorrect!';
        } elseif ($newPass !== $confirmPass) {
            $error = 'New passwords do not match!';
        } elseif (strlen($newPass) < 6) {
            $error = 'Password must be at least 6 characters!';
        } else {
            $hashed = password_hash($newPass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $_SESSION['admin_id']]);
            $success = 'Password changed successfully!';
        }
    }
}

$settings = getAllSettings();
require_once 'includes/admin-header.php';
?>

<?php if ($success): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
<?php endif; ?>

<div class="settings-tabs">
    <button class="tab-btn active" onclick="showTab('general')"><i class="fas fa-store"></i> General</button>
    <button class="tab-btn" onclick="showTab('delivery')"><i class="fas fa-truck"></i> Delivery</button>
    <button class="tab-btn" onclick="showTab('payment')"><i class="fas fa-credit-card"></i> Payment</button>
    <button class="tab-btn" onclick="showTab('theme')"><i class="fas fa-palette"></i> Theme</button>
    <button class="tab-btn" onclick="showTab('password')"><i class="fas fa-key"></i> Password</button>
</div>

<!-- General Settings -->
<div class="tab-content active" id="tab-general">
    <div class="admin-card">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="general">
            <div class="form-row">
                <div class="form-group">
                    <label>Site Name</label>
                    <input type="text" name="site_name" value="<?= $settings['site_name'] ?? '' ?>" required>
                </div>
                <div class="form-group">
                    <label>Tagline</label>
                    <input type="text" name="site_tagline" value="<?= $settings['site_tagline'] ?? '' ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="site_email" value="<?= $settings['site_email'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="site_phone" value="<?= $settings['site_phone'] ?? '' ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Address</label>
                <textarea name="site_address" rows="2"><?= $settings['site_address'] ?? '' ?></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Facebook URL</label>
                    <input type="url" name="facebook_url" value="<?= $settings['facebook_url'] ?? '' ?>" placeholder="https://facebook.com/...">
                </div>
                <div class="form-group">
                    <label>Instagram URL</label>
                    <input type="url" name="instagram_url" value="<?= $settings['instagram_url'] ?? '' ?>" placeholder="https://instagram.com/...">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>WhatsApp Number (with country code)</label>
                    <input type="text" name="whatsapp_number" value="<?= $settings['whatsapp_number'] ?? '' ?>" placeholder="8801XXXXXXXXX">
                </div>
                <div class="form-group">
                    <label>Logo (Upload)</label>
                    <input type="file" name="site_logo" accept="image/*">
                    <?php if (!empty($settings['site_logo'])): ?>
                    <small>Current: <?= $settings['site_logo'] ?></small>
                    <?php endif; ?>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save General Settings</button>
        </form>
    </div>
</div>

<!-- Delivery Settings -->
<div class="tab-content" id="tab-delivery">
    <div class="admin-card">
        <form method="POST">
            <input type="hidden" name="action" value="delivery">
            <div class="form-row">
                <div class="form-group">
                    <label>Delivery Charge - Dhaka (৳)</label>
                    <input type="number" name="delivery_dhaka" value="<?= $settings['delivery_dhaka'] ?? '80' ?>" required>
                </div>
                <div class="form-group">
                    <label>Delivery Charge - Outside Dhaka (৳)</label>
                    <input type="number" name="delivery_outside" value="<?= $settings['delivery_outside'] ?? '150' ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label>Free Delivery Minimum Order (৳) - 0 means disabled</label>
                <input type="number" name="free_delivery_min" value="<?= $settings['free_delivery_min'] ?? '0' ?>">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Delivery Settings</button>
        </form>
    </div>
</div>

<!-- Payment Settings -->
<div class="tab-content" id="tab-payment">
    <div class="admin-card">
        <form method="POST">
            <input type="hidden" name="action" value="payment">
            
            <h3 style="margin-bottom: 15px;"><i class="fas fa-hand-holding-usd"></i> Cash on Delivery</h3>
            <div class="form-group">
                <label class="switch-label">
                    <input type="checkbox" name="cod_enabled" <?= ($settings['cod_enabled'] ?? '0') == '1' ? 'checked' : '' ?>>
                    <span class="switch-slider"></span>
                    Enable Cash on Delivery (COD)
                </label>
            </div>
            
            <hr style="margin: 25px 0;">
            
            <h3 style="margin-bottom: 15px;"><span style="color: #e2136e; font-weight: bold;">bKash</span> - Manual Payment</h3>
            <div class="form-group">
                <label class="switch-label">
                    <input type="checkbox" name="bkash_enabled" <?= ($settings['bkash_enabled'] ?? '0') == '1' ? 'checked' : '' ?>>
                    <span class="switch-slider"></span>
                    Enable bKash Manual Payment
                </label>
            </div>
            <div class="form-group">
                <label>bKash Number (Personal/Merchant)</label>
                <input type="text" name="bkash_number" value="<?= $settings['bkash_number'] ?? '' ?>" placeholder="01XXXXXXXXX">
            </div>
            
            <hr style="margin: 25px 0;">
            
            <h3 style="margin-bottom: 15px;"><span style="color: #f26522; font-weight: bold;">Nagad</span> - Manual Payment</h3>
            <div class="form-group">
                <label class="switch-label">
                    <input type="checkbox" name="nagad_enabled" <?= ($settings['nagad_enabled'] ?? '0') == '1' ? 'checked' : '' ?>>
                    <span class="switch-slider"></span>
                    Enable Nagad Manual Payment
                </label>
            </div>
            <div class="form-group">
                <label>Nagad Number</label>
                <input type="text" name="nagad_number" value="<?= $settings['nagad_number'] ?? '' ?>" placeholder="01XXXXXXXXX">
            </div>
            
            <hr style="margin: 25px 0;">
            
            <h3 style="margin-bottom: 15px;"><i class="fas fa-code"></i> bKash API (Automatic Payment)</h3>
            <div class="form-group">
                <label class="switch-label">
                    <input type="checkbox" name="bkash_api_enabled" <?= ($settings['bkash_api_enabled'] ?? '0') == '1' ? 'checked' : '' ?>>
                    <span class="switch-slider"></span>
                    Enable bKash Payment Gateway API
                </label>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>API Username</label>
                    <input type="text" name="bkash_api_username" value="<?= $settings['bkash_api_username'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>API Password</label>
                    <input type="password" name="bkash_api_password" value="<?= $settings['bkash_api_password'] ?? '' ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>API Key</label>
                    <input type="text" name="bkash_api_key" value="<?= $settings['bkash_api_key'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>API Secret</label>
                    <input type="password" name="bkash_api_secret" value="<?= $settings['bkash_api_secret'] ?? '' ?>">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Payment Settings</button>
        </form>
    </div>
</div>

<!-- Theme Settings -->
<div class="tab-content" id="tab-theme">
    <div class="admin-card">
        <form method="POST">
            <input type="hidden" name="action" value="theme">
            <div class="form-row">
                <div class="form-group">
                    <label>Primary Color</label>
                    <input type="color" name="primary_color" value="<?= $settings['primary_color'] ?? '#000000' ?>" style="height: 50px; width: 100px;">
                    <small>Main brand color (header, buttons)</small>
                </div>
                <div class="form-group">
                    <label>Secondary/Accent Color</label>
                    <input type="color" name="secondary_color" value="<?= $settings['secondary_color'] ?? '#ff6b35' ?>" style="height: 50px; width: 100px;">
                    <small>Accent color (prices, highlights)</small>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Theme Settings</button>
        </form>
    </div>
</div>

<!-- Change Password -->
<div class="tab-content" id="tab-password">
    <div class="admin-card">
        <form method="POST">
            <input type="hidden" name="action" value="password">
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required minlength="6">
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Change Password</button>
        </form>
    </div>
</div>

<script>
function showTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tabName).classList.add('active');
    event.target.classList.add('active');
}
</script>

<?php require_once 'includes/admin-footer.php'; ?>
