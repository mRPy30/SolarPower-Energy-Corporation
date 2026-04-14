<?php
// controllers/customer_track_order.php
header('Content-Type: application/json');

// Get parameters
$orderRef = $_GET['order_ref'] ?? '';
$email = $_GET['email'] ?? '';

// Validate inputs
if (empty($orderRef) || empty($email)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please provide both order reference and email address'
    ]);
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "solar_power";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

// Fetch order with email verification
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

// Return tracking information
echo json_encode([
    'success' => true,
    'order' => $order,
    'tracking_history' => $trackingHistory
]);
?>