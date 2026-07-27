<?php
session_start();
require_once __DIR__ . "/../config/dbconn.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'staff') {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit;
}

function portfolio_video_ensure_table(mysqli $conn): void
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

function portfolio_video_json($status, $message = '', $extra = []): void
{
    echo json_encode(array_merge(["status" => $status, "message" => $message], $extra));
    exit;
}

function portfolio_video_normalize_path($path): string
{
    return str_replace('\\', '/', trim((string) $path));
}

function portfolio_video_delete_upload($path): bool
{
    $normalizedPath = portfolio_video_normalize_path($path);
    if (strpos($normalizedPath, 'uploads/portfolio-videos/') !== 0) {
        return false;
    }

    $uploadRoot = realpath(__DIR__ . '/../uploads/portfolio-videos');
    $fullPath = realpath(__DIR__ . '/../' . $normalizedPath);

    if (!$uploadRoot || !$fullPath || !is_file($fullPath)) {
        return false;
    }

    $uploadRoot = rtrim(str_replace('\\', '/', $uploadRoot), '/');
    $fullPath = str_replace('\\', '/', $fullPath);

    if (strpos($fullPath, $uploadRoot . '/') !== 0) {
        return false;
    }

    return @unlink($fullPath);
}

function portfolio_video_delete_folder($id): void
{
    $folderPath = __DIR__ . '/../uploads/portfolio-videos/' . (int) $id;
    if (!is_dir($folderPath)) {
        return;
    }

    $files = glob($folderPath . '/*');
    if (is_array($files)) {
        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }

    @rmdir($folderPath);
}

function portfolio_video_upload_file(array $file, int $id, string $kind, array $allowedMimes, int $maxBytes, array &$errors): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return '';
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        $errors[] = ucfirst($kind) . " upload failed.";
        return '';
    }

    if (($file['size'] ?? 0) > $maxBytes) {
        $errors[] = ucfirst($kind) . " exceeds the upload size limit.";
        return '';
    }

    $tmpPath = $file['tmp_name'] ?? '';
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = $finfo ? finfo_file($finfo, $tmpPath) : '';
    if ($finfo) {
        finfo_close($finfo);
    }

    if (!in_array($mimeType, $allowedMimes, true)) {
        $errors[] = ucfirst($kind) . " has an invalid file type.";
        return '';
    }

    $uploadDir = __DIR__ . '/../uploads/portfolio-videos/' . $id . '/';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
        $errors[] = "Unable to create upload folder.";
        return '';
    }

    $ext = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
    if ($ext === '') {
        $ext = $kind === 'video' ? 'mp4' : 'jpg';
    }

    $safeName = $kind . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destination = $uploadDir . $safeName;

    if (!move_uploaded_file($tmpPath, $destination)) {
        $errors[] = "Unable to save " . $kind . " file.";
        return '';
    }

    return 'uploads/portfolio-videos/' . $id . '/' . $safeName;
}

portfolio_video_ensure_table($conn);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $videos = [];
    $videoSql = "SELECT v.*, p.project_name
        FROM portfolio_videos v
        LEFT JOIN portfolio_projects p ON p.id = v.project_id
        ORDER BY v.created_at DESC, v.id DESC";

    if ($result = mysqli_query($conn, $videoSql)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $videos[] = $row;
        }
    }

    $projects = [];
    if ($projectResult = mysqli_query($conn, "SELECT id, project_name, location, system_type FROM portfolio_projects ORDER BY project_name ASC")) {
        while ($row = mysqli_fetch_assoc($projectResult)) {
            $projects[] = $row;
        }
    }

    portfolio_video_json("success", "", ["data" => $videos, "projects" => $projects]);
}

if ($method !== 'POST') {
    portfolio_video_json("error", "Unsupported request method.");
}

$action = $_POST['action'] ?? 'save';

if ($action === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        portfolio_video_json("error", "Missing video ID.");
    }

    $stmt = $conn->prepare("SELECT media_url, thumbnail_url FROM portfolio_videos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$existing) {
        portfolio_video_json("error", "Video not found.");
    }

    portfolio_video_delete_upload($existing['media_url'] ?? '');
    portfolio_video_delete_upload($existing['thumbnail_url'] ?? '');
    portfolio_video_delete_folder($id);

    $deleteStmt = $conn->prepare("DELETE FROM portfolio_videos WHERE id = ?");
    $deleteStmt->bind_param("i", $id);
    if ($deleteStmt->execute()) {
        portfolio_video_json("success", "Video deleted.");
    }

    portfolio_video_json("error", "Unable to delete video.");
}

