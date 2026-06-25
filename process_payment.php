// 1. ILAGAY ANG KEY DITO: 
// Kung pang-test: gamitin ang sandbox key (sk-...) at siguraduhing ang $is_production ay false.
// Kung totoong pera na: gamitin ang totoong live key mula kay boss at gawing true ang $is_production.
$secret_key = "sk-dDHeLd2o6TV52ZXTpRrIBk7ZgLOWf5uLqjdpaAwdRVS"; 
$is_production = false; // Gawing true kung live key na ang gamit mo

// 2. Automated Endpoint Router
$url = $is_production 
    ? "https://pg.paymaya.com/checkout/v1/checkouts" 
    : "https://pg-sandbox.paymaya.com/checkout/v1/checkouts";

// 3. Perfect Base64 Header Compilation with the mandatory trailing colon
$base64_secret = base64_encode($secret_key . ":");
$headers = [
    "Content-Type: application/json",
    "Authorization: Basic " . $base64_secret
];

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

// Enforce MOQ validations
foreach ($input['items'] as $item) {
    $productId = intval($item['id'] ?? 0);
    $qty = intval($item['quantity'] ?? 1);
    if ($productId > 0) {
        $pStmt = $conn->prepare("SELECT category, COALESCE(moq, 1) as moq FROM product WHERE id = ?");
        $pStmt->bind_param("i", $productId);
        $pStmt->execute();
        $pRes = $pStmt->get_result()->fetch_assoc();
        $pStmt->close();
        if ($pRes) {
            $category = strtolower($pRes['category'] ?? '');
            $moq = intval($pRes['moq'] ?? 1);
            if (in_array($category, ['panel', 'panels', 'mounting & accessories', 'mounting and accessories', 'mounting', 'accessories'])) {
                if ($qty < $moq) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => "Error: Minimum purchased order quantity for this product category is {$moq} pcs."]);
                    exit;
                }
            }
        }
    }
}

// Generate unique order reference
$orderRef = 'ORD-' . date('YmdHis') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));

// Split customer name
$nameParts = explode(' ', trim($input['customerName']), 2);
$firstName = $nameParts[0];
$lastName = $nameParts[1] ?? '';

// Parse address for city and province
$addressParts = array_map('trim', explode(',', $input['customerAddress']));
$count = count($addressParts);
$provinceText = $addressParts[$count - 1] ?? 'Metro Manila';
$cityText = $addressParts[$count - 2] ?? 'Manila';
$line1Text = implode(', ', array_slice($addressParts, 0, max(1, $count - 2)));

$clean_province = preg_replace('/[\r\n\t\\\\]+/', ' ', trim($input['province'] ?? $provinceText));
$clean_city     = preg_replace('/[\r\n\t\\\\]+/', ' ', trim($input['city'] ?? $cityText));
$clean_line1    = preg_replace('/[\r\n\t\\\\]+/', ' ', trim($input['line1'] ?? $line1Text));

$zipCode = '1000';

// Prepare items for Maya API
$mayaItems = [];
foreach ($input['items'] as $item) {
    $productId = isset($item['id']) ? intval($item['id']) : 0;
    $brandId = isset($item['brand_id']) ? intval($item['brand_id']) : 0;
    $itemName = $item['name'];
    $itemPrice = floatval($item['price']);

    if ($productId > 0 && $brandId > 0) {
        $stmt_pbv = $conn->prepare("SELECT p.displayName, b.brand_name AS brandName, pbv.price 
                                    FROM product_brand_variants pbv
                                    INNER JOIN product p ON pbv.product_id = p.id
                                    INNER JOIN brands b ON pbv.brand_id = b.brand_id
                                    WHERE pbv.product_id = ? AND pbv.brand_id = ?");
        if ($stmt_pbv) {
            $stmt_pbv->bind_param("ii", $productId, $brandId);
            $stmt_pbv->execute();
            $res_pbv = $stmt_pbv->get_result();
            if ($row = $res_pbv->fetch_assoc()) {
                $itemName = $row['brandName'] . " " . $row['displayName'];
                $itemPrice = floatval($row['price']);
            }
            $stmt_pbv->close();
        }
    }

    $mayaItems[] = [
        'name' => $itemName,
        'quantity' => intval($item['quantity']),
        'amount' => [
            'value' => $itemPrice
        ],
        'totalAmount' => [
            'value' => $itemPrice * intval($item['quantity'])
        ]
    ];
}

$protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host      = $_SERVER['HTTP_HOST'] ?? 'localhost';
$siteRoot  = $protocol . '://' . $host;
if ($host === 'localhost' || strpos($host, '127.0.0.1') !== false) {
    $siteRoot .= '/SolarPower-Energy-Corporation';
}

// Prepare Maya checkout payload
$payload = [
    'totalAmount' => [
        'value' => number_format(floatval($input['amountToPay']), 2, '.', ''),
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
            'line1' => $clean_line1,
            'city' => $clean_city,
            'province' => $clean_province,
            'zipCode' => $zipCode,
            'countryCode' => 'PH'
        ]
    ],
    'items' => $mayaItems,
    'redirectUrl' => [
        'success' => $siteRoot . '/payment-success.php?ref=' . $orderRef,
        'failure' => $siteRoot . '/payment-failed.php?ref=' . $orderRef,
        'cancel' => $siteRoot . '/payment-cancelled.php?ref=' . $orderRef
    ],
    'requestReferenceNumber' => $orderRef,
    'metadata' => [
        'orderType' => $input['paymentType'],
        'customerAddress' => $input['customerAddress']
    ]
];

// Make API request to Maya
$ch = curl_init();
$auth = base64_encode($secret_key . ':');

$headers = [
    'Content-Type: application/json',
    'Authorization: Basic ' . $auth
];

curl_setopt_array($ch, [
    CURLOPT_URL => $url,
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