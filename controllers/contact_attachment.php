<?php
session_start();
if (empty($_SESSION['user_id'])) { http_response_code(403); exit('Please sign in to the staff dashboard to download this attachment.'); }
require_once __DIR__ . '/../includes/contact-attachment.php';
$token = $_GET['file'] ?? '';
if (!is_string($token) || !preg_match('/^[a-f0-9]{48}$/D', $token)) { http_response_code(404); exit; }
$path = __DIR__ . '/../uploads/contact-private/' . $token . '.php';
$in = @fopen($path, 'rb');
if (!$in) { http_response_code(404); exit; }
fgets($in);
$meta = json_decode(fgets($in), true);
if (!is_array($meta) || !isset($meta['name'], $meta['size'])) { fclose($in); http_response_code(404); exit; }
session_write_close();
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $meta['name'] . '"');
header('Content-Length: ' . (int)$meta['size']);
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
fpassthru($in);
fclose($in);
