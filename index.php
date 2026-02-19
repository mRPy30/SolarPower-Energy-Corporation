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
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <link rel="stylesheet" href="assets/style.css">
<body> 

   <!-- VIDEO POPUP BANNER -->
    <div class="video-popup-overlay" id="videoPopup">
        <div class="video-popup-container">
            <button class="video-popup-close" id="closeVideoPopup" aria-label="Close popup">
                <i class="fas fa-times"></i>
            </button>
            <div class="video-popup-content" style="cursor: pointer;" onclick="handleVideoClick(event)">
                <video id="promoVideo" autoplay muted loop playsinline>
                    <source src="assets/img/promo-banner.webm" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
        </div>
    </div>

    <?php include "includes/header.php" ?>

    <div class="hero-container">
        <section class="hero" id="home">

            <div class="hero-content">
                <!-- LEFT: HERO TEXT -->
                <div class="hero-text" data-aos="fade-right">
                    <h1>Smart Energy for<br>Smarter Homes</h1>
                    <p class="hero-tagline">One Stop Shop for Solar Power Mega Company</p>
                    <p>Invest in solar today - enjoy decades of energy independence and savings.</p>
                    <div class="hero-cta">
                        <button class="btn btn-primary" onclick="window.location.href='about.php'">
                            Learn More
                        </button>
                        <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#inspectionModal">Book for Inspection</button>
                    </div>
                </div>

                <!-- RIGHT: MINIMAL PROMO BANNER --
                <div class="promo-banner">
                    <div class="star star-1">✦</div>
                    <div class="star star-2">✦</div>
                    <div class="star star-3">✦</div>
                    
                    <div class="promo-content">
                        <span class="promo-badge">New Year Sale</span>
                        
                        <div class="promo-offer">
                            <h3>Get</h3>
                            <span class="promo-discount">10% OFF</span>
                        </div>
                        
                        <p class="promo-footer">The authority to sell</p>
                        
                        <div class="promo-ribbon">
                            <h4>Install Now, Pay Later</h4>
                        </div>
                        
                        <button class="order-btn">Order Now</button>
                    </div>
                </div>-->
            </div>
        </section>
    </div>

 <!-- Savings Calculator -->
    <!-- Savings Calculator -->
    <section class="savings-calculator">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="calculator-box collapsed" id="calculatorBox" data-aos="fade-up">
                        <div class="savings-icon">
                            <i class="fa-regular fa-lightbulb"></i>
                        </div>
                        <h2>Let's check how much you can save!</h2>
                        <p>What's your monthly electric bill?</p>
                        <div class="row justify-content-center mb-4">
                            <div class="col-lg-4 col-md-6">
                                <div class="input-group-custom">
                                    <input 
                                        type="number"
                                        id="billAmount"
                                        placeholder="0"
                                        min="0"
                                        step="0.01"
                                        onfocus="expandCalculator()"
                                        onblur="shrinkCalculatorIfEmpty()"
                                    >
                                    <p>Monthly Electric Bill (₱)</p>
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



<!-- ---------- CATALOG SECTION ---------- -->
 <section class="catalogs-section" id="catalogSection">
        <div class="container">
             
            <div class="catalog-header" data-aos="fade-up">
                <h2>Our Products</h2>
                <p class="catalog-subtitle">Premium solar solutions for your energy needs</p>
            </div>

            <!--promotional product-->
            <?php include "includes/promotional.php" ?>

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

    <section class="bnpl-section" id="bnplSection">
    <div class="container">
        <!-- Header - Centered with aligned description -->
        <div class="bnpl-header" data-aos="fade-up">
            <h2>Install Now, <span class="highlight">Pay Later</span></h2>
            <p class="bnpl-subtitle">Switch to Solar now and enjoy massive savings with 30% down payments.</p>
        </div>
        
        <!-- Steps Grid -->
        <div class="bnpl-steps">
            <!-- Step 1 -->
            <div class="bnpl-step" data-aos="fade-up" data-aos-delay="100">
                <div class="step-circle" data-step="1">
                    <div class="step-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
                <h3>Book Free Ocular Inspection</h3>
                <p>Schedule your free site visit and let our experts assess your property.</p>
            </div>
            
            <!-- Step 2 -->
            <div class="bnpl-step" data-aos="fade-up" data-aos-delay="200">
                <div class="step-circle" data-step="2">
                    <div class="step-icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                </div>
                <h3>Get Free Quotation</h3>
                <p>Receive a detailed proposal tailored to your energy needs and budget.</p>
            </div>
            
            <!-- Step 3 -->
            <div class="bnpl-step" data-aos="fade-up" data-aos-delay="300">
                <div class="step-circle" data-step="3">
                    <div class="step-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                </div>
                <h3>Installation Process</h3>
                <p>Our certified team installs your solar system quickly and professionally.</p>
            </div>
            
            <!-- Step 4 -->
            <div class="bnpl-step" data-aos="fade-up" data-aos-delay="400">
                <div class="step-circle" data-step="4">
                    <div class="step-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                </div>
                <h3>Pay Later</h3>
                <p>Flexible payment plans with zero interest. Start saving from day one!</p>
            </div>
        </div>
        
        <!-- Optional: Add CTA button -->
        <div class="bnpl-cta" style="text-align: center; margin-top: 50px;">
            <button class="btn btn-primary btn-lg" onclick="window.location.href='#inspectionModal'" data-bs-toggle="modal" data-bs-target="#inspectionModal">
                Get Started Today
            </button>
        </div>
    </div>
