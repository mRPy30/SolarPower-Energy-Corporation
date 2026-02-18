<?php
/**
 * Archive Product Controller
 * Handles: restore and permanent delete of archived products
 */
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include __DIR__ . "/../config/dbconn.php";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Create archived_products table if it doesn't exist
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

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

// ── RESTORE: move archived product back to product table ──
if ($action === 'restore' && isset($_POST['archive_id'])) {
    $archive_id = intval($_POST['archive_id']);

    // Fetch the archived product
    $stmt = $conn->prepare("SELECT * FROM archived_products WHERE archive_id = ?");
    $stmt->bind_param("i", $archive_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $archived = $result->fetch_assoc();
    $stmt->close();

    if (!$archived) {
        echo json_encode(['success' => false, 'message' => 'Archived product not found.']);
        exit;
    }

    // Re-insert into product table
    $stmt = $conn->prepare("INSERT INTO product (displayName, brandName, price, category, stockQuantity, warranty, description, imagePath, postedByStaffId) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "ssdsisssi",
        $archived['displayName'],
        $archived['brandName'],
        $archived['price'],
        $archived['category'],
        $archived['stockQuantity'],
        $archived['warranty'],
        $archived['description'],
        $archived['imagePath'],
        $archived['postedByStaffId']
    );

    if ($stmt->execute()) {
        // Remove from archive
        $del = $conn->prepare("DELETE FROM archived_products WHERE archive_id = ?");
        $del->bind_param("i", $archive_id);
        $del->execute();
        $del->close();

        echo json_encode(['success' => true, 'message' => 'Product restored successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to restore product.']);
    }
    $stmt->close();
    exit;
}

// ── PERMANENT DELETE: remove from archived_products forever ──
if ($action === 'permanent_delete' && isset($_POST['archive_id'])) {
    $archive_id = intval($_POST['archive_id']);

    $stmt = $conn->prepare("DELETE FROM archived_products WHERE archive_id = ?");
    $stmt->bind_param("i", $archive_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Product permanently deleted.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Archived product not found.']);
    }
    $stmt->close();
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
$conn->close();
