<?php
header('Content-Type: application/json');

require_once 'config/dbconn.php';

if (!isset($_GET['orderRef'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Order reference required']);
    exit;
}

$orderRef = $_GET['orderRef'];

try {
    $stmt = $conn->prepare("
        SELECT 
            order_reference,
            payment_status,
            order_status,
            created_at
        FROM orders 
        WHERE order_reference = ?
    ");
    
    $stmt->bind_param('s', $orderRef);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();
    
    if ($order) {
        echo json_encode([
            'orderRef' => $order['order_reference'],
            'status' => $order['payment_status'],
            'orderStatus' => $order['order_status'],
            'createdAt' => $order['created_at']
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>