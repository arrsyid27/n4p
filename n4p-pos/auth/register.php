<?php
include '../config/database.php';

if(isset($_POST['register'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = md5($_POST['password']);

    $check = $conn->query("SELECT * FROM users WHERE email='$email'");
    if($check->num_rows > 0){
        echo "Email already registered!";
    } else {
        $conn->query("INSERT INTO users (name,email,password) 
                      VALUES ('$name','$email','$password')");
        header("Location: login.php");
    }
}
?>

<link rel="stylesheet" href="../assets/css/style.css">

<div class="auth-container">
    <h1>Register N4P</h1>
    <form method="POST">
        <input name="name" placeholder="Full Name" required>
        <input name="email" type="email" placeholder="Email" required>
        <input name="password" type="password" placeholder="Password" required>
        <button name="register">Register</button>
        <a href="login.php">Back to Login</a>
    </form>
</div>