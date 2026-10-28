<?php
const CONTACT_ATTACHMENT_LIMIT = 30 * 1024 * 1024;
const CONTACT_ATTACHMENT_GUARD = "<?php http_response_code(404); exit; ?>\n";
function contact_attachment_save(array $file): array {
    if (is_array($file['error'] ?? null) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Attachment upload failed. Maximum size is 30 MB. Check the hosting upload limit.');
    }
    $tmp = $file['tmp_name'] ?? '';
    if (!is_uploaded_file($tmp)) throw new RuntimeException('Invalid attachment upload.');
    $size = filesize($tmp);
    if (!$size || $size > CONTACT_ATTACHMENT_LIMIT) throw new RuntimeException('Attachment must be between 1 byte and 30 MB.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    $allowed = ['application/pdf'=>'pdf', 'image/jpeg'=>'jpg', 'image/png'=>'png', 'image/webp'=>'webp'];
    if (!isset($allowed[$mime])) throw new RuntimeException('Please attach a PDF, JPG, PNG, or WebP file.');
    $name = basename(str_replace('\\', '/', $file['name'] ?? 'attachment'));
    $name = preg_replace('/[^a-zA-Z0-9._ -]/', '_', $name);
    $name = substr(pathinfo($name, PATHINFO_FILENAME), 0, 100) . '.' . $allowed[$mime];
    $token = bin2hex(random_bytes(24));
    $dir = __DIR__ . '/../uploads/contact-private';
    if (!is_dir($dir) && !mkdir($dir, 0700, true)) throw new RuntimeException('Attachment storage is unavailable.');
    $path = $dir . '/' . $token . '.php';
    $out = fopen($path, 'xb');
    if (!$out) throw new RuntimeException('Unable to save attachment.');
    try {
        $header = CONTACT_ATTACHMENT_GUARD . json_encode(['name'=>$name,'mime'=>$mime,'size'=>$size]) . "\n";
        if (fwrite($out, $header) !== strlen($header)) throw new RuntimeException('Unable to save attachment.');
        $in = fopen($tmp, 'rb');
        if (!$in) throw new RuntimeException('Unable to read attachment.');
        try { $copied = stream_copy_to_stream($in, $out); } finally { fclose($in); }
        if ($copied !== $size) throw new RuntimeException('Unable to save complete attachment.');
    } catch (Throwable $e) { fclose($out); @unlink($path); throw $e; }
    fclose($out);
    return ['token'=>$token,'name'=>$name,'path'=>$path];
}
