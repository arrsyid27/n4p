<?php
/**
 * N4P (Not4Posers) POS System
 * Transactions Management Page
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

requireLogin();

$user = getCurrentUser($conn);
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

// Build query
$query = "SELECT t.*, u.full_name FROM transactions t 
          LEFT JOIN users u ON t.user_id = u.id 
          WHERE 1=1";

if ($status) {
    $query .= " AND t.payment_status = '" . $status . "'";
}

$query .= " ORDER BY t.created_at DESC LIMIT 100";

$result = $conn->query($query);
$transactions = $result->fetch_all(MYSQLI_ASSOC);

// Handle payment update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $transaction_id = (int)$_POST['transaction_id'];
    $new_status = sanitize($_POST['status']);
    
    if (in_array($new_status, ['pending', 'completed', 'cancelled'])) {
        $update_query = "UPDATE transactions SET payment_status = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $new_status, $transaction_id);
        
        if ($stmt->execute()) {
            setSuccessMessage('Transaction status updated!');
        } else {
            setErrorMessage('Error updating transaction!');
        }
        header('Location: ' . APP_URL . '/pages/transactions.php' . ($status ? '?status=' . $status : ''));
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions | N4P POS System</title>
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
                <a href="<?php echo APP_URL; ?>/pages/transactions.php" class="active">Transactions</a>
                <?php if (isAdmin()): ?>
                    <a href="<?php echo APP_URL; ?>/pages/inventory.php">Inventory</a>
                    <a href="<?php echo APP_URL; ?>/pages/users.php">Users</a>
                <?php endif; ?>
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

    <!-- no sidebar -->

    <!-- Main Content -->
    <main class="main-container no-sidebar">
        <div class="container">
            <?php 
            $success = getSuccessMessage();
            if ($success) echo '<div class="alert alert-success">' . htmlspecialchars($success) . '</div>';
            
            $error = getErrorMessage();
            if ($error) echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
            ?>

            <h1 class="page-title">Transactions</h1>

            <!-- Filter Buttons -->
            <div style="margin-bottom: 1.5rem; display: flex; gap: 1rem;">
                <a href="<?php echo APP_URL; ?>/pages/transactions.php" class="btn <?php echo !$status ? 'btn-primary' : 'btn-secondary'; ?>">
                    All
                </a>
                <a href="<?php echo APP_URL; ?>/pages/transactions.php?status=pending" class="btn <?php echo $status === 'pending' ? 'btn-warning' : 'btn-secondary'; ?>">
                    Pending
                </a>
                <a href="<?php echo APP_URL; ?>/pages/transactions.php?status=completed" class="btn <?php echo $status === 'completed' ? 'btn-success' : 'btn-secondary'; ?>">
                    Completed
                </a>
                <a href="<?php echo APP_URL; ?>/pages/transactions.php?status=cancelled" class="btn <?php echo $status === 'cancelled' ? 'btn-danger' : 'btn-secondary'; ?>">
                    Cancelled
                </a>
            </div>

            <!-- Transactions Table -->
            <div class="card">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Transaction #</th>
                                <th>Customer</th>
                                <th>Cashier</th>
                                <th>Subtotal</th>
                                <th>Discount</th>
                                <th>Tax</th>
                                <th>Total</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $t): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo APP_URL; ?>/pages/transaction_detail.php?id=<?php echo $t['id']; ?>" style="color: #6b21a8; text-decoration: none;">
                                            <?php echo htmlspecialchars($t['transaction_number']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($t['customer_name'] ?? 'Walk-in'); ?></td>
                                    <td><?php echo htmlspecialchars($t['full_name'] ?? 'Unknown'); ?></td>
                                    <td><?php echo formatCurrency($t['subtotal']); ?></td>
                                    <td><?php echo formatCurrency($t['discount']); ?></td>
                                    <td><?php echo formatCurrency($t['tax']); ?></td>
                                    <td><strong><?php echo formatCurrency($t['total']); ?></strong></td>
                                    <td><?php echo ucfirst($t['payment_method']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $t['payment_status'] === 'completed' ? 'success' : 
                                                 ($t['payment_status'] === 'pending' ? 'warning' : 'secondary'); 
                                        ?>">
                                            <?php echo ucfirst($t['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d M Y H:i', strtotime($t['created_at'])); ?></td>
                                    <td>
                                        <a href="<?php echo APP_URL; ?>/pages/transaction_detail.php?id=<?php echo $t['id']; ?>" class="btn btn-primary btn-small">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($transactions)): ?>
                                <tr>
                                    <td colspan="11" style="text-align: center; color: #6b7280; padding: 2rem;">
                                        No transactions found
                                    </td>
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
