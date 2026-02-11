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

    <!--Recent Installation Section-->
    <section class="py-5">
        <div class="container">
            <div class="row mb-5 align-items-end">
                <div class="col-md-8" data-aos="fade-right">
                    <h2 class="fw-bold">Recent Installations</h2>
                    <p class="text-muted">Proven reliability across residential and commercial sectors.</p>
                </div>
            </div>

            <div class="row g-4" id="projectsContainer">
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
                                <li><strong>Monthly bill before:</strong> ₱18,000</li>
                                <li><strong>Monthly bill after:</strong> ₱1,500</li>
                                <li><strong>Results:</strong> 92% savings</li>
                            </ul>
                            <div>
                                <h4>Testimonial</h4>
                                <ul>
                                    <li style="margin-top: 0">"Our electric bill dropped dramatically after installation"</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

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
                            </ul>
                            <div>
                                <h4>Testimonial</h4>
                                <ul>
                                    <li style="margin-top: 0">"The financing options made it easy to get started"</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

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
                            <div>
                                <h4>Testimonial</h4>
                                <ul>
                                    <li style="margin-top: 0">"Professional installation and excellent ROI"</li>
                                </ul>
                            </div>
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
                            <div>
                                <h4>Testimonial</h4>
                                <ul>
                                    <li style="margin-top: 0">"Best investment for our business operations"</li>
                                </ul>
                            </div>
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
                            <div>
                                <h4>Testimonial</h4>
                                <ul>
                                    <li style="margin-top: 0">"Seamless installation and great support team"</li>
                                </ul>
                            </div>
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
                            </ul>
                            <div>
                                <h4>Testimonial</h4>
                                <ul>
                                    <li style="margin-top: 0">"Eco-friendly solution with amazing cost savings"</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
    </script>
</body>

</html>