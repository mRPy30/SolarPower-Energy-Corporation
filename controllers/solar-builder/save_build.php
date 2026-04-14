<?php
/**
 * Solar Builder API — Save Build / Request Quote
 *
 * POST JSON body:
 * {
 *   "components": {
 *     "panels":     { "id": 95,  "qty": 10 },
 *     "inverter":   { "id": 135, "qty": 1  },
 *     "battery":    { "id": 130, "qty": 2  },
 *     "mounting":   { "id": 500, "qty": 1  },
 *     "wiring":     { "id": 511, "qty": 1  },
 *     "monitoring": { "id": 520, "qty": 1  }
 *   },
 *   "peripherals": [1, 3, 5],
 *   "customer": {
 *     "name":  "Juan Dela Cruz",
 *     "email": "juan@example.com",
 *     "phone": "09171234567"
 *   },
 *   "performance_data": { ... }
 * }
 *
 * Returns: build reference ID.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

require_once __DIR__ . '/../../config/db_pdo.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['components'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request body']);
    exit;
}

try {
    $pdo = getPDO();
    $pdo->beginTransaction();

    // Generate build reference
    $buildRef = 'SB-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

    // Session ID for anonymous tracking
    session_start();
    $sessionId = session_id();

    // Customer info
    $customer = $input['customer'] ?? [];
    $customerName  = trim($customer['name'] ?? '');
    $customerEmail = trim($customer['email'] ?? '');
    $customerPhone = trim($customer['phone'] ?? '');

    // Calculate total
    $totalAmount = 0;
    $components = $input['components'] ?? [];
    $categories = ['panels', 'inverter', 'battery', 'mounting', 'wiring', 'monitoring'];

    // Validate and price components
    $lineItems = [];
    foreach ($categories as $cat) {
        if (empty($components[$cat]['id'])) continue;

        $pid = (int)$components[$cat]['id'];
        $qty = max(1, (int)($components[$cat]['qty'] ?? 1));

        $stmt = $pdo->prepare("SELECT id, price, stockQuantity FROM product WHERE id = ?");
        $stmt->execute([$pid]);
        $product = $stmt->fetch();

        if (!$product) continue;
        if ($qty > (int)$product['stockQuantity']) {
            $pdo->rollBack();
            echo json_encode([
                'success' => false,
                'error'   => "Insufficient stock for product ID {$pid}. Available: {$product['stockQuantity']}",
            ]);
            exit;
        }

        $unitPrice = (float)$product['price'];
        $subtotal = $unitPrice * $qty;
        $totalAmount += $subtotal;

        $lineItems[] = [
            'product_id'    => $pid,
            'category_slug' => $cat,
            'quantity'       => $qty,
            'unit_price'     => $unitPrice,
            'subtotal'       => $subtotal,
        ];
    }

    // Peripherals table was removed - skip peripheral costs

    // Performance data
    $perfJson = isset($input['performance_data']) ? json_encode($input['performance_data']) : null;

    // Insert build
    $buildStmt = $pdo->prepare(
        "INSERT INTO solar_builds (build_reference, session_id, customer_name, customer_email, customer_phone, total_amount, status, performance_data)
         VALUES (?, ?, ?, ?, ?, ?, 'submitted', ?)"
    );
    $buildStmt->execute([$buildRef, $sessionId, $customerName, $customerEmail, $customerPhone, $totalAmount, $perfJson]);
    $buildId = (int)$pdo->lastInsertId();

    // Insert line items
    $itemStmt = $pdo->prepare(
        "INSERT INTO solar_build_items (build_id, product_id, category_slug, quantity, unit_price, subtotal)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    foreach ($lineItems as $li) {
        $itemStmt->execute([$buildId, $li['product_id'], $li['category_slug'], $li['quantity'], $li['unit_price'], $li['subtotal']]);
    }

    // Peripherals table was removed - skip inserting peripherals

    $pdo->commit();

    echo json_encode([
        'success'         => true,
        'build_id'        => $buildId,
        'build_reference' => $buildRef,
        'total_amount'    => round($totalAmount, 2),
        'message'         => 'Build saved successfully! Our team will contact you within 24 hours.',
    ]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
