<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
/* ---------- 1.  DB connection ---------- */
include "config/dbconn.php";


/* ---------- 2.  Fetch products (safe) ---------- */
$products = [];

$sql = "SELECT 
    p.id,
    p.displayName,
    p.brandName,
    p.price,
    p.stockQuantity,
    p.category,
    COALESCE(p.moq, 1) AS moq,
    pi.image_path
FROM product p
LEFT JOIN product_images pi 
    ON p.id = pi.product_id
WHERE pi.image_path IS NOT NULL
GROUP BY p.id
ORDER BY 
    CASE 
        WHEN TRIM(p.brandName) = 'Grid-tie' THEN 0 
        WHEN TRIM(p.brandName) = 'Hybrid' THEN 1 
        WHEN TRIM(p.brandName) = 'LuxPower' THEN 2 
        ELSE 3 
    END ASC, 
    p.id DESC";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
}

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/icon.png">
    <title>Solar Power Energy - Smart Energy for Smarter Homes</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<body>

    <style>
        :root {
            --clr-primary: #ffc107; 
            --clr-secondary: #0a5c3d; 
            --clr-dark: #333;
            --clr-light: #fff;
            --clr-text-secondary: #666;
            --clr-bg-section: #f9f9f9;
            --border-radius-md: 8px;
            --shadow-box: 0 4px 15px rgba(0,0,0,0.08);
            --transition-fast: all 0.3s ease;
        }
        
        /* Hero Section */
        .hero-about {
            background: linear-gradient(to right, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.4)), 
                url('assets/img/products.png') no-repeat center center/cover;
            height: 50vh;
            display: flex;
            align-items: center;
            color: white;
            text-align: center;
        }
    </style>
    
    <?php include "includes/header.php" ?>

    <section class="hero-about">
        <div class="container" data-aos="fade-up">
            <span class="text-warning fw-bold text-uppercase">Products</span>
            <h1 class="display-3 fw-bold">Premium Solar Solutions</h1>
        </div>
    </section>

    <section class="featured-brands" id="featured-brands">
    <div class="container">
        <!-- Top Row - Scrolling LEFT -->
        <div class="carousel-wrapper">
            <div class="brands-scroll-list scroll-left">
                <div class="brand-item"><img src="assets/img/hoymiles.png" alt="Hoymiles"></div>
                <div class="brand-item"><img src="assets/img/solax.png" alt="Solax"></div>
                <div class="brand-item"><img src="assets/img/aiko.png" alt="Aiko"></div>
                <div class="brand-item"><img src="assets/img/iansolar.png" alt="AN Solar"></div>
                <div class="brand-item"><img src="assets/img/lvtopsun.png" alt="LA Topsun"></div>
                <div class="brand-item"><img src="assets/img/aesolar.png" alt="AE Solar"></div>
                <div class="brand-item"><img src="assets/img/jinko.png" alt="Jinko"></div>
                <!-- Duplicate for seamless loop -->
                <div class="brand-item"><img src="assets/img/hoymiles.png" alt="Hoymiles"></div>
                <div class="brand-item"><img src="assets/img/solax.png" alt="Solax"></div>
                <div class="brand-item"><img src="assets/img/aiko.png" alt="Aiko"></div>
                <div class="brand-item"><img src="assets/img/iansolar.png" alt="AN Solar"></div>
                <div class="brand-item"><img src="assets/img/lvtopsun.png" alt="LA Topsun"></div>
                <div class="brand-item"><img src="assets/img/aesolar.png" alt="AE Solar"></div>
                <div class="brand-item"><img src="assets/img/jinko.png" alt="Jinko"></div>
            </div>
        </div>
        
        <!-- Bottom Row - Scrolling RIGHT -->
        <div class="carousel-wrapper">
            <div class="brands-scroll-list scroll-right">
                <div class="brand-item"><img src="assets/img/jasolar.png" alt="JA Solar"></div>
                <div class="brand-item"><img src="assets/img/huawei.png" alt="Huawei"></div>
                <div class="brand-item"><img src="assets/img/luxpower.png" alt="LUX Power"></div>
                <div class="brand-item"><img src="assets/img/trinasolar.png" alt="Trina Solar"></div>
                <div class="brand-item"><img src="assets/img/dyness.png" alt="Dyness"></div>
                <div class="brand-item"><img src="assets/img/deye.png" alt="Deye"></div>
                <div class="brand-item"><img src="assets/img/tigfox.png" alt="Tigfox"></div>
                <!-- Duplicate for seamless loop -->
                <div class="brand-item"><img src="assets/img/jasolar.png" alt="JA Solar"></div>
                <div class="brand-item"><img src="assets/img/huawei.png" alt="Huawei"></div>
                <div class="brand-item"><img src="assets/img/luxpower.png" alt="LUX Power"></div>
                <div class="brand-item"><img src="assets/img/trinasolar.png" alt="Trina Solar"></div>
                <div class="brand-item"><img src="assets/img/dyness.png" alt="Dyness"></div>
                <div class="brand-item"><img src="assets/img/deye.png" alt="Deye"></div>
                <div class="brand-item"><img src="assets/img/tigfox.png" alt="Tigfox"></div>
            </div>
        </div>
    </div>
