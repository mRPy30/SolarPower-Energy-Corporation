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
                    <button class="btn-outline" onclick="backToCatalog()">← Continue Shopping</button>
                    <button class="btn-primary" onclick="validateStep1()">Proceed to Payment →</button>
                </div>
            </div>

            <div id="checkoutStep2" class="checkout-card" style="display:none;">
    <h3>Order Summary & Payment</h3>

    <!-- Payment Method Selection -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-credit-card me-2"></i>Select Payment Method
            </h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-4">
                <i class="fas fa-shield-alt me-2"></i>
                <strong>Secure Payment Required:</strong> All orders require upfront payment via Maya to prevent fake bookings. Your payment is secure and protected.
            </div>

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
                                <p class="text-muted mb-0 small">Pay complete amount via Maya now</p>
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
                                <p class="text-muted mb-0 small">Pay 50% now via Maya, 50% before delivery</p>
                            </div>
                            <span class="badge bg-warning text-dark">Popular</span>
                        </div>
                    </label>
                </div>

                <!-- 20% Initial Payment (NEW!) -->
                <div class="form-check payment-option mb-3">
                    <input class="form-check-input" type="radio" name="paymentMethod" id="paymentInitial" value="initial" onchange="updatePaymentDisplay()">
                    <label class="form-check-label w-100" for="paymentInitial">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>
                                    <i class="fas fa-hand-holding-usd text-info me-2"></i>
                                    20% Initial Payment
                                </strong>
                                <p class="text-muted mb-0 small">Pay 20% now via Maya, 80% before installation</p>
                            </div>
                            <span class="badge bg-info">Flexible</span>
                        </div>
                    </label>
                </div>

            </div>

            <!-- Payment Security Note -->
            <div class="alert alert-light border mt-3">
                <div class="d-flex align-items-start">
                    <i class="fas fa-lock text-success me-3" style="font-size: 1.5rem;"></i>
                    <div>
                        <strong>Why payment is required:</strong>
                        <ul class="mb-0 mt-2 small">
                            <li>Prevents fake bookings and no-shows</li>
                            <li>Secures your order and installation schedule</li>
                            <li>Your payment is protected by Maya's security</li>
                            <li>Get priority processing and installation</li>
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
        <i class="fas fa-info-circle"></i> You are paying the <strong>Full Amount (100%)</strong> via Maya.
    </div>

    <!-- Action Buttons -->
    <div class="checkout-actions mt-4">
        <button class="btn-outline" onclick="goToStep(1)">
            <i class="fas fa-arrow-left me-2"></i>Edit Details
        </button>
        <button id="confirmPaymentBtn" class="btn-primary" onclick="payWithMaya('full')">
            <i class="fas fa-shield-alt me-2"></i>Pay Full Amount with Maya
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
                                <span class="peso">₱</span>
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
            <div class="row g-4 mb-5">
                <!-- Video 1 -->
                <div class="col-lg-3 col-md-6">
                    <div class="video-card">
                        <div class="video-wrapper">
                            <div class="fb-video-responsive">
                                <iframe src="https://www.facebook.com/plugins/video.php?href=https://www.facebook.com/reel/1556081359036132/&show_text=false" allowfullscreen="true" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share">
                                    
                                </iframe>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Video 2 -->
                <div class="col-lg-3 col-md-6">
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
                                <p class="text-muted small">We’ll contact you within 24 hours.</p>
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
                                        <option value="Residential">🏠 Residential</option>
                                        <option value="Commercial">🏢 Commercial</option>
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
    initializeContactForm();
    initializeInspectionForm();
    setupCalculator();
    
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
    // Load cart from memory (no localStorage in artifacts)
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
    // Save cart to memory
    try {
        window.cartStorage = JSON.stringify(cart);
        console.log('💾 Cart saved');
    } catch (error) {
        console.error('❌ Error saving cart:', error);
    }
}

function addToCartFromButton(btn) {
    console.log('🛒 Adding product to cart...');
    const product = JSON.parse(btn.getAttribute('data-product'));
    addToCartLogic(product);
    showCartPopup();
    showNotificationModal('success', '✅ Product added to cart!');
}

