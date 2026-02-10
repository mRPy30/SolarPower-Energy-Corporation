<?php
session_start();

/* ---------- 1.  DB connection ---------- */
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "solar_power";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
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
$conn->close();

$userEmail = $_SESSION['user_email'] ?? '';
$userName = $_SESSION['user_name'] ?? '';
$userPhone = $_SESSION['user_phone'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solar Power Energy - Smart Energy for Smarter Homes</title>

    <!-- Bootstrap + Font-Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Your custom CSS -->
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body>

<!-- ---------- 3.  TOP BAR ---------- -->
<div class="header-top">
    <img src="../../assets/img/DOE.png" alt="DOE">
    <h5>DOE ACCREDITED INSTALLER ESCO 25090095</h5>
</div>

<!-- ---------- 4.  HEADER NAV ---------- -->
<?php include "../../includes/header.php"; ?>

<!-- ---------- 5.  HERO ---------- -->
<section class="hero" id="home">
    <video autoplay muted loop playsinline class="hero-video">
        <source src="../../assets/img/homepage.mp4" type="video/mp4">
    </video>
    <div class="hero-overlay"></div>
    <div class="container hero-content">
        <div class="row">
            <div class="col-lg-8">
                <h1>Smart Energy for<br>Smarter Homes</h1>
                <p>Invest in solar today - enjoy decades of <br>energy independence and savings.</p>
                <div class="hero-cta">
                    <button class="btn btn-primary">Learn More</button>
                    <button class="btn btn-secondary">Book for Inspection</button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ---------- 6.  BRANDS ---------- -->
<section class="featured-brands" id="featured-brands">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center"><h2>Featured Brands</h2></div>
        </div>
        <div class="carousel-wrapper">
            <div class="brands-scroll-list">
                <?php
                $brands = ['hoymiles','solax','aiko','iansolar','lvtopsun','aesolar',
                           'jinko','jasolar','huawei','luxpower','trinasolar','dyness',
                           'deye','tigfox'];
                foreach ($brands as $b): ?>
                    <div class="brand-item"><img src="../../assets/img/<?= $b ?>.png" alt="<?= ucfirst($b) ?>"></div>
                <?php endforeach; ?>
                <!-- duplicate once for seamless scroll -->
                <?php foreach ($brands as $b): ?>
                    <div class="brand-item"><img src="../../assets/img/<?= $b ?>.png" alt="<?= ucfirst($b) ?>"></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- ---------- 7.  CATALOG ---------- -->
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

                <button class="filter-btn" data-category="Solar Panels">
                    <i class="fas fa-solar-panel"></i> Panels
                </button>

                <button class="filter-btn" data-category="Inverters">
                    <i class="fas fa-plug"></i> Inverters
                </button>

                <button class="filter-btn" data-category="Batteries">
                    <i class="fas fa-battery-full"></i> Batteries
                </button>

                <button class="filter-btn" data-category="Accessories">
                    <i class="fas fa-tools"></i> Accessories
                </button>

                <!-- NEW -->
                <button class="filter-btn" data-category="Grid-tie">
                    <i class="fas fa-bolt"></i> Grid-tie
                </button>

                <button class="filter-btn" data-category="Hybrid">
                    <i class="fas fa-solar-panel"></i> Hybrid
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

        <!-- Products Grid -->
        <div class="products-grid" id="productsGrid">
            <?php if ($products): ?>
                <?php foreach ($products as $index => $p): ?>
                    <div class="product-card <?= $index >= 4 ? 'hidden-product' : '' ?>" 
                         data-category="<?= htmlspecialchars($p['category']) ?>"
                         data-name="<?= htmlspecialchars($p['displayName']) ?>"
                         data-price="<?= htmlspecialchars($p['price']) ?>">
                        
                        <div class="product-image">
                            <img 
                                src="../../<?= htmlspecialchars($p['image_path'] ?? 'assets/img/placeholder.png') ?>" 
                                alt="<?= htmlspecialchars($p['displayName']) ?>"
                            >
                            <div class="product-badge"><?= htmlspecialchars($p['category']) ?></div>
                        </div>

                        <div class="product-info">
                            <div class="product-brand"><?= htmlspecialchars($p['brandName']) ?></div>
                            <h3 class="product-name"><?= htmlspecialchars($p['displayName']) ?></h3>
                            
                            <div class="product-price">
                                ₱<?= number_format($p['price'], 2) ?>
                            </div>

                            <div class="product-actions">
                                <button 
                                    class="btn-add-cart"
                                    onclick="addToCart(<?= $p['id'] ?>)"
                                    title="Add to Cart"
                                >
                                    <i class="fas fa-shopping-cart"></i>
                                </button>

                                <a 
                                    href="product-details.php?id=<?= $p['id'] ?>" 
                                    class="btn-buy-now"
                                >
                                    Buy Now
                                </a>
                            </div>
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

<section class="checkout-container" id="checkoutSection">
  <div class="checkout-shell">
    
    <!-- LEFT -->
    <div class="checkout-main">
        <div class="checkout-steps">
          <div class="step active" id="step1">
            <span>1</span>
            <p>Review</p>
          </div>
          <div class="step" id="step2">
            <span>2</span>
            <p>Payment</p>
          </div>
          <div class="step" id="step3">
            <span>3</span>
            <p>Confirm</p>
          </div>
        </div>

      <h2 class="checkout-title">Checkout</h2>

      <!-- STEP CONTENT -->
        <div id="checkoutStep1" class="checkout-card">
          <h3>Review Your Order</h3>
          <p class="text-muted">Check your items before proceeding</p>

          <div id="checkoutCartItems"></div>

          <div class="checkout-actions">
            <button class="btn-outline" onclick="backToCatalog()">← Continue Shopping</button>
            <button class="btn-primary" onclick="goToStep(2)">Proceed to Payment →</button>
          </div>
        </div>
        <div id="checkoutStep2" class="checkout-card" style="display:none;">
          <h3>Choose Payment Method</h3>

          <div class="payment-methods">
            <div class="payment-option" onclick="selectPayment('full', this)">
              <h5>💳 Full Payment</h5>
              <p>Pay the total amount now</p>
            </div>

            <div class="payment-option" onclick="selectPayment('downpayment', this)">
              <h5>📅 50% Downpayment</h5>
              <p>Pay half now, balance after installation</p>
            </div>
          </div>

          <div id="paymentDetails" style="display:none;">
            <div class="summary-row">
              <span>Subtotal</span>
              <span id="checkoutSubtotal"></span>
            </div>

            <div class="summary-row" id="downpaymentRow" style="display:none;">
              <span>Downpayment</span>
              <span id="downpaymentAmount"></span>
            </div>

            <div class="summary-row total-row">
              <span>Total Due</span>
              <span id="checkoutTotal"></span>
            </div>
          </div>

          <div class="checkout-actions">
            <button class="btn-outline" onclick="goToStep(1)">← Back</button>
            <button id="confirmPaymentBtn"
                    class="btn-primary"
                    disabled
                    onclick="payWithMaya()">
              <i class="fas fa-wallet me-2"></i> Pay with Maya
            </button>


          </div>
        </div>

      <div id="checkoutStep3" class="checkout-card" style="display:none;">
          <div class="confirmation-box">
            <i class="fas fa-check-circle success-icon"></i>
            <h3>Order Submitted Successfully!</h3>
            <p>Thank you for choosing Solar Power Energy.</p>

            <div class="confirmation-details">
              <p><strong>Payment Method:</strong> <span id="confirmPaymentMethod"></span></p>
              <p><strong>Total Paid:</strong> <span id="confirmAmount"></span></p>
              <!-- PayMaya Info -->
                <div id="paymayaInfo" class="alert alert-primary mt-3" style="display:none;">
                  <i class="fas fa-wallet me-2"></i>
                  <strong>PayMaya</strong> (E-wallets, Bank Account)
                </div>

            </div>

            <button class="btn-primary" onclick="finishOrder()">Back to Home</button>
          </div>
        </div>
    </div>

    <!-- RIGHT -->
    <aside class="checkout-sidebar">
      <div class="summary-box">
        <h4>Order Summary</h4>
        <div id="checkoutOrderSummary"></div>
      </div>
    </aside>

  </div>
</section>


<!-- ---------- 9.  SAVINGS CALCULATOR ---------- -->
<section class="savings-calculator">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="calculator-box">
                    <div class="savings-icon"><i class="fa-regular fa-lightbulb"></i></div>
                    <h2>Let's check how much you can save!</h2>
                    <p>what's your monthly electric bill?</p>
                    <div class="row justify-content-center mb-4">
                        <div class="col-lg-3 col-md-6">
                            <div class="input-group-custom">
                                <input type="number" id="billAmount" placeholder="0" min="0" step="0.01">
                                <p>Monthly Electric Bill (₱)</p>
                            </div>
                        </div>
                    </div>
                    <button class="calculate-btn" onclick="calculateSavings()">Calculate</button>
                    <div id="errorMessage" class="error-message"></div>
                    <div id="results" class="results">
                        <div class="result-card"><div class="result-value" id="kwpValue">0.0</div><div class="result-label">kWp</div></div>
                        <div class="result-card"><div class="result-value" id="panelsValue">0</div><div class="result-label"># of panels</div></div>
                        <div class="result-card"><div class="result-value" id="monthlySavings">0</div><div class="result-label">Monthly Savings</div></div>
                        <div class="result-card"><div class="result-value" id="yearlySavings">0</div><div class="result-label">Yearly Savings</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!------ Book For Inspection ------>
<section class="book-section py-5" id="inspection-section">
    <div class="container">
        <div class="row g-0 shadow-lg rounded-4 overflow-hidden">
            <div class="col-lg-5 d-none d-lg-block" style="background: linear-gradient(rgba(44, 62, 80, 0.9), rgba(44, 62, 80, 0.9)), url('../../assets/img/solar-install.jpg') center/cover;">
                <div class="h-100 p-5 text-white d-flex flex-column justify-content-center">
                    <h2 class="display-6 fw-bold mb-4" style="color: #f39c12;">Ready to switch?</h2>
                    <p class="lead mb-4">Book a site inspection today and let our experts design the perfect solar system for your home.</p>
                    <ul class="list-unstyled">
                        <li class="mb-3"><i class="fas fa-check-circle me-2 text-warning"></i> Professional Assessment</li>
                        <li class="mb-3"><i class="fas fa-check-circle me-2 text-warning"></i> Accurate ROI Projection</li>
                        <li class="mb-3"><i class="fas fa-check-circle me-2 text-warning"></i> Custom System Design</li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-7 bg-white p-4 p-md-5">
                <div class="form-header mb-4">
                    <h2 class="fw-bold">Book Site Inspection</h2>
                    <p class="text-muted small">Fill out the details below and we'll contact you within 24 hours.</p>
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
</section>

<!-- ---------- 10.  WHY CHOOSE US ---------- -->
<section class="why-choose-us">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5"><h2>Why choose us?</h2></div>
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                    <h3>Reliable Solar Solutions</h3>
                    <p>High-quality solar panels and installations backed by comprehensive warranties and expert support</p>
                    <button class="btn-learn-more">Learn More</button>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-certificate"></i></div>
                    <h3>Certified Installers</h3>
                    <p>Professional and Certified Installers</p>
                    <button class="btn-learn-more">Learn More</button>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                    <h3>Lower Energy Costs</h3>
                    <p>Reduce your electricity bills significantly and achieve energy independence with solar technology</p>
                    <button class="btn-learn-more">Learn More</button>
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- Floating Messenger Button -->
        <a href="https://m.me/757917280729034"
           class="messenger-float"
           target="_blank"
           aria-label="Chat with us on Messenger">
            <i class="fab fa-facebook-messenger"></i>
        </a>x

<!-- ---------- 11.  FOOTER ---------- -->
<?php include "../../includes/footer.php"; ?>

<!-- ---------- 12.  SCRIPTS ---------- -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/script.js"></script>

<!-- ---------- 13.  SMALL UTILITY SCRIPTS ---------- -->
<script>
/* nav background switch */
window.addEventListener('scroll', () => {
    const header = document.getElementById('mainHeader');
    const featured = document.getElementById('featured-brands');
    if (!header || !featured) return;
    window.scrollY >= featured.offsetTop - 120
        ? header.classList.add('scrolled')
        : header.classList.remove('scrolled');
});

/* savings calculator */
function calculateSavings(){
    const bill = parseFloat(document.getElementById('billAmount').value);
    const err  = document.getElementById('errorMessage');
    const res  = document.getElementById('results');
    if (!bill || bill <= 0) { err.textContent = 'Please enter a valid electric bill amount'; res.classList.remove('show'); return; }
    err.textContent = '';
    const avgRate = 13.40, sunH = 4.5, eff = 0.85, panelW = 705, saveP = 0.95;
    const monthlyKWh = bill / avgRate, dailyKWh = monthlyKWh / 30;
    const kwp = dailyKWh / (sunH * eff), panels = Math.ceil((kwp * 1000) / panelW);
    const monthlySave = bill * saveP, yearlySave = monthlySave * 12;
    document.getElementById('kwpValue').textContent = kwp.toFixed(1);
    document.getElementById('panelsValue').textContent = panels;
    document.getElementById('monthlySavings').textContent = '₱' + monthlySave.toLocaleString('en-PH', {maximumFractionDigits: 0});
    document.getElementById('yearlySavings').textContent  = '₱' + yearlySave.toLocaleString('en-PH', {maximumFractionDigits: 0});
    res.classList.add('show');
}
document.getElementById('billAmount')?.addEventListener('keypress', e => { if (e.key === 'Enter') calculateSavings(); });

/* carousel */
let currentIndex = 0;
function moveCarousel(dir){
    const track = document.getElementById('catalogTrack');
    if (!track) return;
    const cards = track.querySelectorAll('.catalog-card');
    if (!cards.length) return;
    const perView = window.innerWidth <= 768 ? 1 : window.innerWidth <= 992 ? 2 : 3;
    const maxIndex = cards.length - perView;
    currentIndex = Math.max(0, Math.min(maxIndex, currentIndex + dir));
    const gap = 30, w = cards[0].offsetWidth;
    track.style.transform = `translateX(${-currentIndex * (w + gap)}px)`;
}
window.addEventListener('resize', () => { currentIndex = 0; moveCarousel(0); });
</script>

<!-- ---------- 14.  CART & CHECKOUT LOGIC ---------- -->
<script>

/* ======  BOOK-INSPECTION FLOW  ====== */
document.addEventListener('DOMContentLoaded', () => {
  /* make “Book for Inspection” button open the modal */
  const btn = document.querySelector('.hero-cta .btn-secondary'); // the 2nd hero button
  if (btn) btn.setAttribute('data-bs-toggle','modal'), btn.setAttribute('data-bs-target','#inspectionModal');

  /* handle submit */
  const form = document.getElementById('inspectionForm');
  form.addEventListener('submit', async e => {
    e.preventDefault();
    const msgBox = document.getElementById('inspectionMsg');
    const btnText = form.querySelector('.btn-text');
    const spinner = form.querySelector('.spinner-border');

    btnText.classList.add('d-none');
    spinner.classList.remove('d-none');

    const res = await fetch('../../controllers/ajax-mail-inspection.php', {
      method : 'POST',
      body   : new FormData(form)
    }).then(r => r.json());

    spinner.classList.add('d-none');
    btnText.classList.remove('d-none');

    msgBox.className = 'mt-3 alert alert-' + (res.success ? 'success' : 'danger');
    msgBox.textContent = res.msg;
    if (res.success) form.reset();
  });
});

function submitInspection(e) {
    e.preventDefault();

    const form = e.target;
    const btn = document.getElementById('inspectionBtn');
    const text = btn.querySelector('.btn-text');
    const spinner = btn.querySelector('.spinner-border');

    btn.disabled = true;
    text.textContent = "Sending...";
    spinner.classList.remove('d-none');

    fetch("../../controllers/send-inspection-email.php", {
        method: "POST",
        body: new FormData(form)
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) form.reset();
    })
    .catch(() => {
        alert("Failed to send request. Please try again.");
    })
    .finally(() => {
        btn.disabled = false;
        text.textContent = "Submit Request";
        spinner.classList.add('d-none');
    });
}

