<?php
include '../config/database.php';

if(isset($_GET['delete'])){
    $conn->query("DELETE FROM products WHERE id=".$_GET['delete']);
    header("Location: ../pages/products.php");
}
?>