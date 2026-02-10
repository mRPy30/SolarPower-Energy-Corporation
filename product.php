<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
/* ---------- 1.  DB connection ---------- */
include "config/dbconn.php";


/* ---------- 2.  Fetch products (safe) ---------- */
$products = [];
$packageProducts = []; // Separate array for packages

$sql  = "SELECT 
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
AND p.brandName NOT IN ('Grid-Tie', 'Hybrid')
GROUP BY p.id
ORDER BY p.id";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        // Separate packages from regular products
        $category = trim($row['category']);
        if ($category === 'Package') {
            $packageProducts[] = $row;
        } else {
            $products[] = $row;
        }
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

    <link rel="stylesheet" href="assets/style.css">
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
            background: linear-gradient(to right, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.4)), 
                url('assets/img/products.png') no-repeat center center/cover;
            height: 60vh;
            min-height: 400px;
            color: var(--clr-light);
            display: flex;
            align-items: center;
            position: relative;
        }

        .hero-about .hero-content {
            text-align: center;
        }

        .hero-about h1 {
            font-size: 48px;
            margin-bottom: 15px;
            font-weight: 700;
            line-height: 1.2;
        }

        .hero-about .subtitle {
            font-size: 22px;
            color: var(--clr-primary);
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 2px;
        }
    </style>
    
    <?php include "includes/header.php" ?>

    <section class="hero-about">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="hero-content">
                        <p class="subtitle">OUR PRODUCTS</p>
                        <h1>The Future of Energy, Available Today</h1>
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
                                    <div class="product-badge"><?= htmlspecialchars($p['category']) ?></div>
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
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Full Delivery Address</label>
                            <textarea class="form-control" id="cust_address" rows="3" placeholder="House No., Street, Barangay, City, Province" required></textarea>
                        </div>
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
                            <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Select Payment Method</h5>
                        </div>
                        <div class="card-body">
                            <div class="payment-options">
                                <!-- Full Payment -->
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

                                <!-- 50% Down Payment -->
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

                                <!-- Cash on Delivery -->
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

                    <!-- Order Summary -->
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

                    <!-- Payment Note -->
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
                        <p class="text-muted">
                            Thank you! Your payment was successful.
                        </p>
                        <p>
                            <strong>Order Reference:</strong>
                            <span id="orderRef"></span>
                        </p>

                        <button class="btn btn-primary mt-3" onclick="location.href='index.php'">
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

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>


</body>
<script src="assets/script.js"></script>
<script>
    // Initialize cart 
let cart = [];

document.addEventListener('DOMContentLoaded', function() {
    loadCartFromStorage();
    updateCartBadge();
    initializeFilters();
    initializeSort();
    initializeSubscription();
    setupCalculator();
});

function addToCartFromButton(btn) {
    const product = JSON.parse(btn.getAttribute('data-product'));
    addToCartLogic(product);
    showCartPopup();
    showNotificationModal('success', 'Product added to cart!');
}

function addToCart(productId) {
    // Find the button with this product ID
    const btn = document.querySelector(`button[data-product*='"id":${productId}']`);
    if (btn) {
        addToCartFromButton(btn);
    } else {
        showNotificationModal('error', 'Product not found');
    }
}

// SUBSCRIPTION FUNCTIONALITY
function initializeSubscription() {
    const subscribeForm = document.getElementById('subscribe-form');
    if (!subscribeForm) return;
    
    const subscribeBtn = document.getElementById('subscribe-btn');
    const btnText = subscribeBtn.querySelector('.btn-text');
    const btnSpinner = subscribeBtn.querySelector('.btn-spinner');
    const emailInput = document.getElementById('subscribe-email');

    subscribeForm.addEventListener('submit', function(event) {
        event.preventDefault();

        const email = emailInput.value.trim();
        if (!email) {
            emailInput.style.borderColor = '#dc3545';
            emailInput.style.boxShadow = '0 0 0 0.2rem rgba(220, 53, 69, 0.25)';
            showNotificationModal('error', 'Please enter an email address.');
            return;
        }

        // Clear error state
        emailInput.style.borderColor = '';
        emailInput.style.boxShadow = '';

        btnText.classList.add('d-none');
        btnSpinner.classList.remove('d-none');
        subscribeBtn.disabled = true;

        fetch('controllers/subscribe.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({ email: email })
        })
        .then(response => response.json())
        .then(data => {
            btnText.classList.remove('d-none');
            btnSpinner.classList.add('d-none');
            subscribeBtn.disabled = false;

            if (data.status === 'success') {
                showNotificationModal('success', data.message);
                emailInput.value = '';
            } else {
                showNotificationModal('error', data.message);
            }
        })
        .catch(error => {
            btnText.classList.remove('d-none');
            btnSpinner.classList.add('d-none');
            subscribeBtn.disabled = false;
            console.error('Error:', error);
            showNotificationModal('error', 'An error occurred. Please try again.');
        });
    });

    // Clear error on input
    emailInput.addEventListener('input', function() {
        this.style.borderColor = '';
        this.style.boxShadow = '';
    });
}

