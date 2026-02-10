let cart = JSON.parse(localStorage.getItem('solarCart')) || [];
let selectedPaymentMethod = null;

document.addEventListener('DOMContentLoaded', () => {
    initializeFilters();
    initializeSorting();
    updateCartBadge();
    setupCheckoutSystem();
});

function initializeFilters() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Update active state
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const category = this.getAttribute('data-category');
            filterProducts(category);
        });
    });
}

function filterProducts(category) {
    const allProducts = document.querySelectorAll('.product-card');
    let visibleCount = 0;
    
    allProducts.forEach((product, index) => {
        const productCategory = product.getAttribute('data-category');
        
        if (category === 'all' || productCategory === category) {
            product.style.display = 'flex';
            // Add stagger animation
            setTimeout(() => {
                product.style.opacity = '1';
                product.style.transform = 'translateY(0)';
            }, index * 50);
            visibleCount++;
        } else {
            product.style.display = 'none';
        }
    });
    
    checkNoProducts(visibleCount);
}

function checkNoProducts(visibleCount) {
    const productsGrid = document.getElementById('productsGrid');
    const existingMsg = document.querySelector('.no-results-message');
    
    if (existingMsg) existingMsg.remove();
    
    if (visibleCount === 0) {
        const noResultsMsg = document.createElement('div');
        noResultsMsg.className = 'no-products no-results-message';
        noResultsMsg.innerHTML = `
            <i class="fas fa-search"></i>
            <p>No products found in this category</p>
            <button onclick="resetFilters()" class="btn-view-more" style="margin-top: 20px;">
                View All Products
            </button>
        `;
        productsGrid.appendChild(noResultsMsg);
    }
}

function resetFilters() {
    const allBtn = document.querySelector('.filter-btn[data-category="all"]');
    if (allBtn) allBtn.click();
}

function initializeSorting() {
    const sortSelect = document.getElementById('sortSelect');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            sortProducts(this.value);
        });
    }
}

function sortProducts(sortType) {
    const grid = document.getElementById('productsGrid');
    const products = Array.from(grid.querySelectorAll('.product-card:not(.no-products)'));
    
    products.sort((a, b) => {
        const priceA = parseFloat(a.getAttribute('data-price'));
        const priceB = parseFloat(b.getAttribute('data-price'));
        const nameA = a.getAttribute('data-name').toLowerCase();
        const nameB = b.getAttribute('data-name').toLowerCase();
        
        switch(sortType) {
            case 'price-low':
                return priceA - priceB;
            case 'price-high':
                return priceB - priceA;
            case 'name-asc':
                return nameA.localeCompare(nameB);
            case 'name-desc':
                return nameB.localeCompare(nameA);
            default:
                return 0;
        }
    });
    
    // Re-append products in sorted order with animation
    products.forEach((product, index) => {
        setTimeout(() => {
            grid.appendChild(product);
        }, index * 30);
    });
}

function toggleViewMore() {
    const hiddenProducts = document.querySelectorAll('.hidden-product');
    const viewMoreBtn = document.getElementById('viewMoreBtn');
    const btnText = viewMoreBtn.querySelector('span');
    const btnIcon = viewMoreBtn.querySelector('i');
    
    if (viewMoreBtn.classList.contains('expanded')) {
        // Collapse
        hiddenProducts.forEach(product => {
            product.classList.remove('show-product');
        });
        
        viewMoreBtn.classList.remove('expanded');
        btnText.textContent = 'View More Products';
        btnIcon.style.transform = 'rotate(0deg)';
        
        // Smooth scroll to catalog
        document.getElementById('catalogSection').scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
        });
    } else {
        // Expand
        hiddenProducts.forEach((product, index) => {
            setTimeout(() => {
                product.classList.add('show-product');
            }, index * 50);
        });
        
        viewMoreBtn.classList.add('expanded');
        btnText.textContent = 'View Less';
        btnIcon.style.transform = 'rotate(180deg)';
    }
}

// ============================================
// CHECKOUT SYSTEM
// ============================================
function setupCheckoutSystem() {
    // Setup cart icon click handler
    const cartIcon = document.querySelector('.nav-icon[onclick*="cart"]');
    if (cartIcon) {
        cartIcon.onclick = (e) => {
            e.preventDefault();
            openCheckout();
        };
    }
}

