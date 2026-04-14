<?php
// controllers/staff_update_tracking.php
session_start();
header('Content-Type: application/json');

// Check if user is staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$orderId = intval($data['order_id']);
$orderStatus = $data['order_status'] ?? '';
$paymentStatus = $data['payment_status'] ?? '';
$trackingNumber = $data['tracking_number'] ?? null;
$currentLocation = $data['current_location'] ?? null;
$estimatedDelivery = $data['estimated_delivery'] ?? null;
$description = $data['description'] ?? '';
$sendNotification = $data['send_notification'] ?? false;

// Validate order status
$validOrderStatuses = [
    'pending', 
    'confirmed', 
    'preparing', 
    'ready_to_ship', 
    'in_transit', 
    'out_for_delivery', 
    'delivered', 
    'cancelled'
];

if (!in_array($orderStatus, $validOrderStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid order status']);
    exit;
}

// Validate payment status
$validPaymentStatuses = ['pending', 'paid', 'partial'];
if (!in_array($paymentStatus, $validPaymentStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid payment status']);
    exit;
}

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

// Get current order details before update
$orderStmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$orderStmt->bind_param("i", $orderId);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();

if ($orderResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    $orderStmt->close();
    $conn->close();
    exit;
}

$currentOrder = $orderResult->fetch_assoc();
$orderStmt->close();

// Set delivered_at timestamp if status is delivered
$deliveredAt = null;
if ($orderStatus === 'delivered') {
    $deliveredAt = date('Y-m-d H:i:s');
}

// Begin transaction
$conn->begin_transaction();

try {
    // Update order
    $updateStmt = $conn->prepare("
        UPDATE orders 
        SET order_status = ?, 
            payment_status = ?,
            tracking_number = ?,
            current_location = ?,
            estimated_delivery = ?,
            delivered_at = ?
        WHERE id = ?
    ");

    $updateStmt->bind_param(
        "ssssssi",
        $orderStatus,
        $paymentStatus,
        $trackingNumber,
        $currentLocation,
        $estimatedDelivery,
        $deliveredAt,
        $orderId
    );

    if (!$updateStmt->execute()) {
        throw new Exception('Failed to update order');
    }
    $updateStmt->close();

    // Insert into tracking history
    $historyStmt = $conn->prepare("
        INSERT INTO order_tracking_history 
        (order_id, status, location, description, updated_by_staff_id) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $staffId = $_SESSION['user_id'];
    $historyStmt->bind_param(
        "isssi",
        $orderId,
        $orderStatus,
        $currentLocation,
        $description,
        $staffId
    );

    if (!$historyStmt->execute()) {
        throw new Exception('Failed to log tracking history');
    }
    $historyStmt->close();

    // Commit transaction
    $conn->commit();

    // Send email notification if requested
    if ($sendNotification) {
        sendTrackingNotification($currentOrder, $orderStatus, $currentLocation, $description);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Order tracking updated successfully'
    ]);

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update tracking: ' . $e->getMessage()
    ]);
}

$conn->close();

// Function to send email notification
function sendTrackingNotification($order, $status, $location, $description) {
    $to = $order['customer_email'];
    $customerName = $order['customer_name'];
    $orderRef = $order['order_reference'];
    
    // Email subject
    $subject = "Order Update: " . $orderRef . " - " . getStatusTitle($status);
    
    // Email message
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
            .status { background: #fff; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #f39c12; }
            .button { background: #f39c12; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px; }
            .footer { text-align: center; margin-top: 20px; color: #7f8c8d; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Order Status Update</h2>
            </div>
            <div class='content'>
                <p>Dear {$customerName},</p>
                <p>Your order <strong>{$orderRef}</strong> has been updated:</p>
                
                <div class='status'>
                    <h3 style='margin-top: 0; color: #f39c12;'>" . getStatusTitle($status) . "</h3>
                    " . ($location ? "<p><strong>Current Location:</strong> {$location}</p>" : "") . "
                    " . ($description ? "<p>{$description}</p>" : "") . "
                </div>
                
                <p>You can track your order anytime using the button below:</p>
                <a href='http://yourwebsite.com/track-order.php?ref={$orderRef}' class='button'>Track Your Order</a>
                
                <p style='margin-top: 30px;'>If you have any questions, please don't hesitate to contact us at <strong>0995-394-7379</strong></p>
            </div>
            <div class='footer'>
                <p>Solar Power Energy Corporation</p>
                <p>This is an automated email, please do not reply.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Solar Power Energy <noreply@solarpower.com>" . "\r\n";
    
    // Send email
    @mail($to, $subject, $message, $headers);
}

function getStatusTitle($status) {
    $titles = [
        'pending' => 'Order Pending',
        'confirmed' => 'Order Confirmed',
        'preparing' => 'Preparing Your Order',
        'ready_to_ship' => 'Ready to Ship',
        'in_transit' => 'In Transit',
        'out_for_delivery' => 'Out for Delivery',
        'delivered' => 'Delivered',
        'cancelled' => 'Order Cancelled'
    ];
    
    return $titles[$status] ?? 'Order Updated';
}
?>