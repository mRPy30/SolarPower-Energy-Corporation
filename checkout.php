<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkout_landing_clean_text($value): string
{
    $text = trim((string) $value);
    $text = preg_replace('/[\r\n\t]+/', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    return trim(strip_tags($text));
}

function checkout_landing_product_item(int $productId): ?array
{
    if ($productId <= 0) {
        return null;
    }

    require __DIR__ . '/config/dbconn.php';

    $sql = "
        SELECT
            p.id,
            p.displayName,
            COALESCE(
                (
                    SELECT COALESCE(b.brand_name, sb.brandName)
                    FROM product_brand_variants pbv
                    LEFT JOIN brands b ON pbv.brand_id = b.brand_id
                    LEFT JOIN supplier_brands sb ON pbv.brand_id = sb.id
                    WHERE pbv.product_id = p.id
                    ORDER BY pbv.price ASC, pbv.id ASC
                    LIMIT 1
                ),
                TRIM(p.brandName)
            ) AS brandName,
            COALESCE(
                (
                    SELECT pbv.price
                    FROM product_brand_variants pbv
                    WHERE pbv.product_id = p.id
                    ORDER BY pbv.price ASC, pbv.id ASC
                    LIMIT 1
                ),
                p.price
            ) AS price,
            (
                SELECT pbv.brand_id
                FROM product_brand_variants pbv
                WHERE pbv.product_id = p.id
                ORDER BY pbv.price ASC, pbv.id ASC
                LIMIT 1
            ) AS brand_id,
            p.category,
            p.packageType,
            COALESCE(p.moq, 1) AS moq,
            COALESCE(
                (
                    SELECT pi.image_path
                    FROM product_images pi
                    WHERE pi.product_id = p.id
                    ORDER BY pi.id ASC
                    LIMIT 1
                ),
                p.imagePath
            ) AS image_path
        FROM product p
        WHERE p.id = ? AND p.status = 'Active'
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $conn->close();
        return null;
    }

    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    $conn->close();

    if (!$row) {
        return null;
    }

    $moq = max(1, (int) ($row['moq'] ?? 1));
    $name = checkout_landing_clean_text($row['displayName'] ?? 'Solar Product');
    $brandName = checkout_landing_clean_text($row['brandName'] ?? '');

    return [
        'id' => (int) $row['id'],
        'product_id' => (int) $row['id'],
        'brand_id' => isset($row['brand_id']) ? (int) $row['brand_id'] : null,
        'displayName' => $name,
        'name' => $name,
        'product_name' => $name,
        'brandName' => $brandName,
        'brand_name' => $brandName,
        'category' => checkout_landing_clean_text($row['category'] ?? ''),
        'packageType' => checkout_landing_clean_text($row['packageType'] ?? ''),
        'price' => round((float) ($row['price'] ?? 0), 2),
        'image_path' => checkout_landing_clean_text($row['image_path'] ?? ''),
        'quantity' => $moq,
        'moq' => $moq,
    ];
}

$checkoutLanding = [
    'action' => '',
    'product_id' => 0,
    'google_name' => '',
    'google_email' => '',
    'is_google' => false,
    'is_guest' => false,
];

if (isset($_GET['action'], $_GET['product_id'])) {
    $checkoutLanding['action'] = strtolower(checkout_landing_clean_text($_GET['action']));
    $checkoutLanding['product_id'] = max(0, (int) filter_var($_GET['product_id'], FILTER_SANITIZE_NUMBER_INT));

    if ($checkoutLanding['action'] === 'google') {
        $checkoutLanding['is_google'] = true;
        $checkoutLanding['google_name'] = checkout_landing_clean_text($_GET['name'] ?? '');
        $email = filter_var(trim((string) ($_GET['email'] ?? '')), FILTER_SANITIZE_EMAIL);
        $checkoutLanding['google_email'] = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';

        $_SESSION['checkout_prefill_name'] = $checkoutLanding['google_name'];
        $_SESSION['checkout_prefill_email'] = $checkoutLanding['google_email'];
        $_SESSION['checkout_prefill_action'] = 'google';
    } elseif ($checkoutLanding['action'] === 'guest') {
        $checkoutLanding['is_guest'] = true;
        unset(
            $_SESSION['checkout_prefill_name'],
            $_SESSION['checkout_prefill_email'],
            $_SESSION['checkout_prefill_action']
        );
    }

    if (($checkoutLanding['is_google'] || $checkoutLanding['is_guest']) && $checkoutLanding['product_id'] > 0) {
        $landingProduct = checkout_landing_product_item($checkoutLanding['product_id']);
        if ($landingProduct) {
            $_SESSION['cart'] = [$landingProduct];
        }
    }
}

$basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$basePath = ($basePath === '/' || $basePath === '\\') ? '/' : rtrim($basePath, '/') . '/';
$sessionCart = isset($_SESSION['cart']) && is_array($_SESSION['cart'])
    ? array_values(array_filter($_SESSION['cart'], 'is_array'))
    : [];
$defaultClient = [
    'email' => '',
    'firstName' => '',
    'lastName' => '',
    'contact_number' => '',
    'address' => '',
];
$client = $defaultClient;
$clientAuthPath = __DIR__ . '/includes/client-auth.php';
if (file_exists($clientAuthPath)) {
    require_once $clientAuthPath;
    if (function_exists('client_auth_session_payload')) {
        $client = array_merge($client, client_auth_session_payload());
    }
}

if ($checkoutLanding['is_guest']) {
    $client = $defaultClient;
}

$fullName = $checkoutLanding['is_google']
    ? $checkoutLanding['google_name']
    : trim(($client['firstName'] ?? '') . ' ' . ($client['lastName'] ?? ''));
$prefillEmail = $checkoutLanding['is_google']
    ? $checkoutLanding['google_email']
    : ($client['email'] ?? $_SESSION['user_email'] ?? '');
$isLoggedIn = isset($_SESSION['user_id']) || isset($_SESSION['client_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - SolarPower Energy</title>
    <link rel="icon" type="image/png" href="assets/img/icon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --solar-green: #155e42;
            --solar-green-dark: #0f3d2d;
            --solar-amber: #f3a712;
            --solar-ink: #17211c;
            --solar-muted: #647067;
            --solar-line: #dde5df;
            --solar-bg: #f5f7f2;
            --solar-panel: #ffffff;
        }
        body {
            background: var(--solar-bg);
            color: var(--solar-ink);
            font-family: Arial, Helvetica, sans-serif;
            min-height: 100vh;
        }
        .checkout-topbar {
            background: var(--solar-panel);
            border-bottom: 1px solid var(--solar-line);
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .checkout-topbar img {
            height: 46px;
            width: auto;
            object-fit: contain;
        }
        .checkout-shell {
            max-width: 1180px;
            margin: 0 auto;
            padding: 28px 16px 48px;
        }
        .checkout-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 380px;
            gap: 18px;
            align-items: start;
        }
        .checkout-panel {
            background: var(--solar-panel);
            border: 1px solid var(--solar-line);
            border-radius: 8px;
            box-shadow: 0 10px 24px rgba(23, 33, 28, 0.05);
        }
        .panel-head {
            padding: 18px 20px;
            border-bottom: 1px solid var(--solar-line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .panel-head h1,
        .panel-head h2 {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
        }
        .panel-body {
            padding: 20px;
        }
        .cart-row {
            display: grid;
            grid-template-columns: 74px minmax(0, 1fr) auto;
            gap: 14px;
            padding: 14px 0;
            border-bottom: 1px solid var(--solar-line);
        }
        .cart-row:last-child {
            border-bottom: 0;
        }
        .cart-row img {
            width: 74px;
            height: 74px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid var(--solar-line);
            background: #fff;
        }
        .item-title {
            font-weight: 700;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }
        .item-meta {
            color: var(--solar-muted);
            font-size: 0.9rem;
        }
        .qty-control {
            display: inline-grid;
            grid-template-columns: 32px 42px 32px;
            align-items: center;
            border: 1px solid var(--solar-line);
            border-radius: 6px;
            overflow: hidden;
            background: #fff;
        }
        .qty-control button {
            width: 32px;
            height: 32px;
            border: 0;
            background: #f7faf7;
            color: var(--solar-ink);
        }
        .qty-control span {
            text-align: center;
            font-weight: 700;
            font-size: 0.9rem;
        }
        .icon-btn {
            width: 36px;
            height: 36px;
            border: 1px solid var(--solar-line);
            border-radius: 6px;
            background: #fff;
            color: #b42318;
        }
        .summary-line {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
            color: var(--solar-muted);
        }
        .summary-line strong {
            color: var(--solar-ink);
        }
        .summary-total {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding-top: 14px;
            border-top: 1px solid var(--solar-line);
            font-size: 1.25rem;
            font-weight: 800;
        }
        .btn-maya {
            background: var(--solar-green);
            border-color: var(--solar-green);
            color: #fff;
            font-weight: 700;
            min-height: 48px;
        }
        .btn-maya:hover,
        .btn-maya:focus {
            background: var(--solar-green-dark);
            border-color: var(--solar-green-dark);
            color: #fff;
        }
        .btn-maya:disabled {
            background: #94a3a0;
            border-color: #94a3a0;
            color: #eef4f0;
            cursor: not-allowed;
            opacity: 0.75;
        }
        .maya-strip {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--solar-muted);
            font-size: 0.92rem;
        }
        .maya-strip img {
            height: 24px;
            width: auto;
        }
        .empty-cart {
            text-align: center;
            padding: 52px 20px;
            color: var(--solar-muted);
        }
        .empty-cart i {
            color: var(--solar-amber);
            font-size: 42px;
            margin-bottom: 12px;
        }
        @media (max-width: 900px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 560px) {
            .checkout-shell {
                padding-left: 10px;
                padding-right: 10px;
            }
            .cart-row {
                grid-template-columns: 58px minmax(0, 1fr);
            }
            .cart-row img {
                width: 58px;
                height: 58px;
            }
            .cart-actions {
                grid-column: 2;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
    <header class="checkout-topbar">
        <div class="container-fluid px-3 px-md-4 py-3 d-flex align-items-center justify-content-between gap-3">
            <a href="index.php" class="d-inline-flex align-items-center text-decoration-none">
                <img src="assets/img/solarpower_energy_corp.png" alt="SolarPower Energy">
            </a>
            <a class="btn btn-outline-secondary btn-sm" href="product.php">
                <i class="fas fa-arrow-left me-2"></i>Shop
            </a>
        </div>
    </header>
    <main class="checkout-shell">
        <div class="checkout-grid">
            <section class="checkout-panel">
                <div class="panel-head">
                    <h1>Checkout</h1>
                    <span class="badge text-bg-warning" id="itemCountBadge">0 items</span>
                </div>
                <div class="panel-body">
                    <div id="cartItems"></div>
                </div>
            </section>
            <aside class="checkout-panel">
                <div class="panel-head">
                    <h2>Customer Details</h2>
                </div>
                <div class="panel-body">
                        <div class="checkout-speedup p-3 rounded mb-4 border text-center">
                            <div class="small fw-semibold mb-2"><i class="fas fa-info-circle text-info me-1"></i> Speed up your checkout:</div>
                            <div class="d-flex gap-2 justify-content-center">
                                <div id="google-autofill-btn" class="google-autofill-slot"></div>
                            </div>
                        </div>
                    <form id="checkoutForm" action="process-payment.php" method="POST" novalidate>
                        <input type="hidden" id="total_items_amount" name="total_items_amount" value="0.00">
                        <input type="hidden" id="calculated_delivery_fee" name="calculated_delivery_fee" value="0.00">
                        <input type="hidden" id="selected_location_name" name="selected_location_name" value="">
                        <input type="hidden" name="paymentType" value="full">
                        <div class="mb-3">
                            <label class="form-label" for="cust_name">Full name</label>
                            <input class="form-control" id="cust_name" name="customerName" value="<?= htmlspecialchars($fullName) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="cust_email">Email</label>
                            <input class="form-control" id="cust_email" name="customerEmail" type="email" value="<?= htmlspecialchars($prefillEmail) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="cust_phone">Mobile number</label>
                            <input class="form-control" id="cust_phone" name="customerPhone" value="<?= htmlspecialchars($client['contact_number'] ?? '') ?>" required>
                        </div>
                        <input type="hidden" id="cust_address" name="customerAddress" value="<?= htmlspecialchars($client['address'] ?? '') ?>">
                        <div class="mb-3">
                            <label class="form-label" for="addr_line1">House No. / Street / Subdivision</label>
                            <input class="form-control" id="addr_line1" name="addressLine1" value="<?= htmlspecialchars($client['address'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="addr_province_region">Province/Region</label>
                            <select class="form-select" id="addr_province_region" name="addressProvinceRegion" required>
                                <option value="">Loading locations...</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="addr_city">City / Municipality</label>
                            <select class="form-select" id="addr_city" name="addressCityMunicipality" required disabled>
                                <option value="">Select province/region first</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="addr_barangay">Barangay</label>
                            <select class="form-select" id="addr_barangay" name="addressBarangay" required disabled>
                                <option value="">Select city/municipality first</option>
                            </select>
                        </div>
                        <div class="mb-3 d-none" id="delivery_location_group" aria-hidden="true">
                            <label class="form-label" for="delivery_location">Delivery location</label>
                            <select class="form-select" id="delivery_location" name="deliveryLocation" required>
                                <option value="">Loading delivery rates...</option>
                            </select>
                            <div class="form-text">Origin: Madrigal Business Park, Alabang, Muntinlupa</div>
                        </div>
                        <!-- Delivery Service Tier -->
                        <div class="mb-3" id="delivery_tier_container">
                            <label class="form-label fw-bold">Delivery Option</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="delivery_option" id="tier_eco" value="Eco-Saver Shipping">
                                <label class="form-check-label" for="tier_eco">
                                    Eco-Saver Shipping (Discounted - Shared Green Route)
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="delivery_option" id="tier_standard" value="SunSpeed Standard" checked>
                                <label class="form-check-label" for="tier_standard">
                                    SunSpeed Standard (Direct Fleet Delivery)
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="delivery_option" id="tier_express" value="SolarFlash Express">
                                <label class="form-check-label" for="tier_express">
                                    SolarFlash Express (Dedicated Trucking - Priority Route)
                                </label>
                            </div>
                            <input type="hidden" id="delivery_service_tier" name="delivery_service_tier" value="SunSpeed Standard">
                        </div>
                        <div class="my-4">
                            <div class="alert alert-warning d-flex align-items-center d-none" id="logistics-warning" role="alert">
                                <i class="fas fa-truck-loading me-2" aria-hidden="true"></i>
                                <div>We're sorry, but we don't offer delivery to your location at the moment. Please contact our customer support at [Insert Corporate Hotline/Email Here] to assist you with alternative shipping arrangements.</div>
                            </div>
                            <div class="summary-line">
                                <span>Items subtotal</span>
                                <span id="items-subtotal">PHP 0.00</span>
                            </div>
                            <div class="summary-line">
                                <span>Delivery option fee</span>
                                <span id="delivery-option-fee">PHP 0.00</span>
                            </div>
                            <div class="summary-line">
                                <span>Delivery fee</span>
                                <span id="delivery-fee">PHP 0.00</span>
                            </div>
                            <div class="summary-total">
                                <span>Total</span>
                                <span id="total-price">PHP 0.00</span>
                            </div>
                        </div>
                        <div class="alert alert-danger d-none" id="checkoutError"></div>
                        <button class="btn btn-maya w-100" id="maya-submit-btn" type="submit" disabled aria-disabled="true" title="Complete checkout details to continue">
                            <i class="fas fa-lock me-2"></i>Pay Securely with Maya
                        </button>
                        <div class="maya-strip mt-3">
                            <img src="assets/img/payments/Maya_logo.png" alt="Maya">
                            <span>Maya Checkout</span>
                        </div>
                    </form>
                </div>
            </aside>
        </div>
    </main>
    <script>
        window.SOLAR_APP_BASE = <?= json_encode($basePath) ?>;
        window.SOLAR_SESSION_CART = <?= json_encode($sessionCart) ?>;
        window.SOLAR_MAYA_CHECKOUT_ENDPOINT = <?= json_encode($basePath . 'controllers/ordering/create-maya-checkout.php') ?>;
        window.SOLAR_DELIVERY_RATES_ENDPOINT = <?= json_encode($basePath . 'controllers/checkout/get-delivery-rates.php') ?>;
        window.SOLAR_DELIVERY_RATE_ENDPOINT = <?= json_encode($basePath . 'controllers/checkout/get-delivery-rate.php') ?>;
        window.SOLAR_PSGC_VERSION = 'Q2_2024';
        window.SOLAR_PSGC_TOKEN = <?= json_encode(getenv('PSA_PSGC_TOKEN') ?: '') ?>;
    </script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <?php if ($checkoutLanding['is_google'] && ($checkoutLanding['google_name'] !== '' || $checkoutLanding['google_email'] !== '')): ?>
    <script>
        $(function () {
            const modalGoogleName = <?= json_encode($checkoutLanding['google_name']) ?>;
            const modalGoogleEmail = <?= json_encode($checkoutLanding['google_email']) ?>;
            const $nameFields = $('#full_name, #cust_name');
            const $emailFields = $('#email, #cust_email');

            if (modalGoogleName) {
                $nameFields.val(modalGoogleName).trigger('input').trigger('change');
            }

            if (modalGoogleEmail) {
                $emailFields.val(modalGoogleEmail).trigger('input').trigger('change');
            }

            $nameFields.add($emailFields)
                .filter(function () {
                    return this.value;
                })
                .addClass('google-checkout-prefill-highlight')
                .first()
                .focus();

            setTimeout(function () {
                $nameFields.add($emailFields).removeClass('google-checkout-prefill-highlight');
            }, 1800);
        });
    </script>
    <?php endif; ?>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script>
        function decodeGoogleJwt(token) {
            const payload = token.split('.')[1];
            const base64 = payload.replace(/-/g, '+').replace(/_/g, '/');
            const json = decodeURIComponent(
                atob(base64)
                    .split('')
                    .map(function (char) {
                        return '%' + ('00' + char.charCodeAt(0).toString(16)).slice(-2);
                    })
                    .join('')
            );
            return JSON.parse(json);
        }
        function handleGoogleAutofill(response) {
            if (!response || !response.credential) return;
            const googleUser = decodeGoogleJwt(response.credential);
            $('#full_name, #cust_name').val(googleUser.name || '').trigger('input').trigger('change');
            $('#email, #cust_email').val(googleUser.email || '').trigger('input').trigger('change');
            $('#full_name, #cust_name, #email, #cust_email')
                .filter(function () {
                    return $(this).length && $(this).val();
                })
                .addClass('google-autofill-highlight')
                .first()
                .focus();
            setTimeout(function () {
                $('#full_name, #cust_name, #email, #cust_email').removeClass('google-autofill-highlight');
            }, 1400);
        }
        function renderGoogleAutofillButton(attempt) {
            const target = document.getElementById('google-autofill-btn');
            if (!target) return;
            if (!window.google || !google.accounts || !google.accounts.id) {
                if ((attempt || 0) < 20) {
                    setTimeout(function () {
                        renderGoogleAutofillButton((attempt || 0) + 1);
                    }, 250);
                }
                return;
            }
            target.innerHTML = '';
            google.accounts.id.initialize({
                client_id: '257004722980-0mumh545gr3qtd2l9qagsreivqo68d2l.apps.googleusercontent.com',
                callback: handleGoogleAutofill
            });
            google.accounts.id.renderButton(target, {
                theme: 'outline',
                size: 'large',
                text: 'continue_with',
                width: 220
            });
        }
        window.addEventListener('load', function () {
            renderGoogleAutofillButton(0);
        });
    </script>
    <style>
        .checkout-speedup {
            background: #111827;
            border-color: #111827 !important;
            color: #fff;
        }

        .checkout-speedup .text-info {
            color: #facc15 !important;
        }

        .checkout-speedup .d-flex {
            justify-content: center;
        }

        .google-autofill-slot {
            min-height: 40px;
            min-width: 220px;
        }
        .google-autofill-highlight {
            border-color: #198754 !important;
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.18) !important;
            transition: border-color 180ms ease, box-shadow 180ms ease;
        }

        .google-checkout-prefill-highlight {
            animation: checkoutPrefillGlow 1.6s ease;
            border-color: #198754 !important;
            box-shadow: 0 0 0 0.22rem rgba(25, 135, 84, 0.2) !important;
            transition: border-color 180ms ease, box-shadow 180ms ease;
        }

        @keyframes checkoutPrefillGlow {
            0% {
                box-shadow: 0 0 0 0 rgba(25, 135, 84, 0);
            }
            35% {
                box-shadow: 0 0 0 0.28rem rgba(25, 135, 84, 0.22);
            }
            100% {
                box-shadow: 0 0 0 0.08rem rgba(25, 135, 84, 0);
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/checkout-auth.js?v=<?= filemtime(__DIR__ . '/assets/checkout-auth.js') ?>"></script>
    <script src="assets/checkout.js?v=<?= filemtime(__DIR__ . '/assets/checkout.js') ?>"></script>
</body>
</html>


