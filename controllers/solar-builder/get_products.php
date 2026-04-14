<?php
/**
 * Solar Builder API — Get Products by Builder Category
 *
 * GET ?category=panels|inverter|battery|mounting|wiring|monitoring
 *
 * Returns products with specs, images, and filter metadata.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../config/db_pdo.php';

// ── Category mapping: builder slug → DB category name + metadata ─────────────
$BUILDER_CATEGORIES = [
    'panels' => [
        'db_category'   => 'Panel',
        'name'          => 'Solar Panels',
        'icon'          => '🌞',
        'filter_labels' => ['Brand', 'Cell Type'],
        'filter_keys'   => ['brand', 'cell_type'],
        'spec_summary'  => ['wattage', 'efficiency', 'panel_type', 'cell_type'],
        'has_quantity'   => true,
        'default_qty'   => 10,
    ],
    'inverter' => [
        'db_category'   => 'Inverter',
        'name'          => 'Inverter',
        'icon'          => '⚡',
        'filter_labels' => ['Brand', 'Type'],
        'filter_keys'   => ['brand', 'inverter_type'],
        'spec_summary'  => ['rated_power_kw', 'inverter_type', 'phase', 'efficiency'],
        'has_quantity'   => false,
        'default_qty'   => 1,
    ],
    'battery' => [
        'db_category'   => 'Battery',
        'name'          => 'Battery Storage',
        'icon'          => '🔋',
        'filter_labels' => ['Brand', 'Chemistry'],
        'filter_keys'   => ['brand', 'chemistry'],
        'spec_summary'  => ['capacity_kwh', 'voltage', 'chemistry', 'cycle_life'],
        'has_quantity'   => true,
        'default_qty'   => 1,
    ],
    'mounting' => [
        'db_category'   => 'Mounting & Accessories',
        'name'          => 'Mounting System',
        'icon'          => '🔩',
        'filter_labels' => ['Brand', 'Mount Type'],
        'filter_keys'   => ['brand', 'mount_type'],
        'spec_summary'  => ['mount_type', 'material', 'max_panels'],
        'has_quantity'   => false,
        'default_qty'   => 1,
    ],
    'wiring' => [
        'db_category'   => 'Wiring & Protection',
        'name'          => 'Wiring & Protection',
        'icon'          => '🔌',
        'filter_labels' => ['Brand', 'Kit Type'],
        'filter_keys'   => ['brand', 'kit_type'],
        'spec_summary'  => ['kit_type', 'max_system_kw', 'spd_type'],
        'has_quantity'   => false,
        'default_qty'   => 1,
    ],
    'monitoring' => [
        'db_category'   => 'Monitoring System',
        'name'          => 'Monitoring System',
        'icon'          => '📡',
        'filter_labels' => ['Brand', 'Connectivity'],
        'filter_keys'   => ['brand', 'connectivity'],
        'spec_summary'  => ['connectivity', 'display_type', 'energy_meter'],
        'has_quantity'   => false,
        'default_qty'   => 1,
    ],
];

// ── Validate input ───────────────────────────────────────────────────────────
$categorySlug = $_GET['category'] ?? '';

if (!isset($BUILDER_CATEGORIES[$categorySlug])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => 'Invalid category. Allowed: ' . implode(', ', array_keys($BUILDER_CATEGORIES)),
    ]);
    exit;
}

$catMeta = $BUILDER_CATEGORIES[$categorySlug];
$dbCategory = $catMeta['db_category'];

// ── Query products ───────────────────────────────────────────────────────────
try {
    $pdo = getPDO();

    // Build base query — combine brand + displayName for a complete product name
    // If displayName already contains the brand, just use displayName
    $sql = "SELECT p.id,
                   CASE
                     WHEN p.brandName REGEXP '^[0-9]+[Ww]?$'
                       THEN CONCAT(p.displayName, ' ', p.brandName)
                     WHEN LOWER(p.displayName) LIKE CONCAT('%', LOWER(p.brandName), '%')
                       THEN p.displayName
                     ELSE CONCAT(p.brandName, ' ', p.displayName)
                   END AS name,
                   p.brandName AS brand, p.price,
                   p.stockQuantity AS stock, p.warranty, p.description,
                   (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.id ASC LIMIT 1) AS image
            FROM product p
            WHERE p.category = :category
              AND p.stockQuantity > 0";

    $params = ['category' => $dbCategory];

    // Exclude obvious demo/test products
    $sql .= " AND p.displayName NOT LIKE '%demo%'
              AND p.displayName NOT LIKE '%test%'
              AND p.displayName NOT LIKE '%asd%'
              AND p.displayName NOT LIKE '%carousel%'
              AND p.displayName NOT LIKE '%convert%'
              AND p.price > 0
              AND p.price < 10000000";

    $sql .= " ORDER BY p.price ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    // Specs table was removed - specs will be empty
    $specs = [];

    // ── Build filter options ─────────────────────────────────────────────────
    $filterOptions = [[], []];

    // ── Assemble response ────────────────────────────────────────────────────
    $result = [];
    foreach ($products as &$p) {
        $pid = (int)$p['id'];
        $p['id'] = $pid;
        $p['price'] = (float)$p['price'];
        $p['stock'] = (int)$p['stock'];
        $p['specs'] = $specs[$pid] ?? [];
        $p['image'] = $p['image'] ?: null;

        // Build spec summary string
        $summaryParts = [];
        foreach ($catMeta['spec_summary'] as $key) {
            if (isset($p['specs'][$key])) {
                $s = $p['specs'][$key];
                $val = $s['value'] . ($s['unit'] ? $s['unit'] : '');
                $summaryParts[] = $val;
            }
        }
        $p['spec_summary'] = implode(' · ', $summaryParts);

        // Collect filter options
        // Filter 1: brand (from product table, skip wattage-like entries)
        if (!empty($p['brand']) && !in_array($p['brand'], $filterOptions[0]) && !preg_match('/^\d+[Ww]?$/', $p['brand'])) {
            $filterOptions[0][] = $p['brand'];
        }
        // Filter 2: from specs
        $fk2 = $catMeta['filter_keys'][1];
        if (isset($p['specs'][$fk2]) && !in_array($p['specs'][$fk2]['value'], $filterOptions[1])) {
            $filterOptions[1][] = $p['specs'][$fk2]['value'];
        }

        // Flatten specs to simple key=>value for frontend
        $flatSpecs = [];
        foreach ($p['specs'] as $k => $v) {
            $flatSpecs[$k] = $v['value'];
        }
        $p['specs'] = $flatSpecs;
    }
    unset($p);

    echo json_encode([
        'success'  => true,
        'category' => [
            'slug'          => $categorySlug,
            'name'          => $catMeta['name'],
            'icon'          => $catMeta['icon'],
            'filter_labels' => $catMeta['filter_labels'],
            'filter_keys'   => $catMeta['filter_keys'],
            'has_quantity'   => $catMeta['has_quantity'],
            'default_qty'   => $catMeta['default_qty'],
        ],
        'filters'  => [
            ['label' => $catMeta['filter_labels'][0], 'key' => $catMeta['filter_keys'][0], 'options' => $filterOptions[0]],
            ['label' => $catMeta['filter_labels'][1], 'key' => $catMeta['filter_keys'][1], 'options' => $filterOptions[1]],
        ],
        'products' => $result ?: $products,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
