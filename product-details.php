<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);

// Get product ID or slug from URL
$productId = 0;
$slug = '';

if (isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO'])) {
    $slug = trim($_SERVER['PATH_INFO'], '/');
} elseif (isset($_GET['slug']) && !empty($_GET['slug'])) {
    $slug = trim($_GET['slug'], '/');
}

// Function to generate slug
function createSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

if (!empty($slug)) {
    include "config/dbconn.php";
    $sql = "SELECT id, displayName FROM product WHERE status = 'Active'";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        if (createSlug($row['displayName']) === $slug) {
            $productId = intval($row['id']);
            break;
        }
    }
    $conn->close();
}

if (!$productId && isset($_GET['id'])) {
    $productId = intval($_GET['id']);
}

if (!$productId) {
    header('Location: index.php');
    exit;
}

// Calculate base url for path routing
$base_url = '/';
if (strpos($_SERVER['REQUEST_URI'], '/SolarPower-Energy-Corporation/') !== false) {
    $base_url = '/SolarPower-Energy-Corporation/';
}

/* ---------- DB connection ---------- */
include "config/dbconn.php";

/* ---------- Fetch product details ---------- */
$product = null;
$productImages = [];

$sql = "SELECT 
    p.id,
    p.displayName,
    CASE WHEN TRIM(p.brandName) = 'Hybrid' THEN 'Package' ELSE TRIM(p.brandName) END AS brandName,
    p.price,
    p.category,
    p.description,
    p.stockQuantity,
    p.warranty,
    p.imagePath
FROM product p
WHERE p.id = ? AND p.status = 'Active'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
} else {
    header('Location: index.php');
    exit;
}
$stmt->close();

/* ---------- Fetch variant products (sharing same displayName) ---------- */
$variants = [];
$is_variant_cat = in_array(strtolower($product['category'] ?? ''), ['panel', 'panels', 'battery', 'batteries', 'inverter', 'inverters']);
if ($product) {
    if ($is_variant_cat) {
        $vSql = "SELECT pbv.id as variant_junction_id, sb.id as brand_id, sb.brandName, pbv.price, pbv.variant_image as imagePath 
                 FROM product_brand_variants pbv 
                 JOIN supplier_brands sb ON pbv.brand_id = sb.id 
                 WHERE pbv.product_id = ? AND sb.status = 'Active'";
        $vStmt = $conn->prepare($vSql);
        $vStmt->bind_param("i", $product['id']);
        $vStmt->execute();
        $vResult = $vStmt->get_result();
        while ($vRow = $vResult->fetch_assoc()) {
            $variants[] = $vRow;
        }
        $vStmt->close();
    } else {
        $vSql = "SELECT id, brandName, price, imagePath FROM product WHERE displayName = ? AND status = 'Active'";
        $vStmt = $conn->prepare($vSql);
        $vStmt->bind_param("s", $product['displayName']);
        $vStmt->execute();
        $vResult = $vStmt->get_result();
        while ($vRow = $vResult->fetch_assoc()) {
            $variants[] = $vRow;
        }
        $vStmt->close();
    }
}

/* ---------- Fetch product images ---------- */
$sql = "SELECT image_path FROM product_images WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $productImages[] = $row['image_path'];
}
$stmt->close();

// If no images, use placeholder
if (empty($productImages)) {
    $productImages[] = 'assets/img/placeholder.png';
}

/* ---------- Fetch related products (same category) ---------- */
$relatedProducts = [];
$sql = "SELECT 
    p.id,
    p.displayName,
    CASE WHEN TRIM(p.brandName) = 'Hybrid' THEN 'Package' ELSE TRIM(p.brandName) END AS brandName,
    p.price,
    p.stockQuantity,
    p.category,
    pi.image_path
FROM product p
LEFT JOIN product_images pi ON p.id = pi.product_id
WHERE p.category = ? AND p.id != ? AND p.status = 'Active'
GROUP BY p.id
LIMIT 4";

