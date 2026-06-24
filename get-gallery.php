<?php
// get-gallery.php - Fetch sub-images or default image for a product
header('Content-Type: application/json; charset=utf-8');

require_once 'config/dbconn.php';

$productId = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$images = [];

if ($productId > 0) {
    // 1. Fetch images from product_images table
    $sql = "SELECT image_path FROM product_images WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $images[] = $row['image_path'];
        }
        $stmt->close();
    }

    // 2. If no sub-images found, get the primary product image
    if (empty($images)) {
        $sql = "SELECT imagePath FROM product WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $images[] = !empty($row['imagePath']) ? $row['imagePath'] : 'assets/img/placeholder.png';
            } else {
                $images[] = 'assets/img/placeholder.png';
            }
            $stmt->close();
        } else {
            $images[] = 'assets/img/placeholder.png';
        }
    }
} else {
    $images[] = 'assets/img/placeholder.png';
}

echo json_encode([
    'success' => true,
    'product_id' => $productId,
    'images' => $images
]);

$conn->close();
?>
