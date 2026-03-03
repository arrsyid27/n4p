<?php
include '../config/database.php';

if(isset($_POST['save']) || isset($_POST['pending'])){

    $invoice = $_POST['invoice'];
    $product_id = $_POST['product_id'];
    $qty = $_POST['qty'];
    $payment = $_POST['payment'];

    $product = $conn->query("SELECT * FROM products WHERE id=$product_id")->fetch_assoc();

    $total = $product['price'] * $qty;
    $change = $payment - $total;

    $status = isset($_POST['pending']) ? 'pending' : 'paid';

    $conn->query("INSERT INTO sales (invoice,total,payment,change_money,status)
                  VALUES ('$invoice','$total','$payment','$change','$status')");

    $sale_id = $conn->insert_id;

    $conn->query("INSERT INTO sales_details (sale_id,product_id,qty,price,subtotal)
                  VALUES ('$sale_id','$product_id','$qty','".$product['price']."','$total')");

    if($status == 'paid'){
        $conn->query("UPDATE products SET stock = stock - $qty WHERE id=$product_id");
    }

    header("Location: ../pages/sales.php");
}

if(isset($_GET['pay'])){
    $id = $_GET['pay'];
    $conn->query("UPDATE sales SET status='paid' WHERE id=$id");
    header("Location: ../pages/pending.php");
}
?>