<?php
session_start();
header('Content-Type: application/json');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// ══════════════════════════════════════════════════════════════════════════════
//  MAYA API CONFIGURATION
//  Loads keys from maya.php (one folder above project).
//  If maya.php is missing, it will return a clear error instead of using
//  expired hardcoded keys.
// ══════════════════════════════════════════════════════════════════════════════
$mayaConfigPath = dirname(__DIR__) . '/maya.php';

if (!file_exists($mayaConfigPath)) {
    echo json_encode([
        'success' => false,
        'message' => 'Maya configuration file (maya.php) not found. '
                   . 'Please create it one folder above your project directory. '
                   . 'See the maya.php template file provided.',
    ]);
    exit;
}

$mayaConfig = include $mayaConfigPath;

$MAYA_PUBLIC_KEY = $mayaConfig['public_key'] ?? '';
$MAYA_SECRET_KEY = $mayaConfig['secret_key'] ?? '';
$MAYA_BASE_URL   = $mayaConfig['base_url']   ?? 'https://pg-sandbox.paymaya.com';

// Guard: catch placeholder / empty keys
$placeholders = [
    'pk-PASTE-YOUR-SANDBOX-PUBLIC-KEY-HERE',
    'sk-PASTE-YOUR-SANDBOX-SECRET-KEY-HERE',
    'pk-PASTE-YOUR-LIVE-PUBLIC-KEY-HERE',
    'sk-PASTE-YOUR-LIVE-SECRET-KEY-HERE',
    '',
];

if (in_array($MAYA_SECRET_KEY, $placeholders) || in_array($MAYA_PUBLIC_KEY, $placeholders)) {
    echo json_encode([
        'success' => false,
        'message' => 'Maya API keys are not set. '
                   . 'Please open maya.php and paste your real keys from https://developers.maya.ph',
    ]);
    exit;
}

// ── Delivery Fee Matrix ───────────────────────────────────────────────────────
$deliveryFeeMatrix = [
    'mm_1_5km'   => 2000,
    'mm_6_10km'  => 2500,
    'mm_11_20km' => 4000,
    'mm_21_30km' => 6000,
    'cavite'     => 4200,
    'laguna'     => 6000,
    'batangas'   => 8500,
    'rizal'      => 7000,
    'bulacan'    => 7000,
    'pampanga'   => 10000,
    'tarlac'     => 10000,
    'vismin'     => 0,
];

// MOQ-restricted categories
$moqCategories = [
    'panel', 'panels',
    'mounting & accessories', 'mounting and accessories',
    'mounting', 'accessories',
];