$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $product['category'], $productId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $relatedProducts[] = $row;
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <base href="<?= $base_url ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/icon.png">
    <title><?= htmlspecialchars($product['displayName']) ?> - Solar Power Energy</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
    <style>
        :root {
            --clr-primary: #ffc107;
            --clr-secondary: #0a5c3d;
            --clr-dark: #1a1a1a;
            --clr-text: #333;
            --clr-text-light: #666;
            --clr-border: #e5e5e5;
            --clr-bg: #ffffff;
            --clr-bg-light: #fafafa;
            --transition: all 0.3s ease;
        }

        /* Minimalist Variant Buttons UI */
        .variant-chips-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }
        .variant-option-chip {
            position: relative;
        }
        .variant-option-chip input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        .variant-option-chip label {
            display: inline-block;
            padding: 6px 12px;
            background-color: transparent;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 500;
            color: #495057;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            user-select: none;
            margin: 0;
        }
        .variant-option-chip label:hover {
            border-color: #ced4da;
        }
        .variant-option-chip input[type="radio"]:checked + label {
            border-color: #f59e0b; /* Primary Accent Color */
            border-width: 1px;
            background-color: transparent;
            color: #f59e0b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: var(--clr-text);
            background: var(--clr-bg-light);
            line-height: 1.6;
        }

        /* Product Details Container */
        .product-details-wrapper {
            padding: 100px 0 80px;
            background: var(--clr-bg);
        }

        .container-custom {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Breadcrumb */
        .breadcrumb-minimal {
            display: flex;
            gap: 8px;
            font-size: 14px;
            color: var(--clr-text-light);
            margin-bottom: 40px;
        }

        .breadcrumb-minimal a {
            color: var(--clr-text-light);
            text-decoration: none;
            transition: var(--transition);
        }

        .breadcrumb-minimal a:hover {
            color: var(--clr-primary);
        }

        .breadcrumb-minimal span {
            color: var(--clr-text);
        }

        /* Product Layout */
        .product-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            margin-bottom: 80px;
        }

        /* Image Gallery */
        .gallery-section {
            position: sticky;
            top: 120px;
            height: fit-content;
        }

        .main-image-container {
            background: var(--clr-bg);
            border-radius: 16px;
            padding: 40px;
            margin-bottom: 20px;
            border: 1px solid var(--clr-border);
        }

        .main-image {
            width: 100%;
            height: 500px;
            object-fit: contain;
        }

        .thumbnail-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 12px;
        }

        .thumbnail-item {
            aspect-ratio: 1;
            border-radius: 8px;
            border: 2px solid var(--clr-border);
            overflow: hidden;
            cursor: pointer;
            transition: var(--transition);
        }

        .thumbnail-item:hover {
            border-color: var(--clr-primary);
            opacity: 0.85;
        }

        .thumbnail-item.active {
            border-color: var(--clr-primary);
            box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.35);
        }

        .thumbnail-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.2s ease;
        }

        .thumbnail-item:hover img {
            transform: scale(1.05);
        }


        .category-badge {
            display: inline-block;
            background: var(--clr-primary);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .product-title {
            font-size: 32px;
            font-weight: 700;
            color: var(--clr-dark);
            margin-bottom: 12px;
            line-height: 1.3;
        }

        .product-brand {
            font-size: 16px;
            color: var(--clr-text-light);
            margin-bottom: 32px;
        }

        .price-section {
            padding: 24px 0;
            border-top: 1px solid var(--clr-border);
            border-bottom: 1px solid var(--clr-border);
            margin-bottom: 32px;
        }

        .product-price {
            font-size: 40px;
            font-weight: 700;
            color: var(--clr-dark);
            line-height: 1;
        }

        /* Stock Status */
        .stock-indicator {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 32px;
        }

        .stock-indicator.in-stock {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .stock-indicator.low-stock {
            background: #fff3e0;
            color: #f57c00;
        }

        .stock-indicator.out-of-stock {
            background: #ffebee;
            color: #c62828;
        }

        .stock-indicator i {
            font-size: 16px;
        }

        /* Warranty Badge */
        .warranty-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: var(--clr-bg-light);
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            color: var(--clr-text);
            margin-bottom: 32px;
            margin-left: 12px;
        }

        .warranty-badge i {
            color: var(--clr-primary);
            font-size: 16px;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 16px;
            margin-bottom: 40px;
        }

        .btn-custom {
            flex: 1;
            padding: 18px 32px;
            border-radius: 12px;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
            color: #1a1a1a;
            font-weight: 700;
        }

        .btn-add-cart {
            background: #f5f5f5;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
            color: #1a1a1a;
        }

        .btn-add-cart:hover {
            background: #f5f5f5;
        }

        .btn-buy-now {
            background: #e7ad00;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-buy-now:hover {
            background: #ffb300;
            transform: translateY(-2px);
        }

        .section-heading {
            font-size: 20px;
            font-weight: 700;
            color: var(--clr-dark);
            margin-bottom: 20px;
        }

        .description-text,
        .product-specs-list {
            font-size: 15px;
            line-height: 1.8;
            color: var(--clr-text);
        }

        /* Quill-generated HTML inside .product-specs-list */
        .product-specs-list p   { margin: 0 0 6px; }
        .product-specs-list ul,
        .product-specs-list ol  { padding-left: 1.4rem; margin: 0 0 8px; }
        .product-specs-list li  { margin-bottom: 4px; }
        .product-specs-list br  { display: block; content: ""; margin-top: 6px; }
        .product-specs-list strong { color: var(--clr-dark); }

        /* Features List */
        .features-list {
            display: grid;
            gap: 16px;
            margin-top: 40px;
            padding-top: 40px;
            border-top: 1px solid var(--clr-border);
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 15px;
            color: var(--clr-text);
        }

        .feature-item i {
            color: var(--clr-primary);
            font-size: 18px;
        }

        /* Related Products */
        .related-section {
            padding: 80px 0;
            background: var(--clr-bg-light);
        }

        .section-title {
            font-size: 28px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 48px;
            color: var(--clr-dark);
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 24px;
        }

        .product-card-minimal {
            background: var(--clr-bg);
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            transition: var(--transition);
            border: 1px solid var(--clr-border);
        }

        .product-card-minimal:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
        }

        .card-image {
            width: 100%;
            aspect-ratio: 1;
            object-fit: cover;
        }

        .card-content {
            padding: 20px;
        }

        .card-brand {
            font-size: 13px;
            color: var(--clr-text-light);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--clr-dark);
            margin-bottom: 12px;
            line-height: 1.4;
        }

        .card-price {
            font-size: 20px;
            font-weight: 700;
            color: var(--clr-dark);
            margin-bottom: 16px;
        }

        .product-actions {
            display: flex;
            gap: 8px;
            position: relative;
            z-index: 10;
        }

        .button-add-cart {
            flex: 0 0 44px;
            height: 44px;
            background: #f5f5f5;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
            color: #1a1a1a;
        }

        .button-add-cart:hover {
            background: #e7ad00;
            color: white;
            border-color: #e7ad00;
            transform: scale(1.05);
        }

        .button-buy-now {
            flex: 1;
            height: 44px;
            background: #e7ad00;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .button-buy-now:hover {
            background: #d39d00;
            color: white;
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(231, 173, 0, 0.3);
        }

        /* Responsive */
        @media (max-width: 968px) {
            .product-layout {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .gallery-section {
                position: relative;
                top: 0;
            }

            .product-title { font-size: 26px; }
            .product-price { font-size: 32px; }

            .action-buttons { flex-direction: column; }
        }

        @media (max-width: 576px) {
            .product-details-wrapper { padding: 100px 0 60px; }
            .main-image-container { padding: 20px; }
            .main-image { height: 300px; }
        }
    </style>
</head>

<body>
    <?php include "includes/header.php" ?>

    <section class="product-details-wrapper" data-checkout-hide>
        <div class="container-custom">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="index.php#catalogSection">Products</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($product['displayName']) ?></li>
                </ol>
            </nav>

            <!-- Product Layout -->
            <div class="product-layout">
                <!-- Image Gallery -->
                <div class="gallery-section">
                    <div class="main-image-container" onclick="openImgModal()" style="cursor:zoom-in;">
                        <img src="<?= htmlspecialchars($productImages[0]) ?>"
                            alt="<?= htmlspecialchars($product['displayName']) ?>"
                            class="main-image"
                            id="mainImage">
                    </div>

                    <div id="thumbnailGalleryWrapper">
                        <?php if (count($productImages) > 1): ?>
                            <div class="thumbnail-gallery">
                                <?php foreach ($productImages as $index => $image): ?>
                                    <div class="thumbnail-item <?= $index === 0 ? 'active' : '' ?>"
                                        onclick="changeMainImage(this, '<?= htmlspecialchars($image) ?>')">
                                        <img src="<?= htmlspecialchars($image) ?>"
                                            alt="Thumbnail <?= $index + 1 ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="product-info-section">
                    <span class="category-badge"><i class="fas fa-tag me-2"></i><?= htmlspecialchars($product['category']) ?></span>

                    <h1 class="product-title"><?= htmlspecialchars($product['displayName']) ?></h1>

                    <p class="product-brand"><?= htmlspecialchars($product['brandName']) ?></p>

                    <div class="price-section">
                        <div class="product-price">₱<?= number_format($product['price'], 2) ?></div>
                    </div>

                    <!-- Hidden Product ID Field -->
                    <input type="hidden" name="product_id" id="hidden_product_id" value="<?= $product['id'] ?>">

                    <!-- Brand Variant Selector -->
                    <?php 
                    $eligible_categories = ['panel', 'panels', 'battery', 'batteries', 'inverter', 'inverters'];
                    $is_eligible = in_array(strtolower($product['category'] ?? ''), $eligible_categories);
                    if ($is_eligible && count($variants) > 0): 
                    ?>
                        <div class="brand-variants-section my-3">
                            <label class="fw-bold text-uppercase text-secondary" style="font-size: 11px; letter-spacing: 0.5px; margin-bottom: 8px; display: block;">Select Brand / Supplier</label>
                            <div class="variant-chips-wrapper">
                                <?php foreach ($variants as $index => $variant): ?>
                                    <?php 
                                    $v_id = $variant['variant_junction_id'] ?? $variant['id'];
                                    $v_img = !empty($variant['imagePath']) ? $variant['imagePath'] : $product['imagePath'];
                                    ?>
                                        <div class="variant-option-chip">
                                            <input type="radio" 
                                                   class="variant-radio" 
                                                   name="brand_variant" 
                                                   id="variant_brand_<?= $v_id ?>" 
                                                   value="<?= $v_id ?>"
                                                   data-price="<?= $variant['price'] ?>"
                                                   data-image="<?= htmlspecialchars($v_img) ?>"
                                                   data-brand="<?= htmlspecialchars($variant['brandName']) ?>"
                                                   <?= $index === 0 ? 'checked' : '' ?>>
                                            <label for="variant_brand_<?= $v_id ?>">
                                                <?= htmlspecialchars($variant['brandName']) ?>
                                            </label>
                                        </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Stock Status & Warranty -->
                    <div style="display: none;">
                        <?php
                        $stockQty = $product['stockQuantity'] ?? 0;
                        if ($stockQty > 10):
                        ?>
                            <div class="stock-indicator in-stock">
                                <i class="fas fa-check-circle"></i>
                                <span>In Stock (<?= $stockQty ?> available)</span>
                            </div>
                        <?php elseif ($stockQty > 0): ?>
                            <div class="stock-indicator low-stock">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Low Stock (Only <?= $stockQty ?> left)</span>
                            </div>
                        <?php else: ?>
                            <div class="stock-indicator out-of-stock">
                                <i class="fas fa-times-circle"></i>
                                <span>Out of Stock</span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($product['warranty'])): ?>
                            <div class="warranty-badge">
                                <i class="fas fa-shield-alt"></i>
                                <span>
                                    <?= htmlspecialchars($product['warranty']) ?>
                                    <?php
                                    $brand = strtolower($product['brandName']);
                                    $category = strtolower($product['category']);
                                    if (strpos($category, 'package') !== false || $brand === 'hybrid' || $brand === 'grid-tie') {
                                        echo "Labor Warranty";
                                    } else {
                                        echo "Product Warranty";
                                    }
                                    ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <button class="btn-custom btn-add-cart" onclick="addToCartFromDetails(<?= $product['id'] ?>)">
                            Add to Cart
                        </button>
                        <button class="btn-custom btn-buy-now" onclick="buyNowFromDetails()">
                            Buy Now
                        </button>
                    </div>

                    <!-- Description -->
                    <div class="description-section">
                        <h2 class="section-heading">Description</h2>
                        <?php
                        $desc = $product['description'] ?? '';

                        if (empty(trim($desc))) {
                            // Nothing stored at all
                            echo '<p class="description-text">No description available for this product.</p>';

                        } elseif (strip_tags($desc) !== $desc) {
                            // ── HTML content (posted by Quill.js) ──────────────────────────
                            // Allow the safe subset of HTML Quill produces:
                            // <p><br><strong><em><u><h2><h3><ul><ol><li>
                            $allowedTags = '<p><br><strong><em><u><h2><h3><ul><ol><li><span>';
                            $safeHtml = strip_tags($desc, $allowedTags);
                            echo '<div class="product-specs-list">' . $safeHtml . '</div>';

                        } else {
                            // ── Legacy plain-text (stored before Quill was added) ──────────
                            // Normalise every flavour of escaped newline that may exist in DB
                            $cleanDesc = preg_replace(
                                '/\\\\r\\\\n|\\\\r|\\\\n/',  // literal \r\n, \r, \n in stored string
                                "\n",
                                $desc
                            );
                            echo '<p class="product-specs-list">' . nl2br(htmlspecialchars($cleanDesc)) . '</p>';
                        }
                        ?>
                    </div>

                    <div class="product-info-section p-4 border rounded bg-light shadow-sm">

                        <div class="d-flex align-items-center mb-4">
                            <div class="text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="background: #ffc107; width: 45px; height: 45px;">
                                <i class="fas fa-truck-fast"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">Standard Delivery</h6>
                                <small class="text-muted">Estimated arrival: 7-10 Business Days</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Select Delivery Location</label>
                            <div class="input-group border rounded bg-white">
                                <span class="input-group-text bg-white border-0"><i class="fas fa-map-marker-alt text-danger"></i></span>
                                <select class="form-select border-0 shadow-none" id="delivery_location_select" onchange="updateGlobalDeliveryFee()">
                                    <option value="0" data-fee="0">-- Select Location --</option>

                                    <optgroup label="Metro Manila / Nearby">
                                        <option value="MM 1-5km" data-fee="2000">1-5km Radius — ₱2,000</option>
                                        <option value="MM 6-10km" data-fee="2500">6-10km Radius — ₱2,500</option>
                                        <option value="MM 11-20km" data-fee="4000">11-20km Radius — ₱4,000</option>
                                        <option value="MM 21-30km" data-fee="6000">21-30km Radius — ₱6,000</option>
                                    </optgroup>

                                    <optgroup label="South Luzon">
                                        <option value="Cavite" data-fee="4200">Cavite — ₱4,200</option>
                                        <option value="Laguna" data-fee="6000">Laguna — ₱6,000</option>
                                        <option value="Batangas" data-fee="8500">Batangas — ₱8,500</option>
                                        <option value="Rizal" data-fee="7000">Rizal — ₱7,000</option>
                                    </optgroup>

                                    <optgroup label="North Luzon">
                                        <option value="Bulacan" data-fee="7000">Bulacan — ₱7,000</option>
                                        <option value="Pampanga" data-fee="10000">Pampanga — ₱10,000</option>
                                        <option value="Tarlac" data-fee="10000">Tarlac — ₱10,000</option>
                                    </optgroup>

                                    <optgroup label="Visayas & Mindanao">
                                        <option value="VisMin" data-fee="0">VisMin (Costs vary by weight/distance)</option>
                                    </optgroup>
                                </select>
                            </div>
                        </div>

                        <div id="fee_display_box" class="mt-2 p-3 rounded border border-primary bg-white d-none">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small fw-bold">Delivery Fee:</span>
                                <span class="h5 mb-0 fw-bold text-primary" id="current_fee_amount">₱0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </section>

    <section class="checkout-container" id="checkoutSection" style="display:none; padding-top: 100px;">
        <div class="checkout-shell">
            <div class="checkout-main">
                <div class="checkout-steps" id="checkoutSteps" data-step="1">
                    <div class="step active" id="ind-step1">
                        <span>1</span>
                        <p>Details</p>
                    </div>
                    <div class="step" id="ind-step2">
                        <span>2</span>
                        <p>Payment</p>
                    </div>
                    <div class="step" id="ind-step3">
                        <span>3</span>
                        <p>Confirm</p>
                    </div>
                </div>

                <h2 class="checkout-title">Checkout</h2>

                <!-- Step 1: Delivery Details -->
                <div id="checkoutStep1" class="checkout-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3>Delivery &amp; Installation Details</h3>
                        <button class="btn btn-sm btn-outline-primary" onclick="backToCatalog()">
                            <i class="fas fa-plus"></i> Add More Product
                        </button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-12 mb-2">
                            <label class="form-label fw-bold">Full Name</label>
                            <input type="text" class="form-control" id="cust_name" placeholder="Juan Dela Cruz" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" class="form-control" id="cust_email" placeholder="juan@example.com" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label fw-bold">Contact Number</label>
                            <input type="text" class="form-control" id="cust_phone" placeholder="09123456789" required>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label class="form-label fw-bold">House No. / Street / Subdivision</label>
                            <input type="text" class="form-control" id="house_street" placeholder="House No., Street, Subdivision" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label fw-bold">Province/Region</label>
                            <select class="form-select" id="province" required>
                                <option value="">Select Province</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label fw-bold">City / Municipality</label>
                            <select class="form-select" id="municipality" disabled required>
                                <option value="">Select City / Municipality</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Barangay</label>
                            <select class="form-select" id="barangay" disabled required>
                                <option value="">Select Barangay</option>
                            </select>
                        </div>
                        <input type="hidden" id="cust_address">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div><i class="fas fa-truck me-2"></i><strong>Delivery &amp; Installation Fees Apply</strong></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="checkout-actions">
                        <button class="btn-outline" onclick="backToCatalog()">← Continue Shopping</button>
                        <button class="btn-primary" onclick="validateStep1()">Proceed to Payment →</button>
                    </div>
                </div>

                <!-- Step 2: Payment -->
                <div id="checkoutStep2" class="checkout-card" style="display:none;">
                    <h3>Order Summary &amp; Payment</h3>
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Secure Payment Portal</h5>
                            <span class="badge bg-light text-primary" style="font-size:0.75rem;"><i class="fas fa-lock me-1"></i>BSP Regulated</span>
                        </div>
                        <div class="card-body">
                            <!-- Risk-Free Notice -->
                            <div class="alert alert-success mb-4" style="border-left:4px solid #0D5C3A;background-color:rgba(13,92,58,0.04);color:#1E293B;">
                                <div class="d-flex gap-2 align-items-start">
                                    <i class="fas fa-shield-alt text-success mt-1" style="font-size:1.2rem;"></i>
                                    <div>
                                        <strong style="color:#0D5C3A;">Risk-Free Booking Guarantee:</strong>
                                        <p class="mb-0 small text-muted mt-1">Your payment is fully secured. If our technical site survey reveals roof structural, spacing, or shading issues that prevent solar installation, your booking downpayment is 100% refundable.</p>
                                    </div>
                                </div>
                            </div>
                            <!-- B2B Notice -->
                            <div class="p-3 mb-4 rounded border text-start" style="background-color:#f8fafc;border-left:4px solid #F2A900 !important;">
                                <h6 class="fw-bold mb-1 text-dark"><i class="fas fa-building text-warning me-2"></i>Corporate or Government Buyer?</h6>
                                <p class="small text-muted mb-2">If you represent an SME, large commercial company, or government institution requiring formal bids, corporate invoices, or grid-tie feasibility studies, skip digital checkouts and request a formal proposal directly.</p>
                                <a href="loans.php#checklist" class="btn btn-sm text-dark fw-bold border-0" style="background-color:#F2A900;border-radius:4px;padding:6px 12px;font-size:0.75rem;"><i class="fas fa-file-contract me-1"></i>Request B2B Engineering Proposal</a>
                            </div>
                            <!-- Payment Term -->
                            <div class="payment-options mb-4">
                                <h6 class="fw-bold mb-3"><i class="fas fa-tasks me-2 text-primary"></i>1. Select Payment Option:</h6>
                                <div class="form-check payment-option mb-3 p-3 border rounded" style="cursor:pointer;">
                                    <input class="form-check-input ms-0 me-2" type="radio" name="paymentMethod" id="paymentFull" value="full" checked onchange="updatePaymentDisplay()">
                                    <label class="form-check-label w-100 ps-4" for="paymentFull">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div><strong class="text-dark">Full Payment (100% upfront)</strong><p class="text-muted mb-0 small">Saves 3% off your total system cost</p></div>
                                            <span class="badge bg-success">Best Value</span>
                                        </div>
                                    </label>
                                </div>
                                <div class="form-check payment-option mb-3 p-3 border rounded" style="cursor:pointer;">
                                    <input class="form-check-input ms-0 me-2" type="radio" name="paymentMethod" id="paymentDown" value="downpayment" onchange="updatePaymentDisplay()">
                                    <label class="form-check-label w-100 ps-4" for="paymentDown">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div><strong class="text-dark">50% Down Payment (Secures Booking)</strong><p class="text-muted mb-0 small">Pay 50% now, remaining 50% upon delivery</p></div>
                                            <span class="badge bg-warning text-dark">Standard</span>
                                        </div>
                                    </label>
                                </div>
                                <div class="form-check payment-option mb-3 p-3 border rounded" style="cursor:pointer;">
                                    <input class="form-check-input ms-0 me-2" type="radio" name="paymentMethod" id="paymentInitial" value="initial" onchange="updatePaymentDisplay()">
                                    <label class="form-check-label w-100 ps-4" for="paymentInitial">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div><strong class="text-dark">20% Booking Fee (Mobilization)</strong><p class="text-muted mb-0 small">Secure panels and scheduling. Balance paid pre-installation</p></div>
                                            <span class="badge bg-info text-white">Low Upfront</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <!-- Payment Channels -->
                            <h6 class="fw-bold mb-3"><i class="fas fa-university me-2 text-primary"></i>2. Choose Payment Channel:</h6>
                            <ul class="nav nav-pills mb-3" id="paymentChannelsTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active fw-bold text-uppercase" id="instapay-tab" data-bs-toggle="pill" data-bs-target="#p-instapay" type="button" role="tab" style="font-size:0.8rem;border-radius:8px;"><i class="fas fa-qrcode me-1"></i>InstaPay / GCash</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link fw-bold text-uppercase" id="bank-tab" data-bs-toggle="pill" data-bs-target="#p-bank" type="button" role="tab" style="font-size:0.8rem;border-radius:8px;"><i class="fas fa-university me-1"></i>Bank Accounts</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a href="viber://chat?number=639171234567" target="_blank" class="nav-link fw-bold text-uppercase" style="font-size:0.8rem;color:#7360f2;"><i class="fab fa-viber me-1"></i>Ask Rep / Financing</a>
                                </li>
                            </ul>
                            <div class="tab-content border rounded p-3 mb-4 bg-white" id="paymentTabContent">
                                <div class="tab-pane fade show active text-center" id="p-instapay" role="tabpanel">
                                    <h6 class="mb-2 fw-semibold text-dark">Scan to Pay via InstaPay QR</h6>
                                    <img src="assets/img/UB-QR Code.jpg" alt="InstaPay QR Code" class="img-fluid" style="max-width:260px;border:1px solid #e2e8f0;border-radius:12px;padding:10px;">
                                    <p class="text-muted small mt-2 mb-0">Works with GCash, PayMaya, ShopeePay, and all major Philippine Banking Apps.</p>
                                </div>
                                <div class="tab-pane fade" id="p-bank" role="tabpanel">
                                    <div class="text-start">
                                        <h6 class="mb-3 fw-bold text-dark">Direct Bank Transfer Details:</h6>
                                        <div class="d-flex flex-column gap-3">
                                            <div class="p-2 border rounded" style="background-color:#fafafa;"><strong class="text-dark"><i class="fas fa-university me-1 text-danger"></i>Bank of the Philippine Islands (BPI)</strong><div class="small text-muted mt-1">Account Name: <strong>SolarPower Energy Corporation</strong></div><div class="small text-muted">Account Number: <strong>1234-5678-90</strong></div></div>
                                            <div class="p-2 border rounded" style="background-color:#fafafa;"><strong class="text-dark"><i class="fas fa-university me-1 text-primary"></i>Metropolitan Bank &amp; Trust Company (Metrobank)</strong><div class="small text-muted mt-1">Account Name: <strong>SolarPower Energy Corporation</strong></div><div class="small text-muted">Account Number: <strong>9876-5432-10</strong></div></div>
                                            <div class="p-2 border rounded" style="background-color:#fafafa;"><strong class="text-dark"><i class="fas fa-university me-1 text-warning"></i>UnionBank of the Philippines</strong><div class="small text-muted mt-1">Account Name: <strong>SolarPower Energy Corporation</strong></div><div class="small text-muted">Account Number: <strong>0011-2233-4455</strong></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Receipt Upload -->
                            <div class="alert alert-light border mt-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-upload text-primary me-3" style="font-size:1.5rem;margin-top:2px;"></i>
                                    <div class="w-100">
                                        <strong>Upload Your Transaction Receipt</strong>
                                        <p class="text-muted small mb-2 mt-1">Once you complete the payment via InstaPay, GCash, or Direct Bank Transfer, take a screenshot or photo of your transaction confirmation receipt and upload it below. Our billing team will verify it instantly.</p>
                                        <div class="mb-2 text-start">
                                            <label for="receiptUpload" class="form-label fw-bold"><i class="fas fa-file-image me-1 text-primary"></i>Transaction Receipt <span class="text-danger">*</span></label>
                                            <input type="file" class="form-control" id="receiptUpload" accept="image/*,.pdf" onchange="previewReceipt(this)">
                                            <div class="form-text">Accepted formats: JPG, PNG, PDF (Max 5MB)</div>
                                        </div>
                                        <div id="receiptPreviewContainer" style="display:none;margin-top:10px;">
                                            <p class="small fw-bold text-success"><i class="fas fa-check-circle me-1"></i>Receipt ready to upload:</p>
                                            <img id="receiptPreviewImg" src="" alt="Receipt Preview" style="max-width:200px;max-height:200px;border-radius:8px;border:2px solid #28a745;object-fit:cover;">
                                            <p id="receiptFileName" class="small text-muted mt-1 mb-0"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Payment Summary (hidden, computed by JS) -->
                    <div class="payment-summary-box p-3 bg-light rounded mb-4" style="display:none;">
                        <h5 class="mb-3"><i class="fas fa-file-invoice-dollar me-2"></i>Payment Summary</h5>
                        <div class="summary-row"><span>Items Subtotal:</span><span id="checkoutSubtotal" class="fw-bold"></span></div>
                        <div class="summary-row"><span>Delivery Fee:</span><span id="deliveryFeeDisplay" class="fw-bold text-primary"></span></div>
                        <hr>
                        <div class="summary-row" style="font-size:1.2rem;"><span class="fw-bold">Amount to Pay Now:</span><span id="amountToPay" class="fw-bold text-primary"></span></div>
                        <div class="summary-row total-row" style="font-size:1.3rem;color:#2c3e50;"><span class="fw-bold">Total Order Amount:</span><span id="checkoutTotal" class="fw-bold text-dark"></span></div>
                    </div>
                    <!-- Payment Note (hidden) -->
                    <div id="paymentNote" class="alert alert-success" style="display:none;">
                        <i class="fas fa-info-circle"></i> You are paying the <strong>Full Amount (100%)</strong> via InstaPay.
                    </div>
                    <!-- Actions -->
                    <div class="checkout-actions mt-4">
                        <button class="btn-outline" onclick="goToStep(1)"><i class="fas fa-arrow-left me-2"></i>Edit Details</button>
                        <button id="confirmPaymentBtn" class="btn-primary" onclick="confirmInstapayOrder()"><i class="fas fa-check-circle me-2"></i>Confirm &amp; Submit Order</button>
                    </div>
                </div>

                <!-- Step 3: Confirmation -->
                <div id="checkoutStep3" class="checkout-card" style="display:none;">
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle text-success mb-3" style="font-size:64px;"></i>
                        <h3>Order Submitted Successfully!</h3>
                        <p class="text-muted">Thank you, <strong><span id="confCustomerName"></span></strong>! Your order and receipt have been submitted. We will verify your payment shortly.</p>
                        <div class="alert alert-info mt-4 text-start">
                            <i class="fas fa-info-circle me-2"></i><strong>Next Steps:</strong>
                            <ul class="list-unstyled mt-2 mb-0">
                                <li>✓ Your order has been saved to our database</li>
                                <li>✓ Your receipt has been uploaded for verification</li>
                                <li>✓ You will receive a confirmation within 24 hours</li>
                                <li>✓ Our team will contact you to schedule delivery/installation</li>
                            </ul>
                        </div>
                        <p class="mt-3"><strong>Order Reference:</strong><br><span id="confOrderRef" class="fw-bold fs-5"></span></p>
                        <p class="mt-1"><strong>Total Amount:</strong> <span id="confTotalAmount" class="fw-bold text-primary"></span></p>
                        <button class="btn btn-outline-secondary btn-sm mt-2" onclick="copyOrderRef()"><i class="fas fa-copy"></i> Copy Reference</button>
                        <div class="mt-4"><p class="text-muted small mb-2">Scan or save this QR code to track your order.</p><div id="orderQr" class="d-inline-block p-2 bg-white"></div></div>
                        <button class="btn btn-primary mt-4" onclick="location.href='index.php'">Back to Home</button>
                    </div>
                </div>
            </div>

            <aside class="checkout-sidebar">
                <div class="summary-box shadow-sm">
                    <h4 class="border-bottom pb-2">Your Order</h4>
                    <div id="checkoutOrderSummary"></div>
                </div>
            </aside>
        </div>
    </section>

    <!-- Related Products -->
    <?php if (!empty($relatedProducts)): ?>
        <section class="related-section" data-checkout-hide>
            <div class="container-custom">
                <h2 class="section-title">You May Also Like</h2>
                <div class="related-grid">
                    <?php foreach ($relatedProducts as $related): ?>
                        <div class="product-card-minimal">
                            <div class="card-image-wrapper" onclick="location.href='product-details.php/<?= createSlug($related['displayName']) ?>'">
                                <img src="<?= htmlspecialchars($related['image_path'] ?? 'assets/img/placeholder.png') ?>"
                                    alt="<?= htmlspecialchars($related['displayName']) ?>"
                                    class="card-image">
                            </div>
                            <div class="card-content">
                                <div class="card-brand"><?= htmlspecialchars($related['brandName']) ?></div>
                                <h3 class="card-title"><?= htmlspecialchars($related['displayName']) ?></h3>
                                <div class="card-price">₱<?= number_format($related['price'], 2) ?></div>

                                <div class="product-actions" onclick="event.stopPropagation()">
                                    <button class="button-add-cart"
                                        data-product='<?= json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
                                        onclick="addToCartFromButton(this)"
                                        title="Add to Cart">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>

                                    <button type="button"
                                        class="button-buy-now"
                                        data-product='<?= json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
                                        onclick="buyNowFromButton(this)">
                                        Buy Now
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php include "includes/footer.php" ?>

    <!-- Lightbox Modal — placed here at body level so position:fixed works correctly on all screen sizes -->
    <div id="imgModal" onclick="handleModalBackdropClick(event)" style="display:none;position:fixed;inset:0;z-index:99999;align-items:center;justify-content:center;background:rgba(0,0,0,0.55);">
        <div onclick="event.stopPropagation()" style="display:flex;gap:0;background:#fff;border-radius:4px;max-width:980px;width:95vw;max-height:92vh;overflow:hidden;position:relative;box-shadow:0 8px 48px rgba(0,0,0,0.28);">
            <!-- Close button -->
            <button onclick="closeImgModal()" style="position:absolute;top:14px;right:14px;background:none;border:none;font-size:26px;line-height:1;cursor:pointer;color:#333;z-index:10;width:36px;height:36px;display:flex;align-items:center;justify-content:center;border-radius:50%;transition:background 0.2s;" onmouseover="this.style.background='#f0f0f0'" onmouseout="this.style.background='none'">&times;</button>

            <!-- Main image area -->
            <div style="flex:1;display:flex;align-items:center;justify-content:center;background:#fff;padding:32px 24px;min-height:480px;">
                <img id="modalImage" src="<?= htmlspecialchars($productImages[0]) ?>" style="max-width:100%;max-height:68vh;object-fit:contain;display:block;">
            </div>

            <!-- Thumbnail sidebar -->
            <div id="modalGalleryWrapper" style="width:140px;flex-shrink:0;border-left:1px solid #f0f0f0;background:#fafafa;overflow-y:auto;padding:16px 12px;display:flex;flex-direction:column;gap:10px; <?= count($productImages) > 1 ? '' : 'display:none;' ?>">
                <p style="font-size:12px;color:#999;margin:0 0 6px;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;">All Photos</p>
                <?php foreach ($productImages as $index => $image): ?>
                <div class="modal-thumb <?= $index === 0 ? 'modal-thumb-active' : '' ?>"
                    onclick="changeModalImage(this, '<?= htmlspecialchars($image) ?>')"
                    style="border:2px solid <?= $index === 0 ? 'var(--clr-primary)' : '#e0e0e0' ?>;border-radius:6px;overflow:hidden;cursor:pointer;aspect-ratio:1;transition:all 0.2s;">
                    <img src="<?= htmlspecialchars($image) ?>" alt="Thumb <?= $index + 1 ?>" style="width:100%;height:100%;object-fit:cover;display:block;">
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // ── Product data passed from PHP ──
        const productData = <?= json_encode($product) ?>;
        productData.image_path = '<?= htmlspecialchars($productImages[0] ?? $product['imagePath']) ?>';
        const originalProductId = <?= intval($product['id']) ?>;

        // ── Brand Variant Change Event Listener ──
        document.addEventListener('DOMContentLoaded', function() {
            const variantRadios = document.querySelectorAll('.variant-radio');
            variantRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.checked) {
                        const selectedId = this.value;
                        const price = parseFloat(this.getAttribute('data-price')) || 0;
                        const mainImage = this.getAttribute('data-image') || 'assets/img/placeholder.png';
                        const brandName = this.getAttribute('data-brand') || '';

                        // 1. Update productData properties for cart session
                        productData.id = originalProductId; // Keep original product id for order placement
                        productData.price = price;
                        productData.image_path = mainImage;
                        productData.brandName = brandName;

                        // 2. Update screen inputs & text values
                        const hiddenInput = document.getElementById('hidden_product_id');
                        if (hiddenInput) hiddenInput.value = originalProductId;

                        const priceDisplay = document.querySelector('.product-price');
                        if (priceDisplay) {
                            priceDisplay.textContent = '₱' + price.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        }

                        const brandDisplay = document.querySelector('.product-brand');
                        if (brandDisplay) brandDisplay.textContent = brandName;

                        // 3. Update main image view & modal primary image
                        const mainImgEl = document.getElementById('mainImage');
                        if (mainImgEl) mainImgEl.src = mainImage;
                        
                        const modalImgEl = document.getElementById('modalImage');
                        if (modalImgEl) modalImgEl.src = mainImage;

                        // 4. Trigger fetch to update thumbnail gallery
                        fetch(`get-gallery.php?product_id=${originalProductId}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.success && data.images) {
                                    const thumbWrapper = document.getElementById('thumbnailGalleryWrapper');
                                    const modalWrapper = document.getElementById('modalGalleryWrapper');

                                    // Render main thumbnail gallery list
                                    if (data.images.length > 1) {
                                        let thumbHtml = '<div class="thumbnail-gallery">';
                                        data.images.forEach((img, index) => {
                                            thumbHtml += `
                                                <div class="thumbnail-item ${index === 0 ? 'active' : ''}" onclick="changeMainImage(this, '${img}')">
                                                    <img src="${img}" alt="Thumbnail ${index + 1}">
                                                </div>
                                            `;
                                        });
                                        thumbHtml += '</div>';
                                        if (thumbWrapper) thumbWrapper.innerHTML = thumbHtml;
                                    } else {
                                        if (thumbWrapper) thumbWrapper.innerHTML = '';
                                    }

                                    // Render modal thumbnail sidebar list
                                    if (data.images.length > 1) {
                                        let modalHtml = '<p style="font-size:12px;color:#999;margin:0 0 6px;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;">All Photos</p>';
                                        data.images.forEach((img, index) => {
                                            modalHtml += `
                                                <div class="modal-thumb ${index === 0 ? 'modal-thumb-active' : ''}"
                                                     onclick="changeModalImage(this, '${img}')"
                                                     style="border:2px solid ${index === 0 ? 'var(--clr-primary)' : '#e0e0e0'};border-radius:6px;overflow:hidden;cursor:pointer;aspect-ratio:1;transition:all 0.2s;">
                                                    <img src="${img}" alt="Thumb ${index + 1}" style="width:100%;height:100%;object-fit:cover;display:block;">
                                                </div>
                                            `;
                                        });
                                        if (modalWrapper) {
                                            modalWrapper.innerHTML = modalHtml;
                                            modalWrapper.style.display = 'flex';
                                        }
                                    } else {
                                        if (modalWrapper) {
                                            modalWrapper.innerHTML = '';
                                            modalWrapper.style.display = 'none';
                                        }
                                    }
                                }
                            })
                            .catch(err => console.error('Error fetching image gallery:', err));
                    }
                });
            });
        });

        // ── Cart ──
        let cart = JSON.parse(localStorage.getItem('solarCart') || '[]');
        let deliveryFee = 0;
        let installationFee = 0;

        // ── Image gallery ──
        function closeImgModal() {
            document.getElementById('imgModal').style.display = 'none';
            document.body.style.overflow = '';
        }
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeImgModal(); });
        function openImgModal() {
            document.getElementById('imgModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        function handleModalBackdropClick(e) {
            if (e.target === document.getElementById('imgModal')) closeImgModal();
        }
        function changeMainImage(el, src) {
            document.getElementById('mainImage').src = src;
            document.getElementById('modalImage').src = src;
            document.querySelectorAll('.thumbnail-item').forEach(t => t.classList.remove('active'));
            el.classList.add('active');
            document.querySelectorAll('.modal-thumb').forEach(t => {
                const isMatch = t.querySelector('img').src === el.querySelector('img').src;
                t.style.borderColor = isMatch ? 'var(--clr-primary)' : '#e0e0e0';
                t.classList.toggle('modal-thumb-active', isMatch);
            });
        }
        function changeModalImage(el, src) {
            document.getElementById('modalImage').src = src;
            document.querySelectorAll('.modal-thumb').forEach(t => { t.style.borderColor = '#e0e0e0'; t.classList.remove('modal-thumb-active'); });
            el.style.borderColor = 'var(--clr-primary)';
            el.classList.add('modal-thumb-active');
            document.querySelectorAll('.thumbnail-item').forEach(t => {
                const isMatch = t.querySelector('img') && t.querySelector('img').src === el.querySelector('img').src;
                t.classList.toggle('active', isMatch);
            });
            document.getElementById('mainImage').src = src;
        }

        // ── Cart helpers ──
        function addToCartFromDetails() {
            const existing = cart.find(i => i.id === productData.id);
            if (existing) { existing.quantity = (existing.quantity || 1) + 1; }
            else { cart.push({ id: productData.id, displayName: productData.displayName, price: parseFloat(productData.price), image_path: productData.image_path, quantity: 1 }); }
            localStorage.setItem('solarCart', JSON.stringify(cart));
            alert('Added to cart!');
        }

        // ── Buy Now: show checkout, hide product page ──
        function buyNowFromDetails() {
            // Clear cart and add this product
            cart = [{ id: productData.id, displayName: productData.displayName, price: parseFloat(productData.price), image_path: productData.image_path, quantity: 1 }];
            localStorage.setItem('solarCart', JSON.stringify(cart));
            showCheckout();
        }

        function showCheckout() {
            document.querySelectorAll('[data-checkout-hide]').forEach(el => {
                el.dataset.prevDisplay = el.style.display || '';
                el.style.display = 'none';
            });
            document.getElementById('checkoutSection').style.display = 'block';
            window.scrollTo(0, 0);
            renderCheckoutSummary();
            goToStep(1);
        }

        function backToCatalog() {
            document.getElementById('checkoutSection').style.display = 'none';
            document.querySelectorAll('[data-checkout-hide]').forEach(el => {
                el.style.display = el.dataset.prevDisplay || '';
            });
            window.scrollTo(0, 0);
        }

        // ── Step navigation ──
        function goToStep(step) {
            document.getElementById('checkoutStep1').style.display = step === 1 ? 'block' : 'none';
            document.getElementById('checkoutStep2').style.display = step === 2 ? 'block' : 'none';
            document.getElementById('checkoutStep3').style.display = step === 3 ? 'block' : 'none';
            document.querySelectorAll('.step').forEach((s, idx) => s.classList.toggle('active', idx + 1 === step));
        }

        // ── Step 1 validation ──
        function validateStep1() {
            const name    = document.getElementById('cust_name').value.trim();
            const email   = document.getElementById('cust_email').value.trim();
            const phone   = document.getElementById('cust_phone').value.trim();
            const street  = document.getElementById('house_street').value.trim();
            const province = document.getElementById('province').value;
            const city    = document.getElementById('municipality').value;
            const brgy    = document.getElementById('barangay').value;
            if (!name || !email || !phone || !street || !province || !city || !brgy) {
                alert('Please fill in all delivery details including Province, City, and Barangay.');
                return;
            }
            const fullAddress = `${street}, ${brgy}, ${city}, ${province}`;
            document.getElementById('cust_address').value = fullAddress;
            renderCheckoutSummary();
            goToStep(2);
        }

        // ── Receipt preview ──
        function previewReceipt(input) {
            const file = input.files[0];
            if (!file) return;
            const container = document.getElementById('receiptPreviewContainer');
            const img       = document.getElementById('receiptPreviewImg');
            const name      = document.getElementById('receiptFileName');
            name.textContent = file.name;
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = e => { img.src = e.target.result; img.style.display = 'block'; };
                reader.readAsDataURL(file);
            } else {
                img.style.display = 'none';
            }
            container.style.display = 'block';
        }

        // ── Sidebar order summary ──
        function renderCheckoutSummary() {
            const container = document.getElementById('checkoutOrderSummary');
            if (!container) return;
            let subtotal = 0;
            let html = '';
            cart.forEach(item => {
                const qty   = item.quantity || 1;
                const price = parseFloat(item.price) || 0;
                subtotal += price * qty;
                html += `
                    <div class="d-flex justify-content-between align-items-start mb-3 pb-3 border-bottom">
                        <div style="flex:1">
                            <div class="fw-semibold small">${item.displayName || item.name}</div>
                            <div class="text-muted small">₱${price.toLocaleString()} × ${qty}</div>
                        </div>
                        <div class="fw-bold small ms-2">₱${(price * qty).toLocaleString()}</div>
                    </div>`;
            });
            const total = subtotal + deliveryFee;
            container.innerHTML = html + `
                <div class="d-flex justify-content-between mb-1 small">
                    <span>Subtotal</span><span>₱${subtotal.toLocaleString()}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 small text-primary">
                    <span>Delivery Fee</span><span>₱${deliveryFee.toLocaleString()}</span>
                </div>
                <div class="d-flex justify-content-between fw-bold mt-2" style="font-size:1.1rem;">
                    <span>Total</span><span class="text-primary">₱${total.toLocaleString()}</span>
                </div>`;
            // Update hidden summary elements
            const sub = document.getElementById('checkoutSubtotal');
            const dfd = document.getElementById('deliveryFeeDisplay');
            const atp = document.getElementById('amountToPay');
            const ct  = document.getElementById('checkoutTotal');
            if (sub) sub.textContent = '₱' + subtotal.toLocaleString();
            if (dfd) dfd.textContent = '₱' + deliveryFee.toLocaleString();
            if (ct)  ct.textContent  = '₱' + total.toLocaleString();
            if (atp) atp.textContent = '₱' + total.toLocaleString();
        }

        // ── Update payment display on radio change ──
        function updatePaymentDisplay() { renderCheckoutSummary(); }

        // ── Submit InstaPay order ──
        function confirmInstapayOrder() {
            const btn = document.getElementById('confirmPaymentBtn');
            const receiptInput = document.getElementById('receiptUpload');
            if (!receiptInput || !receiptInput.files[0]) {
                alert('Please upload your payment receipt before confirming.');
                return;
            }
            btn.disabled = true;
            btn.textContent = 'Submitting...';
            const formData = new FormData();
            formData.append('customerName',    document.getElementById('cust_name').value);
            formData.append('customerEmail',   document.getElementById('cust_email').value);
            formData.append('customerPhone',   document.getElementById('cust_phone').value);
            formData.append('customerAddress', document.getElementById('cust_address').value);
            formData.append('totalAmount',     cart.reduce((s, i) => s + parseFloat(i.price) * (i.quantity || 1), 0) + deliveryFee);
            formData.append('paymentMethod',   'instapay');
            formData.append('items',           JSON.stringify(cart.map(i => ({ id: i.id, displayName: i.displayName || i.name, price: i.price, quantity: i.quantity || 1 }))));
            formData.append('receipt',         receiptInput.files[0]);
            fetch('controllers/ordering/create-instapay-order.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('confCustomerName').textContent = document.getElementById('cust_name').value;
                        document.getElementById('confOrderRef').textContent     = data.orderRef;
                        document.getElementById('confTotalAmount').textContent  = '₱' + (cart.reduce((s,i)=>s+parseFloat(i.price)*(i.quantity||1),0)+deliveryFee).toLocaleString();
                        localStorage.removeItem('solarCart');
                        goToStep(3);
                    } else {
                        alert('Error: ' + data.message);
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Confirm & Submit Order';
                    }
                })
                .catch(err => {
                    alert('Network error. Please try again.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Confirm & Submit Order';
                });
        }

        function copyOrderRef() {
            const ref = document.getElementById('confOrderRef').textContent;
            navigator.clipboard.writeText(ref).then(() => alert('Order reference copied!')).catch(() => {});
        }

        // ── PH Address Dropdowns (PSGC API) ──
        const API_BASE = 'https://psgc.gitlab.io/api';
        async function loadProvinces() {
            try {
                const res  = await fetch(`${API_BASE}/provinces/`);
                const data = await res.json();
                const sel  = document.getElementById('province');
                data.sort((a,b) => a.name.localeCompare(b.name))
                    .forEach(p => { const o = new Option(p.name, p.code); sel.add(o); });
            } catch(e) { console.warn('Province load failed', e); }
        }
        async function loadCities(provinceCode) {
            const sel = document.getElementById('municipality');
            sel.innerHTML = '<option value="">Loading…</option>';
            sel.disabled  = true;
            document.getElementById('barangay').innerHTML = '<option value="">Select Barangay</option>';
            document.getElementById('barangay').disabled = true;
            try {
                const res  = await fetch(`${API_BASE}/provinces/${provinceCode}/cities-municipalities/`);
                const data = await res.json();
                sel.innerHTML = '<option value="">Select City / Municipality</option>';
                data.sort((a,b) => a.name.localeCompare(b.name))
                    .forEach(c => sel.add(new Option(c.name, c.code)));
                sel.disabled = false;
            } catch(e) { sel.innerHTML = '<option value="">Error loading cities</option>'; }
        }
        async function loadBarangays(cityCode) {
            const sel = document.getElementById('barangay');
            sel.innerHTML = '<option value="">Loading…</option>';
            sel.disabled  = true;
            try {
                const res  = await fetch(`${API_BASE}/cities-municipalities/${cityCode}/barangays/`);
                const data = await res.json();
                sel.innerHTML = '<option value="">Select Barangay</option>';
                data.sort((a,b) => a.name.localeCompare(b.name))
                    .forEach(b => sel.add(new Option(b.name, b.code)));
                sel.disabled = false;
            } catch(e) { sel.innerHTML = '<option value="">Error loading barangays</option>'; }
        }
        document.getElementById('province').addEventListener('change', e => { if (e.target.value) loadCities(e.target.value); });
        document.getElementById('municipality').addEventListener('change', e => { if (e.target.value) loadBarangays(e.target.value); });
        loadProvinces();
    </script>
</body>

</html>