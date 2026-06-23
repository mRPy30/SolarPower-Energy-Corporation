<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/icon.png">
    
    <!-- Primary Meta Tags -->
    <title>About Us | SolarPower Energy Corporation</title>
    <meta name="description" content="Learn more about SolarPower Energy Corporation, the Philippines' leading DOE-accredited solar developer. Discover our mission, vision, services, and why homeowners trust us." />
    <meta name="keywords" content="About SolarPower, solar developer Philippines, DOE accredited solar installer, renewable energy provider, solar panel installation Manila" />
    <meta name="author" content="SolarPower Energy Corporation" />
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="https://solarpower.com.ph/about.php" />

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:title" content="About Us | SolarPower Energy Corporation" />
    <meta property="og:description" content="Discover our journey, accredited team, and why SolarPower is the Philippines' most trusted partner in solar panel installation and renewable energy." />
    <meta property="og:image" content="https://solarpower.com.ph/assets/img/logo.png" />
    <meta property="og:url" content="https://solarpower.com.ph/about.php" />

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">

    <!-- Google Fonts for Modern Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">

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
        }

        body {
            font-family: var(--ff-inter);
            color: var(--solar-text-dark);
            background-color: var(--solar-bg-gray);
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: var(--ff-poppins);
            font-weight: 700;
            color: var(--solar-text-dark);
        }

        /* ── SECTION 1: SUB-HERO BANNER ── */
        .sub-hero-banner {
            height: 45vh;
            min-height: 350px;
            position: relative;
            background: linear-gradient(135deg, rgba(11, 46, 32, 0.85) 0%, rgba(20, 35, 55, 0.8) 100%),
                        url('assets/img/projects1.png') no-repeat center center/cover;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            z-index: 1;
        }

        .sub-hero-banner h1 {
            font-family: var(--ff-poppins);
            font-weight: 800;
            color: #FFFFFF;
            font-size: clamp(1.8rem, 4vw, 3rem);
            letter-spacing: -0.5px;
            text-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        /* ── TRUST HUB & LISTS ── */
        .trust-box {
            border: 2px solid rgba(13, 92, 58, 0.12);
            border-radius: 16px;
            background-color: rgba(13, 92, 58, 0.02);
            padding: 20px;
        }

        .checked-list {
            list-style: none;
            padding-left: 0;
            margin-top: 15px;
        }
        
        .checked-list li {
            position: relative;
            padding-left: 30px;
            margin-bottom: 12px;
            font-weight: 600;
            color: var(--solar-text-dark);
            font-size: 0.95rem;
        }
        
        .checked-list li::before {
            content: "\f00c";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            position: absolute;
            left: 0;
            top: 2px;
            color: var(--solar-green);
        }

        /* Sleek blockquote style */
        .sleek-blockquote {
            border-left: 4px solid var(--solar-green);
            padding-left: 20px;
            font-style: italic;
            color: var(--solar-text-muted);
        }

        /* ── THREE PILLARS GLASSMORPHISM CARDS ── */
        .capability-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-top: 4px solid var(--solar-gold) !important;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .capability-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(13, 92, 58, 0.08);
            background: rgba(255, 255, 255, 0.95);
        }

        /* ── PROFILE CARDS ── */
        .profile-card {
            background: var(--solar-white);
            border: 1px solid #E2E8F0;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
            height: 100%;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02);
        }

        .profile-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(13, 92, 58, 0.08);
            border-color: rgba(13, 92, 58, 0.15);
        }

        .profile-img-wrap {
            position: relative;
            overflow: hidden;
            height: 280px;
            background-color: #F1F5F9;
        }

        .profile-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .profile-card:hover .profile-img {
            transform: scale(1.05);
        }

        .profile-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            top: 0;
            background: rgba(13, 92, 58, 0.95);
            color: #FFFFFF;
            padding: 25px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            transform: translateY(100%);
            transition: transform 0.35s ease;
        }

        .profile-card:hover .profile-overlay {
            transform: translateY(0);
        }

        .profile-card-footer {
            background-color: var(--solar-green) !important;
            height: 8px;
            width: 100%;
        }
    </style>