</section>

    <!-- ---------- CATALOG SECTION ---------- -->
 <section class="catalogs-section" id="catalogSection">
        <div class="container">
            <div class="catalog-header">
                <h2>Our Products</h2>
                <p class="catalog-subtitle">Premium solar solutions for your energy needs</p>
            </div>

            <!--SEARCH BAR FUNCTION -->
            <?php include "includes/product-search-bar.php" ?>

            <!-- Filter Bar -->
            <div class="filter-bar" data-aos="fade-up">
                <div class="filter-buttons" id="categoryFilters">
                    <button class="filter-btn active" data-category="all">
                        <i class="fas fa-th"></i> All
                    </button>
                    <button class="filter-btn" data-category="Panel">
                        <i class="fas fa-solar-panel"></i> Panels
                    </button>
                    <button class="filter-btn" data-category="Inverter">
                        <i class="fas fa-plug"></i> Inverters
                    </button>
                    <button class="filter-btn" data-category="Battery">
                        <i class="fas fa-battery-full"></i> Batteries
                    </button>
                    <button class="filter-btn" data-category="Mounting & Accessories">
                        <i class="fas fa-tools"></i> Mounting & Accessories
                    </button>
                    <button class="filter-btn" data-category="Package Setup">
                        <i class="fas fa-box-open"></i> Package Setup
                    </button>
                </div>

                <div class="sort-container">
                    <label class="sort-label">Sort by:</label>
                    <select class="sort-select" id="sortSelect">
                        <option value="default">Default</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                        <option value="name-asc">Name: A to Z</option>
                        <option value="name-desc">Name: Z to A</option>
                    </select>
                </div>
            </div>

            <!-- Products Grid - FIXED onclick handlers -->
            <div class="products-grid" id="productsGrid">
                <?php if ($products): ?>
                    <?php foreach ($products as $index => $p): ?>
                        <div class="product-card <?= $index >= 4 ? 'hidden-product' : '' ?>"
                            data-category="<?= htmlspecialchars($p['category']) ?>"
                            data-brand="<?= htmlspecialchars($p['brandName']) ?>"
                            data-name="<?= htmlspecialchars($p['displayName']) ?>"
                            data-price="<?= htmlspecialchars($p['price']) ?>">

                            <!-- Clickable Product Image and Info -->
                            <div onclick="location.href='product-details.php?id=<?= $p['id'] ?>'" style="cursor: pointer;">
                                <div class="product-image">
                                    <img src="<?= htmlspecialchars($p['image_path'] ?? 'assets/img/placeholder.png') ?>"
                                        alt="<?= htmlspecialchars($p['displayName']) ?>">
                                    <div class="product-badge"> <i class="fas fa-tag"></i> <?= htmlspecialchars($p['category']) ?></div>
                                </div>

                            <div class="product-info">
                                    <div class="product-brand"><?= htmlspecialchars($p['brandName']) ?></div>
                                    <h3 class="product-name"><?= htmlspecialchars($p['displayName']) ?></h3>
                                    <div class="product-price">
                                        ₱<?= number_format($p['price'], 2) ?>
                                    </div>
                                    <div class="preview-stock">
                                        <i class="fas fa-box"></i> Stock: <?= htmlspecialchars($p['stockQuantity']) ?> units
                                    </div>
                                    <?php if ($p['category'] === 'Panel' && intval($p['moq']) > 1): ?>
                                    <div class="moq-badge" style="margin-top:6px; display:inline-block; background:#fff3cd; color:#856404; border:1px solid #ffc107; border-radius:6px; padding:3px 10px; font-size:0.78rem; font-weight:600;">
                                        <i class="fas fa-layer-group"></i> Min. Order: <?= intval($p['moq']) ?> pcs
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Action Buttons (not clickable for navigation) -->
                            <div class="product-actions" onclick="event.stopPropagation()">
                                <button class="btn-add-cart"
                                    data-product='<?= json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
                                    onclick="addToCartFromButton(this)"
                                    title="Add to Cart">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>

                                <button type="button"
                                    class="btn-buy-now"
                                    data-product='<?= json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
                                    onclick="buyNowFromButton(this)">
                                    Buy Now
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-products">
                        <i class="fas fa-box-open"></i>
                        <p>No products available at the moment</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="view-more-container" id="viewMoreContainer">
                <button class="btn-view-more" id="viewMoreBtn" onclick="toggleViewMore()">
                    <span>View More Products</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
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
                        <!-- Delivery Address -->
                        <div class="col-md-12 mb-2">
                            <label class="form-label fw-bold">House No. / Street / Subdivision</label>
                            <input type="text" class="form-control" id="house_street"
                                placeholder="House No., Street, Subdivision" required>
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
                        
                        <!-- Hidden full address (for saving/submitting) -->
                        <input type="hidden" id="cust_address">

                        <!-- Delivery Fee Information -->
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-truck me-2"></i>
                                        <strong>Delivery & Installation Fees Apply</strong>
                                    </div>
                                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#deliveryFeeModal">
                                        View Rates
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="checkout-actions">
                        <button class="btn-outline" onclick="backToCatalog()">← Continue Shopping</button>
                        <button class="btn-primary" onclick="validateStep1()">Proceed to Payment →</button>
                    </div>
                </div>

                <div id="checkoutStep2" class="checkout-card" style="display:none;">
                    <h3>Order Summary & Payment</h3>
                
                    <!-- Payment Method Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-credit-card me-2"></i>Payment Instructions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning mb-4">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Important Notice:</strong> This is not refundable. If your payment does not match 20%, 50%, or 100% of your order total, your order will be void.
                            </div>

                            <!-- InstaPay QR Code Section -->
                            <div class="text-center mb-4">
                                <h5 class="mb-3">Scan to Pay via InstaPay</h5>
                                <img src="assets/img/UB-QR Code.jpg" alt="InstaPay QR Code" class="img-fluid" style="max-width: 300px; border: 2px solid #ddd; border-radius: 10px; padding: 10px;">
                            </div>

                            <!-- Payment Options -->
                            <div class="payment-options mb-4">
                                <h6 class="mb-3">Select Payment Percentage:</h6>
                                
                                <!-- Full Payment (100%) -->
                                <div class="form-check payment-option mb-3">
                                    <input class="form-check-input" type="radio" name="paymentMethod" id="paymentFull" value="full" checked onchange="updatePaymentDisplay()">
                                    <label class="form-check-label w-100" for="paymentFull">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>
                                                    <i class="fas fa-money-bill-wave text-success me-2"></i>
                                                    Full Payment (100%)
                                                </strong>
                                                <p class="text-muted mb-0 small">Pay complete amount now</p>
                                            </div>
                                            <span class="badge bg-success">Recommended</span>
                                        </div>
                                    </label>
                                </div>
        
                                <!-- 50% Down Payment -->
                                <div class="form-check payment-option mb-3">
                                    <input class="form-check-input" type="radio" name="paymentMethod" id="paymentDown" value="downpayment" onchange="updatePaymentDisplay()">
                                    <label class="form-check-label w-100" for="paymentDown">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>
                                                    <i class="fas fa-percentage text-warning me-2"></i>
                                                    50% Down Payment
                                                </strong>
                                                <p class="text-muted mb-0 small">Pay 50% now, 50% before delivery</p>
                                            </div>
                                            <span class="badge bg-warning text-dark">Popular</span>
                                        </div>
                                    </label>
                                </div>
        
                                <!-- 20% Initial Payment -->
                                <div class="form-check payment-option mb-3">
                                    <input class="form-check-input" type="radio" name="paymentMethod" id="paymentInitial" value="initial" onchange="updatePaymentDisplay()">
                                    <label class="form-check-label w-100" for="paymentInitial">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>
                                                    <i class="fas fa-hand-holding-usd text-info me-2"></i>
                                                    20% Initial Payment
                                                </strong>
                                                <p class="text-muted mb-0 small">Pay 20% now, 80% before installation</p>
                                            </div>
                                            <span class="badge bg-info">Flexible</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Receipt Upload Section -->
                            <div class="alert alert-light border mt-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-upload text-primary me-3" style="font-size: 1.5rem; margin-top:2px;"></i>
                                    <div class="w-100">
                                        <strong>Upload Your Transaction Receipt</strong>
                                        <p class="text-muted small mb-2 mt-1">After completing your InstaPay payment, upload a screenshot or photo of your receipt below. Your order will be submitted automatically once you click "Confirm & Submit Order".</p>
                                        <ol class="mb-3 mt-1 small">
                                            <li>Complete your InstaPay payment using the QR code above</li>
                                            <li>Take a screenshot or photo of your transaction receipt</li>
                                            <li>Upload the receipt using the button below</li>
                                            <li>Click <strong>"Confirm & Submit Order"</strong> — your order will be saved automatically</li>
                                        </ol>
                                        <div class="mb-2">
                                            <label for="receiptUpload" class="form-label fw-bold">
                                                <i class="fas fa-file-image me-1 text-primary"></i> Transaction Receipt <span class="text-danger">*</span>
                                            </label>
                                            <input type="file" class="form-control" id="receiptUpload" accept="image/*,.pdf" 
                                                   onchange="previewReceipt(this)">
                                            <div class="form-text">Accepted: JPG, PNG, PDF (Max 5MB)</div>
                                        </div>
                                        <div id="receiptPreviewContainer" style="display:none; margin-top:10px;">
                                            <p class="small fw-bold text-success"><i class="fas fa-check-circle me-1"></i> Receipt ready to upload:</p>
                                            <img id="receiptPreviewImg" src="" alt="Receipt Preview" 
                                                 style="max-width:200px; max-height:200px; border-radius:8px; border:2px solid #28a745; object-fit:cover;">
                                            <p id="receiptFileName" class="small text-muted mt-1 mb-0"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                
                    <!-- Order Summary -->
                    <div class="payment-summary-box p-3 bg-light rounded mb-4">
                        <h5 class="mb-3"><i class="fas fa-file-invoice-dollar me-2"></i>Payment Summary</h5>
                        
                        <div class="summary-row">
                            <span>Items Subtotal:</span>
                            <span id="checkoutSubtotal" class="fw-bold"></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Installation Fee:</span>
                            <span id="installationFeeDisplay" class="fw-bold"></span>
                        </div>

                        <div class="summary-row">
                            <span>Delivery Fee:</span>
                            <span id="deliveryFeeDisplay" class="fw-bold text-primary"></span>
                        </div>
                        
                        <hr>
                        
                        <div class="summary-row" style="font-size: 1.2rem;">
                            <span class="fw-bold">Amount to Pay Now:</span>
                            <span id="amountToPay" class="fw-bold text-primary"></span>
                        </div>
                        
                        <div class="summary-row total-row" style="font-size: 1.3rem; color: #2c3e50;">
                            <span class="fw-bold">Total Order Amount:</span>
                            <span id="checkoutTotal" class="fw-bold text-dark"></span>
                        </div>
                    </div>
                
                    <!-- Payment Note -->
                    <div id="paymentNote" class="alert alert-success">
                        <i class="fas fa-info-circle"></i> You are paying the <strong>Full Amount (100%)</strong> via InstaPay.
                    </div>
                
                    <!-- Action Buttons -->
                    <div class="checkout-actions mt-4">
                        <button class="btn-outline" onclick="goToStep(1)">
                            <i class="fas fa-arrow-left me-2"></i>Edit Details
                        </button>
                        <button id="confirmPaymentBtn" class="btn-primary" onclick="confirmInstapayOrder()">
                            <i class="fas fa-check-circle me-2"></i>Confirm &amp; Submit Order
                        </button>
                    </div>
                </div>

                <div id="checkoutStep3" class="checkout-card" style="display:none;">
                    <div class="text-center py-5">

                        <i class="fas fa-check-circle text-success mb-3" style="font-size:64px;"></i>
                        <h3>Order Submitted Successfully!</h3>
                        <p class="text-muted">Thank you, <strong><span id="confCustomerName"></span></strong>! Your order and receipt have been submitted. We will verify your payment shortly.</p>

                        <div class="alert alert-info mt-4 text-start">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Next Steps:</strong>
                            <ul class="list-unstyled mt-2 mb-0">
                                <li>✓ Your order has been saved to our database</li>
                                <li>✓ Your receipt has been uploaded for verification</li>
                                <li>✓ You will receive a confirmation within 24 hours</li>
                                <li>✓ Our team will contact you to schedule delivery/installation</li>
                            </ul>
                        </div>

                        <!-- Order Reference -->
                        <p class="mt-3">
                            <strong>Order Reference:</strong><br>
                            <span id="confOrderRef" class="fw-bold fs-5"></span>
                        </p>
                        <p class="mt-1">
                            <strong>Total Amount:</strong>
                            <span id="confTotalAmount" class="fw-bold text-primary"></span>
                        </p>

                        <!-- Copy Button -->
                        <button class="btn btn-outline-secondary btn-sm mt-2" onclick="copyOrderRef()">
                            <i class="fas fa-copy"></i> Copy Reference
                        </button>

                        <!-- QR Code -->
                        <div class="mt-4">
                            <p class="text-muted small mb-2">Scan or save this QR code to track your order.</p>
                            <div id="orderQr" class="d-inline-block p-2 bg-white"></div>
                        </div>

                        <button class="btn btn-primary mt-4" onclick="location.href='index.php'">
                            Back to Home
                        </button>
                    </div>
                </div>

                </div>

            <aside class="checkout-sidebar">
                <div class="summary-box shadow-sm">
                    <h4 class="border-bottom pb-2">Your Order</h4>
                    <div id="checkoutOrderSummary">
                        </div>
                </div>
            </aside>
        </div>
    </section>

    <?php include "includes/footer.php" ?>

    <!-- Delivery Fee Modal -->
    <div class="modal fade" id="deliveryFeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-truck me-2"></i>
                        Delivery & Installation Fees
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Delivery Fees -->
                        <div class="col-md-6 mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-shipping-fast me-2"></i>Delivery Fees
                            </h6>
                            <div class="mb-3">
                                <strong>Metro Manila / Nearby Areas:</strong>
                                <ul class="list-unstyled ms-3 mt-2">
                                    <li>• 1-5km: <strong>₱2,000</strong></li>
                                    <li>• 6-10km: <strong>₱2,500</strong></li>
                                    <li>• 11-20km: <strong>₱4,000</strong></li>
                                    <li>• 21-30km: <strong>₱6,000</strong></li>
                                </ul>
                            </div>
                            <div class="mb-3">
                                <strong>South Luzon:</strong>
                                <ul class="list-unstyled ms-3 mt-2">
                                    <li>• Cavite: <strong>₱4,200</strong></li>
                                    <li>• Laguna: <strong>₱6,000</strong></li>
                                    <li>• Batangas: <strong>₱8,500</strong></li>
                                    <li>• Rizal: <strong>₱7,000</strong></li>
                                </ul>
                            </div>
                            <div class="mb-3">
                                <strong>North Luzon:</strong>
                                <ul class="list-unstyled ms-3 mt-2">
                                    <li>• Bulacan: <strong>₱7,000</strong></li>
                                    <li>• Pampanga: <strong>₱10,000</strong></li>
                                    <li>• Tarlac: <strong>₱10,000</strong></li>
                                </ul>
                            </div>
                            <div class="alert alert-info mb-0">
                                <strong>Visayas & Mindanao:</strong><br>
                                Shipping costs may vary due to weight and distance. Please contact us for a quote.
                            </div>
                        </div>
                        <!-- Installation Fees -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-tools me-2"></i>Installation Fees
                            </h6>
                            <div class="alert alert-success">
                                <h5 class="mb-2">Grid-tie & Hybrid Systems</h5>
                                <p class="mb-0">Installation Fee: <strong>₱2,000</strong></p>
                            </div>
                            <div class="alert alert-light border">
                                <h5 class="mb-2">Other Products</h5>
                                <p class="mb-0">Installation: <strong class="text-success">FREE</strong></p>
                            </div>
                            <div class="mt-4">
                                <h6 class="text-dark mb-2">What's Included:</h6>
                                <ul class="small">
                                    <li>Professional installation by certified technicians</li>
                                    <li>System testing and optimization</li>
                                    <li>Basic training on system operation</li>
                                    <li>1-year installation warranty</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });
    </script>