function addToCartLogic(product) {
    const existingItem = cart.find(item => item.id === product.id);
    
    if (existingItem) {
        existingItem.quantity += 1;
        console.log('📈 Increased quantity for:', product.displayName);
    } else {
        cart.push({
            id: product.id,
            displayName: product.displayName,
            price: parseFloat(product.price),
            image_path: product.image_path,
            quantity: 1
        });
        console.log('➕ Added new item:', product.displayName);
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
        item.quantity += change;
        
        if (item.quantity < 1) {
            item.quantity = 1;
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
                <span>Delivery Fee:</span>
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
    
    // Add phone number formatter
    const phoneInput = document.getElementById('cust_phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', formatPhoneNumber);
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
    
    // Close cart modal
    const cartModal = bootstrap.Modal.getInstance(document.getElementById('cartModal'));
    if (cartModal) {
        cartModal.hide();
    }
    
    // Show checkout section
    showCheckout();
    renderCheckoutSummary();
}

function showCheckout() {
    // Hide all other sections
    const sectionsToHide = [
        '.hero', '.featured-brands', '.savings-calculator', 
        '.why-choose-us', '.services-section', '.solar-tips-section', 
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
    console.log('🔙 Returning to catalog...');
    
    document.getElementById('checkoutSection').style.display = 'none';
    
    const sectionsToShow = [
        '.hero', '.featured-brands', '.savings-calculator', 
        '.why-choose-us', '.services-section', '.solar-tips-section', 
        '#catalogSection', '.contact-us', '.subscription-section', 'footer'
    ];
    
    sectionsToShow.forEach(selector => {
        const el = document.querySelector(selector);
        if(el) el.style.display = 'block';
    });
    
    window.scrollTo(0, document.getElementById('catalogSection').offsetTop - 100);
}

function goToStep(step) {
    console.log('📍 Moving to step:', step);
    
    // Hide all steps
    for (let i = 1; i <= 3; i++) {
        document.getElementById(`checkoutStep${i}`).style.display = 'none';
        document.getElementById(`ind-step${i}`).classList.remove('active', 'completed');
    }
    
    // Show current step
    document.getElementById(`checkoutStep${step}`).style.display = 'block';
    document.getElementById(`ind-step${step}`).classList.add('active');
    
    // Mark previous steps as completed
    for (let i = 1; i < step; i++) {
        document.getElementById(`ind-step${i}`).classList.add('completed');
    }
    
    // Update progress indicator
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
    
    const formattedTotal = "₱" + grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
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

    const provinceText = provinceSel.options[provinceSel.selectedIndex]?.text || '';
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
// 5. MAYA PAYMENT INTEGRATION
// ============================================

function updatePaymentDisplay() {
    console.log('💳 Updating payment display...');
    
    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked')?.value || 'full';
    const totalAmount = window.currentTotalAmount || 0;
    
    const amountToPayDisplay = document.getElementById('amountToPay');
    const paymentNote = document.getElementById('paymentNote');
    const confirmBtn = document.getElementById('confirmPaymentBtn');
    const mayaQrSection = document.getElementById('mayaQrSection');
    
    if (mayaQrSection) {
        mayaQrSection.style.display = 'none'; // Always hide static QR
    }
    
    if (paymentMethod === 'full') {
        amountToPayDisplay.textContent = '₱' + totalAmount.toLocaleString(undefined, {minimumFractionDigits: 2});
        paymentNote.innerHTML = '<i class="fas fa-info-circle"></i> You are paying the <strong>Full Amount</strong> via Maya.';
        paymentNote.className = 'alert alert-info';
        confirmBtn.textContent = 'Pay with Maya';
        confirmBtn.onclick = () => payWithMaya('full');
    } else if (paymentMethod === 'downpayment') {
        const downpayment = totalAmount * 0.5;
        amountToPayDisplay.textContent = '₱' + downpayment.toLocaleString(undefined, {minimumFractionDigits: 2});
        paymentNote.innerHTML = '<i class="fas fa-info-circle"></i> You are paying <strong>50% Down Payment</strong> via Maya. Remaining 50% upon delivery.';
        paymentNote.className = 'alert alert-warning';
        confirmBtn.textContent = 'Pay 50% Down Payment';
        confirmBtn.onclick = () => payWithMaya('downpayment');
    } else if (paymentMethod === 'initial') {
        // 20% Initial Payment
        const initialPayment = totalAmount * 0.2;
        const remaining = totalAmount - initialPayment;
        amountToPayDisplay.textContent = '₱' + initialPayment.toLocaleString(undefined, {minimumFractionDigits: 2});
        paymentNote.innerHTML = `
            <i class="fas fa-info-circle"></i> 
            You are paying <strong>20% Initial Payment</strong> (₱${initialPayment.toLocaleString(undefined, {minimumFractionDigits: 2})}) via Maya.<br>
            <small class="text-muted">Remaining balance: ₱${remaining.toLocaleString(undefined, {minimumFractionDigits: 2})} (80% - to be paid before installation)</small>
        `;
        paymentNote.className = 'alert alert-info';
        confirmBtn.textContent = 'Pay 20% Initial Payment with Maya';
        confirmBtn.onclick = () => payWithMaya('initial');
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

function payWithMaya(paymentType) {
    console.log('💰 Processing Maya payment...');
    
    // Validate customer info
    const custName = document.getElementById('cust_name')?.value.trim();
    const custEmail = document.getElementById('cust_email')?.value.trim();
    const custPhone = document.getElementById('cust_phone')?.value.trim();
    const custAddress = document.getElementById('cust_address')?.value.trim();
    
    if (!custName || !custEmail || !custPhone || !custAddress) {
        showNotificationModal('error', 'Please complete all required fields.');
        return;
    }
    
    if (cart.length === 0) {
        showNotificationModal('error', 'Your cart is empty.');
        return;
    }
    
    // Show loading
    const confirmBtn = document.getElementById('confirmPaymentBtn');
    const originalText = confirmBtn.textContent;
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    
    // Calculate amount
    const totalAmount = window.currentTotalAmount || 0;
    let amountToPay = totalAmount;
    
    if (paymentType === 'downpayment') {
        amountToPay = totalAmount * 0.5;
    }
    
    // Prepare order data
    const orderData = {
        customerName: custName,
        customerEmail: custEmail,
        customerPhone: custPhone,
        customerAddress: custAddress,
        paymentType: paymentType,
        amountToPay: amountToPay,
        totalAmount: totalAmount,
        items: getCartItems()
    };
    
    console.log('📤 Sending to Maya API:', orderData);
    
    // Call backend to create Maya payment
    fetch('process_payment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        confirmBtn.disabled = false;
        confirmBtn.textContent = originalText;
        
        if (data.success) {
            console.log('✅ Maya payment created:', data.orderRef);
            
            // Store order reference
            sessionStorage.setItem('currentOrderRef', data.orderRef);
            
            // Redirect to Maya payment (amount is LOCKED by API)
            console.log('🔗 Redirecting to Maya:', data.paymentUrl);
            window.location.href = data.paymentUrl;
            
        } else {
            console.error('❌ Payment creation failed:', data.error);
            showNotificationModal('error', data.error || 'Failed to create payment. Please try again.');
        }
    })
    .catch(error => {
        console.error('❌ Payment error:', error);
        confirmBtn.disabled = false;
        confirmBtn.textContent = originalText;
        showNotificationModal('error', 'An error occurred. Please try again.');
    });
}

function confirmCODOrder() {
    console.log('💵 Processing COD order...');
    
    const custName = document.getElementById('cust_name')?.value.trim();
    const custEmail = document.getElementById('cust_email')?.value.trim();
    const custPhone = document.getElementById('cust_phone')?.value.trim();
    const custAddress = document.getElementById('cust_address')?.value.trim();
    
    if (!custName || !custEmail || !custPhone || !custAddress) {
        showNotificationModal('error', 'Please complete all required fields.');
        return;
    }
    
    if (cart.length === 0) {
        showNotificationModal('error', 'Your cart is empty.');
        return;
    }
    
    const confirmed = confirm('Confirm Cash on Delivery order?\n\nYou will pay when order arrives.');
    if (!confirmed) return;
    
    const confirmBtn = document.getElementById('confirmPaymentBtn');
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    
    const totalAmount = window.currentTotalAmount || 0;
    
    const orderData = {
        customerName: custName,
        customerEmail: custEmail,
        customerPhone: custPhone,
        customerAddress: custAddress,
        paymentType: 'cod',
        amountToPay: 0,
        totalAmount: totalAmount,
        items: getCartItems(),
        paymentMethod: 'cod'
    };
    
    fetch('controllers/ordering/create-cod-order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Confirm COD Order';
        
        if (data.success) {
            console.log('✅ COD order placed:', data.orderRef);
            const orderRef = data.orderRef || 'ORD-COD-' + Date.now();
            displayOrderConfirmation(orderRef);
            clearCart();
            showNotificationModal('success', 'Order placed successfully!');
        } else {
            console.error('❌ COD order failed:', data.message);
            showNotificationModal('error', data.message || 'Failed to place order.');
        }
    })
    .catch(error => {
        console.error('❌ COD error:', error);
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Confirm COD Order';
        showNotificationModal('error', 'An error occurred. Please try again.');
    });
}

function displayOrderConfirmation(orderRef) {
    console.log('🎉 Displaying order confirmation:', orderRef);
    
    // Update confirmation step details
    document.getElementById('confOrderRef').textContent = orderRef;
    document.getElementById('confCustomerName').textContent = document.getElementById('cust_name').value;
    document.getElementById('confTotalAmount').textContent = '₱' + (window.currentTotalAmount || 0).toLocaleString(undefined, {minimumFractionDigits: 2});
    
    // Switch to step 3
    goToStep(3);
}

// ============================================
// 6. SAVINGS CALCULATOR LOGIC
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
    
    // Validation
    if (!billAmount || billAmount <= 0) {
        errorMessage.textContent = 'Please enter a valid electric bill amount';
        results.classList.remove('show');
        return;
    }
    
    errorMessage.textContent = '';
    
    // Constants for Philippines
    const avgRate = 13.40;
    const monthlyConsumption = billAmount / avgRate;
    const dailyConsumption = monthlyConsumption / 30;
    const sunHours = 4.5;
    const systemEfficiency = 0.85;
    const panelWattage = 705;
    const savingsPercentage = 0.95;

    // Calculate required system size (kWp)
    const requiredKwp = dailyConsumption / (sunHours * systemEfficiency);
    const numberOfPanels = Math.ceil((requiredKwp * 1000) / panelWattage);
    const monthlySavings = billAmount * savingsPercentage;
    const yearlySavings = monthlySavings * 12;
    
    // Display results
    document.getElementById('kwpValue').textContent = requiredKwp.toFixed(1);
    document.getElementById('panelsValue').textContent = numberOfPanels;
    document.getElementById('monthlySavings').textContent = '₱' + monthlySavings.toLocaleString('en-PH', {maximumFractionDigits: 0});
    document.getElementById('yearlySavings').textContent = '₱' + yearlySavings.toLocaleString('en-PH', {maximumFractionDigits: 0});
    
    results.classList.add('show');
}

// ============================================
// 7. FILTERS & SEARCH
// ============================================

function initializeFilters() {
    const searchInput = document.getElementById('productSearch');
    const categoryLinks = document.querySelectorAll('.category-filter');

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase();
            filterProducts(term, 'all');
        });
    }

    categoryLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            categoryLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
            const category = link.getAttribute('data-category');
            filterProducts('', category);
        });
    });
}

