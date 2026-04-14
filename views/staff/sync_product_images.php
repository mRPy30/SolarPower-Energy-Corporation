<?php
/**
 * Utility script to sync product images from filesystem to database
 * This will scan the uploads/products folder and insert missing records into product_images table
 * 
 * Run this script once to fix missing image records
 * Access: yoursite.com/views/staff/sync_product_images.php
 */

session_start();

// Security check - only allow logged in staff
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    die('Unauthorized. Please login first.');
}

require_once '../../config/dbconn.php';

echo "<h2>Product Images Sync Utility</h2>";
echo "<pre>";

$uploadsDir = '../../uploads/products/';
$syncedCount = 0;
$skippedCount = 0;
$errorCount = 0;

// Get all product folders
$productFolders = glob($uploadsDir . '*', GLOB_ONLYDIR);

foreach ($productFolders as $folder) {
    $productId = basename($folder);
    
    // Check if product exists in database
    $checkProduct = $conn->prepare("SELECT id FROM product WHERE id = ?");
    $checkProduct->bind_param("i", $productId);
    $checkProduct->execute();
    $productResult = $checkProduct->get_result();
    
    if ($productResult->num_rows === 0) {
        echo "⚠️ Product ID $productId not found in database, skipping...\n";
        $skippedCount++;
        continue;
    }
    
    // Get all images in this folder
    $images = glob($folder . '/*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE);
    
    foreach ($images as $imagePath) {
        $filename = basename($imagePath);
        $relativePath = "uploads/products/$productId/$filename";
        
        // Check if this image already exists in product_images
        $checkImage = $conn->prepare("SELECT id FROM product_images WHERE product_id = ? AND image_path = ?");
        $checkImage->bind_param("is", $productId, $relativePath);
        $checkImage->execute();
        $imageResult = $checkImage->get_result();
        
        if ($imageResult->num_rows === 0) {
            // Image not in database, insert it
            $insertImage = $conn->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)");
            $insertImage->bind_param("is", $productId, $relativePath);
            
            if ($insertImage->execute()) {
                echo "✅ Synced: Product $productId - $filename\n";
                $syncedCount++;
            } else {
                echo "❌ Error syncing: Product $productId - $filename - " . $conn->error . "\n";
                $errorCount++;
            }
            $insertImage->close();
        } else {
            // Image already exists
            $skippedCount++;
        }
        $checkImage->close();
    }
    $checkProduct->close();
}

echo "\n";
echo "========================================\n";
echo "Sync Complete!\n";
echo "✅ Synced: $syncedCount images\n";
echo "⏭️ Skipped: $skippedCount (already exist or no product)\n";
echo "❌ Errors: $errorCount\n";
echo "========================================\n";
echo "</pre>";

echo "<p><a href='dashboard.php'>← Back to Dashboard</a></p>";

$conn->close();
?>