</body>
<script src="assets/script.js"></script>
<script>
    // ============================================
    // SOLAR POWER E-COMMERCE - ORGANIZED JAVASCRIPT
    // ============================================

// ============================================
    // 1. GLOBAL VARIABLES & INITIALIZATION
    // ============================================
    let cart = [];

    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 Solar Power System Initialized');

        // Initialize all modules
        initializeCart();
        initializeFilters();
        initializeSort();
        initializeCheckout();
        initializeSubscription();

        console.log('✅ All modules loaded successfully');
    });

    // ============================================
    // 2. CART MANAGEMENT
    // ============================================

    function initializeCart() {
        console.log('📦 Initializing cart system...');
        loadCartFromMemory();
        updateCartBadge();
    }

    function loadCartFromMemory() {
        if (window.cartStorage) {
            try {
                cart = JSON.parse(window.cartStorage);
                console.log('✅ Cart loaded:', cart.length, 'items');
            } catch (error) {
                console.error('❌ Error loading cart:', error);
                cart = [];
            }
        }
    }

    function saveCartToMemory() {
        try {
            window.cartStorage = JSON.stringify(cart);
            console.log('💾 Cart saved');
        } catch (error) {
            console.error('❌ Error saving cart:', error);
        }
    }

    // keep old name aliases for compatibility
    function loadCartFromStorage() { loadCartFromMemory(); }
    function saveCartToStorage()   { saveCartToMemory(); }

    function addToCartFromButton(btn) {
        console.log('🛒 Adding product to cart...');
        const product = JSON.parse(btn.getAttribute('data-product'));
        addToCartLogic(product);
        showCartPopup();
        showNotificationModal('success', '✅ Product added to cart!');
    }

    function addToCartLogic(product) {
        const existingItem = cart.find(item => item.id === product.id);
        const moq = parseInt(product.moq) || 1;

        if (existingItem) {
            existingItem.quantity += 1;
            console.log('📈 Increased quantity for:', product.displayName);
        } else {
            cart.push({
                id: product.id,
                displayName: product.displayName,
                brandName: product.brandName || '',
                price: parseFloat(product.price),
                image_path: product.image_path,
                quantity: moq,
                moq: moq
            });
            if (moq > 1) {
                showNotificationModal('info', `ℹ️ Minimum order for Solar Panels is ${moq} units.`);
            }
            console.log('➕ Added new item:', product.displayName, '| MOQ:', moq);
        }

        saveCartToMemory();
        updateCartBadge();
    }

    function updateCartBadge() {
        const badge = document.querySelector('.cart-badge');
        if (!badge) return;

        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);

        if (totalItems > 0) {
            badge.textContent = totalItems;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }

    function updateCartQuantity(productId, change) {
        const item = cart.find(i => i.id === productId);
        if (item) {
            const moq = item.moq || 1;
            item.quantity += change;

            if (item.quantity < moq) {
                item.quantity = moq;
                if (moq > 1) {
                    showNotificationModal('info', `ℹ️ Minimum order quantity is ${moq} unit(s).`);
                }
                return;
            }

            saveCartToMemory();
            updateCartBadge();
            renderCartPopup();
        }
    }

    function removeFromCartPopup(productId) {
        if (confirm('Remove this item from cart?')) {
            cart = cart.filter(i => i.id !== productId);
            saveCartToMemory();
            updateCartBadge();
            renderCartPopup();
            showNotificationModal('success', 'Item removed from cart');
        }
    }

    function clearCart() {
        cart = [];
        saveCartToMemory();
        updateCartBadge();
    }

    // ============================================
    // 3. CART POPUP MODAL
    // ============================================

    function createCartModal() {
        let modal = document.getElementById('cartModal');
        if (modal) return modal;

        const modalHTML = `
        <div class="modal fade" id="cartModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Shopping Cart
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="cartModalBody">
                        <!-- Cart items will be rendered here -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Continue Shopping
                        </button>
                        <button type="button" class="btn btn-primary" onclick="proceedToCheckout()" id="proceedCheckoutBtn">
                            <i class="fas fa-arrow-right me-2"></i>
                            Proceed to Checkout
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
        return document.getElementById('cartModal');
    }

    function showCartPopup() {
        const modal = createCartModal();
        renderCartPopup();
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }

    function renderCartPopup() {
        const modalBody = document.getElementById('cartModalBody');
        const proceedBtn = document.getElementById('proceedCheckoutBtn');

        if (!modalBody) return;

        if (cart.length === 0) {
            modalBody.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart text-muted" style="font-size: 64px;"></i>
                <p class="mt-3 text-muted">Your cart is empty</p>
                <button class="btn btn-primary" data-bs-dismiss="modal">
                    Start Shopping
                </button>
            </div>
        `;
            if (proceedBtn) proceedBtn.disabled = true;
            return;
        }

        if (proceedBtn) proceedBtn.disabled = false;

        let subtotal = 0;
        let html = '<div class="cart-items-list">';

        cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;
            const moq = item.moq || 1;
            const minusDisabled = item.quantity <= moq ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '';

            html += `
            <div class="cart-item-row d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                <img src="${item.image_path}" 
                     style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;"
                     onerror="this.src='assets/img/placeholder.png'">
                <div class="flex-grow-1">
                    <h6 class="mb-1 fw-bold">${item.displayName}</h6>
                    <p class="text-muted mb-1" style="font-size: 0.9rem;">
                        ₱${item.price.toLocaleString()} × ${item.quantity}
                    </p>
                    <p class="mb-0 fw-bold text-primary">
                        ₱${itemTotal.toLocaleString(undefined, {minimumFractionDigits: 2})}
                    </p>
                    ${(item.moq || 1) > 1 ? `<small style="color:#856404;background:#fff3cd;border-radius:4px;padding:1px 7px;font-size:0.72rem;"><i class="fas fa-layer-group"></i> MOQ: ${item.moq} pcs</small>` : ''}
                </div>
                <div class="d-flex flex-column gap-2">
                    <div class="quantity-controls d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-outline-secondary" 
                                onclick="updateCartQuantity(${item.id}, -1)" 
                                ${minusDisabled}>
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="fw-bold px-2">${item.quantity}</span>
                        <button class="btn btn-sm btn-outline-secondary" 
                                onclick="updateCartQuantity(${item.id}, 1)">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <button class="btn btn-sm btn-danger" 
                            onclick="removeFromCartPopup(${item.id})">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        `;
        });

        html += '</div>';

        html += `
        <div class="cart-summary bg-light p-3 rounded mt-3">
            <div class="d-flex justify-content-between mb-2">
                <span>Subtotal:</span>
                <span class="fw-bold">₱${subtotal.toLocaleString(undefined, {minimumFractionDigits: 2})}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Delivery Fee:</span>
                <span class="text-info fw-bold">Calculated at checkout</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Installation Fee:</span>
                <span class="text-info fw-bold">Calculated at checkout</span>
            </div>
            <hr>
            <div class="d-flex justify-content-between" style="font-size: 1.2rem;">
                <span class="fw-bold">Subtotal:</span>
                <span class="fw-bold text-primary">₱${subtotal.toLocaleString(undefined, {minimumFractionDigits: 2})}</span>
            </div>
            <small class="text-muted d-block mt-2">*Final total including delivery and installation fees will be shown at checkout</small>
        </div>
    `;

        modalBody.innerHTML = html;
    }

    function buyNowFromButton(btn) {
        const product = JSON.parse(btn.getAttribute('data-product'));

        // Clear cart and add only this product
        cart = [];
        addToCartLogic(product);

        // Go directly to checkout
        proceedToCheckout();
    }

    // ============================================
    // 4. CHECKOUT PROCESS
    // ============================================

    function initializeCheckout() {
        console.log('🛒 Initializing checkout system...');

        const phoneInput = document.getElementById('cust_phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', formatPhoneNumber);
        }

        initializeAddressDropdowns();
    }

    // ============================================
    // PHILIPPINE ADDRESS CASCADE DROPDOWNS
    // Uses PSGC API: https://psgc.gitlab.io/api/
    // ============================================
    const PSGC_BASE = 'https://psgc.gitlab.io/api';

    async function psgcFetch(url) {
        const res = await fetch(url);
        if (!res.ok) throw new Error('PSGC fetch failed: ' + res.status);
        return res.json();
    }

    function setSelectError(selectId, msg) {
        const sel = document.getElementById(selectId);
        if (!sel) return;
        sel.innerHTML = '<option value="">' + msg + '</option>';
        sel.disabled = false;
    }

    function populateSelect(selectId, items, valueKey, labelKey, placeholder) {
        const sel = document.getElementById(selectId);
        if (!sel) return;
        sel.innerHTML = '<option value="">' + placeholder + '</option>';
        items
            .sort((a, b) => (a[labelKey] || '').localeCompare(b[labelKey] || ''))
            .forEach(function(item) {
                const opt = document.createElement('option');
                opt.value = item[valueKey];
                opt.textContent = item[labelKey];
                sel.appendChild(opt);
            });
        sel.disabled = false;
    }

    async function initializeAddressDropdowns() {
        const provinceEl     = document.getElementById('province');
        const municipalityEl = document.getElementById('municipality');
        const barangayEl     = document.getElementById('barangay');

        if (!provinceEl) return;

        provinceEl.addEventListener('change', async function () {
            const code = this.value;

            municipalityEl.innerHTML = '<option value="">Select City / Municipality</option>';
            municipalityEl.disabled = true;
            barangayEl.innerHTML = '<option value="">Select Barangay</option>';
            barangayEl.disabled = true;

            if (!code) return;

            if (code.startsWith('NCR_')) {
                const cityCode = code.replace('NCR_', '');
                barangayEl.innerHTML = '<option value="">Loading barangays...</option>';
                try {
                    const barangays = await psgcFetch(PSGC_BASE + '/cities/' + cityCode + '/barangays/');
                    if (!barangays || barangays.length === 0) throw new Error('No barangays');
                    populateSelect('barangay', barangays, 'name', 'name', 'Select Barangay');
                    municipalityEl.innerHTML = '<option value="' + cityCode + '">' + provinceEl.options[provinceEl.selectedIndex].text + '</option>';
                    municipalityEl.disabled = false;
                } catch(e) {
                    setSelectError('barangay', 'Failed to load barangays. Please refresh.');
                }
                return;
            }

            municipalityEl.innerHTML = '<option value="">Loading cities...</option>';
            try {
                const cities = await psgcFetch(PSGC_BASE + '/provinces/' + code + '/cities/').catch(function() { return []; });
                const municipalities = await psgcFetch(PSGC_BASE + '/provinces/' + code + '/municipalities/').catch(function() { return []; });
                const combined = cities.concat(municipalities);
                if (combined.length === 0) throw new Error('No cities found');
                populateSelect('municipality', combined, 'code', 'name', 'Select City / Municipality');
            } catch(e) {
                console.error('City load error:', e);
                setSelectError('municipality', 'Failed to load cities. Please refresh.');
            }
        });

        municipalityEl.addEventListener('change', async function () {
            const code = this.value;

            barangayEl.innerHTML = '<option value="">Select Barangay</option>';
            barangayEl.disabled = true;

            if (!code) return;

            barangayEl.innerHTML = '<option value="">Loading barangays...</option>';
            try {
                let barangays = await psgcFetch(PSGC_BASE + '/cities/' + code + '/barangays/').catch(function() { return null; });
                if (!barangays || barangays.length === 0) {
                    barangays = await psgcFetch(PSGC_BASE + '/municipalities/' + code + '/barangays/').catch(function() { return []; });
                }
                if (!barangays || barangays.length === 0) throw new Error('No barangays found');
                populateSelect('barangay', barangays, 'name', 'name', 'Select Barangay');
            } catch(e) {
                console.error('Barangay load error:', e);
                setSelectError('barangay', 'Failed to load barangays. Please refresh.');
            }
        });

        provinceEl.innerHTML = '<option value="">Loading provinces...</option>';
        provinceEl.disabled = true;
        try {
            const provinces = await psgcFetch(PSGC_BASE + '/provinces/');
            if (!provinces || provinces.length === 0) throw new Error('Empty provinces response');
            populateSelect('province', provinces, 'code', 'name', 'Select Province');

            const ncrCities = await psgcFetch(PSGC_BASE + '/regions/130000000/cities/').catch(function() { return []; });
            if (ncrCities && ncrCities.length > 0) {
                const optgroup = document.createElement('optgroup');
                optgroup.label = '--- NCR (Metro Manila) ---';
                ncrCities
                    .sort(function(a, b) { return a.name.localeCompare(b.name); })
                    .forEach(function(city) {
                        const opt = document.createElement('option');
                        opt.value = 'NCR_' + city.code;
                        opt.textContent = city.name + ' (NCR)';
                        optgroup.appendChild(opt);
                    });
                provinceEl.appendChild(optgroup);
            }
        } catch(e) {
            console.error('Province load error:', e);
            setSelectError('province', 'Failed to load provinces. Please refresh.');
        }
    }

    function formatPhoneNumber(event) {
        let value = event.target.value.replace(/\D/g, '');

        if (value.startsWith('09')) {
            value = '+639' + value.substring(2);
        } else if (value.startsWith('9') && value.length >= 10) {
            value = '+639' + value.substring(1);
        } else if (value.startsWith('639')) {
            value = '+' + value;
        } else if (value.startsWith('63') && value.length >= 12) {
            value = '+639' + value.substring(2);
        } else if (value.length > 0 && !value.startsWith('0') && !value.startsWith('6') && !value.startsWith('9')) {
            value = '+639' + value;
        }

        if (value.length > 13) {
            value = value.substring(0, 13);
        }

        event.target.value = value;
    }

    function proceedToCheckout() {
        console.log('📋 Proceeding to checkout...');

        if (cart.length === 0) {
            showNotificationModal('error', 'Your cart is empty');
            return;
        }

        const cartModal = bootstrap.Modal.getInstance(document.getElementById('cartModal'));
        if (cartModal) {
            cartModal.hide();
        }

        showCheckout();
        renderCheckoutSummary();
    }

    function showCheckout() {
        const sectionsToHide = [
            '.hero-about', '.featured-brands', '.hero',
            '#catalogSection', '.contact-us', '.subscription-section', 'footer'
        ];

        sectionsToHide.forEach(selector => {
            const el = document.querySelector(selector);
            if (el) el.style.display = 'none';
        });

        document.getElementById('checkoutSection').style.display = 'block';
        window.scrollTo(0, 0);

        goToStep(1);
    }

    function backToCatalog() {
        console.log('🔙 Returning to catalog...');

        document.getElementById('checkoutSection').style.display = 'none';

        const sectionsToShow = [
            '.hero-about', '.featured-brands',
            '#catalogSection', '.contact-us', '.subscription-section', 'footer'
        ];

        sectionsToShow.forEach(selector => {
            const el = document.querySelector(selector);
            if (el) el.style.display = 'block';
        });

        window.scrollTo(0, document.getElementById('catalogSection').offsetTop - 100);
    }

    function goToStep(step) {
        console.log('📍 Moving to step:', step);

        for (let i = 1; i <= 3; i++) {
            document.getElementById(`checkoutStep${i}`).style.display = 'none';
            document.getElementById(`ind-step${i}`).classList.remove('active', 'completed');
        }

        document.getElementById(`checkoutStep${step}`).style.display = 'block';
        document.getElementById(`ind-step${step}`).classList.add('active');

        for (let i = 1; i < step; i++) {
            document.getElementById(`ind-step${i}`).classList.add('completed');
        }

        const checkoutSteps = document.getElementById('checkoutSteps');
        if (checkoutSteps) {
            checkoutSteps.setAttribute('data-step', step);
        }

        window.scrollTo(0, 0);
    }

    function renderCheckoutSummary() {
        console.log('📊 Rendering checkout summary...');

        const summaryDiv = document.getElementById('checkoutOrderSummary');
        const subtotalDisplay = document.getElementById('checkoutSubtotal');
        const totalDisplay = document.getElementById('checkoutTotal');

        if (cart.length === 0) {
            summaryDiv.innerHTML = '<p class="text-center text-muted">Your cart is empty.</p>';
            if (subtotalDisplay) subtotalDisplay.innerText = "₱0.00";
            if (totalDisplay) totalDisplay.innerText = "₱0.00";
            return;
        }

        let cartSubtotal = 0;
        let html = '';

        cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            cartSubtotal += itemTotal;

            const moq = item.moq || 1;
            const minusDisabled = item.quantity <= moq ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '';

            html += `
            <div class="d-flex align-items-center gap-3 mb-3 border-bottom pb-3">
                <img src="${item.image_path}" 
                     style="width:60px; height:60px; object-fit:cover; border-radius:8px;"
                     onerror="this.src='assets/img/placeholder.png'">
                <div class="flex-grow-1">
                    <p class="mb-1 fw-bold" style="font-size: 0.95rem;">${item.displayName}</p>
                    <small class="text-muted">₱${item.price.toLocaleString()} x ${item.quantity}</small>
                    <p class="mb-0 fw-bold text-primary" style="font-size: 0.9rem;">
                        ₱${itemTotal.toLocaleString(undefined, {minimumFractionDigits: 2})}
                    </p>
                </div>
                <div class="d-flex flex-column align-items-end gap-2">
                    <div class="quantity-controls d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-outline-secondary" 
                                onclick="updateCheckoutQuantity(${item.id}, -1)" 
                                ${minusDisabled}>
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="fw-bold px-2">${item.quantity}</span>
                        <button class="btn btn-sm btn-outline-secondary" 
                                onclick="updateCheckoutQuantity(${item.id}, 1)">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <button class="btn btn-sm btn-danger" 
                            onclick="removeFromCheckout(${item.id})">
                        <i class="fas fa-trash-alt me-1"></i> Remove
                    </button>
                </div>
            </div>
        `;
        });

        summaryDiv.innerHTML = html;

        const deliveryFee = calculateDeliveryFee();
        const installationFee = hasGridTieOrHybridProduct() ? 2000 : 0;
        const grandTotal = cartSubtotal + deliveryFee + installationFee;

        const formattedSubtotal = "₱" + cartSubtotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        const formattedTotal = "₱" + grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});

        if (subtotalDisplay) subtotalDisplay.innerText = formattedSubtotal;
        if (totalDisplay) totalDisplay.innerText = formattedTotal;

        window.currentTotalAmount = grandTotal;

        updatePaymentDisplay();
    }

    function updateCheckoutQuantity(productId, change) {
        updateCartQuantity(productId, change);
        renderCheckoutSummary();
    }

    function removeFromCheckout(productId) {
        if (confirm('Remove this item?')) {
            cart = cart.filter(i => i.id !== productId);
            saveCartToMemory();
            updateCartBadge();
            renderCheckoutSummary();

            if (cart.length === 0) {
                showNotificationModal('info', 'Cart is empty. Returning to catalog.');
                setTimeout(() => backToCatalog(), 1500);
            }
        }
    }

    function buildFullAddress() {
        const house = document.getElementById("house_street").value.trim();
        const provinceSel = document.getElementById("province");
        const municipalitySel = document.getElementById("municipality");
        const barangaySel = document.getElementById("barangay");

        let provinceText = provinceSel.options[provinceSel.selectedIndex]?.text || '';
        provinceText = provinceText.replace(' (NCR)', '').replace('--- NCR (Metro Manila) ---', 'Metro Manila');
        const municipalityText = municipalitySel.options[municipalitySel.selectedIndex]?.text || '';
        const barangayText = barangaySel.value || '';

        document.getElementById("cust_address").value =
            `${house}, ${barangayText}, ${municipalityText}, ${provinceText}`;
    }

    function validateStep1() {
        console.log('✅ Validating customer details...');

        clearErrorStates();
        buildFullAddress();

        const name = document.getElementById('cust_name').value.trim();
        const email = document.getElementById('cust_email').value.trim();
        const phone = document.getElementById('cust_phone').value.trim();
        const address = document.getElementById('cust_address').value.trim();
        const house = document.getElementById('house_street')?.value.trim();
        const province = document.getElementById('province')?.value;
        const municipality = document.getElementById('municipality')?.value;
        const barangay = document.getElementById('barangay')?.value;

        let errorMessage = '';

        if (!name) {
            setErrorState('cust_name');
            errorMessage = 'Please enter your full name.';
        } else if (!email) {
            setErrorState('cust_email');
            errorMessage = 'Please enter your email address.';
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            setErrorState('cust_email');
            errorMessage = 'Please enter a valid email address.';
        } else if (!phone) {
            setErrorState('cust_phone');
            errorMessage = 'Please enter your contact number.';
        } else if (!/^\+639\d{9}$/.test(phone)) {
            setErrorState('cust_phone');
            errorMessage = 'Phone must be in format: +639XXXXXXXXX';
        } else if (!house) {
            setErrorState('house_street');
            errorMessage = 'Please enter your house number and street.';
        } else if (!province) {
            setErrorState('province');
            errorMessage = 'Please select a province.';
        } else if (!municipality) {
            setErrorState('municipality');
            errorMessage = 'Please select a city/municipality.';
        } else if (!barangay) {
            setErrorState('barangay');
            errorMessage = 'Please select a barangay.';
        } else if (!address) {
            setErrorState('cust_address');
            errorMessage = 'Please complete your delivery address.';
        }

        if (errorMessage) {
            showNotificationModal('error', errorMessage);
            return;
        }

        console.log('✅ Validation passed!');
        goToStep(2);
        renderCheckoutSummary();
        updatePaymentDisplay();
    }

    function setErrorState(inputId) {
        const input = document.getElementById(inputId);
        if (input) {
            input.classList.add('is-invalid');
            input.style.borderColor = '#dc3545';
            input.style.boxShadow = '0 0 0 0.2rem rgba(220, 53, 69, 0.25)';
        }
    }

    function clearErrorStates() {
        const inputs = ['cust_name', 'cust_email', 'cust_phone', 'cust_address', 'house_street', 'province', 'municipality', 'barangay'];
        inputs.forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.classList.remove('is-invalid');
                input.style.borderColor = '';
                input.style.boxShadow = '';
            }
        });
    }

