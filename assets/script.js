// ===========================
// GLOBAL VARIABLES
// ===========================
let collectedProducts = [];
let selectedPaymentType = '';
let currentIndex = 0;

// ===========================
// INITIALIZATION
// ===========================
document.addEventListener('DOMContentLoaded', function() {
    initializeNavbar();
    initializeFilters();
    initializeSorting();
    initializeCarousel();
    initializeSubscription();
    setupCalculator();
});

// ===========================
// NAVBAR SCROLL
// ===========================
function initializeNavbar() {
    const header = document.getElementById('mainHeader');
    const featured = document.getElementById('featured-brands');
    
    if (!header || !featured) return;
    
    window.addEventListener('scroll', () => {
        const triggerPoint = featured.offsetTop - 120;
        if (window.scrollY >= triggerPoint) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });
}

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


// ========================================
// COOKIE UTILITY FUNCTIONS
// ========================================

/**
 * Set a cookie
 * @param {string} name - Cookie name
 * @param {string} value - Cookie value
 * @param {number} days - Days until expiration
 */
function setCookie(name, value, days = 30) {
    const date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    const expires = "expires=" + date.toUTCString();
    document.cookie = name + "=" + encodeURIComponent(value) + ";" + expires + ";path=/;SameSite=Lax";
}

/**
 * Get a cookie value
 * @param {string} name - Cookie name
 * @returns {string|null} Cookie value or null if not found
 */
function getCookie(name) {
    const nameEQ = name + "=";
    const cookies = document.cookie.split(';');
    
    for (let i = 0; i < cookies.length; i++) {
        let cookie = cookies[i].trim();
        if (cookie.indexOf(nameEQ) === 0) {
            return decodeURIComponent(cookie.substring(nameEQ.length));
        }
    }
    return null;
}

/**
 * Delete a cookie
 * @param {string} name - Cookie name
 */
function deleteCookie(name) {
    document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;";
}

// ========================================
// CART PERSISTENCE WITH COOKIES
// ========================================

/**
 * Save cart to cookies
 */
function saveCartToCookie() {
    const cartJSON = JSON.stringify(cart);
    setCookie('solar_cart', cartJSON, 7); // Save for 7 days
}

/**
 * Load cart from cookies
 */
function loadCartFromCookie() {
    const cartData = getCookie('solar_cart');
    if (cartData) {
        try {
            cart = JSON.parse(cartData);
            renderCheckoutSummary();
        } catch (e) {
            console.error('Error parsing cart cookie:', e);
            cart = [];
        }
    }
}

/**
 * Clear cart cookie
 */
function clearCartCookie() {
    deleteCookie('solar_cart');
    cart = [];
    renderCheckoutSummary();
}


// PSGC API

