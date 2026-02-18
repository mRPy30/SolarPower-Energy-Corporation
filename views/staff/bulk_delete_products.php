<?php
// bulk_delete_products.php
// Save this file in: views/staff/bulk_delete_products.php

session_start();

// Database connection
include "../../config/dbconn.php";

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_ids'])) {
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        $_SESSION['message'] = "Connection failed: " . $conn->connect_error;
        $_SESSION['message_type'] = "error";
        header("Location: dashboard.php");
        exit;
    }

    // Get the comma-separated product IDs
    $product_ids = $_POST['product_ids'];
    
    // Validate and sanitize the IDs
    $ids_array = explode(',', $product_ids);
    $ids_array = array_map('intval', $ids_array); // Convert to integers
    $ids_array = array_filter($ids_array, function($id) { return $id > 0; }); // Remove invalid IDs

    if (empty($ids_array)) {
        $_SESSION['message'] = "No valid products selected for deletion.";
        $_SESSION['message_type'] = "error";
        header("Location: dashboard.php");
        exit;
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

    $deleted_by = $_SESSION['user_id'] ?? null;

    // Create placeholders for prepared statement
    $placeholders = implode(',', array_fill(0, count($ids_array), '?'));
    
    // First, copy all products to archived_products
    $types = str_repeat('i', count($ids_array));
    $fetch_sql = "SELECT * FROM product WHERE id IN ($placeholders)";
    $fetch_stmt = $conn->prepare($fetch_sql);
    $fetch_stmt->bind_param($types, ...$ids_array);
    $fetch_stmt->execute();
    $fetch_result = $fetch_stmt->get_result();

    $archived_count = 0;
    while ($product = $fetch_result->fetch_assoc()) {
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
        $archived_count++;
    }
    $fetch_stmt->close();

    // Now delete from product table
    $sql = "DELETE FROM product WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Bind parameters dynamically
        $stmt->bind_param($types, ...$ids_array);

        // Execute the statement
        if ($stmt->execute()) {
            $deleted_count = $stmt->affected_rows;
            $_SESSION['message'] = "Successfully archived and deleted $deleted_count product(s).";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error deleting products: " . $stmt->error;
            $_SESSION['message_type'] = "error";
        }

        $stmt->close();
    } else {
        $_SESSION['message'] = "Error preparing statement: " . $conn->error;
        $_SESSION['message_type'] = "error";
    }

    $conn->close();
} else {
    $_SESSION['message'] = "Invalid request.";
    $_SESSION['message_type'] = "error";
}

// Redirect back to dashboard
header("Location: dashboard.php");
exit;
?>