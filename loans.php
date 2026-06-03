<?php
// Get current page filename for isActive helper in header
$current_page = 'loans.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/icon.png">
    <title>Solar Financing & Loans | SolarPower Energy Corporation</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">

    <style>
        :root {
            --clr-primary: #ffc107;
            --clr-secondary: #0a5c3d;
            --clr-dark: #1e293b;
            --clr-dark-alt: #0f172a;
            --clr-light: #ffffff;
            --clr-light-alt: #f8fafc;
            --clr-accent: #2d5016;
            --clr-text-muted: #64748b;
            --ff-body: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-md: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.05);
            --shadow-lg: 0 20px 25px -5px rgba(10, 92, 61, 0.08), 0 10px 10px -5px rgba(10, 92, 61, 0.04);
            --border-radius-lg: 16px;
        }

        body {
            font-family: var(--ff-body);
            color: #334155;
            background-color: var(--clr-light-alt);
        }

        /* Hero styling */
        .hero-loans {
            background: linear-gradient(135deg, rgba(10, 92, 61, 0.95) 0%, rgba(45, 80, 22, 0.9) 100%),
                        url('assets/img/aboutus.png') no-repeat center center/cover;
            padding: 100px 0;
            color: var(--clr-light);
            text-align: center;
        }

        .hero-badge {
            background-color: rgba(255, 193, 7, 0.2);
            color: var(--clr-primary);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-size: 0.85rem;
            padding: 8px 20px;
            border-radius: 50px;
            display: inline-block;
            margin-bottom: 20px;
        }

        /* Intro Section */
        .intro-section {
            padding: 80px 0 50px;
        }

        .section-title {
            position: relative;
            padding-bottom: 15px;
            font-weight: 700;
            color: var(--clr-dark-alt);
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background-color: var(--clr-secondary);
            border-radius: 2px;
        }

        .section-title-center::after {
            left: 50%;
            transform: translateX(-50%);
        }

        /* Main financing models cards */
        .financing-card {
            background: var(--clr-light);
            border: 1px solid #e2e8f0;
            border-radius: var(--border-radius-lg);
            padding: 40px 30px;
            height: 100%;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
        }

        .financing-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: rgba(10, 92, 61, 0.15);
        }

        .financing-icon-box {
            width: 64px;
            height: 64px;
            border-radius: 12px;
            background-color: rgba(10, 92, 61, 0.08);
            color: var(--clr-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 25px;
            transition: all 0.3s ease;
        }

        .financing-card:hover .financing-icon-box {
            background-color: var(--clr-secondary);
            color: var(--clr-light);
        }

        .financing-card h4 {
            font-weight: 700;
            color: var(--clr-dark-alt);
            margin-bottom: 15px;
            font-size: 1.3rem;
        }

        .financing-card p {
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.6;
            margin: 0;
        }

        /* Govt programs tab system */
        .govt-section {
            background-color: #f1f5f9;
            padding: 80px 0;
        }

        .loans-nav-pills {
            background: #ffffff;
            padding: 8px;
            border-radius: 50px;
            display: inline-flex;
            box-shadow: var(--shadow-sm);
            margin-bottom: 45px;
        }

        .loans-nav-pills .nav-link {
            border-radius: 50px;
            color: #64748b;
            font-weight: 600;
            padding: 12px 32px;
            border: none;
            transition: all 0.2s ease;
        }

        .loans-nav-pills .nav-link.active {
            background-color: var(--clr-secondary);
            color: #ffffff;
        }

        .loans-nav-pills .nav-link.active.pagibig-tab-btn {
            background-color: var(--clr-primary);
            color: var(--clr-dark-alt);
        }

        .program-card {
            background: var(--clr-light);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            border: none;
        }

        .program-badge {
            background-color: rgba(10, 92, 61, 0.1);
            color: var(--clr-secondary);
            font-weight: 700;
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.85rem;
            display: inline-block;
            margin-bottom: 15px;
        }

        .pagibig-badge {
            background-color: rgba(255, 193, 7, 0.15);
            color: #b28900;
        }

        .program-list {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }

        .program-list li {
            position: relative;
            padding-left: 28px;
            margin-bottom: 12px;
            font-size: 0.95rem;
            color: #475569;
            line-height: 1.5;
        }

        .program-list li::before {
            content: "\f00c";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            position: absolute;
            left: 0;
            top: 3px;
            color: var(--clr-secondary);
            font-size: 0.9rem;
        }

        .pagibig-list li::before {
            color: #b28900;
        }

        .btn-portal {
            padding: 12px 30px;
            font-weight: 700;
            border-radius: 8px;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            transition: all 0.2s ease;
        }

        .btn-portal-primary {
            background-color: var(--clr-secondary);
            color: white;
            border: none;
        }

        .btn-portal-primary:hover {
            background-color: #084930;
            color: white;
            transform: translateY(-2px);
        }

        .btn-portal-warning {
            background-color: var(--clr-primary);
            color: var(--clr-dark-alt);
            border: none;
        }

        .btn-portal-warning:hover {
            background-color: #e0a800;
            color: var(--clr-dark-alt);
            transform: translateY(-2px);
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, var(--clr-secondary) 0%, var(--clr-accent) 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }

        .btn-cta {
            background-color: var(--clr-primary);
            color: var(--clr-dark-alt);
            padding: 15px 40px;
            font-size: 1.05rem;
            font-weight: 700;
            border-radius: 8px;
            border: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }

        .btn-cta:hover {
            background-color: #e0a800;
            color: var(--clr-dark-alt);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.25);
        }
    </style>
