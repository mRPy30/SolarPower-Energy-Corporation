<?php
// ajax_upload_images.php - Upload new images immediately via AJAX and return their data
session_start();
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

require_once '../../config/dbconn.php';

$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

// Verify the product exists
$checkStmt = $conn->prepare("SELECT id FROM product WHERE id = ?");
$checkStmt->bind_param("i", $product_id);
$checkStmt->execute();
if ($checkStmt->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}
$checkStmt->close();

if (!isset($_FILES['new_images']) || empty($_FILES['new_images']['name'][0])) {
    echo json_encode(['success' => false, 'message' => 'No images provided']);
    exit;
}

$uploadDir = "../../uploads/products/{$product_id}/";

// Create directory if it doesn't exist
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$uploadedImages = [];
$fileCount = count($_FILES['new_images']['name']);

for ($i = 0; $i < $fileCount; $i++) {
    if ($_FILES['new_images']['error'][$i] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['new_images']['tmp_name'][$i];
        $originalName = $_FILES['new_images']['name'][$i];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // Validate file type
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'];
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
            $insertStmt = $conn->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)");
            $insertStmt->bind_param("is", $product_id, $relativePath);
            $insertStmt->execute();

            $newImageId = $conn->insert_id;
            $insertStmt->close();

            $uploadedImages[] = [
                'id' => $newImageId,
                'image_path' => $relativePath
            ];
        }
    }
}

if (count($uploadedImages) > 0) {
    echo json_encode([
        'success' => true,
        'message' => count($uploadedImages) . ' image(s) uploaded successfully',
        'images' => $uploadedImages
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'No images were uploaded. Please check file types.']);
}

$conn->close();
?>
