<?php
/**
 * SIMPLIFIED ADD TO CART ENGINE
 * Accept product/variant inputs via POST (variant_id, quantity, price, name)
 * Store directly into $_SESSION['cart']
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Support JSON content type parsing
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    if (stripos($contentType, 'application/json') !== false) {
        $jsonInput = json_decode(file_get_contents('php://input'), true);
        if (is_array($jsonInput)) {
            $_POST = array_merge($_POST, $jsonInput);
        }
    }

    $variant_id = isset($_POST['variant_id']) ? trim($_POST['variant_id']) : null;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0.00;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    if ($variant_id !== null && $variant_id !== '') {
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // If quantity is 0 or less, remove it from the cart
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$variant_id]);
        } else {
            $_SESSION['cart'][$variant_id] = [
                'name' => $name,
                'price' => $price,
                'quantity' => $quantity
            ];
        }

        // Return a simple success JSON response or direct redirect back to the product page
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' || isset($_POST['json'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Cart updated successfully',
                'cart' => $_SESSION['cart']
            ]);
            exit;
        } else {
            $referer = $_SERVER['HTTP_REFERER'] ?? 'product.php';
            header("Location: " . $referer);
            exit;
        }
    } else {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Missing variant_id'
        ]);
        exit;
    }
} else {
    header('Content-Type: application/json');
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed'
    ]);
    exit;
}
