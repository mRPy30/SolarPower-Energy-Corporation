<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/icon.png">
    <title>Our Projects | SolarPower Energy Corporation</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">

    <style>
        :root {
            --clr-primary: #ffc107;
            --clr-secondary: #0a5c3d;
            --clr-bg: #f4f7f6;
            --shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .hero-projects {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('assets/img/projects.png') no-repeat center/cover;
            height: 50vh;
            display: flex;
            align-items: center;
            color: white;
            text-align: center;
        }

<<<<<<< HEAD
        .project-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            background: white;
            box-shadow: var(--shadow);
            transition: 0.3s ease;
        }

        .project-card:hover {
            transform: translateY(-10px);
        }

        .project-img-box {
            height: 250px;
            overflow: hidden;
            position: relative;
        }

        .location-tag {
            position: absolute;
            bottom: 15px;
            left: 15px;
            background: rgba(10, 92, 61, 0.9);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        /* Feature Section (Based on User Requirements) */
=======
        /* Feature Section */
>>>>>>> 367e9b3fa04878f4e17e9221a0d6b622abc19955
        .feature-box {
            padding: 40px;
            border-radius: 15px;
            background: white;
            box-shadow: var(--shadow);
            height: 100%;
            border-top: 5px solid var(--clr-primary);
        }

        .feature-icon {
            font-size: 2.5rem;
            color: var(--clr-primary);
            margin-bottom: 20px;
        }
<<<<<<< HEAD
=======

        /* ── New Horizontal Project Card ── */
        .project-card {
            display: flex;
            border: 1px solid rgba(10, 92, 61, 0.15);
            /* subtle green border */
            border-radius: 16px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .project-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(10, 92, 61, 0.15);
            /* green tinted shadow */
        }

        /* Left image panel */
        .project-card .card-img-panel {
            flex: 0 0 42%;
            min-height: 220px;
            overflow: hidden;
            position: relative;
        }

        .project-card .card-img-panel img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.4s ease;
        }

        .project-card:hover .card-img-panel img {
            transform: scale(1.05);
        }

        /* Right info panel */
        .project-card .card-info-panel {
            flex: 1;
            padding: 22px 24px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            gap: 14px;
        }

        .card-project-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: #1b262c;
            /* Theme dark grey-blue */
            letter-spacing: 0.02em;
            text-transform: uppercase;
            margin: 0;
        }

        .card-project-subtitle {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--clr-primary);
            /* Yellow accent from logo Sun */
            margin-top: 4px;
        }

        /* Individual detail rows */
        .project-detail-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .project-detail-item {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .detail-icon-wrap {
            width: 28px;
            height: 28px;
            border-radius: 0;
            background: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .detail-icon-wrap i {
            font-size: 1.4rem;
            color: var(--clr-secondary);
            /* Theme Green from logo panel */
        }

        .detail-text-wrap {
            display: flex;
            flex-direction: column;
        }

        .detail-value {
            font-size: 1.05rem;
            font-weight: 700;
            color: #1b262c;
            /* Text matching the title color */
            line-height: 1.2;
        }

        .detail-label {
            font-size: 0.55rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #6f808a;
            /* Softened dark blue-grey */
            margin-top: 2px;
        }

        /* Responsive: stack on small screens */
        @media (max-width: 575px) {
            .project-card {
                flex-direction: column;
            }

            .project-card .card-img-panel {
                flex: none;
                height: 220px;
                min-height: unset;
            }
        }

        /* View More Button Styles */
        .view-more-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            padding: 12px 35px;
            background: #fff;
            border: 2px solid #e0e0e0;
            border-radius: 50px;
            color: black;
            /* Deep blue text like the image */
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .view-more-btn-text {
            line-height: 1.2;
        }

        .view-more-btn i {
            font-size: 1.1rem;
            color: black;
            transition: transform 0.3s ease, color 0.3s ease;
        }

        .view-more-btn:hover {
            background-color: #e7ad00;
            border-color: #e7ad00;
            color: #fff;
        }

        .view-more-btn:hover i {
            color: #fff;
        }

        .hidden-project {
            display: none !important;
        }

        /* ═══════════════════════════════════════════
           PROJECT MODAL  –  scoped to .pm-* classes
        ═══════════════════════════════════════════ */

        /* Clickable card cursor */
        .project-card {
            cursor: pointer;
        }

        /* Backdrop */
        #pmOverlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 1080;
            background: rgba(0, 0, 0, 0.65);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            align-items: center;
            justify-content: center;
            padding: 16px;
            animation: pmFadeIn 0.22s ease;
        }

        #pmOverlay.pm-visible {
            display: flex;
        }

        @keyframes pmFadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* Modal card */
        .pm-modal {
            position: relative;
            display: flex;
            width: 100%;
            max-width: 980px;
            min-height: 500px;
            max-height: 92vh;
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.12);
            animation: pmSlideUp 0.28s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes pmSlideUp {
            from {
                transform: translateY(30px) scale(0.97);
                opacity: 0;
            }

            to {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
        }

        /* Close button */
        .pm-close {
            position: absolute;
            top: 14px;
            right: 16px;
            z-index: 10;
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            font-size: 1.1rem;
            color: #1b262c;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.18);
            transition: background 0.2s, transform 0.2s;
        }

        .pm-close:hover {
            background: #ffc107;
            transform: rotate(90deg);
        }

        /* ── Left image panel ── */
        .pm-image-panel {
            flex: 0 0 48%;
            position: relative;
            background: #111;
            overflow: hidden;
        }

        .pm-carousel {
            width: 100%;
            height: 100%;
            position: relative;
        }

        .pm-slide {
            position: absolute;
            inset: 0;
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .pm-slide.pm-active {
            opacity: 1;
            position: relative;
            height: 100%;
        }

        .pm-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            min-height: 340px;
        }

        /* Carousel arrows */
        .pm-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 5;
            background: rgba(255, 255, 255, 0.92);
            border: 2px solid #1b262c;
            border-radius: 50%;
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: #1b262c;
            cursor: pointer;
            transition: background 0.2s, border-color 0.2s, color 0.2s, transform 0.2s;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.2);
        }

        .pm-arrow:hover {
            background: #ffc107;
            border-color: #ffc107;
            color: #fff;
            transform: translateY(-50%) scale(1.08);
        }

        .pm-arrow-prev {
            left: 14px;
        }

        .pm-arrow-next {
            right: 14px;
        }

        /* Carousel dots */
        .pm-dots {
            position: absolute;
            bottom: 14px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 6px;
            z-index: 5;
        }

        .pm-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
        }

        .pm-dot.pm-active {
            background: #ffc107;
            transform: scale(1.3);
        }

        /* ── Right info panel ── */
        .pm-info-panel {
            flex: 1;
            padding: 36px 32px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 20px;
            overflow-y: auto;
        }

        .pm-header-tag {
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #fff;
            background: var(--clr-secondary);
            display: inline-block;
            padding: 4px 12px;
            border-radius: 30px;
            width: fit-content;
        }

        .pm-title {
            font-size: 1.7rem;
            font-weight: 900;
            color: #1b262c;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            line-height: 1.15;
            margin: 0;
        }

        .pm-subtitle {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--clr-primary);
            margin: 0;
        }

        .pm-divider {
            border: none;
            border-top: 1px solid rgba(10, 92, 61, 0.12);
            margin: 0;
        }

        /* Metrics grid */
        .pm-metrics {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px 16px;
        }

        .pm-metric-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .pm-metric-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(10, 92, 61, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .pm-metric-icon i {
            font-size: 1rem;
            color: var(--clr-secondary);
        }

        .pm-metric-text {
            display: flex;
            flex-direction: column;
        }

        .pm-metric-value {
            font-size: 1.15rem;
            font-weight: 800;
            color: #1b262c;
            line-height: 1.2;
        }

        .pm-metric-label {
            font-size: 0.54rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #7a8c95;
            margin-top: 3px;
        }

        /* ── Responsive: stack vertically on small screens ── */
        @media (max-width: 767px) {
            .pm-modal {
                flex-direction: column;
                max-height: 95vh;
                overflow-y: auto;
            }

            .pm-image-panel {
                flex: none;
                height: 260px;
                min-height: unset;
            }

            .pm-slide img {
                min-height: unset;
                height: 260px;
            }

            .pm-info-panel {
                padding: 24px 20px;
            }

            .pm-title {
                font-size: 1.25rem;
            }

            .pm-metrics {
                grid-template-columns: 1fr;
            }
        }
>>>>>>> 367e9b3fa04878f4e17e9221a0d6b622abc19955
    </style>
</head>

<body>

    <?php include "includes/header.php" ?>

    <section class="hero-projects">
        <div class="container" data-aos="fade-up">
            <p class="text-warning fw-bold text-uppercase">Our Portfolio</p>
            <h1 class="display-3 fw-bold">Turning Vision Into Power</h1>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container py-5">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="fw-bold">Why Choose SolarPower?</h2>
                <p class="text-muted">The expertise you need for a lifetime of savings.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-box text-center">
                        <i class="fas fa-tools feature-icon"></i>
                        <h4>Expert Engineering</h4>
<<<<<<< HEAD
                        <p class="text-muted">Systems designed by certified professionals to maximize efficiency based on your specific roof.</p>
=======
                        <p class="text-muted">Systems designed by certified professionals to maximize efficiency based
                            on your specific roof.</p>
>>>>>>> 367e9b3fa04878f4e17e9221a0d6b622abc19955
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-box text-center">
                        <i class="fas fa-hand-holding-usd feature-icon"></i>
                        <h4>Zero-Down Options</h4>
                        <p class="text-muted">Flexible financing and models making solar affordable from day one.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-box text-center">
                        <i class="fas fa-shield-alt feature-icon"></i>
                        <h4>Long-term Warranty</h4>
<<<<<<< HEAD
                        <p class="text-muted">25-year performance warranty on panels with a dedicated local support team.</p>
=======
                        <p class="text-muted">25-year performance warranty on panels with a dedicated local support
                            team.</p>
>>>>>>> 367e9b3fa04878f4e17e9221a0d6b622abc19955
                    </div>
                </div>
            </div>
        </div>
    </section>

<<<<<<< HEAD
    <!--Recent Installation Section-->
=======
    <!-- Recent Installations Section -->
>>>>>>> 367e9b3fa04878f4e17e9221a0d6b622abc19955
    <section class="py-5">
        <div class="container">
            <div class="row mb-5 align-items-end">
                <div class="col-md-8" data-aos="fade-right">
                    <h2 class="fw-bold">Recent Installations</h2>
                    <p class="text-muted">Proven reliability across residential and commercial sectors.</p>
                </div>
            </div>

            <div class="row g-4" id="projectsContainer">
<<<<<<< HEAD
                <div class="col-lg-6" data-aos="fade-up">
                    <div class="project-card">
                        <div class="project-img-box">
                            <img src="assets/img/projects1.png" class="w-100 h-100 object-fit-cover" alt="Residential Project">
                            <span class="location-tag"><i class="fas fa-map-marker-alt me-2"></i>BF Homes, Paranaque</span>
                        </div>
                        <div class="p-4">
                            <h4 class="fw-bold">BF Homes Parañaque</h4>
                            <ul>
                                <li><strong>System:</strong> 12kW Hybrid</li>
                                <li><strong>Monthly bill before:</strong> ₱22,000</li>
                                <li><strong>Monthly bill after:</strong> ₱11,000</li>
                                <li><strong>Results:</strong> 50% savings</li>
=======
                <div class="col-12 col-xl-6" data-aos="fade-up">
                    <div class="project-card" data-pm-title="BF Homes Parañaque"
                        data-pm-subtitle="Residential Installation"
                        data-pm-images='["assets/img/projects1.png","assets/img/bfhomes2.jpg","assets/img/bfhomes3.png"]'
                        data-pm-metrics='[
                           {"icon":"fa-map-marker-alt","value":"Parañaque City","label":"Location"},
                           {"icon":"fa-solar-panel","value":"12kW Hybrid","label":"System Size"},
                           {"icon":"fa-smog","value":"470.80 t","label":"CO₂ Emissions Saved"},
                           {"icon":"fa-tree","value":"14.10 K","label":"Equivalent Trees Planted"}
                         ]'>
                        <div class="card-img-panel">
                            <img src="assets/img/projects1.png" alt="BF Homes Parañaque">
                        </div>
                        <div class="card-info-panel">
                            <div>
                                <h4 class="card-project-title">BF HOMES PARAÑAQUE</h4>
                                <p class="card-project-subtitle">RESIDENTIAL INSTALLATION</p>
                            </div>
                            <ul class="project-detail-list">
                                <li class="project-detail-item">
                                    <div class="detail-icon-wrap"><i class="fas fa-map-marker-alt"></i></div>
                                    <div class="detail-text-wrap">
                                        <span class="detail-value">Parañaque City</span>
                                        <span class="detail-label">Location</span>
                                    </div>
                                </li>
                                <li class="project-detail-item">
                                    <div class="detail-icon-wrap"><i class="fas fa-solar-panel"></i></div>
                                    <div class="detail-text-wrap">
                                        <span class="detail-value">12kW Hybrid</span>
                                        <span class="detail-label">System Size</span>
                                    </div>
                                </li>
                                <li class="project-detail-item">
                                    <div class="detail-icon-wrap"><i class="fas fa-smog"></i></div>
                                    <div class="detail-text-wrap">
                                        <span class="detail-value">470.80 t</span>
                                        <span class="detail-label">CO₂ Emissions Saved</span>
                                    </div>
                                </li>
                                <li class="project-detail-item">
                                    <div class="detail-icon-wrap"><i class="fas fa-tree"></i></div>
                                    <div class="detail-text-wrap">
                                        <span class="detail-value">14.10 K</span>
                                        <span class="detail-label">Equivalent Trees Planted</span>
                                    </div>
                                </li>