const API_BASE = "https://psgc.gitlab.io/api";
document.addEventListener("DOMContentLoaded", () => {
    const provinceSelect = document.getElementById("province");
    const municipalitySelect = document.getElementById("municipality");
    const barangaySelect = document.getElementById("barangay");

    if (!provinceSelect) {
        console.error("Province select NOT FOUND");
        return;
    }

    /* =========================
       LOAD PROVINCES + NCR
    ========================= */

    fetch(`${API_BASE}/provinces/`)
        .then(res => res.json())
        .then(data => {

            provinceSelect.innerHTML =
                `<option value="">Select Province</option>`;

            // ✅ NCR FIRST
            provinceSelect.innerHTML +=
                `<option value="NCR">Metro Manila (NCR)</option>`;

            data.forEach(p => {
                provinceSelect.innerHTML +=
                    `<option value="${p.code}">${p.name}</option>`;
            });
        });


    /* =========================
       PROVINCE → CITY
    ========================= */
    provinceSelect.addEventListener("change", function () {
        municipalitySelect.innerHTML =
            `<option value="">Loading...</option>`;
        barangaySelect.innerHTML =
            `<option value="">Select Barangay</option>`;
        municipalitySelect.disabled = true;
        barangaySelect.disabled = true;
        if (!this.value) return;

        // ✅ NCR
        if (this.value === "NCR") {
            fetch(`${API_BASE}/regions/130000000/cities-municipalities/`)
                .then(res => res.json())
                .then(data => {
                    municipalitySelect.innerHTML =
                        `<option value="">Select City / Municipality</option>`;
                    data.forEach(m => {
                        municipalitySelect.innerHTML +=
                            `<option value="${m.code}">${m.name}</option>`;
                    });
                    municipalitySelect.disabled = false;
                });
            return;
        }

        // ✅ NORMAL PROVINCES
        fetch(`${API_BASE}/provinces/${this.value}/cities-municipalities/`)
            .then(res => res.json())
            .then(data => {
                municipalitySelect.innerHTML =
                    `<option value="">Select City / Municipality</option>`;
                data.forEach(m => {
                    municipalitySelect.innerHTML +=
                        `<option value="${m.code}">${m.name}</option>`;
                });
                municipalitySelect.disabled = false;
            });
    });

    /* =========================
       CITY → BARANGAY
    ========================= */
    municipalitySelect.addEventListener("change", function () {
        barangaySelect.innerHTML =
            `<option value="">Loading...</option>`;
        barangaySelect.disabled = true;
        if (!this.value) return;

        fetch(`${API_BASE}/cities-municipalities/${this.value}/barangays/`)
            .then(res => res.json())
            .then(data => {
                barangaySelect.innerHTML =
                    `<option value="">Select Barangay</option>`;
                data.forEach(b => {
                    barangaySelect.innerHTML +=
                        `<option value="${b.code}">${b.name}</option>`;

                });
                barangaySelect.disabled = false;
            });
    });

});




// ========================================
// USER PREFERENCES WITH COOKIES
// ========================================

/**
 * Save user's filter preference
 */
function saveFilterPreference(category) {
    setCookie('preferred_category', category, 30);
}

/**
 * Load and apply filter preference
 */
function loadFilterPreference() {
    const preferredCategory = getCookie('preferred_category');
    if (preferredCategory) {
        const filterBtn = document.querySelector(`[data-category="${preferredCategory}"]`);
        if (filterBtn) {
            filterBtn.click();
        }
    }
}

// Accordion functionality for Solar Reasons section
document.addEventListener('DOMContentLoaded', function() {
    const accordionHeaders = document.querySelectorAll('.accordion-header');
    
    accordionHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const accordionItem = this.parentElement;
            const isActive = accordionItem.classList.contains('active');
            
            // Close all accordion items
            document.querySelectorAll('.accordion-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Open clicked item if it wasn't active
            if (!isActive) {
                accordionItem.classList.add('active');
            }
        });
    });
    
    // Optional: Open first item by default
    const firstItem = document.querySelector('.accordion-item');
    if (firstItem) {
        firstItem.classList.add('active');
    }
});

/**
 * Save sort preference
 */
function saveSortPreference(sortType) {
    setCookie('preferred_sort', sortType, 30);
}

/**
 * Load and apply sort preference
 */
function loadSortPreference() {
    const preferredSort = getCookie('preferred_sort');
    if (preferredSort) {
        const sortSelect = document.getElementById('sortSelect');
        if (sortSelect) {
            sortSelect.value = preferredSort;
            sortProducts(preferredSort);
        }
    }
}

/**
 * Remember customer details from previous orders
 */
function saveCustomerDetails(name, email, phone, address) {
    const customerData = { name, email, phone, address };
    setCookie('customer_details', JSON.stringify(customerData), 90); // 90 days
}

/**
 * Load and prefill customer details
 */
function loadCustomerDetails() {
    const customerData = getCookie('customer_details');
    if (customerData) {
        try {
            const data = JSON.parse(customerData);
            document.getElementById('cust_name').value = data.name || '';
            document.getElementById('cust_email').value = data.email || '';
            document.getElementById('cust_phone').value = data.phone || '';
            document.getElementById('cust_address').value = data.address || '';
        } catch (e) {
            console.error('Error parsing customer details:', e);
        }
    }
}

