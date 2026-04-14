<?php
// controllers/send-order-email.php

session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get POST data
$orderNumber = $_POST['orderNumber'] ?? '';
$orderItems = json_decode($_POST['orderItems'] ?? '[]', true);
$totalAmount = $_POST['totalAmount'] ?? 0;
$paymentMethod = $_POST['paymentMethod'] ?? '';
$customerEmail = $_SESSION['user_email'] ?? '';
$customerName = $_SESSION['user_name'] ?? 'Customer';

// Validate data
if (empty($orderNumber) || empty($orderItems) || empty($totalAmount)) {
    echo json_encode(['success' => false, 'message' => 'Missing required order information']);
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

// Save order to database
$sql = "INSERT INTO orders (order_number, customer_name, customer_email, total_amount, payment_method, order_date, status) 
        VALUES (?, ?, ?, ?, ?, NOW(), 'pending')";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssds", $orderNumber, $customerName, $customerEmail, $totalAmount, $paymentMethod);

if ($stmt->execute()) {
    $orderId = $stmt->insert_id;
    
    // Save order items
    $sqlItems = "INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)";
    $stmtItems = $conn->prepare($sqlItems);
    
    foreach ($orderItems as $item) {
        $stmtItems->bind_param("iisid", 
            $orderId, 
            $item['id'], 
            $item['name'], 
            $item['quantity'], 
            $item['price']
        );
        $stmtItems->execute();
    }
    
    $stmtItems->close();
    
    // Send email to customer
    sendCustomerEmail($orderNumber, $customerName, $customerEmail, $orderItems, $totalAmount, $paymentMethod);
    
    // Send email to admin
    sendAdminEmail($orderNumber, $customerName, $customerEmail, $orderItems, $totalAmount, $paymentMethod);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Order placed successfully!',
        'orderNumber' => $orderNumber
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save order']);
}

$stmt->close();
$conn->close();

// Email Functions
function sendCustomerEmail($orderNumber, $customerName, $email, $items, $total, $paymentMethod) {
    $subject = "Order Confirmation - $orderNumber";
    
    $itemsHtml = '';
    foreach ($items as $item) {
        $itemTotal = $item['price'] * $item['quantity'];
        $itemsHtml .= "
            <tr>
                <td style='padding: 10px; border-bottom: 1px solid #eee;'>{$item['name']}</td>
                <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: center;'>{$item['quantity']}</td>
                <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>₱" . number_format($item['price'], 2) . "</td>
                <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>₱" . number_format($itemTotal, 2) . "</td>
            </tr>
        ";
    }
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #ffc107; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #fff; }
            .footer { background: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #666; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th { background: #f5f5f5; padding: 12px; text-align: left; font-weight: 600; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1 style='margin: 0; color: #fff;'>Solar Power Energy</h1>
            </div>
            <div class='content'>
                <h2>Order Confirmation</h2>
                <p>Dear $customerName,</p>
                <p>Thank you for your order! We're excited to help you transition to solar energy.</p>
                
                <div style='background: #e7f7ef; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <strong>Order Number:</strong> $orderNumber<br>
                    <strong>Payment Method:</strong> $paymentMethod<br>
                    <strong>Total Amount:</strong> ₱" . number_format($total, 2) . "
                </div>
                
                <h3>Order Details</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th style='text-align: center;'>Quantity</th>
                            <th style='text-align: right;'>Price</th>
                            <th style='text-align: right;'>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        $itemsHtml
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan='3' style='padding: 15px; text-align: right; font-weight: 600;'>Total:</td>
                            <td style='padding: 15px; text-align: right; font-weight: 600;'>₱" . number_format($total, 2) . "</td>
                        </tr>
                    </tfoot>
                </table>
                
                <p>Our team will contact you within 24 hours to confirm your order and arrange delivery.</p>
                
                <p>If you have any questions, please don't hesitate to contact us.</p>
                
                <p>Best regards,<br>Solar Power Energy Team</p>
            </div>
            <div class='footer'>
                <p>This is an automated message. Please do not reply directly to this email.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Solar Power Energy <noreply@solarpower.com>" . "\r\n";
    
    return mail($email, $subject, $message, $headers);
}

function sendAdminEmail($orderNumber, $customerName, $customerEmail, $items, $total, $paymentMethod) {
    $adminEmail = "admin@solarpower.com"; // Change this to your admin email
    $subject = "New Order Received - $orderNumber";
    
    $itemsList = '';
    foreach ($items as $item) {
        $itemTotal = $item['price'] * $item['quantity'];
        $itemsList .= "- {$item['name']} (x{$item['quantity']}): ₱" . number_format($itemTotal, 2) . "\n";
    }
    
    $message = "
    New order received!
    
    Order Number: $orderNumber
    Customer Name: $customerName
    Customer Email: $customerEmail
    Payment Method: $paymentMethod
    Total Amount: ₱" . number_format($total, 2) . "
    
    Order Items:
    $itemsList
    
    Please process this order as soon as possible.
    ";
    
    $headers = "From: Solar Power System <noreply@solarpower.com>" . "\r\n";
    
    return mail($adminEmail, $subject, $message, $headers);
}

// SQL for creating orders table (run this once in your database):
/*
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    customer_name VARCHAR(255),
    customer_email VARCHAR(255),
    total_amount DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(50),
    order_date DATETIME NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);
*/