</section>

    <!-- Rent to Own Section (Industrial & Commercial Only) -->
    <section class="rent-to-own-section" id="rentToOwnSection">
        <div class="container">
            <div class="rto-wrapper">
                <!-- Left: Form -->
                <div class="rto-form-container" data-aos="fade-right">
                    <div class="rto-header">
                        <h2>Rent to Own Solar System</h2>
                        <p class="rto-subtitle">For Industrial & Commercial Properties Only</p>
                        <div class="rto-badge">
                            <i class="fas fa-building"></i>
                            <span>Industrial & Commercial</span>
                        </div>
                    </div>

                    <form class="rto-form" id="rentToOwnForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="rto_firstName">First Name <span class="required">*</span></label>
                                <input type="text" id="rto_firstName" name="firstName" required>
                            </div>
                            <div class="form-group">
                                <label for="rto_lastName">Last Name <span class="required">*</span></label>
                                <input type="text" id="rto_lastName" name="lastName" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="rto_email">Email <span class="required">*</span></label>
                                <input type="email" id="rto_email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="rto_contact">Contact Number <span class="required">*</span></label>
                                <input type="tel" id="rto_contact" name="contactNumber" required placeholder="+63">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="rto_company">Company Name <span class="required">*</span></label>
                            <input type="text" id="rto_company" name="companyName" required>
                        </div>

                        <div class="form-group">
                            <label for="rto_province">Province/City <span class="required">*</span></label>
                            <select id="rto_province" name="province" required>
                                <option value="">Select Province/City</option>
                                <option value="Metro Manila">Metro Manila</option>
                                <option value="Cavite">Cavite</option>
                                <option value="Laguna">Laguna</option>
                                <option value="Batangas">Batangas</option>
                                <option value="Rizal">Rizal</option>
                                <option value="Bulacan">Bulacan</option>
                                <option value="Pampanga">Pampanga</option>
                                <option value="Cebu">Cebu</option>
                                <option value="Davao">Davao</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="rto_electricityBill">Monthly Electricity Bill (₱) <span class="required">*</span></label>
                            <input type="number" id="rto_electricityBill" name="electricityBill" required min="8000" placeholder="₱ 50,000">
                            <small>For our smallest system size (5kWp), we recommend your bill to at least be ₱8,000 to maximize savings.</small>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="rto_propertyType">Type of Property <span class="required">*</span></label>
                                <select id="rto_propertyType" name="propertyType" required>
                                    <option value="">Please select</option>
                                    <option value="Factory">Factory</option>
                                    <option value="Warehouse">Warehouse</option>
                                    <option value="Office Building">Office Building</option>
                                    <option value="Manufacturing Plant">Manufacturing Plant</option>
                                    <option value="Commercial Building">Commercial Building</option>
                                    <option value="Industrial Complex">Industrial Complex</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="rto_ownership">Do you own the property? <span class="required">*</span></label>
                                <select id="rto_ownership" name="ownership" required>
                                    <option value="">Please select</option>
                                    <option value="Yes">Yes, I own it</option>
                                    <option value="No">No, I lease/rent it</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="rto_proceed">How would you like to proceed? <span class="required">*</span></label>
                                <select id="rto_proceed" name="proceed" required>
                                    <option value="">Please select</option>
                                    <option value="Site Inspection">Site Inspection</option>
                                    <option value="Get a Quote">Get a Quote</option>
                                    <option value="Consultation">Consultation Call</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="rto_installation">Target Installation <span class="required">*</span></label>
                                <select id="rto_installation" name="installation" required>
                                    <option value="">Please select</option>
                                    <option value="Immediate">Immediate (1-2 months)</option>
                                    <option value="Within 6 months">Within 6 months</option>
                                    <option value="Within 1 year">Within 1 year</option>
                                    <option value="Just exploring">Just exploring</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit-rto">
                            <span class="btn-text">Submit Application</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>
                    </form>
                </div>

                <!-- Right: Image/Visual -->
                <div class="rto-visual" data-aos="fade-left">
                    <div class="visual-content">
                        <div class="solar-illustration">
                            <i class="fas fa-solar-panel"></i>
                        </div>
                        <h3>Power Your Business</h3>
                        <p>Reduce operational costs with our flexible rent-to-own solar solutions designed for industrial and commercial properties.</p>
                        
                        <div class="benefits-list">
                            <div class="benefit-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Zero upfront costs</span>
                            </div>
                            <div class="benefit-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Flexible payment terms</span>
                            </div>
                            <div class="benefit-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Full ownership after lease</span>
                            </div>
                            <div class="benefit-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Immediate energy savings</span>
                            </div>
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

                        <!-- Payment Instructions -->
                        <div class="alert alert-light border">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-info-circle text-primary me-3" style="font-size: 1.5rem;"></i>
                                <div>
                                    <strong>After Payment:</strong>
                                    <ol class="mb-0 mt-2 small">
                                        <li>Take a screenshot of your transaction receipt</li>
                                        <li>Send the screenshot to <strong>jeca.wdc@gmail.com</strong></li>
                                        <li>Include your order details in the email</li>
                                        <li>Wait for confirmation from our team</li>
                                    </ol>
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
                        <i class="fas fa-check-circle me-2"></i>I Have Sent Payment
                    </button>
                </div>
            </div>

            <div id="checkoutStep3" class="checkout-card" style="display:none;">
                <div class="text-center py-5">

                    <i class="fas fa-check-circle text-success mb-3" style="font-size:64px;"></i>
                    <h3>Order Submitted Successfully!</h3>
                    <p class="text-muted">Thank you for your order. We will verify your InstaPay payment shortly.</p>

                    <div class="alert alert-info mt-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Next Steps:</strong>
                        <ul class="list-unstyled mt-2 mb-0">
                            <li>✓ We will verify your payment receipt sent to <strong>jeca.wdc@gmail.com</strong></li>
                            <li>✓ You will receive a confirmation email within 24 hours</li>
                            <li>✓ Our team will contact you to schedule delivery/installation</li>
                        </ul>
                    </div>

                <!-- Order Reference -->
                <p class="mt-3">
                    <strong>Order Reference:</strong><br>
                    <span id="confOrderRef" class="fw-bold fs-5"></span>
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

<section class="featured-brands" id="featured-brands" data-aos="fade-up">
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
    
    <!-- Services Section -->
       <section class="roles-section">
        <div class="section-header" data-aos="fade-up">
            <h2>What We Do</h2>
            <p class="section-subtitle">Your complete solar energy partner - from supply to installation</p>
        </div>

        <div class="roles-grid">
            <!-- Supplier Card -->
            <div class="role-card" data-aos="fade-up" data-aos-delay="100">
                <div class="number-badge">1</div>
                <div class="image-wrapper">
                    <img src="https://images.unsplash.com/photo-1509391366360-2e959784a276?w=800&h=500&fit=crop" alt="Solar Panel Field">
                    <div class="icon-badge">
                        <i class="fas fa-solar-panel"></i>
                    </div>
                </div>
                <div class="content-wrapper">
                    <h3>Supplier</h3>
                    <p>Direct source for premium photovoltaic panels and high-efficiency inverters from global Tier-1 brands.</p>
                </div>
            </div>

            <!-- Installer Card -->
            <div class="role-card" data-aos="fade-up" data-aos-delay="200">
                <div class="number-badge">2</div>
                <div class="image-wrapper">
                    <img src="https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?w=800&h=500&fit=crop" alt="Solar Installation">
                    <div class="icon-badge">
                        <i class="fas fa-tools"></i>
                    </div>
                </div>
                <div class="content-wrapper">
                    <h3>Installer</h3>
                    <p>Expert technical teams providing end-to-end residential and commercial mounting and system deployment.</p>
                </div>
            </div>

            <!-- Contractor Card -->
            <div class="role-card" data-aos="fade-up" data-aos-delay="300">
                <div class="number-badge">3</div>
                <div class="image-wrapper">
                    <img src="https://images.unsplash.com/photo-1504328345606-18bbc8c9d7d1?w=800&h=500&fit=crop" alt="Industrial Work">
                    <div class="icon-badge">
                        <i class="fas fa-hard-hat"></i>
                    </div>
                </div>
                <div class="content-wrapper">
                    <h3>Contractor</h3>
                    <p>Full project management, from site assessment and permitting to final grid interconnection compliance.</p>
                </div>
            </div>

            <!-- Dealer Card -->
            <div class="role-card" data-aos="fade-up" data-aos-delay="400">
                <div class="number-badge">4</div>
                <div class="image-wrapper">
                    <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=800&h=500&fit=crop" alt="Commercial Buildings">
                    <div class="icon-badge">
                        <i class="fas fa-handshake"></i>
                    </div>
                </div>
                <div class="content-wrapper">
                    <h3>Dealer</h3>
                    <p>Authorized local partnership offering specialized solar hardware packages and customized energy kits.</p>
                </div>
            </div>

            <!-- Distributor Card -->
            <div class="role-card">
                <div class="number-badge">5</div>
                <div class="image-wrapper">
                    <img src="https://images.unsplash.com/photo-1578575437130-527eed3abbec?w=800&h=500&fit=crop" alt="Shipping and Logistics">
                    <div class="icon-badge">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                </div>
                <div class="content-wrapper">
                    <h3>Distributor</h3>
                    <p>Logistics hub ensuring nationwide availability of renewable energy technology to sub-dealers and partners.</p>
                </div>
            </div>

            <!-- Seller Card -->
            <div class="role-card">
                <div class="number-badge">6</div>
                <div class="image-wrapper">
                    <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?w=800&h=500&fit=crop" alt="Modern Office">
                    <div class="icon-badge">
                        <i class="fas fa-store"></i>
                    </div>
                </div>
                <div class="content-wrapper">
                    <h3>Seller</h3>
                    <p>Retail energy consultants providing transparent pricing and tailored ROI estimates for every client.</p>
                </div>
            </div>
        </div>
    </section>

      <!-- Solar System Tips Section -->