// View More Toggle
function toggleViewMore() {
    const hiddenProducts = document.querySelectorAll('.hidden-product');
    const viewMoreBtn = document.getElementById('viewMoreBtn');
    const categoryFilters = document.getElementById('categoryFilters');
    const btnIcon = viewMoreBtn.querySelector('i');
    const btnText = viewMoreBtn.querySelector('span');
    
    if (viewMoreBtn.classList.contains('expanded')) {
        // Collapse - hide products beyond first 4
        hiddenProducts.forEach(product => {
            product.classList.remove('show-product');
        });
        
        // Hide category filters
        categoryFilters.style.display = 'none';
        
        // Update button
        viewMoreBtn.classList.remove('expanded');
        btnText.textContent = 'View More Products';
        
        // Smooth scroll to catalog section
        document.getElementById('catalogSection').scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
        });
    } else {
        // Expand - show all products
        hiddenProducts.forEach((product, index) => {
            setTimeout(() => {
                product.classList.add('show-product');
            }, index * 50); // Stagger animation
        });
        
        // Show category filters with animation
        categoryFilters.style.display = 'flex';
        
        // Update button
        viewMoreBtn.classList.add('expanded');
        btnText.textContent = 'View Less';
    }
}

// Category Filter
document.addEventListener('DOMContentLoaded', () => {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const viewMoreBtn = document.getElementById('viewMoreBtn');
    const productsGrid = document.getElementById('productsGrid');

    // 1. Initial State: Run filter once to set the 6-item limit on page load
    applyFiltersAndViewMode('all');

    // 2. Category Filter Event Listeners
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const category = btn.getAttribute('data-category');
            
            // UI Update: Active Button
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            // Run unified filter
            applyFiltersAndViewMode(category);
        });
    });
});