// ============================================
// 5. INSTAPAY PAYMENT INTEGRATION
// ============================================

function calculateDeliveryFee() {
    const address = document.getElementById('cust_address')?.value.toLowerCase() || '';
    
    if (address.includes('manila') || address.includes('quezon') || address.includes('caloocan') || 
        address.includes('pasig') || address.includes('makati') || address.includes('taguig') || 
        address.includes('pasay') || address.includes('parañaque') || address.includes('muntinlupa') || 
        address.includes('las piñas') || address.includes('valenzuela') || address.includes('malabon') || 
        address.includes('navotas') || address.includes('marikina') || address.includes('san juan') || 
        address.includes('mandaluyong') || address.includes('pateros')) {
        return 2500;
    }
    
    if (address.includes('cavite')) return 4200;
    if (address.includes('laguna')) return 6000;
    if (address.includes('batangas')) return 8500;
    if (address.includes('rizal')) return 7000;
    if (address.includes('bulacan')) return 7000;
    if (address.includes('pampanga')) return 10000;
    if (address.includes('tarlac')) return 10000;
    
    if (address.includes('cebu') || address.includes('davao') || address.includes('iloilo') || 
        address.includes('bacolod') || address.includes('cagayan de oro') || address.includes('zamboanga')) {
        return 0;
    }
    
    return 2000;
}

