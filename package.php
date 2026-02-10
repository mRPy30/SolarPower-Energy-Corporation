<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);

/* ---------- DB connection ---------- */
include "config/dbconn.php";


/* ---------- Fetch Package Products ---------- */
$packages = [];

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
WHERE p.brandName IN ('Grid-Tie', 'Hybrid')
AND pi.image_path IS NOT NULL
GROUP BY p.id
ORDER BY p.brandName, p.price";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $packages[] = $row;
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
<title>SolarPower Energy Corporation - Smart Energy for Smarter Homes</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    
    <style>
        /* Package Cover Section */
        .package-hero {
            position: relative;
            height: 60vh;
            min-height: 500px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .package-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('assets/img/solar-pattern.png') center/cover;
            opacity: 0.1;
        }
        
        .package-hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
            max-width: 800px;
            padding: 0 20px;
        }
        
        .package-hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .package-hero p {
            font-size: 1.3rem;
            margin-bottom: 30px;
            opacity: 0.95;
        }
        
        .package-types {
            display: flex;
            gap: 30px;
            justify-content: center;
            margin-top: 40px;
        }
        
        .package-type-card {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            min-width: 250px;
            border: 2px solid rgba(255,255,255,0.3);
            transition: all 0.3s ease;
        }
        
        .package-type-card:hover {
            transform: translateY(-5px);
            background: rgba(255,255,255,0.25);
            border-color: rgba(255,255,255,0.5);
        }
        
        .package-type-card i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #f39c12;
        }
        
        .package-type-card h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .package-type-card p {
            font-size: 0.95rem;
            margin: 0;
            opacity: 0.9;
        }
        
        /* Packages Section */
        .packages-section {
            padding: 80px 0;
            background: #f8f9fa;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .section-header h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .section-header p {
            font-size: 1.1rem;
            color: #7f8c8d;
        }
        
        /* Filter Bar - Match Catalog Style */
        .filter-bar {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .filter-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 12px 24px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 25px;
            font-weight: 600;
            color: #555;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .filter-btn:hover {
            border-color: #f39c12;
            color: #f39c12;
            transform: translateY(-2px);
        }
        
        .filter-btn.active {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
            border-color: #f39c12;
        }
        
        .filter-btn i {
            font-size: 1.1rem;
        }
        
        .sort-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sort-label {
            font-weight: 600;
            color: #555;
            margin: 0;
        }
        
        .sort-select {
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .sort-select:focus {
            outline: none;
            border-color: #f39c12;
        }
        
        /* Product Grid - Match Catalog Style */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .product-card {
            background: white; 
            border-radius: 12px; 
            overflow: hidden; 
            transition: all 0.3s ease; 
            border: 2px solid #e5e7eb;
            display: flex; 
            flex-direction: column;
            position: relative;
        }
        
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .product-image {
            position: relative; 
            width: 100%; 
            height: 310px; 
            background: #fafafa; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 30px; 
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .product-card:hover .product-image img {
            transform: scale(1.05);
        }
        
        .product-badge {
            position: absolute; 
            top: 12px; 
            right: 12px; 
            background: #e7ad00; 
            color: white; 
            padding: 6px 12px; 
            border-radius: 20px; 
            font-size: 10px; 
            font-weight: 600; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        
        .package-kw-badge {
            position: absolute; 
            top: 12px; 
            right: 12px; 
            background: #e7ad00; 
            color: white; 
            padding: 6px 12px; 
            border-radius: 20px; 
            font-size: 10px; 
            font-weight: 600; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        
        .product-info {
            padding: 20px; 
            flex: 1; 
            display: flex; 
            flex-direction: column; 
        }
        
        .product-brand {
            font-size: 11px; 
            color: #999; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
            margin-bottom: 6px; 
            font-weight: 600;
        }
        
        .product-name {
            font-size: 16px; 
            font-weight: 600; 
            color: #1a1a1a; 
            line-height: 1.4; 
            display: -webkit-box; 
            -webkit-box-orient: vertical; 
            overflow: hidden;
        }
        
        .product-price {
            font-size: 32px; 
            font-weight: 700; 
            color: #1a1a1a; 
            margin-bottom: 16px; 
            margin-top: auto; 
        }
        
        .preview-stock {
            font-size: 14px;
            color: #6b7280;
        }
        
        .product-actions {
            display: flex; 
            gap: 8px;
            position: relative;
            z-index: 10;
            padding: 15px;
        }
        
        .btn-add-cart {
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
        
        .btn-add-cart:hover {
            background: #e7ad00; 
            color: white;
            border-color: #e7ad00;
            transform: scale(1.05);
        }
        
        .btn-buy-now { 
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
        
        .btn-buy-now:hover { 
            background: #d39d00; 
            color: white;
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(231, 173, 0, 0.3);
        }
        
        .no-products {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }
        
        .no-products i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        @media (max-width: 768px) {
            .package-hero h1 {
                font-size: 2.5rem;
            }
            
            .package-hero p {
                font-size: 1.1rem;
            }
            
            .filter-bar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-buttons {
                justify-content: center;
            }
            
            .sort-container {
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
    <?php include "includes/header.php" ?>
    
    <!-- Package Hero Cover Section -->
    <section class="package-hero">
        <div class="package-hero-content">
            <h1>Solar Package Solutions</h1>
            <p>Complete solar power systems designed for your home or business</p>
            
            <div class="package-types">
                <div class="package-type-card">
                    <i class="fas fa-solar-panel"></i>
                    <h3>Grid-Tie Setup</h3>
                    <p>Connect to the grid and save on electricity bills</p>
                </div>
                
                <div class="package-type-card">
                    <i class="fas fa-battery-full"></i>
                    <h3>Hybrid Setup</h3>
                    <p>Grid connection with battery backup storage</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Packages Section -->
    <section class="packages-section" id="packagesSection">
        <div class="container">
            <div class="section-header">
                <h2>Choose Your Package</h2>
                <p>From 2.2kW to 10kW systems - find the perfect fit for your needs</p>
            </div>
            
            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="filter-buttons" id="packageFilters">
                    <button class="filter-btn active" data-type="all">
                        <i class="fas fa-th"></i> All Packages
                    </button>
                    <button class="filter-btn" data-type="Grid-Tie">
                        <i class="fas fa-plug"></i> Grid-Tie Setup
                    </button>
                    <button class="filter-btn" data-type="Hybrid">
                        <i class="fas fa-battery-full"></i> Hybrid Setup
                    </button>
                </div>
                
                <div class="sort-container">
                    <label class="sort-label">Sort by:</label>
                    <select class="sort-select" id="sortSelect">
                        <option value="default">Default</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                        <option value="kw-low">kW: Low to High</option>
                        <option value="kw-high">kW: High to Low</option>
                    </select>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="products-grid" id="packagesGrid">
                <?php if ($packages): ?>
                    <?php foreach ($packages as $p): ?>
                        <?php 
                        // Extract kW from display name (e.g., "2.2kW System" -> "2.2kW")
                        preg_match('/(\d+\.?\d*)kW?/i', $p['displayName'], $matches);
                        $kw = isset($matches[1]) ? $matches[1] . 'kW' : '';
                        ?>
                        <div class="product-card" 
                             data-type="<?= htmlspecialchars($p['brandName']) ?>"
                             data-name="<?= htmlspecialchars($p['displayName']) ?>"
                             data-price="<?= htmlspecialchars($p['price']) ?>"
                             data-kw="<?= htmlspecialchars(str_replace('kW', '', $kw)) ?>">
                            
                            <!-- Clickable Product Image and Info -->
                            <div onclick="location.href='product-details.php?id=<?= $p['id'] ?>'" style="cursor: pointer;">
                                <div class="product-image">
                                    <img src="<?= htmlspecialchars($p['image_path'] ?? 'assets/img/placeholder.png') ?>" 
                                         alt="<?= htmlspecialchars($p['displayName']) ?>">
                                    <div class="product-badge"><?= htmlspecialchars($p['brandName']) ?></div>
                                    <?php if ($kw): ?>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="product-info">
                                    <div class="product-brand"><?= htmlspecialchars($p['brandName']) ?> Package</div>
                                    <h3 class="product-name"><?= htmlspecialchars($p['displayName']) ?></h3>
                                    <div class="product-price">
                                        ₱<?= number_format($p['price'], 2) ?>
                                    </div>
                                    <div class="preview-stock">
                                        <i class="fas fa-box"></i> Stock: <?= htmlspecialchars($p['stockQuantity']) ?> units
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
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
                        <p>No packages available at the moment</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    
    <!-- Checkout Section (Same as index.php) -->
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
                        <button class="btn btn-sm btn-outline-primary" onclick="backToPackages()">
                            <i class="fas fa-plus"></i> Add More Products
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
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Full Delivery Address</label>
                            <textarea class="form-control" id="cust_address" rows="3" placeholder="House No., Street, Barangay, City, Province" required></textarea>
                        </div>
                    </div>

                    <div class="checkout-actions">
                        <button class="btn-outline" onclick="backToPackages()">← Continue Shopping</button>
                        <button class="btn-primary" onclick="validateStep1()">Proceed to Payment →</button>
                    </div>
                </div>

                <div id="checkoutStep2" class="checkout-card" style="display:none;">
                    <h3>Order Summary & Payment</h3>

                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Select Payment Method</h5>
                        </div>
                        <div class="card-body">
                            <div class="payment-options">
                                <div class="form-check payment-option mb-3">
                                    <input class="form-check-input" type="radio" name="paymentMethod" id="paymentFull" value="full" checked onchange="updatePaymentDisplay()">
                                    <label class="form-check-label w-100" for="paymentFull">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><i class="fas fa-money-bill-wave text-success me-2"></i>Full Payment</strong>
                                                <p class="text-muted mb-0 small">Pay 100% via Maya now</p>
                                            </div>
                                            <span class="badge bg-success">Recommended</span>
                                        </div>
                                    </label>
                                </div>

                                <div class="form-check payment-option mb-3">
                                    <input class="form-check-input" type="radio" name="paymentMethod" id="paymentDown" value="downpayment" onchange="updatePaymentDisplay()">
                                    <label class="form-check-label w-100" for="paymentDown">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><i class="fas fa-percentage text-warning me-2"></i>50% Down Payment</strong>
                                                <p class="text-muted mb-0 small">Pay 50% now via Maya, 50% upon delivery</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>

                                <div class="form-check payment-option mb-3">
                                    <input class="form-check-input" type="radio" name="paymentMethod" id="paymentCOD" value="cod" onchange="updatePaymentDisplay()">
                                    <label class="form-check-label w-100" for="paymentCOD">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><i class="fas fa-hand-holding-usd text-info me-2"></i>Cash on Delivery (COD)</strong>
                                                <p class="text-muted mb-0 small">Pay in cash when order arrives</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="payment-summary-box p-3 bg-light rounded mb-4">
                        <div class="summary-row"><span>Items Subtotal:</span><span id="checkoutSubtotal" class="fw-bold"></span></div>
                        <div class="summary-row"><span>Installation Fee:</span><span class="text-success">FREE</span></div>
                        <hr>
                        <div class="summary-row" style="font-size: 1.2rem; color: #2c3e50;">
                            <span>Amount to Pay:</span><span id="amountToPay" class="fw-bold text-primary"></span>
                        </div>
                        <div class="summary-row total-row" style="font-size: 1.5rem; color: #2c3e50;">
                            <span>Total Amount:</span><span id="checkoutTotal"></span>
                        </div>
                    </div>

                    <div id="paymentNote" class="alert alert-info">
                        <i class="fas fa-info-circle"></i> You are paying the <strong>Full Amount</strong> via Maya.
                    </div>

                    <div class="checkout-actions mt-4">
                        <button class="btn-outline" onclick="goToStep(1)">← Edit Details</button>
                        <button id="confirmPaymentBtn" class="btn-primary" onclick="payWithMaya('full')">Pay with Maya</button>
                    </div>
                </div>

                <div id="checkoutStep3" class="checkout-card" style="display:none;">
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle text-success mb-3" style="font-size:64px;"></i>
                        <h3>Order Submitted</h3>
                        <p class="text-muted">Thank you! Your payment was successful.</p>
                        <p><strong>Order Reference:</strong> <span id="orderRef"></span></p>
                        <button class="btn btn-primary mt-3" onclick="location.href='package.php'">
                            Back to Packages
                        </button>
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
    
    <?php include "includes/footer.php" ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="assets/script.js"></script>
    
    <script>
        // Initialize cart from storage
        let cart = [];
        
        document.addEventListener('DOMContentLoaded', function() {
            loadCartFromStorage();
            updateCartBadge();
            initializePackageFilters();
            initializePackageSort();
        });
        
        // ========================================
        // STORAGE FUNCTIONS
        // ========================================
        function saveCartToStorage() {
            try {
                localStorage.setItem('solarCart', JSON.stringify(cart));
            } catch (error) {
                console.error('Error saving cart:', error);
            }
        }
        
        function loadCartFromStorage() {
            try {
                const stored = localStorage.getItem('solarCart');
                if (stored) {
                    cart = JSON.parse(stored);
                }
            } catch (error) {
                console.error('Error loading cart:', error);
                cart = [];
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
        
        // ========================================
        // PACKAGE FILTERS
        // ========================================
        function initializePackageFilters() {
            const filterButtons = document.querySelectorAll('#packageFilters .filter-btn');
            
            filterButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    filterButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    const type = this.getAttribute('data-type');
                    filterPackages(type);
                });
            });
        }
        
        function filterPackages(type) {
            const cards = document.querySelectorAll('.product-card');
            let visibleCount = 0;
            
            cards.forEach(card => {
                const cardType = card.getAttribute('data-type');
                
                if (type === 'all' || cardType === type) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            showNoProductsMessage(visibleCount);
        }
        
// ========================================
// PACKAGE PAGE - CART & CHECKOUT SYSTEM
// ========================================

let cart = [];

document.addEventListener('DOMContentLoaded', function() {
    loadCartFromStorage();
    updateCartBadge();
    initializePackageFilters();
    initializePackageSort();
});

// ========================================
// STORAGE FUNCTIONS
// ========================================
function saveCartToStorage() {
    try {
        localStorage.setItem('solarCart', JSON.stringify(cart));
    } catch (error) {
        console.error('Error saving cart:', error);
    }
}

function loadCartFromStorage() {
    try {
        const stored = localStorage.getItem('solarCart');
        if (stored) {
            cart = JSON.parse(stored);
        }
    } catch (error) {
        console.error('Error loading cart:', error);
        cart = [];
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
    updateCartBadge();
}

// ========================================
// PACKAGE FILTERS
// ========================================
function initializePackageFilters() {
    const filterButtons = document.querySelectorAll('#packageFilters .filter-btn');
    
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const type = this.getAttribute('data-type');
            filterPackages(type);
        });
    });
}

function filterPackages(type) {
    const cards = document.querySelectorAll('.product-card');
    let visibleCount = 0;
    
    cards.forEach(card => {
        const cardType = card.getAttribute('data-type');
        
        if (type === 'all' || cardType === type) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    showNoProductsMessage(visibleCount);
}

function showNoProductsMessage(visibleCount) {
    const grid = document.getElementById('packagesGrid');
    let noProductsMsg = document.querySelector('.no-products-filter');
    
    if (visibleCount === 0) {
        if (!noProductsMsg) {
            noProductsMsg = document.createElement('div');
            noProductsMsg.className = 'no-products no-products-filter';
            noProductsMsg.innerHTML = `
                <i class="fas fa-box-open"></i>
                <p>No packages found in this category</p>
            `;
            grid.appendChild(noProductsMsg);
        }
        noProductsMsg.style.display = 'block';
    } else {
        if (noProductsMsg) {
            noProductsMsg.style.display = 'none';
        }
    }
}

// ========================================
// PACKAGE SORT
// ========================================
function initializePackageSort() {
    const sortSelect = document.getElementById('sortSelect');
    
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            sortPackages(this.value);
        });
    }
}

function sortPackages(sortType) {
    const grid = document.getElementById('packagesGrid');
    const cards = Array.from(document.querySelectorAll('.product-card'));
    
    cards.sort((a, b) => {
        switch(sortType) {
            case 'price-low':
                return parseFloat(a.getAttribute('data-price')) - parseFloat(b.getAttribute('data-price'));
            
            case 'price-high':
                return parseFloat(b.getAttribute('data-price')) - parseFloat(a.getAttribute('data-price'));
            
            case 'kw-low':
                return parseFloat(a.getAttribute('data-kw') || 0) - parseFloat(b.getAttribute('data-kw') || 0);
            
            case 'kw-high':
                return parseFloat(b.getAttribute('data-kw') || 0) - parseFloat(a.getAttribute('data-kw') || 0);
            
            default:
                return 0;
        }
    });
    
    cards.forEach(card => {
        grid.appendChild(card);
    });
}

// ========================================
// CART MODAL
// ========================================
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
                    <div class="modal-body" id="cartModalBody"></div>
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

// ========================================
// CART FUNCTIONS
// ========================================
function addToCartFromButton(btn) {
    const product = JSON.parse(btn.getAttribute('data-product'));
    addToCartLogic(product);
    showCartPopup();
    showNotificationModal('success', 'Package added to cart!');
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
    updateCartBadge();
    renderCartPopup();
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
        updateCartBadge();
        renderCartPopup();
    }
}

function removeFromCartPopup(productId) {
    if (confirm('Remove this item from cart?')) {
        cart = cart.filter(i => i.id !== productId);
        saveCartToStorage();
        updateCartBadge();
        renderCartPopup();
        showNotificationModal('success', 'Item removed from cart');
    }
}

function buyNowFromButton(btn) {
    const product = JSON.parse(btn.getAttribute('data-product'));
    cart = [];
    addToCartLogic(product);
    proceedToCheckout();
}

// ========================================
// CHECKOUT FUNCTIONS
// ========================================
function proceedToCheckout() {
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
    document.querySelector('.package-hero').style.display = 'none';
    document.querySelector('.packages-section').style.display = 'none';
    document.querySelector('footer').style.display = 'none';
    
    document.getElementById('checkoutSection').style.display = 'block';
    window.scrollTo(0, 0);
    goToStep(1);
}

function backToPackages() {
    document.getElementById('checkoutSection').style.display = 'none';
    
    document.querySelector('.package-hero').style.display = 'flex';
    document.querySelector('.packages-section').style.display = 'block';
    document.querySelector('footer').style.display = 'block';
    
    window.scrollTo(0, document.getElementById('packagesSection').offsetTop - 100);
}

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
                        <i class="fas fa-trash-alt"></i>
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
        updateCartBadge();
        renderCheckoutSummary();
        
        if (cart.length === 0) {
            showNotificationModal('error', 'Cart is empty. Returning to packages.');
            setTimeout(() => backToPackages(), 1500);
        }
    }
}

function goToStep(step) {
    document.getElementById('checkoutStep1').style.display = 'none';
    document.getElementById('checkoutStep2').style.display = 'none';
    document.getElementById('checkoutStep3').style.display = 'none';

    document.getElementById('ind-step1').classList.remove('active', 'completed');
    document.getElementById('ind-step2').classList.remove('active', 'completed');
    document.getElementById('ind-step3').classList.remove('active', 'completed');

    document.getElementById(`checkoutStep${step}`).style.display = 'block';
    document.getElementById(`ind-step${step}`).classList.add('active');
    
    for (let i = 1; i < step; i++) {
        document.getElementById(`ind-step${i}`).classList.add('completed');
    }
    
    document.getElementById('checkoutSection').scrollIntoView({ behavior: 'smooth' });
}

function validateStep1() {
    const name = document.getElementById('cust_name').value.trim();
    const email = document.getElementById('cust_email').value.trim();
    const phone = document.getElementById('cust_phone').value.trim();
    const address = document.getElementById('cust_address').value.trim();
    
    if (!name || !email || !phone || !address) {
        showNotificationModal('error', 'Please fill in all required fields');
        return;
    }
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showNotificationModal('error', 'Please enter a valid email address');
        return;
    }
    
    goToStep(2);
    renderCheckoutSummary();
    updatePaymentDisplay();
}

