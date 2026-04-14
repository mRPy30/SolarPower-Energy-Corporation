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

        /* Feature Section */
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

        /* ── New Horizontal Project Card ── */
        .project-card {
            display: flex;
            border: 1px solid #2b2b2b;
            border-radius: 16px;
            overflow: hidden;
            background: #fff;
            box-shadow: none;
            transition: transform 0.25s ease;
        }

        .project-card:hover {
            transform: translateY(-3px);
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
            font-size: 1.05rem;
            font-weight: 800;
            color: #1a1a1a;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            margin: 0;
        }

        .card-project-subtitle {
            font-size: 0.62rem;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #767676;
            margin-top: 2px;
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
            color: #111;
        }

        .detail-text-wrap {
            display: flex;
            flex-direction: column;
        }

        .detail-value {
            font-size: 1.03rem;
            font-weight: 700;
            color: #1a1a1a;
            line-height: 1.2;
        }

        .detail-label {
            font-size: 0.52rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #6f6f6f;
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
                        <p class="text-muted">Systems designed by certified professionals to maximize efficiency based on your specific roof.</p>
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
                        <p class="text-muted">25-year performance warranty on panels with a dedicated local support team.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Recent Installations Section -->
    <section class="py-5">
        <div class="container">
            <div class="row mb-5 align-items-end">
                <div class="col-md-8" data-aos="fade-right">
                    <h2 class="fw-bold">Recent Installations</h2>
                    <p class="text-muted">Proven reliability across residential and commercial sectors.</p>
                </div>
            </div>

            <div class="row g-4" id="projectsContainer">
                <div class="col-12 col-xl-6" data-aos="fade-up">
                    <div class="project-card">
                        <div class="card-img-panel">
                            <img src="assets/img/projects1.png" alt="Waltermart Antipolo">
                        </div>
                        <div class="card-info-panel">
                            <div>
                                <h4 class="card-project-title">WALTERMART ANTIPOLO</h4>
                                <p class="card-project-subtitle">PROJECT TITLE</p>
                            </div>
                            <ul class="project-detail-list">
                                <li class="project-detail-item">
                                    <div class="detail-icon-wrap"><i class="fas fa-map-marker-alt"></i></div>
                                    <div class="detail-text-wrap">
                                        <span class="detail-value">Antipolo</span>
                                        <span class="detail-label">Location</span>
                                    </div>
                                </li>
                                <li class="project-detail-item">
                                    <div class="detail-icon-wrap"><i class="fas fa-solar-panel"></i></div>
                                    <div class="detail-text-wrap">
                                        <span class="detail-value">1406.9 kWp</span>
                                        <span class="detail-label">System Size</span>
                                    </div>
                                </li>
                                <li class="project-detail-item">
                                    <div class="detail-icon-wrap"><i class="fas fa-smog"></i></div>
                                    <div class="detail-text-wrap">
                                        <span class="detail-value">551.50</span>
                                        <span class="detail-label">CO₂ Emissions Saved (t)</span>
                                    </div>
                                </li>
                                <li class="project-detail-item">
                                    <div class="detail-icon-wrap"><i class="fas fa-tree"></i></div>
                                    <div class="detail-text-wrap">
                                        <span class="detail-value">16.46 K</span>
                                        <span class="detail-label">Equivalent Trees Planted</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-6" data-aos="fade-up" data-aos-delay="120">
                    <div class="project-card">
                        <div class="card-img-panel">
                            <img src="assets/img/projects2.jpg" alt="Waltermart Balanga">
                        </div>
                        <div class="card-info-panel">
                            <div>
                                <h4 class="card-project-title">WALTERMART BALANGA</h4>
                                <p class="card-project-subtitle">PROJECT TITLE</p>
                            </div>
                            <ul class="project-detail-list">
                                <li class="project-detail-item">
                                    <div class="detail-icon-wrap"><i class="fas fa-map-marker-alt"></i></div>
                                    <div class="detail-text-wrap">
                                        <span class="detail-value">Balanga</span>
                                        <span class="detail-label">Location</span>
                                    </div>
                                </li>
                                <li class="project-detail-item">
                                    <div class="detail-icon-wrap"><i class="fas fa-solar-panel"></i></div>
                                    <div class="detail-text-wrap">
                                        <span class="detail-value">1101.05 kWp</span>
                                        <span class="detail-label">System Size</span>
                                    </div>
                                </li>
                                <li class="project-detail-item">
                                    <div class="detail-icon-wrap"><i class="fas fa-smog"></i></div>
                                    <div class="detail-text-wrap">
                                        <span class="detail-value">431.61</span>
                                        <span class="detail-label">CO₂ Emissions Saved (t)</span>
                                    </div>
                                </li>
                                <li class="project-detail-item">
                                    <div class="detail-icon-wrap"><i class="fas fa-tree"></i></div>
                                    <div class="detail-text-wrap">
                                        <span class="detail-value">12.88 K</span>
                                        <span class="detail-label">Equivalent Trees Planted</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include "includes/footer.php" ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 1000, once: true });

    </script>
</body>

</html>