<?php
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

$logo_path = 'assets/img/solarpower_energy_corp.png';
$home_link = 'index.php';
$cart_count = session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['cart']) && is_array($_SESSION['cart'])
    ? count($_SESSION['cart'])
    : 0;
$cart_base_path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$cart_base_path = ($cart_base_path === '/' || $cart_base_path === '\\') ? '/' : rtrim($cart_base_path, '/') . '/';
$cart_checkout_href = $cart_base_path . 'checkout.php';
$cart_ajax_endpoint = $cart_base_path . 'add-to-cart-ajax.php';
$cart_ajax_script = __DIR__ . '/../assets/cart-ajax.js';
$order_tracking_css = __DIR__ . '/../assets/order-tracking.css';
$order_tracking_script = __DIR__ . '/../assets/order-tracking.js';

// Get current page filename
$current_page = basename($_SERVER['PHP_SELF']);

// Function to check if link is active
function isActive($page)
{
    global $current_page;
    return $current_page === $page ? 'active' : '';
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php if (file_exists($order_tracking_css)): ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($cart_base_path . 'assets/order-tracking.css?v=' . filemtime($order_tracking_css)); ?>">
<?php endif; ?>

<style>
    html,
    body {
        max-width: 100%;
        overflow-x: hidden;
    }

    .container {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
    }

    /* Header Top - Green Bar */
    .header-top {
        background: #2d5016;
        padding: 12px 0;
        color: var(--clr-light);
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
        z-index: 1001;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .header-top .container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
    }

    .accreditation-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .accreditation-info img {
        width: 40px;
        height: 45px;
    }

    .accreditation-info h5 {
        margin: 0;
        font-size: 17px;
        font-weight: 600;
        color: white;
        line-height: 1.4;
    }

    .header-hotline {
        display: flex;
        align-items: center;
        gap: 8px;
        color: white;
        font-weight: 700;
        font-size: 20px;
        font-family: 'Arial Narrow', Arial, sans-serif;
    }

    .phone-icon {
        width: 30px;
        height: 30px;
        animation: ring 1.5s ease-in-out infinite;
    }

    /* Ringing Animation */
    @keyframes ring {

        0%,
        100% {
            transform: rotate(0deg);
        }

        10%,
        30% {
            transform: rotate(-15deg);
        }

        20%,
        40% {
            transform: rotate(15deg);
        }

        50% {
            transform: rotate(0deg);
        }
    }

    /* Main Header Base Styles */
    header {
        background: var(--clr-light);
        padding: 15px 0;
        position: sticky;
        top: 64px;
        z-index: 1000;
        transition: all 0.35s ease;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    }

    header.scrolled {
        background: var(--clr-light);
        padding: 10px 0;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        top: 64px;
    }

    .header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .logo img {
        width: 180px;
        height: auto;
        max-height: 85px;
        border-radius: var(--border-radius-md);
        transition: all 0.3s ease;
        object-fit: contain;
        object-position: left center;
    }

    header.scrolled .logo img {
        width: 120px;
        max-height: 60px;
    }

    nav ul {
        display: flex;
        list-style: none;
        gap: 30px;
        margin: 0;
        padding: 0;
    }

    nav a {
        color: var(--clr-dark);
        text-decoration: none;
        font-weight: 600;
        font-size: 16px;
        transition: all 0.3s ease;
        letter-spacing: 0.5px;
        position: relative;
    }

    nav a:hover {
        color: var(--clr-primary);
    }

    nav a::after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: -5px;
        left: 0;
        background-color: var(--clr-primary);
        transition: width 0.3s ease;
    }

    nav a:hover::after {
        width: 100%;
    }

    /* Active Link Styles */
    nav a.active {
        color: var(--clr-primary);
        font-weight: 700;
    }

    nav a.active::after {
        width: 100%;
    }

    .nav-actions-item {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .nav-icon-tooltip {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .nav-icon-tooltip::before,
    .nav-icon-tooltip::after {
        position: absolute;
        left: 50%;
        transform: translateX(-50%) translateY(6px);
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity 160ms ease, transform 160ms ease, visibility 160ms ease;
        z-index: 1100;
    }

    .nav-icon-tooltip::before {
        content: attr(data-tooltip);
        bottom: calc(100% + 10px);
        min-width: max-content;
        max-width: 150px;
        padding: 6px 9px;
        border-radius: 7px;
        background: #17211c;
        color: #fff;
        font-size: 12px;
        font-weight: 800;
        line-height: 1.2;
        text-align: center;
        white-space: nowrap;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.18);
    }

    .nav-icon-tooltip::after {
        content: '';
        bottom: calc(100% + 4px);
        border: 6px solid transparent;
        border-top-color: #17211c;
    }

    .nav-icon-tooltip:hover::before,
    .nav-icon-tooltip:hover::after,
    .nav-icon-tooltip:focus-within::before,
    .nav-icon-tooltip:focus-within::after {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(0);
    }

    .order-tracking-nav-link,
    .nav-cart-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border: 1px solid rgba(45, 80, 22, 0.16);
        border-radius: 50%;
        background: #fff;
        color: var(--clr-primary);
        position: relative;
    }

    .order-tracking-nav-link {
        border: 1px solid rgba(45, 80, 22, 0.16);
        padding: 0;
        cursor: pointer;
    }

    .order-tracking-nav-link i {
        font-size: 21px;
        line-height: 1;
    }

    .order-tracking-nav-link span {
        display: none;
    }

    .order-tracking-nav-link::after,
    .nav-cart-link::after {
        display: none;
    }

    .order-tracking-nav-link:hover,
    .order-tracking-nav-link:focus,
    .nav-cart-link:hover {
        background: rgba(45, 80, 22, 0.08);
        color: var(--clr-primary);
        outline: 0;
    }

    .nav-cart-link svg {
        width: 22px;
        height: 22px;
        display: block;
    }

    .cart-count-badge {
        position: absolute;
        top: -7px;
        right: -8px;
        min-width: 20px;
        height: 20px;
        padding: 0 5px;
        border-radius: 999px;
        background: #f3a712;
        color: #1f2a1d;
        border: 2px solid #fff;
        font-size: 11px;
        font-weight: 800;
        line-height: 16px;
        text-align: center;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18);
    }

    .mobile-menu-btn {
        display: none;
        background: none;
        border: none;
        cursor: pointer;
        padding: 5px;
        z-index: 1002;
    }

    .mobile-menu-btn span {
        display: block;
        width: 28px;
        height: 3px;
        background: var(--clr-dark);
        margin: 5px 0;
        transition: all 0.3s ease;
        border-radius: 2px;
    }

    .mobile-menu-btn.active span:nth-child(1) {
        transform: rotate(45deg) translate(8px, 8px);
    }

    .mobile-menu-btn.active span:nth-child(2) {
        opacity: 0;
    }

    .mobile-menu-btn.active span:nth-child(3) {
        transform: rotate(-45deg) translate(7px, -7px);
    }

    @media (max-width: 1024px) {
        .mobile-menu-btn {
            display: block;
        }

        nav {
            position: fixed;
            top: 54px;
            right: -100%;
            width: 300px;
            height: calc(100vh - 54px);
            background: var(--clr-light);
            box-shadow: -2px 0 10px rgba(0,0,0,0.1);
            transition: right 0.3s ease;
            overflow-y: auto;
            padding: 20px 0;
            z-index: 1001;
            visibility: hidden;
        }

        nav.active {
            right: 0;
            visibility: visible;
        }

        nav ul {
            flex-direction: column;
            gap: 0;
        }

        nav ul li {
            border-bottom: 1px solid #eee;
        }

        nav a {
            display: block;
            padding: 15px 25px;
        }

        .nav-actions-item {
            padding: 12px 25px;
        }
    }

    /* Mobile-specific overrides (<=768px) */
    @media (max-width: 768px) {
        .header-top {
            padding: 8px 0;
        }
        .accreditation-info {
            gap: 8px;
        }
        .accreditation-info img {
            width: 30px;
            height: 30px;
        }
        .accreditation-info h5 {
            font-size: 11px;
            line-height: 1.3;
        }
        .header-hotline {
            font-size: 14px;
            gap: 5px;
        }
        .phone-icon {
            width: 18px;
            height: 18px;
        }
        header {
            top: 54px;
            padding: 10px 0;
        }
        header.scrolled {
            top: 54px;
            padding: 8px 0;
        }
        .logo img {
            width: 107px;
            height: 50px;
        }
        header.scrolled .logo img {
            width: 90px;
            height: 55px;
        }
        .nav-cart-link.active {
            border-left: 0;
            background: rgba(45, 80, 22, 0.08);
        }
    }

    /* Small Mobile (<=480px) */
    @media (max-width: 480px) {
        .accreditation-info h5 {
            font-size: 9px;
        }
        .header-hotline {
            font-size: 12px;
        }
        .header-hotline > span {
            display: none;
        }
        .header-hotline::after {
            content: '+63 995 394 7379';
            color: white;
            font-weight: 700;
        }
        .logo img {
            width: 107px;
            height: 50px;
        }
        header.scrolled .logo img {
            width: 70px;
            height: 45px;
        }
    }

    /* Scoped public header sizing. Keep this stronger than page/footer CSS. */
    .header-top.sp-header-top {
        background: #2d5016;
        min-height: 72px;
        padding: 12px 0;
        color: #fff;
        box-sizing: border-box;
    }

    .header-top.sp-header-top .container,
    #mainHeader.sp-main-header > .container {
        width: min(100% - 32px, 1200px);
        max-width: 1200px;
        margin-inline: auto;
        padding-inline: 0;
    }

    .header-top.sp-header-top .accreditation-info img {
        width: 40px;
        height: 45px;
        flex: 0 0 40px;
        object-fit: contain;
    }

    .header-top.sp-header-top .accreditation-info h5 {
        margin: 0;
        color: #fff;
        font-family: Arial, sans-serif;
        font-size: 17px;
        font-weight: 600;
        line-height: 1.4;
    }

    .header-top.sp-header-top .header-hotline {
        color: #fff;
        font-family: Arial, sans-serif;
        font-size: 20px;
        font-weight: 700;
        line-height: 1.2;
    }

    .header-top.sp-header-top .phone-icon {
        width: 30px;
        height: 30px;
        flex: 0 0 30px;
    }

    #mainHeader.sp-main-header {
        background: #fff;
        min-height: 100px;
        padding: 15px 0;
        position: sticky;
        top: 72px;
        z-index: 1000;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        box-sizing: border-box;
    }

    #mainHeader.sp-main-header.scrolled {
        min-height: 82px;
        padding: 10px 0;
        top: 72px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
    }

    #mainHeader.sp-main-header .header-container {
        min-height: 70px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
    }

    #mainHeader.sp-main-header .logo,
    #mainHeader.sp-main-header .logo-img,
    #mainHeader.sp-main-header .logo a {
        display: inline-flex;
        align-items: center;
        flex: 0 0 auto;
    }

    #mainHeader.sp-main-header .logo img.sp-header-logo {
        display: block;
        width: 238px;
        height: 70px;
        max-width: 238px;
        max-height: 70px;
        object-fit: contain;
        object-position: left center;
        border-radius: 8px;
        transition: width 0.3s ease, height 0.3s ease;
    }

    #mainHeader.sp-main-header.scrolled .logo img.sp-header-logo {
        width: 190px;
        height: 58px;
        max-width: 190px;
        max-height: 58px;
    }

    #mainHeader.sp-main-header #mobileNav {
        display: flex;
        align-items: center;
        margin: 0;
    }

    #mainHeader.sp-main-header #mobileNav ul {
        display: flex;
        align-items: center;
        gap: 30px;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    #mainHeader.sp-main-header #mobileNav a,
    #mainHeader.sp-main-header #mobileNav button.order-tracking-nav-link {
        font-family: Arial, sans-serif;
        font-size: 16px;
        font-weight: 700;
        line-height: 1.2;
        letter-spacing: 0;
        text-decoration: none;
        white-space: nowrap;
    }

    #mainHeader.sp-main-header .nav-actions-item {
        display: flex;
        align-items: center;
        gap: 10px;
        border-bottom: 0;
    }

    #mainHeader.sp-main-header .order-tracking-nav-link,
    #mainHeader.sp-main-header .nav-cart-link {
        width: 42px;
        height: 42px;
        min-width: 42px;
        min-height: 42px;
        padding: 0;
        border-radius: 50%;
        box-sizing: border-box;
    }

    #mainHeader.sp-main-header .order-tracking-nav-link i {
        font-size: 21px;
        line-height: 1;
    }

    #mainHeader.sp-main-header .nav-cart-link svg {
        width: 22px;
        height: 22px;
    }

    @media (max-width: 1024px) {
        #mainHeader.sp-main-header #mobileNav {
            position: fixed;
            top: 54px;
            right: -100%;
            width: 300px;
            max-width: min(300px, 86vw);
            height: calc(100vh - 54px);
            display: block;
            background: #fff;
            box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
            padding: 20px 0;
            z-index: 1001;
            visibility: hidden;
            transition: right 0.3s ease, visibility 0.3s ease;
        }

        #mainHeader.sp-main-header #mobileNav.active {
            right: 0;
            visibility: visible;
        }

        #mainHeader.sp-main-header #mobileNav ul {
            flex-direction: column;
            align-items: stretch;
            gap: 0;
        }

        #mainHeader.sp-main-header #mobileNav ul li {
            border-bottom: 1px solid #eee;
        }

        #mainHeader.sp-main-header #mobileNav a,
        #mainHeader.sp-main-header #mobileNav button.order-tracking-nav-link {
            display: flex;
            width: 100%;
            height: auto;
            min-height: 48px;
            justify-content: flex-start;
            border: 0;
            border-radius: 0;
            padding: 15px 25px;
            background: transparent;
        }

        #mainHeader.sp-main-header .nav-actions-item {
            padding: 12px 25px;
        }

        #mainHeader.sp-main-header .nav-actions-item .nav-icon-tooltip {
            width: 42px;
        }

        #mainHeader.sp-main-header .nav-actions-item .order-tracking-nav-link,
        #mainHeader.sp-main-header .nav-actions-item .nav-cart-link {
            width: 42px;
            height: 42px;
            min-height: 42px;
            justify-content: center;
            border: 1px solid rgba(45, 80, 22, 0.16);
            border-radius: 50%;
            background: #fff;
            padding: 0;
        }
    }

    @media (max-width: 768px) {
        .header-top.sp-header-top {
            min-height: 54px;
            padding: 8px 0;
        }

        .header-top.sp-header-top .container,
        #mainHeader.sp-main-header > .container {
            width: min(100% - 24px, 1200px);
        }

        .header-top.sp-header-top .accreditation-info img {
            width: 30px;
            height: 30px;
            flex-basis: 30px;
        }

        .header-top.sp-header-top .accreditation-info h5 {
            font-size: 11px;
            line-height: 1.3;
        }

        .header-top.sp-header-top .header-hotline {
            font-size: 14px;
        }

        .header-top.sp-header-top .phone-icon {
            width: 18px;
            height: 18px;
            flex-basis: 18px;
        }

        #mainHeader.sp-main-header {
            min-height: 74px;
            top: 54px;
            padding: 10px 0;
        }

        #mainHeader.sp-main-header.scrolled {
            min-height: 70px;
            top: 54px;
            padding: 8px 0;
        }

        #mainHeader.sp-main-header .header-container {
            min-height: 54px;
        }

        #mainHeader.sp-main-header .logo img.sp-header-logo,
        #mainHeader.sp-main-header.scrolled .logo img.sp-header-logo {
            width: 150px;
            height: 50px;
            max-width: 150px;
            max-height: 50px;
        }
    }

    @media (max-width: 480px) {
        .header-top.sp-header-top .accreditation-info h5 {
            font-size: 9px;
        }

        .header-top.sp-header-top .header-hotline {
            font-size: 12px;
        }

        #mainHeader.sp-main-header .logo img.sp-header-logo,
        #mainHeader.sp-main-header.scrolled .logo img.sp-header-logo {
            width: 136px;
            height: 46px;
            max-width: 136px;
            max-height: 46px;
        }
    }
