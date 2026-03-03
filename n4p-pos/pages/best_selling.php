<?php
include '../config/database.php';

$query = $conn->query("
SELECT products.name, SUM(sales_details.qty) as total_sold
FROM sales_details
JOIN products ON products.id = sales_details.product_id
GROUP BY product_id
ORDER BY total_sold DESC
LIMIT 5
");
?>

<link rel="stylesheet" href="../assets/css/style.css">

<h2>Best Selling Products</h2>

<table>
<tr>
<th>Product</th>
<th>Total Sold</th>
</tr>

<?php while($row = $query->fetch_assoc()){ ?>
<tr>
<td><?=$row['name']?></td>
<td><?=$row['total_sold']?></td>
</tr>
<?php } ?>
</table>