>>>>>>> 367e9b3fa04878f4e17e9221a0d6b622abc19955
                            </ul>
                        </div>
                    </div>
                </div>

<<<<<<< HEAD
                <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="project-card">
                        <div class="project-img-box">
                            <img src="assets/img/projects2.jpg" class="w-100 h-100 object-fit-cover" alt="Commercial Project">
                            <span class="location-tag"><i class="fas fa-map-marker-alt me-2"></i>Lucero, Pangasinan</span>
                        </div>
                        <div class="p-4">
                             <h4 class="fw-bold">BC Homes Laguna</h4>
                            <ul>
                                <li><strong>System:</strong> 20kW Hybrid</li>
                                <li><strong>Monthly bill before:</strong> ₱23,000</li>
                                <li><strong>Monthly bill after:</strong> ₱4,500</li>
                                <li><strong>Results:</strong> 80% savings</li>
=======
                <div class="col-12 col-xl-6" data-aos="fade-up" data-aos-delay="120">
                    <div class="project-card" data-pm-title="ACE Admin Building"
                        data-pm-subtitle="Commercial Installation"
                        data-pm-images='["assets/img/paran2.jpg","assets/img/paran3.jpg","assets/img/demo-solar4.jpg"]'
                        data-pm-metrics='[
                           {"icon":"fa-map-marker-alt","value":"Sucat, Parañaque City","label":"Location"},
                           {"icon":"fa-solar-panel","value":"6kW Hybrid","label":"System Size"},
                           {"icon":"fa-smog","value":"235.40 t","label":"CO₂ Emissions Saved"},
                           {"icon":"fa-tree","value":"7.05 K","label":"Equivalent Trees Planted"}
                         ]'>
                        <div class="card-img-panel">
                            <img src="assets/img/sucat.jpg" alt="Ace Admin Building Center">
                        </div>
                        <div class="card-info-panel">
                            <div>
                                <h4 class="card-project-title">ACE ADMIN BUILDING</h4>
                                <p class="card-project-subtitle">COMMERCIAL INSTALLATION</p>
                            </div>
                            <ul class="project-detail-list">
                                <li class="project-detail-item">
                                    <div class="detail-icon-wrap"><i class="fas fa-map-marker-alt"></i></div>
                                    <div class="detail-text-wrap">
                                        <span class="detail-value">Sucat, Parañaque City</span>
                                        <span class="detail-label">Location</span>
                                    </div>
                                </li>
                                <li class="project-detail-item">
                                    <div class="detail-icon-wrap"><i class="fas fa-solar-panel"></i></div>
                                    <div class="detail-text-wrap">
                                        <span class="detail-value">6kW Hybrid</span>
                                        <span class="detail-label">System Size</span>
                                    </div>
                                </li>
                                <li class="project-detail-item">
                                    <div class="detail-icon-wrap"><i class="fas fa-smog"></i></div>
                                    <div class="detail-text-wrap">
                                        <span class="detail-value">235.40 t</span>
                                        <span class="detail-label">CO₂ Emissions Saved</span>
                                    </div>
                                </li>
                                <li class="project-detail-item">
                                    <div class="detail-icon-wrap"><i class="fas fa-tree"></i></div>
                                    <div class="detail-text-wrap">
                                        <span class="detail-value">7.05 K</span>
                                        <span class="detail-label">Equivalent Trees Planted</span>
                                    </div>
                                </li>
