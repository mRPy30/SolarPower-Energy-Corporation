<?php
// controllers/customer_track_order.php
header('Content-Type: application/json');

$phone = $_GET['phone'] ?? '';

if (empty($phone)) {
    echo json_encode(['success' => false, 'message' => 'Please provide your cellphone number']);
    exit;
}

include "../config/dbconn.php";

// Modified Query: Join order_items and GROUP_CONCAT the product names
$stmt = $conn->prepare("
    SELECT 
        o.id, 
        o.order_reference, 
        o.order_status, 
        o.total_amount, 
        o.current_location, 
        o.payment_method,
        o.created_at,
        GROUP_CONCAT(oi.product_name SEPARATOR ', ') AS items_ordered
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.customer_phone = ? 
    GROUP BY o.id
    ORDER BY o.created_at DESC
");

$stmt->bind_param("s", $phone);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'No orders found for this cellphone number.']);
    exit;
}

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

echo json_encode(['success' => true, 'orders' => $orders]);
?>