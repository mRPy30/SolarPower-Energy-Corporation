<?php
/**
 * MAYA CHECKOUT - EXCLUSIVE PAYMENT PROCESSOR
 * 
 * This handler processes all checkout requests through Maya Payment Gateway ONLY.
 * Dynamic payload building from cart with product brand variants support.
 */

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// ============================================
// MAYA API CONFIGURATION
// ============================================
class MayaConfig {
    const PUBLIC_KEY = 'pk-jfJcy6j57jBSKH5ZcOSqZ16uaRa0z1lFqH18mlFk3IV';
    const SECRET_KEY = 'sk-VhThe0VYWe92H7y42goqAyNInbJhHeA0DXzZLzHRCAn';
    const API_BASE_URL = 'https://pg-sandbox.paymaya.com';
    const CREATE_CHECKOUT_URL = '/checkout/v1/checkouts';
    const SUCCESS_URL = 'https://solarpower.com.ph/payment-success.php';
    const FAILURE_URL = 'https://solarpower.com.ph/payment-failed.php';
    const CANCEL_URL = 'https://solarpower.com.ph/payment-cancelled.php';
}

// ============================================
// DATABASE CONNECTION
// ============================================
require_once 'config/dbconn.php';

// ============================================
// VALIDATION & SECURITY
// ============================================

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'error' => 'Method not allowed']));
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Invalid JSON payload']));
}

// ============================================
// TEXT SANITIZATION FUNCTION
// ============================================
function sanitizeText($text) {
    $text = stripslashes($text);
    $text = preg_replace("/[\r\n\\\\]+/", " ", $text);
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);
    return $text;
}

// ============================================
// VALIDATE REQUIRED FIELDS
// ============================================
$required = ['customerName', 'customerEmail', 'customerPhone', 'customerAddress', 'totalAmount', 'items'];
foreach ($required as $field) {
    if (!isset($input[$field])) {
        http_response_code(400);
        die(json_encode(['success' => false, 'error' => "Missing required field: $field"]));
    }
}

// Sanitize input
$customerName = sanitizeText($input['customerName']);
$customerEmail = sanitizeText($input['customerEmail']);
$customerPhone = sanitizeText($input['customerPhone']);
$customerAddress = sanitizeText($input['customerAddress']);
$totalAmount = floatval($input['totalAmount'] ?? 0);

// Validate totals
if ($totalAmount <= 0) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Invalid total amount']));
}

// Validate items array
$items = $input['items'] ?? [];
if (!is_array($items) || count($items) === 0) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Cart is empty']));
}

// ============================================
// MOQ & PRODUCT VALIDATION
// ============================================
foreach ($items as $item) {
    $productId = intval($item['id'] ?? 0);
    $qty = intval($item['quantity'] ?? 1);
    
    if ($productId > 0) {
        $stmt = $conn->prepare("SELECT category, COALESCE(moq, 1) as moq FROM product WHERE id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($result) {
            $category = strtolower($result['category'] ?? '');
            $moq = intval($result['moq'] ?? 1);
            
            $restrictedCategories = ['panel', 'panels', 'mounting & accessories', 'mounting and accessories', 'mounting', 'accessories'];
            if (in_array($category, $restrictedCategories) && $qty < $moq) {
                http_response_code(400);
                die(json_encode(['success' => false, 'error' => "Minimum order quantity for this category is {$moq} pcs."]));
            }
        }
    }
}

// ============================================
// BUILD MAYA LINE ITEMS FROM CART
// ============================================
$mayaItems = [];
$lineItemTotal = 0;

foreach ($items as $item) {
    $productId = intval($item['id'] ?? 0);
    $brandId = intval($item['brand_id'] ?? 0);
    $quantity = intval($item['quantity'] ?? 1);
    
    $itemName = sanitizeText($item['name'] ?? 'Solar Product');
    $itemPrice = floatval($item['price'] ?? 0);
    
    // Query product_brand_variants if both product and brand IDs exist
    if ($productId > 0 && $brandId > 0) {
        $stmt = $conn->prepare("
            SELECT p.displayName, b.brandName, pbv.price 
            FROM product_brand_variants pbv
            INNER JOIN product p ON pbv.product_id = p.id
            INNER JOIN brands b ON pbv.brand_id = b.id
            WHERE pbv.product_id = ? AND pbv.brand_id = ?
            LIMIT 1
        ");
        
        if ($stmt) {
            $stmt->bind_param("ii", $productId, $brandId);
            $stmt->execute();
            $variantRow = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if ($variantRow) {
                $displayName = sanitizeText($variantRow['displayName'] ?? '');
                $brandName = sanitizeText($variantRow['brandName'] ?? '');
                $itemName = $displayName . ' - ' . $brandName;
                $itemPrice = floatval($variantRow['price'] ?? $itemPrice);
            }
        }
    }
    
    // Validate item price
    if ($itemPrice <= 0) {
        http_response_code(400);
        die(json_encode(['success' => false, 'error' => 'Invalid item price: ' . $itemName]));
    }
    
    $itemSubtotal = $itemPrice * $quantity;
    $lineItemTotal += $itemSubtotal;
    
    // Build Maya line item
    $mayaItems[] = [
        'name' => $itemName,
        'quantity' => $quantity,
        'amount' => [
            'value' => round($itemPrice, 2),
            'currency' => 'PHP'
        ],
        'totalAmount' => [
            'value' => round($itemSubtotal, 2),
            'currency' => 'PHP'
        ]
    ];
}

// ============================================
// GENERATE ORDER REFERENCE
// ============================================
$orderRef = 'ORD-' . date('YmdHis') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));