function filterProducts(searchTerm, category) {
    const products = document.querySelectorAll('.product-card-container');
    
    products.forEach(product => {
        const name = product.getAttribute('data-name').toLowerCase();
        const cat = product.getAttribute('data-category');
        
        const matchesSearch = name.includes(searchTerm);
        const matchesCategory = category === 'all' || cat === category;

        if (matchesSearch && matchesCategory) {
            product.style.display = 'block';
        } else {
            product.style.display = 'none';
        }
    });
}

function initializeSort() {
    const sortSelect = document.getElementById('sortProducts');
    if (!sortSelect) return;

    sortSelect.addEventListener('change', function() {
        const container = document.getElementById('productGrid');
        const items = Array.from(container.querySelectorAll('.product-card-container'));
        
        items.sort((a, b) => {
            const priceA = parseFloat(a.getAttribute('data-price'));
            const priceB = parseFloat(b.getAttribute('data-price'));
            
            if (this.value === 'price-low') return priceA - priceB;
            if (this.value === 'price-high') return priceB - priceA;
            return 0;
        });

        items.forEach(item => container.appendChild(item));
    });
}

// ============================================
// 8. UTILS & NOTIFICATIONS
// ============================================

function showNotificationModal(type, message) {
    // Check if simple Toast exists, if not, use alert
    const toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        alert(message);
        return;
    }

    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0 show" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    toastContainer.innerHTML = toastHtml;
    setTimeout(() => {
        toastContainer.innerHTML = '';
    }, 4000);
}

