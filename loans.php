<?php
// Get current page filename for isActive helper in header
$current_page = 'loans.php';
include "config/dbconn.php";

/* ---------- Fetch Calculator Settings ---------- */
$calc_settings = [
    'solar_panel_wattage' => 400,
    'kwh_rate' => 12.00,
    'average_sun_hours' => 4.50
];
$res_calc = $conn->query("SELECT * FROM calculator_settings LIMIT 1");
if ($res_calc && $row_calc = $res_calc->fetch_assoc()) {
    $calc_settings = $row_calc;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/icon.png">
    <!-- Primary Meta Tags -->
    <title>GSIS, SSS, and Pag-IBIG Solar Financing Hub | SolarPower Energy Corporation</title>
    <meta name="description" content="Explore state-backed solar panel financing options in the Philippines. Learn how to apply for GSIS Ginhawa Solar Energy Loan, SSS, and Pag-IBIG housing improvement loans." />
    <meta name="keywords" content="GSIS solar loan, Pag-IBIG solar financing, SSS solar panel loan, government solar financing Philippines, home improvement solar loan" />
    <meta name="author" content="SolarPower Energy Corporation" />
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="https://solarpower.com.ph/loans.php" />

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:title" content="GSIS, SSS, and Pag-IBIG Solar Financing Hub | SolarPower Energy Corporation" />
    <meta property="og:description" content="Calculate and apply for government solar panel financing programs including GSIS Ginhawa Solar Energy Loan, SSS, and Pag-IBIG housing loans." />
    <meta property="og:image" content="https://solarpower.com.ph/assets/img/logo.png" />
    <meta property="og:url" content="https://solarpower.com.ph/loans.php" />

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    
    <!-- Google Fonts for Modern Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;600;700;800&family=Syne:wght@700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --solar-green: #0D5C3A;
            --solar-green-hover: #08492d;
            --solar-gold: #F2A900;
            --solar-bg-gray: #F8F9FA;
            --solar-white: #FFFFFF;
            --solar-text-dark: #1E293B;
            --solar-text-muted: #64748B;
            --ff-poppins: 'Poppins', sans-serif;
            --ff-inter: 'Inter', sans-serif;
            --ff-syne: 'Syne', sans-serif;
        }

        body {
            font-family: var(--ff-inter);
            color: var(--solar-text-dark);
            background-color: var(--solar-bg-gray);
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, .brand-font {
            font-family: var(--ff-poppins);
            font-weight: 700;
            color: var(--solar-text-dark);
        }

        /* ── SECTION 1: HERO COVER PAGE (The Split Screen Savings Engine) ── */
        .solar-loans-hero {
            min-height: 450px;
            background: linear-gradient(135deg, rgba(11, 46, 32, 0.9) 0%, rgba(20, 35, 55, 0.85) 100%),
                        url('assets/img/solarloans.jpg') no-repeat center center/cover;
            display: flex;
            align-items: center;
            padding: 60px 0;
            position: relative;
            z-index: 1;
        }

        .solar-loans-hero .hero-badge {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--solar-gold);
            margin-bottom: 10px;
            display: inline-block;
        }

        .solar-loans-hero h1 {
            font-family: var(--ff-poppins);
            font-weight: 800;
            font-size: 2.3rem;
            line-height: 1.2;
        }

        .solar-loans-hero .lead {
            font-size: 0.95rem;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.8) !important;
            margin-bottom: 20px;
        }

        /* Calculator Card container */
        .calculator-card {
            background: var(--solar-white) !important;
            border-radius: 18px !important;
            border: none !important;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15) !important;
            position: relative;
            z-index: 10;
            max-width: 800px;
            margin: 0 auto;
        }

        .slider-group {
            margin-bottom: 25px;
        }

        .slider-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .slider-label {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--solar-text-muted);
        }

        .slider-val-box {
            font-family: var(--ff-poppins);
            font-weight: 800;
            font-size: 1.4rem;
            color: var(--solar-green);
        }

        .custom-range {
            height: 6px;
            border-radius: 5px;
            background: #e2e8f0;
            outline: none;
        }

        .custom-range::-webkit-slider-thumb {
            background: var(--solar-green);
            width: 20px;
            height: 20px;
            border-radius: 50%;
            cursor: pointer;
            transition: transform 0.1s;
        }

        .custom-range::-webkit-slider-thumb:hover {
            transform: scale(1.2);
        }

        /* Dark Green Output Panel */
        .calc-output-panel {
            background: var(--solar-green);
            border-radius: 18px;
            padding: 20px;
            color: var(--solar-white);
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .output-row {
            display: flex;
            align-items: center;
            gap: 15px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 14px 18px;
            border-radius: 14px;
        }

        .output-icon-box {
            width: 44px;
            height: 44px;
            background: var(--solar-gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1a1a1a;
            font-size: 1.25rem;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(242, 169, 0, 0.25);
        }

        .output-meta {
            display: flex;
            flex-direction: column;
        }

        .output-meta span {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 2px;
        }

        .output-meta h3 {
            margin: 0;
            font-size: 1.75rem;
            font-weight: 800;
            font-family: var(--ff-poppins);
            color: var(--solar-white);
            line-height: 1.1;
        }

        .monthly-roi-text {
            color: var(--solar-gold) !important;
        }

        /* ── SECTION 2: GOVERNMENT PROGRAMS (Smart-Tab Hub) ── */
        .smart-tab-section {
            padding: 100px 0;
            background-color: var(--solar-white);
        }

        .section-header-center {
            text-align: center;
            max-width: 700px;
            margin: 0 auto 50px;
        }

        .section-header-center h2 {
            font-size: 2.5rem;
            color: var(--solar-green);
            font-weight: 800;
        }

        /* Minimalist central pills styling */
        .smart-tab-nav {
            background: var(--solar-bg-gray);
            border-radius: 50px;
            padding: 6px;
            border: 1px solid #E2E8F0;
            max-width: 720px;
            margin: 0 auto 40px;
            display: flex;
        }

        .smart-tab-nav .nav-item {
            flex: 1;
        }

        .smart-tab-nav .nav-link {
            border-radius: 50px;
            color: var(--solar-text-muted);
            font-weight: 600;
            font-size: 0.92rem;
            padding: 12px 0;
            text-align: center;
            border: none;
            background: transparent;
            width: 100%;
            transition: all 0.25s ease;
        }

        .smart-tab-nav .nav-link.active {
            background-color: var(--solar-green) !important;
            color: var(--solar-white) !important;
            box-shadow: 0 4px 15px rgba(13, 92, 58, 0.18);
        }

        /* Horizontal Layout inside Tab content */
        .tab-panel-inner {
            background: var(--solar-bg-gray);
            border-radius: 24px;
            border: 1px solid #E2E8F0;
            overflow: hidden;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.02);
        }

        .tab-project-img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 16px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.06);
        }

        .gold-checkmark-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .gold-checkmark-list li {
            position: relative;
            padding-left: 32px;
            margin-bottom: 20px;
            font-size: 1.05rem;
            color: var(--solar-green);
            line-height: 1.5;
        }

        .gold-checkmark-list li::before {
            content: "\f058";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            left: 0;
            top: 2px;
            color: var(--solar-gold);
            font-size: 1.35rem;
        }

        /* ── SECTION 3: SOCIAL PROOF (Visual Bill-Slider) ── */
        .social-proof-section {
            padding: 100px 0;
            background-color: var(--solar-bg-gray);
        }

        .proof-testimonial-card {
            background: var(--solar-white);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(13, 92, 58, 0.04);
            border-left: 6px solid var(--solar-green);
            position: relative;
        }

        .proof-quote-mark {
            font-family: var(--ff-poppins);
            font-size: 5rem;
            color: rgba(13, 92, 58, 0.08);
            position: absolute;
            top: 10px;
            left: 20px;
            line-height: 1;
            pointer-events: none;
        }

        /* Before/After Split Screen Slider */
        .visual-slider-container {
            position: relative;
            width: 100%;
            height: 400px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }

        .slider-image-layer {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
        }

        .slider-before-layer {
            background-image: linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.45)), url('assets/img/electricity.jpg');
        }

        .slider-after-layer {
            background-image: linear-gradient(rgba(0,0,0,0.1), rgba(0,0,0,0.1)), url('assets/img/mock_solar_roof_3d.png');
            width: 50%;
        }

        .slider-bar-divider {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 50%;
            width: 4px;
            background: var(--solar-gold);
            cursor: ew-resize;
            z-index: 20;
            box-shadow: 0 0 8px rgba(0,0,0,0.4);
        }

        .slider-divider-btn {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 42px;
            height: 42px;
            background: var(--solar-gold);
            border-radius: 50%;
            border: 3px solid var(--solar-white);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1a1a1a;
            box-shadow: 0 4px 8px rgba(0,0,0,0.25);
            pointer-events: none;
        }

        /* ── SECTION 4: REPLACEMENT SECTION (Checklist Wizard) ── */
        .readiness-wizard-section {
            padding: 100px 0;
            background-color: var(--solar-white);
        }

        .desk-vector-img {
            width: 100%;
            height: auto;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            max-height: 420px;
            object-fit: cover;
        }

        /* Interactive Checklist cards */
        .checklist-panel {
            background: var(--solar-bg-gray);
            border: 1px solid #E2E8F0;
            border-radius: 24px;
            padding: 35px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.01);
        }

        .checklist-title-desc {
            font-size: 0.95rem;
            color: var(--solar-text-muted);
            margin-bottom: 25px;
        }

        .custom-checklist-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            background: var(--solar-white);
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .custom-checklist-item:hover {
            border-color: var(--solar-green);
            background: rgba(13, 92, 58, 0.01);
        }

        .custom-checkbox-box {
            width: 22px;
            height: 22px;
            border-radius: 6px;
            border: 2px solid #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.2s ease;
            margin-top: 2px;
            color: white;
            font-size: 0.8rem;
        }

        .custom-checklist-item.checked .custom-checkbox-box {
            background-color: var(--solar-green);
            border-color: var(--solar-green);
        }

        .checklist-text {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--solar-text-dark);
            user-select: none;
        }

        .btn-green-proposal {
            background-color: var(--solar-green);
            color: var(--solar-white);
            border: none;
            font-weight: 700;
            border-radius: 50px;
            padding: 15px 30px;
            width: 100%;
            transition: all 0.25s ease;
            box-shadow: 0 4px 15px rgba(13, 92, 58, 0.25);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-green-proposal:hover {
            background-color: var(--solar-green-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(13, 92, 58, 0.4);
            color: white;
        }

        /* Proposal Lead Modal styling */
        .proposal-modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            overflow: hidden;
        }

        .proposal-modal-header {
            background: var(--solar-green);
            color: white;
            padding: 20px 25px;
            border: none;
        }

        .proposal-modal-body {
            padding: 30px 25px;
            background: var(--solar-bg-gray);
        }

        .form-input-premium {
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            padding: 12px 16px;
            background: white;
            font-size: 0.95rem;
            transition: border-color 0.2s;
        }

        .form-input-premium:focus {
            border-color: var(--solar-green);
            box-shadow: 0 0 0 3px rgba(13, 92, 58, 0.1);
        }

        /* Document Upload Slots styling */
        .doc-upload-row:hover {
            border-color: var(--solar-green) !important;
            background-color: rgba(13, 92, 58, 0.02);
        }
        .doc-upload-row.has-file {
            border-color: #0D5C3A !important;
            border-style: solid !important;
            background-color: rgba(13, 92, 58, 0.04);
        }
        .doc-upload-row.has-file .doc-icon {
            color: #0D5C3A !important;
        }
        .doc-upload-row.has-file .badge-status {
            background-color: #0D5C3A !important;
        }
    </style>
</head>

<body>

    <?php include "includes/header.php" ?>

    <!-- ── SECTION 1: HERO COVER PAGE (Centered Typography) ── -->
    <section class="solar-loans-hero">
        <div class="container">
            <div class="row align-items-center justify-content-center">
                <!-- Center Column: Copy -->
                <div class="col-lg-8 col-md-10 text-center" data-aos="fade-up">
                    <span class="hero-badge text-uppercase">Solar Power Financing</span>
                    <h1 class="display-4 fw-extrabold text-white mb-3">Feeling the Weight of High Power Bills? Let's Float an Idea.</h1>
                    <p class="lead text-white opacity-80 mb-4">
                        Electricity is one of the biggest financial burdens for Filipino households. Transition to solar power seamlessly through our flexible financing programs.
                    </p>
                    <div class="d-flex flex-row gap-3 justify-content-center">
                        <a href="#financingHub" class="btn btn-warning px-4 py-2.5 fw-bold rounded-pill text-uppercase text-decoration-none" style="background-color: #F2A900; border-color: #F2A900; color: #000000; font-size: 0.85rem;">
                            EXPLORE PROGRAMS
                        </a>
                        <a href="#loanCalculatorSection" class="btn btn-outline-light px-4 py-2.5 fw-bold rounded-pill text-uppercase text-decoration-none" style="font-size: 0.85rem;">
                            CALCULATE SAVINGS
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── SECTION 1.5: CONFIGURE YOUR SAVINGS CALCULATOR (Large Dedicated Section) ── -->
    <section class="py-5 bg-light" id="loanCalculatorSection">
        <div class="container">
            <div class="section-header-center text-center mb-5" data-aos="fade-up">
                <span class="text-uppercase fw-bold text-success" style="font-size: 0.85rem; letter-spacing: 1.5px; color: #0D5C3A !important;">Calculator Tool</span>
                <h2 class="fw-extrabold text-success mt-2" style="color: #0D5C3A; font-family: var(--ff-poppins); font-size: 2.5rem;">Configure Your Savings</h2>
                <p class="text-muted">Estimate your system cost, monthly amortization, and return on investment in real time.</p>
            </div>
            
            <div class="row justify-content-center" data-aos="fade-up">
                <div class="col-lg-10 col-xl-9">
                    <div class="calculator-card p-0 overflow-hidden" style="border-radius: 24px !important; box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1) !important;">
                        <div class="row g-0">
                            <!-- Left Column: Configurator -->
                            <div class="col-md-6 p-5 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <h4 class="fw-bold mb-4" style="color: #0D5C3A; font-family: var(--ff-poppins);">Input Parameters</h4>
                                    
                                    <!-- Parameter 1: Monthly Electric Bill -->
                                    <div class="slider-group mb-5">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="slider-label fw-bold text-uppercase" style="font-size: 0.8rem; color: #64748B; letter-spacing: 0.5px;">Monthly Electric Bill</span>
                                            <div class="input-group" style="width: 140px;">
                                                <span class="input-group-text bg-light border-end-0 fw-bold" style="color: #0D5C3A;">₱</span>
                                                <input type="number" id="billInput" class="form-control bg-light border-start-0 fw-bold" value="8000" min="3000" max="30000" step="500" style="color: #0D5C3A; font-size: 1.05rem;">
                                            </div>
                                        </div>
                                        <input type="range" class="form-range custom-range w-100" id="billSlider" min="3000" max="30000" step="500" value="8000">
                                        <div class="d-flex justify-content-between text-muted mt-2" style="font-size: 0.75rem;">
                                            <span>₱3,000</span>
                                            <span>₱30,000</span>
                                        </div>
                                    </div>

                                    <!-- Parameter 2: Loan Term -->
                                    <div class="slider-group mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="slider-label fw-bold text-uppercase" style="font-size: 0.8rem; color: #64748B; letter-spacing: 0.5px;">Loan Term</span>
                                            <div class="input-group" style="width: 120px;">
                                                <input type="number" id="termInput" class="form-control bg-light border-end-0 fw-bold text-center" value="15" min="5" max="30" step="5" style="color: #0D5C3A; font-size: 1.05rem;">
                                                <span class="input-group-text bg-light border-start-0 fw-bold" style="color: #0D5C3A;">Yrs</span>
                                            </div>
                                        </div>
                                        <input type="range" class="form-range custom-range w-100" id="termSlider" min="5" max="30" step="5" value="15">
                                        <div class="d-flex justify-content-between text-muted mt-2" style="font-size: 0.75rem;">
                                            <span>5 Years</span>
                                            <span>30 Years</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Results Display Panel -->
                            <div class="col-md-6 p-5 d-flex flex-column justify-content-between" style="background-color: #0D5C3A; color: #FFFFFF;">
                                <div>
                                    <!-- Metric 1: Est. Monthly Amortization -->
                                    <div class="mb-4">
                                        <span class="text-uppercase fw-bold text-white-50" style="font-size: 0.75rem; letter-spacing: 1px;">Est. Monthly Amortization</span>
                                        <h2 class="estimated-amortization text-white mb-0 fw-extrabold mt-1" id="calcAmort" style="font-size: 2.5rem; font-family: var(--ff-poppins);">₱3,953</h2>
                                    </div>

                                    <!-- Metric 2: Monthly Return (ROI) -->
                                    <div class="mb-4">
                                        <span class="text-uppercase fw-bold text-white-50" style="font-size: 0.75rem; letter-spacing: 1px;">Monthly Return (ROI)</span>
                                        <h2 class="monthly-roi mb-0 fw-extrabold mt-1" id="calcRoi" style="color: #F2A900; font-size: 2.5rem; font-family: var(--ff-poppins);">₱2,527</h2>
                                    </div>

                                    <!-- Clean Energy Savings breakdown -->
                                    <div class="border-top border-white border-opacity-15 pt-4 mb-4">
                                        <div class="d-flex justify-content-between text-white-50 mb-3" style="font-size: 0.9rem;">
                                            <span>Est. Solar System Size</span>
                                            <span class="text-white fw-bold" id="metricSystemSize">6.7 kWp</span>
                                        </div>
                                        <div class="d-flex justify-content-between text-white-50 mb-3" style="font-size: 0.9rem;">
                                            <span>Est. 10-Year Savings</span>
                                            <span class="text-white fw-bold" id="metric10YrSavings">₱780,000</span>
                                        </div>
                                        <div class="d-flex justify-content-between text-white-50" style="font-size: 0.9rem;">
                                            <span>Est. 25-Year Savings</span>
                                            <span class="fw-bold" style="color: #F2A900 !important;" id="metric25YrSavings">₱1,950,000</span>
                                        </div>
                                    </div>
                                </div>

                                <a href="#checklist" class="btn w-100 fw-bold py-3 d-flex align-items-center justify-content-center gap-2 text-uppercase text-decoration-none" style="background-color: #F2A900; border-color: #F2A900; color: #000000; border-radius: 8px; font-size: 0.95rem; transition: background-color 0.2s;">
                                    Apply for Financing &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── SECTION 2: NEW DESIGN FOR GOVERNMENT PROGRAMS (The "Smart-Tab" Financing Hub) ── -->
    <section class="py-5" id="programs" style="background-color: var(--solar-white);">
        <div class="container">
            <div class="section-header-center text-center mb-5" data-aos="fade-up">
                <h2 class="fw-extrabold text-success" style="color: #0D5C3A; font-family: var(--ff-poppins); font-size: 2.5rem;">Government Solar Loan Programs</h2>
                <p class="text-muted">Compare features side-by-side to target the correct solar program for your household upgrades.</p>
            </div>

            <!-- Central Nav Switcher -->
            <div class="row justify-content-center mb-5" data-aos="fade-up">
                <div class="col-lg-12">
                    <ul class="nav nav-pills nav-justified smart-tab-nav" id="smartTabs" role="tablist" style="background: var(--solar-bg-gray); border-radius: 50px; padding: 6px; border: 1px solid #E2E8F0;">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="gsis-tab-btn" data-bs-toggle="pill" data-bs-target="#panel-gsis" type="button" role="tab" aria-controls="panel-gsis" aria-selected="true" style="border-radius: 50px; font-weight: 600;">
                                GSIS Ginhawa Solar Loan
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pagibig-tab-btn" data-bs-toggle="pill" data-bs-target="#panel-pagibig" type="button" role="tab" aria-controls="panel-pagibig" aria-selected="false" style="border-radius: 50px; font-weight: 600;">
                                Pag-IBIG Solar Loan
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="sss-tab-btn" data-bs-toggle="pill" data-bs-target="#panel-sss" type="button" role="tab" aria-controls="panel-sss" aria-selected="false" style="border-radius: 50px; font-weight: 600;">
                                SSS Solar Loan
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Tab Content Area -->
            <div class="tab-content" id="smartTabsContent" data-aos="fade-up" data-aos-delay="100">
                <!-- GSIS Ginhawa Solar Energy Loan -->
                <div class="tab-pane fade show active" id="panel-gsis" role="tabpanel" aria-labelledby="gsis-tab-btn">
                    <div class="p-4 p-md-5" style="background: var(--solar-bg-gray); border-radius: 24px; border: 1px solid #E2E8F0;">
                        
                        <!-- Header & Intro -->
                        <div class="mb-5">
                            <h3 class="fw-extrabold text-success mb-3" style="color: #0D5C3A; font-family: var(--ff-poppins); font-size: 2rem;">GINHAWA SOLAR ENERGY LOAN</h3>
                            <p class="lead text-muted" style="font-size: 1.05rem; line-height: 1.7;">
                                The Ginhawa Solar Energy Loan (GSEL) gives GSIS members a smart and accessible way to shift to solar power by offering financing of up to ₱500,000 for home solar panel installation. With rising electricity costs, GSEL empowers members to take control of their energy expenses, reduce monthly bills, and enjoy long-term savings—all while increasing the value of their homes. It’s a practical investment that delivers immediate financial relief and lasting Ginhawa benefits, while supporting a cleaner, more sustainable future.
                            </p>
                        </div>

                        <!-- 2-Column Overview -->
                        <div class="row g-5 mb-5 align-items-center">
                            <div class="col-lg-7">
                                <!-- Primary Stats Header -->
                                <div class="row g-3 mb-4">
                                    <div class="col-sm-6 col-md-4">
                                        <div class="p-3 bg-white rounded-3 border-start border-4 border-success shadow-sm">
                                            <span class="d-block text-muted text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Interest Rate</span>
                                            <h3 class="fw-extrabold text-success mb-0" style="color: #0D5C3A; font-family: var(--ff-poppins); font-size: 1.4rem;">5% p.a.</h3>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4">
                                        <div class="p-3 bg-white rounded-3 border-start border-4 border-warning shadow-sm">
                                            <span class="d-block text-muted text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Max Amount</span>
                                            <h3 class="fw-extrabold text-success mb-0" style="color: #0D5C3A; font-family: var(--ff-poppins); font-size: 1.4rem;">₱500,000</h3>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4">
                                        <div class="p-3 bg-white rounded-3 border-start border-4 border-info shadow-sm">
                                            <span class="d-block text-muted text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Loan Term</span>
                                            <h3 class="fw-extrabold text-success mb-0" style="color: #0D5C3A; font-family: var(--ff-poppins); font-size: 1.4rem;">5 Years</h3>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-success d-flex align-items-center mb-0 border-0" style="background-color: rgba(13, 92, 58, 0.05); color: #0D5C3A;">
                                    <i class="fas fa-calculator me-3 fs-4 text-success"></i>
                                    <div>
                                        <strong>Monthly Amortization Example:</strong> For a maximum <strong>₱500,000.00</strong> loan over 5 years (5% per annum computed in advance), the monthly amortization is <strong>₱10,416.67</strong>.
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <img src="assets/img/gsis.png" class="img-fluid rounded-0 shadow-sm" alt="GSIS Solar Evaluation" style="max-height: 260px; width: 100%; object-fit: cover;">
                            </div>
                        </div>

                        <!-- Requirements & Process Grid -->
                        <div class="row g-4 mb-5">
                            <!-- Column 1: Qualified Borrowers -->
                            <div class="col-md-6">
                                <div class="bg-white p-4 rounded-4 shadow-sm h-100 border border-light">
                                    <h4 class="fw-bold mb-4" style="color: #0D5C3A; font-size: 1.25rem;"><i class="fas fa-user-check me-2 text-warning"></i>Qualified Borrowers</h4>
                                    <p class="text-muted small">Active members under permanent and non-career status (including tagged Special Members) provided the following conditions are met:</p>
                                    <ul class="gold-checkmark-list" style="font-size: 0.9rem;">
                                        <li>Must be in the service for at least three (3) years and has paid at least one (1) month premium contribution (Personal and Government Share) within the last six (6) months prior to application.</li>
                                        <li>Has no pending administrative case and/or criminal charge.</li>
                                        <li>Is not on leave without pay.</li>
                                        <li>Has no GSIS loan accounts in default (except housing loans).</li>
                                        <li>Employed in an Agency not classified as “suspended” based on GSIS records.</li>
                                        <li>Net Take Home Pay (NTHP) after loan amortization must meet the General Appropriations Act (GAA) minimum requirement.</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Column 2: Digital Application Process -->
                            <div class="col-md-6">
                                <div class="bg-white p-4 rounded-4 shadow-sm h-100 border border-light">
                                    <h4 class="fw-bold mb-4" style="color: #0D5C3A; font-size: 1.25rem;"><i class="fas fa-mobile-alt me-2 text-warning"></i>Application Process</h4>
                                    <p class="text-muted small">Applications are processed digitally through the <strong>GSIS Touch</strong> mobile application. One of the following supporting documents must be uploaded upon application:</p>
                                    
                                    <ul class="list-unstyled">
                                        <li class="mb-3 d-flex">
                                            <i class="fas fa-file-invoice-dollar text-success me-3 mt-1 fs-5"></i>
                                            <div>
                                                <strong>Solar Panel Proposal/Quote</strong>
                                                <div class="small text-muted">A detailed quotation from a solar panel installer indicating equipment, system size, and installation fees.</div>
                                            </div>
                                        </li>
                                        <li class="mb-3 d-flex">
                                            <i class="fas fa-file-contract text-success me-3 mt-1 fs-5"></i>
                                            <div>
                                                <strong>Installation Agreement/Contract</strong>
                                                <div class="small text-muted">A signed agreement with a solar panel installer outlining terms of installation, warranties, and conditions.</div>
                                            </div>
                                        </li>
                                        <li class="mb-3 d-flex">
                                            <i class="fas fa-receipt text-success me-3 mt-1 fs-5"></i>
                                            <div>
                                                <strong>Official Receipt (for reimbursements)</strong>
                                                <div class="small text-muted">An official receipt representing expenses incurred for the installation of the solar panel system.</div>
                                            </div>
                                        </li>
                                    </ul>
                                    <div class="border-top pt-3 mt-3">
                                        <p class="small text-muted mb-0"><i class="fas fa-info-circle text-info me-1"></i> The Agency Authorized Officer (AAO) must certify the loan application online prior to GSIS processing.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Loan Terms, Pre-termination & Insurance -->
                        <div class="row g-4">
                            <!-- Column 1: Financial Details -->
                            <div class="col-md-6">
                                <div class="bg-white p-4 rounded-4 shadow-sm h-100 border border-light">
                                    <h4 class="fw-bold mb-3" style="color: #0D5C3A; font-size: 1.25rem;"><i class="fas fa-hand-holding-usd me-2 text-warning"></i>Loan Details & Terms</h4>
                                    
                                    <div class="mb-3">
                                        <h6 class="fw-bold text-success mb-1" style="color: #0D5C3A;">Loanable Amount</h6>
                                        <p class="small text-muted">Covers up to 100% of the cost of solar panels, equipment, and installation fees, up to a maximum of <strong>₱500,000.00</strong> (not exceeding the cost reflected on the submitted quote/receipt).</p>
                                    </div>

                                    <div class="mb-3">
                                        <h6 class="fw-bold text-success mb-1" style="color: #0D5C3A;">Loan Term</h6>
                                        <p class="small text-muted">Repayment is made over five (5) years in sixty (60) equal monthly installments.</p>
                                    </div>

                                    <div>
                                        <h6 class="fw-bold text-success mb-1" style="color: #0D5C3A;">Loan Redemption Insurance (LRI)</h6>
                                        <p class="small text-muted mb-0">A one-time LRI is fully deducted in advance to safeguard both the borrower and GSIS in case of untimely death during the term.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Column 2: Pre-Termination & Cancellation -->
                            <div class="col-md-6">
                                <div class="bg-white p-4 rounded-4 shadow-sm h-100 border border-light">
                                    <h4 class="fw-bold mb-3" style="color: #0D5C3A; font-size: 1.25rem;"><i class="fas fa-ban me-2 text-warning"></i>Pre-Termination & Cancellation</h4>
                                    
                                    <div class="mb-3">
                                        <h6 class="fw-bold text-success mb-1" style="color: #0D5C3A;">Pre-Termination</h6>
                                        <p class="small text-muted">The loan may be pre-terminated by paying off the outstanding balance before the end of the term. <strong>No pre-termination fees</strong> shall be charged.</p>
                                    </div>

                                    <div>
                                        <h6 class="fw-bold text-success mb-1" style="color: #0D5C3A;">Loan Cancellation</h6>
                                        <p class="small text-muted mb-0">Cancellation is allowed within thirty (30) calendar days from the date of loan granting. The principal amount stated in the contract plus pro-rata interest must be paid in full.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Source Link -->
                        <div class="text-end mt-4">
                            <span class="text-muted small">Source: <a href="https://www.gsis.gov.ph/ginhawa-solar-energy-loan/" target="_blank" class="text-success fw-bold text-decoration-none">GSIS Ginhawa Solar Energy Loan Program <i class="fas fa-external-link-alt ms-1" style="font-size: 0.75rem;"></i></a></span>
                        </div>

                    </div>
                </div>

                <!-- Pag-IBIG Long-Term Saver Track -->
                <div class="tab-pane fade" id="panel-pagibig" role="tabpanel" aria-labelledby="pagibig-tab-btn">
                    <div class="p-4 p-md-5" style="background: var(--solar-bg-gray); border-radius: 24px; border: 1px solid #E2E8F0;">
                        
                        <!-- Header & Intro -->
                        <div class="mb-5">
                            <h3 class="fw-extrabold text-success mb-3" style="color: #0D5C3A; font-family: var(--ff-poppins); font-size: 2rem;">Pag-IBIG Solar Loan 2026: Complete Guide to Financing Solar with Housing Loans</h3>
                            <p class="lead text-muted" style="font-size: 1.05rem; line-height: 1.7;">
                                Looking to go solar but don't have ₱300,000–₱500,000 in cash? Your Pag-IBIG housing loan might be the solution. The Home Development Mutual Fund (HDMF) allows qualified members to finance solar panel installations as part of their home improvement loan—potentially turning your electricity savings into your monthly loan payment.
                            </p>
                        </div>

                        <!-- 2-Column Overview -->
                        <div class="row g-5 mb-5">
                            <div class="col-lg-7">
                                <h4 class="fw-bold mb-3" style="color: #0D5C3A; font-size: 1.4rem;">Can You Really Use Pag-IBIG for Solar?</h4>
                                <p class="text-muted mb-4" style="line-height: 1.6;">
                                    <strong>Yes.</strong> Pag-IBIG's housing loan program explicitly allows solar panel installations as a "home improvement" use of funds. Since 2015, Filipino homeowners have used Pag-IBIG financing to install solar, with the loan amount counted against the ₱6 million maximum housing loan limit.
                                </p>
                                <p class="text-muted mb-4" style="line-height: 1.6;">
                                    In 2026, with solar system costs at historic lows (₱55–75/watt installed), a ₱300,000–₱500,000 Pag-IBIG solar loan can now cover a complete 5–8 kW residential system.
                                </p>
                            </div>
                            <div class="col-lg-5 text-center">
                                <img src="assets/img/PAGIBIG.png" class="img-fluid rounded-0 shadow-sm mx-auto d-block" alt="Pag-IBIG Funded Solar Installation" style="width: 100%; max-width: 320px; aspect-ratio: 1 / 1; object-fit: cover;">
                            </div>
                        </div>

                        <!-- Table: How Much Can You Borrow for Solar? -->
                        <div class="bg-white p-4 rounded-4 shadow-sm mb-5 border border-light">
                            <h4 class="fw-bold mb-4" style="color: #0D5C3A; font-size: 1.3rem;"><i class="fas fa-calculator me-2 text-warning"></i>How Much Can You Borrow for Solar?</h4>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-3">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="fw-bold text-uppercase" style="font-size: 0.8rem; color: #64748B;">Loan Amount</th>
                                            <th class="fw-bold text-uppercase" style="font-size: 0.8rem; color: #64748B;">20-Year Monthly Payment</th>
                                            <th class="fw-bold text-uppercase" style="font-size: 0.8rem; color: #64748B;">Solar System Size</th>
                                            <th class="fw-bold text-uppercase" style="font-size: 0.8rem; color: #64748B;">Est. Monthly Savings</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold" style="color: #0D5C3A; font-size: 1.05rem;">₱300,000</td>
                                            <td class="text-muted">~₱2,600/month</td>
                                            <td>3–4 kW</td>
                                            <td class="text-success fw-bold">₱3,000–₱5,000</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold" style="color: #0D5C3A; font-size: 1.05rem;">₱400,000</td>
                                            <td class="text-muted">~₱3,500/month</td>
                                            <td>5–6 kW</td>
                                            <td class="text-success fw-bold">₱5,000–₱7,000</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold" style="color: #0D5C3A; font-size: 1.05rem;">₱500,000</td>
                                            <td class="text-muted">~₱4,350/month</td>
                                            <td>7–8 kW</td>
                                            <td class="text-success fw-bold">₱7,000–₱10,000</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p class="small text-muted mb-2"><em>* Based on 8.5% 20-year Pag-IBIG housing loan rate.</em></p>
                            <div class="alert alert-success d-flex align-items-center mb-0 mt-3 border-0" style="background-color: rgba(13, 92, 58, 0.05); color: #0D5C3A;">
                                <i class="fas fa-check-circle me-3 fs-4 text-success"></i>
                                <div>
                                    <strong>The math often works:</strong> Monthly loan payments are frequently <strong>lower than your current electric bill</strong>—meaning you could be cash-flow positive from Day 1.
                                </div>
                            </div>
                        </div>

                        <!-- Requirements & Tips Grid -->
                        <div class="row g-4 mb-5">
                            <!-- Column 1: Eligibility Requirements -->
                            <div class="col-md-6">
                                <div class="bg-white p-4 rounded-4 shadow-sm h-100 border border-light">
                                    <h4 class="fw-bold mb-4" style="color: #0D5C3A; font-size: 1.25rem;"><i class="fas fa-id-card me-2 text-warning"></i>Eligibility Requirements</h4>
                                    
                                    <div class="mb-3">
                                        <h6 class="fw-bold text-success mb-2" style="color: #0D5C3A;">Basic Pag-IBIG Requirements</h6>
                                        <ul class="gold-checkmark-list" style="font-size: 0.9rem;">
                                            <li>Active Pag-IBIG membership</li>
                                            <li>At least 24 months of contributions</li>
                                            <li>No defaulted Pag-IBIG loans (foreclosed, cancelled, or delinquent)</li>
                                            <li>Must meet age and income requirements</li>
                                            <li>Monthly payment cannot exceed 35% of gross monthly income</li>
                                        </ul>
                                    </div>

                                    <div>
                                        <h6 class="fw-bold text-success mb-2" style="color: #0D5C3A;">For Solar-Specific Financing</h6>
                                        <ul class="gold-checkmark-list" style="font-size: 0.9rem;">
                                            <li>Property must be owned or with long-term lease rights</li>
                                            <li>Clear property title (no legal disputes)</li>
                                            <li>Roof suitable for solar installation</li>
                                            <li>DOE-accredited installer quote and specifications</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Column 2: Maximizing Your Loan -->
                            <div class="col-md-6">
                                <div class="bg-white p-4 rounded-4 shadow-sm h-100 border border-light">
                                    <h4 class="fw-bold mb-4" style="color: #0D5C3A; font-size: 1.25rem;"><i class="fas fa-chart-line-up me-2 text-warning"></i>Maximizing Your Solar Loan</h4>
                                    
                                    <div class="mb-3">
                                        <h6 class="fw-bold text-success mb-2" style="color: #0D5C3A;">Sizing Tips</h6>
                                        <ul class="gold-checkmark-list" style="font-size: 0.9rem;">
                                            <li><strong>Don't oversize:</strong> Match system to your actual daytime consumption</li>
                                            <li><strong>Consider net metering:</strong> If you'll export excess, a slightly larger system makes sense</li>
                                            <li><strong>Factor in rate increases:</strong> Electricity rates rise ~5–7% annually</li>
                                        </ul>
                                    </div>

                                    <div>
                                        <h6 class="fw-bold text-success mb-2" style="color: #0D5C3A;">Combining with Other Programs</h6>
                                        <ul class="gold-checkmark-list" style="font-size: 0.9rem;">
                                            <li><strong>Net Metering:</strong> Sell excess solar back to the grid for credits</li>
                                            <li><strong>Energy audit first:</strong> Reduce consumption before sizing your system</li>
                                            <li><strong>VAT exemption:</strong> RA 9513 may exempt solar equipment from VAT</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pitfalls & Suitability -->
                        <div class="row g-4">
                            <!-- Column 1: Common Pitfalls to Avoid -->
                            <div class="col-md-6">
                                <div class="bg-white p-4 rounded-4 shadow-sm h-100 border border-light">
                                    <h4 class="fw-bold mb-4 text-danger" style="font-size: 1.25rem;"><i class="fas fa-exclamation-triangle me-2 text-danger"></i>Common Pitfalls to Avoid</h4>
                                    <ul class="list-unstyled">
                                        <li class="d-flex align-items-start mb-3">
                                            <span class="fs-5 me-2">⚠️</span>
                                            <div>
                                                <strong>Unscrupulous installers:</strong> Use only DOE-accredited companies.
                                            </div>
                                        </li>
                                        <li class="d-flex align-items-start mb-3">
                                            <span class="fs-5 me-2">⚠️</span>
                                            <div>
                                                <strong>Biting off more than you can chew:</strong> Monthly payment must stay under 35% of income.
                                            </div>
                                        </li>
                                        <li class="d-flex align-items-start mb-3">
                                            <span class="fs-5 me-2">⚠️</span>
                                            <div>
                                                <strong>Skipping net metering:</strong> Without it, excess solar generated during daytime is wasted.
                                            </div>
                                        </li>
                                        <li class="d-flex align-items-start mb-2">
                                            <span class="fs-5 me-2">⚠️</span>
                                            <div>
                                                <strong>Not reading the fine print:</strong> Make sure you understand prepayment penalties and late fees.
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Column 2: Is Pag-IBIG Solar Loan Right for You? -->
                            <div class="col-md-6">
                                <div class="bg-white p-4 rounded-4 shadow-sm h-100 border border-light">
                                    <h4 class="fw-bold mb-4" style="color: #0D5C3A; font-size: 1.25rem;"><i class="fas fa-question-circle me-2 text-warning"></i>Is Pag-IBIG Solar Loan Right for You?</h4>
                                    
                                    <div class="mb-3">
                                        <h6 class="fw-bold text-success mb-2" style="color: #0D5C3A;">Best for:</h6>
                                        <ul class="gold-checkmark-list" style="font-size: 0.9rem;">
                                            <li>Homeowners with existing Pag-IBIG housing loans</li>
                                            <li>Members with stable employment and income</li>
                                            <li>Those wanting long-term, fixed-rate financing</li>
                                            <li>Borrowers who prefer government-backed loans</li>
                                        </ul>
                                    </div>

                                    <div>
                                        <h6 class="fw-bold text-danger mb-2">Consider alternatives if:</h6>
                                        <ul class="list-unstyled" style="font-size: 0.9rem;">
                                            <li class="mb-2"><i class="fas fa-times-circle text-danger me-2"></i>You need solar immediately (housing loan processing takes weeks)</li>
                                            <li class="mb-2"><i class="fas fa-times-circle text-danger me-2"></i>You don't meet the 24-month contribution requirement</li>
                                            <li class="mb-2"><i class="fas fa-times-circle text-danger me-2"></i>You already have high debt obligations</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Source Link -->
                        <div class="text-end mt-4">
                            <span class="text-muted small">Source: <a href="https://solarinstallph.com/blog/pagibig-solar-loan" target="_blank" class="text-success fw-bold text-decoration-none">Pag-IBIG Solar Loan Financing Guide <i class="fas fa-external-link-alt ms-1" style="font-size: 0.75rem;"></i></a></span>
                        </div>

                    </div>
                </div>

                <!-- SSS Energy Sustainability Loan Program -->
                <div class="tab-pane fade" id="panel-sss" role="tabpanel" aria-labelledby="sss-tab-btn">
                    <div class="p-4 p-md-5" style="background: var(--solar-bg-gray); border-radius: 24px; border: 1px solid #E2E8F0;">
                        
                        <!-- Header & Intro -->
                        <div class="mb-5">
                            <h3 class="fw-extrabold text-success mb-3" style="color: #0D5C3A; font-family: var(--ff-poppins); font-size: 2rem;">Energy Sustainability Loan Program</h3>
                            <p class="lead text-muted" style="font-size: 1.05rem; line-height: 1.7;">
                                The Social Security System (SSS) is set to introduce its Energy Sustainability Loan Program, which will allow qualified members to finance residential solar panel systems. The program represents a proactive response to emerging economic pressures, helping Filipino households save on high electricity rates.
                            </p>
                        </div>

                        <!-- 2-Column Overview -->
                        <div class="row g-5 mb-5 align-items-center">
                            <div class="col-lg-7">
                                <!-- Primary Stats Header -->
                                <div class="row g-3 mb-4">
                                    <div class="col-sm-4">
                                        <div class="p-3 bg-white rounded-3 border-start border-4 border-success shadow-sm">
                                            <span class="d-block text-muted text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Repayment Term</span>
                                            <h3 class="fw-extrabold text-success mb-0" style="color: #0D5C3A; font-family: var(--ff-poppins); font-size: 1.4rem;">Up to 7 Yrs</h3>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="p-3 bg-white rounded-3 border-start border-4 border-warning shadow-sm">
                                            <span class="d-block text-muted text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Target Goal</span>
                                            <h3 class="fw-extrabold text-success mb-0" style="color: #0D5C3A; font-family: var(--ff-poppins); font-size: 1.4rem;">100k Homes</h3>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="p-3 bg-white rounded-3 border-start border-4 border-info shadow-sm">
                                            <span class="d-block text-muted text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Program Launch</span>
                                            <h3 class="fw-extrabold text-success mb-0" style="color: #0D5C3A; font-family: var(--ff-poppins); font-size: 1.4rem;">Sept 2026</h3>
                                        </div>
                                    </div>
                                </div>

                                <blockquote class="blockquote bg-white p-4 rounded-4 shadow-sm border-start border-4 border-success mb-0" style="font-size: 0.95rem; font-style: italic;">
                                    <p class="mb-2 text-muted">"The program showcases SSS's role in responding not only to traditional social security concerns, but also to emerging economic pressures affecting Filipino households."</p>
                                    <footer class="blockquote-footer mt-2 fw-bold" style="color: #0D5C3A;">Robert Joseph M. de Claro, SSS President & CEO</footer>
                                </blockquote>
                            </div>
                            <div class="col-lg-5 text-center">
                                <img src="assets/img/SSS.png" class="img-fluid rounded-0 shadow-sm mx-auto d-block" alt="SSS Project Technical Execution" style="width: 100%; max-width: 320px; aspect-ratio: 1 / 1; object-fit: cover;">
                            </div>
                        </div>

                        <!-- Details & Requirements -->
                        <div class="row g-4 mb-4">
                            <!-- Column 1: Eligibility & Savings Account -->
                            <div class="col-md-6">
                                <div class="bg-white p-4 rounded-4 shadow-sm h-100 border border-light">
                                    <h4 class="fw-bold mb-4" style="color: #0D5C3A; font-size: 1.25rem;"><i class="fas fa-user-check me-2 text-warning"></i>Loan Eligibility</h4>
                                    <p class="text-muted small">Qualified SSS members can avail of the financing program under the following guidelines:</p>
                                    <ul class="gold-checkmark-list" style="font-size: 0.9rem;">
                                        <li>Must have a mandatory provident fund account.</li>
                                        <li>Must be actively contributing to the regular SSS program.</li>
                                        <li>Open to members with a Monthly Salary Credit (MSC) above <strong>₱20,000</strong>.</li>
                                        <li>The mandatory provident fund is a compulsory retirement savings scheme that automatically enrolls members meeting the salary credit threshold.</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Column 2: Program Vision & Installation -->
                            <div class="col-md-6">
                                <div class="bg-white p-4 rounded-4 shadow-sm h-100 border border-light">
                                    <h4 class="fw-bold mb-4" style="color: #0D5C3A; font-size: 1.25rem;"><i class="fas fa-globe-asia me-2 text-warning"></i>Sustainability Impact</h4>
                                    <p class="text-muted small">Designed to drive green energy adoption across the country:</p>
                                    <ul class="gold-checkmark-list" style="font-size: 0.9rem;">
                                        <li>Aims to support and solarize at least <strong>100,000 Filipino homes</strong> by 2028.</li>
                                        <li>Helps transition households into renewable energy to achieve direct, long-term electricity savings.</li>
                                        <li>Additional framework details, processing procedures, and guidelines will be officially rolled out close to the launch in September.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Source Link -->
                        <div class="text-end mt-4">
                            <span class="text-muted small">Source: <a href="https://www.gmanetwork.com/lifestyle/home/133655/sss-to-launch-loan-program-for-home-solar-panel-installation-in-september/story" target="_blank" class="text-success fw-bold text-decoration-none">GMA Network: SSS Solar Panel Loan Launch <i class="fas fa-external-link-alt ms-1" style="font-size: 0.75rem;"></i></a></span>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── SECTION 4: REPLACEMENT SECTION (Interactive Requirement & Eligibility Wizard) ── -->
    <section class="py-5" id="checklist" style="background-color: var(--solar-white);">
        <div class="container">
            <div class="row align-items-center g-5">
                <!-- Left: Aerial Rooftop Image -->
                <div class="col-lg-5" data-aos="fade-right">
                    <img src="assets/img/mock_solar_roof_map.png" alt="Solar project blueprints on a working desk" class="desk-vector-img">
                </div>

                <!-- Right: Checklist Panel -->
                <div class="col-lg-7" data-aos="fade-left">
                    <div class="checklist-panel bg-white p-4" style="border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #E2E8F0;">
                        <h3 class="fw-bold mb-2 text-start" style="color: #0D5C3A;">Pre-App Readiness Portal</h3>
                        <p class="text-muted small mb-4">Upload or drag and drop your files to instantly validate and generate your tailored green financing proposal.</p>
                        
                        <!-- Progress Indicator Row -->
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="small fw-bold text-uppercase" style="color: #64748B;">Upload Progress</span>
                            <span class="small fw-bold text-success" id="progressText" style="color: #0D5C3A !important;">0 of 3 Requirements Completed</span>
                        </div>
                        <div class="progress mb-4" style="height: 8px; border-radius: 4px; background-color: #E2E8F0;">
                            <div class="progress-bar" id="progressBar" role="progressbar" style="width: 0%; background-color: #0D5C3A; transition: width 0.3s;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        
                        <!-- Document Upload Slots -->
                        <div class="d-flex flex-column gap-3">
                            <!-- Slot 1: Meralco Bill -->
                            <div class="doc-upload-row p-3 d-flex flex-column gap-2" id="docRow1" style="border: 2px dashed #CBD5E1; border-radius: 12px; transition: all 0.25s; cursor: pointer; position: relative;">
                                <input type="file" id="fileInput1" accept=".pdf,image/*" style="display: none;">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="doc-icon text-muted" id="docIcon1" style="font-size: 1.5rem; transition: color 0.2s;"><i class="fas fa-file-invoice-dollar"></i></div>
                                        <div>
                                            <h6 class="mb-0 fw-bold doc-title" style="color: #0D5C3A; font-size: 0.9rem;">Copy of Latest Meralco / Electric Bill</h6>
                                            <span class="small text-muted fileNameText" id="fileName1">Click or drag & drop file to attach (PDF/Image)</span>
                                        </div>
                                    </div>
                                    <span class="badge text-uppercase badge-status text-white" id="badgeStatus1" style="background-color: #64748B; font-size: 0.65rem; padding: 6px 10px; border-radius: 20px;">Pending Upload</span>
                                </div>
                                <!-- Loading Spinner -->
                                <div class="spinner-container w-100 mt-2 align-items-center gap-2" id="spinner1" style="display: none;">
                                    <div class="spinner-border spinner-border-sm text-success" role="status"></div>
                                    <span class="small text-muted font-monospace" style="font-size: 0.75rem;">Simulating Document Validation Engine...</span>
                                </div>
                            </div>

                            <!-- Slot 2: Transfer Certificate of Title (TCT) -->
                            <div class="doc-upload-row p-3 d-flex flex-column gap-2" id="docRow2" style="border: 2px dashed #CBD5E1; border-radius: 12px; transition: all 0.25s; cursor: pointer; position: relative;">
                                <input type="file" id="fileInput2" accept=".pdf,image/*" style="display: none;">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="doc-icon text-muted" id="docIcon2" style="font-size: 1.5rem; transition: color 0.2s;"><i class="fas fa-file-signature"></i></div>
                                        <div>
                                            <h6 class="mb-0 fw-bold doc-title" style="color: #0D5C3A; font-size: 0.9rem;">Certified Copy of Land Title (TCT)</h6>
                                            <span class="small text-muted fileNameText" id="fileName2">Click or drag & drop file to attach (PDF/Image)</span>
                                        </div>
                                    </div>
                                    <span class="badge text-uppercase badge-status text-white" id="badgeStatus2" style="background-color: #64748B; font-size: 0.65rem; padding: 6px 10px; border-radius: 20px;">Pending Upload</span>
                                </div>
                                <!-- Loading Spinner -->
                                <div class="spinner-container w-100 mt-2 align-items-center gap-2" id="spinner2" style="display: none;">
                                    <div class="spinner-border spinner-border-sm text-success" role="status"></div>
                                    <span class="small text-muted font-monospace" style="font-size: 0.75rem;">Simulating Document Validation Engine...</span>
                                </div>
                            </div>

                            <!-- Slot 3: Government Agency Contribution -->
                            <div class="doc-upload-row p-3 d-flex flex-column gap-2" id="docRow3" style="border: 2px dashed #CBD5E1; border-radius: 12px; transition: all 0.25s; cursor: pointer; position: relative;">
                                <input type="file" id="fileInput3" accept=".pdf,image/*" style="display: none;">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="doc-icon text-muted" id="docIcon3" style="font-size: 1.5rem; transition: color 0.2s;"><i class="fas fa-id-card"></i></div>
                                        <div>
                                            <h6 class="mb-0 fw-bold doc-title" style="color: #0D5C3A; font-size: 0.9rem;">Active GSIS/SSS/Pag-IBIG Membership Proof</h6>
                                            <span class="small text-muted fileNameText" id="fileName3">Click or drag & drop file to attach (PDF/Image)</span>
                                        </div>
                                    </div>
                                    <span class="badge text-uppercase badge-status text-white" id="badgeStatus3" style="background-color: #64748B; font-size: 0.65rem; padding: 6px 10px; border-radius: 20px;">Pending Upload</span>
                                </div>
                                <!-- Loading Spinner -->
                                <div class="spinner-container w-100 mt-2 align-items-center gap-2" id="spinner3" style="display: none;">
                                    <div class="spinner-border spinner-border-sm text-success" role="status"></div>
                                    <span class="small text-muted font-monospace" style="font-size: 0.75rem;">Simulating Document Validation Engine...</span>
                                </div>
                            </div>
                        </div>

                        <!-- Smart CTA button -->
                        <button class="btn w-100 mt-4 d-flex align-items-center justify-content-center gap-2 fw-bold text-uppercase border-0 shadow-sm" id="submitProposalBtn" onclick="openLeadModal()" disabled style="background-color: #E2E8F0; color: #94A3B8; border-radius: 50px; padding: 15px 30px; transition: all 0.3s; cursor: not-allowed;">
                            <i class="fas fa-lock" id="submitProposalIcon"></i> <span id="submitProposalText">Upload Requirements to Unlock Proposal</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Lead Generation modal triggered by checklist CTA -->
    <div class="modal fade" id="leadGenerationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content proposal-modal-content" id="proposalLeadForm" onsubmit="submitLeadForm(event)">
                <div class="modal-header proposal-modal-header d-flex justify-content-between align-items-center">
                    <h5 class="modal-title fw-bold text-white mb-0"><i class="fas fa-file-contract me-2"></i> Request Engineering Proposal</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body proposal-modal-body">
                    <p class="text-muted small mb-4">Please input your contact information to receive your tailored engineering draft design proposal required for loan filing.</p>
                    
                    <div class="mb-3 d-flex flex-column">
                        <label class="form-label small fw-bold text-uppercase mb-2" style="color: var(--solar-green);">Full Name</label>
                        <input type="text" name="name" class="form-control form-input-premium" placeholder="Juan Dela Cruz" required>
                    </div>
                    <div class="mb-3 d-flex flex-column">
                        <label class="form-label small fw-bold text-uppercase mb-2" style="color: var(--solar-green);">Phone Number</label>
                        <input type="text" name="phone" class="form-control form-input-premium" placeholder="e.g. 09171234567" required>
                    </div>
                    <div class="mb-4 d-flex flex-column">
                        <label class="form-label small fw-bold text-uppercase mb-2" style="color: var(--solar-green);">Email Address</label>
                        <input type="email" name="email" class="form-control form-input-premium" placeholder="juan@email.com" required>
                    </div>
                    
                    <button type="submit" class="btn-green-proposal mt-2">
                        <i class="fas fa-paper-plane"></i> Launch Request & Download Proposal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Rocket flight success overlay toast -->
    <div id="rocketAnimation" class="position-fixed bottom-0 start-0 m-3" style="z-index: 1050; display: none;">
        <div class="alert alert-success shadow d-flex align-items-center mb-0 border-0" style="background-color: #0D5C3A; color: #FFFFFF; border-radius: 12px; padding: 12px 20px;">
            <svg class="me-2" width="20" height="20" fill="#F2A900" viewBox="0 0 16 16">
                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
            </svg>
            <span class="fw-bold tracking-wide">Application Launched Successfully!</span>
        </div>
    </div>

    <?php include "includes/footer.php" ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });

        // ── SECTION 1: DYNAMIC CALCULATOR ENGINE ──
        const CALC_CONFIG = {
            kwhRate: <?= floatval($calc_settings['kwh_rate']) ?>,
            panelWattage: <?= intval($calc_settings['solar_panel_wattage']) ?>,
            sunHours: <?= floatval($calc_settings['average_sun_hours']) ?>
        };

        const billSlider = document.getElementById('billSlider');
        const billInput = document.getElementById('billInput');
        const termSlider = document.getElementById('termSlider');
        const termInput = document.getElementById('termInput');
        
        const calcAmort = document.getElementById('calcAmort');
        const calcRoi = document.getElementById('calcRoi');
        const metricSystemSize = document.getElementById('metricSystemSize');
        const metric10YrSavings = document.getElementById('metric10YrSavings');
        const metric25YrSavings = document.getElementById('metric25YrSavings');
        
        function formatPHP(amount) {
            return '₱' + Math.round(amount).toLocaleString('en-US');
        }

        function animateCount(element, targetValue) {
            const startValue = parseInt(element.textContent.replace(/[^0-9]/g, '')) || 0;
            const duration = 300; // ms
            const startTime = performance.now();

            function update(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const currentValue = Math.round(startValue + progress * (targetValue - startValue));
                element.textContent = formatPHP(currentValue);

                if (progress < 1) {
                    requestAnimationFrame(update);
                }
            }
            requestAnimationFrame(update);
        }

        function calculateSolarMetrics() {
            const bill = parseFloat(billInput.value) || 3000;
            const term = parseInt(termInput.value) || 15;
            
            // Logic formulas linked to backend settings
            const kwhUsed = bill / CALC_CONFIG.kwhRate;
            const systemKwp = (kwhUsed / (30 * CALC_CONFIG.sunHours)) * 0.95;
            const systemCost = systemKwp * 74000;
            
            const monthlyInterest = 0.052 / 12;
            const months = term * 12;
            
            const amortization = (systemCost * monthlyInterest * Math.pow(1 + monthlyInterest, months)) / (Math.pow(1 + monthlyInterest, months) - 1);
            const monthlySavings = systemKwp * CALC_CONFIG.sunHours * 30 * 0.95 * CALC_CONFIG.kwhRate;
            const roi = monthlySavings - amortization;

            // Update output widgets with animations
            animateCount(calcAmort, amortization);
            animateCount(calcRoi, roi);

            // Update auxiliary metrics instantly
            metricSystemSize.textContent = systemKwp.toFixed(1) + ' kWp';
            metric10YrSavings.textContent = formatPHP(monthlySavings * 12 * 10);
            metric25YrSavings.textContent = formatPHP(monthlySavings * 12 * 25);
        }

        billSlider.addEventListener('input', (e) => {
            billInput.value = e.target.value;
            calculateSolarMetrics();
        });
        billInput.addEventListener('input', (e) => {
            let val = parseFloat(e.target.value);
            if (isNaN(val)) val = 3000;
            if (val < 3000) val = 3000;
            if (val > 30000) val = 30000;
            billSlider.value = val;
            calculateSolarMetrics();
        });
        
        termSlider.addEventListener('input', (e) => {
            termInput.value = e.target.value;
            calculateSolarMetrics();
        });
        termInput.addEventListener('input', (e) => {
            let val = parseInt(e.target.value);
            if (isNaN(val)) val = 5;
            if (val < 5) val = 5;
            if (val > 30) val = 30;
            termSlider.value = val;
            calculateSolarMetrics();
        });

        calculateSolarMetrics(); // Init calculation on load





        // ── SECTION 4: PRE-APP CHECKLIST DOCUMENT UPLOAD & VALIDATION ENGINE ──
        const docRows = [
            { row: document.getElementById('docRow1'), input: document.getElementById('fileInput1'), name: document.getElementById('fileName1'), status: document.getElementById('badgeStatus1'), spinner: document.getElementById('spinner1'), loaded: false },
            { row: document.getElementById('docRow2'), input: document.getElementById('fileInput2'), name: document.getElementById('fileName2'), status: document.getElementById('badgeStatus2'), spinner: document.getElementById('spinner2'), loaded: false },
            { row: document.getElementById('docRow3'), input: document.getElementById('fileInput3'), name: document.getElementById('fileName3'), status: document.getElementById('badgeStatus3'), spinner: document.getElementById('spinner3'), loaded: false }
        ];
        
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const submitProposalBtn = document.getElementById('submitProposalBtn');
        const submitProposalIcon = document.getElementById('submitProposalIcon');
        const submitProposalText = document.getElementById('submitProposalText');

        docRows.forEach((item, index) => {
            // Trigger file click when row is clicked
            item.row.addEventListener('click', () => {
                item.input.click();
            });

            // Prevent default drag behaviors
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                item.row.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                }, false);
            });

            // Dragover highlight
            item.row.addEventListener('dragover', () => {
                item.row.style.borderColor = '#F2A900';
                item.row.style.backgroundColor = 'rgba(242, 169, 0, 0.02)';
            });

            item.row.addEventListener('dragleave', () => {
                item.row.style.borderColor = item.loaded ? '#0D5C3A' : '#CBD5E1';
                item.row.style.backgroundColor = item.loaded ? 'rgba(13, 92, 58, 0.04)' : 'transparent';
            });

            // Handle dropped files
            item.row.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                if (files.length) {
                    item.input.files = files;
                    handleFileSelection(item, files[0]);
                }
            });

            // Handle standard file inputs
            item.input.addEventListener('change', (e) => {
                if (e.target.files.length) {
                    handleFileSelection(item, e.target.files[0]);
                }
            });
        });

        function handleFileSelection(item, file) {
            // Show loading spinner to simulate validation
            item.spinner.style.display = 'flex';
            item.status.textContent = 'Validating...';
            item.status.style.backgroundColor = '#64748B';
            item.name.textContent = file.name;

            setTimeout(() => {
                item.spinner.style.display = 'none';
                
                // For simplicity, let's treat any PDF or Image format as valid
                const isValid = file.type === 'application/pdf' || file.type.startsWith('image/');
                if (isValid) {
                    item.status.textContent = 'File Attached';
                    item.status.style.backgroundColor = '#0D5C3A';
                    item.row.classList.add('has-file');
                    item.loaded = true;
                } else {
                    item.status.textContent = 'Invalid Format';
                    item.status.style.backgroundColor = '#DC3545';
                    item.row.classList.remove('has-file');
                    item.name.textContent = 'Attached file must be a PDF or Image.';
                    item.loaded = false;
                }
                updateUploadProgress();
            }, 1500);
        }

        function updateUploadProgress() {
            const uploadedCount = docRows.filter(item => item.loaded).length;
            const percentage = Math.round((uploadedCount / docRows.length) * 100);
            
            progressBar.style.width = percentage + '%';
            progressBar.setAttribute('aria-valuenow', percentage);
            progressText.textContent = `${uploadedCount} of 3 Requirements Completed`;
            
            if (uploadedCount === docRows.length) {
                // Unlock CTA button with transition
                submitProposalBtn.removeAttribute('disabled');
                submitProposalBtn.style.backgroundColor = '#F2A900';
                submitProposalBtn.style.color = '#000000';
                submitProposalBtn.style.cursor = 'pointer';
                submitProposalBtn.style.boxShadow = '0 0 15px rgba(242, 169, 0, 0.4)';
                
                submitProposalIcon.className = 'fas fa-arrow-right';
                submitProposalText.textContent = 'Generate & Download Official Proposal →';
            } else {
                submitProposalBtn.setAttribute('disabled', 'true');
                submitProposalBtn.style.backgroundColor = '#E2E8F0';
                submitProposalBtn.style.color = '#94A3B8';
                submitProposalBtn.style.cursor = 'not-allowed';
                submitProposalBtn.style.boxShadow = 'none';
                
                submitProposalIcon.className = 'fas fa-lock';
                submitProposalText.textContent = 'Upload Requirements to Unlock Proposal';
            }
        }

        function openLeadModal() {
            const modal = new bootstrap.Modal(document.getElementById('leadGenerationModal'));
            modal.show();
        }

        function submitLeadForm(e) {
            e.preventDefault();
            
            // Hide modal
            const modalEl = document.getElementById('leadGenerationModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            // Run rocket launch visual
            const rocketOverlay = document.getElementById('rocketAnimation');
            rocketOverlay.style.display = 'flex';

            setTimeout(() => {
                rocketOverlay.style.display = 'none';
                
                Swal.fire({
                    title: 'Proposal Sent!',
                    text: 'Ang iyong customized engineering proposal at blueprint ay naipadala na sa iyong email. Makikipag-ugnayan din ang aming team sa iyo sa loob ng 24 oras.',
                    icon: 'success',
                    confirmButtonColor: '#0D5C3A'
                }).then(() => {
                    // Reset upload portal and form
                    document.getElementById('proposalLeadForm').reset();
                    docRows.forEach(item => {
                        item.loaded = false;
                        item.input.value = '';
                        item.row.classList.remove('has-file');
                        item.status.textContent = 'Pending Upload';
                        item.status.style.backgroundColor = '#64748B';
                        item.name.textContent = 'Click or drag & drop file to attach (PDF/Image)';
                    });
                    updateUploadProgress();
                });
            }, 2500);
        }
    </script>
</body>

</html>
