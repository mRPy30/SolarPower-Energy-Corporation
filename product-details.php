<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);

// Get product ID from URL
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$productId) {
    header('Location: index.php');
    exit;
}

/* ---------- DB connection ---------- */
include "config/dbconn.php";

/* ---------- Fetch product details ---------- */
$product = null;
$productImages = [];

$sql = "SELECT 
    p.id,
    p.displayName,
    p.brandName,
    p.price,
    p.category,
    p.description,
    p.stockQuantity,
    p.warranty
FROM product p
WHERE p.id = ?";

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
    p.brandName,
    p.price,
    p.stockQuantity,
    p.category,
    pi.image_path
FROM product p
LEFT JOIN product_images pi ON p.id = pi.product_id
WHERE p.category = ? AND p.id != ?
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/icon.png">
    <title><?= htmlspecialchars($product['displayName']) ?> - Solar Power Energy</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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

        .thumbnail-item:hover,
        .thumbnail-item.active {
            border-color: var(--clr-primary);
        }

        .thumbnail-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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

        .description-text {
            font-size: 15px;
            line-height: 1.8;
            color: var(--clr-text);
        }

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
            box-shadow: 0 12px 24px rgba(0,0,0,0.08);
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

            .product-title {
                font-size: 26px;
            }

            .product-price {
                font-size: 32px;
            }

            .action-buttons {
                flex-direction: column;
            }
        }

        @media (max-width: 576px) {
            .product-details-wrapper {
                padding: 100px 0 60px;
            }

            .main-image-container {
                padding: 20px;
            }

            .main-image {
                height: 300px;
            }
        }
        
        /* ========== CHECKOUT STYLES ========== */
#checkoutSection {
    display: none;
    padding: 40px 0;
    background: #fafafa;
}

.checkout-shell {
    max-width: 1200px;
    margin: auto;
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 40px;
    padding: 40px 20px;
}