>>>>>>> 367e9b3fa04878f4e17e9221a0d6b622abc19955
                            </ul>
                        </div>
                    </div>
                </div>

<<<<<<< HEAD
                <!-- Hidden Projects -->
                <div class="col-lg-6 more-projects" style="display: none;" data-aos="fade-up">
                    <div class="project-card">
                        <div class="project-img-box">
                            <img src="assets/img/demo-solar1.webp" class="w-100 h-100 object-fit-cover" alt="Residential Project">
                            <span class="location-tag"><i class="fas fa-map-marker-alt me-2"></i>Makati City</span>
                        </div>
                        <div class="p-4">
                            <h4 class="fw-bold">Makati Residential Complex</h4>
                            <ul>
                                <li><strong>System:</strong> 15kW Grid-Tied</li>
                                <li><strong>Monthly bill before:</strong> ₱25,000</li>
                                <li><strong>Monthly bill after:</strong> ₱3,200</li>
                                <li><strong>Results:</strong> 87% savings</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 more-projects" style="display: none;" data-aos="fade-up" data-aos-delay="200">
                    <div class="project-card">
                        <div class="project-img-box">
                            <img src="assets/img/demo-solar2.jpg" class="w-100 h-100 object-fit-cover" alt="Commercial Project">
                            <span class="location-tag"><i class="fas fa-map-marker-alt me-2"></i>Quezon City</span>
                        </div>
                        <div class="p-4">
                             <h4 class="fw-bold">QC Commercial Building</h4>
                            <ul>
                                <li><strong>System:</strong> 30kW Hybrid</li>
                                <li><strong>Monthly bill before:</strong> ₱45,000</li>
                                <li><strong>Monthly bill after:</strong> ₱8,500</li>
                                <li><strong>Results:</strong> 81% savings</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 more-projects" style="display: none;" data-aos="fade-up">
                    <div class="project-card">
                        <div class="project-img-box">
                            <img src="assets/img/demo-solar3.png" class="w-100 h-100 object-fit-cover" alt="Residential Project">
                            <span class="location-tag"><i class="fas fa-map-marker-alt me-2"></i>Cavite</span>
                        </div>
                        <div class="p-4">
                            <h4 class="fw-bold">Cavite Subdivision</h4>
                            <ul>
                                <li><strong>System:</strong> 10kW Grid-Tied</li>
                                <li><strong>Monthly bill before:</strong> ₱15,000</li>
                                <li><strong>Monthly bill after:</strong> ₱2,100</li>
                                <li><strong>Results:</strong> 86% savings</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 more-projects" style="display: none;" data-aos="fade-up" data-aos-delay="200">
                    <div class="project-card">
                        <div class="project-img-box">
                            <img src="assets/img/demo-solar4.jpg" class="w-100 h-100 object-fit-cover" alt="Commercial Project">
                            <span class="location-tag"><i class="fas fa-map-marker-alt me-2"></i>Bulacan</span>
                        </div>
                        <div class="p-4">
                             <h4 class="fw-bold">Bulacan Farm Resort</h4>
                            <ul>
                                <li><strong>System:</strong> 18kW Hybrid</li>
                                <li><strong>Monthly bill before:</strong> ₱28,000</li>
                                <li><strong>Monthly bill after:</strong> ₱4,200</li>
                                <li><strong>Results:</strong> 85% savings</li>