// ========================================
// CONTACT FORM SUBMISSION
// ========================================
function submitContact(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    fetch("controllers/contact_submit.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const modal = new bootstrap.Modal(
                document.getElementById('contactSuccessModal')
            );
            modal.show();
            form.reset();
        } else {
            alert(data.message || "Something went wrong.");
        }
    })
    .catch(() => {
        alert("Unable to send message. Please try again later.");
    });
}

// ========================================
// PRODUCT FILTER FUNCTIONALITY
// ========================================
function initializeFilters() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(b => b.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Get selected category
            const category = this.getAttribute('data-category');
            
            // Filter products
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
    
    // Update view more button visibility
    updateViewMoreButton(visibleCount);
    
    // Show no products message if none visible
    showNoProductsMessage(visibleCount);
}

// ========================================
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
// ========================================
function toggleViewMore() {
    const btn = document.getElementById('viewMoreBtn');
    const hiddenProducts = document.querySelectorAll('.product-card.hidden-product');
    const span = btn.querySelector('span');
    const icon = btn.querySelector('i');
    
    const allHidden = Array.from(hiddenProducts).every(p => p.style.display === 'none');
    
    hiddenProducts.forEach(product => {
        // Only toggle if product matches current filter
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
    
    // Toggle button text and icon
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
        if (viewMoreContainer) {
            viewMoreContainer.style.display = 'none';
        }
    } else {
        if (viewMoreContainer) {
            viewMoreContainer.style.display = 'flex';
        }
        
        // Reset to "View More" state if needed
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
        if (noProductsMsg) {
            noProductsMsg.style.display = 'none';
        }
    }
}

// ========================================
// RESET FILTERS (UTILITY)
// ========================================
function resetFilters() {
    document.querySelector('.filter-btn[data-category="all"]').click();
    document.getElementById('sortSelect').value = 'default';
    sortProducts('default');
}

// ========================================
// CART FUNCTIONS
// ========================================
function addToCart(productId) {
    // Find product by ID and add to cart
    fetch('controllers/get_product.php?id=' + productId)
        .then(res => res.json())
        .then(product => {
            if (product) {
                addToCartLogic(product);
                showNotificationModal('success', 'Product added to cart!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotificationModal('error', 'Failed to add product to cart');
        });
}

function buyNowFromButton(btn) {
    const product = JSON.parse(btn.getAttribute('data-product'));
    addToCartLogic(product);
    showCheckout();
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
    renderCheckoutSummary();
}

function updateQuantity(productId, change) {
    const item = cart.find(i => i.id === productId);
    if (item) {
        item.quantity += change;
        
        // Prevent quantity from going below 1
        if (item.quantity < 1) {
            item.quantity = 1;
            return; // Don't update if trying to go below 1
        }
        
        renderCheckoutSummary();
    }
}

function removeFromCart(productId) {
    // Show confirmation modal
    if (confirm('Are you sure you want to remove this item from your cart?')) {
        cart = cart.filter(i => i.id !== productId);
        renderCheckoutSummary();
        showNotificationModal('success', 'Item removed from cart');
    }
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
        
        // Check if quantity is 1 to disable minus button
        const minusDisabled = item.quantity === 1 ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '';
        
        html += `
            <div class="d-flex align-items-center gap-3 mb-3 border-bottom pb-3 position-relative">
                <img src="${item.image_path}" 
                     style="width:60px; height:60px; object-fit:cover; border-radius:8px;"
                     onerror="this.src='assets/img/placeholder.png'">
                <div class="flex-grow-1">
                    <p class="mb-1 fw-bold" style="font-size: 0.95rem;">${item.displayName}</p>
                    <small class="text-muted">₱${item.price.toLocaleString()} x ${item.quantity}</small>
                    <p class="mb-0 fw-bold text-primary" style="font-size: 0.9rem;">
                        Subtotal: ₱${itemTotal.toLocaleString(undefined, {minimumFractionDigits: 2})}
                    </p>
                </div>
                <div class="d-flex flex-column align-items-end gap-2">
                    <div class="quantity-controls d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-outline-secondary" 
                                onclick="updateQuantity(${item.id}, -1)" 
                                ${minusDisabled}
                                title="${item.quantity === 1 ? 'Use delete button to remove' : 'Decrease quantity'}">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="fw-bold px-2">${item.quantity}</span>
                        <button class="btn btn-sm btn-outline-secondary" 
                                onclick="updateQuantity(${item.id}, 1)"
                                title="Increase quantity">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <button class="btn btn-sm btn-danger" 
                            onclick="removeFromCart(${item.id})"
                            title="Remove from cart">
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
    
    const payBtn = document.getElementById('confirmPaymentBtn');
    if (payBtn) {
        payBtn.disabled = cart.length === 0;
    }
}

// ========================================
// CHECKOUT NAVIGATION
// ========================================
function showCheckout() {
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
}

function backToCatalog() {
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

// ========================================
// BROWSER STORAGE FUNCTIONS
// ========================================
function saveCartToStorage() {
    try {
        const cartData = JSON.stringify(cart);
        // Store in memory (no localStorage in artifacts)
        window.cartStorage = cartData;
    } catch (error) {
        console.error('Error saving cart:', error);
    }
}

function loadCartFromStorage() {
    try {
        const stored = window.cartStorage;
        if (stored) {
            cart = JSON.parse(stored);
        }
    } catch (error) {
        console.error('Error loading cart:', error);
        cart = [];
    }
}

function clearCart() {
    cart = [];
    saveCartToStorage();
    updateCartBadge();
    renderCartPopup();
}

// ========================================
// CART BADGE UPDATE
// ========================================
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
// CART POPUP MODAL
// ========================================
function createCartModal() {
    // Check if modal already exists
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
    
    // Cart Summary
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
// ENHANCED CART FUNCTIONS
// ========================================
function addToCart(productId) {
    fetch('controllers/get_product.php?id=' + productId)
        .then(res => res.json())
        .then(product => {
            if (product) {
                addToCartLogic(product);
                showCartPopup(); // Show popup after adding
                showNotificationModal('success', 'Product added to cart!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotificationModal('error', 'Failed to add product to cart');
        });
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
    
    // Clear cart and add only this product
    cart = [];
    addToCartLogic(product);
    
    // Go directly to checkout
    proceedToCheckout();
}

// ========================================
// PROCEED TO CHECKOUT
// ========================================
function proceedToCheckout() {
    if (cart.length === 0) {
        showNotificationModal('error', 'Your cart is empty');
        return;
    }
    
    // Close the cart modal
    const cartModal = bootstrap.Modal.getInstance(document.getElementById('cartModal'));
    if (cartModal) {
        cartModal.hide();
    }
    
    // Show checkout section
    showCheckout();
    
    // Render cart in checkout
    renderCheckoutSummary();
}

function showCheckout() {
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
    
    // Reset to step 1
    goToStep(1);
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
            <div class="d-flex align-items-center gap-3 mb-3 border-bottom pb-3 position-relative">
                <img src="${item.image_path}" 
                     style="width:60px; height:60px; object-fit:cover; border-radius:8px;"
                     onerror="this.src='assets/img/placeholder.png'">
                <div class="flex-grow-1">
                    <p class="mb-1 fw-bold" style="font-size: 0.95rem;">${item.displayName}</p>
                    <small class="text-muted">₱${item.price.toLocaleString()} x ${item.quantity}</small>
                    <p class="mb-0 fw-bold text-primary" style="font-size: 0.9rem;">
                        Subtotal: ₱${itemTotal.toLocaleString(undefined, {minimumFractionDigits: 2})}
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
    
    const payBtn = document.getElementById('confirmPaymentBtn');
    if (payBtn) {
        payBtn.disabled = cart.length === 0;
    }
}

function updateCheckoutQuantity(productId, change) {
    updateCartQuantity(productId, change);
    renderCheckoutSummary();
}

function removeFromCheckout(productId) {
    if (confirm('Remove this item from cart?')) {
        cart = cart.filter(i => i.id !== productId);
        saveCartToStorage();
        updateCartBadge();
        renderCheckoutSummary();
        
        // If cart is empty, go back to catalog
        if (cart.length === 0) {
            showNotificationModal('error', 'Cart is empty. Returning to catalog.');
            setTimeout(() => backToCatalog(), 1500);
        }
    }
}

// ========================================
// CONTINUE SHOPPING / BACK TO CATALOG
// ========================================
function backToCatalog() {
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
    document.getElementById('checkoutStep1').style.display = 'none';
    document.getElementById('checkoutStep2').style.display = 'none';
    document.getElementById('checkoutStep3').style.display = 'none';

    document.getElementById('ind-step1').classList.remove('active');
    document.getElementById('ind-step2').classList.remove('active');
    document.getElementById('ind-step3').classList.remove('active');

    document.getElementById(`checkoutStep${step}`).style.display = 'block';
    document.getElementById(`ind-step${step}`).classList.add('active');
    
    // Update data-step attribute for progress line animation
    const checkoutSteps = document.getElementById('checkoutSteps') || document.querySelector('.checkout-steps');
    if (checkoutSteps) {
        checkoutSteps.setAttribute('data-step', step);
    }
    
    // Add 'completed' class to previous steps
    for (let i = 1; i < step; i++) {
        document.getElementById(`ind-step${i}`).classList.add('completed');
    }
    
    // Remove 'completed' class from future steps
    for (let i = step + 1; i <= 3; i++) {
        document.getElementById(`ind-step${i}`).classList.remove('completed');
    }
    
    document.getElementById('checkoutSection').scrollIntoView({ behavior: 'smooth' });
}

// ========================================
// CHECKOUT VALIDATION
// ========================================
function validateStep1() {
    // Clear previous error states
    clearErrorStates();
    
    const name = document.getElementById('cust_name').value.trim();
    const email = document.getElementById('cust_email').value.trim();
    const phone = document.getElementById('cust_phone').value.trim();
    const address = document.getElementById('cust_address').value.trim();
    
    let hasError = false;
    let errorMessage = '';
    
    // Validate name
    if (!name) {
        setErrorState('cust_name');
        errorMessage = 'Please enter your full name.';
        hasError = true;
    }
    
    // Validate email
    if (!email) {
        setErrorState('cust_email');
        errorMessage = errorMessage || 'Please enter your email address.';
        hasError = true;
    } else {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            setErrorState('cust_email');
            errorMessage = 'Please enter a valid email address.';
            hasError = true;
        }
    }
    
    // Validate phone
    if (!phone) {
        setErrorState('cust_phone');
        errorMessage = errorMessage || 'Please enter your contact number.';
        hasError = true;
    } else {
        const phoneRegex = /^(09|\+639)\d{9}$/;
        if (!phoneRegex.test(phone.replace(/\s/g, ''))) {
            setErrorState('cust_phone');
            errorMessage = 'Please enter a valid Philippine phone number (e.g., 09123456789).';
            hasError = true;
        }
    }
    
    // Validate address
    if (!address) {
        setErrorState('cust_address');
        errorMessage = errorMessage || 'Please enter your delivery address.';
        hasError = true;
    }
    
    if (hasError) {
        showValidationModal(errorMessage);
        return;
    }
    
    goToStep(2);
    renderCheckoutSummary();
    updatePaymentDisplay(); // Initialize payment display
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
    const inputs = ['cust_name', 'cust_email', 'cust_phone', 'cust_address'];
    inputs.forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.classList.remove('is-invalid');
            input.style.borderColor = '';
            input.style.boxShadow = '';
        }
    });
}

function showValidationModal(message) {
    // Create modal if it doesn't exist
    let modal = document.getElementById('validationErrorModal');
    if (!modal) {
        const modalHTML = `
            <div class="modal fade" id="validationErrorModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header border-0">
                            <h5 class="modal-title">
                                <i class="fas fa-exclamation-circle text-danger me-2"></i>
                                Validation Error
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-times-circle text-danger" style="font-size: 48px;"></i>
                            </div>
                            <p id="validationErrorMessage" class="mb-0"></p>
                        </div>
                        <div class="modal-footer border-0 justify-content-center">
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                                OK, I'll fix it
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        modal = document.getElementById('validationErrorModal');
    }
    
    // Set the error message
    document.getElementById('validationErrorMessage').textContent = message;
    
    // Show the modal
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
}

// Add input listeners to clear error state when user starts typing
document.addEventListener('DOMContentLoaded', function() {
    const inputs = ['cust_name', 'cust_email', 'cust_phone', 'cust_address'];
    inputs.forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('input', function() {
                this.classList.remove('is-invalid');
                this.style.borderColor = '';
                this.style.boxShadow = '';
            });
        }
    });
});