</style>

<div class="header-top sp-header-top">
    <div class="container">
        <div class="accreditation-info">
            <img src="assets/img/DOE.png" alt="DOE Logo">
            <h5>Accredited by the Department of Energy (DOE)<br>ESCO Accreditation #250900095</h5>
        </div>
        <div class="header-hotline">
            <svg class="phone-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white">
                <path d="M20.01 15.38c-1.23 0-2.42-.2-3.53-.56-.35-.12-.74-.03-1.01.24l-1.57 1.97c-2.83-1.35-5.48-3.9-6.89-6.83l1.95-1.66c.27-.28.35-.67.24-1.02-.37-1.11-.56-2.3-.56-3.53 0-.54-.45-.99-.99-.99H4.19C3.65 3 3 3.24 3 3.99 3 13.28 10.73 21 20.01 21c.71 0 .99-.63.99-1.18v-3.45c0-.54-.45-.99-.99-.99z" />
            </svg>
            <span>Order Hotline: <span id="phoneNumber" onclick="copyPhone()" title="Click to copy"
                    style="cursor:pointer; text-underline-offset:3px;">+63 995 394 7379</span></span>
        </div>
    </div>
</div>

<!-- Mobile Menu Overlay -->
<div class="mobile-overlay" id="mobileOverlay"></div>

