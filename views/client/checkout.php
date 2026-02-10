<?php
session_start();
// Database connection
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "solar_power";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Kunin ang Product ID mula sa URL (e.g., checkout.php?id=1)
$product_id = isset($_GET['id']) ? int_get($_GET['id']) : 0;
$product = null;

if ($product_id > 0) {
    $sql = "SELECT p.*, pi.image_path 
            FROM product p 
            LEFT JOIN product_images pi ON p.id = pi.product_id 
            WHERE p.id = ? GROUP BY p.id";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Solar Power Energy</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../assets/style.css">
    <style>
        :root {
            --clr-primary: #ffc107;
            --clr-dark: #2d3436;
        }
        .checkout-container { padding: 60px 0; background: #f4f7f6; min-height: 100vh; }
        .step-indicator { display: flex; justify-content: space-between; margin-bottom: 30px; position: relative; }
        .step-indicator::before { content: ""; position: absolute; top: 15px; left: 0; right: 0; height: 2px; background: #ddd; z-index: 1; }
        .step { width: 35px; height: 35px; border-radius: 50%; background: #fff; border: 2px solid #ddd; display: flex; align-items: center; justify-content: center; z-index: 2; font-weight: bold; transition: 0.3s; }
        .step.active { background: var(--clr-primary); border-color: var(--clr-primary); color: #fff; }
        .checkout-card { background: #fff; border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .payment-option { cursor: pointer; border: 2px solid #eee; transition: 0.3s; }
        .payment-option:hover, .payment-option.selected { border-color: var(--clr-primary); background: #fffdf5; }
        .product-summary-img { width: 80px; height: 80px; object-fit: cover; border-radius: 10px; }
    </style>
</head>
<body>

    <?php include "../../includes/header.php"; ?>

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

<!-- ---------- 11.  FOOTER ---------- -->
<?php include "../../includes/footer.php"; ?>

<!-- ---------- 12.  SCRIPTS ---------- -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/script.js"></script>


    <script>
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
    </script>
</body>
</html>