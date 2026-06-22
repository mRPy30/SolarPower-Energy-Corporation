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
    <title>Refund & Cancellation Policy | SolarPower Energy Corporation</title>
    <meta name="description" content="Read the Refund, Return, and Cancellation Policy for SolarPower Energy Corporation. Learn about our physical equipment return policies and project cancellation guidelines." />
    <meta name="keywords" content="refund policy, return policy, cancellation policy, solar warranty, SolarPower Energy Corp" />
    <meta name="author" content="SolarPower Energy Corporation" />
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="https://solarpower.com.ph/refund-policy.php" />

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:title" content="Refund & Cancellation Policy | SolarPower Energy Corporation" />
    <meta property="og:description" content="Review the Return, Refund, and Contract Cancellation policies at SolarPower Energy Corporation." />
    <meta property="og:image" content="https://solarpower.com.ph/assets/img/logo.png" />
    <meta property="og:url" content="https://solarpower.com.ph/refund-policy.php" />

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

        .hero-refund {
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
    <section class="hero-refund">
        <div class="container" data-aos="zoom-in">
            <span class="text-warning fw-bold text-uppercase tracking-wider">Legal Compliance</span>
            <h1 class="display-4 fw-bold mt-2 text-white">Refund & Cancellation Policy</h1>
            <p class="fs-6 text-white text-opacity-75 mb-0">Last Updated: June 2026</p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-5 bg-white">
        <div class="container max-w-4xl px-4 py-3">
            <div class="legal-content" data-aos="fade-up">
                
                <h2>1. Solar Equipment Purchase Returns</h2>
                <p>
                    For direct purchases of physical solar equipment (such as solar panels, hybrid inverters, solar batteries, and mounting hardware) through our site or official platforms:
                </p>
                <ul>
                    <li><strong>Return Window:</strong> You may request a return or replacement within 7 calendar days from the date of delivery.</li>
                    <li><strong>Condition:</strong> To be eligible for a return, the equipment must be unused, in the same brand-new condition that you received it, and must be in its original packaging with all product warranty tags, manuals, and accessories intact.</li>
                    <li><strong>Inspection:</strong> Once we receive your returned item, our quality assurance engineering team will inspect the hardware. We will notify you of the approval or rejection of your refund request.</li>
                </ul>

                <h2>2. Project Cancellation Policy</h2>
                <p>
                    For custom residential, commercial, or industrial grid-tied and hybrid solar PV installations under signed engineering contracts:
                </p>
                <ul>
                    <li><strong>Cancellation Prior to Engineering Design:</strong> You may cancel your project installation contract within 3 business days of signing for a full refund of any initial down payments, minus any actual administrative or permitting costs incurred by SolarPower Energy Corporation.</li>
                    <li><strong>Cancellation After Procurement & Engineering Phase:</strong> If cancellation is requested after structural plans have been engineered or equipment has been procured, refund amounts will be calculated on a pro-rata basis. The cost of custom engineering drawings, localized site inspections, and non-returnable customized equipment will be deducted from your deposit.</li>
                    <li><strong>Post-Installation:</strong> Once installation works are completed on your property roof, the contract cannot be cancelled, and payments are non-refundable. All post-installation concerns are covered under our comprehensive system performance warranties.</li>
                </ul>

                <h2>3. Refund Processing</h2>
                <p>
                    Approved refunds will be processed and returned to your original payment method (e.g., bank transfer, credit card, or GCash) within 15 to 30 business days, subject to the clearing times of participating financial institutions in the Philippines.
                </p>

                <h2>4. Shipping Costs</h2>
                <p>
                    You will be responsible for paying for your own shipping/handling costs for returning physical solar equipment. Shipping costs are non-refundable. If you receive a refund, the cost of return shipping will be deducted from your refund.
                </p>

                <h2>5. Contact Us</h2>
                <p>
                    If you have any questions about returns, refunds, or cancelling your solar system contract, please contact us at:
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