.checkout-main {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.checkout-steps {
    display: flex;
    justify-content: space-between;
    margin-bottom: 30px;
    position: relative;
}

.checkout-steps::before {
    content: '';
    position: absolute;
    top: 18px;
    left: 10%;
    right: 10%;
    height: 2px;
    background: #e0e0e0;
    z-index: 0;
}

.checkout-steps .step {
    text-align: center;
    flex: 1;
    position: relative;
    z-index: 1;
}

.checkout-steps .step span {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #e0e0e0;
    color: #666;
    font-weight: bold;
    margin-bottom: 8px;
    transition: all 0.3s ease;
}

.checkout-steps .step p {
    font-size: 12px;
    color: #666;
    margin: 0;
    font-weight: 500;
}

.checkout-steps .step.active span {
    background: #e7ad00;
    color: white;
    box-shadow: 0 4px 12px rgba(231, 173, 0, 0.3);
}

.checkout-steps .step.active p {
    color: #e7ad00;
    font-weight: 600;
}

.checkout-title {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 25px;
    color: #1a1a1a;
}

.checkout-card {
    margin-bottom: 25px;
}

.checkout-card h3 {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 8px;
    color: #1a1a1a;
}

.checkout-actions {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    margin-top: 30px;
}

.btn-primary {
    background: #e7ad00;
    border: none;
    padding: 12px 28px;
    font-weight: 600;
    border-radius: 8px;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: #d39d00;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(231, 173, 0, 0.3);
}

.btn-primary:disabled {
    background: #ccc;
    cursor: not-allowed;
    transform: none;
}

.btn-outline {
    background: transparent;
    border: 2px solid #e0e0e0;
    padding: 12px 28px;
    border-radius: 8px;
    color: #666;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-outline:hover {
    border-color: #1a1a1a;
    color: #1a1a1a;
}

.payment-methods {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin: 25px 0;
}

.payment-option {
    border: 2px solid #e0e0e0;
    padding: 20px;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.payment-option:hover {
    border-color: #e7ad00;
    background: #fffbf0;
}

.payment-option.selected-payment {
    border-color: #e7ad00;
    background: #fff8e1;
    box-shadow: 0 4px 12px rgba(231, 173, 0, 0.2);
}

.payment-option h5 {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 8px;
}

.payment-option p {
    font-size: 13px;
    color: #666;
    margin: 0;
}

#paymentDetails {
    background: #fafafa;
    padding: 20px;
    border-radius: 8px;
    margin-top: 20px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 14px;
}

.total-row {
    font-size: 18px;
    font-weight: 700;
    border-top: 2px solid #e0e0e0;
    margin-top: 10px;
    padding-top: 12px;
}

/* Payment Options Styling */
.payment-options {
    padding: 10px 0;
}

.payment-option {
    border: 2px solid #e0e0e0;
    padding: 15px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.payment-option:hover {
    border-color: var(--clr-primary);
    background-color: #f8f9fa;
}

.payment-option input[type="radio"]:checked + label {
    font-weight: bold;
}

.payment-option input[type="radio"]:checked ~ * {
    color: var(--clr-primary);
}

.form-check-input:checked {
    background-color: var(--clr-primary);
    border-color: var(--clr-primary);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 1rem;
}

.total-row {
    font-weight: bold;
    padding-top: 10px;
}

.checkout-sidebar {
    position: sticky;
    top: 90px;
    height: fit-content;
}

.summary-box {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.summary-box h4 {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 15px;
}

.confirmation-box {
    text-align: center;
    padding: 40px 20px;
}

.success-icon {
    font-size: 60px;
    color: #10b981;
    margin-bottom: 15px;
}

.confirmation-details {
    background: #fafafa;
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
    text-align: left;
}

/* ========== RESPONSIVE DESIGN ========== */
@media (max-width: 992px) {
    .checkout-shell {
        grid-template-columns: 1fr;
    }

    .checkout-sidebar {
        position: static;
    }
}

@media (max-width: 768px) {
    .filter-bar { 
        flex-direction: column; 
        align-items: stretch; 
        padding: 15px;
    }
    
    .filter-buttons { 
        justify-content: center; 
    }
    
    .sort-container { 
        justify-content: space-between; 
        width: 100%; 
    }
    
    .sort-select { 
        flex: 1; 
    }
    
    .products-grid { 
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); 
        gap: 16px; 
    }
    
    .product-image { 
        height: 250px;
    }

    .package-offers-section {
        margin-top: 40px;
        padding-top: 40px;
    }
    
    .package-title {
        font-size: 26px;
        margin-bottom: 30px;
    }
    
    .package-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .package-image {
        height: 220px;
    }

    .checkout-actions {
        flex-direction: column;
    }

    .checkout-steps::before {
        display: none;
    }
}

@media (max-width: 480px) {
    .products-grid { 
        grid-template-columns: 1fr; 
    }
    
    .filter-btn { 
        font-size: 13px; 
        padding: 8px 16px; 
    }
    
    .product-image {
        height: 220px;
    }
}
    </style>
</head>
<body>
    <?php include "includes/header.php" ?>

    <section class="product-details-wrapper">
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
                    <div class="main-image-container">
                        <img src="<?= htmlspecialchars($productImages[0]) ?>" 
                             alt="<?= htmlspecialchars($product['displayName']) ?>" 
                             class="main-image" 
                             id="mainImage">
                    </div>
                    
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

                <!-- Product Info -->
                <div class="product-info-section">
                    <span class="category-badge"><i class="fas fa-tag me-2"></i><?= htmlspecialchars($product['category']) ?></span>
                    
                    <h1 class="product-title"><?= htmlspecialchars($product['displayName']) ?></h1>
                    
                    <p class="product-brand"><?= htmlspecialchars($product['brandName']) ?></p>

                    <div class="price-section">
                        <div class="product-price">₱<?= number_format($product['price'], 2) ?></div>
                    </div>

                    <!-- Stock Status & Warranty -->
                    <div>
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
                                    // Check if brand is Hybrid or Grid-tie (case-insensitive)
                                    $brand = strtolower($product['brandName']);
                                    if ($brand === 'hybrid' || $brand === 'grid-tie') {
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
                        <p class="description-text">
                            <?= nl2br(htmlspecialchars($product['description'] ?? 'No description available for this product.')) ?>
                        </p>
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
                <div class="step active" id="ind-step1"><span>1</span><p>Details</p></div>
                <div class="step" id="ind-step2"><span>2</span><p>Payment</p></div>
                <div class="step" id="ind-step3"><span>3</span><p>Confirm</p></div>
            </div>

            <h2 class="checkout-title">Checkout</h2>

            <div id="checkoutStep1" class="checkout-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3>Delivery & Installation Details</h3>
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
                        <label class="form-label fw-bold">Province</label>
                        <select class="form-select" id="province" required><option value="">Select Province</option></select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label fw-bold">City / Municipality</label>
                        <select class="form-select" id="municipality" disabled required><option value="">Select City / Municipality</option></select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Barangay</label>
                        <select class="form-select" id="barangay" disabled required><option value="">Select Barangay</option></select>
                    </div>
                    <input type="hidden" id="cust_address">
                </div>
                <div class="checkout-actions">
                    <button class="btn-outline" onclick="backToCatalog()">Continue Shopping</button>
                    <button class="btn-primary" onclick="validateStep1()">Proceed to Payment</button>
                </div>
            </div>

            <div id="checkoutStep2" class="checkout-card" style="display:none;">
                <div class="checkout-actions mt-4">
                    <button class="btn-outline" onclick="goToStep(1)">Edit Details</button>
                    <button id="confirmPaymentBtn" class="btn-primary" onclick="submitUnionBankPayment()">Confirm & Submit</button>
                </div>
            </div>

            <div id="checkoutStep3" class="checkout-card" style="display:none;">
                <div class="text-center py-5">
                    <i class="fas fa-check-circle text-success mb-3" style="font-size:64px;"></i>
                    <h3>Order Submitted</h3>
                    <p id="orderRef"></p>
                    <div id="orderQr"></div>
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
    <section class="related-section">
        <div class="container-custom">
            <h2 class="section-title">You May Also Like</h2>
            <div class="related-grid">
                <?php foreach ($relatedProducts as $related): ?>
                <div class="product-card-minimal">
                    <div class="card-image-wrapper" onclick="location.href='product-details.php?id=<?= $related['id'] ?>'">
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
   
    
const productData = <?= json_encode($product) ?>;
        productData.image_path = '<?= $productImages[0] ?>';
        let cart = [];
        let deliveryFee = 0;

        function changeMainImage(el, src) {
            document.getElementById('mainImage').src = src;
            document.querySelectorAll('.thumbnail-item').forEach(t => t.classList.remove('active'));
            el.classList.add('active');
        }

        function updateGlobalDeliveryFee() {
            const select = document.getElementById('delivery_location_select');
            const selectedOption = select.options[select.selectedIndex];
            deliveryFee = parseFloat(selectedOption.getAttribute('data-fee')) || 0;
            renderSummary();
        }

        function addToCartFromDetails() {
            addToCartLogic(productData);
            alert('Added to cart!');
        }

        function buyNowFromDetails() {
            cart = [];
            addToCartLogic(productData);
            document.getElementById('productView').style.display = 'none';
            document.getElementById('checkoutSection').style.display = 'block';
            renderSummary();
            window.scrollTo(0,0);
        }

        function backToProduct() {
            document.getElementById('checkoutSection').style.display = 'none';
            document.getElementById('productView').style.display = 'block';
        }

        function goToStep(step) {
            document.getElementById('checkoutStep1').style.display = step === 1 ? 'block' : 'none';
            document.getElementById('checkoutStep2').style.display = step === 2 ? 'block' : 'none';
            document.getElementById('checkoutStep3').style.display = step === 3 ? 'block' : 'none';
            
            document.querySelectorAll('.step').forEach((s, idx) => {
                s.classList.toggle('active', idx + 1 === step);
            });
        }

        function addToCartLogic(product) {
            cart.push({
                id: product.id,
                name: product.displayName,
                price: parseFloat(product.price),
                img: product.image_path,
                qty: 1
            });
        }

        function renderSummary() {
            const container = document.getElementById('checkoutOrderSummary');
            let subtotal = 0;
            let html = '';

            cart.forEach(item => {
                subtotal += item.price;
                html += `<div class="d-flex justify-content-between mb-2">
                            <small>${item.name}</small>
                            <small>₱${item.price.toLocaleString()}</small>
                         </div>`;
            });

            const total = subtotal + deliveryFee;

            container.innerHTML = html + `
                <hr>
                <div class="d-flex justify-content-between mb-1">
                    <span>Subtotal</span>
                    <span>₱${subtotal.toLocaleString()}</span>
                </div>
                <div class="d-flex justify-content-between mb-1 text-success">
                    <span>Delivery Fee</span>
                    <span>+ ₱${deliveryFee.toLocaleString()}</span>
                </div>
                <div class="d-flex justify-content-between fw-bold mt-2 h5">
                    <span>Total</span>
                    <span class="text-primary">₱${total.toLocaleString()}</span>
                </div>
            `;
        }
    </script>
</body>
</html>