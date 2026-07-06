<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config/dbconn.php";

if (!function_exists('createSlug')) {
    function createSlug($text) {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        return empty($text) ? 'n-a' : $text;
    }
}

if (!function_exists('getProductBrandArray')) {
    function getProductBrandArray($brandNames) {
        $brands = preg_split('/\s*,\s*/', (string) $brandNames);
        $cleanBrands = [];

        foreach ($brands as $brand) {
            $brand = trim($brand);
            if ($brand !== '') {
                $cleanBrands[] = $brand;
            }
        }

        return array_values(array_unique($cleanBrands));
    }
}

if (!function_exists('renderProductBrandChips')) {
    function renderProductBrandChips($brandNames) {
        $brands = getProductBrandArray($brandNames);
        return $brands ? htmlspecialchars(implode(', ', $brands)) : 'Brand TBA';
    }
}

if (!function_exists('renderProductCardTitle')) {
    function renderProductCardTitle($brandNames, $displayName) {
        $displayName = trim((string) $displayName);
        $brands = getProductBrandArray($brandNames);
        $primaryBrand = $brands[0] ?? '';

        if ($primaryBrand !== '' && stripos($displayName, $primaryBrand) === false) {
            return htmlspecialchars($primaryBrand . ' - ' . $displayName);
        }

        return htmlspecialchars($displayName);
    }
}

$category_key = isset($_GET['category']) ? strtolower(trim((string) $_GET['category'])) : 'all';
$category_key = preg_replace('/[^a-z0-9-]+/', '', str_replace([' ', '_'], '-', $category_key));

$categories = [
    'panel' => [
        'title' => 'Solar Panels',
        'eyebrow' => 'Category',
        'icon' => 'fa-solar-panel',
        'subtitle' => 'All solar panel products sorted from lowest to highest price.',
        'where' => "(LOWER(TRIM(p.category)) IN ('panel', 'panels') OR LOWER(p.displayName) LIKE '%panel%')"
    ],
    'inverter' => [
        'title' => 'Inverters',
        'eyebrow' => 'Category',
        'icon' => 'fa-plug-circle-bolt',
        'subtitle' => 'Hybrid, grid-tie, and solar inverter products sorted by best entry price.',
        'where' => "(LOWER(TRIM(p.category)) IN ('inverter', 'inverters') OR LOWER(p.displayName) LIKE '%inverter%')"
    ],
    'battery' => [
        'title' => 'Batteries',
        'eyebrow' => 'Category',
        'icon' => 'fa-car-battery',
        'subtitle' => 'Battery products arranged from lowest to highest price.',
        'where' => "(LOWER(TRIM(p.category)) IN ('battery', 'batteries') OR LOWER(p.displayName) LIKE '%battery%' OR LOWER(p.displayName) LIKE '%batteries%')"
    ],
    'package' => [
        'title' => 'Package Setups',
        'eyebrow' => 'Category',
        'icon' => 'fa-boxes-stacked',
        'subtitle' => 'Complete solar package systems sorted from lowest to highest price.',
        'where' => "(LOWER(TRIM(p.category)) = 'package' OR LOWER(TRIM(COALESCE(p.packageType, ''))) IN ('hybrid', 'on-grid', 'off-grid', 'grid-tie', 'package') OR LOWER(p.displayName) LIKE '%package%' OR LOWER(p.displayName) LIKE '%system%')"
    ],
    'mounting' => [
        'title' => 'Mounting Accessories',
        'eyebrow' => 'Category',
        'icon' => 'fa-screwdriver-wrench',
        'subtitle' => 'Mounting, wiring, rail, clamp, and accessory products sorted by price.',
        'where' => "(LOWER(TRIM(p.category)) LIKE '%mount%' OR LOWER(TRIM(p.category)) LIKE '%accessor%' OR LOWER(TRIM(p.category)) LIKE '%wiring%' OR LOWER(p.displayName) LIKE '%mount%' OR LOWER(p.displayName) LIKE '%accessor%' OR LOWER(p.displayName) LIKE '%clamp%' OR LOWER(p.displayName) LIKE '%rail%' OR LOWER(p.displayName) LIKE '%wiring%')"
    ],
    'all' => [
        'title' => 'All Products',
        'eyebrow' => 'Catalog',
        'icon' => 'fa-border-all',
        'subtitle' => 'Browse every active product sorted from lowest to highest price.',
        'where' => "1 = 1"
    ],
];

if (!isset($categories[$category_key])) {
    $category_key = 'all';
}

$active_category = $categories[$category_key];
$products = [];