<header id="mainHeader" class="sp-main-header">
    <div class="container">
        <div class="header-container">
            <div class="logo">
                <div class="logo-img">
                    <a href="index.php">
                        <img src="assets/img/solarpower_energy_corp.png" alt="Solar Power Logo" class="sp-header-logo">
                    </a>
                </div>
            </div>

            <!-- Mobile Menu Button -->
            <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <nav id="mobileNav">
                <ul>
                    <li><a href="index.php" class="<?php echo isActive('index.php'); ?>">HOME</a></li>
                    <li><a href="about.php" class="<?php echo isActive('about.php'); ?>">ABOUT US</a></li>
                    <li><a href="services.php" class="<?php echo isActive('services.php'); ?>">SERVICES</a></li>
                    <li><a href="product.php" class="<?php echo isActive('product.php'); ?>">PRODUCTS</a></li>
                    <li><a href="projects.php" class="<?php echo isActive('projects.php'); ?>">PROJECTS</a></li>
                    <li><a href="loans.php" class="<?php echo isActive('loans.php'); ?>">SOLAR LOANS</a></li>
                    <li><a href="contact.php" class="<?php echo isActive('contact.php'); ?>">CONTACT</a></li>
                    <li class="nav-actions-item">
                        <span class="nav-icon-tooltip" data-tooltip="Track Order">
                            <button type="button" class="order-tracking-nav-link" id="openOrderTracking" aria-label="Track Order">
                                <i class="bi bi-truck" aria-hidden="true"></i>
                                <span>Track Order</span>
                            </button>
                        </span>
                        <span class="nav-icon-tooltip" data-tooltip="Cart">
                            <a href="<?php echo htmlspecialchars($cart_checkout_href); ?>" class="nav-cart-link <?php echo isActive('checkout.php'); ?>" aria-label="View cart and checkout">
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="9" cy="21" r="1"></circle>
                                    <circle cx="20" cy="21" r="1"></circle>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h8.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                </svg>
                                <span id="cart-count" class="cart-count-badge"><?php echo $cart_count; ?></span>
                            </a>
                        </span>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</header>

