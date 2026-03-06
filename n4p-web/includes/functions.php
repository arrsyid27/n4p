<?php
/**
 * N4P (Not4Posers) POS System
 * Session and Authentication Helper Functions
 */

session_start();

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isLoggedIn() && $_SESSION['user_role'] === 'admin';
}

/**
 * Redirect to login if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/pages/login.php');
        exit;
    }
}

/**
 * Redirect to admin page if not admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . APP_URL . '/pages/dashboard.php');
        exit;
    }
}

/**
 * Logout user
 */
function logout() {
    session_destroy();
    header('Location: ' . APP_URL . '/pages/login.php');
    exit;
}

/**
 * Get current logged in user ID
 */
function getCurrentUserId() {
    return isLoggedIn() ? $_SESSION['user_id'] : null;
}

/**
 * Get current logged in user data
 */
function getCurrentUser($conn) {
    if (!isLoggedIn()) return null;
    
    $user_id = $_SESSION['user_id'];
    $query = "SELECT * FROM users WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Format currency to Indonesian Rupiah
 */
function formatCurrency($amount) {
    return 'IDR ' . number_format($amount, 2, ',', '.');
}

/**
 * Format currency without currency name
 */
function formatPrice($amount) {
    return number_format($amount, 2, ',', '.');
}

/**
 * Generate transaction number
 */
function generateTransactionNumber() {
    return 'TRX' . date('YmdHis') . substr(uniqid(), -4);
}

/**
 * Generate SKU
 */
function generateSKU() {
    return 'SKU' . date('Ymd') . substr(uniqid(), -6);
}

/**
 * Sanitize input
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Get success message
 */
function getSuccessMessage() {
    if (isset($_SESSION['success'])) {
        $message = $_SESSION['success'];
        unset($_SESSION['success']);
        return $message;
    }
    return null;
}

/**
 * Get error message
 */
function getErrorMessage() {
    if (isset($_SESSION['error'])) {
        $message = $_SESSION['error'];
        unset($_SESSION['error']);
        return $message;
    }
    return null;
}

/**
 * Set success message
 */
function setSuccessMessage($message) {
    $_SESSION['success'] = $message;
}

/**
 * Set error message
 */
function setErrorMessage($message) {
    $_SESSION['error'] = $message;
}

/**
 * Get all categories
 */
function getAllCategories($conn) {
    $query = "SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC";
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get all products with category
 */
function getAllProducts($conn) {
    $query = "SELECT p.*, c.name as category_name FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.status = 'active' ORDER BY p.name ASC";
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get all users
 */
function getAllUsers($conn) {
    $query = "SELECT * FROM users WHERE status = 'active' ORDER BY created_at DESC";
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Calculate total sales for date range
 */
function getTotalSales($conn, $start_date = null, $end_date = null) {
    $query = "SELECT SUM(total) as total_sales FROM transactions WHERE payment_status = 'completed'";
    
    if ($start_date && $end_date) {
        $query .= " AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
    }
    
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    
    return $row['total_sales'] ?? 0;
}

/**
 * Get best selling products
 */
function getBestSellingProducts($conn, $limit = 10) {
    $query = "SELECT bs.*, p.image_url FROM best_selling_summary bs
              LEFT JOIN products p ON bs.product_id = p.id
              ORDER BY bs.total_sold DESC LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Update best selling summary
 */
function updateBestSellingSummary($conn, $product_id, $quantity = 1, $price = 0) {
    $query = "INSERT INTO best_selling_summary (product_id, product_name, category_id, total_sold, total_revenue) 
              SELECT id, name, category_id, ?, ? FROM products WHERE id = ?
              ON DUPLICATE KEY UPDATE 
              total_sold = total_sold + VALUES(total_sold),
              total_revenue = total_revenue + VALUES(total_revenue),
              last_sold = NOW()";
    
    $stmt = $conn->prepare($query);
    $revenue = $quantity * $price;
    $stmt->bind_param("iii", $quantity, $revenue, $product_id);
    
    return $stmt->execute();
}

/**
 * Get transaction by ID
 */
function getTransactionById($conn, $transaction_id) {
    $query = "SELECT * FROM transactions WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $transaction_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Get transaction items by transaction ID
 */
function getTransactionItems($conn, $transaction_id) {
    $query = "SELECT * FROM transaction_items WHERE transaction_id = ? ORDER BY id ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $transaction_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get pending transactions count
 */
function getPendingTransactionsCount($conn) {
    $query = "SELECT COUNT(*) as count FROM transactions WHERE payment_status = 'pending'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    
    return $row['count'] ?? 0;
}

/**
 * Get today's sales
 */
function getTodaySales($conn) {
    $today = date('Y-m-d');
    $query = "SELECT SUM(total) as total FROM transactions WHERE payment_status = 'completed' AND DATE(created_at) = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['total'] ?? 0;
}

/**
 * Get this month sales
 */
function getMonthSales($conn) {
    $year = date('Y');
    $month = date('m');
    $query = "SELECT SUM(total) as total FROM transactions WHERE payment_status = 'completed' AND YEAR(created_at) = ? AND MONTH(created_at) = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $year, $month);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['total'] ?? 0;
}

/**
 * Get transaction count
 */
function getTransactionCount($conn, $status = null) {
    $query = "SELECT COUNT(*) as count FROM transactions";
    
    if ($status) {
        $query .= " WHERE payment_status = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $status);
    } else {
        $stmt = $conn->prepare($query);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['count'] ?? 0;
}

?>