</head>

<body>

    <?php include "includes/header.php" ?>

    <!-- Hero Section -->
    <section class="hero-loans">
        <div class="container" data-aos="fade-up">
            <span class="hero-badge">Smart Solar Financing</span>
            <h1 class="display-4 fw-bold text-white mb-3">Solar Financing in the Philippines</h1>
            <p class="lead text-white-50 max-width-700 mx-auto">
                Electricity is one of the biggest financial burdens for Filipino households. Transition to solar power seamlessly through our flexible financing programs.
            </p>
        </div>
    </section>

    <!-- Intro & General Financing Options -->
    <section class="intro-section">
        <div class="container">
            <div class="row align-items-center mb-5">
                <div class="col-lg-6" data-aos="fade-right">
                    <h2 class="section-title mb-4">Making Solar Energy Accessible</h2>
                    <p class="lead">Transitioning to solar power shouldn't require exhausting your life savings. We provide clean, affordable, and flexible financing models structured for every budget.</p>
                    <p>Whether you're a homeowner looking to slash your monthly electric bill or a large enterprise striving for zero CAPEX utility operations, we have the ideal payment terms designed for you.</p>
                </div>
                <div class="col-lg-6 text-center" data-aos="fade-left">
                    <img src="assets/img/new_logo.png" alt="SolarPower Logo" class="img-fluid py-4" style="max-width: 320px;">
                </div>
            </div>

            <!-- 4 Standard Financing Models -->
            <div class="row g-4 mt-2">
                <!-- Card 1: In-House Financing -->
                <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="100">
                    <div class="financing-card">
                        <div class="financing-icon-box">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h4>1. In-House Financing</h4>
                        <p>We offer <strong>0% interest in-house financing</strong> with a standard upfront downpayment. Best for clients looking for fast installation with zero interest charges.</p>
                    </div>
                </div>

                <!-- Card 2: Bank Financing -->
                <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="200">
                    <div class="financing-card">
                        <div class="financing-icon-box">
                            <i class="fas fa-university"></i>
                        </div>
                        <h4>2. Bank Financing</h4>
                        <p>Partnering with major local banks like <strong>Sterling Bank of Asia</strong>, we provide green financing packages with interest rates as low as <strong>1.25%</strong> for projects under ₱1M.</p>
                    </div>
                </div>

                <!-- Card 3: Solar PPA -->
                <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="300">
                    <div class="financing-card">
                        <div class="financing-icon-box">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <h4>3. Solar PPA</h4>
                        <p>A <strong>zero CAPEX</strong> solution designed for commercial & industrial clients. Only pay for the power generated by the solar panels at rates lower than grid utilities.</p>
                    </div>
                </div>

                <!-- Card 4: Solar Lease -->
                <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="400">
                    <div class="financing-card">
                        <div class="financing-icon-box">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h4>4. Solar Lease</h4>
                        <p>Pay a structured, predictable fixed monthly leasing fee that is guaranteed to be <strong>lower than your current electric bills</strong>, giving instant savings.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Government Loan Programs Section -->
    <section class="govt-section">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="section-title section-title-center mb-3">Government Solar Loan Programs</h2>
                <p class="text-muted max-width-600 mx-auto">Explore official GSIS and Pag-IBIG options to finance your residential green home improvements.</p>
            </div>

            <!-- Tab Switcher -->
            <div class="text-center" data-aos="fade-up">
                <ul class="nav nav-pills loans-nav-pills" id="loanTabs" role="tablist">
                    <li class="nav-link-wrapper" role="presentation">
                        <button class="nav-link active" id="all-loans-tab" data-bs-toggle="pill" data-bs-target="#tab-all" type="button" role="tab" aria-controls="tab-all" aria-selected="true">All Government Programs</button>
                    </li>
                    <li class="nav-link-wrapper" role="presentation">
                        <button class="nav-link" id="gsis-loan-tab" data-bs-toggle="pill" data-bs-target="#tab-gsis" type="button" role="tab" aria-controls="tab-gsis" aria-selected="false">GSIS Ginhawa Solar</button>
                    </li>
                    <li class="nav-link-wrapper" role="presentation">
                        <button class="nav-link pagibig-tab-btn" id="pagibig-loan-tab" data-bs-toggle="pill" data-bs-target="#tab-pagibig" type="button" role="tab" aria-controls="tab-pagibig" aria-selected="false">Pag-IBIG Financing</button>
                    </li>
                </ul>
            </div>

            <!-- Tab Contents -->
            <div class="tab-content mt-4" id="loanTabsContent" data-aos="fade-up" data-aos-delay="100">
                
                <!-- Tab: All Government Programs -->
                <div class="tab-pane fade show active" id="tab-all" role="tabpanel" aria-labelledby="all-loans-tab">
                    <div class="row g-4 justify-content-center">
                        
                        <!-- GSIS Card -->
                        <div class="col-lg-6">
                            <div class="program-card h-100 p-5 d-flex flex-column" style="border-top: 6px solid var(--clr-secondary);">
                                <span class="program-badge">GSIS Ginhawa Solar Energy Loan</span>
                                <h3 class="fw-bold mb-3">GSIS Ginhawa Program</h3>
                                <p class="text-muted">A dedicated program launched by the Government Service Insurance System (GSIS) to assist government personnel in acquiring residential solar energy packages.</p>
                                
                                <ul class="program-list my-4 flex-grow-1">
                                    <li><strong>Purpose:</strong> Full financial coverage for panel purchasing and installation.</li>
                                    <li><strong>Active GSIS Members:</strong> Must have updated premiums and contributions.</li>
                                    <li><strong>Eligibility:</strong> Net take-home pay must meet standard GAA thresholds.</li>
                                    <li><strong>Background check:</strong> Must have no pending administrative or criminal cases.</li>
                                </ul>

                                <a href="https://www.gsis.gov.ph/ginhawa-solar-energy-loan/" target="_blank" class="btn btn-portal btn-portal-primary w-100 mt-3">
                                    <i class="fas fa-external-link-alt me-2"></i>Apply via Official Portal
                                </a>
                            </div>
                        </div>

                        <!-- Pag-IBIG Card -->
                        <div class="col-lg-6">
                            <div class="program-card h-100 p-5 d-flex flex-column" style="border-top: 6px solid var(--clr-primary);">
                                <span class="program-badge pagibig-badge">Up to ₱500,000 Loan Cap</span>
                                <h3 class="fw-bold mb-3">Pag-IBIG Solar Financing</h3>
                                <p class="text-muted">Provided under Pag-IBIG Fund's Home Improvement Loan program, offering affordable solar installations to upgrade home energy conservation standards.</p>
                                
                                <ul class="program-list pagibig-list my-4 flex-grow-1">
                                    <li><strong>Loan Cap:</strong> Borrow up to ₱500,000 explicitly for premium solar panel systems.</li>
                                    <li><strong>Interest Rates:</strong> Lower, competitive rates with flexible payment structures.</li>
                                    <li><strong>Utility Relief:</strong> Significantly reduces reliance on the electric grid from day one.</li>
                                    <li><strong>Flexible Amortization:</strong> Aligned with the member's monthly capacity to pay.</li>
                                </ul>

                                <a href="https://www.sunstar.com.ph/more-articles/pag-ibig-offers-loans-of-up-to-p500t-for-solar-panels" target="_blank" class="btn btn-portal btn-portal-warning w-100 mt-3">
                                    <i class="fas fa-book-open me-2"></i>Read Full Advisory
                                </a>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Tab: GSIS Program Only -->
                <div class="tab-pane fade" id="tab-gsis" role="tabpanel" aria-labelledby="gsis-loan-tab">
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="program-card p-5" style="border-top: 6px solid var(--clr-secondary);">
                                <span class="program-badge">GSIS Ginhawa Solar Energy Loan Program</span>
                                <h3 class="fw-bold mb-4">Ginhawa Solar Energy Loan Program</h3>
                                <p class="lead mb-4">Launched by the Government Service Insurance System (GSIS) to support environmental sustainability and reduce the household utility load of public workers.</p>
                                
                                <div class="row g-4 mb-4">
                                    <div class="col-md-6">
                                        <h5 class="fw-bold text-success"><i class="fas fa-info-circle me-2"></i>Details</h5>
                                        <p class="small text-muted">This program assists qualified public sector employees in transitioning their residential properties to renewable power generation sources.</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h5 class="fw-bold text-success"><i class="fas fa-user-shield me-2"></i>Eligibility Guidelines</h5>
                                        <ul class="program-list mt-2">
                                            <li>Updated monthly premium payments.</li>
                                            <li>Complies with GAA net pay threshold.</li>
                                            <li>No pending administrative / criminal records.</li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="text-center pt-3 border-top">
                                    <a href="https://www.gsis.gov.ph/ginhawa-solar-energy-loan/" target="_blank" class="btn btn-portal btn-portal-primary w-50">
                                        <i class="fas fa-external-link-alt me-2"></i>Apply via Official Portal
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Pag-IBIG Program Only -->
                <div class="tab-pane fade" id="tab-pagibig" role="tabpanel" aria-labelledby="pagibig-loan-tab">
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="program-card p-5" style="border-top: 6px solid var(--clr-primary);">
                                <span class="program-badge pagibig-badge">Up to ₱500,000 Loan Cap</span>
                                <h3 class="fw-bold mb-4">Pag-IBIG Solar Panel Home Improvement Loan</h3>
                                <p class="lead mb-4">Under Pag-IBIG's Home Improvement Financing, members can acquire solar panels as a qualified capital improvement to reduce household electric overheads.</p>
                                
                                <div class="row g-4 mb-4">
                                    <div class="col-md-6">
                                        <h5 class="fw-bold text-warning"><i class="fas fa-coins me-2"></i>Financial Terms</h5>
                                        <p class="small text-muted">Offers competitive, low-interest rates with flexible amortization plans tailored directly around member income limits.</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h5 class="fw-bold text-warning"><i class="fas fa-leaf me-2"></i>Sustainability Goals</h5>
                                        <ul class="program-list pagibig-list mt-2">
                                            <li>Dedicated loan cap of ₱500,000.</li>
                                            <li>Promotes clean energy adoption.</li>
                                            <li>Drastically reduces grid power costs.</li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="text-center pt-3 border-top">
                                    <a href="https://www.sunstar.com.ph/more-articles/pag-ibig-offers-loans-of-up-to-p500t-for-solar-panels" target="_blank" class="btn btn-portal btn-portal-warning w-50">
                                        <i class="fas fa-book-open me-2"></i>Read Full Advisory
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="cta-section" data-aos="fade-up">
        <div class="container">
            <h2 class="fw-bold mb-3">Ready to switch to clean energy?</h2>
            <p class="lead text-white-50 mb-4">Get a free consultation to identify the best solar financing model suitable for your budget.</p>
            <a href="contact.php" class="btn btn-cta">Get a Free Consultation</a>
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
