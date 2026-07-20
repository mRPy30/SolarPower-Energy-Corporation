<?php
session_start();
require_once __DIR__ . "/../config/dbconn.php";

header('Content-Type: application/json');

// Ensure staff auth
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    // If testing without login, you might want to bypass this temporarily, but this is secure
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit;
}

function portfolio_create_image_resource($path, $mimeType, &$errorMessage) {
    switch ($mimeType) {
        case 'image/jpeg':
            if (!function_exists('imagecreatefromjpeg')) {
                $errorMessage = 'JPEG processing is not available on this server.';
                return false;
            }
            return @imagecreatefromjpeg($path);

        case 'image/png':
            if (!function_exists('imagecreatefrompng')) {
                $errorMessage = 'PNG processing is not available on this server.';
                return false;
            }
            return @imagecreatefrompng($path);

        case 'image/webp':
            if (!function_exists('imagecreatefromwebp')) {
                $errorMessage = 'WebP processing is not available on this server.';
                return false;
            }
            return @imagecreatefromwebp($path);

        case 'image/gif':
            if (!function_exists('imagecreatefromgif')) {
                $errorMessage = 'GIF processing is not available on this server.';
                return false;
            }
            return @imagecreatefromgif($path);

        default:
            $errorMessage = 'Unsupported image format.';
            return false;
    }
}

function portfolio_save_image_resource($image, $path, $mimeType, &$errorMessage) {
    switch ($mimeType) {
        case 'image/jpeg':
            if (!function_exists('imagejpeg')) {
                $errorMessage = 'JPEG saving is not available on this server.';
                return false;
            }
            return imagejpeg($image, $path, 90);

        case 'image/png':
            if (!function_exists('imagepng')) {
                $errorMessage = 'PNG saving is not available on this server.';
                return false;
            }
            imagesavealpha($image, true);
            return imagepng($image, $path, 6);

        case 'image/webp':
            if (!function_exists('imagewebp')) {
                $errorMessage = 'WebP saving is not available on this server.';
                return false;
            }
            imagesavealpha($image, true);
            return imagewebp($image, $path, 90);

        case 'image/gif':
            if (!function_exists('imagegif')) {
                $errorMessage = 'GIF saving is not available on this server.';
                return false;
            }
            return imagegif($image, $path);

        default:
            $errorMessage = 'Unsupported image format.';
            return false;
    }
}

function portfolio_apply_opacity($image, $opacityPercent) {
    $opacity = max(0, min(100, $opacityPercent)) / 100;
    $width = imagesx($image);
    $height = imagesy($image);

    imagealphablending($image, false);
    imagesavealpha($image, true);

    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            $rgba = imagecolorsforindex($image, imagecolorat($image, $x, $y));
            $alpha = 127 - (int) round((127 - $rgba['alpha']) * $opacity);
            $alpha = max(0, min(127, $alpha));
            $color = imagecolorallocatealpha($image, $rgba['red'], $rgba['green'], $rgba['blue'], $alpha);
            imagesetpixel($image, $x, $y, $color);
        }
    }
}

