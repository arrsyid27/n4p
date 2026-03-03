<?php
include '../config/database.php';

$products = $conn->query("SELECT * FROM products");
?>

<link rel="stylesheet" href="../assets/css/style.css">

<h2>Stock Adjustment</h2>

<form method="POST" action="../process/stock_process.php">
    <select name="product_id">
        <?php while($p = $products->fetch_assoc()){ ?>
        <option value="<?=$p['id']?>"><?=$p['name']?></option>
        <?php } ?>
    </select>

    <select name="type">
        <option value="add">Add</option>
        <option value="reduce">Reduce</option>
    </select>

    <input type="number" name="qty" placeholder="Quantity" required>
    <input name="note" placeholder="Note">
    <button name="adjust">Save</button>
</form>