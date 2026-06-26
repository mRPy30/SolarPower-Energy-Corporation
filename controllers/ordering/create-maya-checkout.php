<?php
/**
 * ============================================================
 *  MAYA CHECKOUT HANDLER  —  create-maya-checkout.php
 * ============================================================
 *
 *  ARCHITECTURE
 *  ─────────────────────────────────────────────────────────
 *  1. Validate all POST fields and session cart.
 *  2. Enforce MOQ rules (server-side).
 *  3. DB STEP A — Insert a full order row (status = pending).
 *  4. DB STEP B — Insert every cart item with exact product /
 *                 brand-variant name, qty, unit price, subtotal.
 *                 (Staff Dashboard sees 100 % itemized detail.)
 *  5. Build a FLAT 2-row Maya payload to avoid JSON-parse
 *     errors caused by long or special-character product names:
 *       Row 1  →  "Solar Equipment Order Summary (Ref: #<id>)"
 *       Row 2  →  "Shipping & Delivery Fee (<province>)"
 *  6. POST to Maya Checkout API.
 *  7. Return { success, checkoutUrl, orderRef } on success.
 *
 *  KEY FILE LOCATION
 *  ─────────────────────────────────────────────────────────
 *  Place maya.php ONE folder ABOVE the project root:
 *    /htdocs/maya.php                          ← config
 *    /htdocs/SolarPower-Energy-Corporation/    ← project
 * ============================================================
 */

session_start();
header('Content-Type: application/json');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// ──────────────────────────────────────────────────────────────────────────────
//  MAYA CONFIG  (loaded from maya.php one level above project root)
// ──────────────────────────────────────────────────────────────────────────────
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

$mayaConfig      = include $mayaConfigPath;
$MAYA_PUBLIC_KEY = $mayaConfig['public_key'] ?? '';
$MAYA_SECRET_KEY = $mayaConfig['secret_key'] ?? '';
$MAYA_BASE_URL   = $mayaConfig['base_url']   ?? 'https://pg-sandbox.paymaya.com';

// Guard against placeholder / empty keys
$placeholderKeys = [
    'pk-rpwb5YR6EfnKiMsldZqY4hgpvJjuy8hhxW2bVAAiz2N',
    'sk-6s9dwnYGFJdZOYu1HCUAfUZctWEf9AjtHIG38kezX8W',
    '',
];
if (in_array($MAYA_SECRET_KEY, $placeholderKeys) || in_array($MAYA_PUBLIC_KEY, $placeholderKeys)) {
    echo json_encode([
        'success' => false,
        'message' => 'Maya API keys are not configured. '
                   . 'Please open maya.php and paste your real keys from https://developers.maya.ph',
    ]);
    exit;
}

// ──────────────────────────────────────────────────────────────────────────────
//  DELIVERY FEE MATRIX  (PHP values, keyed by normalized province slug)
// ──────────────────────────────────────────────────────────────────────────────
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

// MOQ-restricted product categories
$moqCategories = [
    'panel', 'panels',
    'mounting & accessories', 'mounting and accessories',
    'mounting', 'accessories',
];