function hasGridTieOrHybridProduct() {
    if (!cart || cart.length === 0) return false;
    
    return cart.some(item => {
        const name = (item.displayName || '').toLowerCase();
        const brand = (item.brandName || '').toLowerCase();
        return brand.includes('grid-tie') || brand.includes('hybrid') || 
               name.includes('grid-tie') || name.includes('hybrid');
    });
}

function updatePaymentDisplay() {
    console.log('💳 Updating payment display...');
    
    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked')?.value || 'full';
    
    const deliveryFee = calculateDeliveryFee();
    const deliveryFeeDisplay = document.getElementById('deliveryFeeDisplay');
    if (deliveryFeeDisplay) {
        if (deliveryFee === 0) {
            deliveryFeeDisplay.innerHTML = '<span class="text-info">Contact us</span>';
        } else {
            deliveryFeeDisplay.textContent = '₱' + deliveryFee.toLocaleString(undefined, {minimumFractionDigits: 2});
        }
    }
    
    const installationFee = hasGridTieOrHybridProduct() ? 2000 : 0;
    const installationFeeDisplay = document.getElementById('installationFeeDisplay');
    if (installationFeeDisplay) {
        if (installationFee === 0) {
            installationFeeDisplay.innerHTML = '<span class="text-success">FREE</span>';
        } else {
            installationFeeDisplay.textContent = '₱' + installationFee.toLocaleString(undefined, {minimumFractionDigits: 2});
        }
    }
    
    let cartTotal = 0;
    if (cart && cart.length > 0) {
        cartTotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    }
    
    const totalAmount = cartTotal + installationFee + deliveryFee;
    window.currentTotalAmount = totalAmount;
    
    const amountToPayDisplay = document.getElementById('amountToPay');
    const paymentNote = document.getElementById('paymentNote');
    const confirmBtn = document.getElementById('confirmPaymentBtn');
    
    if (paymentMethod === 'full') {
        amountToPayDisplay.textContent = '₱' + totalAmount.toLocaleString(undefined, {minimumFractionDigits: 2});
        paymentNote.innerHTML = '<i class="fas fa-info-circle"></i> You are paying the <strong>Full Amount (100%)</strong> via InstaPay.';
        paymentNote.className = 'alert alert-success';
    } else if (paymentMethod === 'downpayment') {
        const downpayment = totalAmount * 0.5;
        amountToPayDisplay.textContent = '₱' + downpayment.toLocaleString(undefined, {minimumFractionDigits: 2});
        paymentNote.innerHTML = '<i class="fas fa-info-circle"></i> You are paying <strong>50% Down Payment</strong> via InstaPay. Remaining 50% before delivery.';
        paymentNote.className = 'alert alert-warning';
    } else if (paymentMethod === 'initial') {
        const initialPayment = totalAmount * 0.2;
        const remaining = totalAmount - initialPayment;
        amountToPayDisplay.textContent = '₱' + initialPayment.toLocaleString(undefined, {minimumFractionDigits: 2});
        paymentNote.innerHTML = `
            <i class="fas fa-info-circle"></i> 
            You are paying <strong>20% Initial Payment</strong> (₱${initialPayment.toLocaleString(undefined, {minimumFractionDigits: 2})}) via InstaPay.<br>
            <small class="text-muted">Remaining balance: ₱${remaining.toLocaleString(undefined, {minimumFractionDigits: 2})} (80% - to be paid before installation)</small>
        `;
        paymentNote.className = 'alert alert-info';
    }
    
    const checkoutTotal = document.getElementById('checkoutTotal');
    if (checkoutTotal) {
        checkoutTotal.textContent = '₱' + totalAmount.toLocaleString(undefined, {minimumFractionDigits: 2});
    }
}

