<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Support both JSON payload and standard POST data
$inputData = json_decode(file_get_contents('php://input'), true);
$emailRaw = isset($inputData['email']) ? $inputData['email'] : ($_POST['email'] ?? '');

$email = filter_var(trim($emailRaw), FILTER_SANITIZE_EMAIL);

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

try {
    require_once __DIR__ . '/config/db_pdo.php';
    $db = getPDO();

    // Auto-create subscribers table if it doesn't exist
    $db->exec("CREATE TABLE IF NOT EXISTS `subscribers` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `email` VARCHAR(255) NOT NULL UNIQUE,
        `status` VARCHAR(50) NOT NULL DEFAULT 'potential_client',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Check if email already exists
    $checkStmt = $db->prepare("SELECT `id` FROM `subscribers` WHERE `email` = ?");
    $checkStmt->execute([$email]);
    if ($checkStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'This email is already subscribed.']);
        exit;
    }

    // Insert new subscriber with fallback support for old enum status
    try {
        $insertStmt = $db->prepare("INSERT INTO `subscribers` (`email`, `status`) VALUES (?, 'potential_client')");
        if ($insertStmt->execute([$email])) {
            echo json_encode(['success' => true, 'message' => 'Thank you for subscribing!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
        }
    } catch (Exception $e) {
        // Fallback: If 'potential_client' fails (e.g. because status is still an ENUM like 'active','inactive','unsubscribed')
        try {
            $insertStmtFallback = $db->prepare("INSERT INTO `subscribers` (`email`, `status`) VALUES (?, 'active')");
            if ($insertStmtFallback->execute([$email])) {
                echo json_encode(['success' => true, 'message' => 'Thank you for subscribing!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
            }
        } catch (Exception $fallbackEx) {
            // If even fallback fails, throw the original exception
            throw $e;
        }
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
exit;