function applyFilters() {
    const activeBtn = document.querySelector('.filter-btn.active');
    const category = activeBtn ? activeBtn.getAttribute('data-category').toLowerCase() : 'all';
    const viewMoreBtn = document.getElementById('viewMoreBtn');
    const isExpanded = viewMoreBtn.classList.contains('expanded');
    const products = document.querySelectorAll('.product-card');
    
    let visibleCount = 0;

    products.forEach((product) => {
        const productCategory = product.getAttribute('data-category').toLowerCase();
        const matchesCategory = (category === 'all' || productCategory === category);

        if (matchesCategory) {
            visibleCount++;
            
            // Logic: 
            // 1. If category is NOT "all", show everything that matches
            // 2. If category IS "all" and NOT expanded, hide items > 4 (CHANGED FROM 6)
            if (category === 'all' && !isExpanded && visibleCount > 4) {
                product.style.display = 'none';
                product.classList.remove('show-product');
            } else {
                product.style.display = 'block';
                setTimeout(() => product.classList.add('show-product'), 10);
            }
        } else {
            product.style.display = 'none';
            product.classList.remove('show-product');
        }
    });

    // Toggle "View More" button visibility
    // Only show the "View More" button if we are in "All" category and have > 4 items
    const viewMoreContainer = document.getElementById('viewMoreContainer');
    if (category === 'all' && visibleCount > 4 || (category === 'all' && isExpanded)) {
        viewMoreContainer.style.display = 'block';
    } else {
        viewMoreContainer.style.display = 'none';
    }

    handleNoResults(visibleCount);
}


