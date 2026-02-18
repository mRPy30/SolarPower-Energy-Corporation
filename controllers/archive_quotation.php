<?php
/**
 * Archive Quotation Controller
 * Handles: restore and permanent delete of archived quotations
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

// Create archived_quotations table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS `archived_quotations` (
    `archive_id` int(11) NOT NULL AUTO_INCREMENT,
    `original_id` int(11) NOT NULL,
    `quotation_number` varchar(10) DEFAULT NULL,
    `client_name` varchar(255) NOT NULL,
    `email` varchar(50) NOT NULL,
    `contact` int(11) DEFAULT NULL,
    `location` varchar(255) DEFAULT NULL,
    `system_type` varchar(50) DEFAULT NULL,
    `kw` decimal(10,2) DEFAULT NULL,
    `officer` varchar(50) DEFAULT NULL,
    `status` varchar(50) DEFAULT NULL,
    `remarks` text DEFAULT NULL,
    `created_by` int(11) DEFAULT NULL,
    `original_created_at` timestamp NULL DEFAULT NULL,
    `deleted_by` int(11) DEFAULT NULL,
    `deleted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

// ── RESTORE: move archived quotation back to quotations table ──
if ($action === 'restore' && isset($_POST['archive_id'])) {
    $archive_id = intval($_POST['archive_id']);

    $stmt = $conn->prepare("SELECT * FROM archived_quotations WHERE archive_id = ?");
    $stmt->bind_param("i", $archive_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $archived = $result->fetch_assoc();
    $stmt->close();

    if (!$archived) {
        echo json_encode(['success' => false, 'message' => 'Archived quotation not found.']);
        exit;
    }

    // Re-insert into quotations table
    $stmt = $conn->prepare("INSERT INTO quotations (quotation_number, client_name, email, contact, location, system_type, kw, officer, status, remarks, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "sssissdsssss",
        $archived['quotation_number'],
        $archived['client_name'],
        $archived['email'],
        $archived['contact'],
        $archived['location'],
        $archived['system_type'],
        $archived['kw'],
        $archived['officer'],
        $archived['status'],
        $archived['remarks'],
        $archived['created_by'],
        $archived['original_created_at']
    );

    if ($stmt->execute()) {
        $del = $conn->prepare("DELETE FROM archived_quotations WHERE archive_id = ?");
        $del->bind_param("i", $archive_id);
        $del->execute();
        $del->close();

        echo json_encode(['success' => true, 'message' => 'Quotation restored successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to restore quotation.']);
    }
    $stmt->close();
    exit;
}

// ── PERMANENT DELETE: remove from archived_quotations forever ──
if ($action === 'permanent_delete' && isset($_POST['archive_id'])) {
    $archive_id = intval($_POST['archive_id']);

    $stmt = $conn->prepare("DELETE FROM archived_quotations WHERE archive_id = ?");
    $stmt->bind_param("i", $archive_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Quotation permanently deleted.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Archived quotation not found.']);
    }
    $stmt->close();
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
$conn->close();
