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

if (!function_exists('brandAssetKey')) {
    function brandAssetKey($text) {
        $text = strtolower(trim((string) $text));
        $text = str_replace(['_', ' '], '-', $text);
        $text = preg_replace('/[^a-z0-9-]+/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }
}

$brand_name = isset($_GET['name']) ? htmlspecialchars($_GET['name'], ENT_QUOTES, 'UTF-8') : '';
$brand_lookup = isset($_GET['name']) ? trim($_GET['name']) : '';

if ($brand_lookup === '') {
    header('Location: product.php');
    exit;
}

$brand_key = brandAssetKey($brand_lookup);
$brand_title = ucwords(str_replace(['-', '_'], ' ', $brand_lookup));
$products = [];

if (isset($conn) && $conn instanceof mysqli) {
    $conn->set_charset('utf8mb4');

    $sql = "
        SELECT
            p.id,
            p.displayName,
            COALESCE(NULLIF(v.brand_names, ''), TRIM(p.brandName)) AS brandName,
            COALESCE(v.min_price, p.price) AS price,
            p.category,
            p.packageType,
            p.stockQuantity,
            COALESCE(p.moq, 1) AS moq,
            COALESCE(pi.image_path, p.imagePath, 'assets/img/placeholder.png') AS image_path
        FROM product p
        LEFT JOIN (
            SELECT
                pbv.product_id,
                GROUP_CONCAT(
                    DISTINCT COALESCE(NULLIF(TRIM(sb.brandName), ''), NULLIF(TRIM(b.brand_name), ''))
                    ORDER BY pbv.price ASC, pbv.id ASC
                    SEPARATOR ', '
                ) AS brand_names,
                MIN(pbv.price) AS min_price
            FROM product_brand_variants pbv
            LEFT JOIN supplier_brands sb
                ON pbv.brand_id = sb.id
            LEFT JOIN brands b
                ON pbv.brand_id = b.brand_id
            GROUP BY pbv.product_id
        ) v
            ON p.id = v.product_id
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
            AND (
                LOWER(REPLACE(REPLACE(TRIM(p.brandName), ' ', '-'), '_', '-')) = ?
                OR LOWER(TRIM(p.brandName)) LIKE CONCAT('%', LOWER(?), '%')
                OR EXISTS (
                    SELECT 1
                    FROM product_brand_variants pbv2
                    LEFT JOIN supplier_brands sb2
                        ON pbv2.brand_id = sb2.id
                    LEFT JOIN brands b2
                        ON pbv2.brand_id = b2.brand_id
                    WHERE pbv2.product_id = p.id
                        AND (
                            LOWER(REPLACE(REPLACE(TRIM(COALESCE(b2.brand_name, sb2.brandName, '')), ' ', '-'), '_', '-')) = ?
                            OR LOWER(COALESCE(b2.brand_name, sb2.brandName, '')) LIKE CONCAT('%', LOWER(?), '%')
                        )
                )
            )
        ORDER BY COALESCE(v.min_price, p.price) ASC, p.displayName ASC
    ";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssss", $brand_key, $brand_lookup, $brand_key, $brand_lookup);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        $stmt->close();
    }
}

$banner_candidates = [
    "assets/img/banner-{$brand_key}.jpg",
    "assets/img/banner-{$brand_key}.png",
    "assets/img/{$brand_key}-banner.jpg",
    "assets/img/{$brand_key}-banner.png",
];

$banner_path = '';
foreach ($banner_candidates as $candidate) {
    if (file_exists(__DIR__ . '/' . $candidate)) {
        $banner_path = $candidate;
        break;
    }
}

