<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/icon.png">
<title>SolarPower Energy Corporation - Smart Energy for Smarter Homes</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/style.css">
<body>

    <style>
        :root {
            --clr-primary: #ffc107; 
            --clr-secondary: #0a5c3d; 
            --clr-dark: #333;
            --clr-light: #fff;
            --clr-text-secondary: #666;
            --clr-bg-section: #f9f9f9;
            --border-radius-md: 8px;
            --shadow-box: 0 4px 15px rgba(0,0,0,0.08);
            --transition-fast: all 0.3s ease;
        }
        
        /* Hero Section */
        .hero-about {
            background: linear-gradient(to right, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.4)), 
                url('assets/img/contact.png') no-repeat center center/cover;
            height: 50vh;
            display: flex;
            align-items: center;
            color: white;
            text-align: center;
        }

        /* Contact Us Section */
.contact-us {
    padding: 80px 0;
    background: var(--clr-light-alt);
}

.contact-info h2 {
    font-size: 32px;
    margin-bottom: var(--spacing-xl);
    color: var(--clr-dark);
    font-weight: 700;
}

/* Visit Us Section */
.visit-us-section {
    background: #f8f9fa;
    padding: var(--spacing-lg);
    border-radius: var(--border-radius-md);
    margin-bottom: var(--spacing-xl);
}

.visit-us-section h3 {
    font-size: 20px;
    font-weight: 700;
    color: var(--clr-dark);
    margin-bottom: var(--spacing-md);
}

.visit-us-section p {
    color: var(--clr-text-secondary);
    line-height: 1.6;
    margin-bottom: var(--spacing-lg);
}

/* WhatsApp Button */
.whatsapp-btn {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-sm);
    background: #2a5b3c;
    color: white;
    padding: 12px 24px;
    border-radius: var(--border-radius-md);
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition-fast);
}

.whatsapp-btn:hover {
    background: #128c26;
    color: white;
    transform: translateY(-2px);
}

.whatsapp-btn i {
    font-size: 20px;
}

/* Company Info */
.company-info {
    margin-bottom: var(--spacing-xl);
}

.company-info strong {
    display: block;
    margin-bottom: var(--spacing-sm);
    color: var(--clr-dark);
    font-size: 18px;
}

.company-info p {
    color: var(--clr-text-secondary);
    line-height: 1.6;
    margin: 0 0 var(--spacing-sm) 0;
}

.company-info .phone-number {
    font-weight: 600;
    color: var(--clr-dark   );
}

/* Hours Section */
.hours-section {
    margin-top: var(--spacing-xl);
}

.hours-toggle {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    border: 1px solid #e0e0e0;
    padding: var(--spacing-md);
    border-radius: var(--border-radius-md);
    cursor: pointer;
    font-size: 16px;
    transition: var(--transition-fast);
}

.hours-toggle:hover {
    background: #f8f9fa;
}

.hours-toggle i {
    transition: transform 0.3s ease;
}

.hours-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.35s ease;
    margin-top: 10px;
}

.hour-item {
    display: flex;
    justify-content: space-between;
    padding: 6px 0;
    font-size: 14px;
    color: #333;
}

.hours-toggle {
    width: 100%;
    background: #fff;
    border: 1px solid #ccc;
    padding: 12px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    border-radius: 6px;
}


.contact-detail {
    display: flex;
    align-items: flex-start;
    margin-bottom: var(--spacing-lg);
    gap: var(--spacing-md);
}

.contact-detail i {
    color: var(--clr-primary);
    font-size: var(--spacing-lg);
    margin-top: 3px;
    min-width: var(--spacing-lg);
}

.contact-detail strong {
    display: block;
    margin-bottom: var(--spacing-xs);
    color: var(--clr-dark);
    font-size: 16px;
}

.contact-detail p,
.contact-detail span {
    color: var(--clr-text-secondary);
    line-height: 1.6;
    margin: 0;
}

.contact-form-wrapper {
    padding: 40px;
    border: 1px solid #d7d7d7;
}

.contact-form .form-control {
    padding: var(--spacing-md);
    border: 1px solid #e0e0e0;
    border-radius: var(--border-radius-md);
    font-size: var(--fs-base);
    transition: border-color 0.3s;
}

.contact-form .form-control:focus {
    border-color: var(--clr-primary);
    box-shadow: none;
}

.contact-form textarea.form-control {
    resize: vertical;
}

.btn-submit {
    background: var(--clr-secondary);
    color: var(--clr-light);
    padding: var(--spacing-md) 40px;
    border: none;
    border-radius: var(--border-radius-md);
    cursor: pointer;
    font-size: 16px;
    font-weight: 700;
    width: 100%;
    transition: var(--transition-fast);
    text-transform: uppercase;
}

