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
    <meta property="og:image" content="https://solarpower.com.ph/assets/img/new_logo.png" />    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "url": "https://solarpower.com.ph",
      "logo": "https://solarpower.com.ph/assets/img/new_logo.png",
      "name": "SolarPower Energy Corporation",
      "contactPoint": {
        "@type": "ContactPoint",
        "telephone": "+63-995-394-7379",
        "contactType": "customer service"
      }
    }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* ── Contact Us Section (minimal redesign) ── */
        .contact-us {
            padding: 80px 0;
            background: #fff;
        }
 
        .contact-info h2 {
            font-size: 28px;
            color: var(--clr-dark);
            font-weight: 700;
            margin-bottom: 6px;
        }
 
        .contact-section-sub {
            color: var(--clr-text-secondary);
            font-size: 0.92rem;
            margin-bottom: 36px;
            line-height: 1.6;
        }
 
        /* Visit Us / WhatsApp block */
        .visit-us-section {
            margin-bottom: 32px;
        }
 
        .visit-us-section h3 {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #999;
            margin-bottom: 4px;
        }
 
        .visit-us-section p {
            color: var(--clr-text-secondary);
            font-size: 0.88rem;
            line-height: 1.6;
            margin-bottom: 14px;
        }
 
        /* Contact detail rows */
        .company-info { margin-bottom: 24px; }
 
        .contact-detail {
            display: flex;
            align-items: flex-start;
            margin-bottom: 18px;
            gap: 12px;
        }
 
        .contact-detail .icon-wrap {
            width: 36px;
            height: 36px;
            background: #f0f7f4;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
 
        .contact-detail i {
            color: var(--clr-secondary);
            font-size: 14px;
        }
 
        .company-info .phone-number {
            font-weight: 500;
            color: var(--clr-dark);
            cursor: pointer;
        }
 
        /* Hours Section */
        .hours-section { margin-top: 20px; }
 
        .hours-toggle {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            border: 1px solid #e5e7eb;
            padding: 12px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            color: var(--clr-dark);
            transition: var(--transition-fast);
        }
 
        .hours-toggle:hover { background: #f9fafb; }
 
        .hours-toggle strong { color: var(--clr-dark); }
 
        .hours-toggle i {
            transition: transform 0.3s ease;
            color: #aaa;
            font-size: 12px;
        }
 
        .hours-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s ease;
            margin-top: 4px;
            background: #fafafa;
            border-radius: 0 0 8px 8px;
            border: 1px solid #e5e7eb;
            border-top: none;
        }
 
        .hour-item {
            display: flex;
            justify-content: space-between;
            padding: 7px 16px;
            font-size: 13px;
            color: #555;
            border-bottom: 1px solid #f0f0f0;
        }
 
        .hour-item:last-child { border-bottom: none; }
 
        .hour-item span:first-child {
            font-weight: 500;
            color: var(--clr-dark);
        }
 
        .contact-detail strong {
            display: block;
            margin-bottom: 2px;
            color: #999;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
 
        .contact-detail p,
        .contact-detail span,
        .contact-detail a {
            color: var(--clr-dark);
            line-height: 1.6;
            margin: 0;
            font-size: 0.88rem;
        }
 
        .contact-detail a {
            text-decoration: none;
            color: var(--clr-dark);
        }
 
        .contact-detail a:hover {
            color: var(--clr-secondary);
        }
 
        /* Form wrapper — clean, no heavy shadow */
        .contact-form-wrapper {
            padding: 0;
            border: none;
            background: transparent;
        }
 
        .contact-form-wrapper h3 {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--clr-dark);
            margin-bottom: 6px;
        }
 
        .contact-form-sub {
            font-size: 0.88rem;
            color: var(--clr-text-secondary);
            margin-bottom: 28px;
        }
 
        .contact-form .form-control {
            padding: 10px 14px;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.875rem;
            background: #fafafa;
            transition: border-color 0.2s, background 0.2s;
            color: var(--clr-dark);
        }
 
        .contact-form .form-control:focus {
            border-color: var(--clr-secondary);
            background: #fff;
            box-shadow: none;
            outline: none;
        }
 
        .contact-form .input-group-text {
            background: #fafafa;
            border: 1.5px solid #e5e7eb;
            border-right: none;
            border-radius: 8px 0 0 8px;
            color: var(--clr-secondary);
            font-weight: 700;
            font-size: 0.88rem;
            padding: 10px 12px;
        }
 
        .contact-form .input-group .form-control {
            border-left: none;
            border-radius: 0 8px 8px 0;
        }
 
        .contact-form .input-group:focus-within .input-group-text {
            border-color: var(--clr-secondary);
            background: #fff;
        }
 
        .contact-form .input-group:focus-within .form-control {
            border-color: var(--clr-secondary);
        }

        .contact-form .input-group .input-group-text {
            font-size: 0.875rem;
            color: var(--clr-dark);
            font-weight: 500;
            user-select: none;
        }
 
        .contact-form textarea.form-control {
            resize: none;
        }
 
        .btn-submit {
            background: var(--clr-secondary);
            color: #fff;
            padding: 11px 0;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            width: 100%;
            transition: background 0.2s;
            letter-spacing: 0.04em;
        }
 
        .btn-submit:hover { background: #085231; }
 
        /* Social Links */
        .contact-social-links { margin-top: 28px; }
 
        .contact-social-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #999;
            margin-bottom: 10px;
        }
 
        .social-links {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
 
        .social-links a {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #f0f0f0;
            color: var(--clr-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }
 
        .social-links a:hover {
            background: var(--clr-secondary);
            color: #fff;
        }
 
        /* Section divider */
        .contact-divider {
            border: none;
            border-top: 1px solid #f0f0f0;
            margin: 24px 0;
        }
 
        /* Responsive */
        @media (max-width: 768px) {
            .whatsapp-btn { width: 100%; justify-content: center; }
            .contact-us { padding: 48px 0; }
        }
    </style>

<body>


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
                        <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#inspectionModal">Book
                            for Inspection</button>
                    </div>
                </div>
            </div>
        </section>
    </div>

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
                                    <input type="number" id="billAmount" placeholder="0" min="0" step="0.01"
                                        onfocus="expandCalculator()" onblur="shrinkCalculatorIfEmpty()">
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
                            data-brand="<?= htmlspecialchars($p['brandName']) ?>"
                            data-name="<?= htmlspecialchars($p['displayName']) ?>"
                            data-price="<?= htmlspecialchars($p['price']) ?>">

                            <!-- Clickable Product Image and Info -->
                            <div onclick="location.href='product-details.php?id=<?= $p['id'] ?>'" style="cursor: pointer;">
                                <div class="product-image">
                                    <img src="<?= htmlspecialchars($p['image_path'] ?? 'assets/img/placeholder.png') ?>"
                                        alt="<?= htmlspecialchars($p['displayName']) ?>">
                                    <div class="product-badge"> <i class="fas fa-tag"></i>
                                        <?= htmlspecialchars($p['category']) ?></div>
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
                                        <div class="moq-badge"
                                            style="margin-top:6px; display:inline-block; background:#fff3cd; color:#856404; border:1px solid #ffc107; border-radius:6px; padding:3px 10px; font-size:0.78rem; font-weight:600;">
                                            <i class="fas fa-layer-group"></i> Min. Order: <?= intval($p['moq']) ?> pcs
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Action Buttons (not clickable for navigation) -->
                            <div class="product-actions" onclick="event.stopPropagation()">
                                <button class="btn-add-cart"
                                    data-product='<?= json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
                                    onclick="addToCartFromButton(this)" title="Add to Cart">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>

                                <button type="button" class="btn-buy-now"
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
            <div class="bnpl-header">
                <h2>Install Now, <span class="highlight">Pay Later</span></h2>
                <p class="bnpl-subtitle">Switch to Solar now and enjoy massive savings with 30% down payments.</p>
            </div>

            <!-- Steps Grid -->
            <div class="bnpl-steps">
                <!-- Step 1 -->
                <div class="bnpl-step">
                    <div class="step-circle" data-step="1">
                        <div class="step-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                    <h3>Book Ocular Inspection</h3>
                    <p>Schedule your site visit and let our experts assess your property.</p>
                </div>

                <!-- Step 2 -->
                <div class="bnpl-step">
                    <div class="step-circle" data-step="2">
                        <div class="step-icon">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                    </div>
                    <h3>Get Free Quotation</h3>
                    <p>Receive a detailed proposal tailored to your energy needs and budget.</p>
                </div>

                <!-- Step 3 -->
                <div class="bnpl-step">
                    <div class="step-circle" data-step="3">
                        <div class="step-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                    </div>
                    <h3>Installation Process</h3>
                    <p>Our certified team installs your solar system quickly and professionally.</p>
                </div>

                <!-- Step 4 -->
                <div class="bnpl-step">
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
                <button class="btn btn-primary btn-lg" onclick="window.location.href='#inspectionModal'"
                    data-bs-toggle="modal" data-bs-target="#inspectionModal">
                    Get Started Today
                </button>
            </div>
        </div>
    </section>

    <!-- Rent to Own Section (Industrial & Commercial Only) --
    <section class="rent-to-own-section" id="rentToOwnSection">
        <div class="container">
            <div class="rto-wrapper">
                <!-- Left: Form --
                <div class="rto-form-container">
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

                <!-- Right: Image/Visual --
                <div class="rto-visual">
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
    </section>-->



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
                            <input type="text" class="form-control" id="cust_name" placeholder="Juan Dela Cruz"
                                required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" class="form-control" id="cust_email" placeholder="juan@example.com"
                                required>
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
                                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal"
                                        data-bs-target="#deliveryFeeModal">
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
                                <strong>Important Notice:</strong> This is not refundable. If your payment does not
                                match 20%, 50%, or 100% of your order total, your order will be void.
                            </div>

                            <!-- InstaPay QR Code Section -->
                            <div class="text-center mb-4">
                                <h5 class="mb-3">Scan to Pay via InstaPay</h5>
                                <img src="assets/img/UB-QR Code.jpg" alt="InstaPay QR Code" class="img-fluid"
                                    style="max-width: 300px; border: 2px solid #ddd; border-radius: 10px; padding: 10px;">
                            </div>

                            <!-- Payment Options -->
                            <div class="payment-options mb-4">
                                <h6 class="mb-3">Select Payment Percentage:</h6>

                                <!-- Full Payment (100%) -->
                                <div class="form-check payment-option mb-3">
                                    <input class="form-check-input" type="radio" name="paymentMethod" id="paymentFull"
                                        value="full" checked onchange="updatePaymentDisplay()">
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
                                    <input class="form-check-input" type="radio" name="paymentMethod" id="paymentDown"
                                        value="downpayment" onchange="updatePaymentDisplay()">
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
                                <!--currently remove-->
                            </div>

                            <!-- Receipt Upload Section -->
                            <div class="alert alert-light border mt-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-upload text-primary me-3"
                                        style="font-size: 1.5rem; margin-top:2px;"></i>
                                    <div class="w-100">
                                        <strong>Upload Your Transaction Receipt</strong>
                                        <p class="text-muted small mb-2 mt-1">After completing your InstaPay payment,
                                            upload a screenshot or photo of your receipt below. Your order will be
                                            submitted automatically once you click "Confirm & Submit Order".</p>
                                        <ol class="mb-3 mt-1 small">
                                            <li>Complete your InstaPay payment using the QR code above</li>
                                            <li>Take a screenshot or photo of your transaction receipt</li>
                                            <li>Upload the receipt using the button below</li>
                                            <li>Click <strong>"Confirm & Submit Order"</strong> — your order will be
                                                saved automatically</li>
                                        </ol>
                                        <div class="mb-2">
                                            <label for="receiptUpload" class="form-label fw-bold">
                                                <i class="fas fa-file-image me-1 text-primary"></i> Transaction Receipt
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="file" class="form-control" id="receiptUpload"
                                                accept="image/*,.pdf" onchange="previewReceipt(this)">
                                            <div class="form-text">Accepted: JPG, PNG, PDF (Max 5MB)</div>
                                        </div>
                                        <div id="receiptPreviewContainer" style="display:none; margin-top:10px;">
                                            <p class="small fw-bold text-success"><i
                                                    class="fas fa-check-circle me-1"></i> Receipt ready to upload:</p>
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
                        <i class="fas fa-info-circle"></i> You are paying the <strong>Full Amount (100%)</strong> via
                        InstaPay.
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
                        <p class="text-muted">Thank you, <strong><span id="confCustomerName"></span></strong>! Your
                            order and receipt have been submitted. We will verify your payment shortly.</p>

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
                    <div class="brand-item"><img src="assets/img/wechat.png" alt="WeChat"></div>
                    <div class="brand-item"><img src="assets/img/dahai.png" alt="Dahai"></div>
                    <div class="brand-item"><img src="assets/img/nuuko.png" alt="Nuuko"></div>
                    <div class="brand-item"><img src="assets/img/srne.png" alt="SRNE"></div>
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
                    <div class="brand-item"><img src="assets/img/wechat.png" alt="WeChat"></div>
                    <div class="brand-item"><img src="assets/img/dahai.png" alt="Dahai"></div>
                    <div class="brand-item"><img src="assets/img/nuuko.png" alt="Nuuko"></div>
                    <div class="brand-item"><img src="assets/img/srne.png" alt="SRNE"></div>
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
                    <div class="brand-item"><img src="assets/img/wechat.png" alt="WeChat"></div>
                    <div class="brand-item"><img src="assets/img/dahai.png" alt="Dahai"></div>
                    <div class="brand-item"><img src="assets/img/nuuko.png" alt="Nuuko"></div>
                    <div class="brand-item"><img src="assets/img/srne.png" alt="SRNE"></div>
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
                    <div class="brand-item"><img src="assets/img/wechat.png" alt="WeChat"></div>
                    <div class="brand-item"><img src="assets/img/dahai.png" alt="Dahai"></div>
                    <div class="brand-item"><img src="assets/img/nuuko.png" alt="Nuuko"></div>
                    <div class="brand-item"><img src="assets/img/srne.png" alt="SRNE"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section --
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
    </section>-->

    <!-- Solar System Types Section -->
    <section class="solar-tips-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Types of Solar Systems</h2>
                <p class="section-subtitle">Find the right solar setup for your home or business</p>
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
                                    src="https://www.facebook.com/plugins/video.php?href=https://www.facebook.com/61578373983187/videos/1562743611632906/?__so__=watchlist&__rv__=video_home_www_playlist_video_list"
                                    allowfullscreen="true"
                                    allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share">
                                </iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Solar System Types Comparison -->
            <div class="solar-systems-wrapper">

                <!-- 01 Grid-Tied — right to left -->
                <div class="solar-system-row" id="system-gridtied" data-aos="fade-left" data-aos-duration="900">
                    <div class="system-image-col">
                        <div class="system-img-frame">
                            <img src="assets/img/gridtied.png" alt="Grid-Tied Solar System" class="system-img">
                        </div>
                    </div>
                    <div class="system-info-col">
                        <span class="system-badge">01 — Grid-Tied</span>
                        <h3 class="system-title">Grid-Tie Solar System</h3>
                        <p class="system-desc">The simplest and most cost-effective setup. Your panels feed directly
                            into the utility grid, which acts as a virtual battery through net metering.</p>
                        <ul class="system-features">
                            <li>Uses the grid as a virtual battery — no local storage needed</li>
                            <li>Excess power fed back to the grid earns you credits</li>
                            <li>Lowest upfront cost of any solar configuration</li>
                            <li>Fastest return on investment (ROI)</li>
                        </ul>
                        <div class="system-note system-note--warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Shuts down completely during grid blackouts
                        </div>
                    </div>
                </div>

                <!-- 02 Hybrid — left to right -->
                <div class="solar-system-row solar-system-row--reverse" id="system-hybrid" data-aos="fade-right" data-aos-duration="900">
                    <div class="system-image-col">
                        <div class="system-img-frame">
                            <img src="assets/img/hybrid-solar.png" alt="Hybrid Solar System" class="system-img">
                        </div>
                    </div>
                    <div class="system-info-col">
                        <span class="system-badge">02 — Hybrid</span>
                        <h3 class="system-title">Hybrid Solar System</h3>
                        <p class="system-desc">The best of both worlds — grid-connected with battery backup. Panels
                            power your home, charge the battery, and the grid fills any remaining gaps.</p>
                        <ul class="system-features">
                            <li>Grid-tied system with built-in battery backup storage</li>
                            <li>Solar panels power the home and charge batteries simultaneously</li>
                            <li>Grid provides supplemental power when solar falls short</li>
                            <li>Continues working during blackouts via stored energy</li>
                        </ul>
                        <div class="system-note system-note--success">
                            <i class="fas fa-bolt"></i>
                            Works during blackouts using stored battery energy
                        </div>
                    </div>
                </div>

                <!-- 03 Off-Grid — right to left -->
                <div class="solar-system-row" id="system-offgrid" data-aos="fade-left" data-aos-duration="900">
                    <div class="system-image-col">
                        <div class="system-img-frame">
                            <img src="assets/img/offgrid.png" alt="Off-Grid Solar System" class="system-img">
                        </div>
                    </div>
                    <div class="system-info-col">
                        <span class="system-badge">03 — Off-Grid</span>
                        <h3 class="system-title">Off-Grid Solar System</h3>
                        <p class="system-desc">Complete energy independence. Ideal for remote cabins and rural
                            properties where grid connection is unavailable or simply unwanted.</p>
                        <ul class="system-features">
                            <li>Fully self-sufficient — zero grid connection required</li>
                            <li>Must produce 100% of all energy needs from solar</li>
                            <li>Battery bank and backup generator ensure reliability</li>
                            <li>Complete independence from utility providers</li>
                        </ul>
                        <div class="system-note system-note--green">
                            <i class="fas fa-leaf"></i>
                            Completely independent from the utility grid
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>


    <!-- Testimonials Section -->
<section class="py-5" style="background: var(--bs-light, #f8f9fa);">
  <div class="container py-5">
    <div class="text-center mb-5" data-aos="fade-up">
      <h2 class="fw-bold">What Our Clients Say</h2>
      <p class="text-muted">Real experiences from homeowners and businesses who made the switch.</p>
    </div>
    <div class="row g-4 justify-content-center">

      <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
        <div style="background:#fff; border:1px solid rgba(0,0,0,0.08); border-radius:16px; padding:1.5rem; position:relative;">
          <span style="position:absolute; top:12px; right:18px; font-size:48px; line-height:1; color:#ddd; font-family:Georgia,serif;">&ldquo;</span>
          <div class="d-flex align-items-center mb-3">
            <img src="assets/img/user2.jpg" alt="Samantha Esplana" class="rounded-circle me-3"
              style="width:50px;height:50px;object-fit:cover;">
            <div>
              <strong style="font-size:14px;">Samantha Esplana</strong>
              <p class="text-muted small mb-0">Alabang, Muntinlupa</p>
            </div>
          </div>
          <div class="mb-3">
            <i class="fas fa-star text-warning"></i>
            <i class="fas fa-star text-warning"></i>
            <i class="fas fa-star text-warning"></i>
            <i class="fas fa-star text-warning"></i>
            <i class="far fa-star text-warning"></i>
          </div>
          <p class="text-muted fst-italic" style="font-size:13px;">"Very professional and reliable service. Everything was done on time and communication was clear throughout the process."</p>
        </div>
      </div>

      <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
        <div style="background:#fff; border:1px solid rgba(0,0,0,0.08); border-radius:16px; padding:1.5rem; position:relative;">
          <span style="position:absolute; top:12px; right:18px; font-size:48px; line-height:1; color:#ddd; font-family:Georgia,serif;">&ldquo;</span>
          <div class="d-flex align-items-center mb-3">
            <img src="assets/img/user2.jpg" alt="Rayne Velasco" class="rounded-circle me-3"
              style="width:50px;height:50px;object-fit:cover;">
            <div>
              <strong style="font-size:14px;">Rayne Velasco</strong>
              <p class="text-muted small mb-0">Bacoor, Cavite</p>
            </div>
          </div>
          <div class="mb-3">
            <i class="fas fa-star text-warning"></i>
            <i class="fas fa-star text-warning"></i>
            <i class="fas fa-star text-warning"></i>
            <i class="fas fa-star text-warning"></i>
            <i class="far fa-star text-warning"></i>
          </div>
          <p class="text-muted fst-italic" style="font-size:13px;">"They are so accommodating and responsive! They answered all my questions that I needed to know about installing solar, which really helped me decide. Highly recommended!"</p>
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
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path
                                    d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </div>
                        <h3 class="accordion-title">Protection Against Rising Electricity Costs</h3>
                        <div class="accordion-toggle">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                            </svg>
                        </div>
                    </div>
                    <div class="accordion-content">
                        <div class="accordion-content-inner">
                            <p>Lock in your energy costs and shield yourself from unpredictable utility rate increases.
                                Solar provides stable, predictable energy expenses for decades.</p>
                            <span class="reason-tag">Financial Security</span>
                        </div>
                    </div>
                </div>

                <!-- Accordion Item 2 -->
                <div class="accordion-item">
                    <div class="accordion-header">
                        <div class="accordion-icon-wrapper">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
                            </svg>
                        </div>
                        <h3 class="accordion-title">Energy Independence</h3>
                        <div class="accordion-toggle">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                            </svg>
                        </div>
                    </div>
                    <div class="accordion-content">
                        <div class="accordion-content-inner">
                            <p>Generate your own clean electricity and reduce reliance on the grid. Take control of your
                                power supply and enjoy freedom from utility companies.</p>
                            <span class="reason-tag">Self-Sufficiency</span>
                        </div>
                    </div>
                </div>

                <!-- Accordion Item 3 -->
                <div class="accordion-item">
                    <div class="accordion-header">
                        <div class="accordion-icon-wrapper">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M3 12h18M3 6h18M3 18h18" />
                                <circle cx="12" cy="12" r="10" />
                            </svg>
                        </div>
                        <h3 class="accordion-title">Environment Friendly</h3>
                        <div class="accordion-toggle">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                            </svg>
                        </div>
                    </div>
                    <div class="accordion-content">
                        <div class="accordion-content-inner">
                            <p>Reduce your carbon footprint and contribute to a cleaner planet. Solar energy produces
                                zero emissions, helping combat climate change for future generations.</p>
                            <span class="reason-tag">Green Living</span>
                        </div>
                    </div>
                </div>

                <!-- Accordion Item 4 -->
                <div class="accordion-item">
                    <div class="accordion-header">
                        <div class="accordion-icon-wrapper">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" />
                            </svg>
                        </div>
                        <h3 class="accordion-title">Low Maintenance</h3>
                        <div class="accordion-toggle">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                            </svg>
                        </div>
                    </div>
                    <div class="accordion-content">
                        <div class="accordion-content-inner">
                            <p>Solar panels require minimal upkeep with no moving parts. Simple occasional cleaning and
                                standard warranties ensure worry-free operation for 25+ years.</p>
                            <span class="reason-tag">Hassle-free</span>
                        </div>
                    </div>
                </div>

                <!-- Accordion Item 5 -->
                <div class="accordion-item">
                    <div class="accordion-header">
                        <div class="accordion-icon-wrapper">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                            </svg>
                        </div>
                        <h3 class="accordion-title">Government Incentives & Rebates</h3>
                        <div class="accordion-toggle">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                            </svg>
                        </div>
                    </div>
                    <div class="accordion-content">
                        <div class="accordion-content-inner">
                            <p>Take advantage of tax credits, rebates, and incentive programs that significantly reduce
                                installation costs. Save thousands with available financial support.</p>
                            <span class="reason-tag">Save More</span>
                        </div>
                    </div>
                </div>

                <!-- Accordion Item 6 -->
                <div class="accordion-item">
                    <div class="accordion-header">
                        <div class="accordion-icon-wrapper">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
                            </svg>
                        </div>
                        <h3 class="accordion-title">Reliable Long-Term Investment</h3>
                        <div class="accordion-toggle">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                            </svg>
                        </div>
                    </div>
                    <div class="accordion-content">
                        <div class="accordion-content-inner">
                            <p>Increase your property value while enjoying immediate savings. Solar systems pay for
                                themselves through energy savings and boost home resale value.</p>
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
                            <p>Connect with our solarpower experts to discuss the best energy solutions for your needs.
                            </p>
                            <a href="https://wa.me/639953947379" class="whatsapp-btn" target="_blank">
                                <i class="fab fa-whatsapp"></i>
                                Chat on WhatsApp
                            </a>
                        </div>

                        <!-- Company Information -->
                        <div class="company-info">
                            <div class="contact-detail">
                                <div class="icon-wrap"><i class="fas fa-map-marker-alt"></i></div>
                                <div>
                                    <strong>Address</strong>
                                    <p>4/F PBB Corporate Center, 1906 Finance Drive, Madrigal Business Park 1, Ayala Alabang, Muntinlupa City, 1780, Philippines</p>
                                </div>
                            </div>

                            <div class="contact-detail">
                                <div class="icon-wrap"><i class="fas fa-phone"></i></div>
                                <div>
                                    <strong>Phone</strong>
                                    <span class="phone-number" id="phone-copy" onclick="copyToClipboard('+639953947379', this)">+639953947379</span>
                                </div>
                            </div>

                            <div class="contact-detail">
                                <div class="icon-wrap"><i class="fas fa-envelope"></i></div>
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

                        <!-- Social Links -->
                        <div class="contact-social-links">
                            <p class="contact-social-label">Follow Us</p>
                            <div class="social-links">
                                <a href="https://www.facebook.com/p/SolarPower-Energy-Corporation-61578373983187/" target="_blank" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                                <a href="https://www.instagram.com/solarpowerenergycorporation?igsh=MWh4YTEyYWpzbDNlNQ==" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                                <a href="https://www.tiktok.com/@solarpower.energy?_r=1&_t=ZS-92HlpTBUuzF" target="_blank" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
                                <a href="https://youtube.com/@solarpowerenergycorporation?si=-kln0fTid4zMZDXq" target="_blank" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                                <a href="https://www.linkedin.com/in/solar-power-6792283aa" target="_blank" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
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
                                    <div class="input-group">
                                        <span class="input-group-text">+63 &nbsp;<i class="fas fa-chevron-down" style="font-size:10px;color:#aaa;"></i></span>
                                        <input type="tel" class="form-control" id="contact_phone" placeholder="9XX XXX XXXX" required maxlength="10" pattern="[0-9]{10}" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                        <input type="hidden" id="contact_phone_full" name="phone">
                                    </div>
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

    <?php include "includes/faqChat.php" ?>



    <!-- INSPECTION MODAL -->
    <div class="modal fade" id="inspectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 rounded-4 overflow-hidden position-relative">

                <!-- Close Button -->
                <button type="button" class="btn-close position-absolute end-0 m-3" data-bs-dismiss="modal"
                    style="z-index:1060;"></button>

                <div class="row g-0 min-vh-modal">

                    <!-- LEFT INFO PANEL -->
                    <div class="col-lg-5 d-none d-lg-flex inspection-left-panel"
                        style="background-color:#0a5c3d; background-image: linear-gradient(160deg, rgba(20,40,20,.92) 0%, rgba(10,92,61,.85) 100%), url('assets/img/solar-install.jpg'); background-size:cover; background-position:center;">
                        <div class="w-100 p-5 text-white d-flex flex-column justify-content-center">

                            <div class="inspection-badge mb-3">
                                <i class="fas fa-solar-panel me-2"></i> Free Site Assessment
                            </div>

                            <h2 class="fw-bold mb-3">Ready to <span class="text-warning">Switch<br>to Solar?</span></h2>
                            <p class="mb-4 opacity-75">Book a site inspection and let our certified engineers design the
                                perfect system for your home or business.</p>

                            <ul class="list-unstyled inspection-features">
                                <li class="mb-3"><i class="fas fa-check-circle text-warning me-2"></i> Professional
                                    Assessment</li>
                                <li class="mb-3"><i class="fas fa-check-circle text-warning me-2"></i> Accurate ROI
                                    Projection</li>
                                <li class="mb-3"><i class="fas fa-check-circle text-warning me-2"></i> Custom System
                                    Design</li>
                            </ul>

                            <hr class="border-white opacity-10 my-4">

                            <p class="small opacity-50 mb-0">
                                <i class="fas fa-shield-alt me-1"></i>
                                Your information is secure and will never be shared.
                            </p>
                        </div>
                    </div>

                    <!-- FORM PANEL -->
                    <div class="col-lg-7 bg-white p-4 p-md-5">
                        <div class="mb-4">
                            <h2 class="fw-bold">Book Site Inspection</h2>
                            <p class="text-muted small">We'll contact you within 24 hours.</p>
                        </div>

                        <form id="inspectionForm" class="inspection-form">
                            <div class="row">

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small text-uppercase">Full Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" name="fullname" class="form-control"
                                            placeholder="Juan Dela Cruz" required>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small text-uppercase">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" name="email" class="form-control"
                                            placeholder="juan@email.com" required>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small text-uppercase">Contact Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text" style="background:#e8f4ef;border-color:#dee2e6;color:#0a5c3d;font-weight:700;font-size:0.93rem;">+639</span>
                                        <input type="tel" name="phone" class="form-control" placeholder="XXXXXXXXX"
                                            required maxlength="9" pattern="[0-9]{9}"
                                            oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                    </div>
                                    <input type="hidden" name="phone_full" class="insp-phone-full">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small text-uppercase">Property Type</label>
                                    <select name="property_type" class="form-select" required>
                                        <option value="" disabled selected>Select type</option>
                                        <option value="Residential">Residential</option>
                                        <option value="Commercial">Commercial</option>
                                    </select>
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label fw-semibold small text-uppercase">Complete Address</label>
                                    <textarea name="address" class="form-control" rows="2"
                                        placeholder="House No., Street, Brgy, City" required></textarea>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small text-uppercase">Inspection Date</label>
                                    <input type="date" name="inspection_date" class="form-control" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small text-uppercase">Monthly Bill (₱)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" name="bill" class="form-control" placeholder="e.g. 5000"
                                            required>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small text-uppercase">Roof Type</label>
                                    <select name="roof_type" id="roofTypeSelect" class="form-select" required>
                                        <option value="" disabled selected>Select roof type</option>
                                        <option value="Concrete/Flat Roof"> Concrete / Flat Roof</option>
                                        <option value="Corrugated Metal"> Corrugated Metal</option>
                                        <option value="Tile (Clay/Concrete)"> Tile (Clay / Concrete)</option>
                                        <option value="Asphalt Shingles"> Asphalt Shingles</option>
                                        <option value="Other">Other (Please specify)</option>
                                    </select>
                                    <input type="text" name="roof_type_other" id="roofOtherInput"
                                        class="form-control mt-2 d-none" placeholder="Please describe your roof type">
                                </div>

                                <!--<div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-uppercase">Terms of Payment</label>
                                <select name="payment_terms" class="form-select" required>
                                    <option value="" disabled selected>Select payment method</option>
                                    <option value="COD">Cash on Delivery (COD)</option>
                                    <option value="Installment">Installment</option>
                                    <option value="Rent To Own">Rent To Own</option>
                                    <option value="Solar Loans">Solar Loans</option>
                                </select>
                            </div>-->

                                <div class="col-12 mb-4">
                                    <label class="form-label fw-semibold small text-uppercase">Additional Notes
                                        (Optional)</label>
                                    <textarea name="notes" class="form-control" rows="3"
                                        placeholder="Tell us about your roof type or any specific concerns..."></textarea>
                                </div>

                            </div>

                            <button type="submit" class="btn w-100 py-3 fw-bold text-uppercase" id="inspectionBtn"
                                style="background:linear-gradient(135deg,#f39c12,#e67e22);color:#fff;border:none;">
                                <span class="btn-text"><i class="fas fa-calendar-check me-2"></i>Confirm My
                                    Schedule</span>
                                <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- SUCCESS MODAL -->
    <div class="modal fade" id="inspectionSuccessModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 overflow-hidden text-center">
                <div style="height:5px; background: linear-gradient(90deg,#f39c12,#e67e22);"></div>
                <div class="modal-body py-5 px-4">
                    <i class="fas fa-solar-panel text-warning mb-3" style="font-size:56px;"></i>
                    <h4 class="fw-bold mb-2">Request Submitted!</h4>
                    <p class="text-muted mb-0">
                        Your inspection request has been received.<br>
                        <strong class="text-dark">Our team will contact you within business hours.</strong>
                    </p>
                </div>
                <div class="modal-footer border-0 justify-content-center pb-4">
                    <button type="button" class="btn fw-bold px-5 py-2" id="successOkBtn" data-bs-dismiss="modal">
                        Got it, thanks!
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
                quantity: moq,   // start at MOQ, not 1
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

        // Add phone number formatter
        const phoneInput = document.getElementById('cust_phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', formatPhoneNumber);
        }

        // Initialize Philippine address cascade dropdowns
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

        // When province changes — load cities/municipalities
        provinceEl.addEventListener('change', async function () {
            const code = this.value;

            municipalityEl.innerHTML = '<option value="">Select City / Municipality</option>';
            municipalityEl.disabled = true;
            barangayEl.innerHTML = '<option value="">Select Barangay</option>';
            barangayEl.disabled = true;

            if (!code) return;

            // NCR cities stored as NCR_<cityCode>
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

        // When municipality/city changes — load barangays
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

        // Load all provinces on page init
        provinceEl.innerHTML = '<option value="">Loading provinces...</option>';
        provinceEl.disabled = true;
        try {
            const provinces = await psgcFetch(PSGC_BASE + '/provinces/');
            if (!provinces || provinces.length === 0) throw new Error('Empty provinces response');
            populateSelect('province', provinces, 'code', 'name', 'Select Province');

            // Append NCR (Metro Manila) highly-urbanized cities as a separate group
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
            '.hero', '.featured-brands', '.savings-calculator', '#solarBuilderSection',
            '.why-choose-us', '.services-section', '.solar-tips-section',
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
            '.hero', '.featured-brands', '.savings-calculator', '#solarBuilderSection',
            '.why-choose-us', '.services-section', '.solar-tips-section',
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

        // Calculate fees
        const deliveryFee = calculateDeliveryFee();
        const installationFee = hasGridTieOrHybridProduct() ? 2000 : 0;
        const grandTotal = cartSubtotal + deliveryFee + installationFee;

        const formattedSubtotal = "₱" + cartSubtotal.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        const formattedTotal = "₱" + grandTotal.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

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

        let provinceText = provinceSel.options[provinceSel.selectedIndex]?.text || '';
        // Strip the ' (NCR)' suffix added for NCR cities shown in province dropdown
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
    
    // Validate file size (max 5MB)
    if (receiptFile.size > 5 * 1024 * 1024) {
        showNotificationModal('error', 'Receipt file is too large. Maximum size is 5MB.');
        return;
    }
    
    const confirmBtn = document.getElementById('confirmPaymentBtn');
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting Order...';
    
    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked')?.value || 'full';
    let paymentPercentage = '100%';
    if (paymentMethod === 'downpayment') paymentPercentage = '50%';
    if (paymentMethod === 'initial') paymentPercentage = '20%';
    
    const totalAmount = window.currentTotalAmount || 0;
    let amountPaid = totalAmount;
    if (paymentMethod === 'downpayment') amountPaid = totalAmount * 0.5;
    if (paymentMethod === 'initial') amountPaid = totalAmount * 0.2;
    
    // Build FormData so we can include the file
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
        // Capture raw text first so we can debug if it is not JSON
        return response.text().then(text => {
            console.log('Raw server response:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                // Server returned non-JSON (PHP error page, 404, etc.)
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
        console.error(' InstaPay order error:', error.message);
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Confirm &amp; Submit Order';
        // Show the actual server error message so it is easy to debug
        const userMsg = error.message.includes('Server returned') 
            ? 'Server error — please check browser console (F12) for details.' 
            : error.message;
        showNotificationModal('error', userMsg);
    });
}

// Old Maya payment function - now replaced by InstaPay
function payWithMaya(paymentType) {
    console.log(' Processing Maya payment...');
    
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
    
    console.log('Sending to Maya API:', orderData);
    
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

function copyOrderRef() {
    const orderRef = document.getElementById('confOrderRef')?.textContent;
    if (orderRef) {
        navigator.clipboard.writeText(orderRef).then(() => {
            showNotificationModal('success', '✅ Order reference copied to clipboard!');
        }).catch(() => {
            // Fallback for older browsers
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

function copyToClipboard(text, el) {
    navigator.clipboard.writeText(text).then(() => {
        const orig = el.textContent;
        el.textContent = 'Copied!';
        setTimeout(() => { el.textContent = orig; }, 1500);
    }).catch(() => {
        const ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
    });
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
            if (calculatorBox) {
                calculatorBox.classList.remove('collapsed');
                calculatorBox.classList.add('expanded');
            }
        }

        function shrinkCalculatorIfEmpty() {
            const billInput = document.getElementById('billAmount');
            const calculatorBox = document.getElementById('calculatorBox');
            const results = document.getElementById('results');
            
            if (calculatorBox && billInput && !billInput.value && !results.classList.contains('show')) {
                setTimeout(() => {
                    calculatorBox.classList.remove('expanded');
                    calculatorBox.classList.add('collapsed');
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
            
            const avgRate = 14.50;
            const monthlyConsumption = billAmount / avgRate;
            const dailyConsumption = monthlyConsumption / 30;
            const sunHours = 4.5;
            const systemEfficiency = 0.85;
            const panelWattage = 610;
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
        // ============================================
// 7. FILTERS & SEARCH
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
    // handled by submitContactForm() called via onsubmit on the form
}

async function submitContactForm(event) {
    event.preventDefault();

    const form        = document.getElementById('contactForm');
    const submitBtn   = document.getElementById('contactSubmitBtn');
    const btnText     = submitBtn.querySelector('.btn-text');
    const btnSpinner  = submitBtn.querySelector('.btn-spinner');

    // Combine +639 prefix with phone digits
    const phoneInput = document.getElementById('contact_phone');
    const phoneFullInput = document.getElementById('contact_phone_full');
    if (phoneFullInput && phoneInput) {
        phoneFullInput.value = '+63' + phoneInput.value;
        phoneInput.name = '';
    }

    // Show loading state
    btnText.classList.add('d-none');
    btnSpinner.classList.remove('d-none');
    submitBtn.disabled = true;

    try {
        const formData = new FormData(form);

        const response = await fetch('controllers/contact_submit.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            form.reset();
            // Show success modal if it exists, otherwise fallback notification
            const successModal = document.getElementById('contactSuccessModal');
            if (successModal) {
                const modal = new bootstrap.Modal(successModal);
                modal.show();
            } else {
                showNotificationModal('success', 'Message sent! We will get back to you soon.');
            }
        } else {
            showNotificationModal('error', result.message || 'Failed to send message. Please try again.');
        }
    } catch (err) {
        console.error('Contact form error:', err);
        showNotificationModal('error', 'There was an error submitting your message. Please try again or contact us directly at solar@solarpower.com.ph');
    } finally {
        // Restore button state
        btnText.classList.remove('d-none');
        btnSpinner.classList.add('d-none');
        submitBtn.disabled = false;
    }
}

document.getElementById('roofTypeSelect').addEventListener('change', function () {
    const other = document.getElementById('roofOtherInput');
    if (this.value === 'Other') {
        other.classList.remove('d-none');
        other.setAttribute('required', 'required');
    } else {
        other.classList.add('d-none');
        other.removeAttribute('required');
        other.value = '';
    }
});


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

// ===========================
// UTILITIES
// ===========================
function toggleHours() {
    const hoursContent = document.getElementById('hours-content');
    const hoursIcon = document.getElementById('hours-icon');
    
    if (hoursContent.style.maxHeight) {
        hoursContent.style.maxHeight = null;
        hoursIcon.style.transform = 'rotate(0deg)';
    } else {
        hoursContent.style.maxHeight = hoursContent.scrollHeight + 'px';
        hoursIcon.style.transform = 'rotate(180deg)';
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

<!-- Toast Notification Container -->
<div id="toast-container" style="position:fixed; top:20px; right:20px; z-index:99999; min-width:300px;"></div>

<!-- ====================================================
     FLOATING TRACK ORDER BUTTON + PANEL
     Ilagay sa taas ng floating chat button
     ==================================================== -->

<style>
/* ── Floating Button ── */
.float-track-btn {
    position: fixed;
    bottom: 150px; /* taas ng ibang floating btn — i-adjust kung kailangan */
    right: 20px;
    z-index: 9990;
    width: 54px;
    height: 54px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f39c12, #e67e22);
    color: white;
    border: none;
    box-shadow: 0 4px 15px rgba(243,156,18,0.5);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    transition: transform 0.2s, box-shadow 0.2s;
}
.float-track-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(243,156,18,0.65);
}
.float-track-btn .track-tooltip {
    position: absolute;
    right: 62px;
    background: #2c3e50;
    color: #fff;
    font-size: 12px;
    font-weight: 600;
    padding: 5px 10px;
    border-radius: 6px;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s;
}
.float-track-btn:hover .track-tooltip { opacity: 1; }

/* ── Slide-up Panel ── */
.track-panel {
    position: fixed;
    bottom: 215px; /* taas ng button */
    right: 20px;
    width: 370px;
    max-width: calc(100vw - 30px);
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 15px 50px rgba(0,0,0,0.18);
    z-index: 9991;
    overflow: hidden;
    transform: translateY(20px) scale(0.97);
    opacity: 0;
    pointer-events: none;
    transition: transform 0.3s ease, opacity 0.3s ease;
}
.track-panel.open {
    transform: translateY(0) scale(1);
    opacity: 1;
    pointer-events: all;
}

/* Panel Header */
.track-panel-header {
    background: linear-gradient(135deg, #2d5016, #3d6b1e);
    color: white;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.track-panel-header h6 {
    margin: 0;
    font-weight: 700;
    font-size: 15px;
}
.track-panel-header .close-panel {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: background 0.2s;
}
.track-panel-header .close-panel:hover { background: rgba(255,255,255,0.35); }

/* Panel Body */
.track-panel-body { padding: 20px; }

/* Search Input */
.track-input-wrap { position: relative; margin-bottom: 12px; }
.track-input-wrap i {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    color: #aaa;
    font-size: 14px;
}
.track-input-wrap input {
    width: 100%;
    padding: 11px 12px 11px 36px;
    border: 2px solid #eee;
    border-radius: 10px;
    font-size: 14px;
    outline: none;
    transition: border 0.2s;
}
.track-input-wrap input:focus { border-color: #f39c12; }

/* Search Button */
.track-search-btn {
    width: 100%;
    padding: 11px;
    background: linear-gradient(135deg, #f39c12, #e67e22);
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    transition: opacity 0.2s;
}
.track-search-btn:hover { opacity: 0.9; }
.track-search-btn:disabled { opacity: 0.6; cursor: not-allowed; }

/* Results */
.track-results { margin-top: 14px; max-height: 260px; overflow-y: auto; }
.track-results::-webkit-scrollbar { width: 4px; }
.track-results::-webkit-scrollbar-thumb { background: #ddd; border-radius: 4px; }

/* Order Row */
.track-order-row {
    border: 1px solid #f0f0f0;
    border-radius: 12px;
    padding: 14px;
    margin-bottom: 10px;
    transition: box-shadow 0.2s;
}
.track-order-row:hover { box-shadow: 0 3px 12px rgba(0,0,0,0.08); }
.track-order-ref { font-size: 11px; color: #999; font-weight: 600; }
.track-order-items { font-size: 13px; font-weight: 700; color: #2c3e50; margin: 4px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.track-order-location { font-size: 12px; color: #888; }
.track-status-badge {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    background: rgba(243,156,18,0.12);
    color: #e67e22;
    padding: 3px 9px;
    border-radius: 20px;
}
.track-order-amount { font-size: 13px; font-weight: 700; color: #27ae60; }

/* States */
.track-empty { text-align: center; padding: 20px 0; }
.track-empty img { width: 55px; opacity: 0.4; margin-bottom: 8px; }
.track-empty p { color: #bbb; font-size: 13px; margin: 0; }
.track-loading { text-align: center; padding: 20px; color: #f39c12; }

/* Full details link */
.track-full-link {
    display: block;
    text-align: center;
    margin-top: 10px;
    font-size: 12px;
    color: #f39c12;
    text-decoration: none;
    font-weight: 600;
}
.track-full-link:hover { text-decoration: underline; }
</style>

<!-- Floating Button -->
<button class="float-track-btn" onclick="toggleTrackPanel()" title="Track My Order">
    <i class="fas fa-shipping-fast"></i>
    <span class="track-tooltip">Track My Order</span>
</button>

<!-- Slide-up Panel -->
<div class="track-panel" id="trackPanel">
    <div class="track-panel-header">
        <h6><i class="fas fa-shipping-fast me-2"></i> Track My Order</h6>
        <button class="close-panel" onclick="toggleTrackPanel()"><i class="fas fa-times"></i></button>
    </div>
    <div class="track-panel-body">
        <div class="track-input-wrap">
            <i class="fas fa-phone"></i>
            <input type="tel" id="floatTrackPhone" placeholder="e.g. +639805926760" 
                   onkeydown="if(event.key==='Enter') doFloatTrack()">
        </div>
        <button class="track-search-btn" id="floatTrackBtn" onclick="doFloatTrack()">
            <i class="fas fa-search me-1"></i> TRACK ORDERS
        </button>
        <div id="floatTrackResults"></div>
    </div>
</div>

<script>
// ── Toggle panel open/close ──────────────────────────────────────────────────
function toggleTrackPanel() {
    const panel = document.getElementById('trackPanel');
    panel.classList.toggle('open');
    if (panel.classList.contains('open')) {
        setTimeout(() => document.getElementById('floatTrackPhone').focus(), 200);
    }
}

// Close panel when clicking outside
document.addEventListener('click', function(e) {
    const panel  = document.getElementById('trackPanel');
    const btn    = document.querySelector('.float-track-btn');
    if (!panel.contains(e.target) && !btn.contains(e.target)) {
        panel.classList.remove('open');
    }
});

// ── Status label map (same as track-order.php) ──────────────────────────────
function getStatusLabel(status) {
    const map = {
        'maya_initial' : 'Initial Payment',
        'maya_full'    : 'Full Payment',
        'down_payment' : 'Down Payment',
        'pending'      : 'Pending',
        'confirmed'    : 'To Ship',
        'in_transit'   : 'To Receive',
        'delivered'    : 'Completed',
    };
    if (!status) return 'Pending';
    return map[status] || status.charAt(0).toUpperCase() + status.slice(1);
}

// ── Fetch orders ─────────────────────────────────────────────────────────────
function doFloatTrack() {
    const phone  = document.getElementById('floatTrackPhone').value.trim();
    const btn    = document.getElementById('floatTrackBtn');
    const result = document.getElementById('floatTrackResults');

    if (!phone) {
        result.innerHTML = `<p class="text-danger small mt-2"><i class="fas fa-exclamation-circle me-1"></i>Please enter your cellphone number.</p>`;
        return;
    }

    // Loading state
    btn.disabled = true;
    btn.innerHTML = `<i class="fas fa-spinner fa-spin me-1"></i> Searching...`;
    result.innerHTML = `<div class="track-loading"><i class="fas fa-spinner fa-spin fa-lg"></i></div>`;

    fetch(`controllers/customer_track_order.php?phone=${encodeURIComponent(phone)}`)
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = `<i class="fas fa-search me-1"></i> TRACK ORDERS`;

            if (!data.success) {
                result.innerHTML = `
                    <div class="track-empty">
                        <img src="https://cdn-icons-png.flaticon.com/512/4076/4076432.png" alt="">
                        <p>${data.message || 'No orders found.'}</p>
                    </div>`;
                return;
            }

            const rows = data.orders.map(order => `
                <div class="track-order-row">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="track-order-ref">${order.order_reference}</span>
                        <span class="track-status-badge">${getStatusLabel(order.order_status)}</span>
                    </div>
                    <div class="track-order-items">${order.items_ordered || 'Solar Product'}</div>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <span class="track-order-location">
                            <i class="fas fa-map-marker-alt me-1"></i>${order.current_location || 'Warehouse'}
                        </span>
                        <span class="track-order-amount">₱${parseFloat(order.total_amount).toLocaleString()}</span>
                    </div>
                </div>
            `).join('');

            result.innerHTML = `
                <div class="track-results">${rows}</div>
                <a href="track-order.php" class="track-full-link">
                    <i class="fas fa-external-link-alt me-1"></i> View full order details
                </a>`;
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = `<i class="fas fa-search me-1"></i> TRACK ORDERS`;
            result.innerHTML = `<p class="text-danger small mt-2"><i class="fas fa-exclamation-circle me-1"></i>Connection error. Please try again.</p>`;
        });
}
</script>
<!-- END FLOATING TRACK ORDER -->

</html>