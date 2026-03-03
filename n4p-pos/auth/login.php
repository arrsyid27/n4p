<?php
include '../config/database.php';

if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = md5($_POST['password']);

    $query = $conn->query("SELECT * FROM users WHERE email='$email' AND password='$password'");
    
    if($query->num_rows > 0){
        $_SESSION['user'] = $query->fetch_assoc();
        header("Location: ../pages/dashboard.php");
    } else {
        echo "Login Failed!";
    }
}
?>

<link rel="stylesheet" href="../assets/css/style.css">

<div class="auth-container">
    <h1>N4P POS</h1>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button name="login">Login</button>
        <a href="register.php">Create Account</a>
    </form>
</div>