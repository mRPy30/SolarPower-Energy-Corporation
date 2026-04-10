<?php
// brand_data.php
header('Content-Type: application/json');

// Database connection
include "../config/dbconn.php";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    echo json_encode([]);
    exit;
}

$category = trim($_GET['category'] ?? '');

if (empty($category)) {
    echo json_encode([]);
    $conn->close();
    exit;
}

// Fetch brands dynamically from the database via category name
$stmt = $conn->prepare("
    SELECT b.brand_name
    FROM brands b
    INNER JOIN categories c ON b.category_id = c.category_id
    WHERE c.category_name = ?
    ORDER BY b.brand_name ASC
");
$stmt->bind_param("s", $category);
$stmt->execute();
$result = $stmt->get_result();

$brands = [];
while ($row = $result->fetch_assoc()) {
    $brands[] = $row['brand_name'];
}

$stmt->close();
$conn->close();

echo json_encode($brands);
?>