<section class="solar-tips-section">
    <div class="container">
        <div class="section-header mb-5" data-aos="fade-up">
            <h2>Solar System Tips</h2>
            <p class="section-subtitle">Essential insights to maximize your solar investment</p>
        </div>
        
        <!-- Video Grid -->
        <div class="row g-4 mb-5 justify-content-center">
            <div class="col-lg-6 col-md-10" data-aos="fade-right">
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
            <div class="col-lg-6 col-md-10" data-aos="fade-left">
                <div class="video-card">
                    <div class="video-wrapper">
                        <div class="fb-video-responsive">
                            <iframe 
                                src="https://www.facebook.com/plugins/video.php?href=https://www.facebook.com/61578373983187/videos/1562743611632906/?__so__=watchlist&__rv__=video_home_www_playlist_video_list" 
                                allowfullscreen="true" 
                                allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Minimal Info Tips -->
        <div class="tips-grid">
            <div class="tip-item" data-aos="fade-up" data-aos-delay="100">
                <div class="tip-content">
                    <span class="tip-number">01</span>
                    <h5>Regular Panel Maintenance</h5>
                    <p>Clean panels every 3-6 months to maintain peak efficiency. Dust and debris can reduce output by up to 25%.</p>
                </div>
            </div>
            
            <div class="tip-item" data-aos="fade-up" data-aos-delay="200">
                <div class="tip-content">
                    <span class="tip-number">02</span>
                    <h5>Optimal Panel Positioning</h5>
                    <p>Ensure minimal shading throughout the day. Even partial shade can significantly impact performance.</p>
                </div>
            </div>
            
            <div class="tip-item" data-aos="fade-up" data-aos-delay="300">
                <div class="tip-content">
                    <span class="tip-number">03</span>
                    <h5>Time Your Energy Usage</h5>
                    <p>Run heavy appliances during peak solar hours (10 AM - 3 PM) to maximize self-consumption.</p>
                </div>
            </div>
            
            <div class="tip-item" data-aos="fade-up" data-aos-delay="100">
                <div class="tip-content">
                    <span class="tip-number">04</span>
                    <h5>Grid-Tie vs Hybrid Systems</h5>
                    <p>Grid-tie systems are more affordable and sell excess power back to the grid, while hybrid systems include battery backup for power during outages. Choose based on your need for energy independence.</p>
                </div>
            </div>
            
            <div class="tip-item" data-aos="fade-up" data-aos-delay="200">
                <div class="tip-content">
                    <span class="tip-number">05</span>
                    <h5>Battery Storage Best Practices</h5>
                    <p>Maintain battery charge between 20-80% for optimal lifespan and keep in temperature-controlled environments.</p>
                </div>
            </div>
            
            <div class="tip-item" data-aos="fade-up" data-aos-delay="300">
                <div class="tip-content">
                    <span class="tip-number">06</span>
                    <h5>Annual Professional Inspection</h5>
                    <p>Schedule yearly inspections to check connections, inverter performance, and ensure warranty compliance.</p>
                </div>
            </div>
        </div>
    </div>
