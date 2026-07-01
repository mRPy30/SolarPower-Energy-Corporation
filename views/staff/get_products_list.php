<?php
header('Content-Type: application/json');

include "../../config/dbconn.php";
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$query = "SELECT
    p.id,
    p.displayName,
    COALESCE(NULLIF(v.brand_names, ''), TRIM(p.brandName)) AS brandName,
    p.price,
    p.stockQuantity,
    p.category,
    p.packageType,
    p.status
FROM product p
LEFT JOIN (
        SELECT
        pbv.product_id,
        GROUP_CONCAT(DISTINCT COALESCE(NULLIF(TRIM(sb.brandName), ''), NULLIF(TRIM(b.brand_name), '')) ORDER BY pbv.price ASC, pbv.id ASC SEPARATOR ', ') AS brand_names
    FROM product_brand_variants pbv
    LEFT JOIN supplier_brands sb
        ON pbv.brand_id = sb.id
    LEFT JOIN brands b
        ON pbv.brand_id = b.brand_id
    GROUP BY pbv.product_id
) v
    ON p.id = v.product_id
ORDER BY p.id DESC";
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
