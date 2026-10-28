<?php
require_once __DIR__ . '/routes.php';

// Stored paths stay relative to the project, while browser URLs respect its base path.
function product_image_url(?string ...$paths): string {
    foreach ($paths as $path) {
        $path = trim(str_replace('\\', '/', (string)$path));
        if ($path === '') continue;
        if (preg_match('~^https?://~i', $path)) {
            $host = strtolower((string)parse_url($path, PHP_URL_HOST));
            if (!in_array($host, ['solarpower.com.ph', 'www.solarpower.com.ph', 'localhost'], true)) return $path;
            $path = (string)parse_url($path, PHP_URL_PATH);
        }
        $path = preg_replace('~^(?:\.\.?/)+~', '', $path);
        $path = ltrim(rawurldecode($path), '/');
        $path = preg_replace('~^SolarPower-Energy-Corporation/~i', '', $path);
        if (str_contains($path, '..') || !preg_match('~^(uploads/|assets/)~', $path)) continue;
        if (is_file(dirname(__DIR__) . '/' . $path) && is_readable(dirname(__DIR__) . '/' . $path)) {
            return asset_url(implode('/', array_map('rawurlencode', explode('/', $path))));
        }
    }
    return asset_url('assets/img/product-placeholder.png');
}

function save_brand_product_image(array $file, int $productId, int $brandId): string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Brand image upload failed. Please choose the file again (maximum 5 MB).');
    }
    if ($productId < 1 || $brandId < 1 || ($file['size'] ?? 0) < 1 || $file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('Brand image must be between 1 byte and 5 MB.');
    }
    $tmp = $file['tmp_name'] ?? '';
    $info = @getimagesize($tmp);
    $extensions = ['image/jpeg'=>'jpg', 'image/png'=>'png', 'image/webp'=>'webp'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    if (!$info || !isset($extensions[$mime]) || $info['mime'] !== $mime) {
        throw new RuntimeException('Brand image must be a valid JPG, PNG or WebP image.');
    }
    $relativeDir = "uploads/products/$productId/";
    $directory = dirname(__DIR__) . '/' . $relativeDir;
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('Cannot create the product image directory.');
    }
    if (!is_writable($directory)) throw new RuntimeException('Product image directory is not writable.');
    $relativePath = $relativeDir . 'variant_' . $brandId . '_' . bin2hex(random_bytes(12)) . '.' . $extensions[$mime];
    $destination = dirname(__DIR__) . '/' . $relativePath;
    if (!move_uploaded_file($tmp, $destination)) throw new RuntimeException('Unable to save the brand image. Please retry.');
    @chmod($destination, 0644);
    if (!is_readable($destination)) throw new RuntimeException('Saved brand image is not readable.');
    return $relativePath;
}