function openCheckout() {
    if (cart.length === 0) {
        showNotification('Your cart is empty', 'error');
        return;
    }
    
    // Hide catalog, show checkout
    document.getElementById('catalogSection').style.display = 'none';
    document.getElementById('checkoutSection').style.display = 'block';
    
    // Reset to step 1
    goToStep(1);
    displayCheckoutCart();
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function backToCatalog() {
    document.getElementById('checkoutSection').style.display = 'none';
    document.getElementById('catalogSection').style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function goToStep(step) {
    // Hide all steps
    for (let i = 1; i <= 3; i++) {
        document.getElementById(`checkoutStep${i}`).style.display = 'none';
        document.getElementById(`step${i}`).classList.remove('active');
    }
    
    // Show current step
    document.getElementById(`checkoutStep${step}`).style.display = 'block';
    document.getElementById(`step${step}`).classList.add('active');
    
    // Mark previous steps as completed
    for (let i = 1; i < step; i++) {
        document.getElementById(`step${i}`).classList.add('completed');
    }
    
    if (step === 2) {
        displayPaymentSummary();
    }
}

function displayCheckoutCart() {
    const summaryDiv = document.getElementById('checkoutOrderSummary');
    
    if (cart.length === 0) {
        summaryDiv.innerHTML = `
            <div style="text-align: center; padding: 40px; color: #999;">
                <i class="fas fa-shopping-cart" style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;"></i>
                <p>Your cart is empty</p>
            </div>
        `;
        return;
    }
    
    let html = '<div class="cart-items">';
    let subtotal = 0;
    
    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        
        html += `
            <div class="cart-item">
                <img src="${item.image}" alt="${item.name}" class="cart-item-image">
                <div class="cart-item-details">
                    <h4>${item.name}</h4>
                    <p class="cart-item-brand">${item.brand}</p>
                    <p class="cart-item-price">₱${item.price.toLocaleString('en-PH', {minimumFractionDigits: 2})}</p>
                </div>
                <div class="cart-item-quantity">
                    <button onclick="updateCartQuantity(${item.id}, -1)" class="qty-btn">
                        <i class="fas fa-minus"></i>
                    </button>
                    <span class="qty-display">${item.quantity}</span>
                    <button onclick="updateCartQuantity(${item.id}, 1)" class="qty-btn">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="cart-item-total">
                    <p>₱${itemTotal.toLocaleString('en-PH', {minimumFractionDigits: 2})}</p>
                    <button onclick="removeFromCart(${item.id})" class="btn-remove">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });
    
    html += `</div>
        <div class="cart-summary">
            <div class="summary-row">
                <span>Subtotal:</span>
                <span>₱${subtotal.toLocaleString('en-PH', {minimumFractionDigits: 2})}</span>
            </div>
            <div class="summary-row total">
                <span>Total:</span>
                <span>₱${subtotal.toLocaleString('en-PH', {minimumFractionDigits: 2})}</span>
            </div>
        </div>
    `;
    
    summaryDiv.innerHTML = html;
}

function displayPaymentSummary() {
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    
    document.getElementById('checkoutSubtotal').textContent = 
        '₱' + subtotal.toLocaleString('en-PH', {minimumFractionDigits: 2});
    document.getElementById('checkoutTotal').textContent = 
        '₱' + subtotal.toLocaleString('en-PH', {minimumFractionDigits: 2});
}

function selectPayment(method) {
    selectedPaymentMethod = method;
    
    // Update UI
    document.querySelectorAll('.payment-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    event.target.closest('.payment-option').classList.add('selected');
    
    // Show payment details
    document.getElementById('paymentDetails').style.display = 'block';
    
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    
    if (method === 'downpayment') {
        const downpayment = subtotal * 0.5;
        document.getElementById('downpaymentInfo').style.display = 'block';
        document.getElementById('downpaymentRow').style.display = 'flex';
        document.getElementById('downpaymentAmount').textContent = 
            '₱' + downpayment.toLocaleString('en-PH', {minimumFractionDigits: 2});
        document.getElementById('checkoutTotal').textContent = 
            '₱' + downpayment.toLocaleString('en-PH', {minimumFractionDigits: 2});
    } else {
        document.getElementById('downpaymentInfo').style.display = 'none';
        document.getElementById('downpaymentRow').style.display = 'none';
        document.getElementById('checkoutTotal').textContent = 
            '₱' + subtotal.toLocaleString('en-PH', {minimumFractionDigits: 2});
    }
    
    // Enable confirm button
    document.getElementById('confirmPaymentBtn').disabled = false;
}

function completeOrder() {
    if (!selectedPaymentMethod) {
        showNotification('Please select a payment method', 'error');
        return;
    }
    
    // Generate order number
    const orderNumber = 'SPE-' + Date.now().toString().slice(-8);
    
    // Get order details
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const finalAmount = selectedPaymentMethod === 'downpayment' ? subtotal * 0.5 : subtotal;
    
    // Display final summary
    document.getElementById('orderNumber').textContent = orderNumber;
    document.getElementById('finalPaymentMethod').textContent = 
        selectedPaymentMethod === 'downpayment' ? '50% Downpayment' : 'Online Banking';
    document.getElementById('finalAmount').textContent = 
        '₱' + finalAmount.toLocaleString('en-PH', {minimumFractionDigits: 2});
    
    // Display order items
    let itemsHtml = '<div class="final-items-list">';
    cart.forEach(item => {
        itemsHtml += `
            <div class="final-item">
                <span>${item.name} (x${item.quantity})</span>
                <span>₱${(item.price * item.quantity).toLocaleString('en-PH', {minimumFractionDigits: 2})}</span>
            </div>
        `;
    });
    itemsHtml += '</div>';
    document.getElementById('finalOrderSummary').innerHTML = itemsHtml;
    
    // Go to step 3
    goToStep(3);
    
    // Clear cart
    clearCart();
    
    // Send order email (you can implement this)
    sendOrderEmail(orderNumber, cart, finalAmount, selectedPaymentMethod);
}

function sendOrderEmail(orderNumber, items, amount, paymentMethod) {
    // Implement your email sending logic here
    console.log('Order placed:', { orderNumber, items, amount, paymentMethod });
}

// ============================================
// NOTIFICATION SYSTEM
// ============================================
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = 'cart-notification';
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    if (type === 'error') {
        notification.style.background = '#ef4444';
    }
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Make functions available globally
window.addToCart = addToCart;
window.removeFromCart = removeFromCart;
window.updateCartQuantity = updateCartQuantity;
window.toggleViewMore = toggleViewMore;
window.openCheckout = openCheckout;
window.backToCatalog = backToCatalog;
window.goToStep = goToStep;
window.selectPayment = selectPayment;
window.completeOrder = completeOrder;

