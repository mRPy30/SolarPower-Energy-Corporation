<?php
session_start();

/* ---------- 1. DB connection ---------- */
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "solar_power";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

/* ---------- 2. Get product ID ---------- */
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$productId) {
    header("Location: index.php");
    exit();
}

/* ---------- 2.  Fetch products (safe) ---------- */
$products = [];
$sql  = "SELECT 
    p.id,
    p.displayName,
    p.brandName,
    p.price,
    p.category,
    pi.image_path
FROM product p
LEFT JOIN product_images pi 
    ON p.id = pi.product_id
GROUP BY p.id";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
}
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    header("Location: index.php");
    exit();
}

/* ---------- 4. Fetch product images ---------- */
$sqlImages = "SELECT image_path FROM product_images WHERE product_id = ?";
$stmtImages = $conn->prepare($sqlImages);
$stmtImages->bind_param("i", $productId);
$stmtImages->execute();
$resultImages = $stmtImages->get_result();
$images = [];
while ($row = $resultImages->fetch_assoc()) {
    $images[] = $row['image_path'];
}
$stmtImages->close();

/* ---------- 5. Fetch related products (same category) ---------- */
$sqlRelated = "SELECT 
    p.id,
    p.displayName,
    p.brandName,
    p.price,
    p.category,
    pi.image_path
FROM product p
LEFT JOIN product_images pi ON p.id = pi.product_id
WHERE p.category = ? AND p.id != ?
GROUP BY p.id
LIMIT 4";