function initializeSubscription() {
    const form = document.getElementById('subscribeForm');
    if (!form) return;

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const email = form.querySelector('input[type="email"]').value;
        showNotificationModal('success', `Salamat! ${email} has been subscribed to our newsletter.`);
        form.reset();
    });
}

function initializeContactForm() {
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', (e) => {
            e.preventDefault();
            showNotificationModal('success', 'Message sent! We will get back to you soon.');
            contactForm.reset();
        });
    }
}

function initializeInspectionForm() {
    const inspectionForm = document.getElementById('inspectionForm');
    if (inspectionForm) {
        inspectionForm.addEventListener('submit', (e) => {
            e.preventDefault();
            showNotificationModal('success', 'Request received. Our engineer will call you for the schedule.');
            inspectionForm.reset();
            bootstrap.Modal.getInstance(document.getElementById('inspectionModal')).hide();
        });
    }
}

// PRODUCT SORT FUNCTIONALITY
// ========================================
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
    
    // Sort based on selected option
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
                
            default: // 'default'
                return 0;
        }
    });
    
    // Re-append products in sorted order
    products.forEach(product => {
        grid.appendChild(product);
    });
}

// ========================================
// VIEW MORE FUNCTIONALITY
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

// RESET FILTERS (UTILITY)
// ========================================
function resetFilters() {
    document.querySelector('.filter-btn[data-category="all"]').click();
    document.getElementById('sortSelect').value = 'default';
    sortProducts('default');
}

</script>

</html>