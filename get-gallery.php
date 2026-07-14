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
    // 3. Fetch variant images
    $vSql = "SELECT variant_image as imagePath FROM product_brand_variants WHERE product_id = ?";
    $vStmt = $conn->prepare($vSql);
    if ($vStmt) {
        $vStmt->bind_param("i", $productId);
        $vStmt->execute();
        $vResult = $vStmt->get_result();
        while ($vRow = $vResult->fetch_assoc()) {
            if (!empty($vRow['imagePath'])) {
                $images[] = $vRow['imagePath'];
            }
        }
        $vStmt->close();
    }
    
    // Also fetch images from other products sharing same displayName
    $sqlName = "SELECT imagePath FROM product WHERE displayName = (SELECT displayName FROM product WHERE id = ?) AND status = 'Active'";
    $nStmt = $conn->prepare($sqlName);
    if ($nStmt) {
        $nStmt->bind_param("i", $productId);
        $nStmt->execute();
        $nResult = $nStmt->get_result();
        while ($nRow = $nResult->fetch_assoc()) {
            if (!empty($nRow['imagePath'])) {
                $images[] = $nRow['imagePath'];
            }
        }
        $nStmt->close();
    }

    $images = array_values(array_unique(array_filter($images)));
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
