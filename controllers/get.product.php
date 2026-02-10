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
    SELECT id, displayName, brandName, price, category, stockQuantity, description
    FROM product
    WHERE id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($product = $result->fetch_assoc()) {
    echo json_encode($product);
} else {
    echo json_encode(['error' => 'Product not found']);
}

$stmt->close();
$conn->close();
