<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
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
    pi.image_path
FROM product p
LEFT JOIN product_images pi 
    ON p.id = pi.product_id
WHERE pi.image_path IS NOT NULL
GROUP BY p.id
ORDER BY 
    CASE 
        WHEN TRIM(p.brandName) = 'Grid-tie' THEN 0 
        WHEN TRIM(p.brandName) = 'LuxPower' THEN 1 
        ELSE 2 
    END ASC, 
    p.id DESC";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        // Add ALL products to the same array
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
    <title>SolarPower Energy - Smart Energy for Smarter Homes</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <link rel="stylesheet" href="assets/style.css">
<body> 

    <?php include "includes/header.php" ?>

    <section class="hero" id="home">
        <div class="hero-overlay"></div>

        <div class="container hero-content">
            <div class="row align-items-center">
    <!-- LEFT: HERO TEXT -->
    <div class="col-lg-7">
        <h1>Smart Energy for<br>Smarter Homes</h1>

        <p class="hero-tagline">
            One Stop Shop for Solar Power Mega Company
        </p>

        <p>
            Invest in solar today - enjoy decades of <br>
            energy independence and savings.
        </p>

        <div class="hero-cta">
            <button class="btn btn-primary">Learn More</button>
            <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#inspectionModal">
                Book for Inspection
            </button>
        </div>
    </div>

    <!-- RIGHT: PROMO BANNER -->
                <div class="col-lg-5 mt-4 mt-lg-0">
                    <div class="promo-banner">
                        <!-- Background layers -->
                        <div class="card-layer card-back"></div>
                        <div class="card-layer card-front"></div>
                        
                        <!-- Stars -->
                        <div class="star star-1">✦</div>
                        <div class="star star-2">✦</div>
                        <div class="star star-3">✦</div>
                        
                        <!-- Content -->
                        <div class="promo-content">
                            <span class="promo-badge">New Year Sale</span>
                            
                            <div class="promo-offer">
                                <h3>Get <span>10% OFF</span></h3>
                            </div>
                            
                            <p class="promo-footer">The authority to sell</p>
                        </div>
                        
                        <!-- Ribbon -->
                        <div class="ribbon">
                            <h4>Install Now, Pay Later</h4>
                        </div>
                        
                        <!-- Order Button -->
                        <button class="order-btn" onclick="document.getElementById('catalogSection').scrollIntoView({behavior: 'smooth'})">
                            Order Now
                        </button>
                    </div>
                </div>
            </div>
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
                <div class="brand-item"><img src="assets/img/hyxipower.png" alt="Jinko"></div>
                <div class="brand-item"><img src="assets/img/Hopewind.jpg" alt="Hopewind"></div>
                <!-- Duplicate for seamless loop -->
                <div class="brand-item"><img src="assets/img/hoymiles.png" alt="Hoymiles"></div>
                <div class="brand-item"><img src="assets/img/solax.png" alt="Solax"></div>
                <div class="brand-item"><img src="assets/img/aiko.png" alt="Aiko"></div>
                <div class="brand-item"><img src="assets/img/iansolar.png" alt="AN Solar"></div>
                <div class="brand-item"><img src="assets/img/lvtopsun.png" alt="LA Topsun"></div>
                <div class="brand-item"><img src="assets/img/aesolar.png" alt="AE Solar"></div>
                <div class="brand-item"><img src="assets/img/jinko.png" alt="Jinko"></div>
                <div class="brand-item"><img src="assets/img/hyxipower.png" alt="Hyxipower"></div>
                <div class="brand-item"><img src="assets/img/Hopewind.jpg" alt="Hopewind"></div>
            </div>
        </div>
        
        <!-- Bottom Row - Scrolling RIGHT -->
        <div class="carousel-wrapper">
            <div class="brands-scroll-list scroll-right">
                <div class="brand-item"><img src="assets/img/hoymiles.png" alt="Hoymiles"></div>
                <div class="brand-item"><img src="assets/img/solax.png" alt="Solax"></div>
                <div class="brand-item"><img src="assets/img/aiko.png" alt="Aiko"></div>
                <div class="brand-item"><img src="assets/img/iansolar.png" alt="AN Solar"></div>
                <div class="brand-item"><img src="assets/img/lvtopsun.png" alt="LA Topsun"></div>
                <div class="brand-item"><img src="assets/img/aesolar.png" alt="AE Solar"></div>
                <div class="brand-item"><img src="assets/img/jinko.png" alt="Jinko"></div>
                <div class="brand-item"><img src="assets/img/hyxipower.png" alt="Jinko"></div>
                <div class="brand-item"><img src="assets/img/Hopewind.jpg" alt="Hopewind"></div>
                <!-- Duplicate for seamless loop -->
                <div class="brand-item"><img src="assets/img/hoymiles.png" alt="Hoymiles"></div>
                <div class="brand-item"><img src="assets/img/solax.png" alt="Solax"></div>
                <div class="brand-item"><img src="assets/img/aiko.png" alt="Aiko"></div>
                <div class="brand-item"><img src="assets/img/iansolar.png" alt="AN Solar"></div>
                <div class="brand-item"><img src="assets/img/lvtopsun.png" alt="LA Topsun"></div>
                <div class="brand-item"><img src="assets/img/aesolar.png" alt="AE Solar"></div>
                <div class="brand-item"><img src="assets/img/jinko.png" alt="Jinko"></div>
                <div class="brand-item"><img src="assets/img/hyxipower.png" alt="Hyxipower"></div>
                <div class="brand-item"><img src="assets/img/Hopewind.jpg" alt="Hopewind"></div>
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

            <!-- Filter Bar -->
            <div class="filter-bar">
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
                        <i class="fas fa-tools"></i> Package Setup
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
                        <!-- Replace the product card section in index.php with this -->
                        <div class="product-card <?= $index >= 4 ? 'hidden-product' : '' ?>" 
                             data-category="<?= htmlspecialchars($p['category']) ?>"
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
                        <label class="form-label fw-bold">Province</label>
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

                </div>
                    

                <div class="checkout-actions">
                    <button class="btn-outline" onclick="backToCatalog()">Continue Shopping</button>
                    <button class="btn-primary" onclick="validateStep1()">Proceed to Payment</button>
                </div>
            </div>

