<?php
/**
 * Archive Supplier Controller
 * Handles: restore and permanent delete of archived suppliers
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

// Create archived_suppliers table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS `archived_suppliers` (
    `archive_id` int(11) NOT NULL AUTO_INCREMENT,
    `original_id` int(11) NOT NULL,
    `supplierName` varchar(255) NOT NULL,
    `contactPerson` varchar(255) DEFAULT NULL,
    `email` varchar(100) DEFAULT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `address` text DEFAULT NULL,
    `city` varchar(100) DEFAULT NULL,
    `country` varchar(100) DEFAULT NULL,
    `registrationDate` timestamp NULL DEFAULT NULL,
    `deleted_by` int(11) DEFAULT NULL,
    `deleted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

// ── RESTORE: move archived supplier back to supplier table ──
if ($action === 'restore' && isset($_POST['archive_id'])) {
    $archive_id = intval($_POST['archive_id']);

    $stmt = $conn->prepare("SELECT * FROM archived_suppliers WHERE archive_id = ?");
    $stmt->bind_param("i", $archive_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $archived = $result->fetch_assoc();
    $stmt->close();

    if (!$archived) {
        echo json_encode(['success' => false, 'message' => 'Archived supplier not found.']);
        exit;
    }

    // Re-insert into supplier table
    $stmt = $conn->prepare("INSERT INTO supplier (supplierName, contactPerson, email, phone, address, city, country, registrationDate) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "ssssssss",
        $archived['supplierName'],
        $archived['contactPerson'],
        $archived['email'],
        $archived['phone'],
        $archived['address'],
        $archived['city'],
        $archived['country'],
        $archived['registrationDate']
    );

    if ($stmt->execute()) {
        $del = $conn->prepare("DELETE FROM archived_suppliers WHERE archive_id = ?");
        $del->bind_param("i", $archive_id);
        $del->execute();
        $del->close();

        echo json_encode(['success' => true, 'message' => 'Supplier restored successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to restore supplier.']);
    }
    $stmt->close();
    exit;
}

// ── PERMANENT DELETE: remove from archived_suppliers forever ──
if ($action === 'permanent_delete' && isset($_POST['archive_id'])) {
    $archive_id = intval($_POST['archive_id']);

    $stmt = $conn->prepare("DELETE FROM archived_suppliers WHERE archive_id = ?");
    $stmt->bind_param("i", $archive_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Supplier permanently deleted.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Archived supplier not found.']);
    }
    $stmt->close();
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
$conn->close();