function getCartItems() {
    const items = [];
    if (cart && cart.length > 0) {
        cart.forEach(item => {
            items.push({
                name: item.displayName || 'Solar Product',
                price: parseFloat(item.price) || 0,
                quantity: parseInt(item.quantity) || 1
            });
        });
    }
    return items;
}

function previewReceipt(input) {
    const container = document.getElementById('receiptPreviewContainer');
    const previewImg = document.getElementById('receiptPreviewImg');
    const fileNameEl = document.getElementById('receiptFileName');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        fileNameEl.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
        
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewImg.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            previewImg.style.display = 'none';
        }
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
}

function confirmInstapayOrder() {
    console.log('💵 Confirming InstaPay order...');
    
    const custName = document.getElementById('cust_name')?.value.trim();
    const custEmail = document.getElementById('cust_email')?.value.trim();
    const custPhone = document.getElementById('cust_phone')?.value.trim();
    const custAddress = document.getElementById('cust_address')?.value.trim();
    const receiptFile = document.getElementById('receiptUpload')?.files[0];
    
    if (!custName || !custEmail || !custPhone || !custAddress) {
        showNotificationModal('error', 'Please complete all required customer details.');
        return;
    }
    
    if (cart.length === 0) {
        showNotificationModal('error', 'Your cart is empty.');
        return;
    }
    
    if (!receiptFile) {
        showNotificationModal('error', 'Please upload your transaction receipt before submitting.');
        document.getElementById('receiptUpload')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }
    
    if (receiptFile.size > 5 * 1024 * 1024) {
        showNotificationModal('error', 'Receipt file is too large. Maximum size is 5MB.');
        return;
    }
    
    const confirmBtn = document.getElementById('confirmPaymentBtn');
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting Order...';
    
    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked')?.value || 'full';
    
    const totalAmount = window.currentTotalAmount || 0;
    let amountPaid = totalAmount;
    if (paymentMethod === 'downpayment') amountPaid = totalAmount * 0.5;
    if (paymentMethod === 'initial') amountPaid = totalAmount * 0.2;
    
    const formData = new FormData();
    formData.append('customerName', custName);
    formData.append('customerEmail', custEmail);
    formData.append('customerPhone', custPhone);
    formData.append('customerAddress', custAddress);
    formData.append('paymentType', paymentMethod);
    formData.append('paymentMethod', 'instapay');
    formData.append('amountPaid', amountPaid);
    formData.append('totalAmount', totalAmount);
    formData.append('deliveryFee', calculateDeliveryFee());
    formData.append('installationFee', hasGridTieOrHybridProduct() ? 2000 : 0);
    formData.append('items', JSON.stringify(getCartItems()));
    formData.append('receipt', receiptFile);
    
    fetch('controllers/ordering/create-instapay-order.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        return response.text().then(text => {
            console.log('Raw server response:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error('Server returned invalid response: ' + text.substring(0, 300));
            }
        });
    })
    .then(data => {
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Confirm &amp; Submit Order';
        
        if (data.success) {
            console.log('InstaPay order saved:', data.orderRef);
            const orderRef = data.orderRef || 'ORD-INSTAPAY-' + Date.now();
            displayOrderConfirmation(orderRef);
            clearCart();
            showNotificationModal('success', 'Order submitted successfully! We will verify your payment soon.');
        } else {
            console.error('Order failed:', data.message);
            showNotificationModal('error', data.message || 'Failed to submit order. Please try again.');
        }
    })
    .catch(error => {
        console.error('InstaPay order error:', error.message);
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Confirm &amp; Submit Order';
        const userMsg = error.message.includes('Server returned') 
            ? 'Server error — please check browser console (F12) for details.' 
            : error.message;
        showNotificationModal('error', userMsg);
    });
}

