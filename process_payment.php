<?php
header('Content-Type: application/json');

// Maya API Configuration
class MayaConfig {
    const PUBLIC_KEY = 'pk-jfJcy6j57jBSKH5ZcOSqZ16uaRa0z1lFqH18mlFk3IV';  // Get from Maya Dashboard
    const SECRET_KEY = 'sk-VhThe0VYWe92H7y42goqAyNInbJhHeA0DXzZLzHRCAn';  // Get from Maya Dashboard
    
    // For TESTING (Sandbox)
    const API_BASE_URL = 'https://pg-sandbox.paymaya.com';

const CREATE_CHECKOUT_URL = '/checkout/v1/checkouts';
    
    // Update these URLs to your actual domain
    const SUCCESS_URL = 'https://solarpower.com.ph/payment-success.php';
    const FAILURE_URL = 'https://solarpower.com.ph/payment-failed.php';
    const CANCEL_URL = 'https://solarpower.com.ph/payment-cancelled.php';
}
// Database connection (using your config)
require_once 'config/dbconn.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required = ['customerName', 'customerEmail', 'customerPhone', 'customerAddress', 'paymentType', 'amountToPay', 'totalAmount', 'items'];
foreach ($required as $field) {
    if (!isset($input[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Missing required field: $field"]);
        exit;
    }
}

// Generate unique order reference
$orderRef = 'ORD-' . date('YmdHis') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));

// Split customer name
$nameParts = explode(' ', trim($input['customerName']), 2);
$firstName = $nameParts[0];
$lastName = $nameParts[1] ?? '';

// Parse address for city
$addressParts = explode(',', $input['customerAddress']);
$city = trim($addressParts[count($addressParts) - 2] ?? 'Manila');
$zipCode = '1000';

// Prepare items for Maya API
$mayaItems = [];
foreach ($input['items'] as $item) {
    $mayaItems[] = [
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

// Prepare Maya checkout payload
$payload = [
    'totalAmount' => [
        'value' => floatval($input['amountToPay']),
        'currency' => 'PHP'
    ],
    'buyer' => [
        'firstName' => $firstName,
        'lastName' => $lastName,
        'contact' => [
            'phone' => $input['customerPhone'],
            'email' => $input['customerEmail']
        ],
        'shippingAddress' => [
            'line1' => $input['customerAddress'],
            'city' => $city,
            'zipCode' => $zipCode
        ]
    ],
    'items' => $mayaItems,
    'redirectUrl' => [
        'success' => MayaConfig::SUCCESS_URL . '?ref=' . $orderRef,
        'failure' => MayaConfig::FAILURE_URL . '?ref=' . $orderRef,
        'cancel' => MayaConfig::CANCEL_URL . '?ref=' . $orderRef
    ],
    'requestReferenceNumber' => $orderRef,
    'metadata' => [
        'orderType' => $input['paymentType'],
        'customerAddress' => $input['customerAddress']
    ]
];

// Make API request to Maya
$ch = curl_init();
$apiKey = MayaConfig::PUBLIC_KEY;
$auth = base64_encode($apiKey . ':');

$headers = [
    'Content-Type: application/json',
    'Authorization: Basic ' . $auth
];

curl_setopt_array($ch, [
    CURLOPT_URL => MayaConfig::API_BASE_URL . MayaConfig::CREATE_CHECKOUT_URL,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_SSL_VERIFYPEER => true
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Connection error: ' . $error]);
    exit;
}

$responseData = json_decode($response, true);

if ($httpCode >= 200 && $httpCode < 300 && isset($responseData['checkoutId'])) {
    // Success! Save order to database
    try {
        // Insert into orders table
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
        
        $paymentMethod = 'maya_' . $input['paymentType'];
        $paymentStatus = 'pending';
        $orderStatus = 'pending';
        
        $stmt->bind_param(
            'sssssdsss',
            $orderRef,
            $input['customerName'],
            $input['customerEmail'],
            $input['customerPhone'],
            $input['customerAddress'],
            $input['totalAmount'],
            $paymentMethod,
            $paymentStatus,
            $orderStatus
        );
        
        $stmt->execute();
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
        
        foreach ($input['items'] as $item) {
            $productId = 0; // You can map this if you have product IDs
            $subtotal = floatval($item['price']) * intval($item['quantity']);
            
            $stmt->bind_param(
                'iisddd',
                $orderId,
                $productId,
                $item['name'],
                $item['quantity'],
                $item['price'],
                $subtotal
            );
            
            $stmt->execute();
        }
        
        $stmt->close();
        
        // Return success with Maya payment URL
        echo json_encode([
            'success' => true,
            'orderRef' => $orderRef,
            'checkoutId' => $responseData['checkoutId'],
            'paymentUrl' => $responseData['redirectUrl']
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage()
        ]);
    }
    
} else {
    // Maya API error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $responseData['message'] ?? 'Failed to create Maya payment',
        'code' => $httpCode
    ]);
}

$conn->close();
?>