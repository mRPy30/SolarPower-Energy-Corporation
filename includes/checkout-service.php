<?php
/**
 * Shared checkout service for product validation, order persistence, and Maya Checkout.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkout_clean_text($value): string
{
    $text = trim((string) $value);
    $text = preg_replace('/[\r\n\t]+/', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    return trim(strip_tags($text));
}

function checkout_json_input(): array
{
    $input = json_decode(file_get_contents('php://input'), true);
    if (is_array($input)) {
        return $input;
    }

    return $_POST ?: [];
}

function checkout_table_columns(mysqli $conn, string $table): array
{
    static $cache = [];
    if (isset($cache[$table])) {
        return $cache[$table];
    }

    $columns = [];
    $result = $conn->query("SHOW COLUMNS FROM `{$table}`");
    while ($row = $result->fetch_assoc()) {
        $columns[$row['Field']] = true;
    }

    $cache[$table] = $columns;
    return $columns;
}

function checkout_insert_row(mysqli $conn, string $table, array $values, array $types): int
{
    $columns = checkout_table_columns($conn, $table);
    $activeValues = [];
    $activeTypes = '';

    foreach ($values as $column => $value) {
        if (!isset($columns[$column])) {
            continue;
        }

        $activeValues[$column] = $value;
        $activeTypes .= $types[$column] ?? 's';
    }

    if (!$activeValues) {
        throw new RuntimeException("No insertable columns found for {$table}.");
    }

    $quotedColumns = '`' . implode('`, `', array_keys($activeValues)) . '`';
    $placeholders = implode(', ', array_fill(0, count($activeValues), '?'));
    $stmt = $conn->prepare("INSERT INTO `{$table}` ({$quotedColumns}) VALUES ({$placeholders})");

    $bindValues = array_values($activeValues);
    $params = [$activeTypes];
    foreach ($bindValues as $index => $value) {
        $params[] = &$bindValues[$index];
    }

    call_user_func_array([$stmt, 'bind_param'], $params);
    $stmt->execute();
    $insertId = $conn->insert_id;
    $stmt->close();

    return (int) $insertId;
}

function checkout_input_items(array $input): array
{
    $items = $input['items'] ?? [];

    if (is_string($items) && trim($items) !== '') {
        $decoded = json_decode($items, true);
        $items = is_array($decoded) ? $decoded : [];
    }

    return is_array($items) ? $items : [];
}

function checkout_normalize_cart_items(array $input): array
{
    $normalized = [];

    foreach (checkout_input_items($input) as $item) {
        if (!is_array($item)) {
            continue;
        }

        $productId = (int) ($item['product_id'] ?? $item['id'] ?? 0);
        $brandId = isset($item['brand_id']) && $item['brand_id'] !== '' ? (int) $item['brand_id'] : null;
        $quantity = max(1, (int) ($item['quantity'] ?? 1));

        if ($productId <= 0) {
            continue;
        }

        $key = $productId . ':' . ($brandId ?: 'base');
        if (!isset($normalized[$key])) {
            $normalized[$key] = [
                'product_id' => $productId,
                'brand_id' => $brandId,
                'quantity' => 0,
            ];
        }

        $normalized[$key]['quantity'] += $quantity;
    }

    return array_values($normalized);
}

function checkout_moq_categories(): array
{
    return ['panel', 'panels', 'mounting & accessories', 'mounting and accessories', 'mounting', 'accessories'];
}

function checkout_fetch_product(mysqli $conn, int $productId, ?int $brandId): array
{
    if ($brandId) {
        $stmt = $conn->prepare(
            "SELECT
                p.id,
                p.displayName,
                p.brandName AS productBrandName,
                p.category,
                p.packageType,
                p.stockQuantity,
                COALESCE(p.moq, 1) AS moq,
                COALESCE(pbv.price, p.price) AS price,
                COALESCE(pbv.variant_image, p.imagePath) AS image_path,
                COALESCE(b.brand_name, sb.brandName, p.brandName) AS brandName
             FROM product p
             INNER JOIN product_brand_variants pbv
                ON pbv.product_id = p.id AND pbv.brand_id = ?
             LEFT JOIN brands b
                ON b.brand_id = pbv.brand_id
             LEFT JOIN supplier_brands sb
                ON sb.id = pbv.brand_id
             WHERE p.id = ? AND p.status = 'Active'
             LIMIT 1"
        );
        $stmt->bind_param('ii', $brandId, $productId);
    } else {
        $stmt = $conn->prepare(
            "SELECT
                p.id,
                p.displayName,
                p.brandName,
                p.category,
                p.packageType,
                p.stockQuantity,
                COALESCE(p.moq, 1) AS moq,
                p.price,
                COALESCE(
                    (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.id ASC LIMIT 1),
                    p.imagePath
                ) AS image_path
             FROM product p
             WHERE p.id = ? AND p.status = 'Active'
             LIMIT 1"
        );
        $stmt->bind_param('i', $productId);
    }

    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$product) {
        throw new RuntimeException('A product in your cart is no longer available.');
    }

    return $product;
}

function checkout_validate_items(mysqli $conn, array $input): array
{
    $cartItems = checkout_normalize_cart_items($input);
    if (!$cartItems) {
        throw new RuntimeException('Your cart is empty.');
    }

    $items = [];
    $subtotal = 0.00;

    foreach ($cartItems as $cartItem) {
        $product = checkout_fetch_product($conn, $cartItem['product_id'], $cartItem['brand_id']);
        $quantity = max(1, (int) $cartItem['quantity']);
        $moq = max(1, (int) ($product['moq'] ?? 1));
        $category = checkout_clean_text($product['category'] ?? 'Solar Product');
        $categoryKey = strtolower($category);

        if (in_array($categoryKey, checkout_moq_categories(), true) && $quantity < $moq) {
            throw new RuntimeException("Minimum order quantity for {$product['displayName']} is {$moq} pcs.");
        }

        $stockQuantity = (int) ($product['stockQuantity'] ?? 0);
        if ($stockQuantity > 0 && $quantity > $stockQuantity) {
            throw new RuntimeException("Only {$stockQuantity} units are available for {$product['displayName']}.");
        }

        $brandName = checkout_clean_text($product['brandName'] ?? $product['productBrandName'] ?? '');
        $displayName = checkout_clean_text($product['displayName'] ?? 'Solar Product');
        $productName = trim($brandName . ' ' . $displayName);
        $unitPrice = round((float) ($product['price'] ?? 0), 2);

        if ($unitPrice <= 0) {
            throw new RuntimeException("The price for {$displayName} is not configured.");
        }

        $lineTotal = round($unitPrice * $quantity, 2);
        $subtotal += $lineTotal;

        $items[] = [
            'product_id' => (int) $product['id'],
            'brand_id' => $cartItem['brand_id'],
            'product_name' => $productName ?: $displayName,
            'display_name' => $displayName,
            'brand_name' => $brandName,
            'category' => $category,
            'quantity' => $quantity,
            'price' => $unitPrice,
            'subtotal' => $lineTotal,
            'image_path' => checkout_clean_text($product['image_path'] ?? ''),
        ];
    }

    return [
        'items' => $items,
        'subtotal' => round($subtotal, 2),
    ];
}

function checkout_delivery_fee(string $location, ?float $clientFee = null): float
{
    $location = strtolower(checkout_clean_text($location));

    $rules = [
        'mm_1_5km' => 2000,
        'metro manila 1-5km' => 2000,
        'mm_6_10km' => 2500,
        'metro manila 6-10km' => 2500,
        'mm_11_20km' => 4000,
        'metro manila 11-20km' => 4000,
        'mm_21_30km' => 6000,
        'metro manila 21-30km' => 6000,
        'cavite' => 4200,
        'laguna' => 6000,
        'batangas' => 8500,
        'rizal' => 7000,
        'bulacan' => 7000,
        'pampanga' => 10000,
        'tarlac' => 10000,
    ];

    foreach ($rules as $needle => $fee) {
        if (strpos($location, $needle) !== false) {
            return (float) $fee;
        }
    }

    if (strpos($location, 'cebu') !== false || strpos($location, 'davao') !== false || strpos($location, 'iloilo') !== false || strpos($location, 'bacolod') !== false || strpos($location, 'cagayan de oro') !== false || strpos($location, 'zamboanga') !== false || strpos($location, 'visayas') !== false || strpos($location, 'mindanao') !== false) {
        return 0.00;
    }

    return $clientFee !== null && $clientFee >= 0 ? round($clientFee, 2) : 0.00;
}

function checkout_app_base_url(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $scriptDir = preg_replace('#/(controllers|api)(/.*)?$#', '', $scriptDir);
    $scriptDir = trim($scriptDir, '/');

    return $scheme . '://' . $host . ($scriptDir ? '/' . $scriptDir : '');
}

function checkout_maya_config(): array
{
    $paths = [
        __DIR__ . '/../config/maya.php',
        __DIR__ . '/../controllers/ordering/maya.php',
        __DIR__ . '/../controllers/maya.php',
    ];

    $config = [
        'public_key' => getenv('MAYA_LIVE_PUBLIC_KEY') ?: getenv('MAYA_PUBLIC_KEY') ?: '',
        'secret_key' => getenv('MAYA_LIVE_SECRET_KEY') ?: getenv('MAYA_SECRET_KEY') ?: '',
        'base_url' => getenv('MAYA_CHECKOUT_BASE_URL') ?: getenv('MAYA_BASE_URL') ?: 'https://pg.maya.ph',
        'is_live' => true,
    ];

    foreach ($paths as $path) {
        if (!file_exists($path)) {
            continue;
        }

        $loaded = include $path;
        if (is_array($loaded)) {
            $config = array_merge($loaded, array_filter($config, function ($value) {
                return $value !== '';
            }));
            break;
        }
    }

    if (empty($config['base_url']) || $config['base_url'] === 'https://pg.paymaya.com') {
        $config['base_url'] = 'https://pg.maya.ph';
    }

    $config['base_url'] = rtrim($config['base_url'], '/');

    return $config;
}

function checkout_money(float $value): array
{
    return [
        'value' => round($value, 2),
        'currency' => 'PHP',
    ];
}

function checkout_name_parts(string $customerName): array
{
    $parts = preg_split('/\s+/', checkout_clean_text($customerName), 2);
    $firstName = $parts[0] ?? 'Customer';
    $lastName = $parts[1] ?? '-';

    return [$firstName ?: 'Customer', $lastName ?: '-'];
}

function checkout_build_maya_payload(int $orderId, string $orderRef, array $customer, array $validated, float $deliveryFee, string $deliveryLocation): array
{
    [$firstName, $lastName] = checkout_name_parts($customer['name']);
    $lineItems = [];

    foreach ($validated['items'] as $item) {
        $lineItems[] = [
            'name' => $item['product_name'],
            'quantity' => $item['quantity'],
            'description' => $item['category'],
            'amount' => checkout_money($item['price']),
            'totalAmount' => checkout_money($item['subtotal']),
        ];
    }

    if ($deliveryFee > 0) {
        $lineItems[] = [
            'name' => 'Delivery Fee',
            'quantity' => 1,
            'description' => $deliveryLocation ?: 'Delivery and logistics',
            'amount' => checkout_money($deliveryFee),
            'totalAmount' => checkout_money($deliveryFee),
        ];
    }

    $grandTotal = round($validated['subtotal'] + $deliveryFee, 2);
    $baseUrl = checkout_app_base_url();

    return [
        'totalAmount' => checkout_money($grandTotal),
        'buyer' => [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'contact' => [
                'phone' => $customer['phone'],
                'email' => $customer['email'],
            ],
            'shippingAddress' => [
                'line1' => $customer['address'],
                'city' => $deliveryLocation ?: 'Selected Location',
                'state' => $deliveryLocation ?: 'Selected Location',
                'zipCode' => '0000',
                'countryCode' => 'PH',
            ],
        ],
        'items' => $lineItems,
        'redirectUrl' => [
            'success' => $baseUrl . '/payment-success.php?ref=' . rawurlencode($orderRef),
            'failure' => $baseUrl . '/payment-failed.php?ref=' . rawurlencode($orderRef),
            'cancel' => $baseUrl . '/payment-cancelled.php?ref=' . rawurlencode($orderRef),
        ],
        'requestReferenceNumber' => $orderRef,
        'metadata' => [
            'orderId' => $orderId,
            'orderRef' => $orderRef,
            'itemsSubtotal' => number_format($validated['subtotal'], 2, '.', ''),
            'deliveryFee' => number_format($deliveryFee, 2, '.', ''),
            'deliveryLocation' => $deliveryLocation,
            'source' => 'solar_checkout',
        ],
    ];
}

function checkout_create_order(mysqli $conn, array $customer, array $validated, float $deliveryFee, string $deliveryLocation): array
{
    $orderRef = 'SP-' . date('YmdHis') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
    $grandTotal = round($validated['subtotal'] + $deliveryFee, 2);
    $remarks = 'Items subtotal: PHP ' . number_format($validated['subtotal'], 2, '.', '') . '; Delivery: PHP ' . number_format($deliveryFee, 2, '.', '') . '; Location: ' . $deliveryLocation;

    $conn->begin_transaction();

    try {
        $orderId = checkout_insert_row($conn, 'orders', [
            'client_id' => isset($_SESSION['client_id']) ? (int) $_SESSION['client_id'] : null,
            'order_reference' => $orderRef,
            'customer_name' => $customer['name'],
            'customer_email' => $customer['email'],
            'customer_phone' => $customer['phone'],
            'customer_address' => $customer['address'],
            'customer_city' => $deliveryLocation,
            'items_subtotal' => $validated['subtotal'],
            'delivery_fee' => $deliveryFee,
            'delivery_location' => $deliveryLocation,
            'total_amount' => $grandTotal,
            'payment_method' => 'maya',
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'sales_channel' => 'Website',
            'service_type' => 'Product Checkout',
            'remarks' => $remarks,
            'created_at' => date('Y-m-d H:i:s'),
        ], [
            'client_id' => 'i',
            'items_subtotal' => 'd',
            'delivery_fee' => 'd',
            'total_amount' => 'd',
        ]);

        $itemStmt = $conn->prepare(
            'INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?, ?)'
        );

        foreach ($validated['items'] as $item) {
            $itemStmt->bind_param(
                'iisidd',
                $orderId,
                $item['product_id'],
                $item['product_name'],
                $item['quantity'],
                $item['price'],
                $item['subtotal']
            );
            $itemStmt->execute();
        }

        $itemStmt->close();
        $conn->commit();

        return [
            'id' => $orderId,
            'reference' => $orderRef,
            'total' => $grandTotal,
        ];
    } catch (Throwable $e) {
        $conn->rollback();
        throw $e;
    }
}

function checkout_mark_order_failed(mysqli $conn, int $orderId, string $message): void
{
    $columns = checkout_table_columns($conn, 'orders');

    if (isset($columns['remarks'])) {
        $stmt = $conn->prepare("UPDATE orders SET payment_status = 'failed', order_status = 'cancelled', remarks = CONCAT(COALESCE(remarks, ''), '\nMaya error: ', ?) WHERE id = ?");
        $stmt->bind_param('si', $message, $orderId);
    } else {
        $stmt = $conn->prepare("UPDATE orders SET payment_status = 'failed', order_status = 'cancelled' WHERE id = ?");
        $stmt->bind_param('i', $orderId);
    }

    $stmt->execute();
    $stmt->close();
}

function checkout_call_maya(array $payload): array
{
    $config = checkout_maya_config();
    $publicKey = $config['public_key'] ?? '';

    if ($publicKey === '') {
        throw new RuntimeException('Maya Checkout public key is not configured.');
    }

    $endpoint = rtrim($config['base_url'] ?? 'https://pg.maya.ph', '/') . '/checkout/v1/checkouts';
    $ch = curl_init($endpoint);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($publicKey . ':'),
        ],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 30,
    ]);

    $rawResponse = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        throw new RuntimeException('Maya connection error: ' . $curlError);
    }

    $response = json_decode((string) $rawResponse, true);
    if (!is_array($response)) {
        $response = [];
    }

    return [
        'ok' => $httpCode >= 200 && $httpCode < 300 && !empty($response['redirectUrl']),
        'status' => $httpCode,
        'endpoint' => $endpoint,
        'body' => $response,
        'raw' => $rawResponse,
    ];
}

function checkout_customer_from_input(array $input): array
{
    $customer = [
        'name' => checkout_clean_text($input['customerName'] ?? $input['name'] ?? ''),
        'email' => checkout_clean_text($input['customerEmail'] ?? $input['email'] ?? ''),
        'phone' => checkout_clean_text($input['customerPhone'] ?? $input['phone'] ?? ''),
        'address' => checkout_clean_text($input['customerAddress'] ?? $input['address'] ?? ''),
    ];

    if ($customer['name'] === '' || $customer['email'] === '' || $customer['phone'] === '' || $customer['address'] === '') {
        throw new RuntimeException('Please complete all customer checkout details.');
    }

    if (!filter_var($customer['email'], FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Please enter a valid email address.');
    }

    return $customer;
}

function checkout_create_maya_checkout(mysqli $conn, array $input): array
{
    $customer = checkout_customer_from_input($input);
    $validated = checkout_validate_items($conn, $input);
    $deliveryLocation = checkout_clean_text($input['selected_location_name'] ?? $input['deliveryLocation'] ?? $input['province'] ?? '');
    $clientDeliveryFee = isset($input['calculated_delivery_fee']) ? (float) $input['calculated_delivery_fee'] : null;
    $deliveryFee = checkout_delivery_fee($deliveryLocation, $clientDeliveryFee);
    $order = checkout_create_order($conn, $customer, $validated, $deliveryFee, $deliveryLocation);
    $payload = checkout_build_maya_payload($order['id'], $order['reference'], $customer, $validated, $deliveryFee, $deliveryLocation);
    $maya = checkout_call_maya($payload);

    if (!$maya['ok']) {
        $message = $maya['body']['message'] ?? $maya['body']['error'] ?? 'Maya Checkout creation failed.';
        checkout_mark_order_failed($conn, $order['id'], $message);

        return [
            'success' => false,
            'error' => $message,
            'message' => $message,
            'code' => $maya['status'],
        ];
    }

    $_SESSION['cart'] = [];

    return [
        'success' => true,
        'orderRef' => $order['reference'],
        'orderId' => $order['id'],
        'checkoutId' => $maya['body']['checkoutId'] ?? null,
        'paymentUrl' => $maya['body']['redirectUrl'],
        'checkoutUrl' => $maya['body']['redirectUrl'],
        'totalAmount' => $order['total'],
    ];
}
