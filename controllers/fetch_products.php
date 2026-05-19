<?php
// fetch_products.php
require_once __DIR__ . '/../config/dbconn.php';

$result = $conn->query("SELECT * FROM product WHERE status = 'Active'");
$products = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

$conn->close();

echo json_encode($products);
?>