function displayOrderConfirmation(orderRef) {
    console.log('🎉 Displaying order confirmation:', orderRef);
    
    document.getElementById('confOrderRef').textContent = orderRef;
    document.getElementById('confCustomerName').textContent = document.getElementById('cust_name').value;
    document.getElementById('confTotalAmount').textContent = '₱' + (window.currentTotalAmount || 0).toLocaleString(undefined, {minimumFractionDigits: 2});
    
    // Generate QR code
    const qrContainer = document.getElementById('orderQr');
    if (qrContainer && typeof QRCode !== 'undefined') {
        qrContainer.innerHTML = '';
        new QRCode(qrContainer, {
            text: orderRef,
            width: 150,
            height: 150,
            colorDark: '#000000',
            colorLight: '#ffffff',
        });
    }
    
    goToStep(3);
}

function copyOrderRef() {
    const orderRef = document.getElementById('confOrderRef')?.textContent;
    if (orderRef) {
        navigator.clipboard.writeText(orderRef).then(() => {
            showNotificationModal('success', '✅ Order reference copied to clipboard!');
        }).catch(() => {
            const el = document.createElement('textarea');
            el.value = orderRef;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
            showNotificationModal('success', '✅ Order reference copied!');
        });
    }
}

// ============================================
// 6. FILTERS & SORT
// ============================================
function initializeSubscription() {
    const subscribeForm = document.getElementById('subscribe-form');
    if (!subscribeForm) return;
    
    const subscribeBtn = document.getElementById('subscribe-btn');
    if (!subscribeBtn) return;
    const btnText = subscribeBtn.querySelector('.btn-text');
    const btnSpinner = subscribeBtn.querySelector('.btn-spinner');
    const emailInput = document.getElementById('subscribe-email');

    subscribeForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const email = emailInput.value.trim();
        if (!email) {
            emailInput.style.borderColor = '#dc3545';
            showNotificationModal('error', 'Please enter an email address.');
            return;
        }
        emailInput.style.borderColor = '';
        if (btnText) btnText.classList.add('d-none');
        if (btnSpinner) btnSpinner.classList.remove('d-none');
        subscribeBtn.disabled = true;

        fetch('controllers/subscribe.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ email: email })
        })
        .then(response => response.json())
        .then(data => {
            if (btnText) btnText.classList.remove('d-none');
            if (btnSpinner) btnSpinner.classList.add('d-none');
            subscribeBtn.disabled = false;
            if (data.status === 'success') {
                showNotificationModal('success', data.message);
                emailInput.value = '';
            } else {
                showNotificationModal('error', data.message);
            }
        })
        .catch(() => {
            if (btnText) btnText.classList.remove('d-none');
            if (btnSpinner) btnSpinner.classList.add('d-none');
            subscribeBtn.disabled = false;
            showNotificationModal('error', 'An error occurred. Please try again.');
        });
    });
}

