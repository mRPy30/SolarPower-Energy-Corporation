<?php
// fetch_products.php
header('Content-Type: application/json');header('Cache-Control: no-store');
require_once __DIR__ . '/../config/dbconn.php';

$result = $conn->query("SELECT p.*,COALESCE((SELECT MIN(v.price) FROM product_brand_variants v WHERE v.product_id=p.id),p.price) AS price FROM product p WHERE p.status='Active'");
$products = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

$conn->close();

echo json_encode($products);
?>