<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/icon.png">
    <title>Services | SolarPower Energy Corporation</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">

    <style>
        :root {
            --clr-primary: #ffc107; 
            --clr-secondary: #0a5c3d; 
            --shadow-box: 0 10px 30px rgba(0,0,0,0.1);
        }

        .hero-services {
            background: linear-gradient(to right, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.4)), 
                url('assets/img/service.png') no-repeat center center/cover;
            height: 50vh; display: flex; align-items: center; color: white; text-align: center;
        }

        /* Photo Capability Cards */
        .capability-card {
            border: none; background: #fff; border-radius: 15px;
            overflow: hidden; box-shadow: var(--shadow-box); transition: 0.4s ease;
            height: 100%; display: flex; flex-direction: column;
        }

        .capability-card:hover { transform: translateY(-10px); }

        .capability-img-wrapper {
            height: 220px; overflow: hidden; position: relative;
        }

        .capability-img-wrapper img {
            width: 100%; height: 100%; object-fit: cover; transition: 0.5s;
        }

        .capability-card:hover .capability-img-wrapper img { transform: scale(1.1); }

        .capability-content { padding: 25px; flex-grow: 1; }
        .capability-content h4 { color: var(--clr-secondary); font-weight: 700; margin-bottom: 12px; }
        .capability-content p { font-size: 0.95rem; color: #666; line-height: 1.6; }

        /* Preservation of original sustainability style */
        .sustainability-banner {
            background: var(--clr-secondary); color: white; padding: 60px 30px;
            border-radius: 15px; margin-top: 50px;
        }
    </style>
</head>
<body>

    <?php include "includes/header.php" ?>

    <section class="hero-services">
        <div class="container" data-aos="zoom-in">
            <span class="text-warning fw-bold text-uppercase">Solutions</span>
            <h1 class="display-3 fw-bold">Our Core Capabilities</h1>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container py-5">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="capability-card">
                        <div class="capability-img-wrapper">
                            <img src="https://images.unsplash.com/photo-1509391366360-2e959784a276?w=800" alt="Supplier">
                        </div>
                        <div class="capability-content">
                            <h4>Supplier</h4>
                            <p>Direct source for premium photovoltaic panels and high-efficiency inverters from global Tier-1 brands.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="capability-card">
                        <div class="capability-img-wrapper">
                            <img src="https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?w=800" alt="Installer">
                        </div>
                        <div class="capability-content">
                            <h4>Installer</h4>
                            <p>Expert technical teams providing end-to-end residential and commercial mounting and system deployment.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="capability-card">
                        <div class="capability-img-wrapper">
                            <img src="https://images.unsplash.com/photo-1504328345606-18bbc8c9d7d1?w=800" alt="Contractor">
                        </div>
                        <div class="capability-content">
                            <h4>Contractor</h4>
                            <p>Full project management, from site assessment and permitting to final grid interconnection compliance.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="capability-card">
                        <div class="capability-img-wrapper">
                            <img src="https://images.unsplash.com/photo-1560179707-f14e90ef3623?w=800" alt="Dealer">
                        </div>
                        <div class="capability-content">
                            <h4>Dealer</h4>
                            <p>Authorized local partnership offering specialized solar hardware packages and customized energy kits.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="capability-card">
                        <div class="capability-img-wrapper">
                            <img src="https://images.unsplash.com/photo-1578575437130-527eed3abbec?w=800" alt="Distributor">
                        </div>
                        <div class="capability-content">
                            <h4>Distributor</h4>
                            <p>Logistics hub ensuring nationwide availability of renewable energy technology to sub-dealers and partners.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="capability-card">
                        <div class="capability-img-wrapper">
                            <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?w=800" alt="Seller">
                        </div>
                        <div class="capability-content">
                            <h4>Seller</h4>
                            <p>Retail energy consultants providing transparent pricing and tailored ROI estimates for every client.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mb-5" data-aos="flip-up">
            <div class="sustainability-banner text-center shadow">
                <i class="fas fa-leaf fa-3x mb-4 text-warning"></i>
                <h2 class="fw-bold">Commitment to Sustainability</h2>
                <p class="px-lg-5 fs-5">We believe in creating a positive environmental impact through clean energy. Every installation is a step toward energy independence.</p>
            </div>
        </div>
    </section>

    <?php include "includes/footer.php" ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init({ duration: 1000, once: true });</script>
</body>
</html>