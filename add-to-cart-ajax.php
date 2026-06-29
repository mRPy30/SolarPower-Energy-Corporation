<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'success' => false,
        'message' => 'Method not allowed',
        'total_items' => isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? count($_SESSION['cart']) : 0,
    ]);
    exit;
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
    if (is_array($input)) {
        $_POST = array_merge($_POST, $input);
    }
}

function cart_ajax_clean($value): string
{
    $text = trim((string) $value);
    $text = preg_replace('/[\r\n\t]+/', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    return trim(strip_tags($text));
}

function cart_ajax_nullable_int($value): ?int
{
    if ($value === null || $value === '') {
        return null;
    }

    $number = (int) $value;
    return $number > 0 ? $number : null;
}

function cart_ajax_item_key(int $productId, ?int $brandId, string $variantId): string
{
    if ($variantId !== '') {
        return 'variant_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $variantId);
    }

    return 'product_' . $productId . '_brand_' . ($brandId ?: 'base');
}

function cart_ajax_find_existing_key(array $cart, int $productId, ?int $brandId, string $variantId): ?string
{
    foreach ($cart as $key => $item) {
        if (!is_array($item)) {
            continue;
        }

        if ($variantId !== '' && (string) ($item['variant_id'] ?? '') === $variantId) {
            return (string) $key;
        }

        $itemProductId = (int) ($item['product_id'] ?? $item['id'] ?? 0);
        $itemBrandId = cart_ajax_nullable_int($item['brand_id'] ?? null);
        if ($itemProductId === $productId && $itemBrandId === $brandId) {
            return (string) $key;
        }
    }

    return null;
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$variantId = cart_ajax_clean($_POST['variant_id'] ?? '');
$productId = (int) ($_POST['product_id'] ?? $_POST['id'] ?? 0);
$brandId = cart_ajax_nullable_int($_POST['brand_id'] ?? null);
$productName = cart_ajax_clean($_POST['product_name'] ?? $_POST['displayName'] ?? $_POST['name'] ?? 'Solar Product');
$brandName = cart_ajax_clean($_POST['brand_name'] ?? $_POST['brandName'] ?? '');
$category = cart_ajax_clean($_POST['category'] ?? '');
$brandVariant = cart_ajax_clean($_POST['brand_variant'] ?? '');
$imagePath = cart_ajax_clean($_POST['image_path'] ?? $_POST['imagePath'] ?? '');
$price = round((float) ($_POST['price'] ?? 0), 2);
$quantity = max(1, (int) ($_POST['quantity'] ?? 1));
$moq = max(1, (int) ($_POST['moq'] ?? 1));

if ($productId <= 0 && $variantId === '') {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'success' => false,
        'message' => 'Missing product_id',
        'total_items' => count($_SESSION['cart']),
    ]);
    exit;
}

if ($productId <= 0) {
    $productId = (int) preg_replace('/\D+/', '', $variantId);
}

$cart = $_SESSION['cart'];
$existingKey = cart_ajax_find_existing_key($cart, $productId, $brandId, $variantId);
$key = $existingKey ?? cart_ajax_item_key($productId, $brandId, $variantId);

if ($existingKey !== null && isset($cart[$existingKey]) && is_array($cart[$existingKey])) {
    $cart[$existingKey]['quantity'] = max($moq, (int) ($cart[$existingKey]['quantity'] ?? 0) + $quantity);
    $cart[$existingKey]['price'] = $price > 0 ? $price : (float) ($cart[$existingKey]['price'] ?? 0);
    $cart[$existingKey]['moq'] = $moq;
} else {
    $cart[$key] = [
        'id' => $productId,
        'product_id' => $productId,
        'variant_id' => $variantId,
        'brand_id' => $brandId,
        'brand_variant' => $brandVariant,
        'displayName' => $productName,
        'name' => $productName,
        'product_name' => $productName,
        'brandName' => $brandName,
        'brand_name' => $brandName,
        'category' => $category,
        'price' => $price,
        'image_path' => $imagePath,
        'quantity' => max($quantity, $moq),
        'moq' => $moq,
    ];
}

$_SESSION['cart'] = $cart;

echo json_encode([
    'status' => 'success',
    'success' => true,
    'message' => 'Product added to cart successfully!',
    'total_items' => count($_SESSION['cart']),
    'cart' => $_SESSION['cart'],
]);