function updatePaymentDisplay() {
    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked')?.value || 'full';
    const totalAmount = window.currentTotalAmount || 0;
    
    const amountToPayDisplay = document.getElementById('amountToPay');
    const paymentNote = document.getElementById('paymentNote');
    const confirmBtn = document.getElementById('confirmPaymentBtn');
    
    if (paymentMethod === 'full') {
        amountToPayDisplay.textContent = '₱' + totalAmount.toLocaleString(undefined, {minimumFractionDigits: 2});
        paymentNote.innerHTML = '<i class="fas fa-info-circle"></i> Paying <strong>Full Amount</strong> via Maya.';
        confirmBtn.textContent = 'Pay with Maya';
        confirmBtn.onclick = () => payWithMaya('full');
    } else if (paymentMethod === 'downpayment') {
        const downpayment = totalAmount * 0.5;
        amountToPayDisplay.textContent = '₱' + downpayment.toLocaleString(undefined, {minimumFractionDigits: 2});
        paymentNote.innerHTML = '<i class="fas fa-info-circle"></i> Paying <strong>50% Down Payment</strong> via Maya.';
        confirmBtn.textContent = 'Pay 50% Down';
        confirmBtn.onclick = () => payWithMaya('downpayment');
    } else {
        amountToPayDisplay.textContent = '₱' + totalAmount.toLocaleString(undefined, {minimumFractionDigits: 2});
        paymentNote.innerHTML = '<i class="fas fa-exclamation-triangle"></i> <strong>Cash on Delivery</strong>';
        confirmBtn.textContent = 'Confirm COD Order';
        confirmBtn.onclick = () => confirmCODOrder();
    }
}