$hero_style = $banner_path !== ''
    ? "background-image: linear-gradient(90deg, rgba(0,0,0,0.55), rgba(0,0,0,0.18)), url('" . htmlspecialchars($banner_path, ENT_QUOTES, 'UTF-8') . "');"
    : "background: linear-gradient(135deg, #0a5c3d 0%, #f5b400 100%);";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/icon.png">
    <title>Authorized <?= htmlspecialchars($brand_title) ?> Products | SolarPower</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .brand-hero {
            min-height: 380px;
            display: flex;
            align-items: center;
            background-size: cover;
            background-position: center;
            color: #fff;
        }

        .brand-hero-content {
            max-width: 760px;
            padding: 72px 0;
        }

        .brand-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
            color: #ffc107;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 1.7px;
            text-transform: uppercase;
        }

        .brand-hero h1 {
            margin: 0 0 14px;
            font-size: clamp(34px, 4.6vw, 64px);
            font-weight: 800;
            line-height: 1.08;
            text-shadow: 0 5px 18px rgba(0, 0, 0, 0.28);
        }

        .brand-hero p {
            margin: 0;
            max-width: 620px;
            font-size: 17px;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.94);
        }

        .brand-products-section {
            background: #f8faf8;
            padding: 56px 0 72px;
        }

        .brand-section-title {
            margin-bottom: 24px;
            color: #1f2933;
            font-size: 28px;
            font-weight: 700;
        }

        .brand-product-card {
            height: 100%;
            overflow: hidden;
            padding-top: 0;
            padding-left: 0;
            padding-right: 0;
            border: 1px solid #e3e7df;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.06);
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }

        .brand-product-card:hover {
            transform: translateY(-5px);
            border-color: #f5b400;
            box-shadow: 0 16px 34px rgba(15, 23, 42, 0.12);
        }

        .brand-product-image-link {
            display: block;
            overflow: hidden;
            background: #f7f9fb;
        }

        .brand-product-card .card-img-top {
            width: 100%;
            height: 220px;
            display: block;
            object-fit: cover;
            object-position: center;
            transition: transform 0.35s ease;
        }

        .brand-product-card:hover .card-img-top {
            transform: scale(1.04);
        }

        .brand-product-card .card-body {
            padding: 16px;
        }

        .brand-product-brand {
            margin-bottom: 6px;
            color: #7b8794;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.7px;
            text-transform: uppercase;
        }

        .brand-product-title {
            min-height: 48px;
            margin-bottom: 12px;
            color: #0a5c3d;
            font-size: 17px;
            font-weight: 800;
            line-height: 1.35;
        }

        .brand-product-price {
            margin-bottom: 16px;
            color: #111827;
            font-size: 22px;
            font-weight: 800;
        }

        .brand-buy-btn {
            width: 100%;
            border: none;
            border-radius: 8px;
            background: #e7ad00;
            color: #fff;
            padding: 11px 14px;
            font-weight: 800;
            text-decoration: none;
            text-align: center;
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .brand-buy-btn:hover {
            background: #d39d00;
            color: #fff;
            transform: translateY(-1px);
        }

        .brand-empty-state {
            border: 1px solid #f3d58c;
            border-radius: 14px;
            background: #fff8e6;
            color: #5f4500;
            padding: 28px;
        }

        @media (max-width: 767.98px) {
            .brand-hero {
                min-height: 320px;
            }

            .brand-hero-content {
                padding: 52px 0;
            }

            .brand-product-card .card-img-top {
                height: 210px;
            }
        }
    </style>
</head>
<body>
    <?php include "includes/header.php"; ?>

    <section class="brand-hero" style="<?= $hero_style ?>">
        <div class="container">
            <div class="brand-hero-content">
                <span class="brand-eyebrow"><i class="fas fa-certificate"></i> Authorized Brand Partner</span>
                <h1>Authorized <?= htmlspecialchars($brand_title) ?> Products</h1>
                <p>Explore official SolarPower product selections for <?= htmlspecialchars($brand_title) ?>, backed by trusted distribution support and professional solar expertise.</p>
            </div>
        </div>
    </section>

    <main class="brand-products-section">
        <div class="container">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <h2 class="brand-section-title mb-0"><?= count($products) ?> Product<?= count($products) === 1 ? '' : 's' ?> Found</h2>
                <a href="product.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Catalog
                </a>
            </div>

            <?php if (count($products) === 0): ?>
                <div class="brand-empty-state text-center">
                    <h3 class="h5 fw-bold mb-2">No products found for this brand</h3>
                    <p class="mb-4">We could not find active products under <?= htmlspecialchars($brand_title) ?> right now.</p>
                    <a href="product.php" class="btn btn-warning fw-bold px-4">View Main Catalog</a>
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-4">
                    <?php foreach ($products as $product): ?>
                        <?php
                            $product_id = (int) $product['id'];
                            $product_name = $product['displayName'] ?? 'Solar Product';
                            $product_slug = createSlug($product_name);
                            $product_image = !empty($product['image_path']) ? $product['image_path'] : 'assets/img/placeholder.png';
                            $product_price = (float) ($product['price'] ?? 0);
                        ?>
                        <div class="col">
                            <article class="card brand-product-card">
                                <a class="brand-product-image-link" href="product-details.php/<?= htmlspecialchars($product_slug) ?>">
                                    <img src="<?= htmlspecialchars($product_image) ?>"
                                         class="card-img-top"
                                         alt="<?= htmlspecialchars($product_name) ?>">
                                </a>
                                <div class="card-body d-flex flex-column">
                                    <div class="brand-product-brand"><?= htmlspecialchars($product['brandName'] ?? $brand_title) ?></div>
                                    <h3 class="brand-product-title"><?= htmlspecialchars($product_name) ?></h3>
                                    <div class="brand-product-price">₱<?= number_format($product_price, 2) ?></div>

                                    <?php if (stripos((string)($product['category'] ?? ''), 'panel') !== false && (int)$product['moq'] > 1): ?>
                                        <div class="mb-3 small fw-semibold text-warning-emphasis">
                                            <i class="fas fa-layer-group me-1"></i>Min. Order: <?= (int)$product['moq'] ?> pcs
                                        </div>
                                    <?php endif; ?>

                                    <a href="checkout.php?action=guest&product_id=<?= $product_id ?>" class="brand-buy-btn mt-auto">
                                        Buy Now
                                    </a>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include "includes/footer.php"; ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
