<?php
header('Content-Type: application/json');

// Database connection
include "../../config/dbconn.php";

// Get product ID from query parameter
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($productId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid product ID']);
    exit;
}

// Fetch product details
$sql = "SELECT 
    p.id,
    p.displayName,
    p.brandName,
    p.price,
    p.stockQuantity,
    p.category,
    p.warranty,
    p.description
FROM product p
WHERE p.id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to prepare statement']);
    exit;
}

$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Product not found']);
    exit;
}

$product = $result->fetch_assoc();

// Fetch all images for this product
$imagesSql = "SELECT id, image_path FROM product_images WHERE product_id = ? ORDER BY id ASC";
$imagesStmt = $conn->prepare($imagesSql);
$imagesStmt->bind_param("i", $productId);
$imagesStmt->execute();
$imagesResult = $imagesStmt->get_result();

$images = [];
while ($imageRow = $imagesResult->fetch_assoc()) {
    $images[] = $imageRow;
}

$product['images'] = $images;

// Return product as JSON
echo json_encode([
    'success' => true,
    'product' => $product
]);

$stmt->close();
$imagesStmt->close();
$conn->close();
?>