function portfolio_apply_center_watermark($imagePath, &$errorMessage) {
    $watermarkPath = __DIR__ . '/../assets/img/icon.png';
    $errorMessage = '';

    if (!extension_loaded('gd')) {
        $errorMessage = 'PHP GD extension is not enabled.';
        return false;
    }

    if (!is_readable($watermarkPath)) {
        $errorMessage = 'Watermark image assets/img/icon.png was not found.';
        return false;
    }

    $imageInfo = @getimagesize($imagePath);
    if (!$imageInfo || empty($imageInfo['mime'])) {
        $errorMessage = 'Uploaded file is not a readable image.';
        return false;
    }

    $watermarkInfo = @getimagesize($watermarkPath);
    if (!$watermarkInfo || empty($watermarkInfo['mime'])) {
        $errorMessage = 'Watermark file is not a readable image.';
        return false;
    }

    $baseImage = portfolio_create_image_resource($imagePath, $imageInfo['mime'], $errorMessage);
    if (!$baseImage) {
        $errorMessage = $errorMessage ?: 'Unable to open uploaded image.';
        return false;
    }

    $watermarkImage = portfolio_create_image_resource($watermarkPath, $watermarkInfo['mime'], $errorMessage);
    if (!$watermarkImage) {
        imagedestroy($baseImage);
        $errorMessage = $errorMessage ?: 'Unable to open watermark image.';
        return false;
    }

    $imageWidth = imagesx($baseImage);
    $imageHeight = imagesy($baseImage);
    $watermarkWidth = imagesx($watermarkImage);
    $watermarkHeight = imagesy($watermarkImage);

    if ($imageWidth <= 0 || $imageHeight <= 0 || $watermarkWidth <= 0 || $watermarkHeight <= 0) {
        imagedestroy($baseImage);
        imagedestroy($watermarkImage);
        $errorMessage = 'Invalid image dimensions.';
        return false;
    }

    $maxWatermarkWidth = max(1, min((int) round($imageWidth * 0.32), 460));
    $maxWatermarkHeight = max(1, min((int) round($imageHeight * 0.46), 540));
    $scale = min($maxWatermarkWidth / $watermarkWidth, $maxWatermarkHeight / $watermarkHeight, 1);
    $targetWidth = max(1, (int) round($watermarkWidth * $scale));
    $targetHeight = max(1, (int) round($watermarkHeight * $scale));

    $resizedWatermark = imagecreatetruecolor($targetWidth, $targetHeight);
    imagealphablending($resizedWatermark, false);
    imagesavealpha($resizedWatermark, true);

    $transparent = imagecolorallocatealpha($resizedWatermark, 0, 0, 0, 127);
    imagefilledrectangle($resizedWatermark, 0, 0, $targetWidth, $targetHeight, $transparent);
    imagecopyresampled(
        $resizedWatermark,
        $watermarkImage,
        0,
        0,
        0,
        0,
        $targetWidth,
        $targetHeight,
        $watermarkWidth,
        $watermarkHeight
    );

    portfolio_apply_opacity($resizedWatermark, 28);

    imagealphablending($baseImage, true);
    if ($imageInfo['mime'] === 'image/png' || $imageInfo['mime'] === 'image/webp') {
        imagesavealpha($baseImage, true);
    }

    $x = (int) round(($imageWidth - $targetWidth) / 2);
    $y = (int) round(($imageHeight - $targetHeight) / 2);
    imagecopy($baseImage, $resizedWatermark, $x, $y, 0, 0, $targetWidth, $targetHeight);

    $saved = portfolio_save_image_resource($baseImage, $imagePath, $imageInfo['mime'], $errorMessage);

    imagedestroy($baseImage);
    imagedestroy($watermarkImage);
    imagedestroy($resizedWatermark);

    if (!$saved) {
        $errorMessage = $errorMessage ?: 'Unable to save watermarked image.';
        return false;
    }

    return true;
}

function portfolio_decode_image_list($imageUrl) {
    $images = json_decode($imageUrl ?? '', true);
    if (!is_array($images)) {
        $images = !empty($imageUrl) ? [$imageUrl] : [];
    }

    return array_values(array_filter(array_map(function ($image) {
        return is_string($image) ? trim($image) : '';
    }, $images)));
}

function portfolio_delete_uploaded_image($imagePath) {
    $normalizedPath = str_replace('\\', '/', trim($imagePath));
    if (strpos($normalizedPath, 'uploads/portfolio/') !== 0) {
        return false;
    }

    $portfolioRoot = realpath(__DIR__ . '/../uploads/portfolio');
    $fullPath = realpath(__DIR__ . '/../' . $normalizedPath);

    if (!$portfolioRoot || !$fullPath || !is_file($fullPath)) {
        return false;
    }

    $portfolioRoot = rtrim(str_replace('\\', '/', $portfolioRoot), '/');
    $fullPathNormalized = str_replace('\\', '/', $fullPath);

    if (strpos($fullPathNormalized, $portfolioRoot . '/') !== 0) {
        return false;
    }

    return @unlink($fullPath);
}

$method = $_SERVER['REQUEST_METHOD'];

// Handle GET requests (Fetch all projects)
if ($method === 'GET') {
    $result = mysqli_query($conn, "SELECT * FROM portfolio_projects ORDER BY created_at DESC");
    $projects = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $projects[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $projects]);
    exit;
}

