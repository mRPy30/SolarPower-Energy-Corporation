<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$conn = new mysqli("localhost", "root", "", "solar_power");
if ($conn->connect_error) {
    echo json_encode(['error' => 'DB connection failed']);
    exit;
}

$id = intval($_GET['id'] ?? 0);

$stmt = $conn->prepare("
    SELECT id, displayName, brandName, price, category, stockQuantity, warranty, description
    FROM product
    WHERE id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($product = $result->fetch_assoc()) {
    // Fetch product images
    $imgStmt = $conn->prepare("
        SELECT id, image_path 
        FROM product_images 
        WHERE product_id = ? 
        ORDER BY id ASC
    ");
    $imgStmt->bind_param("i", $id);
    $imgStmt->execute();
    $imgResult = $imgStmt->get_result();
    
    $images = [];
    while ($img = $imgResult->fetch_assoc()) {
        $images[] = $img;
    }
    $imgStmt->close();
    
    $product['images'] = $images;
    
    echo json_encode(['success' => true, 'product' => $product]);
} else {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
}

$stmt->close();
$conn->close();
