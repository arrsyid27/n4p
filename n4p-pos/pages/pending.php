<?php
include '../config/database.php';

$pending = $conn->query("SELECT * FROM sales WHERE status='pending'");
?>

<link rel="stylesheet" href="../assets/css/style.css">

<h2>Pending Transactions</h2>

<table>
<tr>
<th>Invoice</th>
<th>Total</th>
<th>Action</th>
</tr>

<?php while($row = $pending->fetch_assoc()){ ?>
<tr>
<td><?=$row['invoice']?></td>
<td><?=$row['total']?></td>
<td>
    <a href="../process/sales_process.php?pay=<?=$row['id']?>">Mark as Paid</a>
</td>
</tr>
<?php } ?>
</table>