$id = (int) ($_POST['id'] ?? 0);
$wasNewRecord = $id <= 0;
$title = trim((string) ($_POST['title'] ?? ''));
$projectId = (int) ($_POST['project_id'] ?? 0);
$projectIdValue = $projectId > 0 ? $projectId : null;
$videoFormat = ($_POST['video_format'] ?? 'landscape') === 'vertical' ? 'vertical' : 'landscape';
$mediaType = ($_POST['media_type'] ?? 'url') === 'file' ? 'file' : 'url';
$mediaUrl = trim((string) ($_POST['media_url'] ?? ''));
$categoryTag = trim((string) ($_POST['category_tag'] ?? 'Showcase'));
$status = ($_POST['status'] ?? 'Draft') === 'Published' ? 'Published' : 'Draft';
$allowedCategories = ['Residential', 'Commercial', 'Maintenance', 'Showcase'];

if (!in_array($categoryTag, $allowedCategories, true)) {
    $categoryTag = 'Showcase';
}

if ($title === '') {
    portfolio_video_json("error", "Video title is required.");
}

if ($mediaType === 'url' && !filter_var($mediaUrl, FILTER_VALIDATE_URL)) {
    portfolio_video_json("error", "Please enter a valid YouTube, Vimeo, or video URL.");
}

$existing = [
    'media_type' => '',
    'media_url' => '',
    'thumbnail_url' => '',
];

if ($id > 0) {
    $stmt = $conn->prepare("SELECT media_type, media_url, thumbnail_url FROM portfolio_videos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $existingRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$existingRow) {
        portfolio_video_json("error", "Video not found.");
    }

    $existing = $existingRow;
} else {
    $insertStmt = $conn->prepare("INSERT INTO portfolio_videos (title, media_url, status) VALUES (?, '', 'Draft')");
    $insertStmt->bind_param("s", $title);
    if (!$insertStmt->execute()) {
        portfolio_video_json("error", "Unable to create video record.");
    }
    $id = (int) $conn->insert_id;
    $insertStmt->close();
}

$errors = [];
$uploadedMedia = '';
$uploadedThumb = '';

if ($mediaType === 'file') {
    $uploadedMedia = portfolio_video_upload_file(
        $_FILES['video_file'] ?? [],
        $id,
        'video',
        ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'],
        250 * 1024 * 1024,
        $errors
    );

    if ($uploadedMedia !== '') {
        $mediaUrl = $uploadedMedia;
    } elseif (($existing['media_type'] ?? '') !== 'file' || empty($existing['media_url'])) {
        $errors[] = "Upload an MP4/WebM video file or switch to Video URL.";
    } else {
        $mediaUrl = $existing['media_url'];
    }
}

$uploadedThumb = portfolio_video_upload_file(
    $_FILES['thumbnail_file'] ?? [],
    $id,
    'thumbnail',
    ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
    20 * 1024 * 1024,
    $errors
);

$thumbnailUrl = $uploadedThumb !== '' ? $uploadedThumb : (string) ($existing['thumbnail_url'] ?? '');

if (!empty($errors)) {
    if ($uploadedMedia !== '') {
        portfolio_video_delete_upload($uploadedMedia);
    }
    if ($uploadedThumb !== '') {
        portfolio_video_delete_upload($uploadedThumb);
    }
    if ($wasNewRecord && $id > 0) {
        $cleanupStmt = $conn->prepare("DELETE FROM portfolio_videos WHERE id = ?");
        $cleanupStmt->bind_param("i", $id);
        $cleanupStmt->execute();
        portfolio_video_delete_folder($id);
    }
    portfolio_video_json("error", implode(" ", $errors));
}

$updateStmt = $conn->prepare("UPDATE portfolio_videos
    SET title = ?, project_id = ?, video_format = ?, media_type = ?, media_url = ?, thumbnail_url = ?, category_tag = ?, status = ?
    WHERE id = ?");
$updateStmt->bind_param(
    "sissssssi",
    $title,
    $projectIdValue,
    $videoFormat,
    $mediaType,
    $mediaUrl,
    $thumbnailUrl,
    $categoryTag,
    $status,
    $id
);

if (!$updateStmt->execute()) {
    if ($uploadedMedia !== '') {
        portfolio_video_delete_upload($uploadedMedia);
    }
    if ($uploadedThumb !== '') {
        portfolio_video_delete_upload($uploadedThumb);
    }
    if ($wasNewRecord && $id > 0) {
        $cleanupStmt = $conn->prepare("DELETE FROM portfolio_videos WHERE id = ?");
        $cleanupStmt->bind_param("i", $id);
        $cleanupStmt->execute();
        portfolio_video_delete_folder($id);
    }
    portfolio_video_json("error", "Unable to save video details.");
}

if ($uploadedMedia !== '' && !empty($existing['media_url']) && $existing['media_url'] !== $uploadedMedia) {
    portfolio_video_delete_upload($existing['media_url']);
}

if ($mediaType === 'url' && !empty($existing['media_url']) && $existing['media_url'] !== $mediaUrl) {
    portfolio_video_delete_upload($existing['media_url']);
}

if ($uploadedThumb !== '' && !empty($existing['thumbnail_url']) && $existing['thumbnail_url'] !== $uploadedThumb) {
    portfolio_video_delete_upload($existing['thumbnail_url']);
}

portfolio_video_json("success", "Video saved.", ["id" => $id]);
?>