function payWithMaya(paymentType) {
    showNotificationModal('success', 'Redirecting to Maya payment...');
    setTimeout(() => {
        displayOrderConfirmation('ORD-' + Date.now());
    }, 2000);
}

function confirmCODOrder() {
    displayOrderConfirmation('COD-' + Date.now());
}

function displayOrderConfirmation(orderId) {
    document.getElementById('orderRef').textContent = orderId;
    goToStep(3);
    clearCart();
}

function showNotificationModal(type, message) {
    let modal = document.getElementById('notificationModal');
    if (!modal) {
        const modalHTML = `
            <div class="modal fade" id="notificationModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header border-0">
                            <h5 class="modal-title" id="notifTitle"></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <i id="notifIcon" style="font-size: 48px;"></i>
                            <p id="notifMessage" class="mt-3"></p>
                        </div>
                        <div class="modal-footer border-0 justify-content-center">
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        modal = document.getElementById('notificationModal');
    }
    
    const icon = document.getElementById('notifIcon');
    const title = document.getElementById('notifTitle');
    const msg = document.getElementById('notifMessage');
    
    if (type === 'success') {
        icon.className = 'fas fa-check-circle text-success';
        title.innerHTML = '<i class="fas fa-check-circle text-success me-2"></i>Success';
    } else {
        icon.className = 'fas fa-times-circle text-danger';
        title.innerHTML = '<i class="fas fa-exclamation-circle text-danger me-2"></i>Error';
    }
    
    msg.textContent = message;
    
    new bootstrap.Modal(modal).show();
}

</script>

</body>
</html>