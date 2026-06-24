<?php
/**
 * get-supplier-brands.php
 * Returns active brands for a given product category from the `brands` table.
 * Joins with `categories` to match by category_name.
 *
 * Query param: ?category=Panel | Battery | Inverter  (case-insensitive partial match)
 * Response:    JSON array of { brand_id, brand_name }
 */
header('Content-Type: application/json');
include 'config/dbconn.php';

$category = trim($_GET['category'] ?? '');
$brands   = [];

if (!empty($category)) {
    /*
     * The `product.category` column stores values like 'Panel', 'Battery', 'Inverter'.
     * The `categories.category_name` column stores the canonical names.
     * We join brands → categories and do a case-insensitive match so that
     * passing 'Panel' or 'Panels' or 'panel' all work correctly.
     */
    $stmt = $conn->prepare("
        SELECT b.brand_id, b.brand_name
        FROM   brands b
        JOIN   categories c ON b.category_id = c.category_id
        WHERE  LOWER(c.category_name) = LOWER(?)
        ORDER  BY b.brand_name ASC
    ");

    if ($stmt) {
        $stmt->bind_param("s", $category);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $brands[] = $row;
        }
        $stmt->close();
    }
}

$conn->close();
echo json_encode($brands);
