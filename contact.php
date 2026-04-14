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


/* ============================================
   BOOK SITE INSPECTION SECTION
   ============================================ */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(30px); }
    to   { opacity: 1; transform: translateY(0); }
}

.book-section .rounded-4 { border-radius: 20px !important; }

/* Left panel */
.inspection-left-panel {
    background-color: #0a5c3d !important;
    min-height: auto;
    max-width: 100%;
}
.inspection-left-panel .w-100 { padding: 36px 28px !important; }
.inspection-left-panel h2     { font-size: 1.4rem !important; }
.inspection-left-panel > .w-100 > p { font-size: .85rem; }

/* Badge */
.inspection-badge {
    display: inline-flex;
    align-items: center;
    background: rgba(243,156,18,.15);
    border: 1px solid rgba(243,156,18,.35);
    color: #f39c12;
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .07em;
    text-transform: uppercase;
    padding: 5px 12px;
    border-radius: 50px;
    width: fit-content;
}

/* Feature list */
.inspection-features {
    display: flex;
    flex-direction: column;
    gap: 0;
    padding-left: 0;
    margin-bottom: 0;
}
.inspection-features li {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid rgba(255,255,255,.08);
    list-style: none;
}
.inspection-features li:last-child { border-bottom: none; }
.inspection-features .feat-icon {
    width: 32px; height: 32px; min-width: 32px;
    border-radius: 8px;
    background: rgba(243,156,18,.15);
    border: 1px solid rgba(243,156,18,.25);
    display: flex; align-items: center; justify-content: center;
    color: #f39c12; font-size: .8rem;
}
.inspection-features li strong {
    display: block; color: #fff; font-size: .83rem; margin-bottom: 1px;
}
.inspection-features li small {
    color: rgba(255,255,255,.5); font-size: .73rem; line-height: 1.4;
}

/* Form labels */
.inspection-form .form-label {
    font-size: .71rem; font-weight: 700;
    letter-spacing: .06em; color: #555; margin-bottom: 6px;
    text-transform: uppercase;
}

/* Inputs & selects */
.inspection-form .form-control,
.inspection-form .form-select {
    background-color: #f8f9fa !important;
    border: 1.5px solid #e5e7eb !important;
    border-radius: 10px;
    font-size: .875rem; color: #333;
    padding: .6rem 1rem;
    transition: border-color .2s, box-shadow .2s, background-color .2s;
}
.inspection-form .form-control:focus,
.inspection-form .form-select:focus {
    background-color: #fff !important;
    border-color: #f39c12 !important;
    box-shadow: 0 0 0 3px rgba(243,156,18,.12) !important;
    outline: none;
}

/* Input groups */
.inspection-form .input-group-text {
    background-color: #f8f9fa !important;
    border: 1.5px solid #e5e7eb !important;
    border-right: none !important;
    border-radius: 10px 0 0 10px;
    color: #adb5bd; padding: 0 14px;
    transition: border-color .2s, color .2s, background-color .2s;
}
.inspection-form .input-group .form-control {
    border-left: none !important;
    border-radius: 0 10px 10px 0;
}
.inspection-form .input-group:focus-within .input-group-text {
    border-color: #f39c12 !important;
    background-color: #fff8ee !important;
    color: #f39c12;
}
.inspection-form .input-group:focus-within .form-control {
    border-color: #f39c12 !important;
}
.inspection-form textarea.form-control {
    border-radius: 10px; resize: vertical; min-height: 80px;
}
#roofOtherInput { margin-top: 8px; border-radius: 10px; }

/* Submit button */
#inspectionBtn,
#inspectionBtn.btn {
    display: block !important; width: 100% !important;
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%) !important;
    border: none !important; color: #fff !important;
    border-radius: 12px !important;
    font-size: .9rem; font-weight: 700 !important;
    letter-spacing: .05em; text-transform: uppercase;
    padding: 14px !important;
    box-shadow: 0 4px 18px rgba(243,156,18,.38);
    transition: transform .2s, box-shadow .2s;
    cursor: pointer !important;
}
#inspectionBtn:hover,
#inspectionBtn.btn:hover {
    background: linear-gradient(135deg, #e67e22 0%, #d35400 100%) !important;
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(243,156,18,.48);
    color: #fff !important;
}
#inspectionBtn:active { transform: translateY(0); }

