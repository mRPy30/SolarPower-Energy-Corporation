<?php
session_start();
header('Content-Type: application/json');

// Enable full error reporting for mysqli to catch all issues in the try-catch block
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

    // Check for POST data and file
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    // Get POST data
    $customerName = $_POST['customerName'] ?? '';
    $customerEmail = $_POST['customerEmail'] ?? '';
    $customerPhone = $_POST['customerPhone'] ?? '';
    $customerAddress = $_POST['customerAddress'] ?? '';
    $totalAmount = floatval($_POST['totalAmount'] ?? 0);
    $items = json_decode($_POST['items'] ?? '[]', true);
    $paymentMethod = $_POST['paymentMethod'] ?? 'instapay';

    // Validate input
    if (empty($customerName) || empty($customerEmail) || empty($items)) {
        echo json_encode(['success' => false, 'message' => 'Required fields are missing']);
        exit;
    }

    // Handle File Upload
    $receiptPath = null;
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/receipts/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                throw new Exception('Failed to create receipts directory');
            }
        }
        
        $fileTmpPath = $_FILES['receipt']['tmp_name'];
        $fileName = $_FILES['receipt']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $newFileName = 'RCP-' . time() . '-' . uniqid() . '.' . $fileExtension;
        $destPath = $uploadDir . $newFileName;
        
        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $receiptPath = 'uploads/receipts/' . $newFileName;
        } else {
            throw new Exception('Failed to move uploaded receipt');
        }
    } else {
        $fileError = isset($_FILES['receipt']) ? $_FILES['receipt']['error'] : 'No file uploaded';
        echo json_encode(['success' => false, 'message' => 'Payment receipt is required for InstaPay. Error code: ' . $fileError]);
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
         customer_address, total_amount, payment_method, payment_status, order_status, receipt_path) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', ?)
    ");
    
    $stmt->bind_param(
        "sssssdss",
        $orderReference,
        $customerName,
        $customerEmail,
        $customerPhone,
        $customerAddress,
        $totalAmount,
        $paymentMethod,
        $receiptPath
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
        $productName = $item['displayName'] ?? $item['name'] ?? 'Unknown Product';
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
    // Rollback on any error (Exception or Fatal Error)
    if (isset($conn) && $conn) {
        try { $conn->rollback(); } catch (Throwable $rbErr) {}
        try { $conn->close(); } catch (Throwable $clErr) {}
    }
    
    // Delete uploaded file if database insert failed
    if (isset($receiptPath) && $receiptPath && file_exists('../../' . $receiptPath)) {
        unlink('../../' . $receiptPath);
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Order creation failed: ' . $e->getMessage()
    ]);
}
?>
