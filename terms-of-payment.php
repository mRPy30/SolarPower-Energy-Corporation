<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/icon.png">
    
    <!-- Primary Meta Tags -->
    <title>Terms of Payment & Scope of Work | SolarPower Energy Corporation</title>
    <meta name="description" content="Review the Terms of Payment, Scope of Work, and Warranty guidelines for SolarPower Energy Corporation solar panel installations in the Philippines." />
    <meta name="keywords" content="payment terms, scope of work, solar warranty, SolarPower Energy Corp" />
    <meta name="author" content="SolarPower Energy Corporation" />
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="https://solarpower.com.ph/terms-of-payment.php" />

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:title" content="Terms of Payment & Scope of Work | SolarPower Energy Corporation" />
    <meta property="og:description" content="Review our payment terms, scope of works, and warranty provisions." />
    <meta property="og:image" content="https://solarpower.com.ph/assets/img/logo.png" />
    <meta property="og:url" content="https://solarpower.com.ph/terms-of-payment.php" />

    <!-- CDN Stylesheets -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">

    <!-- Tailwind CSS with configuration to prevent preflight conflicts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            corePlugins: {
                preflight: false,
            },
            theme: {
                extend: {
                    colors: {
                        brand: {
                            green: '#0D5C3A',
                            forest: '#0a5c3d',
                            gold: '#F2A900',
                            dark: '#1e293b'
                        }
                    }
                }
            }
        }
    </script>

    <style>
        :root {
            --clr-primary: #F2A900; 
            --clr-secondary: #0D5C3A; 
            --shadow-box: 0 10px 30px rgba(0,0,0,0.05);
        }

        .hero-payment {
            background: linear-gradient(to right, rgba(13, 92, 58, 0.95), rgba(10, 92, 61, 0.85)), 
                url('assets/img/service.png') no-repeat center center/cover;
            height: 35vh; 
            display: flex; 
            align-items: center; 
            color: white; 
            text-align: center;
        }

        .legal-content p {
            font-size: 1.05rem;
            line-height: 1.8;
            color: #475569; /* Slate 600 */
            margin-bottom: 1.5rem;
        }

        .legal-content h2 {
            color: #0D5C3A;
            font-weight: 700;
            font-size: 1.75rem;
            margin-top: 2.5rem;
            margin-bottom: 1rem;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 0.5rem;
        }

        .legal-content h3 {
            color: #1e293b;
            font-weight: 600;
            font-size: 1.25rem;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }

        .legal-content ul, .legal-content ol {
            margin-bottom: 1.5rem;
            padding-left: 1.5rem;
        }

        .legal-content li {
            font-size: 1.05rem;
            line-height: 1.8;
            color: #475569;
            margin-bottom: 0.5rem;
        }

        .contact-card {
            background-color: #f8fafc;
            border-left: 4px solid #0D5C3A;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-top: 2rem;
            box-shadow: var(--shadow-box);
        }
    </style>