document.getElementById('sortSelect').addEventListener('change', function () {
    const value = this.value;
    const grid = document.getElementById('productsGrid');
    const cards = Array.from(grid.querySelectorAll('.product-card'));

    let sortedCards = [...cards];

    switch (value) {
        case 'price-low':
            sortedCards.sort((a, b) =>
                parseFloat(a.dataset.price) - parseFloat(b.dataset.price)
            );
            break;

        case 'price-high':
            sortedCards.sort((a, b) =>
                parseFloat(b.dataset.price) - parseFloat(a.dataset.price)
            );
            break;

        case 'name-asc':
            sortedCards.sort((a, b) =>
                a.dataset.name.localeCompare(b.dataset.name)
            );
            break;

        case 'name-desc':
            sortedCards.sort((a, b) =>
                b.dataset.name.localeCompare(a.dataset.name)
            );
            break;

        default:
            sortedCards = cards; // original order
    }

    // Re-append sorted cards
    grid.innerHTML = '';
    sortedCards.forEach(card => grid.appendChild(card));
});

function filterProducts(category) {
    const allProducts = document.querySelectorAll('.product-card');

    allProducts.forEach((product, index) => {
        const productCategory = product.getAttribute('data-category');

        if (
            category === 'all' ||
            productCategory.toLowerCase() === category.toLowerCase()
        ) {
            // Show product with stagger animation
            setTimeout(() => {
                product.style.display = 'block';
                product.style.animation = 'fadeInUp 0.6s ease';
            }, index * 30);
        } else {
            product.style.display = 'none';
        }
    });

    // Check if no products match
    setTimeout(() => {
        const visibleProducts = Array.from(
            document.querySelectorAll('.product-card')
        ).filter(p => p.style.display === 'block');

        const productsGrid = document.getElementById('productsGrid');

        // Remove existing no-results message
        const existingMsg = document.querySelector('.no-results-message');
        if (existingMsg) existingMsg.remove();

        if (visibleProducts.length === 0) {
            const noResultsMsg = document.createElement('div');
            noResultsMsg.className = 'no-products no-results-message';
            noResultsMsg.innerHTML = `
                <i class="fas fa-search"></i>
                <p>No products found in this category</p>
            `;
            productsGrid.appendChild(noResultsMsg);
        }
    }, 500);
}


document.addEventListener('DOMContentLoaded', () => {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const products = document.querySelectorAll('.product-card');

    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Active state
            filterButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');

            const category = button.dataset.category;

            products.forEach(product => {
                const productCategory = product.dataset.category;

                if (category === 'all' || productCategory === category) {
                    product.style.display = 'block';
                } else {
                    product.style.display = 'none';
                }
            });

            handleNoResults(category);
        });
    });
});

