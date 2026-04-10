<?php
// add_product_ajax.php
// Place this in the same folder as dashboard.php (views/staff/)
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

include "../../config/dbconn.php";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed: ' . $conn->connect_error]);
    exit;
}

$user_id       = (int) $_SESSION['user_id'];
$category      = trim($_POST['category']       ?? '');
$brand         = trim($_POST['brand']          ?? '');
$productName   = trim($_POST['product-name']   ?? '');
$warranty      = trim($_POST['warranty']       ?? '');
$price         = (float) ($_POST['price']      ?? 0);
$stockQuantity = (int)   ($_POST['stock-quantity'] ?? 0);
$description   = trim($_POST['description']    ?? '');

// Validate
if (empty($category) || empty($brand) || empty($productName) || $price <= 0 || $stockQuantity < 0) {
    echo json_encode(['success' => false, 'message' => 'Please fill all required fields correctly.']);
    $conn->close();
    exit;
}

$imagePath = 'path/to/uploaded/image.jpg'; // placeholder, real paths stored in product_images

$stmt = $conn->prepare(
    "INSERT INTO product (displayName, brandName, price, category, stockQuantity, warranty, description, imagePath, postedByStaffId)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("ssdsisssi",
    $productName, $brand, $price, $category,
    $stockQuantity, $warranty, $description, $imagePath, $user_id
);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Error inserting product: ' . $stmt->error]);
    $stmt->close();
    $conn->close();
    exit;
}

$product_id = $stmt->insert_id;
$stmt->close();

// ── IMAGE UPLOAD ──────────────────────────────────────────────────────────────
$uploadDir    = "../../uploads/products/$product_id/";
$allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
$maxImages    = 15;
$count        = 0;
$firstImageUrl = null;

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (!empty($_FILES['product-images']['name'][0])) {
    foreach ($_FILES['product-images']['tmp_name'] as $key => $tmpName) {
        if ($count >= $maxImages) break;
        if ($_FILES['product-images']['error'][$key] !== 0) continue;

        $ext = strtolower(pathinfo($_FILES['product-images']['name'][$key], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedTypes)) continue;

        $newName    = uniqid("img_") . "." . $ext;
        $targetPath = $uploadDir . $newName;

        if (move_uploaded_file($tmpName, $targetPath)) {
            $relativePath = "uploads/products/$product_id/$newName";

            $imgStmt = $conn->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)");
            $imgStmt->bind_param("is", $product_id, $relativePath);
            $imgStmt->execute();
            $imgStmt->close();

            if ($count === 0) {
                // Build a web-accessible URL to return to JS
                $firstImageUrl = "../../uploads/products/$product_id/$newName";
            }
            $count++;
        }
    }
}

$conn->close();

echo json_encode([
    'success'     => true,
    'message'     => "Product '{$productName}' added successfully with {$count} image(s)!",
    'product'     => [
        'id'           => $product_id,
        'displayName'  => $productName,
        'brandName'    => $brand,
        'price'        => number_format($price, 2),
        'category'     => $category,
        'stockQuantity'=> $stockQuantity,
        'imageSrc'     => $firstImageUrl ?? '../../assets/img/placeholder.png',
    ]
]);
?>