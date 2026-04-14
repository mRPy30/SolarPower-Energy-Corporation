<?php
/**
 * Fetch Archived Suppliers
 * Include this file in dashboard.php to load archived supplier data.
 * Requires $conn (mysqli connection) to be available.
 */

// Create archived_suppliers table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS `archived_suppliers` (
    `archive_id` int(11) NOT NULL AUTO_INCREMENT,
    `original_id` int(11) NOT NULL,
    `supplierName` varchar(255) NOT NULL,
    `contactPerson` varchar(255) DEFAULT NULL,
    `email` varchar(100) DEFAULT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `address` text DEFAULT NULL,
    `city` varchar(100) DEFAULT NULL,
    `country` varchar(100) DEFAULT NULL,
    `registrationDate` timestamp NULL DEFAULT NULL,
    `deleted_by` int(11) DEFAULT NULL,
    `deleted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

// Fetch all archived suppliers
$archived_suppliers = [];
$archive_s_result = $conn->query("SELECT * FROM archived_suppliers ORDER BY deleted_at DESC");
if ($archive_s_result && $archive_s_result->num_rows > 0) {
    while ($row = $archive_s_result->fetch_assoc()) {
        $archived_suppliers[] = $row;
    }
}
$archived_supplier_count = count($archived_suppliers);