function handleNoResults(visibleCount) {
    const grid = document.getElementById('productsGrid');
    const existing = document.querySelector('.no-results-message');
    if (existing) existing.remove();

    if (visibleCount === 0) {
        const msg = document.createElement('div');
        msg.className = 'no-products no-results-message';
        msg.innerHTML = `
            <i class="fas fa-search"></i>
            <p>No products found</p>
        `;
        grid.appendChild(msg);
    }
}

// Function to navigate to product details
function goToProductDetails(productId) {
    window.location.href = `product-details.php?id=${productId}`;
}

// Updated Add to Cart - prevents card click
function addToCart(productId) {
    const productCard = document.querySelector(`.product-card [onclick*="addToCart(${productId})"]`).closest('.product-card');
    
    const product = {
        id: productId,
        name: productCard.querySelector('.product-name').textContent,
        brand: productCard.querySelector('.product-brand').textContent,
        category: productCard.querySelector('.product-badge').textContent,
        price: parseFloat(productCard.querySelector('.product-price').textContent.replace('₱', '').replace(',', '')),
        image: productCard.querySelector('.product-image img').src,
        quantity: 1
    };

    const existingIndex = cart.findIndex(item => item.id === productId);
    
    if (existingIndex > -1) {
        cart[existingIndex].quantity++;
    } else {
        cart.push(product);
    }

    localStorage.setItem('solarCart', JSON.stringify(cart));
    updateCartBadge();
    
    const btn = event.target.closest('.btn-add-cart');
    const originalHTML = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-check"></i>';
    btn.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
    
    setTimeout(() => {
        btn.innerHTML = originalHTML;
        btn.style.background = '';
    }, 1500);
    
    showCartNotification('Product added to cart!');
}

// Updated Buy Now - prevents card click
function buyNow(productId) {
    const productCard = document.querySelector(`.product-card [onclick*="buyNow(${productId})"]`).closest('.product-card');
    
    const product = {
        id: productId,
        name: productCard.querySelector('.product-name').textContent,
        brand: productCard.querySelector('.product-brand').textContent,
        category: productCard.querySelector('.product-badge').textContent,
        price: parseFloat(productCard.querySelector('.product-price').textContent.replace('₱', '').replace(',', '')),
        image: productCard.querySelector('.product-image img').src,
        quantity: 1
    };

    cart = [product];
    localStorage.setItem('solarCart', JSON.stringify(cart));
    updateCartBadge();
    
    showCheckout();
}

// Add to Cart Function (placeholder - integrate with your cart system)
function addToCart(productId) {
    // Add your cart logic here
    console.log('Adding product to cart:', productId);
    
    // Show feedback
    const btn = event.target.closest('.btn-add-cart');
    const originalHTML = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-check"></i>';
    btn.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
    
    setTimeout(() => {
        btn.innerHTML = originalHTML;
        btn.style.background = '';
    }, 1500);
    
    // You can also show a toast notification here
    showCartNotification('Product added to cart!');
}

