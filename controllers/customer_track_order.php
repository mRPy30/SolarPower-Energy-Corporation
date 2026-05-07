<?php
// controllers/customer_track_order.php
header('Content-Type: application/json');

// Get parameters
$phone = $_GET['phone'] ?? '';
$orderRef = $_GET['order_ref'] ?? '';
$email = $_GET['email'] ?? '';

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "solar_power";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// MODE 1: Search by Phone (Order History)
if (!empty($phone)) {
    $stmt = $conn->prepare("
        SELECT o.*, 
        (SELECT product_name FROM order_items WHERE order_id = o.id LIMIT 1) as items_ordered
        FROM orders o 
        WHERE customer_phone = ? 
        ORDER BY created_at DESC
    ");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    $stmt->close();
    echo json_encode([
        'success' => count($orders) > 0,
        'orders' => $orders,
        'message' => count($orders) > 0 ? '' : 'No orders found for this phone number.'
    ]);
    $conn->close();
    exit;
}

// MODE 2: Search by Order Reference and Email (Detailed Tracking)
if (!empty($orderRef) && !empty($email)) {
    $stmt = $conn->prepare("
        SELECT * FROM orders 
        WHERE order_reference = ? 
        AND customer_email = ?
    ");

    $stmt->bind_param("ss", $orderRef, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Order not found. Please check your order reference and email address.'
        ]);
        $stmt->close();
        $conn->close();
        exit;
    }

    $order = $result->fetch_assoc();
    $orderId = $order['id'];
    $stmt->close();

    // Fetch tracking history
    $historyStmt = $conn->prepare("
        SELECT * FROM order_tracking_history 
        WHERE order_id = ? 
        ORDER BY created_at ASC
    ");

    $historyStmt->bind_param("i", $orderId);
    $historyStmt->execute();
    $historyResult = $historyStmt->get_result();

    $trackingHistory = [];
    while ($row = $historyResult->fetch_assoc()) {
        $trackingHistory[] = $row;
    }

    $historyStmt->close();
    $conn->close();

    echo json_encode([
        'success' => true,
        'order' => $order,
        'tracking_history' => $trackingHistory
    ]);
    exit;
}

echo json_encode([
    'success' => false,
    'message' => 'Please provide search criteria (Phone number or Order Reference + Email).'
]);
$conn->close();
?>