<?php
include '../config/database.php';
include '../auth/auth_check.php';

$totalSales = $conn->query("SELECT SUM(total) as total FROM sales WHERE status='paid'")->fetch_assoc()['total'];
$totalProducts = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];
$totalPending = $conn->query("SELECT COUNT(*) as total FROM sales WHERE status='pending'")->fetch_assoc()['total'];
?>

<link rel="stylesheet" href="../assets/css/style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="sidebar">
    <h2>N4P</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="products.php">Products</a>
    <a href="sales.php">Sales</a>
    <a href="pending.php">Pending</a>
    <a href="stock_adjustment.php">Stock</a>
    <a href="best_selling.php">Best Selling</a>
    <a href="../auth/logout.php">Logout</a>
</div>

<div class="main">
    <h1>Dashboard Not4Posers</h1>

    <div class="cards">
        <div class="card">Total Sales<br>Rp <?=number_format($totalSales)?></div>
        <div class="card">Products<br><?=$totalProducts?></div>
        <div class="card">Pending<br><?=$totalPending?></div>
    </div>

    <canvas id="salesChart"></canvas>
</div>

<script>
const ctx = document.getElementById('salesChart');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
        datasets: [{
            label: 'Sales Traffic',
            data: [12,19,3,5,2,3,9],
            borderColor: '#00ffcc'
        }]
    }
});
</script>