</section>


    <!-- 6 Reasons Section -->
    <section class="solar-reasons-section">
        <div class="solar-reasons-container">    
            <!-- LEFT SIDE - ILLUSTRATION -->
            <div class="reasons-illustration" data-aos="fade-right">
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
            <div class="reasons-accordion" data-aos="fade-left">
                    
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
            <div class="col-lg-5 mb-4 mb-lg-0" data-aos="fade-right">
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
                                <span>8:00 AM - 5:00 PM</span>
                            </div>
                            <div class="hour-item">
                                <span>Tuesday</span>
                                <span>8:00 AM - 5:00 PM</span>
                            </div>
                            <div class="hour-item">
                                <span>Wednesday</span>
                                <span>8:00 AM - 5:00 PM</span>
                            </div>
                            <div class="hour-item">
                                <span>Thursday</span>
                                <span>8:00 AM - 5:00 PM</span>
                            </div>
                            <div class="hour-item">
                                <span>Friday</span>
                                <span>8:00 AM - 5:00 PM</span>
                            </div>
                            <div class="hour-item">
                                <span>Saturday</span>
                                <span>8:00 AM - 5:00 PM</span>
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
            <div class="col-lg-7" data-aos="fade-left">
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
                <div class="col-lg-8" data-aos="zoom-in">
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


    <!-- ==========================================
         Floating Chatbot Widget
         ========================================== -->
    <style>
    /* Toggle FAB */
    .chat-fab {
        position: fixed;
        bottom: 90px;
        right: 24px;
        z-index: 9999;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2d5016, #3d6b1f);
        color: #fff;
        border: none;
        font-size: 26px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 6px 24px rgba(45,80,22,.45);
        transition: transform .3s, box-shadow .3s;
    }
    .chat-fab:hover {
        transform: scale(1.1);
        box-shadow: 0 8px 32px rgba(45,80,22,.55);
    }
    .chat-fab .fab-close { display: none; }
    .chat-fab.open .fab-open  { display: none; }
    .chat-fab.open .fab-close { display: inline; }

    /* Widget Panel */
    .chatbot-widget {
        position: fixed;
        bottom: 164px;
        right: 24px;
        z-index: 9998;
        width: 400px;
        max-width: calc(100vw - 24px);
        height: 580px;
        max-height: calc(100vh - 180px);
        display: flex;
        flex-direction: column;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 12px 48px rgba(0,0,0,.18);
        background: #fff;
        opacity: 0;
        transform: translateY(20px) scale(.95);
        pointer-events: none;
        transition: opacity .3s ease, transform .3s ease;
    }
    .chatbot-widget.open {
        opacity: 1;
        transform: translateY(0) scale(1);
        pointer-events: auto;
    }

    /* Chat Header */
    .chat-header {
        background: linear-gradient(135deg, #2d5016, #3d6b1f);
        color: #fff;
        padding: 16px 18px;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
    }
    .chat-header-avatar {
        width: 42px; height: 42px;
        border-radius: 50%;
        background: rgba(255,255,255,.18);
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; flex-shrink: 0;
    }
    .chat-header-info h3 {
        margin: 0; font-size: .97rem; font-weight: 700; color: #fff;
    }
    .chat-header-info span {
        font-size: .76rem; opacity: .85;
        display: flex; align-items: center; gap: 5px;
    }
    .chat-header-info span::before {
        content: ''; width: 8px; height: 8px;
        background: #4ade80; border-radius: 50%; display: inline-block;
    }
    .chat-header-actions {
        margin-left: auto; display: flex; gap: 6px;
    }
    .chat-header-actions button {
        background: rgba(255,255,255,.15); border: none; color: #fff;
        width: 30px; height: 30px; border-radius: 50%;
        cursor: pointer; font-size: 13px; transition: background .2s;
        display: flex; align-items: center; justify-content: center;
    }
    .chat-header-actions button:hover { background: rgba(255,255,255,.3); }

    /* Chat Body */
    .chat-body {
        flex: 1 1 0;
        overflow-y: auto;
        padding: 18px 16px;
        display: flex;
        flex-direction: column;
        gap: 14px;
        background: #f7f8fa;
        scroll-behavior: smooth;
        min-height: 0;
    }
    .chat-body::-webkit-scrollbar { width: 5px; }
    .chat-body::-webkit-scrollbar-track { background: transparent; }
    .chat-body::-webkit-scrollbar-thumb { background: #ccc; border-radius: 3px; }

    /* Message Bubbles */
    .msg-row {
        display: flex; gap: 8px; max-width: 90%;
        animation: msgSlideIn .35s ease-out;
    }
    @keyframes msgSlideIn {
        from { opacity: 0; transform: translateY(10px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .msg-row.bot  { align-self: flex-start; }
    .msg-row.user { align-self: flex-end; flex-direction: row-reverse; }
    .msg-avatar {
        width: 30px; height: 30px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; flex-shrink: 0; margin-top: 2px;
    }
    .msg-row.bot .msg-avatar  { background: #e8f5e9; color: #2d5016; }
    .msg-row.user .msg-avatar { background: #e3f2fd; color: #1565c0; }
    .msg-bubble {
        padding: 12px 15px; border-radius: 16px;
        font-size: .86rem; line-height: 1.6; word-break: break-word;
    }
    .msg-row.bot .msg-bubble {
        background: #fff; color: #333; border: 1px solid #e9ecef;
        border-top-left-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,.04);
    }
    .msg-row.user .msg-bubble {
        background: linear-gradient(135deg, #2d5016, #3d6b1f);
        color: #fff; border-top-right-radius: 4px;
    }
    .msg-bubble .msg-time { display: block; font-size: .68rem; margin-top: 6px; opacity: .5; }
    .msg-row.user .msg-bubble .msg-time { text-align: right; }
    .msg-bubble strong { color: #2d5016; }
    .msg-row.user .msg-bubble strong { color: #ffc107; }
    .msg-bubble ul { margin: 6px 0 6px 16px; padding: 0; }
    .msg-bubble ul li { margin-bottom: 4px; line-height: 1.55; }
    .msg-bubble .highlight {
        background: #fff3cd; padding: 1px 5px; border-radius: 4px;
        font-weight: 600; color: #2d5016;
    }

    /* Typing Indicator */
    .typing-indicator { display: flex; gap: 8px; align-self: flex-start; max-width: 90%; }
    .typing-dots {
        background: #fff; border: 1px solid #e9ecef;
        border-radius: 16px; border-top-left-radius: 4px;
        padding: 12px 18px; display: flex; align-items: center; gap: 4px;
    }
    .typing-dots span {
        width: 7px; height: 7px; background: #aaa; border-radius: 50%;
        animation: typingBounce 1.4s ease-in-out infinite;
    }
    .typing-dots span:nth-child(2) { animation-delay: .2s; }
    .typing-dots span:nth-child(3) { animation-delay: .4s; }
    @keyframes typingBounce {
        0%, 60%, 100% { transform: translateY(0); opacity: .4; }
        30% { transform: translateY(-5px); opacity: 1; }
    }

    /* Inline Topic Buttons */
    .inline-topics { display: flex; flex-direction: column; gap: 6px; margin-top: 4px; width: 100%; }
    .inline-topic-btn {
        display: flex; align-items: center; gap: 10px;
        background: #fff; border: 1.5px solid #e5e7eb; border-radius: 12px;
        padding: 10px 13px; cursor: pointer; font-size: .82rem;
        font-weight: 500; color: #333; transition: all .2s;
        text-align: left; width: 100%;
    }
    .inline-topic-btn:hover {
        border-color: #2d5016; background: #f0fdf4; transform: translateX(3px);
    }
    .inline-topic-btn .qa-icon {
        width: 30px; height: 30px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; flex-shrink: 0;
    }
    .msg-row.bot.topics-row { max-width: 95%; }
    .msg-row.bot.topics-row .msg-bubble { padding: 14px 14px 10px; }

    /* CTA Footer */
    .chat-cta { padding: 12px 14px; background: #fff; border-top: 1px solid #eee; flex-shrink: 0; }
    .chat-talk-btn {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        width: 100%; padding: 12px;
        background: linear-gradient(135deg, #2d5016, #3d6b1f);
        color: #fff; border: none; border-radius: 50px;
        font-size: .92rem; font-weight: 700; cursor: pointer;
        transition: all .2s; text-decoration: none;
    }
    .chat-talk-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(220,38,38,.35); color: #fff;
    }
    .chat-powered { text-align: center; font-size: .7rem; color: #aaa; margin-top: 8px; }

    /* Chatbot Responsive */
    @media (max-width: 768px) {
        .chat-fab { bottom: 80px; right: 18px; width: 54px; height: 54px; font-size: 22px; }
        .chatbot-widget {
            bottom: 144px; right: 12px; left: 12px;
            width: auto; max-width: none;
            max-height: calc(100vh - 160px); height: 520px;
        }
    }
    @media (max-width: 480px) {
        .chatbot-widget {
            bottom: 140px; right: 8px; left: 8px;
            border-radius: 14px; height: 480px;
        }
        .chat-body { padding: 14px 12px; }
        .msg-bubble { padding: 10px 12px; font-size: .82rem; }
        .inline-topic-btn { padding: 9px 11px; font-size: .8rem; }
    }
    </style>

    <!-- Toggle Button -->
    <button class="chat-fab" id="chatFab" onclick="toggleChat()">
        <i class="fas fa-comment-dots fab-open"></i>
        <i class="fas fa-times fab-close"></i>
    </button>

    <!-- Chat Widget Panel -->
    <div class="chatbot-widget" id="chatWidget">
        <div class="chat-header">
            <div class="chat-header-avatar"><i class="fas fa-solar-panel"></i></div>
            <div class="chat-header-info">
                <h3>SolarPower Energy Support</h3>
                <span>Online &bull; Replies instantly</span>
            </div>
            <div class="chat-header-actions">
                <button onclick="resetChat()" title="Reset Chat"><i class="fas fa-redo-alt"></i></button>
                <button onclick="toggleChat()" title="Close"><i class="fas fa-times"></i></button>
            </div>
        </div>
        <div class="chat-body" id="chatBody"></div>
        <div class="chat-cta">
            <a href="contact.php" class="chat-talk-btn">
                <i class="fas fa-phone-alt"></i> Talk to an Agent
            </a>
            <div class="chat-powered">Powered by SolarPower Energy Corporation</div>
        </div>
    </div>

    <script>
    // Chatbot Data & Logic
    const faqData = [
        { id:'cost', question:'How much does solar cost?', icon:'fa-coins', iconBg:'#fef3c7', iconColor:'#f59e0b',
          answer:`Solar installation costs vary based on your system size and energy needs. Here's a typical breakdown for the Philippines:<br><br><ul><li><strong>Small Residential (3-5kW):</strong> ₱150,000 – ₱250,000</li><li><strong>Medium Residential (8-12kW):</strong> ₱400,000 – ₱600,000</li><li><strong>Large Residential/Commercial (20kW+):</strong> Custom pricing based on requirements</li></ul>💳 We offer <span class="highlight">flexible payment plans</span> and can help you access government incentives and financing options to reduce upfront costs. Many of our customers finance their systems and see immediate savings on their monthly bills!` },
        { id:'roi', question:'How long is the ROI (Return on Investment)?', icon:'fa-chart-line', iconBg:'#dbeafe', iconColor:'#3b82f6',
          answer:`Most of our Filipino customers achieve <span class="highlight">full ROI within 4-6 years</span>, depending on several factors:<br><br><ul><li><strong>Current electricity bill:</strong> Higher bills = faster payback</li><li><strong>System size and efficiency:</strong> Quality components maximize production</li><li><strong>Location and sunlight:</strong> Philippines has excellent solar potential!</li><li><strong>Net metering:</strong> Selling excess power speeds up ROI</li><li><strong>Electricity rate increases:</strong> MERALCO rates typically rise 3-5% annually</li></ul>⚡ After payback, you'll enjoy <strong>FREE electricity for 20+ years</strong> since solar panels last 25-30 years with proper maintenance. That's decades of zero or minimal electric bills!` },
        { id:'brownout', question:'What happens during brownouts or power outages?', icon:'fa-bolt', iconBg:'#fce7f3', iconColor:'#ec4899',
          answer:`This is one of the most common questions in the Philippines! The answer depends on your system type:<br><br><ul><li><strong>Grid-Tied System:</strong> Automatically shuts off during outages for safety (to protect utility workers). When power returns, your system automatically reconnects.</li><li><strong>Hybrid System (with battery backup):</strong> You'll have <span class="highlight">continuous power during brownouts!</span> Your batteries keep essential appliances running — perfect for frequent Philippine outages.</li><li><strong>Off-Grid System:</strong> Complete independence from the grid with 24/7 backup power from your battery bank.</li></ul>🔋 <strong>Our recommendation:</strong> Hybrid systems are ideal for the Philippines due to frequent brownouts. Solar charges batteries during the day, and stored power covers outages and nighttime!` },
        { id:'netmeter', question:'Do you assist with net-metering applications?', icon:'fa-file-alt', iconBg:'#ede9fe', iconColor:'#8b5cf6',
          answer:`<strong>Yes, absolutely!</strong> We handle the entire net-metering process from start to finish:<br><br><ul><li>✅ <strong>Document preparation:</strong> We compile all required forms and technical specs</li><li>✅ <strong>Submission to utility:</strong> Coordination with MERALCO or your local distribution utility</li><li>✅ <strong>Bi-directional meter installation:</strong> We arrange the meter replacement</li><li>✅ <strong>Follow-up and approval:</strong> We track your application until approved</li><li>✅ <strong>Final inspection:</strong> We coordinate with ERC and utility inspectors</li></ul>💡 With net-metering, <span class="highlight">excess energy goes back to the grid</span> and you get credits that reduce your bill even further. The process typically takes 2-4 months, and we handle all the paperwork!` },
        { id:'maintenance', question:'Is there maintenance required for solar panels?', icon:'fa-tools', iconBg:'#d1fae5', iconColor:'#10b981',
          answer:`Great news — solar panels require <span class="highlight">very minimal maintenance!</span> No moving parts. Here's what's recommended:<br><br><ul><li><strong>Panel cleaning:</strong> 2-4 times per year to remove dust, leaves, and bird droppings</li><li><strong>Visual inspection:</strong> Check for physical damage or debris after typhoons</li><li><strong>Electrical inspection:</strong> Annual checkup of inverter, wiring, and connections</li><li><strong>Performance monitoring:</strong> Track daily production through our mobile app (we'll alert you to any issues)</li></ul>🛠️ <strong>We offer maintenance packages:</strong> quarterly cleaning & inspection, priority response, performance optimization, and warranty extensions. Systems run at peak efficiency for 25-30 years!` }
    ];

    const chatBody   = document.getElementById('chatBody');
    const chatFab    = document.getElementById('chatFab');
    const chatWidget = document.getElementById('chatWidget');
    let chatInited = false;

    function toggleChat() {
        const isOpen = chatWidget.classList.toggle('open');
        chatFab.classList.toggle('open', isOpen);
        if (isOpen && !chatInited) { chatInited = true; initChat(); }
    }
    function getTimeStr() {
        return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
    function scrollToBottom() {
        requestAnimationFrame(() => { chatBody.scrollTop = chatBody.scrollHeight; });
    }
    function addBotMessage(html, showTime = true) {
        const row = document.createElement('div');
        row.className = 'msg-row bot';
        row.innerHTML = `<div class="msg-avatar"><i class="fas fa-solar-panel"></i></div><div class="msg-bubble">${html}${showTime ? '<span class="msg-time">' + getTimeStr() + '</span>' : ''}</div>`;
        chatBody.appendChild(row); scrollToBottom();
    }
    function addUserMessage(text) {
        const row = document.createElement('div');
        row.className = 'msg-row user';
        row.innerHTML = `<div class="msg-avatar"><i class="fas fa-user"></i></div><div class="msg-bubble">${text}<span class="msg-time">${getTimeStr()}</span></div>`;
        chatBody.appendChild(row); scrollToBottom();
    }
    function showTyping() {
        const el = document.createElement('div');
        el.className = 'typing-indicator'; el.id = 'typingIndicator';
        el.innerHTML = `<div class="msg-avatar" style="background:#e8f5e9;color:#2d5016;"><i class="fas fa-solar-panel"></i></div><div class="typing-dots"><span></span><span></span><span></span></div>`;
        chatBody.appendChild(el); scrollToBottom();
    }
    function hideTyping() {
        const el = document.getElementById('typingIndicator');
        if (el) el.remove();
    }
    function renderMenu() {
        const old = chatBody.querySelector('.msg-row.topics-row');
        if (old) old.remove();
        const row = document.createElement('div');
        row.className = 'msg-row bot topics-row';
        let btnsHtml = '';
        faqData.forEach(faq => {
            btnsHtml += `<button class="inline-topic-btn" data-faq="${faq.id}"><div class="qa-icon" style="background:${faq.iconBg};color:${faq.iconColor};"><i class="fas ${faq.icon}"></i></div>${faq.question}</button>`;
        });
        row.innerHTML = `<div class="msg-avatar"><i class="fas fa-solar-panel"></i></div><div class="msg-bubble"><div class="inline-topics">${btnsHtml}</div></div>`;
        chatBody.appendChild(row);
        row.querySelectorAll('.inline-topic-btn').forEach(btn => {
            btn.addEventListener('click', () => handleQuestion(btn.dataset.faq));
        });
        scrollToBottom();
    }
    let isBusy = false;
    function handleQuestion(id) {
        if (isBusy) return;
        const faq = faqData.find(f => f.id === id);
        if (!faq) return;
        isBusy = true;
        const topicsRow = chatBody.querySelector('.msg-row.topics-row');
        if (topicsRow) topicsRow.remove();
        addUserMessage(faq.question);
        showTyping();
        const delay = Math.min(900 + faq.answer.length * 1, 2200);
        setTimeout(() => {
            hideTyping(); addBotMessage(faq.answer);
            setTimeout(() => {
                addBotMessage(`Would you like to know about something else? Choose a topic below. 👇`, false);
                renderMenu(); isBusy = false;
            }, 500);
        }, delay);
    }
    function resetChat() { chatBody.innerHTML = ''; initChat(); }
    function initChat() {
        showTyping();
        setTimeout(() => {
            hideTyping();
            addBotMessage(`Hi! 👋 Welcome to <strong>SolarPower Energy</strong>. I'm here to answer your questions. Choose a topic below!`);
            setTimeout(() => { renderMenu(); }, 300);
        }, 1200);
    }
    </script>

    <!-- Floating Messenger Button -->
        <a href="https://m.me/757917280729034"
           class="messenger-float"
           target="_blank"
           aria-label="Chat with us on Messenger">
            <i class="fab fa-facebook-messenger"></i>
        </a>

        <div class="modal fade" id="inspectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 overflow-hidden">
                
            <!-- Close Button -->
            <button type="button" class="btn-close position-absolute end-0 m-3" data-bs-dismiss="modal" style="z-index: 1060;"></button>
                
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
                        <p class="text-muted small">We'll contact you within 24 hours.</p>
                    </div>

                    <!-- Hybrid Form: Tries PHP first, falls back to FormSubmit -->
                    <form id="inspectionForm" class="inspection-form">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-uppercase">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-user text-muted"></i>
                                    </span>
                                    <input type="text" 
                                           name="fullname" 
                                           class="form-control bg-light border-start-0" 
                                           placeholder="Juan Dela Cruz" 
                                           required>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-uppercase">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-envelope text-muted"></i>
                                    </span>
                                    <input type="email" 
                                           name="email" 
                                           class="form-control bg-light border-start-0" 
                                           placeholder="juan@email.com" 
                                           required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-uppercase">Contact Number</label>
                                <input type="tel" 
                                       name="phone" 
                                       class="form-control bg-light" 
                                       placeholder="0917-000-0000" 
                                       required>
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
                                <textarea name="address" 
                                          class="form-control bg-light" 
                                          rows="2" 
                                          placeholder="House No., Street, Brgy, City" 
                                          required></textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-uppercase">Inspection Date</label>
                                <input type="date" 
                                       name="inspection_date" 
                                       class="form-control bg-light" 
                                       required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-uppercase">Monthly Bill (₱)</label>
                                <input type="number" 
                                       name="bill" 
                                       class="form-control bg-light" 
                                       placeholder="e.g. 5000" 
                                       required>
                            </div>

                            <div class="col-12 mb-4">
                                <label class="form-label fw-semibold small text-uppercase">Additional Notes (Optional)</label>
                                <textarea name="notes" 
                                          class="form-control bg-light" 
                                          rows="3" 
                                          placeholder="Tell us about your roof type or any specific concerns..."></textarea>
                            </div>
                        </div>

                        <button type="submit" 
                                class="btn w-100 py-3 fw-bold text-uppercase shadow-sm" 
                                id="inspectionBtn" 
                                style="background: #f39c12; color: white; border: none;">
                            <span class="btn-text">Confirm My Schedule</span>
                            <span class="spinner-border spinner-border-sm d-none"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="inspectionSuccessModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle text-success me-2"></i>
                    Request Submitted!
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-solar-panel text-warning" style="font-size: 64px;"></i>
                </div>
                <h4 class="mb-3">Thank You!</h4>
                <p class="mb-0">
                    Your inspection request has been received.<br>
                    <strong>Our team will contact you within 24 hours.</strong>
                </p>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

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

// VIDEO POPUP FUNCTIONALITY
// ============================================

// Show video popup after page loads
window.addEventListener('load', function() {
    // Show popup immediately on every page load (no session tracking)
    setTimeout(function() {
        showVideoPopup();
    }, 100); // 100ms for instant display
});

// Function to show video popup
function showVideoPopup() {
    const popup = document.getElementById('videoPopup');
    const video = document.getElementById('promoVideo');
    
    popup.classList.add('active');
    document.body.style.overflow = 'hidden'; // Prevent scrolling
    
    // Play video
    video.play();
}

// Function to close video popup
function closeVideoPopup() {
    const popup = document.getElementById('videoPopup');
    const video = document.getElementById('promoVideo');
    
    popup.classList.remove('active');
    document.body.style.overflow = ''; // Restore scrolling
    
    // Pause video
    video.pause();
}

// Function to handle video click - close popup and go to catalog
function handleVideoClick(event) {
    // Prevent the click from bubbling to overlay
    event.stopPropagation();
    
    // Close the popup
    closeVideoPopup();
    
    // Small delay then scroll to catalog
    setTimeout(function() {
        const catalogSection = document.getElementById('catalogSection');
        if (catalogSection) {
            catalogSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }, 300);
}

// Close button click event
document.getElementById('closeVideoPopup').addEventListener('click', closeVideoPopup);

// Close popup when clicking outside the video
document.getElementById('videoPopup').addEventListener('click', function(e) {
    if (e.target === this) {
        closeVideoPopup();
    }
});

// Close popup with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeVideoPopup();
    }
});


document.getElementById('promoVideo').addEventListener('ended', function() {
    setTimeout(closeVideoPopup, 1000); // Close 1 second after video ends
});

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
            brandName: product.brandName || '',
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

    let cartSubtotal = 0;
    let html = '';

    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        cartSubtotal += itemTotal;
        
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
    
    // Calculate fees
    const deliveryFee = calculateDeliveryFee();
    const installationFee = hasGridTieOrHybridProduct() ? 2000 : 0;
    const grandTotal = cartSubtotal + deliveryFee + installationFee;
    
    const formattedSubtotal = "₱" + cartSubtotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const formattedTotal = "₱" + grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    
    if (subtotalDisplay) subtotalDisplay.innerText = formattedSubtotal;
    if (totalDisplay) totalDisplay.innerText = formattedTotal;
    
    window.currentTotalAmount = grandTotal;
    
    // Update payment display with new total
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
// 5. INSTAPAY PAYMENT INTEGRATION
// ============================================

// Delivery fee calculation based on location
function calculateDeliveryFee() {
    const address = document.getElementById('cust_address')?.value.toLowerCase() || '';
    
    // Metro Manila / Nearby Areas (1-30km)
    if (address.includes('manila') || address.includes('quezon') || address.includes('caloocan') || 
        address.includes('pasig') || address.includes('makati') || address.includes('taguig') || 
        address.includes('pasay') || address.includes('parañaque') || address.includes('muntinlupa') || 
        address.includes('las piñas') || address.includes('valenzuela') || address.includes('malabon') || 
        address.includes('navotas') || address.includes('marikina') || address.includes('san juan') || 
        address.includes('mandaluyong') || address.includes('pateros')) {
        
        // For now, return mid-range since we can't determine exact distance
        return 2500; // 6-10km default for Metro Manila
    }
    
    // South Luzon
    if (address.includes('cavite')) return 4200;
    if (address.includes('laguna')) return 6000;
    if (address.includes('batangas')) return 8500;
    if (address.includes('rizal')) return 7000;
    
    // North Luzon
    if (address.includes('bulacan')) return 7000;
    if (address.includes('pampanga')) return 10000;
    if (address.includes('tarlac')) return 10000;
    
    // Visayas & Mindanao - varies
    if (address.includes('cebu') || address.includes('davao') || address.includes('iloilo') || 
        address.includes('bacolod') || address.includes('cagayan de oro') || address.includes('zamboanga')) {
        return 0; // Will vary, show "Contact us"
    }
    
    // Default - nearby areas
    return 2000; // 1-5km default
}

// Check if cart contains Grid-tie or Hybrid products
function hasGridTieOrHybridProduct() {
    if (!cart || cart.length === 0) return false;
    
    // We need to check the actual product data
    // For now, we'll check if any product has these keywords in the name
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
    
    // Calculate delivery fee
    const deliveryFee = calculateDeliveryFee();
    const deliveryFeeDisplay = document.getElementById('deliveryFeeDisplay');
    if (deliveryFeeDisplay) {
        if (deliveryFee === 0) {
            deliveryFeeDisplay.innerHTML = '<span class="text-info">Contact us</span>';
        } else {
            deliveryFeeDisplay.textContent = '₱' + deliveryFee.toLocaleString(undefined, {minimumFractionDigits: 2});
        }
    }
    
    // Calculate installation fee (only for Grid-tie or Hybrid)
    const installationFee = hasGridTieOrHybridProduct() ? 2000 : 0;
    const installationFeeDisplay = document.getElementById('installationFeeDisplay');
    if (installationFeeDisplay) {
        if (installationFee === 0) {
            installationFeeDisplay.innerHTML = '<span class="text-success">FREE</span>';
        } else {
            installationFeeDisplay.textContent = '₱' + installationFee.toLocaleString(undefined, {minimumFractionDigits: 2});
        }
    }
    
    // Calculate base total from cart
    let cartTotal = 0;
    if (cart && cart.length > 0) {
        cartTotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    }
    
    // Add installation and delivery fees to total
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
    
    // Update checkout total display
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

function confirmInstapayOrder() {
    console.log('💵 Confirming InstaPay order...');
    
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
    
    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked')?.value || 'full';
    let paymentPercentage = '100%';
    if (paymentMethod === 'downpayment') paymentPercentage = '50%';
    if (paymentMethod === 'initial') paymentPercentage = '20%';
    
    const confirmed = confirm(
        `Please confirm:\n\n` +
        `1. You have sent the ${paymentPercentage} payment via InstaPay\n` +
        `2. You have sent the payment screenshot to jeca.wdc@gmail.com\n` +
        `3. You understand this payment is non-refundable\n\n` +
        `Click OK to confirm your order.`
    );
    
    if (!confirmed) return;
    
    const confirmBtn = document.getElementById('confirmPaymentBtn');
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    
    const totalAmount = window.currentTotalAmount || 0;
    let amountPaid = totalAmount;
    if (paymentMethod === 'downpayment') amountPaid = totalAmount * 0.5;
    if (paymentMethod === 'initial') amountPaid = totalAmount * 0.2;
    
    const orderData = {
        customerName: custName,
        customerEmail: custEmail,
        customerPhone: custPhone,
        customerAddress: custAddress,
        paymentType: paymentMethod,
        paymentMethod: 'instapay',
        amountPaid: amountPaid,
        totalAmount: totalAmount,
        items: getCartItems(),
        deliveryFee: calculateDeliveryFee(),
        installationFee: hasGridTieOrHybridProduct() ? 2000 : 0
    };
    
    // In a real implementation, this would send to your backend
    // For now, we'll simulate success
    setTimeout(() => {
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = '<i class="fas fa-check-circle me-2"></i>I Have Sent Payment';
        
        const orderRef = 'ORD-INSTAPAY-' + Date.now();
        displayOrderConfirmation(orderRef);
        clearCart();
        showNotificationModal('success', 'Order received! We will verify your payment and contact you soon.');
    }, 1500);
}

// Old Maya payment function - now replaced by InstaPay
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

        function expandCalculator() {
            const calculatorBox = document.getElementById('calculatorBox');
            const bulbIcon = document.querySelector('.savings-icon');
            
            if (calculatorBox) {
                calculatorBox.classList.remove('collapsed');
                calculatorBox.classList.add('expanded');
            }
            
            // Add active class to trigger glow and wiggle animation
            if (bulbIcon) {
                bulbIcon.classList.add('active');
            }
        }

        function shrinkCalculatorIfEmpty() {
            const billInput = document.getElementById('billAmount');
            const calculatorBox = document.getElementById('calculatorBox');
            const results = document.getElementById('results');
            const bulbIcon = document.querySelector('.savings-icon');
            
            if (calculatorBox && billInput && !billInput.value && !results.classList.contains('show')) {
                setTimeout(() => {
                    calculatorBox.classList.remove('expanded');
                    calculatorBox.classList.add('collapsed');
                    
                    // Remove active class when input loses focus and is empty
                    if (bulbIcon) {
                        bulbIcon.classList.remove('active');
                    }
                }, 200);
            }
        }

        function calculateSavings() {
            const billAmount = parseFloat(document.getElementById('billAmount').value);
            const errorMessage = document.getElementById('errorMessage');
            const results = document.getElementById('results');
            const calculatorBox = document.getElementById('calculatorBox');
            
            if (!billAmount || billAmount <= 0) {
                errorMessage.textContent = 'Please enter a valid electric bill amount';
                results.classList.remove('show');
                return;
            }
            
            errorMessage.textContent = '';
            
            if (calculatorBox) {
                calculatorBox.classList.remove('collapsed');
                calculatorBox.classList.add('expanded');
            }
            
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
            
            setTimeout(() => {
                document.getElementById('kwpValue').textContent = requiredKwp.toFixed(1);
                document.getElementById('panelsValue').textContent = numberOfPanels;
                document.getElementById('monthlySavings').textContent = '₱' + monthlySavings.toLocaleString('en-PH', {maximumFractionDigits: 0});
                document.getElementById('yearlySavings').textContent = '₱' + yearlySavings.toLocaleString('en-PH', {maximumFractionDigits: 0});
                
                results.classList.add('show');
            }, 100);
        }

        document.addEventListener('DOMContentLoaded', function() {
            setupCalculator();
            
            const calculatorBox = document.getElementById('calculatorBox');
            if (calculatorBox) {
                calculatorBox.classList.add('collapsed');
            }
            
            // Add click handler for bulb icon with wiggle animation
            const bulbIcon = document.querySelector('.savings-icon');
            if (bulbIcon) {
                bulbIcon.addEventListener('click', function() {
                    // Trigger wiggle animation
                    this.style.animation = 'none';
                    setTimeout(() => {
                        this.style.animation = '';
                    }, 10);
                    
                    // Expand calculator if collapsed
                    const billInput = document.getElementById('billAmount');
                    if (calculatorBox && calculatorBox.classList.contains('collapsed')) {
                        expandCalculator();
                        if (billInput) {
                            setTimeout(() => billInput.focus(), 300);
                        }
                    }
                });
            }
        });
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

        function expandCalculator() {
            const calculatorBox = document.getElementById('calculatorBox');
            const bulbIcon = document.querySelector('.savings-icon');
            
            if (calculatorBox) {
                calculatorBox.classList.remove('collapsed');
                calculatorBox.classList.add('expanded');
            }
            
            // Add active class to trigger glow and wiggle animation
            if (bulbIcon) {
                bulbIcon.classList.add('active');
            }
        }

        function shrinkCalculatorIfEmpty() {
            const billInput = document.getElementById('billAmount');
            const calculatorBox = document.getElementById('calculatorBox');
            const results = document.getElementById('results');
            const bulbIcon = document.querySelector('.savings-icon');
            
            if (calculatorBox && billInput && !billInput.value && !results.classList.contains('show')) {
                setTimeout(() => {
                    calculatorBox.classList.remove('expanded');
                    calculatorBox.classList.add('collapsed');
                    
                    // Remove active class when input loses focus and is empty
                    if (bulbIcon) {
                        bulbIcon.classList.remove('active');
                    }
                }, 200);
            }
        }

        function calculateSavings() {
            const billAmount = parseFloat(document.getElementById('billAmount').value);
            const errorMessage = document.getElementById('errorMessage');
            const results = document.getElementById('results');
            const calculatorBox = document.getElementById('calculatorBox');
            
            if (!billAmount || billAmount <= 0) {
                errorMessage.textContent = 'Please enter a valid electric bill amount';
                results.classList.remove('show');
                return;
            }
            
            errorMessage.textContent = '';
            
            if (calculatorBox) {
                calculatorBox.classList.remove('collapsed');
                calculatorBox.classList.add('expanded');
            }
            
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
            
            setTimeout(() => {
                document.getElementById('kwpValue').textContent = requiredKwp.toFixed(1);
                document.getElementById('panelsValue').textContent = numberOfPanels;
                document.getElementById('monthlySavings').textContent = '₱' + monthlySavings.toLocaleString('en-PH', {maximumFractionDigits: 0});
                document.getElementById('yearlySavings').textContent = '₱' + yearlySavings.toLocaleString('en-PH', {maximumFractionDigits: 0});
                
                results.classList.add('show');
            }, 100);
        }

        document.addEventListener('DOMContentLoaded', function() {
            setupCalculator();
            
            const calculatorBox = document.getElementById('calculatorBox');
            if (calculatorBox) {
                calculatorBox.classList.add('collapsed');
            }
        });
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
    
    if (!inspectionForm) return;
    
    inspectionForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('inspectionBtn');
        const btnText = submitBtn.querySelector('.btn-text');
        const spinner = submitBtn.querySelector('.spinner-border');
        
        // Show loading state
        btnText.classList.add('d-none');
        spinner.classList.remove('d-none');
        submitBtn.disabled = true;
        
        try {
            const formData = new FormData(inspectionForm);
            
            // STEP 1: Try PHP handler first (beautiful custom email)
            console.log('📧 Attempting to send via PHP...');
            
            const phpResponse = await fetch('send-inspection-email.php', {
                method: 'POST',
                body: formData
            });
            
            const phpResult = await phpResponse.json();
            
            if (phpResult.success) {
                // ✅ SUCCESS - PHP worked!
                console.log('✅ Email sent successfully via PHP');
                showSuccessAndReset();
                return;
            } else {
                throw new Error('PHP handler failed');
            }
            
        } catch (phpError) {
            // STEP 2: PHP failed, try FormSubmit backup
            console.warn('⚠️ PHP failed, trying FormSubmit backup...', phpError);
            
            try {
                const formData = new FormData(inspectionForm);
                
                // Add FormSubmit config
                formData.append('_subject', '🌞 New Solar Inspection Request');
                formData.append('_captcha', 'false');
                formData.append('_template', 'box');
                
                const formsubmitResponse = await fetch('https://formsubmit.co/solar@solarpower.com.ph', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                
                if (formsubmitResponse.ok) {
                    // ✅ SUCCESS - FormSubmit worked!
                    console.log('✅ Email sent successfully via FormSubmit');
                    showSuccessAndReset();
                } else {
                    throw new Error('FormSubmit also failed');
                }
                
            } catch (formsubmitError) {
                // Both methods failed
                console.error('❌ Both PHP and FormSubmit failed', formsubmitError);
                
                // Reset button
                btnText.classList.remove('d-none');
                spinner.classList.add('d-none');
                submitBtn.disabled = false;
                
                alert('There was an error submitting your request. Please try again or contact us directly at solar@solarpower.com.ph');
            }
        }
    });
}

function showSuccessAndReset() {
    const inspectionForm = document.getElementById('inspectionForm');
    const submitBtn = document.getElementById('inspectionBtn');
    const btnText = submitBtn.querySelector('.btn-text');
    const spinner = submitBtn.querySelector('.spinner-border');
    
    // Reset button
    btnText.classList.remove('d-none');
    spinner.classList.add('d-none');
    submitBtn.disabled = false;
    
    // Reset form
    inspectionForm.reset();
    
    // Close inspection modal
    const inspectionModal = bootstrap.Modal.getInstance(document.getElementById('inspectionModal'));
    if (inspectionModal) {
        inspectionModal.hide();
    }
    
    // Show success modal
    const successModal = new bootstrap.Modal(document.getElementById('inspectionSuccessModal'));
    successModal.show();
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeInspectionForm();
});

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
// 13. RENT TO OWN FORM
// ============================================
function initializeRentToOwnForm() {
    const rtoForm = document.getElementById('rentToOwnForm');
    
    if (!rtoForm) return;
    
    rtoForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = rtoForm.querySelector('.btn-submit-rto');
        const btnText = submitBtn.querySelector('.btn-text');
        const spinner = submitBtn.querySelector('.spinner-border');
        
        // Show loading state
        btnText.classList.add('d-none');
        spinner.classList.remove('d-none');
        submitBtn.disabled = true;
        
        try {
            const formData = new FormData(rtoForm);
            
            // Add FormSubmit config
            formData.append('_subject', '🏭 New Rent-to-Own Application (Industrial/Commercial)');
            formData.append('_captcha', 'false');
            formData.append('_template', 'box');
            
            const response = await fetch('https://formsubmit.co/solar@solarpower.com.ph', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            if (response.ok) {
                // Success
                showNotificationModal('success', 'Application submitted successfully! We will contact you soon.');
                rtoForm.reset();
            } else {
                throw new Error('Submission failed');
            }
            
        } catch (error) {
            console.error('Error submitting form:', error);
            showNotificationModal('error', 'There was an error submitting your application. Please try again or contact us directly.');
        } finally {
            // Reset button state
            btnText.classList.remove('d-none');
            spinner.classList.add('d-none');
            submitBtn.disabled = false;
        }
    });
}

// Initialize Rent to Own form when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeRentToOwnForm();
});

// ========== MOBILE-OPTIMIZED VIEW MORE FUNCTIONALITY ==========

/**
 * Enhanced toggle function for View More button
 * Shows first 4 products on load, then toggles all products
 */
function toggleViewMore() {
    const productsGrid = document.getElementById('productsGrid');
    const viewMoreBtn = document.getElementById('viewMoreBtn');
    const btnIcon = viewMoreBtn.querySelector('i');
    const btnText = viewMoreBtn.childNodes[viewMoreBtn.childNodes.length - 1];
    
    // Toggle the show-all class
    productsGrid.classList.toggle('show-all');
    
    // Update button appearance and text
    if (productsGrid.classList.contains('show-all')) {
        viewMoreBtn.classList.add('expanded');
        btnText.textContent = ' View Less';
        
        // Scroll smoothly to show newly revealed products
        setTimeout(() => {
            const firstHiddenProduct = document.querySelector('.hidden-product');
            if (firstHiddenProduct) {
                firstHiddenProduct.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'nearest' 
                });
            }
        }, 100);
    } else {
        viewMoreBtn.classList.remove('expanded');
        btnText.textContent = ' View More';
        
        // Scroll back to the beginning of the grid
        const catalogSection = document.querySelector('.catalogs-section');
        if (catalogSection) {
            catalogSection.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'start' 
            });
        }
    }
}

/**
 * Initialize the view more functionality on page load
 */
document.addEventListener('DOMContentLoaded', function() {
    const productsGrid = document.getElementById('productsGrid');
    const viewMoreContainer = document.getElementById('viewMoreContainer');
    const allProducts = document.querySelectorAll('.product-card');
    
    // Count visible vs hidden products
    const hiddenProducts = document.querySelectorAll('.hidden-product');
    
    // Hide the "View More" button if there are 4 or fewer products total
    if (allProducts.length <= 4) {
        if (viewMoreContainer) {
            viewMoreContainer.style.display = 'none';
        }
    } else {
        if (viewMoreContainer) {
            viewMoreContainer.style.display = 'block';
        }
    }
    
    // Log for debugging
    console.log(`Total products: ${allProducts.length}`);
    console.log(`Hidden products: ${hiddenProducts.length}`);
});

/**
 * Optional: Add filter functionality that respects the view more state
 */
function filterProducts(category) {
    const productsGrid = document.getElementById('productsGrid');
    const products = document.querySelectorAll('.product-card');
    const viewMoreBtn = document.getElementById('viewMoreBtn');
    const viewMoreContainer = document.getElementById('viewMoreContainer');
    
    let visibleCount = 0;
    
    products.forEach((product, index) => {
        const productCategory = product.getAttribute('data-category');
        
        if (category === 'All' || productCategory === category) {
            product.style.display = 'flex';
            visibleCount++;
            
            // Apply hidden-product class to items after the 4th
            if (visibleCount > 4) {
                product.classList.add('hidden-product');
            } else {
                product.classList.remove('hidden-product');
            }
        } else {
            product.style.display = 'none';
            product.classList.remove('hidden-product');
        }
    });
    
    // Reset the grid to collapsed state
    productsGrid.classList.remove('show-all');
    if (viewMoreBtn) {
        viewMoreBtn.classList.remove('expanded');
        const btnText = viewMoreBtn.childNodes[viewMoreBtn.childNodes.length - 1];
        btnText.textContent = ' View More';
    }
    
    // Show/hide view more button based on filtered results
    if (viewMoreContainer) {
        viewMoreContainer.style.display = visibleCount > 4 ? 'block' : 'none';
    }
}

/**
 * Optional: Smooth fade-in animation for products when they appear
 */
function animateProductReveal() {
    const hiddenProducts = document.querySelectorAll('.products-grid.show-all .hidden-product');
    
    hiddenProducts.forEach((product, index) => {
        setTimeout(() => {
            product.style.animationDelay = `${index * 0.1}s`;
        }, index * 50);
    });
}
</script>

</html>