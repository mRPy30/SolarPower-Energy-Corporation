<?php
/**
 * Returns active brands for the Add Product variant checklist.
 *
 * Query param: ?category=Panel | Panels | Battery | Batteries | Inverter
 * Response:    JSON array of { brand_id, brand_name }
 */

if (ob_get_level() === 0) {
    ob_start();
}

header('Content-Type: application/json');
ini_set('display_errors', '0');
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_OFF);

function supplier_brands_json(array $payload, int $statusCode = 200): void
{
    if (ob_get_length()) {
        ob_clean();
    }

    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

function supplier_brands_clean($value): string
{
    $text = trim((string) $value);
    $text = preg_replace('/[\r\n\t]+/', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    return trim(strip_tags($text));
}

function supplier_brands_category_variants(string $category): array
{
    $category = supplier_brands_clean($category);
    if ($category === '') {
        return [];
    }

    $variants = [$category];
    $lower = strtolower($category);

    if ($lower === 'panels') {
        $variants[] = 'Panel';
    } elseif ($lower === 'batteries') {
        $variants[] = 'Battery';
    } elseif (substr($lower, -1) === 's') {
        $variants[] = substr($category, 0, -1);
    }

    return array_values(array_unique(array_filter($variants)));
}

$category = supplier_brands_clean($_GET['category'] ?? '');
$categoryVariants = supplier_brands_category_variants($category);

if (!$categoryVariants) {
    supplier_brands_json([]);
}

require __DIR__ . '/config/dbconn.php';

if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_errno) {
    supplier_brands_json([
        'success' => false,
        'message' => 'Database connection failed.'
    ], 500);
}

$types = str_repeat('s', count($categoryVariants));

$sql = "
    SELECT DISTINCT b.brand_id, b.brand_name
    FROM brands b
    INNER JOIN categories c ON b.category_id = c.category_id
    WHERE LOWER(c.category_name) IN (
        " . implode(',', array_fill(0, count($categoryVariants), 'LOWER(?)')) . "
    )
    ORDER BY b.brand_name ASC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    $conn->close();
    supplier_brands_json([
        'success' => false,
        'message' => 'Unable to prepare brand lookup.'
    ], 500);
}

$params = [$types];
foreach ($categoryVariants as $index => $variant) {
    $params[] = &$categoryVariants[$index];
}

call_user_func_array([$stmt, 'bind_param'], $params);
$stmt->execute();
$result = $stmt->get_result();

$brands = [];
while ($row = $result->fetch_assoc()) {
    $brands[] = [
        'brand_id' => (int) $row['brand_id'],
        'brand_name' => $row['brand_name'],
    ];
}

$stmt->close();
$conn->close();

supplier_brands_json($brands);
