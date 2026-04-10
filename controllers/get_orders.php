<?php
header('Content-Type: application/json');

include "../config/dbconn.php";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        SELECT 
            id, 
            order_reference, 
            customer_name, 
            customer_email,
            customer_phone,
            customer_address,
            total_amount, 
            payment_method,
            payment_status, 
            order_status, 
            tracking_number, 
            current_location, 
            estimated_delivery, 
            receipt_path,
            staff_notes,
            created_at 
        FROM orders 
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Build receipt URL for frontend display
    foreach ($orders as &$order) {
        if (!empty($order['receipt_path'])) {
            // Provide a full URL path so the dashboard can show the image
            $order['receipt_url'] = '/' . ltrim($order['receipt_path'], '/');
        } else {
            $order['receipt_url'] = null;
        }
    }

    echo json_encode($orders);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>