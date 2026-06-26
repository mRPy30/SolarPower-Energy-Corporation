<?php
/**
 * CART ENGINE
 * Supports normal Add to Cart and single-item Buy Now session cart flows.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$contentType = isset($_SERVER['CONTENT_TYPE']) ? trim($_SERVER['CONTENT_TYPE']) : '';
if (stripos($contentType, 'application/json') !== false) {
    $jsonInput = json_decode(file_get_contents('php://input'), true);
    if (is_array($jsonInput)) {
        $_POST = array_merge($_POST, $jsonInput);
    }
}

$variant_id = isset($_POST['variant_id']) ? trim($_POST['variant_id']) : null;
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : intval($variant_id);
$brand_id = isset($_POST['brand_id']) && $_POST['brand_id'] !== '' ? intval($_POST['brand_id']) : null;
$brand_variant = isset($_POST['brand_variant']) ? trim($_POST['brand_variant']) : '';
$name = isset($_POST['name']) ? trim($_POST['name']) : 'Product';
$price = isset($_POST['price']) ? floatval($_POST['price']) : 0.00;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
$mode = isset($_POST['mode']) ? trim($_POST['mode']) : 'add';

if ($variant_id === null || $variant_id === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing variant_id']);
    exit;
}

if ($mode === 'buy_now') {
    $_SESSION['cart'] = [];
} elseif (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($quantity <= 0) {
    unset($_SESSION['cart'][$variant_id]);
} else {
    $_SESSION['cart'][$variant_id] = [
        'product_id' => $product_id,
        'variant_id' => $variant_id,
        'brand_id' => $brand_id,
        'brand_variant' => $brand_variant,
        'name' => $name,
        'price' => $price,
        'quantity' => $quantity,
    ];
}

echo json_encode([
    'success' => true,
    'message' => $mode === 'buy_now' ? 'Buy Now cart prepared' : 'Cart updated successfully',
    'checkout' => $mode === 'buy_now',
    'cart' => $_SESSION['cart'],
]);