try {
    // ── Database ──────────────────────────────────────────────────────────────
    $conn = new mysqli('localhost', 'root', '', 'solar_power');
    if ($conn->connect_error) {
        throw new Exception('DB connection failed: ' . $conn->connect_error);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    // ── Read POST payload ──────────────────────────────────────────────────────
    $customerName    = trim($_POST['customerName']    ?? '');
    $customerEmail   = trim($_POST['customerEmail']   ?? '');
    $customerPhone   = trim($_POST['customerPhone']   ?? '');
    $customerAddress = trim($_POST['customerAddress'] ?? '');
    $province        = trim($_POST['province']        ?? '');
    $items           = json_decode($_POST['items']    ?? '[]', true);
    $paymentTermKey  = trim($_POST['paymentTerm']     ?? 'full');

    // ── Basic validation ───────────────────────────────────────────────────────
    if (!$customerName || !$customerEmail || !$customerPhone || !$customerAddress || empty($items)) {
        echo json_encode([
            'success' => false,
            'message' => 'All customer fields and at least one item are required.',
        ]);
        exit;
    }

    // ── Resolve delivery fee ───────────────────────────────────────────────────
    $provinceKey = strtolower(str_replace([' ', '-'], '_', $province));
    $provinceKey = preg_replace('/[^a-z0-9_]/', '_', $provinceKey);
    $provinceKey = preg_replace('/_+/', '_', $provinceKey);
    $deliveryFee = $deliveryFeeMatrix[$provinceKey] ?? 0;

    // ── MOQ server-side enforcement ────────────────────────────────────────────
    foreach ($items as $item) {
        $productId = intval($item['id'] ?? 0);
        $qty       = intval($item['quantity'] ?? 1);
        if ($productId > 0) {
            $pStmt = $conn->prepare(
                "SELECT category, COALESCE(moq, 1) AS moq FROM product WHERE id = ?"
            );
            $pStmt->bind_param("i", $productId);
            $pStmt->execute();
            $pRow = $pStmt->get_result()->fetch_assoc();
            $pStmt->close();
            if ($pRow) {
                $cat = strtolower($pRow['category'] ?? '');
                $moq = intval($pRow['moq'] ?? 1);
                if (in_array($cat, $moqCategories) && $qty < $moq) {
                    echo json_encode([
                        'success' => false,
                        'message' => "Minimum order quantity for this product is {$moq} pcs.",
                    ]);
                    exit;
                }
            }
        }
    }

    // ── Build itemized line items ──────────────────────────────────────────────
    $lineItems    = [];
    $itemSubtotal = 0;

    foreach ($items as $item) {
        $productId = intval($item['id'] ?? 0);
        $brandId   = intval($item['brand_id'] ?? 0);
        $qty       = intval($item['quantity'] ?? 1);
        $unitPrice = floatval($item['price'] ?? 0);
        $itemLabel = $item['displayName'] ?? ($item['name'] ?? 'Product');

        if ($productId > 0 && $brandId > 0) {
            $iStmt = $conn->prepare(
                "SELECT p.displayName, b.brand_name AS brandName, pbv.price
                 FROM product_brand_variants pbv
                 INNER JOIN product p ON pbv.product_id = p.id
                 INNER JOIN brands  b ON pbv.brand_id   = b.brand_id
                 WHERE pbv.product_id = ? AND pbv.brand_id = ?
                 LIMIT 1"
            );
            $iStmt->bind_param("ii", $productId, $brandId);
            $iStmt->execute();
            $iRow = $iStmt->get_result()->fetch_assoc();
            $iStmt->close();
            if ($iRow) {
                $itemLabel = $iRow['brandName'] . ' ' . $iRow['displayName'];
                $unitPrice = floatval($iRow['price']);
            }
        } elseif ($productId > 0) {
            $iStmt = $conn->prepare(
                "SELECT displayName FROM product WHERE id = ? LIMIT 1"
            );
            $iStmt->bind_param("i", $productId);
            $iStmt->execute();
            $iRow = $iStmt->get_result()->fetch_assoc();
            $iStmt->close();
            if ($iRow) {
                $itemLabel = $iRow['displayName'];
            }
        }

        $subtotal      = $unitPrice * $qty;
        $itemSubtotal += $subtotal;

        $lineItems[] = [
            'name'        => $itemLabel,
            'quantity'    => $qty,
            'code'        => 'PROD-' . $productId . ($brandId ? '-B' . $brandId : ''),
            'description' => $itemLabel . ' (Qty: ' . $qty . ')',
            'amount'      => [
                'value'    => number_format($unitPrice, 2, '.', ''),
                'currency' => 'PHP',
            ],
            'totalAmount' => [
                'value'    => number_format($subtotal, 2, '.', ''),
                'currency' => 'PHP',
            ],
        ];
    }

    // ── Delivery fee as a line item ────────────────────────────────────────────
    if ($deliveryFee > 0) {
        $lineItems[] = [
            'name'        => 'Delivery & Installation Fee (' . $province . ')',
            'quantity'    => 1,
            'code'        => 'DELIVERY-FEE',
            'description' => 'Delivery and installation fee for ' . $province,
            'amount'      => [
                'value'    => number_format($deliveryFee, 2, '.', ''),
                'currency' => 'PHP',
            ],
            'totalAmount' => [
                'value'    => number_format($deliveryFee, 2, '.', ''),
                'currency' => 'PHP',
            ],
        ];
    }

    // ── Calculate amount to charge (apply payment term) ───────────────────────
    $grandTotal = $itemSubtotal + $deliveryFee;
    switch ($paymentTermKey) {
        case 'downpayment':
            $amountToCharge = round(($itemSubtotal * 0.50) + $deliveryFee, 2);
            break;
        case 'initial':
            $amountToCharge = round(($itemSubtotal * 0.20) + $deliveryFee, 2);
            break;
        default: // 'full'
            $amountToCharge = $grandTotal;
            break;
    }

    // ── Build redirect base URL ────────────────────────────────────────────────
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $siteRoot = $protocol . '://' . $host . '/SolarPower-Energy-Corporation';

    $orderRef = 'SP-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));

    // ── Save pending order to DB BEFORE calling Maya ──────────────────────────
    $conn->begin_transaction();

    $stmt = $conn->prepare("
        INSERT INTO orders
        (order_reference, customer_name, customer_email, customer_phone,
         customer_address, total_amount, payment_method, payment_status, order_status)
        VALUES (?, ?, ?, ?, ?, ?, 'maya', 'pending', 'pending')
    ");
    $stmt->bind_param(
        "sssssd",
        $orderRef, $customerName, $customerEmail,
        $customerPhone, $customerAddress, $grandTotal
    );
    if (!$stmt->execute()) {
        throw new Exception('Failed to create order record.');
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
        $pid   = intval($item['id'] ?? 0);
        $pName = $item['displayName'] ?? ($item['name'] ?? 'Product');
        $pQty  = intval($item['quantity'] ?? 1);
        $pPrc  = floatval($item['price'] ?? 0);
        $pSub  = $pPrc * $pQty;
        $itemStmt->bind_param("iisidd", $orderId, $pid, $pName, $pQty, $pPrc, $pSub);
        $itemStmt->execute();
    }
    $itemStmt->close();
    $conn->commit();
    $conn->close();

    // ── Build Maya Checkout payload ────────────────────────────────────────────
    $nameParts = explode(' ', $customerName, 2);
    $firstName = $nameParts[0];
    $lastName  = $nameParts[1] ?? '-';

    $checkoutPayload = [
        'totalAmount' => [
            'value'    => number_format($amountToCharge, 2, '.', ''),
            'currency' => 'PHP',
            'details'  => [
                'discount'      => 0,
                'serviceCharge' => 0,
                'shippingFee'   => number_format($deliveryFee, 2, '.', ''),
                'tax'           => 0,
                'subtotal'      => number_format($itemSubtotal, 2, '.', ''),
            ],
        ],
        'buyer' => [
            'firstName' => $firstName,
            'lastName'  => $lastName,
            'contact'   => [
                'phone' => $customerPhone,
                'email' => $customerEmail,
            ],
            'shippingAddress' => [
                'firstName'   => $firstName,
                'lastName'    => $lastName,
                'phone'       => $customerPhone,
                'email'       => $customerEmail,
                'line1'       => $customerAddress,
                'city'        => $province,
                'state'       => $province,
                'zipCode'     => '0000',
                'countryCode' => 'PH',
            ],
            'billingAddress' => [
                'line1'       => $customerAddress,
                'city'        => $province,
                'state'       => $province,
                'zipCode'     => '0000',
                'countryCode' => 'PH',
            ],
        ],
        'items'       => $lineItems,
        'redirectUrl' => [
            'success' => $siteRoot . '/payment-success.php?ref=' . $orderRef,
            'failure' => $siteRoot . '/payment-failed.php?ref=' . $orderRef,
            'cancel'  => $siteRoot . '/product-details.php?cancelled=1',
        ],
        'requestReferenceNumber' => $orderRef,
        'metadata' => [
            'orderId'     => $orderId,
            'orderRef'    => $orderRef,
            'paymentTerm' => $paymentTermKey,
        ],
    ];

    // ── Call Maya Checkout API ─────────────────────────────────────────────────
    $ch = curl_init($MAYA_BASE_URL . '/checkout/v1/checkouts');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($MAYA_SECRET_KEY . ':'),
        ],
        CURLOPT_POSTFIELDS     => json_encode($checkoutPayload),
        CURLOPT_SSL_VERIFYPEER => true,  // always true in production
        CURLOPT_TIMEOUT        => 30,
    ]);

    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlErr) {
        throw new Exception('cURL error: ' . $curlErr);
    }

    $result = json_decode($response, true);

    if (isset($result['redirectUrl'])) {
        echo json_encode([
            'success'     => true,
            'checkoutUrl' => $result['redirectUrl'],
            'orderRef'    => $orderRef,
        ]);
    } else {
        // Return a clear error from Maya
        $mayaMessage = $result['message'] ?? ($result['error'] ?? 'Maya checkout creation failed.');
        echo json_encode([
            'success' => false,
            'message' => $mayaMessage . ' | Details: ' . json_encode($result),
        ]);
    }

} catch (Throwable $e) {
    if (isset($conn) && $conn) {
        try { $conn->rollback(); } catch (Throwable $rb) {}
        try { $conn->close();    } catch (Throwable $cl) {}
    }
    echo json_encode([
        'success' => false,
        'message' => 'Checkout error: ' . $e->getMessage(),
    ]);
}
?>
