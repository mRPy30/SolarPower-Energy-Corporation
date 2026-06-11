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
    <title>Privacy Policy | SolarPower Energy Corporation</title>
    <meta name="description" content="Read the Privacy Policy for SolarPower Energy Corporation. Learn how we collect, protect, and use information from our solar calculator and lead forms in compliance with data privacy standards." />
    <meta name="keywords" content="privacy policy, data protection, solar calculator Philippines, Bill Calculator, SolarPower Energy Corp" />
    <meta name="author" content="SolarPower Energy Corporation" />
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="https://solarpower.com.ph/privacy-policy.php" />

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:title" content="Privacy Policy | SolarPower Energy Corporation" />
    <meta property="og:description" content="Learn how we securely handle and protect your personal information at SolarPower Energy Corporation." />
    <meta property="og:image" content="https://solarpower.com.ph/assets/img/logo.png" />
    <meta property="og:url" content="https://solarpower.com.ph/privacy-policy.php" />

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

        .hero-privacy {
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
    <section class="hero-privacy">
        <div class="container" data-aos="zoom-in">
            <span class="text-warning fw-bold text-uppercase tracking-wider">Legal Compliance</span>
            <h1 class="display-4 fw-bold mt-2 text-white">Privacy Policy</h1>
            <p class="fs-6 text-white text-opacity-75 mb-0">Last Updated: June 2026</p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-5 bg-white">
        <div class="container max-w-4xl px-4 py-3">
            <div class="legal-content" data-aos="fade-up">
                
                <h2>1. Introduction</h2>
                <p>
                    At <strong>SolarPower Energy Corporation</strong> (operating "solarpower.com.ph"), we are highly committed to protecting the privacy, security, and confidentiality of our clients' personal data. This Privacy Policy details how we collect, process, secure, and share your personal information in accordance with the Data Privacy Act of 2012 (Republic Act No. 10173) of the Philippines and international data protection standards.
                </p>
                <p>
                    By using our website, submitting lead inquiry forms, using our interactive Solar Bill Calculator, or contacting us through our online communication widgets, you consent to the data collection and processing practices described in this policy.
                </p>

                <h2>2. Information We Collect</h2>
                <p>
                    To provide accurate solar energy solutions and personalized estimates, we collect several types of information from our website visitors:
                </p>
                <ul>
                    <li>
                        <strong>Personal Contact Identification:</strong> Your name, email address, mobile phone number, and installation/delivery address provided when you fill out lead generation inquiry forms, subscribe to our newsletter, or initiate live chat widgets.
                    </li>
                    <li>
                        <strong>Solar Calculator Logs:</strong> Inputs provided to our interactive Solar Calculator / Bill Calculator (such as your average estimated monthly electricity bill in PHP, utility provider name, and property type) to calculate potential monthly and yearly energy savings.
                    </li>
                    <li>
                        <strong>Communication Data:</strong> Records of your inquiries, chats, and requests sent via Viber, Facebook Messenger, WhatsApp, or standard contact emails.
                    </li>
                    <li>
                        <strong>Technical Device & Analytics Data:</strong> IP address, browser type, operating system, referrer URLs, page views, and activity flow patterns captured automatically through cookies and diagnostic tracking tags.
                    </li>
                </ul>

                <h2>3. How We Use Your Information</h2>
                <p>
                    We process and utilize your personal data exclusively for clean, transparent business transactions and service optimizations:
                </p>
                <ol>
                    <li>To generate, customize, and deliver your estimated solar system capacity and financial ROI savings reports based on your solar calculator inputs.</li>
                    <li>To contact you directly via our official communication channels—including our Phone Hotline (+63 995 394 7379), Facebook Messenger, and Viber deep links—to answer inquiries and schedule site assessments.</li>
                    <li>To manage customer relationships, provide technical support, and confirm system service appointments.</li>
                    <li>To improve and optimize our website content, calculator algorithms, and user experiences.</li>
                    <li>To run and optimize targeted promotional campaigns, such as Google Ads and Facebook Ads, ensuring you receive relevant information and updates regarding renewable energy offers.</li>
                </ol>

                <h2>4. Data Protection & Secure Storage</h2>
                <p>
                    We implement standard physical, technical, and administrative security protocols to ensure that your personal information remains strictly confidential and protected against unauthorized access, alterations, disclosure, or accidental destruction.
                </p>
                <p>
                    <strong>Crucially, SolarPower Energy Corp. does not sell, rent, trade, or share your personal client data with third-party marketing companies.</strong> Any data shared with external service providers (e.g., payment gateways, shipping couriers) is conducted strictly under binding confidentiality agreements solely to facilitate your requested transactions.
                </p>

                <h2>5. Cookies & Tracking Technologies</h2>
                <p>
                    We use cookies and similar tracking pixels to enhance your browsing experience, analyze site traffic, and optimize marketing campaigns.
                </p>
                <ul>
                    <li><strong>Google Analytics:</strong> Helps us understand visitor demographic details, traffic sources, and site page popularity.</li>
                    <li><strong>Google Ads Conversion Tags & Pixels:</strong> Helps us measure the effectiveness of our search engine advertisements and deliver relevant solar energy promotions to users who have previously shown interest in our energy savings calculators.</li>
                </ul>
                <p>
                    You have the option to disable cookies through your browser settings; however, doing so may affect your ability to fully utilize some features of our website (including our interactive savings calculator).
                </p>

                <h2>6. Your Rights</h2>
                <p>
                    Under the Data Privacy Act of 2012, you hold the right to be informed, access, correct, object to processing, or request the deletion of your personal data from our active storage files. To exercise any of these rights, please contact our Data Protection Officer using the details below.
                </p>

                <h2>7. Contact Us</h2>
                <p>
                    If you have questions about this Privacy Policy, your personal information, or wish to request data correction/removal, feel free to reach out to us at:
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
