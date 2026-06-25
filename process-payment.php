<?php
/**
 * LIGHTWEIGHT MAYA INTEGRATION FILE
 * Compiles PayMaya payload from $_SESSION['cart']
 * Inserts order into database and initiates redirection
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/dbconn.php';

// Support both form POST and JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input && !empty($_POST)) {
    $input = $_POST;
}

// Synchronize items to $_SESSION['cart'] if passed in request (backward compatibility)
if (isset($input['items']) && is_array($input['items'])) {
    $_SESSION['cart'] = [];
    foreach ($input['items'] as $item) {
        $id = $item['id'] ?? $item['variant_id'] ?? null;
        if ($id) {
            $_SESSION['cart'][$id] = [
                'name' => $item['name'] ?? 'Product',
                'price' => floatval($item['price'] ?? 0.00),
                'quantity' => intval($item['quantity'] ?? 1)
            ];
        }
    }
}

// Ensure cart is not empty
if (empty($_SESSION['cart'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Your cart is empty.']);
    exit;
}

$customerName = isset($input['customerName']) ? trim($input['customerName']) : '';
$customerEmail = isset($input['customerEmail']) ? trim($input['customerEmail']) : '';
$customerPhone = isset($input['customerPhone']) ? trim($input['customerPhone']) : '';
$customerAddress = isset($input['customerAddress']) ? trim($input['customerAddress']) : '';
$paymentType = isset($input['paymentType']) ? trim($input['paymentType']) : 'full';

if (empty($customerName) || empty($customerEmail) || empty($customerPhone) || empty($customerAddress)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing customer checkout details.']);
    exit;
}

// 1. Compile the Maya payload using a single flat loop over $_SESSION['cart']
$line_items = [];
$subtotal = 0;

foreach ($_SESSION['cart'] as $id => $item) {
    $item_total = $item['price'] * $item['quantity'];
    $subtotal += $item_total;

    $line_items[] = [
        "name" => strip_tags($item['name']),
        "quantity" => intval($item['quantity']),
        "amount" => [ "value" => number_format($item['price'], 2, '.', '') ],
        "totalAmount" => [ "value" => number_format($item_total, 2, '.', '') ]
    ];
}

// Include the dynamic delivery fee if available
$delivery_fee = isset($_SESSION['delivery_fee']) ? floatval($_SESSION['delivery_fee']) : 0.00;
// If session delivery fee is not set, but a total is provided, infer it:
if ($delivery_fee <= 0 && isset($input['totalAmount']) && floatval($input['totalAmount']) > $subtotal) {
    $delivery_fee = floatval($input['totalAmount']) - $subtotal;
}

if ($delivery_fee > 0) {
    $line_items[] = [
        "name" => "Delivery / Shipping Fee",
        "quantity" => 1,
        "amount" => [ "value" => number_format($delivery_fee, 2, '.', '') ],
        "totalAmount" => [ "value" => number_format($delivery_fee, 2, '.', '') ]
    ];
}

$grand_total = $subtotal + $delivery_fee;

// Generate unique order reference
$orderRef = 'ORD-' . date('YmdHis') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));

// Split customer name
$nameParts = explode(' ', trim($customerName), 2);
$firstName = $nameParts[0] ?? 'Customer';
$lastName = $nameParts[1] ?? 'Customer';

// Save order and order items to database
try {
    $conn->begin_transaction();

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
    
    $paymentMethod = 'maya_' . $paymentType;
    $paymentStatus = 'pending';
    $orderStatus = 'pending';
    
    $stmt->bind_param(
        'sssssdsss',
        $orderRef,
        $customerName,
        $customerEmail,
        $customerPhone,
        $customerAddress,
        $grand_total,
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
    
    foreach ($_SESSION['cart'] as $id => $item) {
        $productId = intval($id);
        $subtotal_item = floatval($item['price']) * intval($item['quantity']);
        
        $stmt->bind_param(
            'iisddd',
            $orderId,
            $productId,
            $item['name'],
            $item['quantity'],
            $item['price'],
            $subtotal_item
        );
        
        $stmt->execute();
    }
    
    $stmt->close();
    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
    $conn->close();
    exit;
}

// 2. Maya API Integration Setup
$secret_key = "sk-dDHeLd2o6TV52ZXTpRrIBk7ZgLOWf5uLqjdpaAwdRVS"; // Default sandbox key
$is_production = false;
$url = "https://pg-sandbox.paymaya.com/checkout/v1/checkouts";

// Load keys dynamically from maya.php config file if available
$mayaConfigPath = __DIR__ . '/controllers/ordering/maya.php';
if (!file_exists($mayaConfigPath)) {
    $mayaConfigPath = __DIR__ . '/maya.php';
}
if (!file_exists($mayaConfigPath)) {
    $mayaConfigPath = dirname(__DIR__) . '/maya.php';
}

if (file_exists($mayaConfigPath)) {
    $mayaConfig = include $mayaConfigPath;
    if (is_array($mayaConfig) && isset($mayaConfig['secret_key']) && strpos($mayaConfig['secret_key'], 'PASTE') === false) {
        $secret_key = $mayaConfig['secret_key'];
        $is_production = $mayaConfig['is_live'] ?? false;
        $url = ($mayaConfig['base_url'] ?? 'https://pg-sandbox.paymaya.com') . '/checkout/v1/checkouts';
    }
}

// Build checkout payload
$payload = [
    'totalAmount' => [
        'value' => number_format($grand_total, 2, '.', ''),
        'currency' => 'PHP'
    ],
    'buyer' => [
        'firstName' => $firstName,
        'lastName' => $lastName,
        'contact' => [
            'phone' => $customerPhone,
            'email' => $customerEmail
        ],
        'shippingAddress' => [
            'line1' => $customerAddress,
            'city' => 'Manila',
            'zipCode' => '1000',
            'countryCode' => 'PH'
        ]
    ],
    'items' => $line_items,
    'redirectUrl' => [
        'success' => ($_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . (dirname($_SERVER['SCRIPT_NAME']) === '/' ? '' : dirname($_SERVER['SCRIPT_NAME'])) . '/payment-success.php?ref=' . $orderRef,
        'failure' => ($_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . (dirname($_SERVER['SCRIPT_NAME']) === '/' ? '' : dirname($_SERVER['SCRIPT_NAME'])) . '/payment-failed.php?ref=' . $orderRef,
        'cancel' => ($_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . (dirname($_SERVER['SCRIPT_NAME']) === '/' ? '' : dirname($_SERVER['SCRIPT_NAME'])) . '/payment-cancelled.php?ref=' . $orderRef
    ],
    'requestReferenceNumber' => $orderRef,
    'metadata' => [
        'orderType' => $paymentType,
        'customerAddress' => $customerAddress
    ]
];

// Call Maya checkout API
$ch = curl_init();
$auth = base64_encode($secret_key . ':');

curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Basic ' . $auth
    ],
    CURLOPT_SSL_VERIFYPEER => true
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);
$conn->close();

if ($error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Connection error: ' . $error]);
    exit;
}

$responseData = json_decode($response, true);

if ($httpCode >= 200 && $httpCode < 300 && isset($responseData['checkoutId'])) {
    echo json_encode([
        'success' => true,
        'orderRef' => $orderRef,
        'checkoutId' => $responseData['checkoutId'],
        'paymentUrl' => $responseData['redirectUrl']
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $responseData['message'] ?? 'Failed to create Maya payment',
        'code' => $httpCode
    ]);
}
?>