</head>
<body>

    <?php include "includes/header.php" ?>

    <!-- Hero Section -->
    <section class="hero-payment">
        <div class="container" data-aos="zoom-in">
            <span class="text-warning fw-bold text-uppercase tracking-wider">Corporate Policy</span>
            <h1 class="display-4 fw-bold mt-2 text-white">Terms of Payment & Scope of Work</h1>
            <p class="fs-6 text-white text-opacity-75 mb-0">Last Updated: June 2026</p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-5 bg-white">
        <div class="container max-w-4xl px-4 py-3">
            <div class="legal-content" data-aos="fade-up">
                
                <h2>1. Terms of Payment</h2>
                <p>
                    SolarPower Energy Corporation enforces structured payment schedules to manage engineering, logistics, and installation timelines effectively:
                </p>
                <h3>Labor & Materials Allocation</h3>
                <ul>
                    <li><strong>50% Downpayment:</strong> Required upon official signing of the contract to initiate equipment procurement.</li>
                    <li><strong>30% Progress Payment:</strong> Due prior to installation starting and upon approval of the localized installation layout plan.</li>
                    <li><strong>20% Final Settlement:</strong> Due immediately upon successful system commissioning and electrical load testing.</li>
                </ul>

                <h3>Stocks & Delivery Timeframes</h3>
                <ul>
                    <li><strong>Material Sourcing Lead Time:</strong> Estimated between 15 to 30 calendar days for custom solar kit components.</li>
                    <li><strong>Delivery Window:</strong> Completed within 3 to 5 business days once all materials are sorted in our warehouse facility.</li>
                </ul>

                <h3>Financing & Payment Methods</h3>
                <ul>
                    <li><strong>Flexible Financing:</strong> Up to 12 Months installment terms are available for qualified residential clients at a flat rate of 12% Interest.</li>
                    <li><strong>Special Promotions:</strong> Subject to current "Install Now, Pay Later" promotions as advertised.</li>
                    <li><strong>Payment Modes:</strong> We accept cash, corporate checks, bank transfers, and all major credit cards.</li>
                </ul>

                <h2>2. Scope of Works & General Provisions</h2>
                <p>
                    Our services strictly follow professional engineering guidelines and clear pricing schedules:
                </p>
                <h3>Primary Scope of Work</h3>
                <ul>
                    <li>The scope of work is limited strictly to the <strong>supply and installation of solar PV panels, inverters, structural mounting systems, and specified system accessories</strong> as listed in your approved official quotation.</li>
                </ul>

                <h3>Pricing & Billing Terms</h3>
                <ul>
                    <li><strong>Queuing Policy:</strong> Operations follow a strict "First come, first serve" scheduling basis.</li>
                    <li><strong>Price Adjustments:</strong> Quoted prices are subject to change without prior notice due to international shipping rate volatility.</li>
                    <li><strong>Additional Works:</strong> Any electrical materials or structural support upgrades not specified in the initial contract are billed separately as an additional order.</li>
                    <li><strong>Quotations:</strong> All pricing options are valid for exactly 15 days from the date of sending and are fundamentally inclusive of VAT.</li>
                    <li><strong>Check Clearances:</strong> Deliveries will only be scheduled after bank check payments are fully cleared (a standard 3-banking-day clearing period is mandatory).</li>
                    <li><strong>Permitting & Approvals:</strong> Securing local barangay clearances, home-owner association gate passes, zoning permits, and Net-Metering applications is the sole responsibility of the client.</li>
                </ul>

                <h2>3. System Warranties & Maintenance</h2>
                <p>
                    We protect your renewable energy investment with comprehensive industry-standard warranties and proactive upkeep:
                </p>
                <h3>Product Warranties</h3>
                <ul>
                    <li><strong>Solar PV Panels:</strong> 12 Years product warranty against manufacturing defects.</li>
                    <li><strong>Solar Inverters:</strong> 5 Years manufacturer warranty.</li>
                    <li><strong>Workmanship & Service:</strong> 2 Years full installer workmanship warranty.</li>
                </ul>

                <h3>Preventive Maintenance & Upkeep</h3>
                <ul>
                    <li><strong>Panel Cleaning Service:</strong> Includes 2 free comprehensive cleanings during the first year of system operation. This removal of dirt, bird droppings, and dust prevents long-term solar panel hot-spots and generation losses.</li>
                    <li><strong>Periodic Inspections:</strong> Complimentary checks every 6 months to inspect wiring for wear, check mounting tightness, and scan panels for structural damage.</li>
                    <li><strong>Inverter Diagnostic Checks:</strong> Comprehensive diagnostic runs and firmware updates to ensure optimal energy conversion parameters.</li>
                </ul>

                <h2>4. Contact Us</h2>
                <p>
                    For inquiries regarding payment terms, financing applications, or detailed scopes of work, please reach our billing department at:
                </p>
                
                <div class="contact-card">
                    <h3 class="m-0 text-brand-green font-bold text-lg mb-2">SolarPower Energy Corp.</h3>
                    <p class="m-0 text-sm mb-1"><i class="fas fa-map-marker-alt text-warning me-2"></i> Madrigal Business Park, Muntinlupa City, Metro Manila, Philippines</p>
                    <p class="m-0 text-sm mb-1"><i class="fas fa-phone text-warning me-2"></i> Hotline: <a href="tel:+639953947379" class="text-brand-green font-semibold hover:underline">+63 995 394 7379</a></p>
                    <p class="m-0 text-sm mb-1"><i class="fab fa-facebook-messenger text-warning me-2"></i> Messenger: <a href="https://m.me/61578373983187" target="_blank" class="text-brand-green font-semibold hover:underline">m.me/61578373983187</a></p>
                    <p class="m-0 text-sm"><i class="fab fa-viber text-warning me-2"></i> Viber: <a href="viber://chat?number=639953947379" class="text-brand-green font-semibold hover:underline">+63 995 394 7379</a></p>
                </div>

            </div>
        </div>
    </section>

    <?php include "includes/footer.php" ?>

    <!-- JS Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true
        });
    </script>
</body>
</html>