if (isset($conn) && $conn instanceof mysqli) {
    $conn->set_charset('utf8mb4');
    $variantColumnCheck = $conn->query("SHOW COLUMNS FROM product_brand_variants LIKE 'variant_name'");
    if ($variantColumnCheck && $variantColumnCheck->num_rows === 0) {
        $conn->query("ALTER TABLE product_brand_variants ADD COLUMN variant_name VARCHAR(255) NOT NULL DEFAULT '' AFTER brand_id");
    }

    $sql = "
        SELECT
            p.id,
            p.displayName AS parentDisplayName,
            COALESCE(NULLIF(TRIM(pbv.variant_name), ''), p.displayName) AS displayName,
            pbv.id AS variant_id,
            pbv.brand_id AS variant_brand_id,
            COALESCE(
                NULLIF(TRIM(b.brand_name), ''),
                NULLIF(TRIM(sb.brandName), ''),
                TRIM(p.brandName)
            ) AS brandName,
            COALESCE(pbv.price, p.price) AS price,
            p.category,
            p.packageType,
            p.stockQuantity,
            COALESCE(p.moq, 1) AS moq,
            COALESCE(NULLIF(pbv.variant_image, ''), pi.image_path, p.imagePath, 'assets/img/placeholder.png') AS image_path
        FROM product p
        LEFT JOIN product_brand_variants pbv
            ON pbv.product_id = p.id
        LEFT JOIN supplier_brands sb
            ON pbv.brand_id = sb.id
        LEFT JOIN brands b
            ON pbv.brand_id = b.brand_id
        LEFT JOIN (
            SELECT pi1.product_id, pi1.image_path
            FROM product_images pi1
            INNER JOIN (
                SELECT product_id, MIN(id) AS first_image_id
                FROM product_images
                GROUP BY product_id
            ) first_pi
                ON pi1.id = first_pi.first_image_id
        ) pi
            ON p.id = pi.product_id
        WHERE p.status = 'Active'
            AND {$active_category['where']}
        ORDER BY COALESCE(pbv.price, p.price) ASC, parentDisplayName ASC, displayName ASC, brandName ASC
    ";

    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
}

