<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../views/login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "solar_power");
if ($conn->connect_error) {
    die("Connection failed");
}

// Create archived_products table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS `archived_products` (
    `archive_id` int(11) NOT NULL AUTO_INCREMENT,
    `original_id` int(11) NOT NULL,
    `displayName` varchar(255) NOT NULL,
    `brandName` varchar(255) NOT NULL,
    `price` decimal(10,2) NOT NULL,
    `category` varchar(50) NOT NULL,
    `stockQuantity` int(11) NOT NULL DEFAULT 0,
    `warranty` varchar(100) DEFAULT NULL,
    `description` text DEFAULT NULL,
    `imagePath` varchar(255) NOT NULL,
    `postedByStaffId` int(11) DEFAULT NULL,
    `deleted_by` int(11) DEFAULT NULL,
    `deleted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

$product_id = intval($_POST['product_id']);
$deleted_by = $_SESSION['user_id'] ?? null;

// Copy product data to archived_products before deleting
$stmt = $conn->prepare("SELECT * FROM product WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if ($product) {
    // Insert into archived_products
    $archive_stmt = $conn->prepare("INSERT INTO archived_products (original_id, displayName, brandName, price, category, stockQuantity, warranty, description, imagePath, postedByStaffId, deleted_by, deleted_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $archive_stmt->bind_param(
        "issdsisssii",
        $product['id'],
        $product['displayName'],
        $product['brandName'],
        $product['price'],
        $product['category'],
        $product['stockQuantity'],
        $product['warranty'],
        $product['description'],
        $product['imagePath'],
        $product['postedByStaffId'],
        $deleted_by
    );
    $archive_stmt->execute();
    $archive_stmt->close();

    // Now delete from product table
    $del_stmt = $conn->prepare("DELETE FROM product WHERE id = ?");
    $del_stmt->bind_param("i", $product_id);
    $del_stmt->execute();
    $del_stmt->close();
}

$conn->close();

header("Location: ../views/staff/dashboard.php#product");
exit;
