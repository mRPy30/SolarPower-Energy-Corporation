<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: checkout.php');
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once __DIR__ . '/config/dbconn.php';
require_once __DIR__ . '/includes/checkout-service.php';

function process_payment_session_items(): array
{
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        return [];
    }

    $items = [];
    foreach ($_SESSION['cart'] as $item) {
        if (!is_array($item)) {
            continue;
        }

        $productId = (int) ($item['product_id'] ?? $item['id'] ?? 0);
        if ($productId <= 0) {
            continue;
        }

        $items[] = [
            'id' => $productId,
            'product_id' => $productId,
            'brand_id' => $item['brand_id'] ?? null,
            'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
        ];
    }

    return $items;
}

try {
    $input = $_POST;
    $input['delivery_rate_id'] = $input['delivery_rate_id'] ?? $input['deliveryLocation'] ?? null;
    $input['items'] = process_payment_session_items();

    $result = checkout_create_maya_checkout($conn, $input);

    if (!empty($result['success']) && !empty($result['checkoutUrl'])) {
        header('Location: ' . $result['checkoutUrl']);
        exit;
    }

    $message = $result['message'] ?? $result['error'] ?? 'Failed to build secure payment redirect.';
    http_response_code(502);
    echo 'Maya Checkout Error: ' . htmlspecialchars($message);
} catch (RuntimeException $e) {
    http_response_code(400);
    echo 'Checkout Error: ' . htmlspecialchars($e->getMessage());
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Checkout Error: ' . htmlspecialchars($e->getMessage());
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
