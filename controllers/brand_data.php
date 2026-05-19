<?php
/**
 * brand_data.php
 * Returns brands for a given category name (used by the Add Product form dropdown).
 * Queries the `brands` table joined with `categories`.
 */
header('Content-Type: application/json');

include __DIR__ . '/../config/dbconn.php';



$category = trim($_GET['category'] ?? '');
$brands   = [];

if ($category !== '') {
    // Fetch brands whose associated category name matches (case-insensitive)
    $stmt = $conn->prepare("
        SELECT b.brand_name
        FROM brands b
        JOIN categories c ON b.category_id = c.category_id
        WHERE c.category_name = ?
        ORDER BY b.brand_name ASC
    ");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $brands[] = $row['brand_name'];
    }
    $stmt->close();
} else {
    // No category filter — return all brand names
    $result = $conn->query("SELECT DISTINCT brand_name FROM brands ORDER BY brand_name ASC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $brands[] = $row['brand_name'];
        }
    }
}

$conn->close();
echo json_encode(array_values(array_unique($brands)));