// Cart Notification (optional)
function showCartNotification(message) {
    // Remove existing notification
    const existing = document.querySelector('.cart-notification');
    if (existing) existing.remove();
    
    const notification = document.createElement('div');
    notification.className = 'cart-notification';
    notification.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <span>${message}</span>
    `;
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 16px 24px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 600;
        animation: slideInRight 0.4s ease;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.4s ease';
        setTimeout(() => notification.remove(), 400);
    }, 3000);
}

// Add animation keyframes
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);


/* ========== COMPLETE CHECKOUT & CART SYSTEM ========== */

// Cart Storage
let cart = JSON.parse(localStorage.getItem('solarCart')) || [];

// Add to Cart Function
function addToCart(productId) {
    const productCard = document.querySelector(`.product-card [onclick="addToCart(${productId})"]`).closest('.product-card');
    
    const product = {
        id: productId,
        name: productCard.querySelector('.product-name').textContent,
        brand: productCard.querySelector('.product-brand').textContent,
        category: productCard.querySelector('.product-badge').textContent,
        price: parseFloat(productCard.querySelector('.product-price').textContent.replace('₱', '').replace(',', '')),
        image: productCard.querySelector('.product-image img').src,
        quantity: 1
    };

    // Check if product already in cart
    const existingIndex = cart.findIndex(item => item.id === productId);
    
    if (existingIndex > -1) {
        cart[existingIndex].quantity++;
    } else {
        cart.push(product);
    }

    // Save to localStorage
    localStorage.setItem('solarCart', JSON.stringify(cart));
    
    // Update cart badge
    updateCartBadge();
    
    // Visual feedback
    const btn = event.target.closest('.btn-add-cart');
    const originalHTML = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-check"></i>';
    btn.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
    
    setTimeout(() => {
        btn.innerHTML = originalHTML;
        btn.style.background = '';
    }, 1500);
    
    showCartNotification('Product added to cart!');
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

// Buy Now Function - Direct to Checkout
function buyNow(productId) {
    // Get product details
    const productCard = document.querySelector(`[href="product-details.php?id=${productId}"]`).closest('.product-card');
    
    const product = {
        id: productId,
        name: productCard.querySelector('.product-name').textContent,
        brand: productCard.querySelector('.product-brand').textContent,
        category: productCard.querySelector('.product-badge').textContent,
        price: parseFloat(productCard.querySelector('.product-price').textContent.replace('₱', '').replace(',', '')),
        image: productCard.querySelector('.product-image img').src,
        quantity: 1
    };

    // Clear cart and add this product
    cart = [product];
    localStorage.setItem('solarCart', JSON.stringify(cart));
    
    // Go directly to checkout
    showCheckout();
}

// Show Checkout Section - FIXED VERSION
function showCheckout() {
    if (cart.length === 0) {
        alert('Your cart is empty!');
        return;
    }

    // Hide all main sections
    document.getElementById('catalogSection').style.display = 'none';
    document.getElementById('featured-brands').style.display = 'none';
    document.querySelector('.savings-calculator').style.display = 'none';
    document.querySelector('.why-choose-us').style.display = 'none';
    document.querySelector('.book-section').style.display = 'none';
    
    // Show checkout section
    const checkoutSection = document.getElementById('checkoutSection');
    checkoutSection.classList.add('active');
    
    // Reset to step 1
    goToStep(1);
    
    // Render cart items
    renderCheckoutCart();
    
    // Scroll to checkout
    setTimeout(() => {
        checkoutSection.scrollIntoView({ behavior: 'smooth' });
    }, 100);
}

// Back to Catalog - FIXED VERSION
function backToCatalog() {
    // Hide checkout
    const checkoutSection = document.getElementById('checkoutSection');
    checkoutSection.classList.remove('active');
    
    // Show all main sections again
    document.getElementById('catalogSection').style.display = 'block';
    document.getElementById('featured-brands').style.display = 'block';
    document.querySelector('.savings-calculator').style.display = 'block';
    document.querySelector('.why-choose-us').style.display = 'block';
    document.querySelector('.book-section').style.display = 'block';
    
    // Scroll to catalog
    setTimeout(() => {
        document.getElementById('catalogSection').scrollIntoView({ behavior: 'smooth' });
    }, 100);
}

// Finish Order - Return to home
function finishOrder() {
    // Hide checkout
    const checkoutSection = document.getElementById('checkoutSection');
    checkoutSection.classList.remove('active');
    
    // Show all main sections
    document.getElementById('catalogSection').style.display = 'block';
    document.getElementById('featured-brands').style.display = 'block';
    document.querySelector('.savings-calculator').style.display = 'block';
    document.querySelector('.why-choose-us').style.display = 'block';
    document.querySelector('.book-section').style.display = 'block';
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Buy Now Function - Direct to Checkout - FIXED VERSION
function buyNow(productId) {
    // Get product details
    const productCard = document.querySelector(`[href="product-details.php?id=${productId}"]`).closest('.product-card');
    
    const product = {
        id: productId,
        name: productCard.querySelector('.product-name').textContent,
        brand: productCard.querySelector('.product-brand').textContent,
        category: productCard.querySelector('.product-badge').textContent,
        price: parseFloat(productCard.querySelector('.product-price').textContent.replace('₱', '').replace(',', '')),
        image: productCard.querySelector('.product-image img').src,
        quantity: 1
    };

    // Clear cart and add this product
    cart = [product];
    localStorage.setItem('solarCart', JSON.stringify(cart));
    updateCartBadge();
    
    // Go directly to checkout
    showCheckout();
}
// Render Cart in Checkout
function renderCheckoutCart() {
    const container = document.getElementById('checkoutOrderSummary');
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    
    let html = '<div class="checkout-items">';
    
    cart.forEach((item, index) => {
        html += `
            <div class="checkout-item">
                <img src="${item.image}" alt="${item.name}" class="checkout-item-image">
                <div class="checkout-item-details">
                    <h4>${item.name}</h4>
                    <p class="text-muted">${item.brand} | ${item.category}</p>
                    <div class="d-flex align-items-center gap-3 mt-2">
                        <div class="quantity-control">
                            <button onclick="updateQuantity(${index}, -1)" class="qty-btn-checkout">-</button>
                            <span class="qty-display">${item.quantity}</span>
                            <button onclick="updateQuantity(${index}, 1)" class="qty-btn-checkout">+</button>
                        </div>
                        <button onclick="removeFromCart(${index})" class="btn-remove-checkout">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </div>
                </div>
                <div class="checkout-item-price">
                    ₱${(item.price * item.quantity).toLocaleString('en-PH', {minimumFractionDigits: 2})}
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    html += `
        <div class="checkout-summary-total">
            <div class="summary-row">
                <span>Subtotal (${cart.reduce((sum, item) => sum + item.quantity, 0)} items):</span>
                <span class="fw-bold">₱${subtotal.toLocaleString('en-PH', {minimumFractionDigits: 2})}</span>
            </div>
            <div class="summary-row total-row">
                <span>Total:</span>
                <span>₱${subtotal.toLocaleString('en-PH', {minimumFractionDigits: 2})}</span>
            </div>
        </div>
    `;
    
    container.innerHTML = html;
}

// Update Quantity
function updateQuantity(index, change) {
    cart[index].quantity += change;
    
    if (cart[index].quantity <= 0) {
        cart.splice(index, 1);
    }
    
    localStorage.setItem('solarCart', JSON.stringify(cart));
    updateCartBadge();
    renderCheckoutCart();
    
    if (cart.length === 0) {
        backToCatalog();
    }
}

// Remove from Cart
function removeFromCart(index) {
    cart.splice(index, 1);
    localStorage.setItem('solarCart', JSON.stringify(cart));
    updateCartBadge();
    renderCheckoutCart();
    
    if (cart.length === 0) {
        backToCatalog();
        alert('Your cart is empty!');
    }
}

// Back to Catalog
function backToCatalog() {
    document.getElementById('checkoutSection').style.display = 'none';
    document.getElementById('catalogSection').style.display = 'block';
    document.getElementById('catalogSection').scrollIntoView({ behavior: 'smooth' });
}

// Step Navigation
function goToStep(step) {
    // Hide all steps
    for (let i = 1; i <= 3; i++) {
        document.getElementById(`checkoutStep${i}`).style.display = 'none';
        document.getElementById(`step${i}`).classList.remove('active', 'completed');
    }
    
    // Show current step
    document.getElementById(`checkoutStep${step}`).style.display = 'block';
    document.getElementById(`step${step}`).classList.add('active');
    
    // Mark previous steps as completed
    for (let i = 1; i < step; i++) {
        document.getElementById(`step${i}`).classList.add('completed');
    }
    
    if (step === 2) {
        setupPaymentStep();
    } else if (step === 3) {
        setupConfirmationStep();
    }
}