// ──────────────────────────────────────────────────────────────────────────────
//  HELPER — strip control chars / extra whitespace for safe DB & payload text
// ──────────────────────────────────────────────────────────────────────────────
function cleanText(string $text): string {
    $text = stripslashes($text);
    $text = preg_replace("/[\r\n\t\\\\]+/", ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    return trim(strip_tags($text));
}

// ──────────────────────────────────────────────────────────────────────────────
//  MAIN LOGIC
// ──────────────────────────────────────────────────────────────────────────────
try {

    // ── Database connection ───────────────────────────────────────────────────
    $conn = new mysqli('localhost', 'root', '', 'solar_power');
    if ($conn->connect_error) {
        throw new Exception('DB connection failed: ' . $conn->connect_error);
    }

    // ── Method guard ──────────────────────────────────────────────────────────
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
        exit;
    }

    // ── Read & sanitize POST fields ───────────────────────────────────────────
    $customerName    = cleanText($_POST['customerName']    ?? '');
    $customerEmail   = cleanText($_POST['customerEmail']   ?? '');
    $customerPhone   = cleanText($_POST['customerPhone']   ?? '');
    $customerAddress = cleanText($_POST['customerAddress'] ?? '');
    $province        = cleanText($_POST['province']        ?? '');
    $paymentTermKey  = cleanText($_POST['paymentTerm']     ?? 'full');

    // Items arrive as a JSON string from the frontend hidden field
    $items = json_decode($_POST['items'] ?? '[]', true);
    if (!is_array($items)) {
        $items = [];
    }

    // Also accept items passed as the hidden summary fields (fallback)
    $frontendSubtotal  = floatval($_POST['total_items_amount']       ?? 0);
    $frontendDelivery  = floatval($_POST['calculated_delivery_fee']  ?? 0);
    $frontendLocation  = cleanText($_POST['selected_location_name']  ?? $province);

    // ── Basic validation ──────────────────────────────────────────────────────
    if (!$customerName || !$customerEmail || !$customerPhone || !$customerAddress) {
        echo json_encode([
            'success' => false,
            'message' => 'All customer fields (name, email, phone, address) are required.',
        ]);
        exit;
    }

    // Use session cart if items array is empty (backward compatibility)
    if (empty($items) && !empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $variantId => $cartItem) {
            $items[] = [
                'id'       => $variantId,
                'name'     => $cartItem['name']     ?? 'Product',
                'price'    => $cartItem['price']    ?? 0,
                'quantity' => $cartItem['quantity'] ?? 1,
            ];
        }
    }

    if (empty($items)) {
        echo json_encode(['success' => false, 'message' => 'Cart is empty. Please add products before checking out.']);
        exit;
    }

    // ── Resolve delivery fee ──────────────────────────────────────────────────
    // Priority: (1) hidden field from frontend, (2) province matrix, (3) zero
    $deliveryFee = 0.00;

    if ($frontendDelivery > 0) {
        // Trust the pre-calculated value already displayed to the customer
        $deliveryFee     = $frontendDelivery;
        $locationDisplay = $frontendLocation ?: $province;
    } else {
        $slug        = strtolower(str_replace([' ', '-'], '_', $province));
        $slug        = preg_replace('/[^a-z0-9_]/', '_', $slug);
        $slug        = preg_replace('/_+/', '_', $slug);
        $deliveryFee     = floatval($deliveryFeeMatrix[$slug] ?? 0);
        $locationDisplay = $province;
    }

    // ── MOQ server-side enforcement ───────────────────────────────────────────
    foreach ($items as $item) {
        $productId = intval($item['id'] ?? 0);
        $qty       = intval($item['quantity'] ?? 1);

        if ($productId > 0) {
            $pStmt = $conn->prepare(
                "SELECT category, COALESCE(moq, 1) AS moq FROM product WHERE id = ? LIMIT 1"
            );
            $pStmt->bind_param('i', $productId);
            $pStmt->execute();
            $pRow = $pStmt->get_result()->fetch_assoc();
            $pStmt->close();

            if ($pRow) {
                $cat = strtolower($pRow['category'] ?? '');
                $moq = intval($pRow['moq'] ?? 1);
                if (in_array($cat, $moqCategories) && $qty < $moq) {
                    echo json_encode([
                        'success' => false,
                        'message' => "Minimum order quantity for this product category is {$moq} pcs.",
                    ]);
                    exit;
                }
            }
        }
    }

    // ── Build itemized data for DB (full detail) ──────────────────────────────
    // We enrich each item with the authoritative name and price from the DB
    // so the Staff Dashboard gets 100 % accurate product data.
    $dbItems      = [];   // enriched items for order_items table
    $itemSubtotal = 0.00;

    foreach ($items as $item) {
        $productId = intval($item['id']       ?? 0);
        $brandId   = intval($item['brand_id'] ?? 0);
        $qty       = intval($item['quantity'] ?? 1);
        $unitPrice = floatval($item['price']  ?? 0);
        $itemLabel = cleanText($item['displayName'] ?? ($item['name'] ?? 'Solar Product'));

        // Attempt to fetch authoritative name + price from product_brand_variants
        if ($productId > 0 && $brandId > 0) {
            $vStmt = $conn->prepare(
                "SELECT p.displayName, b.brand_name AS brandName, pbv.price
                 FROM   product_brand_variants pbv
                 INNER JOIN product p ON pbv.product_id = p.id
                 INNER JOIN brands  b ON pbv.brand_id   = b.brand_id
                 WHERE  pbv.product_id = ? AND pbv.brand_id = ?
                 LIMIT  1"
            );
            $vStmt->bind_param('ii', $productId, $brandId);
            $vStmt->execute();
            $vRow = $vStmt->get_result()->fetch_assoc();
            $vStmt->close();

            if ($vRow) {
                $itemLabel = cleanText($vRow['brandName'] . ' ' . $vRow['displayName']);
                $unitPrice = floatval($vRow['price']);
            }
        } elseif ($productId > 0) {
            // No brand variant — fetch display name from product table
            $nStmt = $conn->prepare(
                "SELECT displayName FROM product WHERE id = ? LIMIT 1"
            );
            $nStmt->bind_param('i', $productId);
            $nStmt->execute();
            $nRow = $nStmt->get_result()->fetch_assoc();
            $nStmt->close();
            if ($nRow) {
                $itemLabel = cleanText($nRow['displayName']);
            }
        }

        $lineSubtotal  = $unitPrice * $qty;
        $itemSubtotal += $lineSubtotal;

        $dbItems[] = [
            'product_id'   => $productId,
            'product_name' => $itemLabel,
            'quantity'     => $qty,
            'price'        => $unitPrice,
            'subtotal'     => $lineSubtotal,
        ];
    }

    // ── Grand total ───────────────────────────────────────────────────────────
    // Use the frontend subtotal when provided (already shown to customer),
    // otherwise trust the DB-computed subtotal.
    $confirmedSubtotal = ($frontendSubtotal > 0) ? $frontendSubtotal : $itemSubtotal;
    $grandTotal        = round($confirmedSubtotal + $deliveryFee, 2);

    // ── Apply payment term multiplier ─────────────────────────────────────────
    switch ($paymentTermKey) {
        case 'downpayment':
            $amountToCharge = round(($confirmedSubtotal * 0.50) + $deliveryFee, 2);
            break;
        case 'initial':
            $amountToCharge = round(($confirmedSubtotal * 0.20) + $deliveryFee, 2);
            break;
        default: // 'full'
            $amountToCharge = $grandTotal;
            break;
    }

    // ── Generate order reference ──────────────────────────────────────────────
    $orderRef = 'SP-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));

    // ══════════════════════════════════════════════════════════════════════════
    //  DB STEP A — Insert order row (status: pending)
    //  Save NOW, before calling Maya, so no order is ever lost.
    // ══════════════════════════════════════════════════════════════════════════
    $conn->begin_transaction();

    $oStmt = $conn->prepare("
        INSERT INTO orders
            (order_reference, customer_name, customer_email, customer_phone,
             customer_address, total_amount, payment_method, payment_status, order_status)
        VALUES (?, ?, ?, ?, ?, ?, 'maya', 'pending', 'pending')
    ");
    $oStmt->bind_param(
        'sssssd',
        $orderRef, $customerName, $customerEmail,
        $customerPhone, $customerAddress, $grandTotal
    );
    if (!$oStmt->execute()) {
        throw new Exception('Failed to insert order row: ' . $oStmt->error);
    }
    $orderId = $conn->insert_id;   // ← used in the Maya payload label below
    $oStmt->close();

    // ══════════════════════════════════════════════════════════════════════════
    //  DB STEP B — Insert every cart item with full product detail
    //  Staff Dashboard queries: SELECT * FROM order_items WHERE order_id = ?
    // ══════════════════════════════════════════════════════════════════════════
    $iStmt = $conn->prepare("
        INSERT INTO order_items
            (order_id, product_id, product_name, quantity, price, subtotal)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    foreach ($dbItems as $di) {
        $iStmt->bind_param(
            'iisidd',
            $orderId,
            $di['product_id'],
            $di['product_name'],
            $di['quantity'],
            $di['price'],
            $di['subtotal']
        );
        if (!$iStmt->execute()) {
            throw new Exception('Failed to insert order item: ' . $iStmt->error);
        }
    }
    $iStmt->close();

    $conn->commit();
    // DB work done — connection kept open only until after the Maya call
    // so we can update the order status if Maya rejects the request.

    // ══════════════════════════════════════════════════════════════════════════
    //  MAYA PAYLOAD — FLAT 2-ROW FORMAT
    //
    //  Sending all product names directly to Maya can trigger JSON parse errors
    //  when names contain special characters, ampersands, long strings, etc.
    //  Instead we send two clean summary rows and link them to the internal
    //  order via the DB order ID so Maya's own receipt is still meaningful.
    // ══════════════════════════════════════════════════════════════════════════
    $mayaLineItems = [
        [
            'name'        => 'Solar Equipment Order Summary (Ref: #' . $orderId . ')',
            'quantity'    => 1,
            'code'        => 'ORDER-' . $orderId,
            'description' => 'Full itemized breakdown available on your order confirmation.',
            'amount'      => [
                'value'    => number_format($confirmedSubtotal, 2, '.', ''),
                'currency' => 'PHP',
            ],
            'totalAmount' => [
                'value'    => number_format($confirmedSubtotal, 2, '.', ''),
                'currency' => 'PHP',
            ],
        ],
    ];

    // Only add the delivery row when a fee applies (avoid ₱0.00 rows)
    if ($deliveryFee > 0) {
        $mayaLineItems[] = [
            'name'        => 'Shipping & Delivery Fee (' . $locationDisplay . ')',
            'quantity'    => 1,
            'code'        => 'DELIVERY-FEE',
            'description' => 'Delivery and logistics to ' . $locationDisplay,
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

    // ── Redirect URLs ─────────────────────────────────────────────────────────
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $siteRoot = $protocol . '://' . $host . '/SolarPower-Energy-Corporation';

    // ── Split customer name ───────────────────────────────────────────────────
    $nameParts = explode(' ', $customerName, 2);
    $firstName = $nameParts[0];
    $lastName  = $nameParts[1] ?? '-';

    // ── Full Maya checkout payload ────────────────────────────────────────────
    $checkoutPayload = [
        'totalAmount' => [
            'value'    => number_format($amountToCharge, 2, '.', ''),
            'currency' => 'PHP',
            'details'  => [
                'discount'      => '0.00',
                'serviceCharge' => '0.00',
                'shippingFee'   => number_format($deliveryFee, 2, '.', ''),
                'tax'           => '0.00',
                'subtotal'      => number_format($confirmedSubtotal, 2, '.', ''),
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
                'city'        => $locationDisplay,
                'state'       => $locationDisplay,
                'zipCode'     => '0000',
                'countryCode' => 'PH',
            ],
            'billingAddress' => [
                'line1'       => $customerAddress,
                'city'        => $locationDisplay,
                'state'       => $locationDisplay,
                'zipCode'     => '0000',
                'countryCode' => 'PH',
            ],
        ],
        'items'       => $mayaLineItems,
        'redirectUrl' => [
            'success' => $siteRoot . '/payment-success.php?ref=' . $orderRef,
            'failure' => $siteRoot . '/payment-failed.php?ref='  . $orderRef,
            'cancel'  => $siteRoot . '/product-details.php?cancelled=1',
        ],
        'requestReferenceNumber' => $orderRef,
        'metadata' => [
            'orderId'          => $orderId,
            'orderRef'         => $orderRef,
            'paymentTerm'      => $paymentTermKey,
            'itemsSubtotal'    => number_format($confirmedSubtotal, 2, '.', ''),
            'deliveryFee'      => number_format($deliveryFee, 2, '.', ''),
            'deliveryLocation' => $locationDisplay,
        ],
    ];

    // ── POST to Maya Checkout API ─────────────────────────────────────────────
    $ch = curl_init($MAYA_BASE_URL . '/checkout/v1/checkouts');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($MAYA_SECRET_KEY . ':'),
        ],
        CURLOPT_POSTFIELDS     => json_encode($checkoutPayload),
        CURLOPT_SSL_VERIFYPEER => true,
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

    // ── Handle Maya response ──────────────────────────────────────────────────
    if (isset($result['redirectUrl'])) {
        // SUCCESS — clear the session cart
        unset($_SESSION['cart']);
        unset($_SESSION['delivery_fee']);

        $conn->close();

        echo json_encode([
            'success'     => true,
            'checkoutUrl' => $result['redirectUrl'],
            'orderRef'    => $orderRef,
        ]);

    } else {
        // FAILURE — Maya rejected the request.
        // Mark the pending order so staff can see it was not paid.
        $failStmt = $conn->prepare(
            "UPDATE orders SET payment_status = 'maya_error', order_status = 'cancelled' WHERE id = ?"
        );
        $failStmt->bind_param('i', $orderId);
        $failStmt->execute();
        $failStmt->close();
        $conn->close();

        $mayaMessage = $result['message'] ?? ($result['error'] ?? 'Maya checkout creation failed.');

        echo json_encode([
            'success' => false,
            'message' => $mayaMessage,
            'details' => $result,
        ]);
    }

} catch (Throwable $e) {
    if (isset($conn) && $conn instanceof mysqli) {
        try { $conn->rollback(); } catch (Throwable $rb) {}
        try { $conn->close();    } catch (Throwable $cl) {}
    }
    echo json_encode([
        'success' => false,
        'message' => 'Checkout error: ' . $e->getMessage(),
    ]);
}
?>
