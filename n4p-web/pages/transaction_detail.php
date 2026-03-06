<?php
/**
 * N4P (Not4Posers) POS System
 * Transaction Detail Page
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

requireLogin();

$user = getCurrentUser($conn);
$transaction_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$transaction_id) {
    header('Location: ' . APP_URL . '/pages/transactions.php');
    exit;
}

$transaction = getTransactionById($conn, $transaction_id);
if (!$transaction) {
    setErrorMessage('Transaction not found!');
    header('Location: ' . APP_URL . '/pages/transactions.php');
    exit;
}

$items = getTransactionItems($conn, $transaction_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Detail | N4P POS System</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
    <style>
        .receipt {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #1f2937;
            padding-bottom: 1rem;
        }

        .receipt-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .receipt-subtitle {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .receipt-info {
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            line-height: 1.8;
        }

        .receipt-info div {
            display: flex;
            justify-content: space-between;
            padding: 0.25rem 0;
        }

        .receipt-items {
            margin-bottom: 1.5rem;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
            padding: 1rem 0;
        }

        .receipt-item {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 0.5rem;
            font-size: 0.875rem;
            margin-bottom: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px dashed #e5e7eb;
        }

        .receipt-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .item-name {
            grid-column: 1 / -1;
            font-weight: 600;
        }

        .item-qty {
            text-align: right;
        }

        .item-subtotal {
            text-align: right;
        }

        .receipt-total {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 1rem;
            gap: 1rem;
            padding: 0.75rem 0;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.875rem;
            padding: 0.25rem 0;
        }

        .total-amount {
            font-weight: 700;
            font-size: 1.125rem;
            border-top: 2px solid #1f2937;
            padding-top: 0.75rem;
            margin-top: 0.75rem;
            display: flex;
            justify-content: space-between;
        }

        .receipt-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.75rem;
            color: #6b7280;
            line-height: 1.6;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            justify-content: center;
        }

        @media print {
            body {
                background: white;
            }
            .button-group {
                display: none;
            }
        }
    </style>
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
            <li><a href="<?php echo APP_URL; ?>/pages/transactions.php" class="active">📋 Transactions</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-container">
        <div class="receipt">
            <!-- Receipt Header -->
            <div class="receipt-header">
                <div class="receipt-title">N4P POS</div>
                <div class="receipt-subtitle">Receipt / Invoice</div>
            </div>

            <!-- Transaction Info -->
            <div class="receipt-info">
                <div>
                    <span>Transaction #:</span>
                    <strong><?php echo htmlspecialchars($transaction['transaction_number']); ?></strong>
                </div>
                <div>
                    <span>Date:</span>
                    <span><?php echo date('d M Y H:i:s', strtotime($transaction['created_at'])); ?></span>
                </div>
                <div>
                    <span>Customer:</span>
                    <span><?php echo htmlspecialchars($transaction['customer_name'] ?? 'Walk-in'); ?></span>
                </div>
                <div>
                    <span>Payment Method:</span>
                    <span><?php echo ucfirst($transaction['payment_method']); ?></span>
                </div>
                <div>
                    <span>Status:</span>
                    <span class="badge badge-<?php echo $transaction['payment_status'] === 'completed' ? 'success' : ($transaction['payment_status'] === 'pending' ? 'warning' : 'secondary'); ?>">
                        <?php echo ucfirst($transaction['payment_status']); ?>
                    </span>
                </div>
            </div>

            <!-- Items -->
            <div class="receipt-items">
                <?php foreach ($items as $item): ?>
                    <div class="receipt-item">
                        <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                        <div style="display: flex; justify-content: space-between; grid-column: 1 / -1; gap: 1rem;">
                            <div>
                                <span class="item-qty"><?php echo $item['quantity']; ?> x IDR <?php echo number_format($item['unit_price'], 2, ',', '.'); ?></span>
                            </div>
                            <div class="item-subtotal">
                                IDR <?php echo number_format($item['subtotal'], 2, ',', '.'); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Total Summary -->
            <div class="receipt-total">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>IDR <?php echo number_format($transaction['subtotal'], 2, ',', '.'); ?></span>
                </div>
                <?php if ($transaction['discount'] > 0): ?>
                    <div class="total-row">
                        <span>Discount (<?php echo $transaction['discount_percentage']; ?>%):</span>
                        <span>-IDR <?php echo number_format($transaction['discount'], 2, ',', '.'); ?></span>
                    </div>
                <?php endif; ?>
                <div class="total-row">
                    <span>Tax (10%):</span>
                    <span>IDR <?php echo number_format($transaction['tax'], 2, ',', '.'); ?></span>
                </div>
                <div class="total-amount">
                    <span>TOTAL</span>
                    <span>IDR <?php echo number_format($transaction['total'], 2, ',', '.'); ?></span>
                </div>
            </div>

            <!-- Footer -->
            <div class="receipt-footer">
                <p>
                    Thank you for your purchase!<br>
                    Please come again<br>
                    <br>
                    This is a proof of transaction
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="button-group">
                <button onclick="window.print()" class="btn btn-primary">🖨️ Print</button>
                <a href="<?php echo APP_URL; ?>/pages/transactions.php" class="btn btn-secondary">Back</a>
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
