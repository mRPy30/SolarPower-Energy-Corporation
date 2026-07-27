<?php
require_once __DIR__ . '/../config/dbconn.php';

header('Content-Type: application/json');

function portfolio_video_view_ensure_table(mysqli $conn): void
{
    $sql = "CREATE TABLE IF NOT EXISTS portfolio_videos (
        id INT(11) NOT NULL AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        project_id INT(11) DEFAULT NULL,
        video_format ENUM('landscape','vertical') NOT NULL DEFAULT 'landscape',
        media_type ENUM('file','url') NOT NULL DEFAULT 'url',
        media_url TEXT NOT NULL,
        thumbnail_url TEXT DEFAULT NULL,
        category_tag ENUM('Residential','Commercial','Maintenance','Showcase') NOT NULL DEFAULT 'Showcase',
        status ENUM('Published','Draft') NOT NULL DEFAULT 'Draft',
        view_count INT UNSIGNED NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_project_id (project_id),
        KEY idx_status_format (status, video_format),
        KEY idx_category_tag (category_tag)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

    mysqli_query($conn, $sql);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Unsupported request method.']);
    exit;
}

portfolio_video_view_ensure_table($conn);

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Missing video ID.']);
    exit;
}

$stmt = $conn->prepare("UPDATE portfolio_videos SET view_count = view_count + 1 WHERE id = ? AND status = 'Published'");
$stmt->bind_param("i", $id);
$stmt->execute();

echo json_encode(['success' => $stmt->affected_rows > 0]);
?>
