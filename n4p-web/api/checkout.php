<?php
/**
 * N4P (Not4Posers) POS System
 * Checkout Payment Handler API
 */

header('Content-Type: application/json');

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['cart']) || empty($data['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart data']);
    exit;
}

// Validate and process cart
$cart = $data['cart'];
$customer_name = $data['customerName'] ?? 'Walk-in Customer';
$payment_method = $data['paymentMethod'] ?? 'cash';
$discount_percentage = (int)($data['discountPercentage'] ?? 0);

$conn->begin_transaction();

try {
    // Calculate totals
    $subtotal = 0;
    foreach ($cart as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    
    $discount = $subtotal * ($discount_percentage / 100);
    $subtotal_after_discount = $subtotal - $discount;
    $tax = $subtotal_after_discount * 0.1; // 10% tax
    $total = $subtotal_after_discount + $tax;
    
    // Generate transaction number
    $transaction_number = generateTransactionNumber();
    
    // Insert transaction
    $query = "INSERT INTO transactions (
        transaction_number, user_id, customer_name, subtotal, discount, 
        discount_percentage, tax, total, payment_method, payment_status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed')";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param(
        "sisdddsss",
        $transaction_number, $user_id, $customer_name, $subtotal,
        $discount, $discount_percentage, $tax, $total, $payment_method
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $transaction_id = $conn->insert_id;
    
    // Insert transaction items and update stock
    foreach ($cart as $item) {
        $product_id = $item['id'];
        $quantity = $item['quantity'];
        $unit_price = $item['price'];
        $item_subtotal = $unit_price * $quantity;
        
        // Insert transaction item
        $item_query = "INSERT INTO transaction_items (
            transaction_id, product_id, product_name, quantity, unit_price, subtotal
        ) VALUES (?, ?, ?, ?, ?, ?)";
        
        $item_stmt = $conn->prepare($item_query);
        if (!$item_stmt) {
            throw new Exception('Item prepare failed: ' . $conn->error);
        }
        
        $item_stmt->bind_param(
            "iissdd",
            $transaction_id, $product_id, $item['name'], $quantity, $unit_price, $item_subtotal
        );
        
        if (!$item_stmt->execute()) {
            throw new Exception('Item execute failed: ' . $item_stmt->error);
        }
        
        // Update product stock
        $stock_query = "UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?";
        $stock_stmt = $conn->prepare($stock_query);
        if (!$stock_stmt) {
            throw new Exception('Stock prepare failed: ' . $conn->error);
        }
        
        $stock_stmt->bind_param("iii", $quantity, $product_id, $quantity);
        if (!$stock_stmt->execute()) {
            throw new Exception('Stock update failed: ' . $stock_stmt->error);
        }
        
        if ($stock_stmt->affected_rows == 0) {
            throw new Exception('Insufficient stock for product ID: ' . $product_id);
        }
        
        // Update best selling summary
        updateBestSellingSummary($conn, $product_id, $quantity, $unit_price);
    }
    
    // Insert payment log
    $payment_query = "INSERT INTO payment_logs (transaction_id, payment_method, amount_paid) VALUES (?, ?, ?)";
    $payment_stmt = $conn->prepare($payment_query);
    if (!$payment_stmt) {
        throw new Exception('Payment prepare failed: ' . $conn->error);
    }
    
    $payment_stmt->bind_param("isd", $transaction_id, $payment_method, $total);
    if (!$payment_stmt->execute()) {
        throw new Exception('Payment insert failed: ' . $payment_stmt->error);
    }
    
    // Update daily sales summary
    $today = date('Y-m-d');
    $summary_query = "INSERT INTO daily_sales_summary (sale_date, total_transactions, total_revenue, total_discount, total_tax, total_items_sold)
                      VALUES (?, 1, ?, ?, ?, ?)
                      ON DUPLICATE KEY UPDATE
                      total_transactions = total_transactions + 1,
                      total_revenue = total_revenue + VALUES(total_revenue),
                      total_discount = total_discount + VALUES(total_discount),
                      total_tax = total_tax + VALUES(total_tax),
                      total_items_sold = total_items_sold + VALUES(total_items_sold)";
    
    $total_items = count($cart);
    $summary_stmt = $conn->prepare($summary_query);
    if (!$summary_stmt) {
        throw new Exception('Summary prepare failed: ' . $conn->error);
    }
    
    $summary_stmt->bind_param("sdddi", $today, $total, $discount, $tax, $total_items);
    if (!$summary_stmt->execute()) {
        throw new Exception('Summary insert failed: ' . $summary_stmt->error);
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'transaction_id' => $transaction_id,
        'transaction_number' => $transaction_number,
        'total' => $total,
        'message' => 'Transaction completed successfully'
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