$stmtRelated = $conn->prepare($sqlRelated);
$stmtRelated->bind_param("si", $product['category'], $productId);
$stmtRelated->execute();
$resultRelated = $stmtRelated->get_result();
$relatedProducts = [];
while ($row = $resultRelated->fetch_assoc()) {
    $relatedProducts[] = $row;
}
$stmtRelated->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['displayName']) ?> - Solar Power Energy</title>

    <!-- Bootstrap + Font-Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Your custom CSS -->
    <link rel="stylesheet" href="../../assets/style.css">
    
    <style>
        /* Product Details Specific Styles */
        .product-details-section {
            padding: 40px 0 80px;
            background: #fafafa;
            min-height: calc(100vh - 200px);
        }

        .breadcrumb-nav {
            background: white;
            padding: 20px 0;
            margin-bottom: 30px;
            border-bottom: 1px solid #e5e5e5;
        }

        .breadcrumb {
            margin: 0;
            background: transparent;
            padding: 0;
        }

        .breadcrumb-item + .breadcrumb-item::before {
            content: "›";
            color: #666;
        }

        .breadcrumb-item a {
            color: var(--clr-primary);
            text-decoration: none;
        }

        .product-detail-container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        /* Image Gallery */
        .product-gallery {
            position: sticky;
            top: 100px;
        }

        .main-image-container {
            background: #fafafa;
            border-radius: 12px;
            padding: 40px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 450px;
            border: 1px solid #f0f0f0;
        }

        .main-image-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .thumbnail-gallery {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }

        .thumbnail {
            background: #fafafa;
            border-radius: 8px;
            padding: 15px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .thumbnail:hover {
            border-color: var(--clr-primary);
        }

        .thumbnail.active {
            border-color: var(--clr-primary);
            background: #fff8e1;
        }

        .thumbnail img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        /* Product Info */
        .product-detail-info {
            padding-left: 20px;
        }

        .product-badge-detail {
            display: inline-block;
            background: var(--clr-primary);
            color: var(--clr-dark);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 15px;
        }

        .product-brand-detail {
            font-size: 14px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .product-title-detail {
            font-size: 32px;
            font-weight: 700;
            color: var(--clr-dark);
            margin-bottom: 20px;
            line-height: 1.3;
        }

        .product-price-detail {
            font-size: 42px;
            font-weight: 700;
            color: var(--clr-primary);
            margin-bottom: 25px;
        }

        .stock-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: #ecfdf5;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #059669;
            margin-bottom: 30px;
        }

        .stock-status.out-of-stock {
            background: #fef2f2;
            color: #dc2626;
        }

        .product-description {
            font-size: 15px;
            color: #666;
            line-height: 1.8;
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 1px solid #e5e5e5;
        }

        /* Quantity Selector */
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .quantity-selector label {
            font-size: 15px;
            font-weight: 600;
            color: var(--clr-dark);
        }

        .quantity-controls {
            display: flex;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
        }

        .quantity-btn {
            width: 44px;
            height: 44px;
            background: #f5f5f5;
            border: none;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .quantity-btn:hover {
            background: var(--clr-primary);
            color: white;
        }

        .quantity-input {
            width: 60px;
            height: 44px;
            border: none;
            text-align: center;
            font-size: 16px;
            font-weight: 600;
            border-left: 1px solid #e0e0e0;
            border-right: 1px solid #e0e0e0;
        }

        /* Action Buttons */
        .product-actions-detail {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        .btn-add-to-cart {
            flex: 1;
            padding: 16px 32px;
            background: var(--clr-secondary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-add-to-cart:hover {
            background: #085231;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(10, 92, 61, 0.3);
        }

        .btn-buy-now-detail {
            flex: 1;
            padding: 16px 32px;
            background: var(--clr-primary);
            color: var(--clr-dark);
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-buy-now-detail:hover {
            background: #dfa701;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 193, 7, 0.3);
        }

        /* Product Features */
        .product-features {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 30px;
            padding: 25px;
            background: #fafafa;
            border-radius: 8px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: #666;
        }

        .feature-item i {
            color: var(--clr-primary);
            font-size: 18px;
        }

        /* Tabs */
        .product-tabs {
            margin-top: 50px;
            border-top: 1px solid #e5e5e5;
            padding-top: 40px;
        }

        .nav-tabs {
            border-bottom: 2px solid #e5e5e5;
            margin-bottom: 30px;
        }

        .nav-tabs .nav-link {
            color: #666;
            font-weight: 600;
            padding: 12px 24px;
            border: none;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link:hover {
            color: var(--clr-primary);
            border-color: transparent;
        }

        .nav-tabs .nav-link.active {
            color: var(--clr-primary);
            background: transparent;
            border-color: var(--clr-primary);
        }

        .tab-content {
            padding: 20px 0;
        }

        .specifications-list {
            list-style: none;
            padding: 0;
        }

        .specifications-list li {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
        }

        .specifications-list li:last-child {
            border-bottom: none;
        }

        .spec-label {
            font-weight: 600;
            color: var(--clr-dark);
        }

        .spec-value {
            color: #666;
        }

        /* Related Products */
        .related-products-section {
            margin-top: 60px;
            padding-top: 60px;
            border-top: 1px solid #e5e5e5;
        }

        .related-products-section h3 {
            font-size: 28px;
            font-weight: 700;
            color: var(--clr-dark);
            margin-bottom: 30px;
        }

        .related-products-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        @media (max-width: 992px) {
            .product-detail-info {
                padding-left: 0;
                margin-top: 30px;
            }

            .product-gallery {
                position: static;
            }

            .related-products-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .product-features {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .product-detail-container {
                padding: 20px;
            }

            .product-title-detail {
                font-size: 24px;
            }

            .product-price-detail {
                font-size: 32px;
            }

            .product-actions-detail {
                flex-direction: column;
            }

            .related-products-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<!-- ---------- TOP BAR ---------- -->
<div class="header-top">
    <img src="../../assets/img/DOE.png" alt="DOE">
    <h5>DOE ACCREDITED INSTALLER ESCO 25090095</h5>
</div>

<!-- ---------- HEADER NAV ---------- -->
<?php include "../../includes/header.php"; ?>

<!-- ---------- BREADCRUMB ---------- -->
<div class="breadcrumb-nav">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="index.php#catalogSection">Products</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($product['displayName']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<!-- ---------- PRODUCT DETAILS ---------- -->
<section class="product-details-section">
    <div class="container">
        <div class="product-detail-container">
            <div class="row">
                <!-- Product Gallery -->
                <div class="col-lg-6">
                    <div class="product-gallery">
                        <div class="main-image-container">
                            <img id="mainImage" src="../../<?= htmlspecialchars($images[0] ?? 'assets/img/placeholder.png') ?>" alt="<?= htmlspecialchars($product['displayName']) ?>">
                        </div>
                        
                        <?php if (count($images) > 1): ?>
                        <div class="thumbnail-gallery">
                            <?php foreach ($images as $index => $img): ?>
                                <div class="thumbnail <?= $index === 0 ? 'active' : '' ?>" onclick="changeImage('../../<?= htmlspecialchars($img) ?>', this)">
                                    <img src="../../<?= htmlspecialchars($img) ?>" alt="Thumbnail">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="col-lg-6">
                    <div class="product-detail-info">
                        <span class="product-badge-detail"><?= htmlspecialchars($product['category']) ?></span>
                        <div class="product-brand-detail"><?= htmlspecialchars($product['brandName']) ?></div>
                        <h1 class="product-title-detail"><?= htmlspecialchars($product['displayName']) ?></h1>
                        
                        <div class="product-price-detail">
                            ₱<?= number_format($product['price'], 2) ?>
                        </div>

                        <?php if ($product['stock_quantity'] > 0): ?>
                            <div class="stock-status">
                                <i class="fas fa-check-circle"></i>
                                In Stock (<?= $product['stock_quantity'] ?> available)
                            </div>
                        <?php else: ?>
                            <div class="stock-status out-of-stock">
                                <i class="fas fa-times-circle"></i>
                                Out of Stock
                            </div>
                        <?php endif; ?>

                        <div class="product-description">
                            <?= nl2br(htmlspecialchars($product['description'] ?? 'No description available.')) ?>
                        </div>

                        <!-- Quantity Selector -->
                        <div class="quantity-selector">
                            <label>Quantity:</label>
                            <div class="quantity-controls">
                                <button class="quantity-btn" onclick="decrementQuantity()">−</button>
                                <input type="number" id="quantity" class="quantity-input" value="1" min="1" max="<?= $product['stock_quantity'] ?>">
                                <button class="quantity-btn" onclick="incrementQuantity()">+</button>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="product-actions-detail">
                            <button class="btn-add-to-cart" onclick="addToCartDetail(<?= $product['id'] ?>)">
                                <i class="fas fa-shopping-cart"></i>
                                Add to Cart
                            </button>
                            <button class="btn-buy-now-detail" onclick="buyNowDetail(<?= $product['id'] ?>)">
                                Buy Now
                            </button>
                        </div>

                        <!-- Product Features -->
                        <div class="product-features">
                            <div class="feature-item">
                                <i class="fas fa-shield-alt"></i>
                                <span><?= htmlspecialchars($product['warranty'] ?? '1 Year Warranty') ?></span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-truck"></i>
                                <span>Free Delivery</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-certificate"></i>
                                <span>DOE Certified</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-headset"></i>
                                <span>24/7 Support</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Tabs -->
            <div class="product-tabs">
                <ul class="nav nav-tabs" id="productTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button">
                            Description
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="specifications-tab" data-bs-toggle="tab" data-bs-target="#specifications" type="button">
                            Specifications
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="warranty-tab" data-bs-toggle="tab" data-bs-target="#warranty" type="button">
                            Warranty
                        </button>
                    </li>
                </ul>
                <div class="tab-content" id="productTabContent">
                    <div class="tab-pane fade show active" id="description" role="tabpanel">
                        <p><?= nl2br(htmlspecialchars($product['description'] ?? 'No description available.')) ?></p>
                    </div>
                    <div class="tab-pane fade" id="specifications" role="tabpanel">
                        <?php 
                        $specs = json_decode($product['specifications'] ?? '{}', true);
                        if (!empty($specs)):
                        ?>
                        <ul class="specifications-list">
                            <?php foreach ($specs as $key => $value): ?>
                                <li>
                                    <span class="spec-label"><?= htmlspecialchars($key) ?></span>
                                    <span class="spec-value"><?= htmlspecialchars($value) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php else: ?>
                            <p>No specifications available.</p>
                        <?php endif; ?>
                    </div>
                    <div class="tab-pane fade" id="warranty" role="tabpanel">
                        <p><?= htmlspecialchars($product['warranty'] ?? '1 Year Manufacturer Warranty') ?></p>
                        <ul>
                            <li>Coverage includes manufacturing defects</li>
                            <li>Free replacement for defective units</li>
                            <li>Professional installation support</li>
                            <li>Extended warranty options available</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <?php if (!empty($relatedProducts)): ?>
            <div class="related-products-section">
                <h3>Related Products</h3>
                <div class="related-products-grid">
                    <?php foreach ($relatedProducts as $related): ?>
                        <div class="product-card" onclick="window.location.href='product-details.php?id=<?= $related['id'] ?>'" style="cursor: pointer;">
                            <div class="product-image">
                                <img src="../../<?= htmlspecialchars($related['image_path'] ?? 'assets/img/placeholder.png') ?>" alt="<?= htmlspecialchars($related['displayName']) ?>">
                                <div class="product-badge"><?= htmlspecialchars($related['category']) ?></div>
                            </div>
                            <div class="product-info">
                                <div class="product-brand"><?= htmlspecialchars($related['brandName']) ?></div>
                                <h3 class="product-name"><?= htmlspecialchars($related['displayName']) ?></h3>
                                <div class="product-price">₱<?= number_format($related['price'], 2) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Floating Messenger Button -->
<a href="https://m.me/757917280729034" class="messenger-float" target="_blank" aria-label="Chat with us on Messenger">
    <i class="fab fa-facebook-messenger"></i>
</a>

<!-- ---------- FOOTER ---------- -->
<?php include "../../includes/footer.php"; ?>

<!-- ---------- SCRIPTS ---------- -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/script.js"></script>

<script>
// Cart Storage
let cart = JSON.parse(localStorage.getItem('solarCart')) || [];

// Change Main Image
function changeImage(src, element) {
    document.getElementById('mainImage').src = src;
    document.querySelectorAll('.thumbnail').forEach(thumb => thumb.classList.remove('active'));
    element.classList.add('active');
}

// Quantity Controls
function incrementQuantity() {
    const input = document.getElementById('quantity');
    const max = parseInt(input.max);
    if (parseInt(input.value) < max) {
        input.value = parseInt(input.value) + 1;
    }
}

function decrementQuantity() {
    const input = document.getElementById('quantity');
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
    }
}

// Add to Cart
function addToCartDetail(productId) {
    const quantity = parseInt(document.getElementById('quantity').value);
    
    const product = {
        id: productId,
        name: "<?= addslashes($product['displayName']) ?>",
        brand: "<?= addslashes($product['brandName']) ?>",
        category: "<?= addslashes($product['category']) ?>",
        price: <?= $product['price'] ?>,
        image: "<?= addslashes($images[0] ?? 'assets/img/placeholder.png') ?>",
        quantity: quantity
    };

    const existingIndex = cart.findIndex(item => item.id === productId);
    
    if (existingIndex > -1) {
        cart[existingIndex].quantity += quantity;
    } else {
        cart.push(product);
    }

    localStorage.setItem('solarCart', JSON.stringify(cart));
    updateCartBadge();
    
    showNotification(`Added ${quantity} item(s) to cart!`, 'success');
}

// Buy Now
function buyNowDetail(productId) {
    const quantity = parseInt(document.getElementById('quantity').value);
    
    const product = {
        id: productId,
        name: "<?= addslashes($product['displayName']) ?>",
        brand: "<?= addslashes($product['brandName']) ?>",
        category: "<?= addslashes($product['category']) ?>",
        price: <?= $product['price'] ?>,
        image: "<?= addslashes($images[0] ?? 'assets/img/placeholder.png') ?>",
        quantity: quantity
    };

    cart = [product];
    localStorage.setItem('solarCart', JSON.stringify(cart));
    
    window.location.href = 'index.php#checkout';
}

// Update Cart Badge
function updateCartBadge() {
    const badge = document.querySelector('.cart-badge');
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    
    if (badge) {
        badge.textContent = totalItems;
        badge.style.display = totalItems > 0 ? 'flex' : 'none';
    }
}

// Show Notification
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = 'cart-notification';
    notification.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.4s ease';
        setTimeout(() => notification.remove(), 400);
    }, 3000);
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    updateCartBadge();
});
</script>
</body>
</html>