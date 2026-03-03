<?php
include '../config/database.php';

if(isset($_POST['add'])){
    $conn->query("INSERT INTO products (name,category,price,stock) 
                  VALUES ('$_POST[name]','$_POST[category]','$_POST[price]','$_POST[stock]')");
}

$products = $conn->query("SELECT * FROM products");
?>

<link rel="stylesheet" href="../assets/css/style.css">

<h2>Product Catalog</h2>

<form method="POST">
    <input name="name" placeholder="Product Name" required>
    <input name="category" placeholder="Category" required>
    <input name="price" type="number" placeholder="Price" required>
    <input name="stock" type="number" placeholder="Stock" required>
    <button name="add">Add Product</button>
</form>

<table>
<tr>
<th>Name</th>
<th>Category</th>
<th>Price</th>
<th>Stock</th>
</tr>

<?php while($row = $products->fetch_assoc()){ ?>
<tr>
<td><?=$row['name']?></td>
<td><?=$row['category']?></td>
<td><?=$row['price']?></td>
<td><?=$row['stock']?></td>
</tr>
<?php } ?>
</table>