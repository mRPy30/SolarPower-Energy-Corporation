<?php
header('Content-Type: application/json');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "solar_power";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

try {
    // We select from 'orders' as shown in your phpMyAdmin screenshot
    $sql = "SELECT id, order_reference, customer_name, customer_email, 
                   customer_phone, total_amount, order_status, 
                   payment_status, tracking_number, current_location, 
                   estimated_delivery, delivered_at, created_at 
            FROM orders 
            ORDER BY created_at DESC";
            
    $result = $conn->query($sql);
    $tracking = [];

    if ($result) {
        while($row = $result->fetch_assoc()) {
            $tracking[] = $row;
        }
        echo json_encode(['success' => true, 'tracking' => $tracking]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Query failed: ' . $conn->error]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>