<?php
header('Content-Type: application/json');

// Database connection
include "../../config/dbconn.php";

function product_api_fail(string $message, int $statusCode = 500): void
{
    http_response_code($statusCode);
    echo json_encode([
        'success' => false,
        'message' => $message,
        'error' => $message,
    ]);
    exit;
}

function product_api_ensure_column(mysqli $conn, string $table, string $column, string $alterSql): void
{
    $safeTable = str_replace('`', '``', $table);
    $safeColumn = $conn->real_escape_string($column);
    $check = $conn->query("SHOW COLUMNS FROM `{$safeTable}` LIKE '{$safeColumn}'");

    if (!$check) {
        product_api_fail("Unable to inspect {$table}.{$column}: " . $conn->error);
    }

    if ($check->num_rows === 0 && !$conn->query($alterSql)) {
        product_api_fail("Unable to update {$table}.{$column}: " . $conn->error);
    }
}

$conn->query("CREATE TABLE IF NOT EXISTS `product_images` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->query("CREATE TABLE IF NOT EXISTS `product_brand_variants` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NULL,
    `brand_id` INT NULL,
    `variant_name` VARCHAR(255) NOT NULL DEFAULT '',
    `price` DECIMAL(10,2) NULL,
    `variant_image` VARCHAR(255) NULL,
    INDEX (`product_id`),
    INDEX (`brand_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

product_api_ensure_column($conn, 'product', 'packageType', "ALTER TABLE `product` ADD COLUMN `packageType` ENUM('On-Grid','Hybrid','Off-Grid') DEFAULT NULL AFTER `category`");
product_api_ensure_column($conn, 'product', 'moq', "ALTER TABLE `product` ADD COLUMN `moq` INT NOT NULL DEFAULT 1 AFTER `postedByStaffId`");
product_api_ensure_column($conn, 'product', 'status', "ALTER TABLE `product` ADD COLUMN `status` ENUM('Active','Hidden') NOT NULL DEFAULT 'Active' AFTER `moq`");
product_api_ensure_column($conn, 'product_brand_variants', 'variant_name', "ALTER TABLE `product_brand_variants` ADD COLUMN `variant_name` VARCHAR(255) NOT NULL DEFAULT '' AFTER `brand_id`");

// Get product ID from query parameter
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($productId <= 0) {
    product_api_fail('Invalid product ID', 400);
}

// Fetch product details
$sql = "SELECT 
    p.id,
    p.displayName,
    COALESCE(NULLIF(v.brand_names, ''), TRIM(p.brandName)) AS brandName,
    p.price,
    p.stockQuantity,
    p.category,
    p.packageType,
    p.warranty,
    p.description,
    COALESCE(p.moq, 1) AS moq,
    p.status
FROM product p
LEFT JOIN (
    SELECT
        pbv.product_id,
        GROUP_CONCAT(DISTINCT COALESCE(NULLIF(TRIM(b.brand_name), ''), NULLIF(TRIM(sb.brandName), '')) ORDER BY pbv.price ASC, pbv.id ASC SEPARATOR ', ') AS brand_names
    FROM product_brand_variants pbv
    LEFT JOIN supplier_brands sb
        ON pbv.brand_id = sb.id
    LEFT JOIN brands b
        ON pbv.brand_id = b.brand_id
    GROUP BY pbv.product_id
) v
    ON p.id = v.product_id
WHERE p.id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    product_api_fail('Failed to prepare product details query: ' . $conn->error);
}

$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    product_api_fail('Product not found', 404);
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
