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

    // DELETE Action
    if ($action === 'delete') {
        $id = intval($_POST['id']);
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
    $status = $_POST['status'];

    $image_url = "";
    
    // Handle Multiple Image Uploads if files were provided
    $uploaded_images = [];
    if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
        $total_files = min(count($_FILES['images']['name']), 10); // Limit to 10 images
        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['images']['tmp_name'][$i];
                $fileName = time() . '_' . $i . '_' . preg_replace("/[^a-zA-Z0-9.-]/", "_", $_FILES['images']['name'][$i]);
                $destPath = __DIR__ . '/../uploads/portfolio/' . $fileName;

                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $uploaded_images[] = 'uploads/portfolio/' . $fileName;
                }
            }
        }
    }

    if (!empty($uploaded_images)) {
        $image_url = json_encode($uploaded_images);
    }

    if ($id > 0) {
        // UPDATE
        if ($image_url !== "") {
            $stmt = $conn->prepare("UPDATE portfolio_projects SET project_name=?, subtitle=?, location=?, system_type=?, co2_reduction=?, efficiency_rate=?, status=?, image_url=? WHERE id=?");
            $stmt->bind_param("ssssssssi", $title, $subtitle, $location, $system, $co2, $efficiency, $status, $image_url, $id);
        } else {
            $stmt = $conn->prepare("UPDATE portfolio_projects SET project_name=?, subtitle=?, location=?, system_type=?, co2_reduction=?, efficiency_rate=?, status=? WHERE id=?");
            $stmt->bind_param("sssssssi", $title, $subtitle, $location, $system, $co2, $efficiency, $status, $id);
        }
    } else {
        // INSERT
        // If no image was uploaded, provide a default JSON array
        if ($image_url === "") $image_url = json_encode(['assets/img/projects1.png']);
        
        $stmt = $conn->prepare("INSERT INTO portfolio_projects (project_name, subtitle, location, system_type, co2_reduction, efficiency_rate, status, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $title, $subtitle, $location, $system, $co2, $efficiency, $status, $image_url);
    }

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Project saved!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database save failed: " . $stmt->error]);
    }
    exit;
}
?>
