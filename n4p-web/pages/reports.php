<?php
/**
 * N4P (Not4Posers) POS System
 * Sales Reports Page
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

requireAdmin();

$user = getCurrentUser($conn);

// Get date range from request
$start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : date('Y-m-d');

// Get sales summary for period
$summary_query = "SELECT 
    SUM(total) as total_revenue,
    SUM(total_discount) as total_discount,
    SUM(total_tax) as total_tax,
    SUM(total_transactions) as total_transactions,
    SUM(total_items_sold) as total_items_sold,
    COUNT(*) as total_days
FROM daily_sales_summary 
WHERE sale_date BETWEEN ? AND ?";

$stmt = $conn->prepare($summary_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();
$summary = $result->fetch_assoc();

// Get best sellers
$best_sellers_query = "SELECT bs.*, p.image_url FROM best_selling_summary bs
                       LEFT JOIN products p ON bs.product_id = p.id
                       ORDER BY bs.total_sold DESC LIMIT 10";
$bs_result = $conn->query($best_sellers_query);
$best_sellers = $bs_result->fetch_all(MYSQLI_ASSOC);

// Get daily sales
$daily_query = "SELECT * FROM daily_sales_summary 
                WHERE sale_date BETWEEN ? AND ?
                ORDER BY sale_date DESC";
$daily_stmt = $conn->prepare($daily_query);
$daily_stmt->bind_param("ss", $start_date, $end_date);
$daily_stmt->execute();
$daily_result = $daily_stmt->get_result();
$daily_sales = $daily_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Reports | N4P POS System</title>
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
                <a href="<?php echo APP_URL; ?>/pages/inventory.php">Inventory</a>
                <a href="<?php echo APP_URL; ?>/pages/reports.php" class="active">Reports</a>
            </div>
            
            <div class="navbar-user">
                <div class="user-menu">
                    <button class="user-menu-toggle" onclick="toggleUserMenu()">👤</button>
                    <div class="user-dropdown" id="userDropdown">
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
            <li><a href="<?php echo APP_URL; ?>/pages/inventory.php">📈 Inventory</a></li>
            <li><a href="<?php echo APP_URL; ?>/pages/reports.php" class="active">📊 Reports</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-container">
        <div class="container">
            <h1 class="page-title">Sales Reports</h1>

            <!-- Date Filter -->
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-body">
                    <form method="GET" action="" style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 1rem; align-items: flex-end;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="start_date">Start Date</label>
                            <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="end_date">End Date</label>
                            <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                        </div>

                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="<?php echo APP_URL; ?>/pages/reports.php" class="btn btn-secondary">Reset</a>
                    </form>
                </div>
            </div>

            <!-- Summary Statistics -->
            <div class="dashboard-grid cols-4">
                <div class="stat-card success">
                    <div class="stat-label">Total Revenue</div>
                    <div class="stat-value"><?php echo formatCurrency($summary['total_revenue'] ?? 0); ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Total Transactions</div>
                    <div class="stat-value"><?php echo $summary['total_transactions'] ?? 0; ?></div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-label">Total Items Sold</div>
                    <div class="stat-value"><?php echo $summary['total_items_sold'] ?? 0; ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Total Discount</div>
                    <div class="stat-value"><?php echo formatCurrency($summary['total_discount'] ?? 0); ?></div>
                </div>
            </div>

            <!-- Best Sellers -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-top: 2rem;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Best Selling Products</h2>
                    </div>

                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Total Sold</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($best_sellers as $product): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                        <td><?php echo number_format($product['total_sold']); ?> units</td>
                                        <td><?php echo formatCurrency($product['total_revenue']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($best_sellers)): ?>
                                    <tr>
                                        <td colspan="3" style="text-align: center; color: #6b7280;">No sales data</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Average Statistics</h2>
                    </div>

                    <div class="card-body">
                        <div style="padding: 1rem 0; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between;">
                            <span>Avg Daily Revenue</span>
                            <strong><?php echo formatCurrency($summary['total_days'] > 0 ? $summary['total_revenue'] / $summary['total_days'] : 0); ?></strong>
                        </div>

                        <div style="padding: 1rem 0; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between;">
                            <span>Avg Transaction Value</span>
                            <strong><?php echo formatCurrency($summary['total_transactions'] > 0 ? $summary['total_revenue'] / $summary['total_transactions'] : 0); ?></strong>
                        </div>

                        <div style="padding: 1rem 0; display: flex; justify-content: space-between;">
                            <span>Avg Items/Transaction</span>
                            <strong><?php echo $summary['total_transactions'] > 0 ? round($summary['total_items_sold'] / $summary['total_transactions'], 1) : 0; ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily Sales Detail -->
            <div class="card" style="margin-top: 2rem;">
                <div class="card-header">
                    <h2 class="card-title">Daily Sales Details</h2>
                </div>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Transactions</th>
                                <th>Items Sold</th>
                                <th>Revenue</th>
                                <th>Discount</th>
                                <th>Tax</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($daily_sales as $day): ?>
                                <tr>
                                    <td><?php echo date('d M Y', strtotime($day['sale_date'])); ?></td>
                                    <td><?php echo $day['total_transactions']; ?></td>
                                    <td><?php echo $day['total_items_sold']; ?></td>
                                    <td><strong><?php echo formatCurrency($day['total_revenue']); ?></strong></td>
                                    <td><?php echo formatCurrency($day['total_discount']); ?></td>
                                    <td><?php echo formatCurrency($day['total_tax']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($daily_sales)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: #6b7280;">No sales data for selected period</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer">
                    <button onclick="window.print()" class="btn btn-primary">🖨️ Print Report</button>
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
