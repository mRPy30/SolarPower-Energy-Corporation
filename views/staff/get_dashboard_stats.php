<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// Include database connection
include "../../config/dbconn.php";

header('Content-Type: application/json');

// Create database connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

// Function to get statistics
function get_stats($conn) {
    $stats = [
        'clients' => 0,
        'products' => 0,
        'orders' => 0,
        'suppliers' => 0,
    ];

    $result = $conn->query("SELECT COUNT(*) FROM client");
    if ($result) {
        $stats['clients'] = $result->fetch_row()[0];
        $result->close();
    }

    $result = $conn->query("SELECT COUNT(*) FROM product");
    if ($result) {
        $stats['products'] = $result->fetch_row()[0];
        $result->close();
    }

    $result = $conn->query("SELECT COUNT(*) FROM orders");
    if ($result) {
        $stats['orders'] = $result->fetch_row()[0];
        $result->close();
    }
    
    $result = $conn->query("SELECT COUNT(*) FROM supplier");
    if ($result) {
        $stats['suppliers'] = $result->fetch_row()[0];
        $result->close();
    }

    return $stats;
}

// Function to get recent orders
function get_recent_orders($conn) {
    $orders = [];
    
    // Join orders with order_items to get at least one product name for display
    $query = "SELECT 
                o.id,
                o.order_reference,
                o.order_status,
                o.customer_name,
                (SELECT product_name FROM order_items WHERE order_id = o.id LIMIT 1) as product_name
              FROM orders o
              ORDER BY o.created_at DESC
              LIMIT 5";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        $result->close();
    }
    
    return $orders;
}

// Function to get most sold product
function get_most_sold_product($conn) {
    $query = "SELECT 
                product_name as productName,
                SUM(quantity) as totalSold
              FROM order_items oi
              JOIN orders o ON oi.order_id = o.id
              WHERE o.order_status = 'delivered'
              GROUP BY product_id
              ORDER BY totalSold DESC
              LIMIT 1";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $result->close();
        return $product;
    }
    
    return null;
}

// Get all data
$stats = get_stats($conn);
$recent_orders = get_recent_orders($conn);
$most_sold_product = get_most_sold_product($conn);

$conn->close();

// Return JSON response
echo json_encode([
    'success' => true,
    'stats' => $stats,
    'recent_orders' => $recent_orders,
    'most_sold_product' => $most_sold_product
]);
?>