function initializeFilters() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const category = this.getAttribute('data-category');
            filterProducts(category);
        });
    });
}

function filterProducts(category) {
    const products = document.querySelectorAll('.product-card');
    let visibleCount = 0;
    products.forEach(product => {
        const productCategory = product.getAttribute('data-category');
        if (category === 'all' || productCategory === category) {
            product.style.display = 'block';
            visibleCount++;
        } else {
            product.style.display = 'none';
        }
    });
    updateViewMoreButton(visibleCount);
    showNoProductsMessage(visibleCount);
}

function initializeSort() {
    const sortSelect = document.getElementById('sortSelect');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            sortProducts(this.value);
        });
    }
}

function sortProducts(sortType) {
    const grid = document.getElementById('productsGrid');
    const products = Array.from(document.querySelectorAll('.product-card'));
    products.sort((a, b) => {
        switch(sortType) {
            case 'price-low':
                return parseFloat(a.getAttribute('data-price')) - parseFloat(b.getAttribute('data-price'));
            case 'price-high':
                return parseFloat(b.getAttribute('data-price')) - parseFloat(a.getAttribute('data-price'));
            case 'name-asc':
                return a.getAttribute('data-name').localeCompare(b.getAttribute('data-name'));
            case 'name-desc':
                return b.getAttribute('data-name').localeCompare(a.getAttribute('data-name'));
            default:
                return 0;
        }
    });
    products.forEach(product => { grid.appendChild(product); });
}

function toggleViewMore() {
    const btn = document.getElementById('viewMoreBtn');
    const hiddenProducts = document.querySelectorAll('.product-card.hidden-product');
    const span = btn.querySelector('span');
    const icon = btn.querySelector('i');
    const allHidden = Array.from(hiddenProducts).every(p => p.style.display === 'none');
    hiddenProducts.forEach(product => {
        if (product.style.display !== 'none' || allHidden) {
            if (product.classList.contains('show-product')) {
                product.classList.remove('show-product');
                product.classList.add('hidden-product');
            } else {
                product.classList.remove('hidden-product');
                product.classList.add('show-product');
            }
        }
    });
    if (span.textContent === 'View More Products') {
        span.textContent = 'View Less Products';
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    } else {
        span.textContent = 'View More Products';
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    }
}

function updateViewMoreButton(visibleCount) {
    const viewMoreContainer = document.getElementById('viewMoreContainer');
    const viewMoreBtn = document.getElementById('viewMoreBtn');
    if (visibleCount <= 4) {
        if (viewMoreContainer) viewMoreContainer.style.display = 'none';
    } else {
        if (viewMoreContainer) viewMoreContainer.style.display = 'flex';
        const hiddenProducts = document.querySelectorAll('.product-card.hidden-product[style*="display: block"], .product-card.hidden-product:not([style*="display: none"])');
        if (hiddenProducts.length === 0 && viewMoreBtn) {
            viewMoreBtn.querySelector('span').textContent = 'View More Products';
            viewMoreBtn.querySelector('i').classList.remove('fa-chevron-up');
            viewMoreBtn.querySelector('i').classList.add('fa-chevron-down');
        }
    }
}

function showNoProductsMessage(visibleCount) {
    const grid = document.getElementById('productsGrid');
    let noProductsMsg = document.querySelector('.no-products-filter');
    if (visibleCount === 0) {
        if (!noProductsMsg) {
            noProductsMsg = document.createElement('div');
            noProductsMsg.className = 'no-products-filter col-12 text-center py-5';
            noProductsMsg.innerHTML = `
                <i class="fas fa-box-open" style="font-size: 48px; color: #ccc; margin-bottom: 16px;"></i>
                <p style="color: #666;">No products found in this category</p>
            `;
            grid.appendChild(noProductsMsg);
        }
        noProductsMsg.style.display = 'block';
    } else {
        if (noProductsMsg) noProductsMsg.style.display = 'none';
    }
}

</script>
</html>