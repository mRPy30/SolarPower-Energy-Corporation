<?php
/**
 * Fetch Archived Quotations
 * Include this file in dashboard.php to load archived quotation data.
 * Requires $conn (mysqli connection) to be available.
 */

// Create archived_quotations table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS `archived_quotations` (
    `archive_id` int(11) NOT NULL AUTO_INCREMENT,
    `original_id` int(11) NOT NULL,
    `quotation_number` varchar(10) DEFAULT NULL,
    `client_name` varchar(255) NOT NULL,
    `email` varchar(50) NOT NULL,
    `contact` int(11) DEFAULT NULL,
    `location` varchar(255) DEFAULT NULL,
    `system_type` varchar(50) DEFAULT NULL,
    `kw` decimal(10,2) DEFAULT NULL,
    `officer` varchar(50) DEFAULT NULL,
    `status` varchar(50) DEFAULT NULL,
    `remarks` text DEFAULT NULL,
    `created_by` int(11) DEFAULT NULL,
    `original_created_at` timestamp NULL DEFAULT NULL,
    `deleted_by` int(11) DEFAULT NULL,
    `deleted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

// Fetch all archived quotations
$archived_quotations = [];
$archive_q_result = $conn->query("SELECT * FROM archived_quotations ORDER BY deleted_at DESC");
if ($archive_q_result && $archive_q_result->num_rows > 0) {
    while ($row = $archive_q_result->fetch_assoc()) {
        $archived_quotations[] = $row;
    }
}
$archived_quotation_count = count($archived_quotations);