<div id="checkoutStep2" class="checkout-card" style="display:none;">
    <h3>Order Summary & Payment</h3>

    <!-- Payment Method Selection -->
    <div class="card mb-4">
        <div class="card-header text-white" style="background: #004b8d;">
            <h5 class="mb-0">
                <i class="fas fa-university me-2"></i>UnionBank Direct Deposit
            </h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-4">
                <i class="fas fa-shield-alt me-2"></i>
                <strong>Secure Payment Required:</strong> All orders require upfront payment via UnionBank to prevent fake bookings. Your payment is secure and protected.
            </div>

            <!-- UnionBank QR Code -->
            <div class="text-center mb-4 p-4 bg-light rounded">
                <h6 class="fw-bold mb-3">Scan to Pay via InstaPay</h6>
                <div class="qr-container mb-3" style="display: inline-block; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <img src="assets/img/UB-QR Code.jpg" alt="UnionBank QR Code" style="width: 280px; height: 280px;">
                </div>
                <div class="bank-details p-3 bg-white rounded">
                    <p class="mb-2"><strong>Account Name:</strong> SOLARPOWER ENERGY CORPORATION</p>
                    <p class="mb-2"><strong>Account Number:</strong> **** **** 7200</p>
                    <p class="mb-0"><strong>Bank:</strong> UnionBank of the Philippines</p>
                    <p class="mb-0 text-muted small">CORPORATE REGULAR CHECKING ACCOUNT</p>
                </div>
            </div>

            <!-- Payment Options -->
            <div class="payment-options">
                
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
                                <p class="text-muted mb-0 small">Pay complete amount via UnionBank now</p>
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
                                <p class="text-muted mb-0 small">Pay 50% now via UnionBank, 50% before delivery</p>
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
                                <p class="text-muted mb-0 small">Pay 20% now via UnionBank, 80% before installation</p>
                            </div>
                            <span class="badge bg-info">Flexible</span>
                        </div>
                    </label>
                </div>

            </div>

            <!-- Important Payment Instructions -->
            <div class="alert alert-warning border mt-3">
                <div class="d-flex align-items-start">
                    <i class="fas fa-exclamation-triangle me-3" style="font-size: 1.5rem;"></i>
                    <div>
                        <strong>Important Payment Instructions:</strong>
                        <ul class="mb-0 mt-2 small">
                            <li>Scan the QR code using your bank app or InstaPay</li>
                            <li><strong>Make sure your payment amount matches the "Amount to Pay Now" below</strong></li>
                            <li>Upload your transaction receipt/screenshot after payment</li>
                            <li>Keep your reference number for tracking</li>
                        </ul>
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
            <span class="text-success">FREE</span>
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
        <i class="fas fa-info-circle"></i> You are paying the <strong>Full Amount (100%)</strong> via UnionBank.
    </div>

    <!-- Transaction Receipt Upload -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                <i class="fas fa-upload me-2"></i>Upload Transaction Receipt
            </h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-3">
                <i class="fas fa-info-circle me-2"></i>
                After making your payment via UnionBank, please upload a screenshot or photo of your transaction receipt.
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold">Transaction Receipt *</label>
                <input type="file" class="form-control" id="transactionReceipt" accept="image/*" required>
                <small class="text-muted">Accepted formats: JPG, PNG, PDF (Max 5MB)</small>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold">Reference Number (Optional)</label>
                <input type="text" class="form-control" id="referenceNumber" placeholder="Enter transaction reference number">
            </div>

            <!-- Receipt Preview -->
            <div id="receiptPreview" class="mt-3" style="display: none;">
                <label class="form-label fw-bold">Receipt Preview:</label>
                <div class="border rounded p-2 text-center">
                    <img id="receiptImage" src="" alt="Receipt Preview" style="max-width: 100%; max-height: 300px;">
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="checkout-actions mt-4">
        <button class="btn-outline" onclick="goToStep(1)">
            <i class="fas fa-arrow-left me-2"></i>Edit Details
        </button>
        <button id="confirmPaymentBtn" class="btn-primary" onclick="submitUnionBankPayment()">
            <i class="fas fa-check-circle me-2"></i>Confirm Payment & Submit Order
        </button>
    </div>
</div>

            <div id="checkoutStep3" class="checkout-card" style="display:none;">
                <div class="text-center py-5">

                    <i class="fas fa-check-circle text-success mb-3" style="font-size:64px;"></i>
                    <h3>Order Submitted</h3>
                    <p class="text-muted">Thank you! Your payment was successful.</p>

                <!-- Order Reference -->
                <p class="mt-3">
                    <strong>Order Reference:</strong><br>
                    <span id="orderRef" class="fw-bold fs-5"></span>
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

    <!-- Savings Calculator -->
<section class="savings-calculator">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <div class="calculator-box collapsed" id="calculatorBox">
                    <div class="savings-icon">
                        <i class="fa-regular fa-lightbulb"></i>
                    </div>

                    <h2>Let's check how much you can save!</h2>
                    <p>What's your monthly electric bill?</p>

                    <div class="row justify-content-center mb-4">
                        <div class="col-lg-4 col-md-6">
                            <div class="input-group-custom">
                                <span class="peso"></span>
                                <input 
                                        type="number"
                                        id="billAmount"
                                        placeholder="0"
                                        min="0"
                                        step="0.01"
                                        onfocus="expandCalculator()"
                                        onblur="shrinkCalculatorIfEmpty()"
                                    >
                                <p>Monthly Electric Bill</p>
                            </div>
                        </div>
                    </div>

                    <button class="calculate-btn" onclick="calculateSavings()">Calculate</button>

                    <div id="errorMessage" class="error-message"></div>

                    <div id="results" class="results">
                        <div class="result-card">
                            <div class="result-value" id="kwpValue">0.0</div>
                            <div class="result-label">Required System Size (kWp)</div>
                        </div>
                        <div class="result-card">
                            <div class="result-value" id="panelsValue">0</div>
                            <div class="result-label">Solar Panels</div>
                        </div>
                        <div class="result-card">
                            <div class="result-value" id="monthlySavings">0</div>
                            <div class="result-label">Monthly Savings (₱)</div>
                        </div>
                        <div class="result-card">
                            <div class="result-value" id="yearlySavings">0</div>
                            <div class="result-label">Yearly Savings (₱)</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>



    <!-- Services Section -->
    <section class="services-section">
        <div class="container">
            <h2>Our Services</h2>
        </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <h3>Residential, Commercial & Industrial Solar Installation</h3>
                        <p>Expert guidance to help you understand solar energy benefits and determine the best system for your property and energy needs. DOE-accredited installation services performed by certified technicians ensuring quality, safety, and optimal system performance.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fa-solid fa-solar-panel"></i>                        
                        </div>
                        <h3>Grid-Tie and Hybrid Systems</h3>
                        <p><p>Our grid-tie and hybrid solar systems are designed for efficiency, reliability, and long-term savings. Whether reducing energy costs through grid connection or ensuring uninterrupted power with battery storage, we deliver smart solar solutions tailored to your needs.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-pencil-ruler"></i>
                        </div>
                        <h3>Solar Panels Maintenance and Upgrades</h3>
                        <p>Professional maintenance and system upgrades to ensure optimal performance, improved efficiency, and extended lifespan of your solar panels.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <h3>Energy Audit & System Design</h3>
                        <p>Comprehensive energy audits and customized system designs to identify your power needs and maximize solar efficiency. We analyze usage patterns and site conditions to create cost-effective, reliable solar solutions.</p>
                    </div>
                </div>
            </div>
    </section>

           <!-- Solar System Tips Section -->
