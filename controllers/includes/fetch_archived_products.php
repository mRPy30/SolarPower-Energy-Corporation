<?php
/**
 * Fetch Archived Products
 * Include this file in dashboard.php to load archived product data.
 * Requires $conn (mysqli connection) to be available.
 */

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

// Fetch all archived products
$archived_products = [];
$archive_result = $conn->query("SELECT * FROM archived_products ORDER BY deleted_at DESC");
if ($archive_result && $archive_result->num_rows > 0) {
    while ($row = $archive_result->fetch_assoc()) {
        $archived_products[] = $row;
    }
}
$archived_product_count = count($archived_products);