.btn-submit:hover {
    background: #085231;
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 768px) {
    .visit-us-section {
        padding: var(--spacing-md);
    }
    
    .whatsapp-btn {
        width: 100%;
        justify-content: center;
    }
}

       
    </style>

    <?php include "includes/header.php" ?>

    <section class="hero-about">
        <div class="container" data-aos="fade-up">
            <span class="text-warning fw-bold text-uppercase">Contact Us</span>
            <h1 class="display-3 fw-bold">Speak with our solar specialists today.</h1>
        </div>
    </section>

    <!-- Contact Us Section -->
<section class="contact-us" id="contact-us">
    <div class="container">
        <div class="row">
            <!-- Left Side - Contact Information -->
            <div class="col-lg-5 mb-4 mb-lg-0">
                <div class="contact-info">
                    <h2>Contact Us</h2>
                    
                    <!-- Visit Us Section -->
                    <div class="visit-us-section">
                        <h3>Visit Us</h3>
                        <p>Come visit our showroom to see our solar products and speak with our experts in person.</p>
                        <a href="https://wa.me/639953947379" class="whatsapp-btn" target="_blank">
                            <i class="fab fa-whatsapp"></i>
                            Chat on WhatsApp
                        </a>
                    </div>
                    
                    <!-- Company Information -->
                    <div class="company-info">
                        <div class="contact-detail">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <strong>Address</strong>
                                <p>4/F PBB Corporate Center, 1906 Finance Drive, Madrigal Business Park 1, Ayala Alabang, Muntinlupa City, 1780, Philippines</p>
                            </div>
                        </div>
                        
                        <div class="contact-detail">
                            <i class="fas fa-phone"></i>
                            <div>
                                <strong>Phone</strong>
                                <span class="phone-number" id="phone-copy" onclick="copyToClipboard('0995-394-7379', this)">+63 995 394 7379</span>
                            </div>
                        </div>
                        
                        <div class="contact-detail">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <strong>Email</strong>
                                <a href="mailto:solar@solarpower.com.ph" class="contact-link">solar@solarpower.com.ph</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Business Hours -->
                    <div class="hours-section">
                        <button class="hours-toggle" onclick="toggleHours()">
                            <strong>Business Hours</strong>
                            <i class="fas fa-chevron-down" id="hours-icon"></i>
                        </button>
                        <div class="hours-content" id="hours-content">
                            <div class="hour-item">
                                <span>Monday</span>
                                <span>8:00 AM - 5:00 PM</span>
                            </div>
                            <div class="hour-item">
                                <span>Tuesday</span>
                                <span>8:00 AM - 5:00 PM</span>
                            </div>
                            <div class="hour-item">
                                <span>Wednesday</span>
                                <span>8:00 AM - 5:00 PM</span>
                            </div>
                            <div class="hour-item">
                                <span>Thursday</span>
                                <span>8:00 AM - 5:00 PM</span>
                            </div>
                            <div class="hour-item">
                                <span>Friday</span>
                                <span>8:00 AM - 5:00 PM</span>
                            </div>
                            <div class="hour-item">
                                <span>Saturday</span>
                                <span>8:00 AM - 5:00 PM</span>
                            </div>
                            <div class="hour-item">
                                <span>Sunday</span>
                                <span>Closed</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Contact Form -->
            <div class="col-lg-7">
                <div class="contact-form-wrapper">
                    <h3 class="mb-4">Send us a Message</h3>
                    <form class="contact-form" id="contactForm" onsubmit="submitContactForm(event)">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <input type="text" class="form-control" id="contact_name" name="name" placeholder="Full Name *" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <input type="email" class="form-control" id="contact_email" name="email" placeholder="Email Address *" required>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <input type="tel" class="form-control" id="contact_phone" name="phone" placeholder="Phone Number *" required>
                            </div>
                            
                            <div class="col-12 mb-4">
                                <textarea class="form-control" id="contact_message" name="message" rows="6" placeholder="Your Message *" required></textarea>
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" class="btn-submit" id="contactSubmitBtn">
                                    <span class="btn-text">Send Message</span>
                                    <span class="btn-spinner d-none">
                                        <i class="fas fa-spinner fa-spin"></i> Sending...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

    <section class="savings-calculator">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="calculator-box">
                        <div class="savings-icon">
                            <i class="fa-solid fa-solar-panel"></i>
                        </div>
                        <h2>Let's check how much you can save!</h2>
                        <p>what's your monthly electric bill?</p>

                        <div class="row justify-content-center mb-4">
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="input-group-custom">
                                    <input type="number" id="billAmount" placeholder="0" min="0" step="0.01">
                                    <p>Monthly Electric Bill (₱)</p>
                                </div>
                            </div>
                        </div>
                        
                        <button class="calculate-btn" onclick="calculateSavings()">Calculate</button>
                        <div id="errorMessage" class="error-message"></div>

                        <div id="results" class="results">
                            <div class="result-card">
                                <div class="result-value" id="kwpValue">0.0</div>
                                <div class="result-label">kWp</div>
                            </div>
                            <div class="result-card">
                                <div class="result-value" id="panelsValue">0</div>
                                <div class="result-label"># of panels</div>
                            </div>
                            <div class="result-card">
                                <div class="result-value" id="monthlySavings">0</div>
                                <div class="result-label">Monthly Savings</div>
                            </div>
                            <div class="result-card">
                                <div class="result-value" id="yearlySavings">0</div>
                                <div class="result-label">Yearly Savings</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!------ Book For Inspection ------>
    <section class="book-section py-5" id="inspection-section">
        <div class="container">
            <div class="row g-0 shadow-lg rounded-4 overflow-hidden">
                <div class="col-lg-5 d-none d-lg-block" style="background: linear-gradient(rgba(44, 62, 80, 0.9), rgba(44, 62, 80, 0.9)), url('../../assets/img/solar-install.jpg') center/cover;">
                    <div class="h-100 p-5 text-white d-flex flex-column justify-content-center">
                        <h2 class="display-6 fw-bold mb-4" style="color: #f39c12;">Ready to switch?</h2>
                        <p class="lead mb-4">Book a site inspection today and let our experts design the perfect solar system for your home.</p>
                        <ul class="list-unstyled">
                            <li class="mb-3"><i class="fas fa-check-circle me-2 text-warning"></i> Professional Assessment</li>
                            <li class="mb-3"><i class="fas fa-check-circle me-2 text-warning"></i> Accurate ROI Projection</li>
                            <li class="mb-3"><i class="fas fa-check-circle me-2 text-warning"></i> Custom System Design</li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-7 bg-white p-4 p-md-5">
                    <div class="form-header mb-4">
                        <h2 class="fw-bold">Book Site Inspection</h2>
                        <p class="text-muted small">Fill out the details below and we'll contact you within 24 hours.</p>
                    </div>

                    <form id="inspectionForm" class="inspection-form" onsubmit="submitInspection(event)">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-uppercase">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                                    <input type="text" name="fullname" class="form-control bg-light border-start-0" placeholder="Juan Dela Cruz" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-uppercase">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                                    <input type="email" name="email" class="form-control bg-light border-start-0" placeholder="juan@email.com" required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-uppercase">Contact Number</label>
                                <input type="tel" name="phone" class="form-control bg-light" placeholder="0917-000-0000" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-uppercase">Property Type</label>
                                <select name="property_type" class="form-select bg-light" required>
                                    <option value="" selected disabled>Select type</option>
                                    <option value="Residential">🏠 Residential</option>
                                    <option value="Commercial">🏢 Commercial</option>
                                </select>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label fw-semibold small text-uppercase">Complete Address</label>
                                <textarea name="address" class="form-control bg-light" rows="2" placeholder="House No., Street, Brgy, City" required></textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-uppercase">Inspection Date</label>
                                <input type="date" name="inspection_date" class="form-control bg-light" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-uppercase">Monthly Bill (₱)</label>
                                <input type="number" name="bill" class="form-control bg-light" placeholder="e.g. 5000" required>
                            </div>

                            <div class="col-12 mb-4">
                                <label class="form-label fw-semibold small text-uppercase">Additional Notes (Optional)</label>
                                <textarea name="notes" class="form-control bg-light" rows="3" placeholder="Tell us about your roof type or any specific concerns..."></textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn w-100 py-3 fw-bold text-uppercase shadow-sm" id="inspectionBtn" style="background: #f39c12; color: white; border: none;">
                            <span class="btn-text">Confirm My Schedule</span>
                            <span class="spinner-border spinner-border-sm d-none"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    
     <?php include "includes/footer.php" ?>


    <!-- Bootstrap JS Bundle -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });
    </script>

</body>
<script>
function submitInspection(e) {
    e.preventDefault();

    const form = e.target;
    const btn = document.getElementById('inspectionBtn');
    const text = btn.querySelector('.btn-text');
    const spinner = btn.querySelector('.spinner-border');

    btn.disabled = true;
    text.textContent = "Sending...";
    spinner.classList.remove('d-none');

    fetch("controllers/send-inspection-email.php", {
        method: "POST",
        body: new FormData(form)
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) form.reset();
    })
    .catch(() => {
        alert("Failed to send request. Please try again.");
    })
    .finally(() => {
        btn.disabled = false;
        text.textContent = "Submit Request";
        spinner.classList.add('d-none');
    });
}
</script>
<script src="assets/script.js"></script>
</html>