=======
                <div class="col-12 col-xl-6 hidden-project project-extra" data-aos="fade-up" data-aos-delay="240">
                    <div class="project-card" data-pm-title="Bacoor Residential" data-pm-subtitle="Maintenance Service"
                        data-pm-images='["assets/img/bacoor.jpg","assets/img/bacoor2.jpg","assets/img/bacoor3.jpg"]'
                        data-pm-metrics='[
                           {"icon":"fa-map-marker-alt","value":"Bacoor City, Cavite","label":"Location"},
                           {"icon":"fa-tools","value":"Preventive Maintenance","label":"Service Type"},
                           {"icon":"fa-check-circle","value":"Completed","label":"Status"},
                           {"icon":"fa-bolt","value":"+15% Efficiency","label":"Performance Optimized"}
                         ]'>
                        <div class="card-img-panel">
                            <img src="assets/img/bacoor.jpg" alt="Bacoor City Preventive Maintenance">
                        </div>
                        <div class="card-info-panel">
                            <div>
                                <h4 class="card-project-title">BACOOR RESIDENTIAL</h4>
                                <p class="card-project-subtitle">MAINTENANCE SERVICE</p>
                            </div>
                            <ul class="project-detail-list">
                                <li class="project-detail-item">
                                    <div class="detail-icon-wrap"><i class="fas fa-map-marker-alt"></i></div>
                                    <div class="detail-text-wrap">
                                        <span class="detail-value">Bacoor City, Cavite</span>
                                        <span class="detail-label">Location</span>
                                    </div>
                                </li>
                                <li class="project-detail-item">
                                    <div class="detail-icon-wrap"><i class="fas fa-tools"></i></div>
                                    <div class="detail-text-wrap">
                                        <span class="detail-value">Preventive Maintenance</span>
                                        <span class="detail-label">Service Type</span>
                                    </div>
                                </li>
                                <li class="project-detail-item">
                                    <div class="detail-icon-wrap"><i class="fas fa-check-circle"></i></div>
                                    <div class="detail-text-wrap">
                                        <span class="detail-value">Completed</span>
                                        <span class="detail-label">Status</span>
                                    </div>
                                </li>
                                <li class="project-detail-item">
                                    <div class="detail-icon-wrap"><i class="fas fa-bolt"></i></div>
                                    <div class="detail-text-wrap">
                                        <span class="detail-value">+15% Efficiency</span>
                                        <span class="detail-label">Performance Optimized</span>
                                    </div>
                                </li>