<div class="modal fade order-tracking-modal" id="orderTrackingModal" tabindex="-1" aria-labelledby="orderTrackingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="orderTrackingModalLabel">
                    <i class="bi bi-truck" aria-hidden="true"></i> Track Order
                </h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="orderTrackingForm" novalidate>
                    <label class="visually-hidden" for="orderTrackingReference">Enter your Order Reference or Tracking Number</label>
                    <div class="tracking-search-wrap">
                        <input
                            type="text"
                            class="tracking-reference-input"
                            id="orderTrackingReference"
                            name="order_reference"
                            placeholder="Enter Order Reference or Tracking Number"
                            autocomplete="off"
                            inputmode="text">
                        <button type="submit" class="tracking-submit-btn" id="orderTrackingSubmit">Track</button>
                    </div>
                </form>

                <div class="tracking-message" id="orderTrackingMessage" role="status" aria-live="polite"></div>

                <div class="tracking-placeholder" id="orderTrackingPlaceholder">
                    Enter your order reference or tracking number above to see your order progress.
                </div>

                <div class="tracking-order-summary" id="orderTrackingSummary"></div>
                <div class="tracking-stepper" id="orderTrackingTimeline"></div>
            </div>
        </div>
    </div>
</div>

<script>
    window.SOLAR_APP_BASE = window.SOLAR_APP_BASE || <?php echo json_encode($cart_base_path); ?>;
    window.SOLAR_CART_AJAX_ENDPOINT = <?php echo json_encode($cart_ajax_endpoint); ?>;

    // Scroll effect for header
    window.addEventListener('scroll', function() {
        const header = document.getElementById('mainHeader');
        if (header && window.scrollY > 50) {
            header.classList.add('scrolled');
        } else if (header) {
            header.classList.remove('scrolled');
        }
    });

    // Mobile Menu Toggle
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileNav = document.getElementById('mobileNav');
    const mobileOverlay = document.getElementById('mobileOverlay');

    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            this.classList.toggle('active');
            mobileNav.classList.toggle('active');
            mobileOverlay.classList.toggle('active');
            document.body.style.overflow = this.classList.contains('active') ? 'hidden' : '';
        });
    }

    // Close menu when overlay is clicked
    if (mobileOverlay) {
        mobileOverlay.addEventListener('click', function() {
            mobileMenuBtn.classList.remove('active');
            mobileNav.classList.remove('active');
            this.classList.remove('active');
            document.body.style.overflow = '';
        });
    }

    // Close menu when a link is clicked
    const navLinks = document.querySelectorAll('#mobileNav a, #mobileNav button');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 1024) {
                mobileMenuBtn.classList.remove('active');
                mobileNav.classList.remove('active');
                mobileOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });

    // Copy phone number to clipboard
    function copyPhone() {
        const number = '+63 995 394 7379';
        navigator.clipboard.writeText(number).then(function() {
            const el = document.getElementById('phoneNumber');
            const original = el.textContent;
            el.textContent = 'Copied!';
            el.style.textDecoration = 'none';
            setTimeout(function() {
                el.textContent = original;
                el.style.textDecoration = 'underline';
            }, 1500);
        }).catch(function() {
            // Fallback for older browsers
            const temp = document.createElement('input');
            temp.value = number;
            document.body.appendChild(temp);
            temp.select();
            document.execCommand('copy');
            document.body.removeChild(temp);
            alert('Phone number copied: ' + number);
        });
    }

    // Dropdown Toggle Logic
    document.getElementById('profileBtn')?.addEventListener('click', function(e) {
        document.getElementById('profileDropdown').classList.toggle('show');
    });
</script>
<?php if (file_exists($cart_ajax_script)): ?>
    <script src="<?php echo htmlspecialchars($cart_base_path . 'assets/cart-ajax.js?v=' . filemtime($cart_ajax_script)); ?>"></script>
<?php endif; ?>
<script>
    if (!window.jQuery) {
        var solarJqueryScript = document.createElement('script');
        solarJqueryScript.src = 'https://code.jquery.com/jquery-3.7.1.min.js';
        solarJqueryScript.defer = true;
        document.head.appendChild(solarJqueryScript);
    }
</script>
<?php if (file_exists($order_tracking_script)): ?>
    <script src="<?php echo htmlspecialchars($cart_base_path . 'assets/order-tracking.js?v=' . filemtime($order_tracking_script)); ?>" defer></script>
<?php endif; ?>
