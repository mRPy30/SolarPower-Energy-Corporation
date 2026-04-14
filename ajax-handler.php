<?php
// ============================================
// AJAX HANDLER FOR PAYMAYA & COD ORDERS
// File: ajax-handler.php
// ============================================

session_start();
header('Content-Type: application/json');
// Include database connection
require_once 'config/dbconn.php';

// Get JSON input
$input = file_get_contents('php://input');
$requestData = json_decode($input, true);

// Validate request
if (!$requestData || !isset($requestData['action'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

// ============================================
// PAYMAYA CHECKOUT HANDLER
// ============================================
if ($requestData['action'] === 'create_maya_checkout') {
    
    // PayMaya API Configuration
    define('PAYMAYA_ENV', 'sandbox'); // Change to 'production' for live
    define('PAYMAYA_PUBLIC_KEY', 'pk-Z0OSzLvIcOI2UIvDhdTGVVfRSSeiGStnceqwUE7n0Ah');
    define('PAYMAYA_SECRET_KEY', 'sk-X0vDFlRfJMyVUlqCqapYVVRLvWnLHzf1LcmivehzLK8');
    
    $apiUrls = [
        'sandbox' => 'https://pg-sandbox.paymaya.com/checkout/v1/checkouts',
        'production' => 'https://pg.paymaya.com/checkout/v1/checkouts'
    ];
    $PAYMAYA_API_URL = $apiUrls[PAYMAYA_ENV];
    
    // Extract data
    $amount = floatval($requestData['amount']);
    $totalAmount = floatval($requestData['totalAmount']);
    $paymentType = $requestData['paymentType'] ?? 'full';
    $items = $requestData['items'] ?? [];
    $customer = $requestData['customer'] ?? [];
    
    // Validate
    if ($amount <= 0 || empty($items) || empty($customer)) {
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }
    
    // Generate unique reference
    $referenceNumber = 'ORD-' . strtoupper(uniqid());
    $trackingNumber = 'TRK-' . date('Ymd') . '-' . rand(1000, 9999);
    
    // Determine payment method
    if ($paymentType === 'full') {
        $paymentMethod = 'maya_full';
        $paymentStatus = 'pending';
    } else if ($paymentType === 'downpayment') {
        $paymentMethod = 'maya_50percent';
        $paymentStatus = 'partial';
    } else {
        $paymentMethod = 'cod';
        $paymentStatus = 'pending';
    }
    
    // Get domain automatically
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $domain = $protocol . "://" . $_SERVER['HTTP_HOST'];
    
    // Prepare PayMaya checkout payload
    $checkoutData = [
        'totalAmount' => [
            'value' => $amount,
            'currency' => 'PHP'
        ],
        'buyer' => [
            'firstName' => explode(' ', $customer['name'])[0],
            'lastName' => explode(' ', $customer['name'])[1] ?? 'Customer',
            'contact' => [
                'phone' => $customer['phone'],
                'email' => $customer['email']
            ],
            'shippingAddress' => [
                'line1' => $customer['address'],
                'city' => 'Manila',
                'countryCode' => 'PH'
            ]
        ],
        'items' => [],
        'redirectUrl' => [
            'success' => $domain . '/payment-success.php?ref=' . $referenceNumber . '&type=' . $paymentType,
            'failure' => $domain . '/payment-failed.php?ref=' . $referenceNumber,
            'cancel' => $domain . '/payment-cancelled.php?ref=' . $referenceNumber
        ],
        'requestReferenceNumber' => $referenceNumber,
        'metadata' => [
            'paymentType' => $paymentType,
            'totalAmount' => $totalAmount
        ]
    ];
    
    // Add items to payload
    foreach ($items as $item) {
        $checkoutData['items'][] = [
            'name' => $item['name'],
            'quantity' => intval($item['quantity']),
            'amount' => [
                'value' => floatval($item['price'])
            ],
            'totalAmount' => [
                'value' => floatval($item['price']) * intval($item['quantity'])
            ]
        ];
    }
    
    // Save order to database FIRST
    $stmt = $conn->prepare("INSERT INTO orders (
        order_reference, customer_name, customer_email, customer_phone, 
        customer_address, total_amount, payment_method, payment_status, 
        order_status, tracking_number, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW())");
    
    $stmt->bind_param("sssssdsss",
        $referenceNumber, $customer['name'], $customer['email'], 
        $customer['phone'], $customer['address'], $totalAmount,
        $paymentMethod, $paymentStatus, $trackingNumber
    );
    
    if (!$stmt->execute()) {
        echo json_encode(['error' => 'Failed to create order: ' . $stmt->error]);
        exit;
    }
    
    $orderId = $conn->insert_id;
    $stmt->close();
    
    // Insert order items
    $itemStmt = $conn->prepare("INSERT INTO order_items (
        order_id, product_id, product_name, quantity, price, subtotal
    ) VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach ($items as $item) {
        $productId = isset($item['id']) ? intval($item['id']) : 0;
        $quantity = intval($item['quantity']);
        $price = floatval($item['price']);
        $subtotal = $price * $quantity;
        
        $itemStmt->bind_param("iisidd",
            $orderId, $productId, $item['name'], 
            $quantity, $price, $subtotal
        );
        $itemStmt->execute();
    }
    $itemStmt->close();
    
    // Call PayMaya API
    $ch = curl_init($PAYMAYA_API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($checkoutData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode(PAYMAYA_SECRET_KEY . ':')
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        
        // Delete order if cURL failed
        $conn->query("DELETE FROM order_items WHERE order_id = $orderId");
        $conn->query("DELETE FROM orders WHERE id = $orderId");
        
        echo json_encode(['error' => 'Connection Error: ' . $error]);
        exit;
    }
    
    curl_close($ch);
    
    // Handle PayMaya response
    if ($httpCode === 200 || $httpCode === 201) {
        $responseData = json_decode($response, true);
        
        echo json_encode([
            'success' => true,
            'checkoutUrl' => $responseData['redirectUrl'],
            'checkoutId' => $responseData['checkoutId'] ?? '',
            'referenceNumber' => $referenceNumber
        ]);
    } else {
        // Delete order if PayMaya failed
        $conn->query("DELETE FROM order_items WHERE order_id = $orderId");
        $conn->query("DELETE FROM orders WHERE id = $orderId");
        
        $errorData = json_decode($response, true);
        echo json_encode([
            'error' => 'PayMaya Error: ' . ($errorData['message'] ?? 'Unknown error'),
            'httpCode' => $httpCode,
            'details' => $errorData
        ]);
    }
    
    $conn->close();
    exit;
}

// ============================================
// COD ORDER HANDLER
// ============================================
if ($requestData['action'] === 'create_cod_order') {
    
    $amount = floatval($requestData['amount']);
    $items = $requestData['items'] ?? [];
    $customer = $requestData['customer'] ?? [];
    
    if ($amount <= 0 || empty($items) || empty($customer)) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }
    
    $referenceNumber = 'ORD-' . strtoupper(uniqid());
    $trackingNumber = 'TRK-' . date('Ymd') . '-' . rand(1000, 9999);
    
    $stmt = $conn->prepare("INSERT INTO orders (
        order_reference, customer_name, customer_email, customer_phone,
        customer_address, total_amount, payment_method, payment_status,
        order_status, tracking_number, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, 'cod', 'pending', 'pending', ?, NOW())");
    
    $stmt->bind_param("sssssds",
        $referenceNumber, $customer['name'], $customer['email'],
        $customer['phone'], $customer['address'], $amount, $trackingNumber
    );
    
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Failed to create order: ' . $stmt->error]);
        exit;
    }
    
    $orderId = $conn->insert_id;
    $stmt->close();
    
    // Insert order items
    $itemStmt = $conn->prepare("INSERT INTO order_items (
        order_id, product_id, product_name, quantity, price, subtotal
    ) VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach ($items as $item) {
        $productId = isset($item['id']) ? intval($item['id']) : 0;
        $quantity = intval($item['quantity']);
        $price = floatval($item['price']);
        $subtotal = $price * $quantity;
        
        $itemStmt->bind_param("iisidd",
            $orderId, $productId, $item['name'],
            $quantity, $price, $subtotal
        );
        $itemStmt->execute();
    }
    $itemStmt->close();
    
    echo json_encode([
        'success' => true,
        'orderId' => $referenceNumber,
        'message' => 'COD order placed successfully'
    ]);
    
    $conn->close();
    exit;
}

// If no valid action
echo json_encode(['error' => 'Invalid action']);
exit;
?>