<?php
/**
 * N4P (Not4Posers) POS System
 * Dashboard Page
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

requireLogin();

$user = getCurrentUser($conn);

// pull all active products for dashboard grid
$products = [];
$prod_query = "SELECT p.*, c.name AS category_name FROM products p 
               LEFT JOIN categories c ON p.category_id=c.id 
               WHERE p.status='active'";
$prod_res = $conn->query($prod_query);
if ($prod_res) {
    $products = $prod_res->fetch_all(MYSQLI_ASSOC);
} else {
    $products = [];
}  

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | N4P POS System</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="navbar">
            <div class="navbar-brand">
                <a href="<?php echo APP_URL; ?>/" style="text-decoration: none; color: inherit;">N4P</a>
            </div>
            
            <div class="navbar-nav">
                <a href="<?php echo APP_URL; ?>/pages/dashboard.php" class="active">Dashboard</a>
                <a href="<?php echo APP_URL; ?>/pages/pos.php">POS</a>
                <a href="<?php echo APP_URL; ?>/pages/products.php">Products</a>
                <a href="<?php echo APP_URL; ?>/pages/transactions.php">Transactions</a>
                <?php if (isAdmin()): ?>
                    <a href="<?php echo APP_URL; ?>/pages/inventory.php">Inventory</a>
                    <a href="<?php echo APP_URL; ?>/pages/users.php">Users</a>
                <?php endif; ?>
            </div>
            
            <div class="navbar-user">
                <div class="user-menu">
                    <button class="user-menu-toggle" onclick="toggleUserMenu()">👤</button>
                    <div class="user-dropdown" id="userDropdown">
                        <div style="padding: 0.75rem 1rem; color: #6b7280; font-size: 0.875rem;">
                            <div><?php echo htmlspecialchars($user['full_name']); ?></div>
                            <div><?php echo $user['user_role'] === 'admin' ? 'Administrator' : 'Cashier'; ?></div>
                        </div>
                        <a href="<?php echo APP_URL; ?>/pages/profile.php">My Profile</a>
                        <a href="<?php echo APP_URL; ?>/pages/settings.php">Settings</a>
                        <button onclick="logout()" style="color: #ef4444;">Logout</button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- no sidebar on this page -->

    <!-- Main Content -->
    <main class="main-container no-sidebar">
        <div class="container">
            <h1 class="page-title">Dashboard</h1>

            <!-- Products grid (SNS-B style) -->
            <div class="products-grid">
                <?php if (empty($products)): ?>
                    <p style="text-align: center; color: var(--text-gray);">Tidak ada produk tersedia.</p>
                <?php else: ?>
                    <?php foreach ($products as $prod): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <?php if (!empty($prod['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($prod['image_url']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>">
                                <?php else: ?>
                                    <div class="placeholder">No image</div>
                                <?php endif; ?>
                                <?php if ($prod['stock'] <= 0): ?>
                                    <span class="badge sold-out">Sold out</span>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <div class="product-name"><?php echo htmlspecialchars($prod['name']); ?></div>
                                <div class="product-price"><?php echo formatCurrency($prod['selling_price']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
    </main>

    <script>
        function toggleUserMenu() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('active');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.querySelector('.user-menu');
            const dropdown = document.getElementById('userDropdown');
            
            if (!userMenu.contains(event.target)) {
                dropdown.classList.remove('active');
            }
        });

        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '<?php echo APP_URL; ?>/api/logout.php';
            }
        }
    </script>
</body>
</html>