// ========================================
// COOKIE CONSENT BANNER
// ========================================

/**
 * Show cookie consent banner
 */
function showCookieConsent() {
    const consentGiven = getCookie('cookie_consent');
    
    if (!consentGiven) {
        const banner = document.createElement('div');
        banner.id = 'cookieConsent';
        banner.style.cssText = `
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(44, 62, 80, 0.95);
            color: white;
            padding: 20px;
            z-index: 9999;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.2);
            animation: slideUp 0.3s ease-out;
        `;
        
        banner.innerHTML = `
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-8 col-md-7 mb-3 mb-md-0">
                        <h5 style="margin-bottom: 8px;">
                            <i class="fas fa-cookie-bite me-2"></i>We Use Cookies
                        </h5>
                        <p style="margin: 0; font-size: 14px; opacity: 0.9;">
                            We use cookies to enhance your browsing experience, remember your cart items, 
                            and save your preferences. By continuing to use our site, you agree to our use of cookies.
                        </p>
                    </div>
                    <div class="col-lg-4 col-md-5 text-md-end">
                        <button onclick="acceptCookies()" class="btn btn-success me-2">
                            <i class="fas fa-check me-1"></i>Accept
                        </button>
                        <button onclick="declineCookies()" class="btn btn-outline-light">
                            Decline
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(banner);
        
        // Add animation CSS
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideUp {
                from { transform: translateY(100%); }
                to { transform: translateY(0); }
            }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Accept cookies
 */
function acceptCookies() {
    setCookie('cookie_consent', 'accepted', 365);
    document.getElementById('cookieConsent')?.remove();
    
    // Initialize cookie-based features
    loadCartFromCookie();
    loadFilterPreference();
    loadSortPreference();
    loadCustomerDetails();
}

/**
 * Decline cookies
 */
function declineCookies() {
    setCookie('cookie_consent', 'declined', 365);
    document.getElementById('cookieConsent')?.remove();
    
    // Clear all cookies except consent
    deleteCookie('solar_cart');
    deleteCookie('preferred_category');
    deleteCookie('preferred_sort');
    deleteCookie('customer_details');
}

// ========================================
// RECENTLY VIEWED PRODUCTS
// ========================================

/**
 * Add product to recently viewed
 */
function addToRecentlyViewed(productId) {
    const consent = getCookie('cookie_consent');
    if (consent !== 'accepted') return;
    
    let recentlyViewed = getCookie('recently_viewed');
    let viewedArray = recentlyViewed ? JSON.parse(recentlyViewed) : [];
    
    // Remove if already exists
    viewedArray = viewedArray.filter(id => id !== productId);
    
    // Add to beginning
    viewedArray.unshift(productId);
    
    // Keep only last 10
    viewedArray = viewedArray.slice(0, 10);
    
    setCookie('recently_viewed', JSON.stringify(viewedArray), 30);
}

/**
 * Get recently viewed products
 */
function getRecentlyViewed() {
    const recentlyViewed = getCookie('recently_viewed');
    return recentlyViewed ? JSON.parse(recentlyViewed) : [];
}

// ========================================
// ANALYTICS & TRACKING
// ========================================

/**
 * Track page visit
 */
function trackPageVisit() {
    const consent = getCookie('cookie_consent');
    if (consent !== 'accepted') return;
    
    const visitCount = parseInt(getCookie('visit_count') || '0') + 1;
    setCookie('visit_count', visitCount.toString(), 365);
    
    const lastVisit = getCookie('last_visit');
    if (lastVisit) {
        const daysSinceVisit = Math.floor((Date.now() - parseInt(lastVisit)) / (1000 * 60 * 60 * 24));
        if (daysSinceVisit > 7) {
            // Show returning visitor message
            showWelcomeBackMessage(visitCount);
        }
    }
    
    setCookie('last_visit', Date.now().toString(), 365);
}

/**
 * Show welcome back message for returning visitors
 */
function showWelcomeBackMessage(visitCount) {
    // You can customize this to show a notification or special offer
    console.log(`Welcome back! This is visit #${visitCount}`);
}

// ========================================
// MODIFIED EXISTING FUNCTIONS TO USE COOKIES
// ========================================

// Update addToCartLogic to save to cookie
const originalAddToCartLogic = addToCartLogic;
function addToCartLogic(product) {
    originalAddToCartLogic(product);
    saveCartToCookie();
}

// Update removeFromCart to save to cookie
const originalRemoveFromCart = removeFromCart;
function removeFromCart(productId) {
    originalRemoveFromCart(productId);
    saveCartCookie();
}

// Update updateQuantity to save to cookie
const originalUpdateQuantity = updateQuantity;
function updateQuantity(productId, change) {
    originalUpdateQuantity(productId, change);
    saveCartToCookie();
}

// Save filter preference when changed
const originalFilterProducts = filterProducts;
function filterProducts(category) {
    originalFilterProducts(category);
    const consent = getCookie('cookie_consent');
    if (consent === 'accepted') {
        saveFilterPreference(category);
    }
}

// Save sort preference when changed
const originalSortProducts = sortProducts;
function sortProducts(sortType) {
    originalSortProducts(sortType);
    const consent = getCookie('cookie_consent');
    if (consent === 'accepted') {
        saveSortPreference(sortType);
    }
}

// Save customer details after successful order
const originalValidateStep1 = validateStep1;
function validateStep1() {
    const name = document.getElementById('cust_name').value.trim();
    const email = document.getElementById('cust_email').value.trim();
    const phone = document.getElementById('cust_phone').value.trim();
    const address = document.getElementById('cust_address').value.trim();
    
    const consent = getCookie('cookie_consent');
    if (consent === 'accepted') {
        saveCustomerDetails(name, email, phone, address);
    }
    
    originalValidateStep1();
}

// Clear cart cookie after successful order
const originalDisplayOrderConfirmation = displayOrderConfirmation;
function displayOrderConfirmation(orderId) {
    originalDisplayOrderConfirmation(orderId);
    clearCartCookie();
}

// ========================================
// INITIALIZATION
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    // Show cookie consent banner
    showCookieConsent();
    
    // If consent already given, load preferences
    const consent = getCookie('cookie_consent');
    if (consent === 'accepted') {
        loadCartFromCookie();
        loadFilterPreference();
        loadSortPreference();
        loadCustomerDetails();
        trackPageVisit();
    }
});

