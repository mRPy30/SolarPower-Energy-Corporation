<?php
// Read-only product discovery; hidden products never enter the sitemap.
ob_start();
try {
    require_once __DIR__ . '/includes/seo.php';
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    require __DIR__ . '/config/dbconn.php';
    $result = $conn->query("SELECT displayName FROM product WHERE status = 'Active' ORDER BY id");
    $urls = [];
    while ($row = $result->fetch_assoc()) {
        $urls['https://solarpower.com.ph/product-details/' . rawurlencode(solar_seo_product_slug($row['displayName']))] = true;
    }
    $conn->close();
    if (count($urls) > 50000) throw new RuntimeException('Sitemap pagination required');
    ob_end_clean();
    header('Content-Type: application/xml; charset=UTF-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($urls as $url => $_) {
        echo '<url><loc>' . htmlspecialchars($url, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc></url>';
    }
    echo '</urlset>';
} catch (Throwable $error) {
    if (ob_get_level()) ob_end_clean();
    error_log('Product sitemap could not be generated. Check database availability.');
    http_response_code(503);
    header('Retry-After: 3600');
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Sitemap temporarily unavailable.';
}
