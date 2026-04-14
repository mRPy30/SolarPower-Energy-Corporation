<?php
// edit_product.php - Update product details and manage images
header('Content-Type: application/json');

// Include your database connection
require_once '../../config/dbconn.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get form data
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$displayName = isset($_POST['displayName']) ? trim($_POST['displayName']) : '';
$brandName = isset($_POST['brandName']) ? trim($_POST['brandName']) : '';
$price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
$category = isset($_POST['category']) ? trim($_POST['category']) : '';
$stockQuantity = isset($_POST['stockQuantity']) ? intval($_POST['stockQuantity']) : 0;
$warranty = isset($_POST['warranty']) ? trim($_POST['warranty']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$delete_images = isset($_POST['delete_images']) ? $_POST['delete_images'] : '';

// Validate required fields
if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

if (empty($displayName) || empty($brandName) || $price <= 0 || empty($category)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();
    
    // 1. Update product details
    $query = "UPDATE product 
              SET displayName = ?, 
                  brandName = ?, 
                  price = ?, 
                  category = ?, 
                  stockQuantity = ?, 
                  warranty = ?, 
                  description = ?
              WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssdsissi", $displayName, $brandName, $price, $category, $stockQuantity, $warranty, $description, $product_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update product details');
    }
    
    // 2. Delete marked images
    if (!empty($delete_images)) {
        $imageIds = explode(',', $delete_images);
        foreach ($imageIds as $imageId) {
            $imageId = intval($imageId);
            if ($imageId > 0) {
                // Get image path before deleting from database
                $getImageQuery = "SELECT image_path FROM product_images WHERE id = ? AND product_id = ?";
                $getImageStmt = $conn->prepare($getImageQuery);
                $getImageStmt->bind_param("ii", $imageId, $product_id);
                $getImageStmt->execute();
                $imageResult = $getImageStmt->get_result();
                
                if ($imageRow = $imageResult->fetch_assoc()) {
                    $imagePath = '../../' . $imageRow['image_path'];
                    
                    // Delete from database
                    $deleteQuery = "DELETE FROM product_images WHERE id = ? AND product_id = ?";
                    $deleteStmt = $conn->prepare($deleteQuery);
                    $deleteStmt->bind_param("ii", $imageId, $product_id);
                    $deleteStmt->execute();
                    
                    // Delete physical file
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
            }
        }
    }
    
    // 3. Upload new images
    if (isset($_FILES['new_images']) && !empty($_FILES['new_images']['name'][0])) {
        $uploadDir = "../../uploads/products/{$product_id}/";
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileCount = count($_FILES['new_images']['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES['new_images']['error'][$i] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['new_images']['tmp_name'][$i];
                $originalName = $_FILES['new_images']['name'][$i];
                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                
                // Validate file type
                $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($extension, $allowedTypes)) {
                    continue;
                }
                
                // Generate unique filename
                $newFileName = 'img_' . uniqid() . '.' . $extension;
                $destination = $uploadDir . $newFileName;
                
                // Move uploaded file
                if (move_uploaded_file($tmpName, $destination)) {
                    // Save to database
                    $relativePath = "uploads/products/{$product_id}/{$newFileName}";
                    $insertImageQuery = "INSERT INTO product_images (product_id, image_path) VALUES (?, ?)";
                    $insertImageStmt = $conn->prepare($insertImageQuery);
                    $insertImageStmt->bind_param("is", $product_id, $relativePath);
                    $insertImageStmt->execute();
                }
            }
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>