<section class="solar-tips-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2>Solar System Tips</h2>
            <p class="section-subtitle">Essential insights to maximize your solar investment</p>
        </div>

        <!-- Video Grid -->
        <div class="row g-4 mb-5 justify-content-center">
            <div class="col-lg-6 col-md-10">
                <div class="video-card">
                    <div class="video-wrapper">
                        <div class="fb-video-responsive">
                            <iframe 
                                src="https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2Freel%2F1556081359036132%2F&show_text=false" 
                                allowfullscreen="true" 
                                allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-10">
                <div class="video-card">
                    <div class="video-wrapper">
                        <div class="fb-video-responsive">
                            <iframe 
                                src="https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2Freel%2F2111021589731457%2F&show_text=false" 
                                allowfullscreen="true" 
                                allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Cards -->
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="tip-info-card">
                    <div class="tip-icon">
                        <i class="fas fa-battery-three-quarters"></i>
                    </div>
                    <h5>Energy Storage Optimization</h5>
                    <p>Maximize your battery lifespan by keeping charge levels between 20-80%. Invest in quality lithium-ion batteries for better efficiency and longer warranties.</p>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="tip-info-card">
                    <div class="tip-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h5>Monitor Your System</h5>
                    <p>Use monitoring apps to track solar production in real-time. Understanding your energy patterns helps optimize usage and maximize savings.</p>
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- 6 Reasons Section -->
    <section class="solar-reasons-section">
        <div class="solar-reasons-container">    
            <!-- LEFT SIDE - ILLUSTRATION -->
            <div class="reasons-illustration">
                <h2 class="reasons-title">
                      6 Reasons Why<br>
                      <span class="light-text">Your Home Must Be Powered by</span><br>
                      <span class="brand-text">SolarPower Energy Corporation</span>
                    </h2>

                    <p class="reasons-subtitle">
                      Smart, sustainable, and cost-efficient energy solutions built for Filipino homes.
                    </p>
                <div class="illustration-wrapper">
                <!-- Sun with Rays -->
                <div class="sun">
                  <div class="sun-ray"></div>
                  <div class="sun-ray"></div>
                  <div class="sun-ray"></div>
                  <div class="sun-ray"></div>
                  <div class="sun-ray"></div>
                  <div class="sun-ray"></div>
                  <div class="sun-ray"></div>
                  <div class="sun-ray"></div>
                </div>

                <!-- Ground -->
                <div class="ground"></div>
            
                <!-- House Container -->
                <div class="house-container">
                  <!-- Roof -->
                  <div class="roof"></div>
                  
                  <!-- Solar Panel -->
                  <div class="solar-panel">
                    <div class="solar-cell"></div>
                    <div class="solar-cell"></div>
                    <div class="solar-cell"></div>
                    <div class="solar-cell"></div>
                    <div class="solar-cell"></div>
                    <div class="solar-cell"></div>
                    <div class="solar-cell"></div>
                    <div class="solar-cell"></div>
                  </div>
            
                  <!-- Wiring System -->
                  <div class="wiring">
                    <div class="wire wire-vertical"></div>
                    <div class="wire wire-horizontal"></div>
                    <div class="wire wire-to-left-window"></div>
                    <div class="wire wire-to-right-window"></div>
                  </div>
            
                  <!-- Junction Box -->
                  <div class="junction-box"></div>
            
                  <!-- Energy Particles flowing through wires -->
                  <div class="energy-particle particle-1"></div>
                  <div class="energy-particle particle-2"></div>
                  <div class="energy-particle particle-3"></div>
            
                  <!-- House Body -->
                  <div class="house-body">
                    <div class="window window-left"></div>
                    <div class="window window-right"></div>
                    <div class="door">
                      <div class="door-knob"></div>
                    </div>
                  </div>
                </div>

                <!-- Pine Trees -->
                <div class="tree tree-left">
                  <div class="pine-layer pine-layer-4"></div>
                  <div class="pine-layer pine-layer-3"></div>
                  <div class="pine-layer pine-layer-2"></div>
                  <div class="pine-layer pine-layer-1"></div>
                  <div class="tree-trunk"></div>
                </div>

                  <div class="tree tree-right">
                    <div class="pine-layer pine-layer-4"></div>
                    <div class="pine-layer pine-layer-3"></div>
                    <div class="pine-layer pine-layer-2"></div>
                    <div class="pine-layer pine-layer-1"></div>
                    <div class="tree-trunk"></div>
                  </div>
                </div>
            </div>

            <!-- RIGHT SIDE - ACCORDION -->
            <div class="reasons-accordion">
                    
                <!-- Accordion Item 1 -->
                <div class="accordion-item">
                    <div class="accordion-header">
                        <div class="accordion-icon-wrapper">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </div>
                        <h3 class="accordion-title">Protection Against Rising Electricity Costs</h3>
                        <div class="accordion-toggle">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="accordion-content">
                        <div class="accordion-content-inner">
                            <p>Lock in your energy costs and shield yourself from unpredictable utility rate increases. Solar provides stable, predictable energy expenses for decades.</p>
                            <span class="reason-tag">Financial Security</span>
                        </div>
                    </div>
                </div>

                <!-- Accordion Item 2 -->
                <div class="accordion-item">
                    <div class="accordion-header">
                        <div class="accordion-icon-wrapper">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                            </svg>
                        </div>
                        <h3 class="accordion-title">Energy Independence</h3>
                        <div class="accordion-toggle">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="accordion-content">
                        <div class="accordion-content-inner">
                            <p>Generate your own clean electricity and reduce reliance on the grid. Take control of your power supply and enjoy freedom from utility companies.</p>
                            <span class="reason-tag">Self-Sufficiency</span>
                        </div>
                    </div>
                </div>

                <!-- Accordion Item 3 -->
                <div class="accordion-item">
                    <div class="accordion-header">
                        <div class="accordion-icon-wrapper">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 12h18M3 6h18M3 18h18"/>
                                <circle cx="12" cy="12" r="10"/>
                            </svg>
                        </div>
                        <h3 class="accordion-title">Environment Friendly</h3>
                        <div class="accordion-toggle">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="accordion-content">
                        <div class="accordion-content-inner">
                            <p>Reduce your carbon footprint and contribute to a cleaner planet. Solar energy produces zero emissions, helping combat climate change for future generations.</p>
                            <span class="reason-tag">Green Living</span>
                        </div>
                    </div>
                </div>

                <!-- Accordion Item 4 -->
                <div class="accordion-item">
                    <div class="accordion-header">
                        <div class="accordion-icon-wrapper">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                            </svg>
                        </div>
                        <h3 class="accordion-title">Low Maintenance</h3>
                        <div class="accordion-toggle">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="accordion-content">
                        <div class="accordion-content-inner">
                            <p>Solar panels require minimal upkeep with no moving parts. Simple occasional cleaning and standard warranties ensure worry-free operation for 25+ years.</p>
                            <span class="reason-tag">Hassle-free</span>
                        </div>
                    </div>
                </div>

                <!-- Accordion Item 5 -->
                <div class="accordion-item">
                    <div class="accordion-header">
                        <div class="accordion-icon-wrapper">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                            </svg>
                        </div>
                        <h3 class="accordion-title">Government Incentives & Rebates</h3>
                        <div class="accordion-toggle">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="accordion-content">
                        <div class="accordion-content-inner">
                            <p>Take advantage of tax credits, rebates, and incentive programs that significantly reduce installation costs. Save thousands with available financial support.</p>
                            <span class="reason-tag">Save More</span>
                        </div>
                    </div>
                </div>

                <!-- Accordion Item 6 -->
                <div class="accordion-item">
                    <div class="accordion-header">
                        <div class="accordion-icon-wrapper">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                            </svg>
                        </div>
                        <h3 class="accordion-title">Reliable Long-Term Investment</h3>
                        <div class="accordion-toggle">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="accordion-content">
                        <div class="accordion-content-inner">
                            <p>Increase your property value while enjoying immediate savings. Solar systems pay for themselves through energy savings and boost home resale value.</p>
                            <span class="reason-tag">Smart Investment</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
    
    <!-- Contact Us Section -->
