<?php
/**
 * save-promo-images.php
 * Handles uploaded promotional images from admin-promo-images.php
 * 
 * Place this file in the SAME directory as admin-promo-images.php
 * (i.e. your website root or admin folder).
 */

// ── Config ──────────────────────────────────────────────────────────────────
define('UPLOAD_DIR',   __DIR__ . '/../../../assets/img/');   // where images are saved
define('CONFIG_FILE',  __DIR__ . '/promo-images.json'); // persists active paths
define('MAX_SIZE',     5 * 1024 * 1024);             // 5 MB
define('ALLOWED_MIME', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('ADMIN_URL',    '../dashboard.php');    // redirect back here

// Slot → saved filename map
$slotMap = [
    'main'   => 'promo-main',
    'top'    => 'promo-top',
    'bottom' => 'promo-bottom',
];

// ── Guard: POST only ─────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_error('Invalid request method.');
}

$slot = $_POST['slot'] ?? '';
if (!array_key_exists($slot, $slotMap)) {
    redirect_error('Unknown image slot.');
}

// ── Validate upload ──────────────────────────────────────────────────────────
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE   => 'File exceeds server upload limit.',
        UPLOAD_ERR_FORM_SIZE  => 'File exceeds form size limit.',
        UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'A PHP extension blocked the upload.',
    ];
    $code = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;
    redirect_error($uploadErrors[$code] ?? 'Upload failed (code ' . $code . ').');
}

$file    = $_FILES['image'];
$tmpPath = $file['tmp_name'];

// Size check
if ($file['size'] > MAX_SIZE) {
    redirect_error('File is too large. Maximum allowed size is 5MB.');
}

// MIME check (use finfo for accuracy, not just browser-reported type)
$finfo    = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($tmpPath);
if (!in_array($mimeType, ALLOWED_MIME, true)) {
    redirect_error('Unsupported file type: ' . $mimeType . '. Please upload JPG, PNG, WebP, or GIF.');
}

// Extension from MIME
$extMap = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
];
$ext = $extMap[$mimeType];

// ── Ensure upload directory exists ───────────────────────────────────────────
if (!is_dir(UPLOAD_DIR)) {
    if (!mkdir(UPLOAD_DIR, 0755, true)) {
        redirect_error('Could not create upload directory. Check server permissions.');
    }
}

// ── Move file ────────────────────────────────────────────────────────────────
$baseFilename = $slotMap[$slot] . '.' . $ext;
$destPath     = UPLOAD_DIR . $baseFilename;
$webPath      = 'assets/img/' . $baseFilename;

// Remove old files for this slot (different extension)
foreach (['jpg','jpeg','png','webp','gif'] as $oldExt) {
    $old = UPLOAD_DIR . $slotMap[$slot] . '.' . $oldExt;
    if ($old !== $destPath && file_exists($old)) {
        @unlink($old);
    }
}

if (!move_uploaded_file($tmpPath, $destPath)) {
    redirect_error('Failed to save the image. Check directory write permissions.');
}

// ── Update JSON config ────────────────────────────────────────────────────────
$config = [];
if (file_exists(CONFIG_FILE)) {
    $config = json_decode(file_get_contents(CONFIG_FILE), true) ?? [];
}
$config[$slot] = $webPath;
file_put_contents(CONFIG_FILE, json_encode($config, JSON_PRETTY_PRINT));

// ── Redirect back with success ────────────────────────────────────────────────
header('Location: ' . ADMIN_URL . '?saved=1');
exit;

// ── Helper ───────────────────────────────────────────────────────────────────
function redirect_error(string $msg): void {
    header('Location: ' . ADMIN_URL . '?error=' . urlencode($msg));
    exit;
}