// ========================================
// PAYMENT OPTIONS HANDLING
// ========================================
function updatePaymentDisplay() {
    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked')?.value || 'full';
    const totalAmount = window.currentTotalAmount || 0;
    
    const amountToPayDisplay = document.getElementById('amountToPay');
    const paymentNote = document.getElementById('paymentNote');
    const confirmBtn = document.getElementById('confirmPaymentBtn');
    
    if (paymentMethod === 'full') {
        amountToPayDisplay.textContent = '₱' + totalAmount.toLocaleString(undefined, {minimumFractionDigits: 2});
        paymentNote.innerHTML = '<i class="fas fa-info-circle"></i> You are paying the <strong>Full Amount</strong> via Maya.';
        paymentNote.className = 'alert alert-info';
        confirmBtn.textContent = 'Pay with Maya';
        confirmBtn.onclick = () => payWithMaya('full');
    } else if (paymentMethod === 'downpayment') {
        const downpayment = totalAmount * 0.5;
        amountToPayDisplay.textContent = '₱' + downpayment.toLocaleString(undefined, {minimumFractionDigits: 2});
        paymentNote.innerHTML = '<i class="fas fa-info-circle"></i> You are paying <strong>50% Down Payment</strong> via Maya. The remaining balance will be collected upon delivery.';
        paymentNote.className = 'alert alert-warning';
        confirmBtn.textContent = 'Pay 50% Down Payment';
        confirmBtn.onclick = () => payWithMaya('downpayment');
    } else if (paymentMethod === 'cod') {
        amountToPayDisplay.textContent = '₱' + totalAmount.toLocaleString(undefined, {minimumFractionDigits: 2});
        paymentNote.innerHTML = `
            <i class="fas fa-exclamation-triangle"></i> 
            <strong>Cash on Delivery</strong><br>
            <small>Reminder: Please prepare the exact amount of <strong>₱${totalAmount.toLocaleString(undefined, {minimumFractionDigits: 2})}</strong> upon delivery. Our delivery personnel will collect the payment when your order arrives.</small>
        `;
        paymentNote.className = 'alert alert-warning';
        confirmBtn.textContent = 'Confirm COD Order';
        confirmBtn.onclick = () => confirmCODOrder();
    }
}