<section class="contact-us" id="contact-us">
    <div class="container">
        <div class="row">
            <!-- Left Side - Contact Information -->
            <div class="col-lg-5 mb-4 mb-lg-0">
                <div class="contact-info">
                    <h2>Contact Us</h2>
                    
                    <!-- Visit Us Section -->
                    <div class="visit-us-section">
                        <h3>Visit Us</h3>
                        <p>Come visit our showroom to see our solar products and speak with our experts in person.</p>
                        <a href="https://wa.me/639953947379" class="whatsapp-btn" target="_blank">
                            <i class="fab fa-whatsapp"></i>
                            Chat on WhatsApp
                        </a>
                    </div>
                    
                    <!-- Company Information -->
                    <div class="company-info">
                        <div class="contact-detail">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <strong>Address</strong>
                                <p>4/F PBB Corporate Center, 1906 Finance Drive, Madrigal Business Park 1, Ayala Alabang, Muntinlupa City, 1780, Philippines</p>
                            </div>
                        </div>
                        
                        <div class="contact-detail">
                            <i class="fas fa-phone"></i>
                            <div>
                                <strong>Phone</strong>
                                <span class="phone-number" id="phone-copy" onclick="copyToClipboard('0995-394-7379', this)">0995-394-7379</span>
                            </div>
                        </div>
                        
                        <div class="contact-detail">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <strong>Email</strong>
                                <a href="mailto:solar@solarpower.com.ph" class="contact-link">solar@solarpower.com.ph</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Business Hours -->
                    <div class="hours-section">
                        <button class="hours-toggle" onclick="toggleHours()">
                            <strong>Business Hours</strong>
                            <i class="fas fa-chevron-down" id="hours-icon"></i>
                        </button>
                        <div class="hours-content" id="hours-content">
                            <div class="hour-item">
                                <span>Monday</span>
                                <span>8:00 AM - 6:00 PM</span>
                            </div>
                            <div class="hour-item">
                                <span>Tuesday</span>
                                <span>8:00 AM - 6:00 PM</span>
                            </div>
                            <div class="hour-item">
                                <span>Wednesday</span>
                                <span>8:00 AM - 6:00 PM</span>
                            </div>
                            <div class="hour-item">
                                <span>Thursday</span>
                                <span>8:00 AM - 6:00 PM</span>
                            </div>
                            <div class="hour-item">
                                <span>Friday</span>
                                <span>8:00 AM - 6:00 PM</span>
                            </div>
                            <div class="hour-item">
                                <span>Saturday</span>
                                <span>9:00 AM - 5:00 PM</span>
                            </div>
                            <div class="hour-item">
                                <span>Sunday</span>
                                <span>Closed</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Contact Form -->
            <div class="col-lg-7">
                <div class="contact-form-wrapper">
                    <h3 class="mb-4">Send us a Message</h3>
                    <form class="contact-form" id="contactForm" onsubmit="submitContactForm(event)">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <input type="text" class="form-control" id="contact_name" name="name" placeholder="Full Name *" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <input type="email" class="form-control" id="contact_email" name="email" placeholder="Email Address *" required>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <input type="tel" class="form-control" id="contact_phone" name="phone" placeholder="Phone Number *" required>
                            </div>
                            
                            <div class="col-12 mb-4">
                                <textarea class="form-control" id="contact_message" name="message" rows="6" placeholder="Your Message *" required></textarea>
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" class="btn-submit" id="contactSubmitBtn">
                                    <span class="btn-text">Send Message</span>
                                    <span class="btn-spinner d-none">
                                        <i class="fas fa-spinner fa-spin"></i> Sending...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

    <section class="subscription-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="subscription-bar">
                        <h3>Subscribe Now!</h3>
                        <p style="color: rgba(255,255,255,0.9); margin-bottom: 20px;">Get weekly solar tips, updates, and exclusive offers delivered to your inbox</p>
                        <form id="subscribe-form" class="d-flex">
                            <input type="email" 
                                   name="email" 
                                   id="subscribe-email"
                                   class="form-control" 
                                   placeholder="Enter your email address" 
                                   required>
                            <button type="submit" class="btn btn-subscribe" id="subscribe-btn">
                                <span class="btn-text">Subscribe!</span>
                                <span class="btn-spinner d-none">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Success Modal -->
    <div class="modal fade" id="contactSuccessModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Message Sent
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-solar-panel text-success"
                           style="font-size: 48px;"></i>
                    </div>
                    <p class="mb-1">
                        Thank you for sending contacts
                    </p>
                    <strong>Enjoy browsing our website!</strong>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include "includes/footer.php" ?>


    <!-- Floating Messenger Button -->
        <a href="https://m.me/757917280729034"
           class="messenger-float"
           target="_blank"
           aria-label="Chat with us on Messenger">
            <i class="fab fa-facebook-messenger"></i>
        </a>

        <!------ Book For Inspection ------>
        <div class="modal fade" id="inspectionModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 rounded-4 overflow-hidden">
                        
                    <!-- Close Button -->
                    <button type="button" class="btn-close position-absolute end-0 m-3" data-bs-dismiss="modal"></button>
                        
                    <div class="row g-0">
                        
                        <!-- LEFT INFO PANEL -->
                        <div class="col-lg-5 d-none d-lg-block"
                            style="background: linear-gradient(rgba(44,62,80,.9), rgba(44,62,80,.9)),
                            url('assets/img/solar-install.jpg') center/cover;">
                            
                            <div class="h-100 p-5 text-white d-flex flex-column justify-content-center">
                                <h2 class="fw-bold mb-4" style="color:#f39c12;">Ready to switch?</h2>
                                <p class="mb-4">Book a site inspection and let our experts design your perfect solar system.</p>
                                <ul class="list-unstyled">
                                    <li class="mb-3"><i class="fas fa-check-circle text-warning me-2"></i> Professional Assessment</li>
                                    <li class="mb-3"><i class="fas fa-check-circle text-warning me-2"></i> Accurate ROI Projection</li>
                                    <li class="mb-3"><i class="fas fa-check-circle text-warning me-2"></i> Custom System Design</li>
                                </ul>
                            </div>
                        </div>
                        
                        <!-- FORM PANEL -->
                        <div class="col-lg-7 bg-white p-4 p-md-5">
                            <div class="mb-4">
                                <h2 class="fw-bold">Book Site Inspection</h2>
                                <p class="text-muted small">Weâ€™ll contact you within 24 hours.</p>
                            </div>

                        <form id="inspectionForm" class="inspection-form" onsubmit="submitInspection(event)">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small text-uppercase">Full Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                                        <input type="text" name="fullname" class="form-control bg-light border-start-0" placeholder="Juan Dela Cruz" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small text-uppercase">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                                        <input type="email" name="email" class="form-control bg-light border-start-0" placeholder="juan@email.com" required>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small text-uppercase">Contact Number</label>
                                    <input type="tel" name="phone" class="form-control bg-light" placeholder="0917-000-0000" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small text-uppercase">Property Type</label>
                                    <select name="property_type" class="form-select bg-light" required>
                                        <option value="" selected disabled>Select type</option>
                                        <option value="Residential">Residential</option>
                                        <option value="Commercial">Commercial</option>
                                    </select>
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label fw-semibold small text-uppercase">Complete Address</label>
                                    <textarea name="address" class="form-control bg-light" rows="2" placeholder="House No., Street, Brgy, City" required></textarea>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small text-uppercase">Inspection Date</label>
                                    <input type="date" name="inspection_date" class="form-control bg-light" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small text-uppercase">Monthly Bill (₱)</label>
                                    <input type="number" name="bill" class="form-control bg-light" placeholder="e.g. 5000" required>
                                </div>

                                <div class="col-12 mb-4">
                                    <label class="form-label fw-semibold small text-uppercase">Additional Notes (Optional)</label>
                                    <textarea name="notes" class="form-control bg-light" rows="3" placeholder="Tell us about your roof type or any specific concerns..."></textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn w-100 py-3 fw-bold text-uppercase shadow-sm" id="inspectionBtn" style="background: #f39c12; color: white; border: none;">
                                <span class="btn-text">Confirm My Schedule</span>
                                <span class="spinner-border spinner-border-sm d-none"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>




    <!-- Bootstrap JS Bundle -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>


