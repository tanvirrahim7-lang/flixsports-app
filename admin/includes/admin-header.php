<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin' ?> - STYRIN Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <h2>STYRIN</h2>
                <span>Admin Panel</span>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="products.php" class="<?= in_array(basename($_SERVER['PHP_SELF']), ['products.php', 'product-add.php', 'product-edit.php']) ? 'active' : '' ?>">
                    <i class="fas fa-box"></i> Products
                </a>
                <a href="orders.php" class="<?= in_array(basename($_SERVER['PHP_SELF']), ['orders.php', 'order-detail.php']) ? 'active' : '' ?>">
                    <i class="fas fa-shopping-bag"></i> Orders
                    <?php 
                    $pendingCount = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
                    if ($pendingCount > 0): ?>
                    <span class="nav-badge"><?= $pendingCount ?></span>
                    <?php endif; ?>
                </a>
                <a href="categories.php" class="<?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : '' ?>">
                    <i class="fas fa-tags"></i> Categories
                </a>
                <a href="settings.php" class="<?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <a href="<?= SITE_URL ?>" target="_blank">
                    <i class="fas fa-external-link-alt"></i> View Site
                </a>
                <a href="logout.php" class="logout-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-topbar">
                <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                <h1><?= $pageTitle ?? 'Dashboard' ?></h1>
                <div class="admin-user">
                    <span><i class="fas fa-user-circle"></i> <?= $_SESSION['admin_name'] ?? 'Admin' ?></span>
                </div>
            </div>
            <div class="admin-content">