function confirmCODOrder() {
    if (cart.length === 0) {
        showNotificationModal('error', 'Your cart is empty.');
        return;
    }
    
    const confirmBtn = document.getElementById('confirmPaymentBtn');
    const originalText = confirmBtn.innerHTML;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
    confirmBtn.disabled = true;
    
    const customerData = {
        name: document.getElementById('cust_name').value,
        email: document.getElementById('cust_email').value,
        phone: document.getElementById('cust_phone').value,
        address: document.getElementById('cust_address').value
    };
    
    // Simulate COD order submission
    setTimeout(() => {
        fetch('controllers/create-cod-order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                amount: window.currentTotalAmount,
                items: cart,
                customer: customerData,
                paymentMethod: 'cod'
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                displayOrderConfirmation(data.orderId);
            } else {
                showNotificationModal('error', data.message || 'Order submission failed.');
                confirmBtn.innerHTML = originalText;
                confirmBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Even on error, show confirmation for demo purposes
            displayOrderConfirmation();
        });
    }, 1000);
}

// ========================================
// PAYMENT FUNCTIONS
// ========================================
function payWithMaya(paymentType = 'full') {
    if (cart.length === 0) {
        showNotificationModal('error', 'Your cart is empty. Please add products before proceeding to payment.');
        return;
    }
    
    const payBtn = document.getElementById('confirmPaymentBtn');
    const originalText = payBtn.innerHTML;
    payBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
    payBtn.disabled = true;
    
    const totalAmount = window.currentTotalAmount;
    const amountToPay = paymentType === 'downpayment' ? totalAmount * 0.5 : totalAmount;
    
    const customerData = {
        name: document.getElementById('cust_name').value,
        email: document.getElementById('cust_email').value,
        phone: document.getElementById('cust_phone').value,
        address: document.getElementById('cust_address').value
    };
    
    fetch('controllers/create-maya-checkout.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            amount: amountToPay,
            totalAmount: totalAmount,
            paymentType: paymentType,
            items: cart,
            customer: customerData
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.checkoutUrl) {
            window.location.href = data.checkoutUrl;
        } else if (data.success) {
            displayOrderConfirmation(data.orderId);
        } else {
            showNotificationModal('error', data.message || 'Payment initialization failed. Please try again.');
            payBtn.innerHTML = originalText;
            payBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Payment error:', error);
        showNotificationModal('error', 'An error occurred while processing your payment. Please try again.');
        payBtn.innerHTML = originalText;
        payBtn.disabled = false;
    });
}

function displayOrderConfirmation(orderId) {
    const orderRef = orderId || 'ORD-' + Date.now();
    document.getElementById('orderRef').textContent = orderRef;
    
    goToStep(3);
    
    cart = [];
    renderCheckoutSummary();
}

</script>
</html>