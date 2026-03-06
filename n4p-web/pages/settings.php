<?php
/**
 * N4P (Not4Posers) POS System
 * Settings Page
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

requireLogin();

$user = getCurrentUser($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | N4P POS System</title>
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
                <a href="<?php echo APP_URL; ?>/pages/dashboard.php">Dashboard</a>
                <a href="<?php echo APP_URL; ?>/pages/pos.php">POS</a>
                <a href="<?php echo APP_URL; ?>/pages/products.php">Products</a>
                <a href="<?php echo APP_URL; ?>/pages/transactions.php">Transactions</a>
            </div>
            
            <div class="navbar-user">
                <div class="user-menu">
                    <button class="user-menu-toggle" onclick="toggleUserMenu()">👤</button>
                    <div class="user-dropdown" id="userDropdown">
                        <a href="<?php echo APP_URL; ?>/pages/profile.php">My Profile</a>
                        <a href="<?php echo APP_URL; ?>/pages/settings.php" class="active">Settings</a>
                        <button onclick="logout()" style="color: #ef4444;">Logout</button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <aside class="sidebar">
        <ul class="sidebar-nav">
            <li><a href="<?php echo APP_URL; ?>/pages/dashboard.php">📊 Dashboard</a></li>
            <li><a href="<?php echo APP_URL; ?>/pages/pos.php">🛒 POS / Checkout</a></li>
            <li><a href="<?php echo APP_URL; ?>/pages/products.php">📦 Products</a></li>
            <li><a href="<?php echo APP_URL; ?>/pages/transactions.php">📋 Transactions</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-container">
        <div class="container" style="max-width: 600px;">
            <h1 class="page-title">Settings</h1>

            <!-- Application Settings -->
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h2 class="card-title">Application Settings</h2>
                </div>

                <div class="card-body">
                    <div style="padding: 1rem 0; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong>Application Name</strong>
                            <p style="color: #6b7280; font-size: 0.875rem; margin: 0.25rem 0 0;">N4P - Not4Posers POS System</p>
                        </div>
                    </div>

                    <div style="padding: 1rem 0; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong>Version</strong>
                            <p style="color: #6b7280; font-size: 0.875rem; margin: 0.25rem 0 0;">1.0.0</p>
                        </div>
                    </div>

                    <div style="padding: 1rem 0; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong>Tax Percentage</strong>
                            <p style="color: #6b7280; font-size: 0.875rem; margin: 0.25rem 0 0;">10%</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Information -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">System Information</h2>
                </div>

                <div class="card-body">
                    <div style="padding: 1rem 0; border-bottom: 1px solid #e5e7eb;">
                        <strong>Database</strong>
                        <p style="color: #6b7280; font-size: 0.875rem; margin: 0.5rem 0 0;">MySQL</p>
                    </div>

                    <div style="padding: 1rem 0; border-bottom: 1px solid #e5e7eb;">
                        <strong>Database Name</strong>
                        <p style="color: #6b7280; font-size: 0.875rem; margin: 0.5rem 0 0;">n4p_pos</p>
                    </div>

                    <div style="padding: 1rem 0; border-bottom: 1px solid #e5e7eb;">
                        <strong>PHP Version</strong>
                        <p style="color: #6b7280; font-size: 0.875rem; margin: 0.5rem 0 0;"><?php echo phpversion(); ?></p>
                    </div>

                    <div style="padding: 1rem 0;">
                        <strong>Server</strong>
                        <p style="color: #6b7280; font-size: 0.875rem; margin: 0.5rem 0 0;"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Apache'; ?></p>
                    </div>
                </div>
            </div>

            <!-- Help & Support -->
            <div class="card" style="margin-top: 2rem;">
                <div class="card-header">
                    <h2 class="card-title">Help & Support</h2>
                </div>

                <div class="card-body">
                    <div style="line-height: 1.8;">
                        <p><strong>📱 Getting Started</strong></p>
                        <p style="color: #6b7280;">
                            1. Login with your account<br>
                            2. Configure your products<br>
                            3. Start selling with POS<br>
                            4. Monitor your sales and inventory
                        </p>

                        <p style="margin-top: 1rem;"><strong>🛒 POS Features</strong></p>
                        <p style="color: #6b7280;">
                            • Fast product search<br>
                            • Multiple payment methods<br>
                            • Discount & tax calculation<br>
                            • Receipt printing
                        </p>

                        <p style="margin-top: 1rem;"><strong>📊 Admin Features</strong></p>
                        <p style="color: #6b7280;">
                            • Inventory management<br>
                            • Stock adjustments<br>
                            • Sales reports<br>
                            • User management
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function toggleUserMenu() {
            document.getElementById('userDropdown').classList.toggle('active');
        }

        function logout() {
            if (confirm('Logout?')) {
                window.location.href = '<?php echo APP_URL; ?>/api/logout.php';
            }
        }
    </script>
</body>
</html>
