<?php
/**
 * save-promo-images.php
 * Handles uploaded promotional images from admin-promo-images.php
 * 
 * Place this file in the SAME directory as admin-promo-images.php
 * (i.e. your website root or admin folder).
 */

date_default_timezone_set('Asia/Manila');

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

// Get other fields
$link  = $_POST['link'] ?? '';
$start = $_POST['start'] ?? '';

$start = normalize_start_schedule($start);
if ($start === null) {
    redirect_error('Invalid Start Posting format. Use MM/DD/YYYY HH:MM am/pm.');
}

// ── Update JSON config ────────────────────────────────────────────────────────
$config = [];
if (file_exists(CONFIG_FILE)) {
    $config = json_decode(file_get_contents(CONFIG_FILE), true) ?? [];
}

// Ensure nested structure for this slot
if (!isset($config[$slot]) || is_string($config[$slot])) {
    $config[$slot] = ['image' => is_string($config[$slot] ?? null) ? $config[$slot] : ''];
}

$config[$slot]['link']  = $link;
$config[$slot]['start'] = $start;
unset($config[$slot]['end']);


// ── Handle Optional Image Upload ─────────────────────────────────────────────
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    
    $file    = $_FILES['image'];
    $tmpPath = $file['tmp_name'];

    // Size check
    if ($file['size'] > MAX_SIZE) {
        redirect_error('File is too large. Maximum allowed size is 5MB.');
    }

    // MIME check
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($tmpPath);
    if (!in_array($mimeType, ALLOWED_MIME, true)) {
        redirect_error('Unsupported file type: ' . $mimeType . '. Please upload JPG, PNG, WebP, or GIF.');
    }

    // Extension from MIME
    $extMap = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];
    $ext = $extMap[$mimeType] ?? 'jpg';

    // Ensure upload directory exists
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    // Move file
    $baseFilename = $slotMap[$slot] . '.' . $ext;
    $destPath     = UPLOAD_DIR . $baseFilename;
    $webPath      = 'assets/img/' . $baseFilename;

    // Remove old files for this slot
    foreach (['jpg','jpeg','png','webp','gif'] as $oldExt) {
        $old = UPLOAD_DIR . $slotMap[$slot] . '.' . $oldExt;
        if ($old !== $destPath && file_exists($old)) {
            @unlink($old);
        }
    }

    if (move_uploaded_file($tmpPath, $destPath)) {
        $config[$slot]['image'] = $webPath;
    } else {
        redirect_error('Failed to save the image. Check directory write permissions.');
    }
}

file_put_contents(CONFIG_FILE, json_encode($config, JSON_PRETTY_PRINT));

// ── Redirect back with success ────────────────────────────────────────────────
header('Location: ' . ADMIN_URL . '?saved=1');
exit;


// ── Helper ───────────────────────────────────────────────────────────────────
function redirect_error(string $msg): void {
    header('Location: ' . ADMIN_URL . '?error=' . urlencode($msg));
    exit;
}

function normalize_start_schedule(string $value): ?string {
    $value = trim($value);

    if ($value === '') {
        return date('m/d/Y h:i a');
    }

    $dt = DateTime::createFromFormat('m/d/Y h:i a', $value);
    if ($dt instanceof DateTime) {
        return $dt->format('m/d/Y h:i a');
    }

    // Backward compatibility with old datetime-local payload.
    $dt = DateTime::createFromFormat('Y-m-d\TH:i', $value);
    if ($dt instanceof DateTime) {
        return $dt->format('m/d/Y h:i a');
    }

    // Last attempt for parseable date text.
    try {
        return (new DateTime($value))->format('m/d/Y h:i a');
    } catch (Exception $e) {
        return null;
    }
}
