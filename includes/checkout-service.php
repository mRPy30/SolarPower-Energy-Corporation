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

function checkout_ensure_delivery_rates_table(mysqli $conn): void
{
    $conn->query("
        CREATE TABLE IF NOT EXISTS delivery_rates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            origin_address VARCHAR(255) DEFAULT 'Madrigal Business Park, Alabang, Muntinlupa',
            rate_type VARCHAR(50),
            location_name VARCHAR(100),
            price DECIMAL(10,2) NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
}

function checkout_assert_order_schema(mysqli $conn): void
{
    $required = [
        'client_id',
        'order_reference',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'delivery_location',
        'items_subtotal',
        'delivery_fee',
        'total_amount',
        'payment_method',
        'payment_status',
        'order_status',
    ];

    $columns = checkout_table_columns($conn, 'orders');
    $missing = [];

    foreach ($required as $column) {
        if (!isset($columns[$column])) {
            $missing[] = $column;
        }
    }

    if ($missing) {
        throw new RuntimeException('The active orders table is missing checkout columns: ' . implode(', ', $missing) . '. Do not create a new orders table; apply the checkout migration to the existing table.');
    }
}

function checkout_delivery_rate_from_input(mysqli $conn, array $input): array
{
    checkout_ensure_delivery_rates_table($conn);

    $rateId = (int) ($input['delivery_rate_id'] ?? $input['deliveryRateId'] ?? 0);
    if ($rateId <= 0) {
        throw new RuntimeException('Please select a delivery location.');
    }

    $stmt = $conn->prepare(
        'SELECT id, origin_address, rate_type, location_name, price
         FROM delivery_rates
         WHERE id = ?
         LIMIT 1'
    );
    $stmt->bind_param('i', $rateId);
    $stmt->execute();
    $rate = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$rate) {
        throw new RuntimeException('Selected delivery rate is no longer available.');
    }

    $price = (float) ($rate['price'] ?? 0);
    if ($price <= 0) {
        throw new RuntimeException("We're sorry, but we don't offer delivery to your location at the moment. Please contact our customer support at [Insert Corporate Hotline/Email Here] to assist you with alternative shipping arrangements.");
    }

    // Apply delivery tier modifier
    $tier = trim((string) ($input['delivery_service_tier'] ?? 'SunSpeed Standard'));
    $validTiers = [
        'Eco-Saver Shipping',
        'SunSpeed Standard',
        'SolarFlash Express'
    ];
    if (!in_array($tier, $validTiers, true)) {
        $tier = 'SunSpeed Standard';
    }

    $modifier = 1.00;
    if ($tier === 'Eco-Saver Shipping') {
        $modifier = 0.85;
    } elseif ($tier === 'SolarFlash Express') {
        $modifier = 1.40;
    }

    $finalPrice = round($price * $modifier, 2);
    $locationName = checkout_clean_text($rate['location_name'] ?? '');
    $locationNameWithTier = $locationName . ' [' . $tier . ']';

    return [
        'id' => (int) $rate['id'],
        'origin_address' => checkout_clean_text($rate['origin_address'] ?? 'Madrigal Business Park, Alabang, Muntinlupa'),
        'rate_type' => checkout_clean_text($rate['rate_type'] ?? ''),
        'location_name' => $locationNameWithTier,
        'price' => $finalPrice,
    ];
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
        $variantId = isset($item['variant_id']) && $item['variant_id'] !== '' ? (int) $item['variant_id'] : null;
        $quantity = max(1, (int) ($item['quantity'] ?? 1));

        if ($productId <= 0) {
            continue;
        }

        $key = $productId . ':' . ($brandId ?: 'base') . ':' . ($variantId ?: 'base');
        if (!isset($normalized[$key])) {
            $normalized[$key] = [
                'product_id' => $productId,
                'brand_id' => $brandId,
                'variant_id' => $variantId,
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

function checkout_ensure_variant_name_column(mysqli $conn): void
{
    static $checked = false;
    if ($checked) {
        return;
    }

    $checked = true;
    $variantColumnCheck = $conn->query("SHOW COLUMNS FROM product_brand_variants LIKE 'variant_name'");
    if ($variantColumnCheck && $variantColumnCheck->num_rows === 0) {
        $conn->query("ALTER TABLE product_brand_variants ADD COLUMN variant_name VARCHAR(255) NOT NULL DEFAULT '' AFTER brand_id");
    }
}

function checkout_fetch_product(mysqli $conn, int $productId, ?int $brandId, ?int $variantId = null): array
{
    checkout_ensure_variant_name_column($conn);

    if ($variantId) {
        $stmt = $conn->prepare(
            "SELECT
                p.id,
                COALESCE(NULLIF(TRIM(pbv.variant_name), ''), p.displayName) AS displayName,
                p.brandName AS productBrandName,
                p.category,
                p.packageType,
                p.stockQuantity,
                COALESCE(p.moq, 1) AS moq,
                COALESCE(pbv.price, p.price) AS price,
                COALESCE(pbv.variant_image, p.imagePath) AS image_path,
                pbv.id AS variant_id,
                COALESCE(b.brand_name, sb.brandName, p.brandName) AS brandName
             FROM product p
             INNER JOIN product_brand_variants pbv
                ON pbv.product_id = p.id AND pbv.id = ?
             LEFT JOIN brands b
                ON b.brand_id = pbv.brand_id
             LEFT JOIN supplier_brands sb
                ON sb.id = pbv.brand_id
             WHERE p.id = ? AND p.status = 'Active'
             LIMIT 1"
        );
        $stmt->bind_param('ii', $variantId, $productId);
    } elseif ($brandId) {
        $stmt = $conn->prepare(
            "SELECT
                p.id,
                COALESCE(NULLIF(TRIM(pbv.variant_name), ''), p.displayName) AS displayName,
                p.brandName AS productBrandName,
                p.category,
                p.packageType,
                p.stockQuantity,
                COALESCE(p.moq, 1) AS moq,
                COALESCE(pbv.price, p.price) AS price,
                COALESCE(pbv.variant_image, p.imagePath) AS image_path,
                pbv.id AS variant_id,
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
        $product = checkout_fetch_product($conn, $cartItem['product_id'], $cartItem['brand_id'], $cartItem['variant_id']);
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
        $productName = ($brandName !== '' && stripos($displayName, $brandName) === false)
            ? trim($brandName . ' ' . $displayName)
            : $displayName;
        $unitPrice = round((float) ($product['price'] ?? 0), 2);

        if ($unitPrice <= 0) {
            throw new RuntimeException("The price for {$displayName} is not configured.");
        }

        $lineTotal = round($unitPrice * $quantity, 2);
        $subtotal += $lineTotal;

        $items[] = [
            'product_id' => (int) $product['id'],
            'brand_id' => $cartItem['brand_id'],
            'variant_id' => $cartItem['variant_id'],
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

function checkout_build_maya_payload(int $orderId, string $orderRef, array $customer, array $validated, array $deliveryRate): array
{
    [$firstName, $lastName] = checkout_name_parts($customer['name']);
    $lineItems = [];
    $deliveryFee = $deliveryRate['price'];
    $deliveryLocation = $deliveryRate['location_name'];

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
            'deliveryRateId' => (string) $deliveryRate['id'],
            'deliveryOrigin' => $deliveryRate['origin_address'],
            'itemsSubtotal' => number_format($validated['subtotal'], 2, '.', ''),
            'deliveryFee' => number_format($deliveryFee, 2, '.', ''),
            'deliveryLocation' => $deliveryLocation,
            'source' => 'solar_checkout',
        ],
    ];
}

function checkout_generate_order_reference(): string
{
    return 'SP-' . date('YmdHis') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
}

function checkout_create_order(mysqli $conn, array $customer, array $validated, array $deliveryRate, ?string $orderRef = null, string $paymentStatus = 'paid', string $orderStatus = 'processing'): array
{
    checkout_assert_order_schema($conn);

    $orderRef = $orderRef ?: checkout_generate_order_reference();
    $deliveryFee = $deliveryRate['price'];
    $deliveryLocation = $deliveryRate['location_name'];
    $grandTotal = round($validated['subtotal'] + $deliveryFee, 2);
    $paymentMethod = 'maya';
    $clientId = isset($_SESSION['client_id']) && (int) $_SESSION['client_id'] > 0 ? (int) $_SESSION['client_id'] : null;

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare(
            "INSERT INTO orders (
                client_id,
                order_reference,
                customer_name,
                customer_email,
                customer_phone,
                customer_address,
                delivery_location,
                items_subtotal,
                delivery_fee,
                total_amount,
                payment_method,
                payment_status,
                order_status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            'issssssdddsss',
            $clientId,
            $orderRef,
            $customer['name'],
            $customer['email'],
            $customer['phone'],
            $customer['address'],
            $deliveryLocation,
            $validated['subtotal'],
            $deliveryFee,
            $grandTotal,
            $paymentMethod,
            $paymentStatus,
            $orderStatus
        );
        $stmt->execute();
        $orderId = (int) $conn->insert_id;
        $stmt->close();

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

function checkout_ensure_pending_maya_table(mysqli $conn): void
{
    $conn->query("
        CREATE TABLE IF NOT EXISTS maya_pending_checkouts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_reference VARCHAR(100) NOT NULL UNIQUE,
            checkout_id VARCHAR(150) DEFAULT NULL,
            checkout_url TEXT DEFAULT NULL,
            payload_json LONGTEXT NOT NULL,
            total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            status VARCHAR(30) NOT NULL DEFAULT 'pending',
            order_id INT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            paid_at DATETIME DEFAULT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_maya_pending_status (status),
            INDEX idx_maya_pending_order_id (order_id)
        )
    ");
}

function checkout_store_pending_maya_checkout(mysqli $conn, string $orderRef, ?string $checkoutId, string $checkoutUrl, array $customer, array $validated, array $deliveryRate): void
{
    checkout_ensure_pending_maya_table($conn);

    $payload = [
        'customer' => $customer,
        'validated' => $validated,
        'deliveryRate' => $deliveryRate,
    ];
    $payloadJson = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($payloadJson === false) {
        throw new RuntimeException('Unable to prepare checkout session data.');
    }

    $totalAmount = round((float) $validated['subtotal'] + (float) $deliveryRate['price'], 2);
    $status = 'pending';

    $stmt = $conn->prepare("
        INSERT INTO maya_pending_checkouts (
            order_reference,
            checkout_id,
            checkout_url,
            payload_json,
            total_amount,
            status
        ) VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            checkout_id = VALUES(checkout_id),
            checkout_url = VALUES(checkout_url),
            payload_json = VALUES(payload_json),
            total_amount = VALUES(total_amount),
            status = VALUES(status),
            order_id = NULL,
            paid_at = NULL
    ");
    $stmt->bind_param('ssssds', $orderRef, $checkoutId, $checkoutUrl, $payloadJson, $totalAmount, $status);
    $stmt->execute();
    $stmt->close();

    $_SESSION['pending_maya_orders'][$orderRef] = [
        'checkout_id' => $checkoutId,
        'created_at' => time(),
    ];
}

function checkout_load_pending_maya_checkout(mysqli $conn, string $orderRef): ?array
{
    checkout_ensure_pending_maya_table($conn);

    $stmt = $conn->prepare('SELECT * FROM maya_pending_checkouts WHERE order_reference = ? LIMIT 1');
    $stmt->bind_param('s', $orderRef);
    $stmt->execute();
    $pending = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$pending) {
        return null;
    }

    $payload = json_decode((string) $pending['payload_json'], true);
    if (!is_array($payload)) {
        throw new RuntimeException('Unable to restore checkout session data.');
    }

    $pending['payload'] = $payload;
    return $pending;
}

function checkout_find_order_by_reference(mysqli $conn, string $orderRef): ?array
{
    $stmt = $conn->prepare('SELECT id, order_reference, total_amount, payment_status, order_status FROM orders WHERE order_reference = ? LIMIT 1');
    $stmt->bind_param('s', $orderRef);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $order ?: null;
}

function checkout_mark_pending_maya_status(mysqli $conn, string $orderRef, string $status, ?int $orderId = null): void
{
    checkout_ensure_pending_maya_table($conn);

    if ($status === 'paid') {
        $stmt = $conn->prepare("UPDATE maya_pending_checkouts SET status = 'paid', order_id = ?, paid_at = NOW() WHERE order_reference = ?");
        $stmt->bind_param('is', $orderId, $orderRef);
    } else {
        $stmt = $conn->prepare('UPDATE maya_pending_checkouts SET status = ? WHERE order_reference = ? AND status = \'pending\'');
        $stmt->bind_param('ss', $status, $orderRef);
    }

    $stmt->execute();
    $stmt->close();
}

function checkout_finalize_paid_maya_order(mysqli $conn, string $orderRef): array
{
    $orderRef = checkout_clean_text($orderRef);
    if ($orderRef === '' || $orderRef === 'Unknown') {
        throw new RuntimeException('Missing order reference.');
    }

    $existingOrder = checkout_find_order_by_reference($conn, $orderRef);
    if ($existingOrder) {
        return [
            'id' => (int) $existingOrder['id'],
            'reference' => $existingOrder['order_reference'],
            'total' => (float) $existingOrder['total_amount'],
            'already_saved' => true,
        ];
    }

    $pending = checkout_load_pending_maya_checkout($conn, $orderRef);
    if (!$pending || !isset($pending['payload']['customer'], $pending['payload']['validated'], $pending['payload']['deliveryRate'])) {
        throw new RuntimeException('We could not verify this paid checkout session. Please contact support with your Maya payment reference.');
    }

    if (($pending['status'] ?? '') !== 'pending') {
        throw new RuntimeException('This checkout session is not available for order finalization.');
    }

    $order = checkout_create_order(
        $conn,
        $pending['payload']['customer'],
        $pending['payload']['validated'],
        $pending['payload']['deliveryRate'],
        $orderRef,
        'paid',
        'processing'
    );

    checkout_mark_pending_maya_status($conn, $orderRef, 'paid', (int) $order['id']);
    unset($_SESSION['pending_maya_orders'][$orderRef]);

    return $order;
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

    if (strpos($publicKey, 'pk-') !== 0) {
        throw new RuntimeException('Maya Checkout Create Checkout must use the PUBLIC API key that starts with pk-. Using an sk- secret key causes K003 invalid authentication credentials.');
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
    $deliveryRate = checkout_delivery_rate_from_input($conn, $input);
    $orderRef = checkout_generate_order_reference();
    $grandTotal = round($validated['subtotal'] + $deliveryRate['price'], 2);
    $payload = checkout_build_maya_payload(0, $orderRef, $customer, $validated, $deliveryRate);
    $maya = checkout_call_maya($payload);

    if (!$maya['ok']) {
        $message = $maya['body']['message'] ?? $maya['body']['error']['message'] ?? $maya['body']['error'] ?? 'Maya Checkout creation failed.';

        return [
            'success' => false,
            'error' => $message,
            'message' => $message,
            'mayaCode' => $maya['body']['code'] ?? $maya['body']['error']['code'] ?? null,
            'code' => $maya['status'],
        ];
    }

    $checkoutUrl = $maya['body']['redirectUrl'];
    $checkoutId = $maya['body']['checkoutId'] ?? null;
    checkout_store_pending_maya_checkout($conn, $orderRef, $checkoutId, $checkoutUrl, $customer, $validated, $deliveryRate);

    return [
        'success' => true,
        'orderRef' => $orderRef,
        'orderId' => null,
        'checkoutId' => $checkoutId,
        'paymentUrl' => $checkoutUrl,
        'checkoutUrl' => $checkoutUrl,
        'totalAmount' => $grandTotal,
    ];
}
