<?php
$conn = new mysqli("localhost", "root", "", "n4p_pos");

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

session_start();
?>