</body>
<script src="assets/script.js"></script>
<script>
// 1. GLOBAL VARIABLES
// ============================================
let cart = [];

// ============================================
// 2. INITIALIZATION
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Initializing Solar Power System...');
    
    loadCartFromStorage();
    updateCartBadge();
    initializeFilters();
    initializeSort();
    initializeSubscription();
    setupCalculator();
    setupPhoneInput();
    
    console.log('✅ System initialized');
});

// ============================================
// 3. CART MANAGEMENT
// ============================================
function loadCartFromStorage() {
    try {
        if (window.cartStorage) {
            cart = JSON.parse(window.cartStorage);
            console.log('✅ Cart loaded:', cart.length, 'items');
        }
    } catch (error) {
        console.error('❌ Error loading cart:', error);
        cart = [];
    }
}

function saveCartToStorage() {
    try {
        window.cartStorage = JSON.stringify(cart);
        updateCartBadge();
    } catch (error) {
        console.error('❌ Error saving cart:', error);
    }
}

function addToCartFromButton(btn) {
    const product = JSON.parse(btn.getAttribute('data-product'));
    addToCartLogic(product);
    showCartPopup();
    showNotificationModal('success', '✅ Product added to cart!');
}

function addToCartLogic(product) {
    const existingItem = cart.find(item => item.id === product.id);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            id: product.id,
            displayName: product.displayName,
            price: parseFloat(product.price),
            image_path: product.image_path,
            quantity: 1
        });
    }
    
    saveCartToStorage();
}

function updateCartQuantity(productId, change) {
    const item = cart.find(i => i.id === productId);
    if (item) {
        item.quantity += change;
        
        if (item.quantity < 1) {
            item.quantity = 1;
            return;
        }
        
        saveCartToStorage();
        renderCartPopup();
    }
}

