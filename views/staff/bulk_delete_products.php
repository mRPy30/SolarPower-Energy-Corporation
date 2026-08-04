<?php
session_start();

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: /login', true, 303);
    exit;
}

include __DIR__ . '/../../config/dbconn.php';

function finish_bulk_archive(string $message, string $type = 'error'): void
{
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type === 'success' ? 'success' : 'error';
    $_SESSION['add_product_msg'] = $message;
    $_SESSION['add_product_msg_type'] = $type === 'success' ? 'success' : 'error';

    header('Location: /dashboard#product', true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    finish_bulk_archive('Invalid archive request.');
}

$productIds = $_POST['product_ids'] ?? '';
$ids = array_values(array_filter(array_map('intval', explode(',', $productIds)), static function ($id) {
    return $id > 0;
}));

if (empty($ids)) {
    finish_bulk_archive('No valid products selected for archive.');
}

$conn->query("CREATE TABLE IF NOT EXISTS `archived_products` (
    `archive_id` int(11) NOT NULL AUTO_INCREMENT,
    `original_id` int(11) NOT NULL,
    `displayName` varchar(255) NOT NULL,
    `brandName` varchar(255) NOT NULL,
    `price` decimal(10,2) NOT NULL,
    `category` varchar(50) NOT NULL,
    `stockQuantity` int(11) NOT NULL DEFAULT 0,
    `warranty` varchar(100) DEFAULT NULL,
    `description` text DEFAULT NULL,
    `imagePath` varchar(255) NOT NULL,
    `postedByStaffId` int(11) DEFAULT NULL,
    `deleted_by` int(11) DEFAULT NULL,
    `deleted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

$deletedBy = $_SESSION['user_id'] ?? null;
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types = str_repeat('i', count($ids));

$conn->begin_transaction();

try {
    $select = $conn->prepare("SELECT * FROM product WHERE id IN ($placeholders)");
    if (!$select) {
        throw new Exception('Unable to prepare product lookup: ' . $conn->error);
    }

    $select->bind_param($types, ...$ids);
    $select->execute();
    $products = $select->get_result();

    $archive = $conn->prepare("INSERT INTO archived_products
        (original_id, displayName, brandName, price, category, stockQuantity, warranty, description, imagePath, postedByStaffId, deleted_by, deleted_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    if (!$archive) {
        throw new Exception('Unable to prepare archive insert: ' . $conn->error);
    }

    $archivedCount = 0;
    while ($product = $products->fetch_assoc()) {
        $archive->bind_param(
            'issdsisssii',
            $product['id'],
            $product['displayName'],
            $product['brandName'],
            $product['price'],
            $product['category'],
            $product['stockQuantity'],
            $product['warranty'],
            $product['description'],
            $product['imagePath'],
            $product['postedByStaffId'],
            $deletedBy
        );

        if (!$archive->execute()) {
            throw new Exception('Unable to archive product: ' . $archive->error);
        }

        $archivedCount++;
    }

    $select->close();
    $archive->close();

    if ($archivedCount === 0) {
        throw new Exception('Selected products were not found.');
    }

    $delete = $conn->prepare("DELETE FROM product WHERE id IN ($placeholders)");
    if (!$delete) {
        throw new Exception('Unable to prepare product delete: ' . $conn->error);
    }

    $delete->bind_param($types, ...$ids);
    if (!$delete->execute()) {
        throw new Exception('Unable to archive selected products: ' . $delete->error);
    }

    $deletedCount = $delete->affected_rows;
    $delete->close();

    $conn->commit();
    $conn->close();

    finish_bulk_archive("Successfully archived {$deletedCount} product(s).", 'success');
} catch (Exception $e) {
    $conn->rollback();
    $conn->close();
    finish_bulk_archive('Archive failed: ' . $e->getMessage());
}
