<?php
include 'config/database.php';

if(isset($_SESSION['user'])){
    header("Location: pages/dashboard.php");
} else {
    header("Location: auth/login.php");
}
?>