function removeFromCartPopup(productId) {
    if (confirm('Remove this item from cart?')) {
        cart = cart.filter(i => i.id !== productId);
        saveCartToStorage();
        renderCartPopup();
        showNotificationModal('success', 'Item removed from cart');
    }
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

function clearCart() {
    cart = [];
    saveCartToStorage();
}

function getCartItems() {
    return cart.map(item => ({
        name: item.displayName,
        price: item.price,
        quantity: item.quantity
    }));
}

// ============================================
// 4. CART POPUP MODAL
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
                            <i class="fas fa-shopping-cart me-2"></i>Shopping Cart
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="cartModalBody"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Continue Shopping
                        </button>
                        <button type="button" class="btn btn-primary" onclick="proceedToCheckout()" id="proceedCheckoutBtn">
                            <i class="fas fa-arrow-right me-2"></i>Proceed to Checkout
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
                <button class="btn btn-primary" data-bs-dismiss="modal">Start Shopping</button>
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
        const minusDisabled = item.quantity === 1 ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '';
        
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
                <span>Installation Fee:</span>
                <span class="text-success fw-bold">FREE</span>
            </div>
            <hr>
            <div class="d-flex justify-content-between" style="font-size: 1.2rem;">
                <span class="fw-bold">Total:</span>
                <span class="fw-bold text-primary">₱${subtotal.toLocaleString(undefined, {minimumFractionDigits: 2})}</span>
            </div>
        </div>
    `;
    
    modalBody.innerHTML = html;
    window.currentTotalAmount = subtotal;
}

function buyNowFromButton(btn) {
    const product = JSON.parse(btn.getAttribute('data-product'));
    cart = [];
    addToCartLogic(product);
    proceedToCheckout();
}

// ============================================
// 5. CHECKOUT NAVIGATION
// ============================================
function proceedToCheckout() {
    if (cart.length === 0) {
        showNotificationModal('error', 'Your cart is empty');
        return;
    }
    
    const cartModal = bootstrap.Modal.getInstance(document.getElementById('cartModal'));
    if (cartModal) cartModal.hide();
    
    showCheckout();
    renderCheckoutSummary();
}

function showCheckout() {
    const sectionsToHide = [
        '.hero', '.featured-brands', '.savings-calculator', 
        '.services-section', '.solar-tips-section', '.solar-reasons-section',
        '#catalogSection', '.contact-us', '.subscription-section', 'footer'
    ];
    
    sectionsToHide.forEach(selector => {
        const el = document.querySelector(selector);
        if(el) el.style.display = 'none';
    });

    document.getElementById('checkoutSection').style.display = 'block';
    window.scrollTo(0, 0);
    goToStep(1);
}

function backToCatalog() {
    document.getElementById('checkoutSection').style.display = 'none';
    
    const sectionsToShow = [
        '.hero', '.featured-brands', '.savings-calculator', 
        '.services-section', '.solar-tips-section', '.solar-reasons-section',
        '#catalogSection', '.contact-us', '.subscription-section', 'footer'
    ];
    
    sectionsToShow.forEach(selector => {
        const el = document.querySelector(selector);
        if(el) el.style.display = 'block';
    });
    
    window.scrollTo(0, document.getElementById('catalogSection').offsetTop - 100);
}

function goToStep(step) {
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
    if (checkoutSteps) checkoutSteps.setAttribute('data-step', step);
    
    window.scrollTo(0, 0);
}

// ============================================
// 6. CHECKOUT SUMMARY
// ============================================
function renderCheckoutSummary() {
    const summaryDiv = document.getElementById('checkoutOrderSummary');
    const subtotalDisplay = document.getElementById('checkoutSubtotal');
    const totalDisplay = document.getElementById('checkoutTotal');
    
    if (cart.length === 0) {
        summaryDiv.innerHTML = '<p class="text-center text-muted">Your cart is empty.</p>';
        if (subtotalDisplay) subtotalDisplay.innerText = "₱0.00";
        if (totalDisplay) totalDisplay.innerText = "₱0.00";
        return;
    }

    let grandTotal = 0;
    let html = '';

    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        grandTotal += itemTotal;
        const minusDisabled = item.quantity === 1 ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '';
        
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
    
    const formattedTotal = "₱" + grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2});
    if (subtotalDisplay) subtotalDisplay.innerText = formattedTotal;
    if (totalDisplay) totalDisplay.innerText = formattedTotal;
    
    window.currentTotalAmount = grandTotal;
}

function updateCheckoutQuantity(productId, change) {
    updateCartQuantity(productId, change);
    renderCheckoutSummary();
}

function removeFromCheckout(productId) {
    if (confirm('Remove this item?')) {
        cart = cart.filter(i => i.id !== productId);
        saveCartToStorage();
        renderCheckoutSummary();
        
        if (cart.length === 0) {
            showNotificationModal('info', 'Cart is empty. Returning to catalog.');
            setTimeout(() => backToCatalog(), 1500);
        }
    }
}

// ============================================
// 7. STEP 1 VALIDATION
// ============================================
function setupPhoneInput() {
    const phoneInput = document.getElementById('cust_phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
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
            
            if (value.length > 13) value = value.substring(0, 13);
            e.target.value = value;
        });
    }
}

function buildFullAddress() {
    const house = document.getElementById("house_street").value.trim();
    const provinceSel = document.getElementById("province");
    const municipalitySel = document.getElementById("municipality");
    const barangaySel = document.getElementById("barangay");

    const provinceText = provinceSel.options[provinceSel.selectedIndex]?.text || '';
    const municipalityText = municipalitySel.options[municipalitySel.selectedIndex]?.text || '';
    const barangayText = barangaySel.value || '';

    document.getElementById("cust_address").value = `${house}, ${barangayText}, ${municipalityText}, ${provinceText}`;
}

function validateStep1() {
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
// 8. INSTAPAY QR GENERATOR
// ============================================
class InstaPayQRGenerator {
    constructor() {
        this.merchantName = 'SOLARPOWER ENERGY CORPORATION';
        this.accountNumber = '002180027200';
        this.bankCode = '0112';
    }

    generateQRData(amount) {
        let qrString = '';
        qrString += this.createTLV('00', '01');
        qrString += this.createTLV('01', '12');
        qrString += this.createTLV('26', this.createMerchantAccountInfo());
        qrString += this.createTLV('52', '0000');
        qrString += this.createTLV('53', '608');
        if (amount && amount > 0) {
            qrString += this.createTLV('54', amount.toFixed(2));
        }
        qrString += this.createTLV('58', 'PH');
        qrString += this.createTLV('59', this.merchantName);
        qrString += this.createTLV('60', 'MUNTINLUPA');
        qrString += this.createTLV('62', this.createAdditionalDataField());
        qrString += '6304';
        qrString += this.calculateCRC16(qrString);
        return qrString;
    }

    createMerchantAccountInfo() {
        let merchantInfo = '';
        merchantInfo += this.createTLV('00', 'com.p2pqrpay');
        merchantInfo += this.createTLV('01', this.accountNumber);
        merchantInfo += this.createTLV('02', this.bankCode);
        return merchantInfo;
    }

    createAdditionalDataField() {
        let additionalData = '';
        const timestamp = Date.now().toString().slice(-8);
        additionalData += this.createTLV('01', `ORD${timestamp}`);
        additionalData += this.createTLV('05', 'SolarPowerOrder');
        return additionalData;
    }

    createTLV(tag, value) {
        const length = value.length.toString().padStart(2, '0');
        return tag + length + value;
    }

    calculateCRC16(data) {
        let crc = 0xFFFF;
        const polynomial = 0x1021;
        for (let i = 0; i < data.length; i++) {
            crc ^= (data.charCodeAt(i) << 8);
            for (let j = 0; j < 8; j++) {
                if (crc & 0x8000) {
                    crc = (crc << 1) ^ polynomial;
                } else {
                    crc = crc << 1;
                }
            }
        }
        crc = crc & 0xFFFF;
        return crc.toString(16).toUpperCase().padStart(4, '0');
    }
}

const qrGenerator = new InstaPayQRGenerator();

// ============================================
// 9. PAYMENT DISPLAY & QR GENERATION
// ============================================
function updatePaymentDisplay() {
    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked')?.value || 'full';
    const totalAmount = window.currentTotalAmount || 0;
    
    const amountToPayDisplay = document.getElementById('amountToPay');
    const qrAmountDisplay = document.getElementById('qrAmountDisplay');
    const paymentNote = document.getElementById('paymentNote');
    const confirmBtn = document.getElementById('confirmPaymentBtn');
    
    let amountToPay = totalAmount;
    let paymentLabel = 'Full Amount (100%)';
    let noteClass = 'alert alert-success';
    
    if (paymentMethod === 'downpayment') {
        amountToPay = totalAmount * 0.5;
        paymentLabel = '50% Down Payment';
        noteClass = 'alert alert-warning';
    } else if (paymentMethod === 'initial') {
        amountToPay = totalAmount * 0.2;
        paymentLabel = '20% Initial Payment';
        noteClass = 'alert alert-info';
    }
    
    const formattedAmount = '₱' + amountToPay.toLocaleString(undefined, {minimumFractionDigits: 2});
    if (amountToPayDisplay) amountToPayDisplay.textContent = formattedAmount;
    if (qrAmountDisplay) qrAmountDisplay.textContent = formattedAmount;
    
    if (paymentNote) {
        paymentNote.className = noteClass;
        paymentNote.innerHTML = `<i class="fas fa-info-circle"></i> You are paying the <strong>${paymentLabel}</strong> via InstaPay.`;
    }
    
    if (confirmBtn) {
        confirmBtn.innerHTML = `<i class="fas fa-check-circle me-2"></i>Confirm Payment & Submit Order`;
        confirmBtn.onclick = () => submitUnionBankPayment();
    }
    
    generateInstaPayQR(amountToPay);
    window.currentAmountToPay = amountToPay;
}

function generateInstaPayQR(amount) {
    const qrContainer = document.getElementById('dynamicQrCode');
    if (!qrContainer) return;
    
    qrContainer.innerHTML = '';

    try {
        const qrData = qrGenerator.generateQRData(amount);
        console.log('📱 Generated InstaPay QR for ₱' + amount.toFixed(2));

        if (typeof QRCode !== 'undefined') {
            new QRCode(qrContainer, {
                text: qrData,
                width: 280,
                height: 280,
                colorDark: '#004b8d',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
            console.log('✅ QR Code generated successfully');
        } else {
            throw new Error('QRCode library not loaded');
        }
    } catch (error) {
        console.error('❌ QR Generation Error:', error);
        qrContainer.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Failed to generate QR code</p>
                <button class="btn btn-sm btn-primary" onclick="updatePaymentDisplay()">Retry</button>
            </div>
        `;
    }
}

