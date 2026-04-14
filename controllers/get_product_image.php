<?php
// get_product_image.php - Fetch the first image for a product
header('Content-Type: application/json');

// Include your database connection
require_once '../config/dbconn.php'; 

// Get product ID from query parameter
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

try {
    // Fetch the first image for this product from product_images table
    $query = "SELECT image_path FROM product_images 
              WHERE product_id = ? 
              ORDER BY id ASC 
              LIMIT 1";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'image_path' => $row['image_path']
        ]);
    } else {
        // No image found in product_images table
        echo json_encode([
            'success' => false,
            'message' => 'No image found'
        ]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>