<?php
header('Content-Type: application/json');

include_once(__DIR__ . '/../config/dbconn.php');

$search  = isset($_GET['search'])  ? trim($_GET['search'])  : '';
$status  = isset($_GET['status'])  ? trim($_GET['status'])  : '';
$payment = isset($_GET['payment']) ? trim($_GET['payment']) : '';

$query = "SELECT id, order_reference, customer_name, customer_email, customer_phone,
          total_amount, payment_method, payment_status, order_status,
          tracking_number, created_at
          FROM orders
          WHERE order_status != 'archived'";

$params = [];
$types  = '';

if ($search !== '') {
    $query .= " AND (customer_name LIKE ? OR order_reference LIKE ? OR customer_email LIKE ?)";
    $sp = '%' . $search . '%';
    $params[] = $sp; $params[] = $sp; $params[] = $sp;
    $types .= 'sss';
}
if ($status !== '') {
    $query .= " AND order_status = ?";
    $params[] = $status;
    $types .= 's';
}
if ($payment !== '') {
    $query .= " AND payment_method = ?";
    $params[] = $payment;
    $types .= 's';
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'data' => $orders]);
?>
