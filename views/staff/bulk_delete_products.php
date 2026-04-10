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

    // Create placeholders for prepared statement
    $placeholders = implode(',', array_fill(0, count($ids_array), '?'));
    
    // Prepare the DELETE statement
    $sql = "DELETE FROM product WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Bind parameters dynamically
        $types = str_repeat('i', count($ids_array)); // 'i' for integer
        $stmt->bind_param($types, ...$ids_array);

        // Execute the statement
        if ($stmt->execute()) {
            $deleted_count = $stmt->affected_rows;
            $_SESSION['message'] = "Successfully deleted $deleted_count product(s).";
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