// ========================================
// UTILITY: VIEW ALL COOKIES (FOR DEBUGGING)
// ========================================

/**
 * Get all cookies as an object
 */
function getAllCookies() {
    const cookies = {};
    document.cookie.split(';').forEach(cookie => {
        const [name, value] = cookie.trim().split('=');
        if (name) {
            cookies[name] = decodeURIComponent(value);
        }
    });
    return cookies;
}

/**
 * Clear all cookies
 */
function clearAllCookies() {
    const cookies = getAllCookies();
    Object.keys(cookies).forEach(name => {
        deleteCookie(name);
    });
    console.log('All cookies cleared');
}

// ========================================
// EXPORT FOR CONSOLE DEBUGGING
// ========================================

// Make functions available in console for debugging
window.cookieUtils = {
    setCookie,
    getCookie,
    deleteCookie,
    getAllCookies,
    clearAllCookies,
    saveCartToCookie,
    loadCartFromCookie,
    clearCartCookie
};

console.log('Cookie management system loaded. Use window.cookieUtils for debugging.');


function submitInspection(e) {
    e.preventDefault();

    const form = e.target;
    const btn = document.getElementById('inspectionBtn');
    const text = btn.querySelector('.btn-text');
    const spinner = btn.querySelector('.spinner-border');

    btn.disabled = true;
    text.textContent = "Sending...";
    spinner.classList.remove('d-none');

    fetch("controllers/send-inspection-email.php", {
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