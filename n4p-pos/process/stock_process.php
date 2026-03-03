<?php
include '../config/database.php';

if(isset($_POST['adjust'])){

    $product_id = $_POST['product_id'];
    $type = $_POST['type'];
    $qty = $_POST['qty'];
    $note = $_POST['note'];

    if($type == 'add'){
        $conn->query("UPDATE products SET stock = stock + $qty WHERE id=$product_id");
    } else {
        $conn->query("UPDATE products SET stock = stock - $qty WHERE id=$product_id");
    }

    $conn->query("INSERT INTO stock_adjustments (product_id,type,qty,note)
                  VALUES ('$product_id','$type','$qty','$note')");

    header("Location: ../pages/stock_adjustment.php");
}
?>