// Setup Payment Step
let selectedPaymentMethod = null;
let selectedPaymentOption = null;

function setupPaymentStep() {
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    
    // Reset payment selection
    selectedPaymentMethod = null;
    selectedPaymentOption = null;
    document.getElementById('paymentDetails').style.display = 'none';
    document.getElementById('confirmPaymentBtn').disabled = true;
    
    // Remove old payment details if exists
    const oldDetails = document.getElementById('paymentOptionsDetails');
    if (oldDetails) oldDetails.remove();
}

// Select Payment Method (Full Payment or Downpayment)
function selectPayment(method, el) {
    selectedPaymentMethod = method;

    document.querySelectorAll('.payment-option')
        .forEach(opt => opt.classList.remove('selected'));

    el.classList.add('selected');

    const subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);

    document.getElementById('paymayaInfo').style.display = 'block';
    document.getElementById('paymentDetails').style.display = 'block';

    let total = subtotal;

    if (method === 'downpayment') {
        total = subtotal * 0.5;
        document.getElementById('downpaymentRow').style.display = 'flex';
        document.getElementById('downpaymentAmount').textContent =
            '₱' + total.toLocaleString('en-PH', { minimumFractionDigits: 2 });
    } else {
        document.getElementById('downpaymentRow').style.display = 'none';
    }

    document.getElementById('checkoutSubtotal').textContent =
        '₱' + subtotal.toLocaleString('en-PH', { minimumFractionDigits: 2 });

    document.getElementById('checkoutTotal').textContent =
        '₱' + total.toLocaleString('en-PH', { minimumFractionDigits: 2 });

    document.getElementById('confirmPaymentBtn').disabled = false;
}


async function payWithMaya() {
    if (!selectedPaymentMethod) {
        alert('Please select a payment option');
        return;
    }

    const subtotal = cart.reduce((s, i) => s + i.price * i.quantity, 0);
    const amount = selectedPaymentMethod === 'downpayment'
        ? subtotal * 0.5
        : subtotal;

    try {
        const res = await fetch('../../controllers/create-maya-checkout.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                payment_type: selectedPaymentMethod,
                amount,
                cart
            })
        });

        const data = await res.json();

        if (data.checkoutUrl) {
            window.location.href = data.checkoutUrl;
        } else {
            alert('Payment initialization failed');
        }
    } catch (e) {
        alert('Unable to connect to payment gateway');
    }
}


// Show Payment Channel Options (PayMaya, Bank Transfer)
function showPaymentChannels() {
    let existingChannels = document.getElementById('paymentOptionsDetails');
    if (!existingChannels) {
        existingChannels = document.createElement('div');
        existingChannels.id = 'paymentOptionsDetails';
        existingChannels.className = 'payment-channels mt-4';
        document.getElementById('paymentDetails').appendChild(existingChannels);
    }
    
    existingChannels.innerHTML = `
        <h5 class="mb-3"><i class="fas fa-credit-card"></i> Select Payment Channel</h5>
        <div class="payment-channels-grid">
            <div class="payment-channel-card" onclick="selectPaymentChannel('paymaya')">
                <div class="channel-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h6>PayMaya / Maya</h6>
                <p class="small text-muted">E-Wallet Payment</p>
            </div>
            
            <div class="payment-channel-card" onclick="selectPaymentChannel('gcash')">
                <div class="channel-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <h6>GCash</h6>
                <p class="small text-muted">E-Wallet Payment</p>
            </div>
            
            <div class="payment-channel-card" onclick="selectPaymentChannel('bank')">
                <div class="channel-icon">
                    <i class="fas fa-university"></i>
                </div>
                <h6>Bank Transfer</h6>
                <p class="small text-muted">BDO, BPI, Metrobank</p>
            </div>
            
            <div class="payment-channel-card" onclick="selectPaymentChannel('card')">
                <div class="channel-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <h6>Credit/Debit Card</h6>
                <p class="small text-muted">Visa, Mastercard</p>
            </div>
        </div>
        
        <div id="channelDetails" class="channel-details mt-4" style="display: none;"></div>
    `;
}

