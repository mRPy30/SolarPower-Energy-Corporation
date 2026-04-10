<?php
/**
 * Maya Checkout Creation
 * IMPORTANT: No output before JSON response!
 */

// Start output buffering to prevent any accidental output
ob_start();

// Start session and set headers FIRST
session_start();
header('Content-Type: application/json');

// Disable HTML error output - we want JSON only
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Function to return JSON and exit
function jsonResponse($data) {
    ob_clean(); // Clear any buffered output
    echo json_encode($data);
    exit;
}

// Function to log errors
function logError($message, $context = []) {
    error_log("Maya Checkout Error: $message - " . json_encode($context));
}

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Validate input
    if (!$data) {
        logError("Invalid JSON input", ['input' => $input]);
        jsonResponse([
            'success' => false, 
            'message' => 'Invalid request data. Please refresh and try again.'
        ]);
    }
    
    if (!isset($data['amount']) || !isset($data['items']) || !isset($data['customer'])) {
        logError("Missing required fields", ['data' => $data]);
        jsonResponse([
            'success' => false, 
            'message' => 'Missing required information. Please check your order details.'
        ]);
    }
    
    // Extract data
    $amount = floatval($data['amount']);
    $totalAmount = floatval($data['totalAmount']);
    $paymentType = $data['paymentType'] ?? 'full';
    $items = $data['items'];
    $customer = $data['customer'];
    
    // Validate customer data
    if (empty($customer['name']) || empty($customer['email']) || 
        empty($customer['phone']) || empty($customer['address'])) {
        jsonResponse([
            'success' => false, 
            'message' => 'Please fill in all customer information fields.'
        ]);
    }
    
    // Validate items
    if (empty($items) || !is_array($items)) {
        jsonResponse([
            'success' => false, 
            'message' => 'Your cart is empty. Please add items before checkout.'
        ]);
    }
    
    // Database connection - using dbconn.php from config folder
    $dbPath = dirname(__DIR__) . '/../../config/dbconn.php';
    
    if (!file_exists($dbPath)) {
        logError("Database config not found", [
            'path' => $dbPath,
            'calculated_from' => __DIR__
        ]);
        jsonResponse([
            'success' => false,
            'message' => 'Database configuration file not found.'
        ]);
    }
    
    require_once $dbPath;
    
    if (!isset($conn) || !$conn) {
        logError("Database connection failed");
        jsonResponse([
            'success' => false,
            'message' => 'Database connection error. Please try again later.'
        ]);
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    // Generate unique order reference
    $orderReference = 'ORD-' . strtoupper(uniqid());
    
    // Insert into orders table (without payment_type if column doesn't exist)
    $stmt = $conn->prepare("
        INSERT INTO orders (
            order_reference, 
            customer_name, 
            customer_email, 
            customer_phone, 
            customer_address,
            total_amount,
            payment_method,
            payment_status,
            order_status,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    if (!$stmt) {
        throw new Exception('Failed to prepare order statement: ' . $conn->error);
    }
    
    $paymentMethod = 'maya - ' . $paymentType; // Include payment type in method
    $paymentStatus = 'pending';
    $orderStatus = 'pending';
    
    $stmt->bind_param(
        "sssssdsss",
        $orderReference,
        $customer['name'],
        $customer['email'],
        $customer['phone'],
        $customer['address'],
        $totalAmount,
        $paymentMethod,
        $paymentStatus,
        $orderStatus
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create order: ' . $stmt->error);
    }
    
    $orderId = $conn->insert_id;
    $stmt->close();
    
    // Insert order items
    $stmt = $conn->prepare("
        INSERT INTO order_items (
            order_id,
            product_id,
            product_name,
            quantity,
            price,
            subtotal
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    if (!$stmt) {
        throw new Exception('Failed to prepare order items statement: ' . $conn->error);
    }
    
    foreach ($items as $item) {
        $productId = intval($item['id']);
        $productName = $item['name'];
        $productBrand = $item['brand'] ?? '';
        $quantity = intval($item['quantity']);
        $price = floatval($item['price']);
        $subtotal = $price * $quantity;
        
        $stmt->bind_param(
            "iissidi",
            $orderId,
            $productId,
            $productName,
            $productBrand,
            $quantity,
            $price,
            $subtotal
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to add order item: ' . $stmt->error);
        }
        
        // Update product stock
        $updateStmt = $conn->prepare("
            UPDATE products 
            SET stockQuantity = stockQuantity - ? 
            WHERE id = ? AND stockQuantity >= ?
        ");
        
        if (!$updateStmt) {
            throw new Exception('Failed to prepare stock update: ' . $conn->error);
        }
        
        $updateStmt->bind_param("iii", $quantity, $productId, $quantity);
        
        if (!$updateStmt->execute() || $updateStmt->affected_rows === 0) {
            throw new Exception('Insufficient stock for product: ' . $productName);
        }
        $updateStmt->close();
    }
    $stmt->close();
    
    // Create Maya checkout session
    $mayaCheckout = createMayaCheckout($orderReference, $amount, $paymentType, $customer, $items);
    
    if (!$mayaCheckout || !isset($mayaCheckout['checkoutUrl'])) {
        throw new Exception('Failed to create Maya checkout session. Please try again.');
    }
    
    // Update order with Maya checkout ID (only if columns exist)
    // Check if columns exist first
    $checkColumns = $conn->query("SHOW COLUMNS FROM orders LIKE 'maya_checkout_id'");
    
    if ($checkColumns && $checkColumns->num_rows > 0) {
        $stmt = $conn->prepare("
            UPDATE orders 
            SET maya_checkout_id = ?, maya_checkout_url = ? 
            WHERE id = ?
        ");
        
        if ($stmt) {
            $mayaCheckoutId = $mayaCheckout['checkoutId'] ?? null;
            $mayaCheckoutUrl = $mayaCheckout['checkoutUrl'];
            $stmt->bind_param("ssi", $mayaCheckoutId, $mayaCheckoutUrl, $orderId);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        // Log Maya info if columns don't exist
        logError("Maya checkout created but columns don't exist", [
            'orderId' => $orderId,
            'checkoutId' => $mayaCheckout['checkoutId'] ?? null,
            'checkoutUrl' => $mayaCheckout['checkoutUrl']
        ]);
    }
    
    // Commit transaction
    $conn->commit();
    $conn->close();
    
    // Return success with checkout URL
    jsonResponse([
        'success' => true,
        'orderId' => $orderId,
        'orderReference' => $orderReference,
        'checkoutUrl' => $mayaCheckout['checkoutUrl']
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn) {
        $conn->rollback();
        $conn->close();
    }
    
    logError($e->getMessage(), ['trace' => $e->getTraceAsString()]);
    
    jsonResponse([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Create Maya Payment Checkout Session
 */
function createMayaCheckout($orderReference, $amount, $paymentType, $customer, $items) {
    // Maya API Credentials
    $publicKey = 'pk-z8y8JmaNke3IbaGyMQ75nJ5bNnZV6Z1D8QQ1VD9IUmG';
    $secretKey = 'sk-gmw8VOSvEksLMhqUGPIbkbMFHhOtmHaJChxIhaowQ5u';
    
    // Use sandbox for testing
    $apiUrl = 'https://pg-sandbox.paymaya.com/checkout/v1/checkouts';
    
    // Base64 encode the secret key for authorization
    $authHeader = 'Basic ' . base64_encode($secretKey . ':');
    
    // Prepare line items for Maya
    $lineItems = [];
    foreach ($items as $item) {
        $lineItems[] = [
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
    
    // Get current domain
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $baseUrl = $protocol . '://' . $host;
    
    // Prepare checkout payload
    $payload = [
        'totalAmount' => [
            'value' => $amount,
            'currency' => 'PHP'
        ],
        'buyer' => [
            'firstName' => explode(' ', $customer['name'])[0] ?? $customer['name'],
            'lastName' => explode(' ', $customer['name'], 2)[1] ?? '',
            'contact' => [
                'phone' => $customer['phone'],
                'email' => $customer['email']
            ],
            'shippingAddress' => [
                'line1' => $customer['address'],
                'city' => 'Manila',
                'countryCode' => 'PH',
                'zipCode' => '1000'
            ]
        ],
        'items' => $lineItems,
        'redirectUrl' => [
            'success' => $baseUrl . '/payment-success.php?ref=' . $orderReference,
            'failure' => $baseUrl . '/payment-failed.php?ref=' . $orderReference,
            'cancel' => $baseUrl . '/payment-cancelled.php?ref=' . $orderReference
        ],
        'requestReferenceNumber' => $orderReference,
        'metadata' => [
            'paymentType' => $paymentType
        ]
    ];
    
    // Initialize cURL
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: ' . $authHeader
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    // Log the response for debugging
    logError("Maya API Response", [
        'httpCode' => $httpCode,
        'response' => $response,
        'curlError' => $curlError
    ]);
    
    // Handle errors
    if ($curlError) {
        logError("cURL Error: $curlError");
        return null;
    }
    
    if ($httpCode !== 200 && $httpCode !== 201) {
        logError("Maya API Error", ['httpCode' => $httpCode, 'response' => $response]);
        return null;
    }
    
    $result = json_decode($response, true);
    
    if (!$result || !isset($result['checkoutId']) || !isset($result['redirectUrl'])) {
        logError("Invalid Maya response", ['response' => $response]);
        return null;
    }
    
    return [
        'checkoutId' => $result['checkoutId'],
        'checkoutUrl' => $result['redirectUrl']
    ];
}

// Clear output buffer and end
ob_end_flush();
?>