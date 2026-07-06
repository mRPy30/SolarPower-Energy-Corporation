<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function cart_controller_rows(): array
{
    return array_values(array_filter($_SESSION['cart'] ?? [], 'is_array'));
}

function cart_controller_total_items(): int
{
    return count(cart_controller_rows());
}

function cart_controller_send(array $payload): void
{
    if (array_key_exists('cart', $payload)) {
        $payload['cart'] = array_values(array_filter((array) $payload['cart'], 'is_array'));
    } else {
        $payload['cart'] = cart_controller_rows();
    }

    if (!array_key_exists('total_items', $payload)) {
        $payload['total_items'] = count($payload['cart']);
    }

    echo json_encode($payload);
}

$action = $_GET['action'] ?? '';

if ($action === 'sync') {
    $input = json_decode(file_get_contents('php://input'), true);
    $cart = $input['cart'] ?? null;
    if (is_array($cart)) {
        $_SESSION['cart'] = [];
        foreach ($cart as $item) {
            $_SESSION['cart'][] = [
                'id' => intval($item['product_id'] ?? $item['id']),
                'product_id' => intval($item['product_id'] ?? $item['id']),
                'variant_id' => $item['variant_id'] ?? '',
                'brand_id' => isset($item['brand_id']) ? intval($item['brand_id']) : null,
                'displayName' => $item['displayName'] ?? $item['name'] ?? '',
                'brandName' => $item['brandName'] ?? '',
                'category' => $item['category'] ?? '',
                'price' => floatval($item['price']),
                'image_path' => $item['image_path'] ?? $item['imagePath'] ?? '',
                'quantity' => intval($item['quantity'] ?? 1),
                'moq' => intval($item['moq'] ?? 1)
            ];
        }
        cart_controller_send(['success' => true]);
    } else {
        cart_controller_send(['success' => false, 'error' => 'Invalid cart data']);
    }
    exit;
}

if ($action === 'add') {
    $_SESSION['cart'] = cart_controller_rows();
    $input = json_decode(file_get_contents('php://input'), true);
    $item = $input['item'] ?? null;
    if ($item) {
        $found = false;
        foreach ($_SESSION['cart'] as &$cartItem) {
            $incomingVariantId = (string)($item['variant_id'] ?? '');
            $cartVariantId = (string)($cartItem['variant_id'] ?? '');
            $sameVariant = $incomingVariantId !== '' && $incomingVariantId === $cartVariantId;
            $sameBaseProduct = $incomingVariantId === '' && $cartItem['id'] == $item['id'] && $cartItem['brand_id'] == $item['brand_id'];
            if ($sameVariant || $sameBaseProduct) {
                $cartItem['quantity'] += intval($item['quantity'] ?? 1);
                $found = true;
                break;
            }
        }
        if (!$found) {
            $_SESSION['cart'][] = [
                'id' => intval($item['id']),
                'product_id' => intval($item['product_id'] ?? $item['id']),
                'variant_id' => $item['variant_id'] ?? '',
                'brand_id' => isset($item['brand_id']) ? intval($item['brand_id']) : null,
                'displayName' => $item['displayName'] ?? '',
                'brandName' => $item['brandName'] ?? '',
                'category' => $item['category'] ?? '',
                'price' => floatval($item['price']),
                'image_path' => $item['image_path'] ?? '',
                'quantity' => intval($item['quantity'] ?? 1),
                'moq' => intval($item['moq'] ?? 1)
            ];
        }
        cart_controller_send(['success' => true]);
    } else {
        cart_controller_send(['success' => false, 'error' => 'No item provided']);
    }
    exit;
}

if ($action === 'buynow') {
    $input = json_decode(file_get_contents('php://input'), true);
    $item = $input['item'] ?? null;
    if ($item) {
        $_SESSION['cart'] = [];
        $_SESSION['cart'][] = [
            'id' => intval($item['id']),
            'product_id' => intval($item['product_id'] ?? $item['id']),
            'variant_id' => $item['variant_id'] ?? '',
            'brand_id' => isset($item['brand_id']) ? intval($item['brand_id']) : null,
            'displayName' => $item['displayName'] ?? '',
            'brandName' => $item['brandName'] ?? '',
            'category' => $item['category'] ?? '',
            'price' => floatval($item['price']),
            'image_path' => $item['image_path'] ?? '',
            'quantity' => intval($item['quantity'] ?? 1),
            'moq' => intval($item['moq'] ?? 1)
        ];
        cart_controller_send(['success' => true]);
    } else {
        cart_controller_send(['success' => false, 'error' => 'No item provided']);
    }
    exit;
}

if ($action === 'get') {
    cart_controller_send(['success' => true]);
    exit;
}

if ($action === 'update') {
    $_SESSION['cart'] = cart_controller_rows();
    $input = json_decode(file_get_contents('php://input'), true);
    $index = intval($input['index'] ?? -1);
    $quantity = intval($input['quantity'] ?? 1);
    if ($index >= 0 && isset($_SESSION['cart'][$index])) {
        $_SESSION['cart'][$index]['quantity'] = $quantity;
        cart_controller_send(['success' => true]);
    } else {
        cart_controller_send(['success' => false, 'error' => 'Invalid index']);
    }
    exit;
}

if ($action === 'remove') {
    $_SESSION['cart'] = cart_controller_rows();
    $input = json_decode(file_get_contents('php://input'), true);
    $index = intval($input['index'] ?? -1);
    if ($index >= 0 && isset($_SESSION['cart'][$index])) {
        array_splice($_SESSION['cart'], $index, 1);
        cart_controller_send(['success' => true]);
    } else {
        cart_controller_send(['success' => false, 'error' => 'Invalid index']);
    }
    exit;
}

if ($action === 'clear') {
    $_SESSION['cart'] = [];
    cart_controller_send(['success' => true]);
    exit;
}