// Select Payment Channel
function selectPaymentChannel(channel) {
    selectedPaymentOption = channel;
    
    // Update UI
    document.querySelectorAll('.payment-channel-card').forEach(card => card.classList.remove('selected'));
    event.target.closest('.payment-channel-card').classList.add('selected');
    
    // Show channel-specific details
    const channelDetails = document.getElementById('channelDetails');
    channelDetails.style.display = 'block';
    
    const amount = selectedPaymentMethod === 'downpayment' 
        ? cart.reduce((sum, item) => sum + (item.price * item.quantity), 0) * 0.5
        : cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    
    let detailsHTML = '';
    
    switch(channel) {
        case 'paymaya':
        case 'gcash':
            detailsHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>E-Wallet Payment Instructions</strong>
                    <ol class="mt-2 mb-0">
                        <li>Open your ${channel === 'paymaya' ? 'Maya' : 'GCash'} app</li>
                        <li>Send ₱${amount.toLocaleString('en-PH', {minimumFractionDigits: 2})} to: <strong>0917-123-4567</strong></li>
                        <li>Upload proof of payment below</li>
                    </ol>
                </div>
                <div class="upload-section">
                    <label class="form-label fw-bold">Upload Payment Proof</label>
                    <input type="file" class="form-control" id="paymentProof" accept="image/*" onchange="handleProofUpload()">
                    <small class="text-muted">Accepted formats: JPG, PNG (Max 5MB)</small>
                </div>
            `;
            break;
            
        case 'bank':
            detailsHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Bank Transfer Instructions</strong>
                    <div class="bank-accounts mt-3">
                        <div class="bank-item">
                            <strong>BDO:</strong> 1234-5678-9012 (Juan Dela Cruz)
                        </div>
                        <div class="bank-item">
                            <strong>BPI:</strong> 9876-5432-1098 (Juan Dela Cruz)
                        </div>
                        <div class="bank-item">
                            <strong>Metrobank:</strong> 5555-6666-7777 (Juan Dela Cruz)
                        </div>
                    </div>
                    <p class="mt-2">Amount to transfer: <strong>₱${amount.toLocaleString('en-PH', {minimumFractionDigits: 2})}</strong></p>
                </div>
                <div class="upload-section">
                    <label class="form-label fw-bold">Upload Bank Receipt</label>
                    <input type="file" class="form-control" id="paymentProof" accept="image/*" onchange="handleProofUpload()">
                    <small class="text-muted">Accepted formats: JPG, PNG (Max 5MB)</small>
                </div>
            `;
            break;
            
        case 'card':
            detailsHTML = `
                <div class="card-payment-form">
                    <div class="alert alert-warning">
                        <i class="fas fa-shield-alt"></i> Your payment is secured with SSL encryption
                    </div>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label">Card Number</label>
                            <input type="text" class="form-control" id="cardNumber" placeholder="1234 5678 9012 3456" maxlength="19">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Expiry Date</label>
                            <input type="text" class="form-control" id="cardExpiry" placeholder="MM/YY" maxlength="5">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">CVV</label>
                            <input type="text" class="form-control" id="cardCVV" placeholder="123" maxlength="3">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Cardholder Name</label>
                            <input type="text" class="form-control" id="cardName" placeholder="JUAN DELA CRUZ">
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('confirmPaymentBtn').disabled = false;
            break;
    }
    
    channelDetails.innerHTML = detailsHTML;
    
    // Enable confirm button only after proof upload (except for card)
    if (channel !== 'card') {
        document.getElementById('confirmPaymentBtn').disabled = true;
    }
}

// Handle Proof Upload
let uploadedProof = null;

function handleProofUpload() {
    const fileInput = document.getElementById('paymentProof');
    const file = fileInput.files[0];
    
    if (file) {
        if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB');
            fileInput.value = '';
            return;
        }
        
        uploadedProof = file;
        document.getElementById('confirmPaymentBtn').disabled = false;
        
        showCartNotification('Payment proof uploaded successfully!');
    }
}


// Setup Confirmation Step
function setupConfirmationStep() {
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const amount = selectedPaymentMethod === 'downpayment' ? subtotal * 0.5 : subtotal;
    const orderNumber = 'SPE-' + Date.now();
    
    // Render order summary
    let itemsHTML = '<div class="final-order-items">';
    cart.forEach(item => {
        itemsHTML += `
            <div class="final-order-item">
                <img src="${item.image}" alt="${item.name}">
                <div class="final-item-info">
                    <h6>${item.name}</h6>
                    <p class="text-muted">${item.brand} | Qty: ${item.quantity}</p>
                </div>
                <div class="final-item-price">
                    ₱${(item.price * item.quantity).toLocaleString('en-PH', {minimumFractionDigits: 2})}
                </div>
            </div>
        `;
    });
    itemsHTML += '</div>';
    
    document.getElementById('finalOrderSummary').innerHTML = itemsHTML;
    document.getElementById('orderNumber').textContent = orderNumber;
    
    const paymentMethodText = selectedPaymentMethod === 'downpayment' 
        ? `50% Downpayment via ${selectedPaymentOption.toUpperCase()}`
        : `Full Payment via ${selectedPaymentOption.toUpperCase()}`;
    
    document.getElementById('finalPaymentMethod').textContent = paymentMethodText;
    document.getElementById('finalAmount').textContent = '₱' + amount.toLocaleString('en-PH', {minimumFractionDigits: 2});
    
    // Here you would typically send the order to your backend
    saveOrder({
        orderNumber,
        items: cart,
        subtotal,
        paymentMethod: selectedPaymentMethod,
        paymentChannel: selectedPaymentOption,
        amountPaid: amount,
        proof: uploadedProof
    });
    
    // Clear cart
    cart = [];
    localStorage.setItem('solarCart', JSON.stringify(cart));
    updateCartBadge();
}

// Save Order to Backend (placeholder)
function saveOrder(orderData) {
    console.log('Order Data:', orderData);
    
    // Send to backend
    // fetch('../../controllers/save-order.php', {
    //     method: 'POST',
    //     body: JSON.stringify(orderData),
    //     headers: {'Content-Type': 'application/json'}
    // }).then(res => res.json()).then(data => {
    //     console.log('Order saved:', data);
    // });
}

// Cart Notification
function showCartNotification(message) {
    const existing = document.querySelector('.cart-notification');
    if (existing) existing.remove();
    
    const notification = document.createElement('div');
    notification.className = 'cart-notification';
    notification.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <span>${message}</span>
    `;
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 16px 24px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 600;
        animation: slideInRight 0.4s ease;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.4s ease';
        setTimeout(() => notification.remove(), 400);
    }, 3000);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    updateCartBadge();
    
    // Update all "View Details" buttons to "Buy Now" with proper functionality
    document.querySelectorAll('.btn-buy-now').forEach(btn => {
        const productId = new URLSearchParams(btn.href.split('?')[1]).get('id');
        btn.onclick = (e) => {
            e.preventDefault();
            buyNow(productId);
        };
    });
});
</script>
</body>
</html>