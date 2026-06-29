<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$basePath = ($basePath === '/' || $basePath === '\\') ? '/' : rtrim($basePath, '/') . '/';
$sessionCart = isset($_SESSION['cart']) && is_array($_SESSION['cart'])
    ? array_values(array_filter($_SESSION['cart'], 'is_array'))
    : [];

$client = [
    'email' => '',
    'firstName' => '',
    'lastName' => '',
    'contact_number' => '',
    'address' => '',
];

$clientAuthPath = __DIR__ . '/includes/client-auth.php';
if (file_exists($clientAuthPath)) {
    require_once $clientAuthPath;
    if (function_exists('client_auth_session_payload')) {
        $client = array_merge($client, client_auth_session_payload());
    }
}
$fullName = trim(($client['firstName'] ?? '') . ' ' . ($client['lastName'] ?? ''));
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
                    <?php if (!$isLoggedIn): ?>
                        <div class="bg-light p-3 rounded mb-4 border text-center">
                            <div class="small fw-semibold mb-2"><i class="fas fa-info-circle text-info me-1"></i> Speed up your checkout:</div>
                            <div class="d-flex gap-2 justify-content-center">
                                <a class="btn btn-sm btn-outline-dark fw-semibold py-1 px-3 d-flex align-items-center gap-1" href="<?= $basePath ?>controllers/auth/oauth-start.php?provider=google&return_to=<?= urlencode($_SERVER['REQUEST_URI']) ?>">
                                    <i class="fab fa-google text-danger"></i> Google
                                </a>
                                <a class="btn btn-sm btn-primary fw-semibold py-1 px-3 d-flex align-items-center gap-1" href="<?= $basePath ?>controllers/auth/oauth-start.php?provider=facebook&return_to=<?= urlencode($_SERVER['REQUEST_URI']) ?>" style="background-color: #1877F2; border-color: #1877F2;">
                                    <i class="fab fa-facebook-f"></i> Facebook
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

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
                            <input class="form-control" id="cust_email" name="customerEmail" type="email" value="<?= htmlspecialchars($client['email'] ?? $_SESSION['user_email'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="cust_phone">Mobile number</label>
                            <input class="form-control" id="cust_phone" name="customerPhone" value="<?= htmlspecialchars($client['contact_number'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="cust_address">Complete address</label>
                            <textarea class="form-control" id="cust_address" name="customerAddress" rows="3" required><?= htmlspecialchars($client['address'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="delivery_location">Delivery location</label>
                            <select class="form-select" id="delivery_location" name="deliveryLocation" required>
                                <option value="">Loading delivery rates...</option>
                            </select>
                            <div class="form-text">Origin: Madrigal Business Park, Alabang, Muntinlupa</div>
                        </div>

                        <div class="my-4">
                            <div class="summary-line">
                                <span>Items subtotal</span>
                                <strong id="summarySubtotal">PHP 0.00</strong>
                            </div>
                            <div class="summary-line">
                                <span>Delivery</span>
                                <strong id="summaryDelivery">PHP 0.00</strong>
                            </div>
                            <div class="summary-total">
                                <span>Total</span>
                                <span id="summaryTotal">PHP 0.00</span>
                            </div>
                        </div>

                        <div class="alert alert-danger d-none" id="checkoutError"></div>

                        <button class="btn btn-maya w-100" id="mayaCheckoutBtn" type="submit">
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
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/checkout-auth.js?v=<?= filemtime(__DIR__ . '/assets/checkout-auth.js') ?>"></script>
    <script src="assets/checkout.js?v=<?= filemtime(__DIR__ . '/assets/checkout.js') ?>"></script>
</body>
</html>
