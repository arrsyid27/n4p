<?php
include '../config/database.php';

$products = $conn->query("SELECT * FROM products");
?>

<link rel="stylesheet" href="../assets/css/style.css">
<script src="../assets/js/script.js"></script>

<h2>Sales Transaction</h2>

<form method="POST" action="../process/sales_process.php">
    <input type="text" name="invoice" value="INV<?=time()?>" readonly>

    <select name="product_id">
        <?php while($p = $products->fetch_assoc()){ ?>
        <option value="<?=$p['id']?>">
            <?=$p['name']?> - Rp <?=$p['price']?>
        </option>
        <?php } ?>
    </select>

    <input type="number" name="qty" placeholder="Qty" required>
    <input type="number" name="payment" placeholder="Payment" required>

    <button name="save">Process Payment</button>
    <button name="pending">Save as Pending</button>
</form>