/* Success modal OK button */
#successOkBtn,
#successOkBtn.btn {
    background: linear-gradient(135deg, #f39c12, #e67e22) !important;
    color: #fff !important; border-radius: 10px;
    border: none !important;
    box-shadow: 0 4px 14px rgba(243,156,18,.3);
}
#successOkBtn:hover {
    background: linear-gradient(135deg, #e67e22, #d35400) !important;
    color: #fff !important;
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

<!-- Savings Calculator -->
    <section class="savings-calculator">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="calculator-box collapsed" id="calculatorBox" data-aos="fade-up">
                        <div class="savings-icon">
                            <i class="fa-regular fa-lightbulb"></i>
                        </div>
                        <h2>Let's check how much you can save!</h2>
                        <p>What's your monthly electric bill?</p>
                        <div class="row justify-content-center mb-4">
                            <div class="col-lg-4 col-md-6">
                                <div class="input-group-custom">
                                    <input
                                        type="number"
                                        id="billAmount"
                                        placeholder="0"
                                        min="0"
                                        step="0.01"
                                        onfocus="expandCalculator()"
                                        onblur="shrinkCalculatorIfEmpty()">
                                    <p>Monthly Electric Bill (₱)</p>
                                </div>
                            </div>
                        </div>
                        <button class="calculate-btn" onclick="calculateSavings()">Calculate</button>
                        <div id="errorMessage" class="error-message"></div>
                        <div id="results" class="results">
                            <div class="result-card">
                                <div class="result-value" id="kwpValue">0.0</div>
                                <div class="result-label">Required System Size (kWp)</div>
                            </div>
                            <div class="result-card">
                                <div class="result-value" id="panelsValue">0</div>
                                <div class="result-label">Solar Panels</div>
                            </div>
                            <div class="result-card">
                                <div class="result-value" id="monthlySavings">0</div>
                                <div class="result-label">Monthly Savings (₱)</div>
                            </div>
                            <div class="result-card">
                                <div class="result-value" id="yearlySavings">0</div>
                                <div class="result-label">Yearly Savings (₱)</div>
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

                <!-- LEFT INFO PANEL -->
                <div class="col-lg-5 d-none d-lg-flex inspection-left-panel"
                     style="background-color:#0a5c3d; background-image: linear-gradient(160deg, rgba(20,40,20,.92) 0%, rgba(10,92,61,.85) 100%), url('assets/img/solar-install.jpg'); background-size:cover; background-position:center;">
                    <div class="w-100 p-5 text-white d-flex flex-column justify-content-center">

                        <div class="inspection-badge mb-3">
                            <i class="fas fa-solar-panel me-2"></i> Free Site Assessment
                        </div>

                        <h2 class="fw-bold mb-3">Ready to <span class="text-warning">Switch<br>to Solar?</span></h2>
                        <p class="mb-4 opacity-75">Book a site inspection and let our certified engineers design the perfect system for your home or business.</p>

                        <ul class="list-unstyled inspection-features">
                            <li class="mb-3">
                                <span class="feat-icon"><i class="fas fa-check-circle"></i></span>
                                <div><strong>Professional Assessment</strong><small>Certified engineers on-site</small></div>
                            </li>
                            <li class="mb-3">
                                <span class="feat-icon"><i class="fas fa-chart-line"></i></span>
                                <div><strong>Accurate ROI Projection</strong><small>Know your savings upfront</small></div>
                            </li>
                            <li class="mb-3">
                                <span class="feat-icon"><i class="fas fa-drafting-compass"></i></span>
                                <div><strong>Custom System Design</strong><small>Tailored to your property</small></div>
                            </li>
                        </ul>

                        <hr class="border-white opacity-10 my-4">
                        <p class="small opacity-50 mb-0">
                            <i class="fas fa-shield-alt me-1"></i>
                            Your information is secure and will never be shared.
                        </p>
                    </div>
                </div>

                <!-- FORM PANEL -->
                <div class="col-lg-7 bg-white p-4 p-md-5">
                    <div class="mb-4">
                        <h2 class="fw-bold">Book Site Inspection</h2>
                        <p class="text-muted small">We'll contact you within 24 hours.</p>
                    </div>

                    <form id="inspectionForm" class="inspection-form">
                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-uppercase">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" name="fullname" class="form-control" placeholder="Juan Dela Cruz" required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-uppercase">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" placeholder="juan@email.com" required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-uppercase">Contact Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="tel" name="phone" class="form-control" placeholder="0917-000-0000" required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-uppercase">Property Type</label>
                                <select name="property_type" class="form-select" required>
                                    <option value="" disabled selected>Select type</option>
                                    <option value="Residential">Residential</option>
                                    <option value="Commercial">Commercial</option>
                                </select>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label fw-semibold small text-uppercase">Complete Address</label>
                                <textarea name="address" class="form-control" rows="2" placeholder="House No., Street, Brgy, City" required></textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-uppercase">Inspection Date</label>
                                <input type="date" name="inspection_date" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-uppercase">Monthly Bill (₱)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" name="bill" class="form-control" placeholder="e.g. 5000" required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-uppercase">Roof Type</label>
                                <select name="roof_type" id="roofTypeSelect" class="form-select" required>
                                    <option value="" disabled selected>Select roof type</option>
                                    <option value="Concrete/Flat Roof">Concrete / Flat Roof</option>
                                    <option value="Corrugated Metal">Corrugated Metal</option>
                                    <option value="Tile (Clay/Concrete)">Tile (Clay / Concrete)</option>
                                    <option value="Asphalt Shingles">Asphalt Shingles</option>
                                    <option value="Other">Other (Please specify)</option>
                                </select>
                                <input type="text" name="roof_type_other" id="roofOtherInput"
                                    class="form-control mt-2 d-none"
                                    placeholder="Please describe your roof type">
                            </div>

                            <div class="col-12 mb-4">
                                <label class="form-label fw-semibold small text-uppercase">Additional Notes (Optional)</label>
                                <textarea name="notes" class="form-control" rows="3"
                                    placeholder="Tell us about your roof type or any specific concerns..."></textarea>
                            </div>

                        </div>

                        <button type="submit" class="btn w-100" id="inspectionBtn">
                            <span class="btn-text"><i class="fas fa-calendar-check me-2"></i>Confirm My Schedule</span>
                            <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </section>

    <!-- INSPECTION SUCCESS MODAL -->
    <div class="modal fade" id="inspectionSuccessModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 overflow-hidden text-center">
                <div style="height:5px; background: linear-gradient(90deg,#f39c12,#e67e22);"></div>
                <div class="modal-body py-5 px-4">
                    <i class="fas fa-solar-panel text-warning mb-3" style="font-size:56px;"></i>
                    <h4 class="fw-bold mb-2">Request Submitted!</h4>
                    <p class="text-muted mb-0">
                        Your inspection request has been received.<br>
                        <strong class="text-dark">Our team will contact you within 24 hours.</strong>
                    </p>
                </div>
                <div class="modal-footer border-0 justify-content-center pb-4">
                    <button type="button" class="btn fw-bold px-5 py-2" id="successOkBtn" data-bs-dismiss="modal">
                        Got it, thanks!
                    </button>
                </div>
            </div>
        </div>
    </div>

    
     <?php include "includes/footer.php" ?>


    <!-- Bootstrap JS Bundle -->
    <script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script><script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
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
function setupCalculator() {
            const billInput = document.getElementById('billAmount');
            
            if (billInput) {
                billInput.addEventListener('keypress', function(event) {
                    if (event.key === 'Enter') {
                        calculateSavings();
                    }
                });
            }
        }

        function expandCalculator() {
            const calculatorBox = document.getElementById('calculatorBox');
            if (calculatorBox) {
                calculatorBox.classList.remove('collapsed');
                calculatorBox.classList.add('expanded');
            }
        }

        function shrinkCalculatorIfEmpty() {
            const billInput = document.getElementById('billAmount');
            const calculatorBox = document.getElementById('calculatorBox');
            const results = document.getElementById('results');
            
            if (calculatorBox && billInput && !billInput.value && !results.classList.contains('show')) {
                setTimeout(() => {
                    calculatorBox.classList.remove('expanded');
                    calculatorBox.classList.add('collapsed');
                }, 200);
            }
        }

        function calculateSavings() {
            const billAmount = parseFloat(document.getElementById('billAmount').value);
            const errorMessage = document.getElementById('errorMessage');
            const results = document.getElementById('results');
            const calculatorBox = document.getElementById('calculatorBox');
            
            if (!billAmount || billAmount <= 0) {
                errorMessage.textContent = 'Please enter a valid electric bill amount';
                results.classList.remove('show');
                return;
            }
            
            errorMessage.textContent = '';
            
            if (calculatorBox) {
                calculatorBox.classList.remove('collapsed');
                calculatorBox.classList.add('expanded');
            }
            
            const avgRate = 13.40;
            const monthlyConsumption = billAmount / avgRate;
            const dailyConsumption = monthlyConsumption / 30;
            const sunHours = 4.5;
            const systemEfficiency = 0.85;
            const panelWattage = 705;
            const savingsPercentage = 0.95;
            
            const requiredKwp = dailyConsumption / (sunHours * systemEfficiency);
            const numberOfPanels = Math.ceil((requiredKwp * 1000) / panelWattage);
            const monthlySavings = billAmount * savingsPercentage;
            const yearlySavings = monthlySavings * 12;
            
            setTimeout(() => {
                document.getElementById('kwpValue').textContent = requiredKwp.toFixed(1);
                document.getElementById('panelsValue').textContent = numberOfPanels;
                document.getElementById('monthlySavings').textContent = '₱' + monthlySavings.toLocaleString('en-PH', {maximumFractionDigits: 0});
                document.getElementById('yearlySavings').textContent = '₱' + yearlySavings.toLocaleString('en-PH', {maximumFractionDigits: 0});
                
                results.classList.add('show');
            }, 100);
        }

        document.addEventListener('DOMContentLoaded', function() {
            setupCalculator();
            
            const calculatorBox = document.getElementById('calculatorBox');
            if (calculatorBox) {
                calculatorBox.classList.add('collapsed');
            }
            
            // Add click handler for bulb icon with wiggle animation
            const bulbIcon = document.querySelector('.savings-icon');
            if (bulbIcon) {
                bulbIcon.addEventListener('click', function() {
                    // Trigger wiggle animation
                    this.style.animation = 'none';
                    setTimeout(() => {
                        this.style.animation = '';
                    }, 10);
                    
                    // Expand calculator if collapsed
                    const billInput = document.getElementById('billAmount');
                    if (calculatorBox && calculatorBox.classList.contains('collapsed')) {
                        expandCalculator();
                        if (billInput) {
                            setTimeout(() => billInput.focus(), 300);
                        }
                    }
                });
            }
        });
        function setupCalculator() {
            const billInput = document.getElementById('billAmount');
            
            if (billInput) {
                billInput.addEventListener('keypress', function(event) {
                    if (event.key === 'Enter') {
                        calculateSavings();
                    }
                });
            }
        }

        function expandCalculator() {
            const calculatorBox = document.getElementById('calculatorBox');
            if (calculatorBox) {
                calculatorBox.classList.remove('collapsed');
                calculatorBox.classList.add('expanded');
            }
        }

        function shrinkCalculatorIfEmpty() {
            const billInput = document.getElementById('billAmount');
            const calculatorBox = document.getElementById('calculatorBox');
            const results = document.getElementById('results');
            
            if (calculatorBox && billInput && !billInput.value && !results.classList.contains('show')) {
                setTimeout(() => {
                    calculatorBox.classList.remove('expanded');
                    calculatorBox.classList.add('collapsed');
                }, 200);
            }
        }

        function calculateSavings() {
            const billAmount = parseFloat(document.getElementById('billAmount').value);
            const errorMessage = document.getElementById('errorMessage');
            const results = document.getElementById('results');
            const calculatorBox = document.getElementById('calculatorBox');
            
            if (!billAmount || billAmount <= 0) {
                errorMessage.textContent = 'Please enter a valid electric bill amount';
                results.classList.remove('show');
                return;
            }
            
            errorMessage.textContent = '';
            
            if (calculatorBox) {
                calculatorBox.classList.remove('collapsed');
                calculatorBox.classList.add('expanded');
            }
            
            const avgRate = 13.40;
            const monthlyConsumption = billAmount / avgRate;
            const dailyConsumption = monthlyConsumption / 30;
            const sunHours = 4.5;
            const systemEfficiency = 0.85;
            const panelWattage = 705;
            const savingsPercentage = 0.95;
            
            const requiredKwp = dailyConsumption / (sunHours * systemEfficiency);
            const numberOfPanels = Math.ceil((requiredKwp * 1000) / panelWattage);
            const monthlySavings = billAmount * savingsPercentage;
            const yearlySavings = monthlySavings * 12;
            
            setTimeout(() => {
                document.getElementById('kwpValue').textContent = requiredKwp.toFixed(1);
                document.getElementById('panelsValue').textContent = numberOfPanels;
                document.getElementById('monthlySavings').textContent = '₱' + monthlySavings.toLocaleString('en-PH', {maximumFractionDigits: 0});
                document.getElementById('yearlySavings').textContent = '₱' + yearlySavings.toLocaleString('en-PH', {maximumFractionDigits: 0});
                
                results.classList.add('show');
            }, 100);
        }

        document.addEventListener('DOMContentLoaded', function() {
            setupCalculator();
            
            const calculatorBox = document.getElementById('calculatorBox');
            if (calculatorBox) {
                calculatorBox.classList.add('collapsed');
            }
        });
function submitContactForm(e) {
    e.preventDefault();

    const form = e.target;
    const btn  = document.getElementById('contactSubmitBtn');
    const text = btn.querySelector('.btn-text');
    const spinner = btn.querySelector('.btn-spinner');

    // Loading state
    btn.disabled = true;
    text.classList.add('d-none');
    spinner.classList.remove('d-none');

    fetch("controllers/contact_submit.php", {
        method: "POST",
        body: new FormData(form)
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        text.classList.remove('d-none');
        spinner.classList.add('d-none');

        if (data.success) {
            // Success feedback
            form.reset();
            btn.style.background = '#27ae60';
            text.textContent = '✓ Message Sent!';
            setTimeout(() => {
                btn.style.background = '';
                text.textContent = 'Send Message';
            }, 3000);
        } else {
            alert(data.message || 'Something went wrong. Please try again.');
        }
    })
    .catch(() => {
        btn.disabled = false;
        text.classList.remove('d-none');
        spinner.classList.add('d-none');
        alert('Connection error. Please try again.');
    });
}

/* ── Roof type "Other" reveal ── */
document.addEventListener('DOMContentLoaded', function () {
    const roofSelect = document.getElementById('roofTypeSelect');
    if (roofSelect) {
        roofSelect.addEventListener('change', function () {
            const other = document.getElementById('roofOtherInput');
            if (this.value === 'Other') {
                other.classList.remove('d-none');
                other.setAttribute('required', 'required');
            } else {
                other.classList.add('d-none');
                other.removeAttribute('required');
                other.value = '';
            }
        });
    }
});

/* ── Inspection form submit ── */
function initializeInspectionForm() {
    const inspectionForm = document.getElementById('inspectionForm');
    if (!inspectionForm) return;

    inspectionForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const submitBtn = document.getElementById('inspectionBtn');
        const btnText   = submitBtn.querySelector('.btn-text');
        const spinner   = submitBtn.querySelector('.spinner-border');

        btnText.classList.add('d-none');
        spinner.classList.remove('d-none');
        submitBtn.disabled = true;

        try {
            const formData   = new FormData(inspectionForm);
            const phpResponse = await fetch('controllers/send-inspection-email.php', {
                method: 'POST',
                body: formData
            });
            const phpResult = await phpResponse.json();

            if (phpResult.success) {
                showSuccessAndReset();
                return;
            } else {
                throw new Error('PHP handler failed');
            }

        } catch (phpError) {
            try {
                const formData2 = new FormData(inspectionForm);
                formData2.append('_subject',  '🌞 New Solar Inspection Request');
                formData2.append('_captcha',  'false');
                formData2.append('_template', 'box');

                const fsResponse = await fetch('https://formsubmit.co/solar@solarpower.com.ph', {
                    method: 'POST',
                    body: formData2,
                    headers: { 'Accept': 'application/json' }
                });

                if (fsResponse.ok) {
                    showSuccessAndReset();
                } else {
                    throw new Error('FormSubmit also failed');
                }

            } catch (fsError) {
                const btnText2 = submitBtn.querySelector('.btn-text');
                const spinner2 = submitBtn.querySelector('.spinner-border');
                btnText2.classList.remove('d-none');
                spinner2.classList.add('d-none');
                submitBtn.disabled = false;
                alert('There was an error submitting your request. Please try again or contact us directly.');
            }
        }
    });
}

function showSuccessAndReset() {
    const inspectionForm = document.getElementById('inspectionForm');
    const submitBtn      = document.getElementById('inspectionBtn');
    const btnText        = submitBtn.querySelector('.btn-text');
    const spinner        = submitBtn.querySelector('.spinner-border');

    btnText.classList.remove('d-none');
    spinner.classList.add('d-none');
    submitBtn.disabled = false;

    inspectionForm.reset();

    const successModal = new bootstrap.Modal(document.getElementById('inspectionSuccessModal'));
    successModal.show();
}

document.addEventListener('DOMContentLoaded', function () {
    initializeInspectionForm();
});
</script>

</body>
</html>