>>>>>>> 367e9b3fa04878f4e17e9221a0d6b622abc19955
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

<<<<<<< HEAD
            <div class="text-center mt-5">
                <button id="viewMoreBtn" class="btn btn-warning btn-lg px-5 py-3" onclick="toggleProjects()">
                    <i class="fas fa-plus-circle me-2"></i>View More Projects
                </button>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-5 bg-light">
        <div class="container py-5">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="fw-bold">What Our Clients Say</h2>
                <p class="text-muted">Real experiences from homeowners and businesses who made the switch.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-box">
                        <div class="d-flex align-items-center mb-3">
                            <img src="assets/img/testimonial_image.jpg" alt="Maria Santos" class="rounded-circle me-3" style="width: 60px; height: 60px; object-fit: cover;">
                            <div>
                                <strong>Maria Santos</strong>
                                <p class="text-muted small mb-0">BF Homes, Parañaque</p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="text-muted fst-italic">"Our electric bill dropped dramatically after installation. The team was professional and the system has been running flawlessly for over a year now."</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-box">
                        <div class="d-flex align-items-center mb-3">
                            <img src="assets/img/testimonial_image2.avif" alt="Roberto Cruz" class="rounded-circle me-3" style="width: 60px; height: 60px; object-fit: cover;">
                            <div>
                                <strong>Roberto Cruz</strong>
                                <p class="text-muted small mb-0">Lucero, Pangasinan</p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="text-muted fst-italic">"The financing options made it easy to get started without upfront costs. We're now saving thousands every month and our carbon footprint is much lower."</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-box">
                        <div class="d-flex align-items-center mb-3">
                            <img src="assets/img/testimonial_image3.jpg" alt="Jennifer Reyes" class="rounded-circle me-3" style="width: 60px; height: 60px; object-fit: cover;">
                            <div>
                                <strong>Jennifer Reyes</strong>
                                <p class="text-muted small mb-0">BC Homes, Laguna</p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="text-muted fst-italic">"Excellent customer service from consultation to installation. The 25-year warranty gives us peace of mind knowing we're covered for the long term."</p>
