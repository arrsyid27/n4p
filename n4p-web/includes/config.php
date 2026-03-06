<?php
/**
 * N4P (Not4Posers) POS System
 * Database Configuration File
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'n4p_pos');

// Application Settings
define('APP_NAME', 'N4P - Not4Posers');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/n4p-web');

// Session timeout (in minutes)
define('SESSION_TIMEOUT', 30);

// Tax percentage
define('TAX_PERCENTAGE', 10);

// Create Database Connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to UTF-8
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}

// Timezone Setting
date_default_timezone_set('Asia/Jakarta');
?>
