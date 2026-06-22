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
    <title>Terms of Service | SolarPower Energy Corporation</title>
    <meta name="description" content="Read the Terms of Service for SolarPower Energy Corporation. Learn about our service agreements, warranties, calculations, and general guidelines for solar panel installations in the Philippines." />
    <meta name="keywords" content="terms of service, solar agreement, solar installer terms, SolarPower Energy Corp" />
    <meta name="author" content="SolarPower Energy Corporation" />
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="https://solarpower.com.ph/terms-of-service.php" />

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:title" content="Terms of Service | SolarPower Energy Corporation" />
    <meta property="og:description" content="Review the Terms of Service governing the use of SolarPower Energy Corporation services and online platforms." />
    <meta property="og:image" content="https://solarpower.com.ph/assets/img/logo.png" />
    <meta property="og:url" content="https://solarpower.com.ph/terms-of-service.php" />

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

        .hero-terms {
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
    <section class="hero-terms">
        <div class="container" data-aos="zoom-in">
            <span class="text-warning fw-bold text-uppercase tracking-wider">Legal Compliance</span>
            <h1 class="display-4 fw-bold mt-2 text-white">Terms of Service</h1>
            <p class="fs-6 text-white text-opacity-75 mb-0">Last Updated: June 2026</p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-5 bg-white">
        <div class="container max-w-4xl px-4 py-3">
            <div class="legal-content" data-aos="fade-up">
                
                <h2>1. Acceptance of Terms</h2>
                <p>
                    By accessing or using the website of <strong>SolarPower Energy Corporation</strong> (operating under "solarpower.com.ph"), utilizing our interactive Solar Calculators, purchasing solar packages, or contracting our installation services, you agree to comply with and be bound by these Terms of Service. If you do not agree, please do not use our services or website.
                </p>

                <h2>2. Description of Services</h2>
                <p>
                    SolarPower Energy Corporation is a DOE-accredited solar developer in the Philippines. We provide engineering, procurement, construction, and maintenance of solar photovoltaic (PV) power systems for residential, commercial, and industrial properties.
                </p>

                <h2>3. Use of Interactive Calculators</h2>
                <p>
                    All calculations, estimates, and financial projections provided by our Solar Bill Calculator or lead estimators are indicators for informational purposes only. The actual system sizing, technical requirements, generation capability, and financial ROI depend on formal on-site structural engineering assessments, utility rate variations, and local weather patterns.
                </p>

                <h2>4. Intellectual Property</h2>
                <p>
                    All content, branding, calculations, system configurations, logos, images, and text contained on this website are the property of SolarPower Energy Corporation and are protected by applicable intellectual property and copyright laws. Unauthorized reproduction, modification, or distribution is strictly prohibited.
                </p>

                <h2>5. Limitation of Liability</h2>
                <p>
                    SolarPower Energy Corporation and its affiliates shall not be liable for any indirect, incidental, or consequential damages resulting from the use of, or inability to use, our online tools, solar equipment, or installation services, to the fullest extent permitted under Philippine law.
                </p>

                <h2>6. Governing Law</h2>
                <p>
                    These Terms of Service are governed by and construed in accordance with the laws of the Republic of the Philippines. Any disputes arising from these terms or our services shall be subject to the exclusive jurisdiction of the competent courts of Muntinlupa City, Metro Manila, Philippines.
                </p>

                <h2>7. Contact Us</h2>
                <p>
                    If you have any questions or clarifications regarding these Terms of Service, please contact our support team at:
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
