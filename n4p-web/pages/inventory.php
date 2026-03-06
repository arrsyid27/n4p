<?php
/**
 * N4P (Not4Posers) POS System
 * Inventory Management Page
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

requireAdmin();

$user = getCurrentUser($conn);

// Get products with low stock
$low_stock_query = "SELECT p.*, c.name as category_name FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    WHERE p.stock <= p.min_stock AND p.status = 'active'
                    ORDER BY p.stock ASC";
$low_stock_result = $conn->query($low_stock_query);
$low_stock = $low_stock_result->fetch_all(MYSQLI_ASSOC);

// Get all products
$products = getAllProducts($conn);

// Handle stock adjustment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adjust_stock'])) {
    $product_id = (int)$_POST['product_id'];
    $adjustment_type = sanitize($_POST['adjustment_type']);
    $quantity = (int)$_POST['quantity'];
    $reason = sanitize($_POST['reason']);
    
    if (in_array($adjustment_type, ['in', 'out', 'correction']) && $quantity > 0) {
        $query = "INSERT INTO stock_adjustments (product_id, user_id, adjustment_type, quantity, reason) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iisss", $product_id, $user_id, $adjustment_type, $quantity, $reason);
        
        if ($stmt->execute()) {
            // Update product stock
            if ($adjustment_type === 'in') {
                $update_query = "UPDATE products SET stock = stock + ? WHERE id = ?";
            } elseif ($adjustment_type === 'out') {
                $update_query = "UPDATE products SET stock = stock - ? WHERE id = ?";
            } else {
                // For correction, set to exact value
                $update_query = "UPDATE products SET stock = ? WHERE id = ?";
            }
            
            $update_stmt = $conn->prepare($update_query);
            if ($adjustment_type === 'correction') {
                $update_stmt->bind_param("ii", $quantity, $product_id);
            } else {
                $update_stmt->bind_param("ii", $quantity, $product_id);
            }
            $update_stmt->execute();
            
            setSuccessMessage('Stock adjustment recorded!');
            header('Location: ' . APP_URL . '/pages/inventory.php');
            exit;
        }
    }
}

// Get recent adjustments
$adjustment_query = "SELECT sa.*, p.name as product_name, u.full_name FROM stock_adjustments sa
                     LEFT JOIN products p ON sa.product_id = p.id
                     LEFT JOIN users u ON sa.user_id = u.id
                     ORDER BY sa.created_at DESC LIMIT 20";
$adjustment_result = $conn->query($adjustment_query);
$adjustments = $adjustment_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management | N4P POS System</title>
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
                <a href="<?php echo APP_URL; ?>/pages/inventory.php" class="active">Inventory</a>
                <a href="<?php echo APP_URL; ?>/pages/users.php">Users</a>
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

    <!-- no sidebar here -->

    <!-- Main Content -->
    <main class="main-container no-sidebar">
        <div class="container">
            <?php 
            $success = getSuccessMessage();
            if ($success) echo '<div class="alert alert-success">' . htmlspecialchars($success) . '</div>';
            
            $error = getErrorMessage();
            if ($error) echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
            ?>

            <h1 class="page-title">Inventory Management</h1>

            <!-- Stock Adjustment Form -->
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h2 class="card-title">Stock Adjustment</h2>
                </div>
                
                <form method="POST" action="">
                    <div class="card-body">
                        <div class="form-row cols-4">
                            <div class="form-group">
                                <label for="product_id">Product *</label>
                                <select id="product_id" name="product_id" required>
                                    <option value="">Select Product</option>
                                    <?php foreach ($products as $p): ?>
                                        <option value="<?php echo $p['id']; ?>">
                                            <?php echo htmlspecialchars($p['name']); ?> (Stock: <?php echo $p['stock']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="adjustment_type">Type *</label>
                                <select id="adjustment_type" name="adjustment_type" required>
                                    <option value="in">Stock In</option>
                                    <option value="out">Stock Out</option>
                                    <option value="correction">Correction</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="quantity">Quantity *</label>
                                <input type="number" id="quantity" name="quantity" min="1" required>
                            </div>

                            <div class="form-group">
                                <label for="reason">Reason *</label>
                                <input type="text" id="reason" name="reason" placeholder="e.g., Restock, Damage, Return" required>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" name="adjust_stock" class="btn btn-success">
                            ➕ Adjust Stock
                        </button>
                    </div>
                </form>
            </div>

            <!-- Low Stock Alert -->
            <?php if (!empty($low_stock)): ?>
                <div class="card" style="margin-bottom: 2rem; border-left: 4px solid #ef4444;">
                    <div class="card-header" style="background-color: #fee2e2;">
                        <h2 class="card-title" style="color: #991b1b;">⚠️ Low Stock Items</h2>
                    </div>
                    
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Current Stock</th>
                                    <th>Min Stock</th>
                                    <th>Max Stock</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($low_stock as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                                        <td><strong><?php echo $item['stock']; ?></strong></td>
                                        <td><?php echo $item['min_stock']; ?></td>
                                        <td><?php echo $item['max_stock']; ?></td>
                                        <td>
                                            <span class="badge badge-danger">LOW STOCK</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Recent Adjustments -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Recent Adjustments</h2>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Type</th>
                                <th>Quantity</th>
                                <th>Reason</th>
                                <th>By</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($adjustments as $adj): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($adj['product_name']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $adj['adjustment_type'] === 'in' ? 'success' : 
                                                 ($adj['adjustment_type'] === 'out' ? 'danger' : 'info'); 
                                        ?>">
                                            <?php echo strtoupper($adj['adjustment_type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $adj['quantity']; ?></td>
                                    <td><?php echo htmlspecialchars($adj['reason']); ?></td>
                                    <td><?php echo htmlspecialchars($adj['full_name'] ?? 'Unknown'); ?></td>
                                    <td><?php echo date('d M Y H:i', strtotime($adj['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($adjustments)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: #6b7280;">No adjustments yet</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