// ============================================
// PARSE CUSTOMER DETAILS
// ============================================
$nameParts = array_filter(explode(' ', trim($customerName), 2));
$firstName = isset($nameParts[0]) ? sanitizeText($nameParts[0]) : 'Customer';
$lastName = isset($nameParts[1]) ? sanitizeText($nameParts[1]) : '';

// Extract city from address
$addressParts = array_map('trim', explode(',', $customerAddress));
$city = isset($addressParts[count($addressParts) - 2]) ? sanitizeText($addressParts[count($addressParts) - 2]) : 'Manila';
$zipCode = '1000';

// ============================================
// BUILD MAYA CHECKOUT PAYLOAD
// ============================================
$payload = [
    'totalAmount' => [
        'value' => round($totalAmount, 2),
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
            'city' => $city,
            'zipCode' => $zipCode,
            'countryCode' => 'PH'
        ]
    ],
    'items' => $mayaItems,
    'redirectUrl' => [
        'success' => MayaConfig::SUCCESS_URL . '?ref=' . urlencode($orderRef),
        'failure' => MayaConfig::FAILURE_URL . '?ref=' . urlencode($orderRef),
        'cancel' => MayaConfig::CANCEL_URL . '?ref=' . urlencode($orderRef)
    ],
    'requestReferenceNumber' => $orderRef,
    'metadata' => [
        'orderType' => 'full_payment',
        'customerEmail' => $customerEmail,
        'paymentMethod' => 'maya_checkout'
    ]
];

// ============================================
// CALL MAYA API
// ============================================
$ch = curl_init();

// Build Authorization header with Base64 encoding
$secretKey = MayaConfig::SECRET_KEY;
$authHeader = 'Authorization: Basic ' . base64_encode($secretKey . ':');

$headers = [
    'Content-Type: application/json',
    $authHeader
];

curl_setopt_array($ch, [
    CURLOPT_URL => MayaConfig::API_BASE_URL . MayaConfig::CREATE_CHECKOUT_URL,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    http_response_code(500);
    die(json_encode(['success' => false, 'error' => 'cURL error: ' . $curlError]));
}

$responseData = json_decode($response, true);

// ============================================
// HANDLE MAYA API RESPONSE
// ============================================
if ($httpCode >= 200 && $httpCode < 300 && isset($responseData['checkoutId'])) {
    // SUCCESS: Save order to database
    try {
        // Insert order
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
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $paymentMethod = 'maya_checkout';
        $paymentStatus = 'pending';
        $orderStatus = 'pending';
        
        $stmt->bind_param(
            'sssssdsss',
            $orderRef,
            $customerName,
            $customerEmail,
            $customerPhone,
            $customerAddress,
            $totalAmount,
            $paymentMethod,
            $paymentStatus,
            $orderStatus
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Order insert failed: ' . $stmt->error);
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
            throw new Exception('Prepare failed for order_items: ' . $conn->error);
        }
        
        foreach ($items as $item) {
            $pId = intval($item['id'] ?? 0);
            $pName = sanitizeText($item['name'] ?? 'Product');
            $pQty = intval($item['quantity'] ?? 1);
            $pPrice = floatval($item['price'] ?? 0);
            $pSubtotal = $pPrice * $pQty;
            
            $stmt->bind_param(
                'iisdd',
                $orderId,
                $pId,
                $pName,
                $pQty,
                $pPrice,
                $pSubtotal
            );
            
            if (!$stmt->execute()) {
                throw new Exception('Item insert failed: ' . $stmt->error);
            }
        }
        
        $stmt->close();
        $conn->close();
        
        // Return success response
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Maya checkout created successfully',
            'orderRef' => $orderRef,
            'checkoutId' => $responseData['checkoutId'],
            'paymentUrl' => $responseData['redirectUrl']
        ]);
        exit;
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage()
        ]);
        $conn->close();
        exit;
    }
    
} else {
    // FAILURE: Maya API error
    http_response_code($httpCode);
    echo json_encode([
        'success' => false,
        'error' => $responseData['message'] ?? 'Failed to create Maya checkout',
        'code' => $httpCode,
        'details' => $responseData
    ]);
    $conn->close();
    exit;
}

?>
