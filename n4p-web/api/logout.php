<?php
/**
 * N4P (Not4Posers) POS System
 * Logout Handler
 */

session_start();
session_destroy();

header('Location: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../pages/login.php'));
exit;
?>