// Setup receipt preview
document.addEventListener('DOMContentLoaded', function() {
    const receiptInput = document.getElementById('transactionReceipt');
    if (receiptInput) {
        receiptInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('receiptImage').src = event.target.result;
                    document.getElementById('receiptPreview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }
});

// ============================================
// 10. SUBMIT PAYMENT
// ============================================
function submitUnionBankPayment() {
    console.log('📤 Submitting payment...');
    
    const custName = document.getElementById('cust_name')?.value.trim();
    const custEmail = document.getElementById('cust_email')?.value.trim();
    const custPhone = document.getElementById('cust_phone')?.value.trim();
    const custAddress = document.getElementById('cust_address')?.value.trim();
    
    if (!custName || !custEmail || !custPhone || !custAddress) {
        showNotificationModal('error', 'Please complete all required details.');
        goToStep(1);
        return;
    }
    
    const receiptFile = document.getElementById('transactionReceipt')?.files[0];
    if (!receiptFile) {
        showNotificationModal('error', 'Please upload your transaction receipt.');
        return;
    }
    
    if (cart.length === 0) {
        showNotificationModal('error', 'Your cart is empty.');
        return;
    }
    
    const confirmBtn = document.getElementById('confirmPaymentBtn');
    const originalText = confirmBtn.innerHTML;
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
    
    const totalAmount = window.currentTotalAmount || 0;
    const amountToPay = window.currentAmountToPay || totalAmount;
    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked')?.value || 'full';
    
    const formData = new FormData();
    formData.append('customerName', custName);
    formData.append('customerEmail', custEmail);
    formData.append('customerPhone', custPhone);
    formData.append('customerAddress', custAddress);
    formData.append('paymentType', paymentMethod);
    formData.append('amountToPay', amountToPay);
    formData.append('totalAmount', totalAmount);
    formData.append('items', JSON.stringify(getCartItems()));
    formData.append('transactionReceipt', receiptFile);
    formData.append('referenceNumber', document.getElementById('referenceNumber')?.value.trim() || '');
    
    fetch('controllers/ordering/process-unionbank-payment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = originalText;
        
        if (data.success) {
            console.log('✅ Order submitted:', data.orderRef);
            displayOrderConfirmation(data.orderRef);
            clearCart();
            showNotificationModal('success', 'Order submitted successfully! Check your email.');
        } else {
            console.error('❌ Submission failed:', data.error);
            showNotificationModal('error', data.error || 'Failed to submit order. Please try again.');
        }
    })
    .catch(error => {
        console.error('❌ Error:', error);
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = originalText;
        showNotificationModal('error', 'An error occurred. Please try again.');
    });
}

// ============================================
// 11. ORDER CONFIRMATION
// ============================================
function displayOrderConfirmation(orderRef) {
    document.getElementById('orderRef').textContent = orderRef;
    
    const qrContainer = document.getElementById('orderQr');
    if (qrContainer) {
        qrContainer.innerHTML = '';
        if (typeof QRCode !== 'undefined') {
            new QRCode(qrContainer, {
                text: orderRef,
                width: 200,
                height: 200,
                colorDark: '#004b8d',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H
            });
        }
    }
    
    goToStep(3);
}

function copyOrderRef() {
    const orderRef = document.getElementById('orderRef').textContent;
    
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(orderRef).then(() => {
            showNotificationModal('success', 'Order reference copied!');
        }).catch(() => fallbackCopyText(orderRef));
    } else {
        fallbackCopyText(orderRef);
    }
}

