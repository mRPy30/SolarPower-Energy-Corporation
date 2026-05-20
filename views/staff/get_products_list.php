<?php
header('Content-Type: application/json');

include "../../config/dbconn.php";
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$query = "SELECT id, displayName, brandName, price, stockQuantity, category, packageType, status FROM product ORDER BY id DESC";
$result = $conn->query($query);
$products = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
echo json_encode([
    'success' => true,
    'products' => $products
]);
$conn->close();
?>
