<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/icon.png">
    <title>About Us | SolarPower Energy Corporation</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">

    <style>
        :root {
            --clr-primary: #ffc107; 
            --clr-secondary: #0a5c3d; 
            --clr-dark: #333;
            --clr-dark-alt: #272727;
            --clr-light: #fff;
            --clr-light-alt: #f5f5f5;
            --clr-text-main: rgb(63, 63, 63); 
            --clr-bg-section: #f9f9f9;
            --ff-body: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            --fs-base: 14px;
            --shadow-box: 0 4px 15px rgba(0,0,0,0.08);
            --border-radius-md: 8px;
        }

        body {
            font-family: var(--ff-body);
            font-size: var(--fs-base);
            color: var(--clr-text-main);
            overflow-x: hidden;
        }

        /* Hero Section */
        .hero-about {
            background: linear-gradient(to right, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.4)), 
                url('assets/img/aboutus.png') no-repeat center center/cover;
            height: 50vh;
            min-height: 350px;
            color: var(--clr-light);
            display: flex;
            align-items: center;
        }

        /* Accreditation Section */
        .accreditation-section {
            padding: 80px 0;
            background: var(--clr-bg-section);
        }

        .cert-card {
            background: white;
            padding: 20px;
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-box);
            text-align: center;
            border: 1px solid #eee;
        }

        .cert-img {
            max-width: 100%;
            height: auto;
            margin-bottom: 20px;
            border-radius: 4px;
            transition: transform 0.3s ease;
        }

        .cert-img:hover {
            transform: scale(1.02);
        }

        .doe-badge {
            max-width: 120px;
            margin-bottom: 15px;
        }

        /* Standard Components */
        .role-card {
            background: var(--clr-light);
            padding: 40px 25px;
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-box);
            text-align: center;
            height: 100%;
            border-top: 5px solid var(--clr-primary);
        }

        .mission-box { background: var(--clr-primary); color: var(--clr-dark-alt); padding: 80px 40px; }
        .vision-box { background: var(--clr-secondary); color: var(--clr-light); padding: 80px 40px; }

        @media (max-width: 768px) {
            .hero-about { height: 40vh; text-align: center; }
        }
    </style>
</head>
<body>

    <?php include "includes/header.php" ?>

    <section class="hero-about">
        <div class="container" data-aos="fade-up">
            <div class="text-center">
                <span style="color: var(--clr-primary); font-weight: 700; text-transform: uppercase; letter-spacing: 2px;">WHO WE ARE</span>
                <h1 class="display-4 fw-bold text-white">Powering a Brighter, <br> Greener Future</h1>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white">
        <div class="container py-lg-5">
            <div class="row align-items-center">
                <div class="col-lg-5 text-center mb-5 mb-lg-0" data-aos="fade-right">
                    <img src="assets/img/logo_no_background.png" alt="Logo" class="img-fluid" style="max-width: 280px;">
                </div>
                <div class="col-lg-7" data-aos="fade-left">
                    <h2>SolarPower Energy Corporation</h2>
                    <p>The company is a renewable energy solutions provide committed to making clean, affordable, and sustainable solar power accessible to homes, businesses, and communities across Philippines. Established in 2025, our company specializes in the design, installation, and maintenance of solar photovoltaic (PV) systems tailored to meet diverse energy needs.</p>
                    <p>As a new and dynamic player in the solar industry, we bring fresh ideas, cutting-edge technology, and a deep commitment to environmental sustainability. Our mission is to accelerate the transition to renewable energy by offering efficient, cost-effective solar solutions that reduce electricity costs and carbon footprints.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5" style="background: var(--clr-bg-section);">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="100">
                    <div class="role-card">
                        <i class="fas fa-tags fa-3x mb-3 text-success"></i>
                        <h4 class="fw-bold">SELLER</h4>
                        <p class="text-muted">High-quality solar components at competitive prices.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
                    <div class="role-card">
                        <i class="fas fa-truck-loading fa-3x mb-3 text-success"></i>
                        <h4 class="fw-bold">DISTRIBUTOR</h4>
                        <p class="text-muted">Reliable supply chain delivering Tier-1 technology.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="300">
                    <div class="role-card">
                        <i class="fas fa-tools fa-3x mb-3 text-success"></i>
                        <h4 class="fw-bold">INSTALLER</h4>
                        <p class="text-muted">Expert engineering and certified technical deployment.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="accreditation-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                    <h2 class="fw-bold mb-3">Accredited & Certified</h2>
                    <p class="lead mb-4">We are proud to be a Department of Energy (DOE) recognized solar service provider.</p>
                    <div class="d-flex align-items-start mb-3">
                        <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                        <p>Registered Renewable Energy Developer</p>
                    </div>
                    <div class="d-flex align-items-start mb-3">
                        <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                        <p>Compliance with Philippine Grid Code standards</p>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="flip-left" data-aos-duration="1000">
                    <div class="cert-card">
                        <h5 class="fw-bold">Certificate of Accreditation</h5>
                        <img src="assets/img/doe-certificate.png" alt="DOE Certificate" class="cert-img shadow">
                        <p class="small text-muted mb-0">Authorized Solar PV Provider (Registration #250900095)</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container-fluid px-0">
        <div class="row g-0">
            <div class="col-md-6" data-aos="fade-up">
                <div class="mission-box text-center">
                    <i class="fas fa-bullseye fa-3x mb-4"></i>
                    <h3 class="fw-bold">Mission</h3>
                    <p class="fs-5">To promote sustainable living by providing reliable and cost-efficient solar energy solutions.</p>
                </div>
            </div>
            <div class="col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="vision-box text-center">
                    <i class="fas fa-eye fa-3x mb-4"></i>
                    <h3 class="fw-bold">Vision</h3>
                    <p class="fs-5">To become a leading solar energy provider nationwide, empowering every Filipino to save energy.</p>
                </div>
            </div>
        </div>
    </div>

    <?php include "includes/footer.php" ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800, // Animation speed
            once: true,    // Animation only happens once while scrolling down
            offset: 100    // Distance from the original trigger point
        });
    </script>
    <script src="assets/script.js"></script>
</body>
</html>