// Handle POST requests (Insert, Update, Delete)
if ($method === 'POST') {
    $action = $_POST['action'] ?? '';

    // DELETE IMAGE Action
    if ($action === 'delete_image') {
        $project_id = intval($_POST['project_id']);
        $image_index = intval($_POST['image_index']);
        
        $result = mysqli_query($conn, "SELECT image_url FROM portfolio_projects WHERE id = $project_id");
        $project = mysqli_fetch_assoc($result);
        
        if ($project) {
            $images = portfolio_decode_image_list($project['image_url']);
            if (isset($images[$image_index])) {
                $imagePath = $images[$image_index];
                
                // Delete file from disk
                portfolio_delete_uploaded_image($imagePath);
                
                // Remove from array
                array_splice($images, $image_index, 1);
                
                // Update database
                $newImageUrl = json_encode($images);
                $stmt = $conn->prepare("UPDATE portfolio_projects SET image_url = ? WHERE id = ?");
                $stmt->bind_param("si", $newImageUrl, $project_id);
                
                if ($stmt->execute()) {
                    echo json_encode(["status" => "success", "message" => "Image deleted"]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Failed to update database"]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Image not found"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Project not found"]);
        }
        exit;
    }

    // DELETE PROJECT Action
    if ($action === 'delete') {
        $id = intval($_POST['id']);
        
        // Get project to delete its folder
        $result = mysqli_query($conn, "SELECT image_url FROM portfolio_projects WHERE id = $id");
        $project = mysqli_fetch_assoc($result);
        
        if ($project) {
            $images = portfolio_decode_image_list($project['image_url']);
            foreach ($images as $img) {
                portfolio_delete_uploaded_image($img);
            }
            
            // Delete folder if it exists
            $folderPath = __DIR__ . '/../uploads/portfolio/' . $id;
            if (is_dir($folderPath)) {
                @rmdir($folderPath);
            }
        }
        
        $stmt = $conn->prepare("DELETE FROM portfolio_projects WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Project deleted."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database error."]);
        }
        exit;
    }

    // INSERT or UPDATE Action
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $title = $_POST['project_name'];
    $subtitle = $_POST['subtitle'];
    $location = $_POST['location'];
    $system = $_POST['system_type'];
    $co2 = $_POST['co2_reduction'];
    $efficiency = $_POST['efficiency_rate'];
    $service_type = $_POST['service_type'] ?? $_POST['status'] ?? 'Supply and Install';

    $image_url = "";
    $upload_errors = [];
    $existing_images = [];

    if ($id > 0) {
        $existingImageUrl = '';
        $existingStmt = $conn->prepare("SELECT image_url FROM portfolio_projects WHERE id = ?");
        $existingStmt->bind_param("i", $id);
        $existingStmt->execute();
        $existingStmt->bind_result($existingImageUrl);

        if (!$existingStmt->fetch()) {
            echo json_encode(["status" => "error", "message" => "Project not found."]);
            exit;
        }
        $existingStmt->close();

        $existing_images = portfolio_decode_image_list($existingImageUrl);
    }
    
    // Handle Main Image Upload
    $main_image_path = '';
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['main_image']['tmp_name'];
        $fileName = $_FILES['main_image']['name'];
        $fileSize = $_FILES['main_image']['size'];
        
        // Validate file size (20MB max)
        if ($fileSize > 20 * 1024 * 1024) {
            $upload_errors[] = "Main image exceeds 20MB limit";
        } else {
            // Validate file type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $fileTmpPath);
            finfo_close($finfo);
            
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (!in_array($mimeType, $allowedMimes)) {
                $upload_errors[] = "Main image: Invalid file type. Only JPG, PNG, WebP, GIF allowed.";
            } else {
                $main_image_path = true; // Mark for processing
            }
        }
    }
    
    // Handle Gallery Images Upload
    $gallery_images = [];
    if (isset($_FILES['gallery_images']) && is_array($_FILES['gallery_images']['name'])) {
        $galleryFileCount = count($_FILES['gallery_images']['name']);
        if ($galleryFileCount > 9) {
            $upload_errors[] = "You can upload up to 9 gallery images only.";
        }

        $total_files = min($galleryFileCount, 9);
        
        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['gallery_images']['error'][$i] !== UPLOAD_ERR_OK) {
                $errorCode = $_FILES['gallery_images']['error'][$i];
                if ($errorCode != UPLOAD_ERR_NO_FILE) {
                    $upload_errors[] = "Gallery image " . ($i + 1) . ": Upload error";
                }
                continue;
            }
            
            $fileTmpPath = $_FILES['gallery_images']['tmp_name'][$i];
            $fileName = $_FILES['gallery_images']['name'][$i];
            $fileSize = $_FILES['gallery_images']['size'][$i];
            
            if ($fileSize > 20 * 1024 * 1024) {
                $upload_errors[] = "Gallery image " . ($i + 1) . ": Exceeds 20MB limit";
                continue;
            }
            
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $fileTmpPath);
            finfo_close($finfo);
            
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (!in_array($mimeType, $allowedMimes)) {
                $upload_errors[] = "Gallery image " . ($i + 1) . ": Invalid file type";
                continue;
            }
            
            $gallery_images[] = [
                'tmp' => $fileTmpPath,
                'name' => $fileName
            ];
        }
    }

    $estimatedImageCount = $id > 0 ? count($existing_images) : 0;
    if ($main_image_path && $estimatedImageCount === 0) {
        $estimatedImageCount = 1;
    }
    $estimatedImageCount += count($gallery_images);

    if ($estimatedImageCount > 10) {
        $upload_errors[] = "Project images exceed the 10-image limit. Delete some current images first, then upload again.";
    }

    // Save to database first if updating, so we have an ID
    if ($id > 0) {
        // UPDATE - only update text fields first
        $stmt = $conn->prepare("UPDATE portfolio_projects SET project_name=?, subtitle=?, location=?, system_type=?, co2_reduction=?, efficiency_rate=?, service_type=? WHERE id=?");
        $stmt->bind_param("sssssssi", $title, $subtitle, $location, $system, $co2, $efficiency, $service_type, $id);
        $stmt->execute();
    } else {
        // INSERT - set default image first
        $default_image = json_encode(['assets/img/projects1.png']);
        $stmt = $conn->prepare("INSERT INTO portfolio_projects (project_name, subtitle, location, system_type, co2_reduction, efficiency_rate, service_type, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $title, $subtitle, $location, $system, $co2, $efficiency, $service_type, $default_image);
        $stmt->execute();
        $id = $conn->insert_id;
    }
    
    // Now process image uploads with the project ID
    $uploaded_images = [];
    $uploaded_main_image = '';
    $uploaded_gallery_images = [];

    if (empty($upload_errors)) {
        // Create project-specific folder
        $uploadDir = __DIR__ . '/../uploads/portfolio/' . $id . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Process main image
        if ($main_image_path && isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['main_image']['tmp_name'];
            $fileName = $_FILES['main_image']['name'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $safeName = 'main_' . time() . '.' . $fileExt;
            $destPath = $uploadDir . $safeName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $watermarkError = '';
                if (portfolio_apply_center_watermark($destPath, $watermarkError)) {
                    $uploaded_main_image = 'uploads/portfolio/' . $id . '/' . $safeName;
                    $uploaded_images[] = $uploaded_main_image;
                } else {
                    @unlink($destPath);
                    $upload_errors[] = "Main image watermark failed: " . $watermarkError;
                }
            } else {
                $upload_errors[] = "Main image: Failed to save file";
            }
        }

        // Process gallery images
        foreach ($gallery_images as $idx => $gal) {
            $fileTmpPath = $gal['tmp'];
            $fileName = $gal['name'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $safeName = 'gallery_' . ($idx + 1) . '_' . time() . '.' . $fileExt;
            $destPath = $uploadDir . $safeName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $watermarkError = '';
                if (portfolio_apply_center_watermark($destPath, $watermarkError)) {
                    $uploadedGalleryImage = 'uploads/portfolio/' . $id . '/' . $safeName;
                    $uploaded_gallery_images[] = $uploadedGalleryImage;
                    $uploaded_images[] = $uploadedGalleryImage;
                } else {
                    @unlink($destPath);
                    $upload_errors[] = "Gallery image " . ($idx + 1) . " watermark failed: " . $watermarkError;
                }
            } else {
                $upload_errors[] = "Gallery image " . ($idx + 1) . ": Failed to save file";
            }
        }
    }

    if (!empty($upload_errors)) {
        foreach ($uploaded_images as $uploadedImage) {
            portfolio_delete_uploaded_image($uploadedImage);
        }
        $uploaded_images = [];
        $uploaded_main_image = '';
        $uploaded_gallery_images = [];
    }

    // Merge new uploads with current images instead of replacing the whole image list.
    if (!empty($uploaded_main_image) || !empty($uploaded_gallery_images)) {
        $oldMainImage = $existing_images[0] ?? '';
        if (!empty($uploaded_main_image)) {
            $final_images = array_merge([$uploaded_main_image], array_slice($existing_images, 1), $uploaded_gallery_images);
        } else {
            $final_images = array_merge($existing_images, $uploaded_gallery_images);
        }

        $final_images = array_values(array_slice(array_unique($final_images), 0, 10));
        $image_url = json_encode($final_images);
        $stmt = $conn->prepare("UPDATE portfolio_projects SET image_url = ? WHERE id = ?");
        $stmt->bind_param("si", $image_url, $id);

        if ($stmt->execute()) {
            if (!empty($uploaded_main_image) && !empty($oldMainImage) && $oldMainImage !== $uploaded_main_image) {
                portfolio_delete_uploaded_image($oldMainImage);
            }
        } else {
            foreach ($uploaded_images as $uploadedImage) {
                portfolio_delete_uploaded_image($uploadedImage);
            }
            $uploaded_images = [];
            $upload_errors[] = "Failed to update project images.";
        }
    }

    $hasUploadErrors = !empty($upload_errors);
    $message = $hasUploadErrors
        ? "Project details were saved, but image upload was not completed. " . implode(" ", $upload_errors)
        : "Project saved! " . count($uploaded_images) . " image(s) uploaded.";

    echo json_encode([
        "status" => $hasUploadErrors ? "error" : "success",
        "message" => $message,
        "images_count" => count($uploaded_images),
        "errors" => $upload_errors
    ]);
    exit;
}
?>
