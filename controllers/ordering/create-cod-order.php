<?php
session_start();
header('Content-Type: application/json');

// Enable full error reporting for mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Database connection
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "solar_power";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    // Get POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Validate input
    if (!$data || !isset($data['items'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request data'
        ]);
        exit;
    }

    // Extract data
    $totalAmount = floatval($data['totalAmount'] ?? ($data['amount'] ?? 0));
    $items = $data['items'] ?? [];
    $paymentMethod = $data['paymentMethod'] ?? 'cod';

    // Extract customer data (handle both object and flat structure)
    if (isset($data['customer']) && is_array($data['customer'])) {
        $customer = $data['customer'];
    } else {
        $customer = [
            'name' => $data['customerName'] ?? $data['customer']['name'] ?? '',
            'email' => $data['customerEmail'] ?? $data['customer']['email'] ?? '',
            'phone' => $data['customerPhone'] ?? $data['customer']['phone'] ?? '',
            'address' => $data['customerAddress'] ?? $data['customer']['address'] ?? ''
        ];
    }

    // Validate customer data
    if (empty($customer['name']) || empty($customer['email'])) {
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
        throw new Exception('Failed to create order: ' . $stmt->error);
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
        $productId = intval($item['id'] ?? 0);
        $productName = $item['name'] ?? $item['displayName'] ?? 'Unknown Product';
        $quantity = intval($item['quantity'] ?? 1);
        $price = floatval($item['price'] ?? 0);
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
            throw new Exception('Failed to add order item: ' . $itemStmt->error);
        }
    }
    
    $itemStmt->close();
    
    // Commit transaction
    $conn->commit();
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully',
        'orderRef' => $orderReference
    ]);
    
} catch (Throwable $e) {
    // Rollback on any error
    if (isset($conn) && $conn) {
        try { $conn->rollback(); } catch (Throwable $rbErr) {}
        try { $conn->close(); } catch (Throwable $clErr) {}
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Order creation failed: ' . $e->getMessage()
    ]);
}
?>