$category_links = [
    'panel' => 'Solar Panels',
    'inverter' => 'Inverters',
    'battery' => 'Batteries',
    'package' => 'Package Setups',
    'mounting' => 'Mounting Accessories',
    'all' => 'All Products',
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/icon.png">
    <title><?= htmlspecialchars($active_category['title']) ?> | SolarPower Energy</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .category-page-hero {
            background:
                linear-gradient(90deg, rgba(10, 92, 61, 0.92), rgba(10, 92, 61, 0.58)),
                url('assets/img/products.png') center / cover no-repeat;
            color: #fff;
            padding: 86px 0;
        }

        .category-page-hero h1 {
            margin: 0 0 12px;
            font-size: clamp(36px, 5vw, 64px);
            font-weight: 800;
            line-height: 1.08;
        }

        .category-page-hero p {
            max-width: 680px;
            margin: 0;
            color: rgba(255, 255, 255, 0.93);
            font-size: 17px;
            line-height: 1.7;
        }

        .category-products-section {
            padding: 48px 0 72px;
            background: #f8faf8;
        }

        .category-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 24px;
            padding: 18px;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
        }

        .category-toolbar h2 {
            margin: 0;
            color: #1f2933;
            font-size: 22px;
            font-weight: 800;
        }

        .category-toolbar p {
            margin: 4px 0 0;
            color: #667085;
            font-size: 14px;
        }

        .category-switcher {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .category-switcher a {
            display: inline-flex;
            align-items: center;
            padding: 9px 13px;
            border: 1px solid #e5e7eb;
            border-radius: 999px;
            background: #fff;
            color: #344054;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none !important;
            transition: all 0.2s ease;
        }

        .category-switcher a:hover,
        .category-switcher a.active {
            border-color: #d39d00;
            background: #e7ad00;
            color: #fff;
            box-shadow: 0 4px 12px rgba(231, 173, 0, 0.3);
        }

        .category-product-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 24px;
        }

        .category-product-card {
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }

        .category-product-card:hover {
            transform: translateY(-5px);
            border-color: #e7ad00;
            box-shadow: 0 16px 34px rgba(15, 23, 42, 0.12);
        }

        .category-product-image {
            position: relative;
            width: 100%;
            height: 235px;
            overflow: hidden;
            background: #f8fafc;
        }

        .category-product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.35s ease;
        }

        .category-product-card:hover .category-product-image img {
            transform: scale(1.04);
        }

        .category-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 12px;
            border-radius: 999px;
            background: #e7ad00;
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .category-product-body {
            display: flex;
            flex-direction: column;
            flex: 1;
            padding: 16px;
        }

        .category-product-brand {
            margin-bottom: 7px;
            color: #8a94a6;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            overflow-wrap: anywhere;
        }

        .category-product-title {
            margin: 0 0 12px;
            color: #0a5c3d;
            font-size: 18px;
            font-weight: 800;
            line-height: 1.3;
        }

        .category-product-price {
            margin-top: auto;
            margin-bottom: 14px;
            color: #111827;
            font-size: 23px;
            font-weight: 900;
        }

        .category-min-order {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 14px;
            padding: 5px 10px;
            border: 1px solid #ffc107;
            border-radius: 7px;
            background: #fff3cd;
            color: #856404;
            font-size: 12px;
            font-weight: 700;
        }

        .category-view-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            min-height: 44px;
            border-radius: 7px;
            background: #e7ad00;
            color: #fff;
            font-weight: 800;
            text-decoration: none;
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .category-view-btn:hover {
            background: #d39d00;
            color: #fff;
            transform: translateY(-1px);
        }

        .category-empty-state {
            padding: 52px 24px;
            border: 1px dashed #cbd5e1;
            border-radius: 16px;
            background: #fff;
            text-align: center;
        }

        .category-empty-state i {
            margin-bottom: 14px;
            color: #cbd5e1;
            font-size: 52px;
        }

        @media (max-width: 1199.98px) {
            .category-product-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 991.98px) {
            .category-product-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575.98px) {
            .category-page-hero {
                padding: 64px 0;
            }

            .category-product-grid {
                grid-template-columns: 1fr;
            }

            .category-toolbar {
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <?php include "includes/header.php"; ?>

    <section class="category-page-hero">
        <div class="container">
            <h1><?= htmlspecialchars($active_category['title']) ?></h1>
            <p><?= htmlspecialchars($active_category['subtitle']) ?></p>
        </div>
    </section>

    <section class="category-products-section">
        <div class="container">
            <div class="category-toolbar">
                <div>
                    <h2><?= count($products) ?> product listing<?= count($products) === 1 ? '' : 's' ?> found</h2>
                    <p>Sorted from lowest to highest price.</p>
                </div>

                <nav class="category-switcher" aria-label="Switch product category">
                    <?php foreach ($category_links as $linkKey => $label): ?>
                        <a href="category.php?category=<?= rawurlencode($linkKey) ?>"
                           class="<?= $linkKey === $category_key ? 'active' : '' ?>">
                            <?= htmlspecialchars($label) ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>

            <?php if (!empty($products)): ?>
                <div class="category-product-grid">
                    <?php foreach ($products as $product): ?>
                        <?php
                        $detailsSlugSource = $product['parentDisplayName'] ?? $product['displayName'];
                        $detailsUrl = 'product-details.php?id=' . rawurlencode(createSlug($detailsSlugSource));
                        ?>
                        <article class="category-product-card">
                            <a href="<?= htmlspecialchars($detailsUrl) ?>" class="category-product-image">
                                <img src="<?= htmlspecialchars($product['image_path']) ?>"
                                     alt="<?= htmlspecialchars($product['displayName']) ?>">
                                <span class="category-badge">
                                    <i class="fas fa-tag"></i>
                                    <?= htmlspecialchars($product['category']) ?>
                                </span>
                            </a>

                            <div class="category-product-body">
                                <div class="category-product-brand"><?= renderProductBrandChips($product['brandName']) ?></div>
                                <h3 class="category-product-title"><?= renderProductCardTitle($product['brandName'], $product['displayName']) ?></h3>
                                <div class="category-product-price">₱<?= number_format((float) $product['price'], 2) ?></div>

                                <?php if (stripos((string) $product['category'], 'panel') !== false && (int) $product['moq'] > 1): ?>
                                    <div class="category-min-order">
                                        <i class="fas fa-layer-group"></i>
                                        Min. Order: <?= (int) $product['moq'] ?> pcs
                                    </div>
                                <?php endif; ?>

                                <a href="<?= htmlspecialchars($detailsUrl) ?>" class="category-view-btn">
                                    View Details
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="category-empty-state">
                    <i class="fas fa-box-open"></i>
                    <h3>No products found in this category yet.</h3>
                    <p class="text-muted mb-4">Please check the main catalog or update this category from the staff dashboard.</p>
                    <a href="product.php" class="btn btn-warning fw-bold">Back to Products</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include "includes/footer.php"; ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