function fallbackCopyText(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    document.body.appendChild(textArea);
    textArea.select();
    
    try {
        document.execCommand('copy');
        showNotificationModal('success', 'Order reference copied!');
    } catch (err) {
        showNotificationModal('error', 'Failed to copy. Please copy manually.');
    }
    
    document.body.removeChild(textArea);
}

// ============================================
// 12. FILTERS & SORTING
// ============================================
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
    
    products.forEach(product => {
        const productCategory = product.getAttribute('data-category');
        
        if (category === 'all' || productCategory === category) {
            product.style.display = 'block';
        } else {
            product.style.display = 'none';
        }
    });
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
    if (!grid) return;
    
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
    
    products.forEach(product => grid.appendChild(product));
}

function toggleViewMore() {
    const grid = document.getElementById('productsGrid');
    const btn = document.getElementById('viewMoreBtn');
    const btnText = btn.querySelector('span');

    const isExpanded = grid.classList.contains('show-all');

    if (isExpanded) {
        grid.classList.remove('show-all');
        btn.classList.remove('active');
        btnText.textContent = 'View More Products';
    } else {
        grid.classList.add('show-all');
        btn.classList.add('active');
        btnText.textContent = 'View Less Products';
    }
}

// ============================================
// 13. SAVINGS CALCULATOR
// ============================================
function setupCalculator() {
    const billInput = document.getElementById('billAmount');
    if (billInput) {
        billInput.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                calculateSavings();
            }
        });
    }
}

function calculateSavings() {
    const billAmount = parseFloat(document.getElementById('billAmount').value);
    const errorMessage = document.getElementById('errorMessage');
    const results = document.getElementById('results');
    
    if (!billAmount || billAmount <= 0) {
        errorMessage.textContent = 'Please enter a valid electric bill amount';
        results.classList.remove('show');
        return;
    }
    
    errorMessage.textContent = '';
    
    const avgRate = 13.40;
    const monthlyConsumption = billAmount / avgRate;
    const dailyConsumption = monthlyConsumption / 30;
    const sunHours = 4.5;
    const systemEfficiency = 0.85;
    const panelWattage = 705;
    const savingsPercentage = 0.95;

    const requiredKwp = dailyConsumption / (sunHours * systemEfficiency);
    const numberOfPanels = Math.ceil((requiredKwp * 1000) / panelWattage);
    const monthlySavings = billAmount * savingsPercentage;
    const yearlySavings = monthlySavings * 12;
    
    document.getElementById('kwpValue').textContent = requiredKwp.toFixed(1);
    document.getElementById('panelsValue').textContent = numberOfPanels;
    document.getElementById('monthlySavings').textContent = '₱' + monthlySavings.toLocaleString('en-PH', {maximumFractionDigits: 0});
    document.getElementById('yearlySavings').textContent = '₱' + yearlySavings.toLocaleString('en-PH', {maximumFractionDigits: 0});
    
    results.classList.add('show');
}

// ============================================
// 14. SUBSCRIPTION
// ============================================
function initializeSubscription() {
    const subscribeForm = document.getElementById('subscribe-form');
    if (!subscribeForm) return;
    
    subscribeForm.addEventListener('submit', function(event) {
        event.preventDefault();
        
        const emailInput = document.getElementById('subscribe-email');
        const email = emailInput.value.trim();
        
        if (!email) {
            showNotificationModal('error', 'Please enter an email address.');
            return;
        }
        
        fetch('controllers/subscribe.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({ email: email })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showNotificationModal('success', data.message);
                emailInput.value = '';
            } else {
                showNotificationModal('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotificationModal('error', 'An error occurred. Please try again.');
        });
    });
}

// ============================================
// 15. NOTIFICATION MODAL
// ============================================
function showNotificationModal(type, message) {
    let modal = document.getElementById('notificationModal');
    if (!modal) {
        const modalHTML = `
            <div class="modal fade" id="notificationModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header border-0">
                            <h5 class="modal-title">
                                <i id="notificationModalIcon" class="me-2"></i>
                                <span id="notificationModalTitleText">Notification</span>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <div class="mb-3">
                                <i id="notificationModalBodyIcon" style="font-size: 48px;"></i>
                            </div>
                            <p id="notificationModalMessage" class="mb-0"></p>
                        </div>
                        <div class="modal-footer border-0 justify-content-center">
                            <button type="button" class="btn" id="notificationModalBtn" data-bs-dismiss="modal">OK</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        modal = document.getElementById('notificationModal');
    }
    
    const icon = document.getElementById('notificationModalIcon');
    const bodyIcon = document.getElementById('notificationModalBodyIcon');
    const titleText = document.getElementById('notificationModalTitleText');
    const messageEl = document.getElementById('notificationModalMessage');
    const btn = document.getElementById('notificationModalBtn');
    
    if (type === 'success') {
        icon.className = 'fas fa-check-circle text-success me-2';
        bodyIcon.className = 'fas fa-check-circle text-success';
        titleText.textContent = 'Success';
        btn.className = 'btn btn-success';
    } else if (type === 'error') {
        icon.className = 'fas fa-exclamation-circle text-danger me-2';
        bodyIcon.className = 'fas fa-times-circle text-danger';
        titleText.textContent = 'Error';
        btn.className = 'btn btn-danger';
    } else {
        icon.className = 'fas fa-info-circle text-primary me-2';
        bodyIcon.className = 'fas fa-info-circle text-primary';
        titleText.textContent = 'Information';
        btn.className = 'btn btn-primary';
    }
    
    messageEl.textContent = message;
    
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
}


</script>

</html>