</head>

<body>

    <?php include "includes/header.php" ?>

    <!-- ── SECTION 1: SUB-HERO BANNER ── -->
    <section class="sub-hero-banner" style="position: relative; overflow: hidden;">
        <div class="container text-center py-5" data-aos="fade-up" style="position: relative; z-index: 3;">
            <h1 class="display-4 text-uppercase fw-extrabold text-white mb-3" style="letter-spacing: 1px;">Powering a Brighter,
Greener Future</h1>
        </div>
    </section>


    <!-- ── SECTION 2: OVERVIEW & ACCREDITATION HUB ── -->
    <section class="py-5 bg-white">
        <div class="container py-lg-5">
            <div class="row g-5 align-items-center">
                <!-- Left Column: Logo Only -->
                <div class="col-lg-5 pe-lg-5 text-center" data-aos="fade-right">
                    <img src="assets/img/solarpower_energy_corp.png" alt="SolarPower Logo" style="max-height: 180px; width: auto; object-fit: contain;">
                </div>

                <!-- Right Column: Corporate Profile Text & DOE Status Hub -->
                <div class="col-lg-7" data-aos="fade-left">
                    <span class="text-uppercase fw-bold text-tracking" style="color: #F2A900; font-size: 0.85rem; letter-spacing: 2px;">Who We Are</span>
                    <h2 class="display-6 fw-extrabold mt-2 mb-4" style="color: #0D5C3A; font-family: var(--ff-poppins);">SolarPower Energy Corporation</h2>
                    <p class="text-muted mb-4" style="font-size: 1.05rem; line-height: 1.7;">
                        A premier renewable energy solutions provider committed to making clean, affordable, and sustainable solar power accessible to homes, businesses, and communities across the Philippines. Established in 2025, our enterprise specializes in the seamless design, installation, and engineering maintenance of high-capacity solar photovoltaic (PV) systems tailored to meet diverse local utility needs.
                    </p>
                    <blockquote class="sleek-blockquote mb-4" style="font-size: 1.05rem; line-height: 1.7;">
                        As a dynamic leader in the Philippine solar landscape, we bring cutting-edge technology, transparent government financing integrations, and a deep-seated commitment to technical excellence, effectively reducing regional utility burdens and national carbon footprints.
                    </blockquote>

                </div>
            </div>
        </div>
    </section>

    <!-- ── SECTION: ACCREDITED & CERTIFIED (DOE Seal Section) ── -->
    <section class="py-5 bg-white border-top">
        <div class="container py-lg-4">
            <div class="row align-items-center g-5">
                <!-- Left Column: Text & Verification -->
                <div class="col-lg-6" data-aos="fade-right">
                    <h2 class="fw-extrabold mb-3" style="color: #0D5C3A; font-family: var(--ff-poppins); font-size: 2.2rem;">Accredited & Certified</h2>
                    <p class="lead text-muted mb-4" style="font-size: 1.1rem; line-height: 1.6;">
                        We are proud to be a Department of Energy (DOE) recognized solar service provider.
                    </p>
                    
                    <ul class="checked-list">
                        <li>Registered Renewable Energy Developer</li>
                        <li>Compliance with Philippine Grid Code standards</li>
                    </ul>
                </div>

                <!-- Right Column: Massive Seal Badge -->
                <div class="col-lg-6 text-center" data-aos="fade-left">
                    <div class="bg-white p-4 shadow-sm border border-slate-100 d-inline-block text-center" style="border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); max-width: 480px; width: 100%;">
                        <img src="assets/img/DOE.png" alt="DOE Seal Logo" class="img-fluid mb-3" style="max-height: 280px; object-fit: contain;">
                        <p class="small text-muted mb-0 fw-bold mt-2 text-uppercase" style="letter-spacing: 0.5px;">Authorized Solar PV Provider (Registration #250900095)</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── SECTION 3: THE THREE PILLARS OF EXCELLENCE ── -->
    <section class="py-5 bg-light">
        <div class="container py-lg-5">
            <div class="text-center mb-5" data-aos="fade-up">
                <span class="text-uppercase fw-bold" style="color: #0D5C3A; font-size: 0.85rem; letter-spacing: 1.5px;">Business Capabilities</span>
                <h2 class="fw-extrabold mt-2" style="color: #0D5C3A; font-family: var(--ff-poppins); font-size: 2.3rem;">The Three Pillars of Excellence</h2>
                <p class="text-muted">Empowering sustainable infrastructure with targeted operations at every scale.</p>
            </div>

            <div class="row g-4" data-aos="fade-up" data-aos-delay="100">
                <!-- Card 1 -->
                <div class="col-md-4">
                    <div class="card capability-card p-4 border-0">
                        <div class="mb-4 text-success d-flex align-items-center justify-content-center rounded-circle" style="width: 55px; height: 55px; background-color: rgba(13, 92, 58, 0.05); color: #0D5C3A !important; font-size: 1.5rem;">
                            <i class="fas fa-tags"></i>
                        </div>
                        <h5 class="fw-bold mb-3" style="color: #0D5C3A;">SUPPLY & DISTRIBUTION SELLER</h5>
                        <p class="text-muted mb-0 small" style="line-height: 1.6;">Sourcing and delivering high-efficiency, commercial-grade solar components and monocrystalline modules at highly competitive local rates.</p>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="col-md-4">
                    <div class="card capability-card p-4 border-0">
                        <div class="mb-4 text-success d-flex align-items-center justify-content-center rounded-circle" style="width: 55px; height: 55px; background-color: rgba(13, 92, 58, 0.05); color: #0D5C3A !important; font-size: 1.5rem;">
                            <i class="fas fa-warehouse"></i>
                        </div>
                        <h5 class="fw-bold mb-3" style="color: #0D5C3A;">TIER-1 TECHNOLOGY DISTRIBUTOR</h5>
                        <p class="text-muted mb-0 small" style="line-height: 1.6;">Maintaining an uncompromised, resilient supply chain that ensures rapid delivery of premium global alternative energy technologies.</p>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="col-md-4">
                    <div class="card capability-card p-4 border-0">
                        <div class="mb-4 text-success d-flex align-items-center justify-content-center rounded-circle" style="width: 55px; height: 55px; background-color: rgba(13, 92, 58, 0.05); color: #0D5C3A !important; font-size: 1.5rem;">
                            <i class="fas fa-tools"></i>
                        </div>
                        <h5 class="fw-bold mb-3" style="color: #0D5C3A;">MASTER ENGINEERING INSTALLER</h5>
                        <p class="text-muted mb-0 small" style="line-height: 1.6;">Deploying expert local engineering crews and certified technicians for precision-targeted rooftop mounting and utility-compliant grid tie-ins.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── SECTION 4: MISSION & VISION BANDS ── -->
    <section class="py-5 bg-white">
        <div class="container py-lg-4">
            <div class="row g-4">
                <!-- Mission Panel -->
                <div class="col-md-6" data-aos="fade-right">
                    <div class="p-5 rounded-4 text-center h-100" style="background-color: var(--solar-bg-gray); border: 1px solid rgba(0,0,0,0.03);">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 50px; height: 50px; background-color: rgba(13, 92, 58, 0.08); color: #0D5C3A; font-size: 1.5rem;">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <h4 class="fw-extrabold mb-3" style="color: #0D5C3A;">Our Mission</h4>
                        <p class="text-muted mb-0 lead" style="font-size: 1.05rem; line-height: 1.6;">To promote sustainable living by providing reliable and cost-efficient solar energy solutions.</p>
                    </div>
                </div>

                <!-- Vision Panel -->
                <div class="col-md-6" data-aos="fade-left">
                    <div class="p-5 rounded-4 text-center h-100" style="background-color: var(--solar-bg-gray); border: 1px solid rgba(0,0,0,0.03);">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 50px; height: 50px; background-color: rgba(242, 169, 0, 0.08); color: #F2A900; font-size: 1.5rem;">
                            <i class="fas fa-globe"></i>
                        </div>
                        <h4 class="fw-extrabold mb-3" style="color: #0D5C3A;">Our Vision</h4>
                        <p class="text-muted mb-0 lead" style="font-size: 1.05rem; line-height: 1.6;">To become a leading solar energy provider nationwide, empowering every Filipino to save energy.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── SECTION 5: OUR SERVICES ── -->
    <section class="py-5 bg-light">
        <div class="container py-lg-5">
            <div class="text-center mb-5" data-aos="fade-up">
                <span class="text-uppercase fw-bold" style="color: #0D5C3A; font-size: 0.85rem; letter-spacing: 1.5px;">Core Solutions</span>
                <h2 class="fw-extrabold mt-2 text-uppercase" style="color: #0D5C3A; font-family: var(--ff-poppins); font-size: 2.3rem;">Our Services</h2>
                <p class="text-muted">High-performing technical services engineered to secure energy savings and asset longevity.</p>
            </div>

            <div class="row g-4 justify-content-center" data-aos="fade-up" data-aos-delay="100">
                <!-- Service 1 -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 p-4 border-0 shadow-sm text-center" style="border-radius: 16px; background-color: #FFFFFF;">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 mx-auto" style="width: 60px; height: 60px; background-color: rgba(13, 92, 58, 0.08); color: #0D5C3A; font-size: 1.5rem;">
                            <i class="fas fa-wrench"></i>
                        </div>
                        <h5 class="fw-bold mb-3" style="color: #0D5C3A;">Solar Panel Maintenance & Upgrades</h5>
                        <p class="text-muted small mb-0" style="line-height: 1.6;">Maximize power output with periodic cleaning audits, inverter testing, module health checks, and capacity expansions.</p>
                    </div>
                </div>

                <!-- Service 2 -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 p-4 border-0 shadow-sm text-center" style="border-radius: 16px; background-color: #FFFFFF;">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 mx-auto" style="width: 60px; height: 60px; background-color: rgba(13, 92, 58, 0.08); color: #0D5C3A; font-size: 1.5rem;">
                            <i class="fas fa-drafting-compass"></i>
                        </div>
                        <h5 class="fw-bold mb-3" style="color: #0D5C3A;">Energy Audit & System Design</h5>
                        <p class="text-muted small mb-0" style="line-height: 1.6;">Detailed audits of your consumption profile matched with precision engineering design to size a system that yields maximum savings.</p>
                    </div>
                </div>

                <!-- Service 3 -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 p-4 border-0 shadow-sm text-center" style="border-radius: 16px; background-color: #FFFFFF;">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 mx-auto" style="width: 60px; height: 60px; background-color: rgba(13, 92, 58, 0.08); color: #0D5C3A; font-size: 1.5rem;">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <h5 class="fw-bold mb-3" style="color: #0D5C3A;">Net-Metering</h5>
                        <p class="text-muted small mb-0" style="line-height: 1.6;">Full legal integration and utility setups allowing you to sell excess solar energy back to the grid, offsetting Meralco bills.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── SECTION 6: THE BRAND STORY (The Split Layout) ── -->
    <section class="py-5 bg-light">
        <div class="container py-lg-5">
            <div class="row align-items-center g-5">
                <!-- Left Column: Copy Story -->
                <div class="col-lg-6" data-aos="fade-right">
                    <span class="text-uppercase fw-bold text-success" style="font-size: 0.85rem; letter-spacing: 1.5px; color: var(--solar-green) !important;">About SolarPower</span>
                    <h2 class="display-6 fw-extrabold mt-2 mb-4" style="color: var(--solar-green); font-family: var(--ff-poppins);">Bridging the Energy Gap in the Philippines</h2>
                    <p class="lead text-muted mb-4" style="font-size: 1.05rem; line-height: 1.7;">
                        SolarPower Energy Corporation was founded with a single driving mission: to address the pressing issues of <span class="value-badge-text" style="color: #0D5C3A; font-weight: 800;">high electricity costs</span> and <span class="value-badge-text" style="color: #0D5C3A; font-weight: 800;">unreliable grid services</span> across the archipelago. 
                    </p>
                    <p class="text-muted mb-4" style="line-height: 1.7;">
                        We believe clean, renewable energy should not be a luxury reserved only for those who can afford massive upfront cash outlays. By integrating with key local banking entities and state-backed housing loan initiatives, we configure custom systems that immediately align with homeowners' savings profiles.
                    </p>
                    <p class="text-muted mb-0" style="line-height: 1.7;">
                        From engineering assessments to post-installation net metering setups, our licensed <span style="color: #0D5C3A; font-weight: 700;">project managers</span>, <span style="color: #0D5C3A; font-weight: 700;">certified electrical engineers</span>, and <span style="color: #0D5C3A; font-weight: 700;">safety officers</span> manage each project step with transparency and technical mastery.
                    </p>
                </div>

                <!-- Right Column: Collaboration Image -->
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="position-relative">
                        <img src="assets/img/about.png" alt="SolarPower Team Collaboration" class="img-fluid w-100 shadow" style="border-radius: 24px; max-height: 440px; object-fit: cover;">
                        <div class="position-absolute bottom-0 start-0 m-4 p-4 bg-white shadow-lg d-none d-md-block" style="border-radius: 16px; border-left: 5px solid var(--solar-gold); max-width: 280px;">
                            <h6 class="fw-bold mb-1" style="color: var(--solar-green);">Engineering Mastery</h6>
                            <p class="small text-muted mb-0">100% compliant with the Philippine Electrical and Grid Standards.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── SECTION 6: WHY SOLARPOWER? ── -->
    <section class="py-5 bg-white">
        <div class="container py-lg-5">
            <div class="text-center mb-5" data-aos="fade-up">
                <span class="text-uppercase fw-bold" style="color: #0D5C3A; font-size: 0.85rem; letter-spacing: 1.5px;">Value Proposition</span>
                <h2 class="fw-extrabold mt-2 text-uppercase" style="color: #0D5C3A; font-family: var(--ff-poppins); font-size: 2.3rem;">Why SolarPower?</h2>
                <p class="text-muted">Uncompromising quality, legal compliance, and customer-first design that sets us apart.</p>
            </div>

            <div class="row g-4" data-aos="fade-up" data-aos-delay="100">
                <!-- Point 1 -->
                <div class="col-md-6 col-lg-4">
                    <div class="p-4 rounded-4 border h-100 transition-all bg-white" style="border-color: rgba(13, 92, 58, 0.1) !important; box-shadow: 0 4px 12px rgba(0,0,0,0.01);">
                        <div class="d-flex align-items-center mb-3">
                            <div class="d-flex align-items-center justify-content-center rounded-circle me-3" style="width: 48px; height: 48px; background-color: rgba(13, 92, 58, 0.08); color: #0D5C3A; font-size: 1.25rem;">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <h5 class="fw-bold mb-0" style="color: #0D5C3A; font-size: 1.1rem;">Professional & Certified Installers</h5>
                        </div>
                        <p class="text-muted mb-0 small" style="line-height: 1.6;">Our installation crews consist of certified, licensed electrical engineers and safety officers ensuring maximum design accuracy and site safety.</p>
                    </div>
                </div>

                <!-- Point 2 -->
                <div class="col-md-6 col-lg-4">
                    <div class="p-4 rounded-4 border h-100 transition-all bg-white" style="border-color: rgba(13, 92, 58, 0.1) !important; box-shadow: 0 4px 12px rgba(0,0,0,0.01);">
                        <div class="d-flex align-items-center mb-3">
                            <div class="d-flex align-items-center justify-content-center rounded-circle me-3" style="width: 48px; height: 48px; background-color: rgba(13, 92, 58, 0.08); color: #0D5C3A; font-size: 1.25rem;">
                                <i class="fas fa-sliders-h"></i>
                            </div>
                            <h5 class="fw-bold mb-0" style="color: #0D5C3A; font-size: 1.1rem;">Customized Energy Solutions</h5>
                        </div>
                        <p class="text-muted mb-0 small" style="line-height: 1.6;">Every roof is different. We tailor array scales, panel inclinations, and system configuration directly to your exact monthly power patterns.</p>
                    </div>
                </div>

                <!-- Point 3 -->
                <div class="col-md-6 col-lg-4">
                    <div class="p-4 rounded-4 border h-100 transition-all bg-white" style="border-color: rgba(13, 92, 58, 0.1) !important; box-shadow: 0 4px 12px rgba(0,0,0,0.01);">
                        <div class="d-flex align-items-center mb-3">
                            <div class="d-flex align-items-center justify-content-center rounded-circle me-3" style="width: 48px; height: 48px; background-color: rgba(13, 92, 58, 0.08); color: #0D5C3A; font-size: 1.25rem;">
                                <i class="fas fa-gem"></i>
                            </div>
                            <h5 class="fw-bold mb-0" style="color: #0D5C3A; font-size: 1.1rem;">High-Quality Solar Components</h5>
                        </div>
                        <p class="text-muted mb-0 small" style="line-height: 1.6;">We deploy only premium, globally recognized Tier-1 monocrystalline panels, heavy-duty mounting rails, and certified smart-grid inverters.</p>
                    </div>
                </div>

                <!-- Point 4 -->
                <div class="col-md-6 col-lg-4">
                    <div class="p-4 rounded-4 border h-100 transition-all bg-white" style="border-color: rgba(13, 92, 58, 0.1) !important; box-shadow: 0 4px 12px rgba(0,0,0,0.01);">
                        <div class="d-flex align-items-center mb-3">
                            <div class="d-flex align-items-center justify-content-center rounded-circle me-3" style="width: 48px; height: 48px; background-color: rgba(13, 92, 58, 0.08); color: #0D5C3A; font-size: 1.25rem;">
                                <i class="fas fa-hand-holding-usd"></i>
                            </div>
                            <h5 class="fw-bold mb-0" style="color: #0D5C3A; font-size: 1.1rem;">Competitive Pricing & Financing Options</h5>
                        </div>
                        <p class="text-muted mb-0 small" style="line-height: 1.6;">Maximize your budget with our straight-to-state integrations offering direct funding support through GSIS, Pag-IBIG, and SSS loan channels.</p>
                    </div>
                </div>

                <!-- Point 5 -->
                <div class="col-md-6 col-lg-4">
                    <div class="p-4 rounded-4 border h-100 transition-all bg-white" style="border-color: rgba(13, 92, 58, 0.1) !important; box-shadow: 0 4px 12px rgba(0,0,0,0.01);">
                        <div class="d-flex align-items-center mb-3">
                            <div class="d-flex align-items-center justify-content-center rounded-circle me-3" style="width: 48px; height: 48px; background-color: rgba(13, 92, 58, 0.08); color: #0D5C3A; font-size: 1.25rem;">
                                <i class="fas fa-headset"></i>
                            </div>
                            <h5 class="fw-bold mb-0" style="color: #0D5C3A; font-size: 1.1rem;">Committed After-Sales Support</h5>
                        </div>
                        <p class="text-muted mb-0 small" style="line-height: 1.6;">Our client assistance continues long after setup with dedicated maintenance, array system monitoring, and prompt warranty tracking.</p>
                    </div>
                </div>

                <!-- Point 6 -->
                <div class="col-md-6 col-lg-4">
                    <div class="p-4 rounded-4 border h-100 transition-all bg-white" style="border-color: rgba(13, 92, 58, 0.1) !important; box-shadow: 0 4px 12px rgba(0,0,0,0.01);">
                        <div class="d-flex align-items-center mb-3">
                            <div class="d-flex align-items-center justify-content-center rounded-circle me-3" style="width: 48px; height: 48px; background-color: rgba(13, 92, 58, 0.08); color: #0D5C3A; font-size: 1.25rem;">
                                <i class="fas fa-certificate"></i>
                            </div>
                            <h5 class="fw-bold mb-0" style="color: #0D5C3A; font-size: 1.1rem;">DOE-Accredited Solar Installer</h5>
                        </div>
                        <p class="text-muted mb-0 small" style="line-height: 1.6;">Full legal authorization, compliance certificates, and formal technical accreditation with the Philippine Department of Energy.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include "includes/footer.php" ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
    </script>
    <script src="assets/script.js"></script>
</body>

</html>