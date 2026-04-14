<?php
session_start();
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "solar_power";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

// Get POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input
if (!$data || !isset($data['items']) || !isset($data['customer'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request data'
    ]);
    exit;
}

// Extract data
$totalAmount = floatval($data['amount']);
$items = $data['items'];
$customer = $data['customer'];
$paymentMethod = $data['paymentMethod'] ?? 'cod';

// Validate customer data
if (empty($customer['name']) || empty($customer['email']) || 
    empty($customer['phone']) || empty($customer['address'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Customer information is incomplete'
    ]);
    exit;
}

// Validate cart items
if (empty($items) || !is_array($items)) {
    echo json_encode([
        'success' => false,
        'message' => 'Cart is empty'
    ]);
    exit;
}

// Generate order reference
$orderReference = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));

// Begin transaction
$conn->begin_transaction();

try {
    // Insert order
    $stmt = $conn->prepare("
        INSERT INTO orders 
        (order_reference, customer_name, customer_email, customer_phone, 
         customer_address, total_amount, payment_method, payment_status, order_status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')
    ");
    
    $stmt->bind_param(
        "sssssds",
        $orderReference,
        $customer['name'],
        $customer['email'],
        $customer['phone'],
        $customer['address'],
        $totalAmount,
        $paymentMethod
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create order');
    }
    
    $orderId = $conn->insert_id;
    $stmt->close();
    
    // Insert order items
    $itemStmt = $conn->prepare("
        INSERT INTO order_items 
        (order_id, product_id, product_name, quantity, price, subtotal) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($items as $item) {
        $productId = intval($item['id']);
        $productName = $item['displayName'];
        $quantity = intval($item['quantity']);
        $price = floatval($item['price']);
        $subtotal = $price * $quantity;
        
        $itemStmt->bind_param(
            "iisidd",
            $orderId,
            $productId,
            $productName,
            $quantity,
            $price,
            $subtotal
        );
        
        if (!$itemStmt->execute()) {
            throw new Exception('Failed to add order item');
        }
    }
    
    $itemStmt->close();
    
    // Commit transaction
    $conn->commit();
    
    // Send success response
    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully',
        'orderId' => $orderReference
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => 'Order creation failed: ' . $e->getMessage()
    ]);
}

$conn->close();
?>