=======
            <div class="row mt-5">
                <div class="col-12 d-flex justify-content-center" data-aos="fade-up">
                    <div class="view-more-btn" id="viewMoreBtn" onclick="toggleProjects()">
                        <div class="view-more-btn-text">View More<br>Products</div>
                        <i class="fas fa-chevron-down" id="viewMoreIcon"></i>
>>>>>>> 367e9b3fa04878f4e17e9221a0d6b622abc19955
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include "includes/footer.php" ?>

<<<<<<< HEAD
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });

        function toggleProjects() {
            const moreProjects = document.querySelectorAll('.more-projects');
            const viewMoreBtn = document.getElementById('viewMoreBtn');
            
            moreProjects.forEach(project => {
                if (project.style.display === 'none') {
                    project.style.display = 'block';
                    viewMoreBtn.innerHTML = '<i class="fas fa-minus-circle me-2"></i>View Less';
                } else {
                    project.style.display = 'none';
                    viewMoreBtn.innerHTML = '<i class="fas fa-plus-circle me-2"></i>View More Projects';
                    // Scroll to Recent Installations section
                    document.querySelector('.py-5').scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        }
=======
    <!-- ═══════════ PROJECT MODAL ═══════════ -->
    <div id="pmOverlay" role="dialog" aria-modal="true" aria-label="Project Details">
        <div class="pm-modal" id="pmModal">

            <!-- Close button -->
            <button class="pm-close" id="pmClose" aria-label="Close modal">
                <i class="fas fa-times"></i>
            </button>

            <!-- Left: image carousel -->
            <div class="pm-image-panel">
                <div class="pm-carousel" id="pmCarousel">
                    <!-- slides injected by JS -->
                </div>
                <!-- Arrow buttons -->
                <button class="pm-arrow pm-arrow-prev" id="pmPrev" aria-label="Previous image">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="pm-arrow pm-arrow-next" id="pmNext" aria-label="Next image">
                    <i class="fas fa-chevron-right"></i>
                </button>
                <!-- Dots -->
                <div class="pm-dots" id="pmDots"></div>
            </div>

            <!-- Right: project details -->
            <div class="pm-info-panel" id="pmInfoPanel">
                <!-- content injected by JS -->
            </div>

        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        /* ── AOS init (graceful fallback if library missing) ── */
        if (typeof AOS !== 'undefined') {
            AOS.init({ duration: 1000, once: true });
        }

        /* ── View More / Less toggle ── */
        function toggleProjects() {
            var extras = document.querySelectorAll('.project-extra');
            var btnText = document.querySelector('.view-more-btn-text');
            var btnIcon = document.getElementById('viewMoreIcon');
            var isHidden = extras[0].classList.contains('hidden-project');

            extras.forEach(function (el) {
                if (isHidden) {
                    el.classList.remove('hidden-project');
                } else {
                    el.classList.add('hidden-project');
                }
            });

            if (isHidden) {
                btnText.innerHTML = 'View Less<br>Products';
                btnIcon.classList.remove('fa-chevron-down');
                btnIcon.classList.add('fa-chevron-up');
            } else {
                btnText.innerHTML = 'View More<br>Products';
                btnIcon.classList.remove('fa-chevron-up');
                btnIcon.classList.add('fa-chevron-down');
            }
        }

        /* ══════════════════════════════════════════════
           PROJECT MODAL + CAROUSEL  –  Vanilla JS
        ══════════════════════════════════════════════ */
        (function () {
            'use strict';

            var overlay = document.getElementById('pmOverlay');
            var carousel = document.getElementById('pmCarousel');
            var dotsWrap = document.getElementById('pmDots');
            var infoPanel = document.getElementById('pmInfoPanel');
            var btnClose = document.getElementById('pmClose');
            var btnPrev = document.getElementById('pmPrev');
            var btnNext = document.getElementById('pmNext');

            var images = [];
            var currentIdx = 0;

            /* ── Helpers ── */
            function safeParseJSON(str, fallback) {
                try { return JSON.parse(str); } catch (e) { return fallback; }
            }

            /* ── Build carousel slides ── */
            function buildCarousel(imgs) {
                carousel.innerHTML = '';
                dotsWrap.innerHTML = '';
                images = imgs;
                currentIdx = 0;

                imgs.forEach(function (src, i) {
                    var slide = document.createElement('div');
                    slide.className = 'pm-slide' + (i === 0 ? ' pm-active' : '');
                    var img = document.createElement('img');
                    img.src = src;
                    img.alt = 'Project image ' + (i + 1);
                    img.loading = 'lazy';
                    slide.appendChild(img);
                    carousel.appendChild(slide);

                    /* dot */
                    var dot = document.createElement('span');
                    dot.className = 'pm-dot' + (i === 0 ? ' pm-active' : '');
                    dot.setAttribute('data-idx', i);
                    dot.addEventListener('click', function (e) {
                        e.stopPropagation();
                        goTo(parseInt(this.getAttribute('data-idx')));
                    });
                    dotsWrap.appendChild(dot);
                });

                /* hide arrows when only one image */
                btnPrev.style.display = imgs.length < 2 ? 'none' : '';
                btnNext.style.display = imgs.length < 2 ? 'none' : '';
                dotsWrap.style.display = imgs.length < 2 ? 'none' : '';
            }

            /* ── Navigate carousel ── */
            function goTo(idx) {
                var slides = carousel.querySelectorAll('.pm-slide');
                var dots = dotsWrap.querySelectorAll('.pm-dot');

                slides[currentIdx].classList.remove('pm-active');
                dots[currentIdx].classList.remove('pm-active');

                currentIdx = (idx + images.length) % images.length;

                slides[currentIdx].classList.add('pm-active');
                dots[currentIdx].classList.add('pm-active');
            }

            btnPrev.addEventListener('click', function (e) {
                e.stopPropagation();
                goTo(currentIdx - 1);
            });

            btnNext.addEventListener('click', function (e) {
                e.stopPropagation();
                goTo(currentIdx + 1);
            });

            /* ── Build info panel ── */
            function buildInfo(title, subtitle, metrics) {
                var html = '';
                html += '<span class="pm-header-tag">' + escHtml(subtitle) + '</span>';
                html += '<h2 class="pm-title">' + escHtml(title) + '</h2>';
                html += '<p class="pm-subtitle">' + escHtml(subtitle) + '</p>';
                html += '<hr class="pm-divider">';
                html += '<div class="pm-metrics">';
                metrics.forEach(function (m) {
                    html += '<div class="pm-metric-item">';
                    html += '<div class="pm-metric-icon"><i class="fas ' + escHtml(m.icon) + '"></i></div>';
                    html += '<div class="pm-metric-text">';
                    html += '<span class="pm-metric-value">' + escHtml(m.value) + '</span>';
                    html += '<span class="pm-metric-label">' + escHtml(m.label) + '</span>';
                    html += '</div></div>';
                });
                html += '</div>';
                infoPanel.innerHTML = html;
            }

            /* ── Escape HTML to prevent XSS ── */
            function escHtml(str) {
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }

            /* ── Extract data from card DOM (fallback) ── */
            function extractFromCard(card) {
                var titleEl = card.querySelector('.card-project-title');
                var subtitleEl = card.querySelector('.card-project-subtitle');
                var imgEl = card.querySelector('.card-img-panel img');
                var detailVals = card.querySelectorAll('.detail-value');
                var detailLbls = card.querySelectorAll('.detail-label');
                var detailIcos = card.querySelectorAll('.detail-icon-wrap i');

                var title = titleEl ? titleEl.textContent.trim() : 'Project';
                var subtitle = subtitleEl ? subtitleEl.textContent.trim() : '';
                var imgSrc = imgEl ? imgEl.src : '';

                var metrics = [];
                detailVals.forEach(function (el, i) {
                    var iconEl = detailIcos[i];
                    var iconClass = '';
                    if (iconEl) {
                        iconClass = Array.from(iconEl.classList)
                            .find(function (c) { return c.startsWith('fa-') && c !== 'fa-solid'; }) || '';
                    }
                    metrics.push({
                        icon: iconClass,
                        value: el.textContent.trim(),
                        label: detailLbls[i] ? detailLbls[i].textContent.trim() : ''
                    });
                });

                return { title: title, subtitle: subtitle, images: [imgSrc], metrics: metrics };
            }

            /* ── Open modal ── */
            function openModal(card) {
                var rawImages = card.getAttribute('data-pm-images');
                var rawMetrics = card.getAttribute('data-pm-metrics');
                var pmTitle = card.getAttribute('data-pm-title');
                var pmSubtitle = card.getAttribute('data-pm-subtitle');

                var data;
                if (rawImages && rawMetrics && pmTitle) {
                    data = {
                        title: pmTitle,
                        subtitle: pmSubtitle || '',
                        images: safeParseJSON(rawImages, []),
                        metrics: safeParseJSON(rawMetrics, [])
                    };
                } else {
                    /* graceful DOM fallback */
                    data = extractFromCard(card);
                }

                if (!data.images.length) {
                    var imgEl = card.querySelector('.card-img-panel img');
                    if (imgEl) data.images = [imgEl.src];
                }

                buildCarousel(data.images);
                buildInfo(data.title, data.subtitle, data.metrics);

                overlay.classList.add('pm-visible');
                document.body.style.overflow = 'hidden';
                btnClose.focus();
            }

            /* ── Close modal ── */
            function closeModal() {
                overlay.classList.remove('pm-visible');
                document.body.style.overflow = '';
            }

            /* ── Wire up every .project-card ── */
            document.querySelectorAll('.project-card').forEach(function (card) {
                card.addEventListener('click', function () {
                    openModal(card);
                });
            });

            /* ── Close triggers ── */
            btnClose.addEventListener('click', closeModal);

            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) closeModal();
            });

            /* ── Keyboard support ── */
            document.addEventListener('keydown', function (e) {
                if (!overlay.classList.contains('pm-visible')) return;
                switch (e.key) {
                    case 'Escape': closeModal(); break;
                    case 'ArrowLeft': goTo(currentIdx - 1); break;
                    case 'ArrowRight': goTo(currentIdx + 1); break;
                }
            });

        }());
>>>>>>> 367e9b3fa04878f4e17e9221a0d6b622abc19955
    </script>
</body>

</html>