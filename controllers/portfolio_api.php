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
            $images = json_decode($project['image_url'], true);
            if (is_array($images) && isset($images[$image_index])) {
                $imagePath = $images[$image_index];
                
                // Delete file from disk
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                
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
            $images = json_decode($project['image_url'], true);
            if (is_array($images)) {
                foreach ($images as $img) {
                    if (file_exists($img)) {
                        unlink($img);
                    }
                }
            }
            
            // Delete folder if it exists
            $folderPath = __DIR__ . '/../uploads/portfolio/' . $id;
            if (is_dir($folderPath)) {
                rmdir($folderPath);
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
        $total_files = min(count($_FILES['gallery_images']['name']), 9);
        
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
            $uploaded_images[] = 'uploads/portfolio/' . $id . '/' . $safeName;
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
            $uploaded_images[] = 'uploads/portfolio/' . $id . '/' . $safeName;
        } else {
            $upload_errors[] = "Gallery image " . ($idx + 1) . ": Failed to save file";
        }
    }
    
    // Update database with new images if any were uploaded
    if (!empty($uploaded_images)) {
        $image_url = json_encode($uploaded_images);
        $stmt = $conn->prepare("UPDATE portfolio_projects SET image_url = ? WHERE id = ?");
        $stmt->bind_param("si", $image_url, $id);
        $stmt->execute();
    }

    echo json_encode([
        "status" => "success",
        "message" => "Project saved! " . count($uploaded_images) . " image(s) uploaded.",
        "images_count" => count($uploaded_images),
        "errors" => $upload_errors
    ]);
    exit;
}
?>
