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
            --shadow-box: 0 4px 15px rgba(0, 0, 0, 0.08);
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
            color: var(--clr-dark);
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
   BOOK SITE INSPECTION SECTION  (redesigned)
   ============================================ */

        /* --- Section wrapper --- */
        .book-section {
            background: var(--clr-bg-section);
            padding: 80px 0 0;
        }

        /* --- Eyebrow label ("— Contact Us") --- */
        .book-eyebrow {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--clr-secondary);
            margin-bottom: 10px;
        }

        .book-eyebrow::before {
            content: '';
            display: inline-block;
            width: 28px;
            height: 2px;
            background: var(--clr-secondary);
            border-radius: 2px;
        }

        /* --- Page heading --- */
        .book-heading {
            font-size: clamp(1.6rem, 3.5vw, 2.4rem);
            font-weight: 800;
            color: var(--clr-dark);
            line-height: 1.2;
            margin-bottom: 28px;
        }

        /* --- Two-column card --- */
        .book-card {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 40px rgba(0, 0, 0, .10);
            background: #fff;
        }

        /* --- Left: Form panel --- */
        .book-form-panel {
            padding: 40px 40px 36px;
            background: #fff;
        }

        /* --- Right: Info card --- */
        .book-info-panel {
            background: var(--clr-secondary);
            padding: 36px 28px;
            display: flex;
            flex-direction: column;
            gap: 28px;
            color: #fff;
        }

        /* Info group */
        .book-info-group h4 {
            font-size: .82rem;
            font-weight: 800;
            letter-spacing: .07em;
            text-transform: uppercase;
            color: var(--clr-primary);
            margin-bottom: 10px;
        }

        .book-info-group p,
        .book-info-group a {
            font-size: .83rem;
            color: rgba(255, 255, 255, .82);
            margin: 0;
            line-height: 1.65;
            text-decoration: none;
        }

        .book-info-group a:hover {
            color: var(--clr-primary);
        }

        /* Divider inside info panel */
        .book-info-divider {
            border: none;
            border-top: 1px solid rgba(255, 255, 255, .13);
            margin: 0;
        }

        /* Social icons row */
        .book-socials {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 6px;
        }

        .book-social-btn {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--clr-primary);
            color: var(--clr-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .88rem;
            text-decoration: none;
            transition: transform .25s, box-shadow .25s;
            box-shadow: 0 2px 8px rgba(255, 193, 7, .25);
        }

        .book-social-btn:hover {
            transform: translateY(-3px) scale(1.08);
            box-shadow: 0 6px 18px rgba(255, 193, 7, .45);
            color: var(--clr-dark);
        }

        /* --- Form internals --- */
        /* Form labels */
        .inspection-form .form-label {
            font-size: .71rem;
            font-weight: 700;
            letter-spacing: .06em;
            color: #777;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        /* Inputs & selects */
        .inspection-form .form-control,
        .inspection-form .form-select {
            background-color: #f8f9fa !important;
            border: 1.5px solid #e5e7eb !important;
            border-radius: 10px;
            font-size: .875rem;
            color: #333;
            padding: .6rem 1rem;
            transition: border-color .2s, box-shadow .2s, background-color .2s;
        }

        .inspection-form .form-control:focus,
        .inspection-form .form-select:focus {
            background-color: #fff !important;
            border-color: var(--clr-primary) !important;
            box-shadow: 0 0 0 3px rgba(255, 193, 7, .13) !important;
            outline: none;
        }

        /* Input groups */
        .inspection-form .input-group-text {
            background-color: #f8f9fa !important;
            border: 1.5px solid #e5e7eb !important;
            border-right: none !important;
            border-radius: 10px 0 0 10px;
            color: #adb5bd;
            padding: 0 14px;
            transition: border-color .2s, color .2s, background-color .2s;
        }

        .inspection-form .input-group .form-control {
            border-left: none !important;
            border-radius: 0 10px 10px 0;
        }

        .inspection-form .input-group:focus-within .input-group-text {
            border-color: var(--clr-primary) !important;
            background-color: #fffbea !important;
            color: var(--clr-primary);
        }

        .inspection-form .input-group:focus-within .form-control {
            border-color: var(--clr-primary) !important;
        }

        .inspection-form textarea.form-control {
            border-radius: 10px;
            resize: vertical;
            min-height: 80px;
        }

        #roofOtherInput {
            margin-top: 8px;
            border-radius: 10px;
        }

        /* Submit button */
        #inspectionBtn,
        #inspectionBtn.btn {
            display: block !important;
            width: auto !important;
            background: var(--clr-primary) !important;
            border: none !important;
            color: var(--clr-dark) !important;
            border-radius: 50px !important;
            font-size: .88rem;
            font-weight: 700 !important;
            letter-spacing: .05em;
            text-transform: uppercase;
            padding: 13px 32px !important;
            box-shadow: var(--shadow-btn-primary);
            transition: transform .2s, box-shadow .2s;
            cursor: pointer !important;
        }

        #inspectionBtn:hover,
        #inspectionBtn.btn:hover {
            background: #e6ac00 !important;
            transform: translateY(-2px);
            box-shadow: var(--shadow-btn-primary-hover);
            color: var(--clr-dark) !important;
        }

        #inspectionBtn:active {
            transform: translateY(0);
        }

        /* Success modal OK button */
        #successOkBtn,
        #successOkBtn.btn {
            background: linear-gradient(135deg, #f39c12, #e67e22) !important;
            color: #fff !important;
            border-radius: 10px;
            border: none !important;
            box-shadow: 0 4px 14px rgba(243, 156, 18, .3);
        }

        #successOkBtn:hover {
            background: linear-gradient(135deg, #e67e22, #d35400) !important;
            color: #fff !important;
        }

        /* --- Map strip below the card --- */
        .book-map {
            margin-top: 32px;
            border-radius: 0 0 0 0;
            overflow: hidden;
            width: 100%;
            line-height: 0;
        }

        .book-map iframe {
            width: 100%;
            height: 360px;
            border: none;
            display: block;
            filter: saturate(.85);
            transition: filter .3s;
        }

        .book-map iframe:hover {
            filter: saturate(1);
        }

        /* --- Responsive --- */
        @media (max-width: 991px) {
            .book-card {
                grid-template-columns: 1fr;
            }

            .book-info-panel {
                flex-direction: row;
                flex-wrap: wrap;
                gap: 20px;
                padding: 26px 28px;
            }

            .book-info-group {
                flex: 1 1 180px;
            }

            .book-info-divider {
                display: none;
            }
        }

        @media (max-width: 576px) {
            .book-form-panel {
                padding: 24px 18px;
            }

            .book-info-panel {
                padding: 22px 18px;
            }

            .book-map iframe {
                height: 260px;
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
                            <p>Come visit our showroom to see our solar products and speak with our experts in person.
                            </p>
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
                                    <p>4/F PBB Corporate Center, 1906 Finance Drive, Madrigal Business Park 1, Ayala
                                        Alabang, Muntinlupa City, 1780, Philippines</p>
                                </div>
                            </div>

                            <div class="contact-detail">
                                <i class="fas fa-phone"></i>
                                <div>
                                    <strong>Phone</strong>
                                    <span class="phone-number" id="phone-copy"
                                        onclick="copyToClipboard('0995-394-7379', this)">+63 995 394 7379</span>
                                </div>
                            </div>

                            <div class="contact-detail">
                                <i class="fas fa-envelope"></i>
                                <div>
                                    <strong>Email</strong>
                                    <a href="mailto:solar@solarpower.com.ph"
                                        class="contact-link">solar@solarpower.com.ph</a>
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
                                    <input type="text" class="form-control" id="contact_name" name="name"
                                        placeholder="Full Name *" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <input type="email" class="form-control" id="contact_email" name="email"
                                        placeholder="Email Address *" required>
                                </div>

                                <div class="col-12 mb-3">
                                    <input type="tel" class="form-control" id="contact_phone" name="phone"
                                        placeholder="Phone Number *" required>
                                </div>

                                <div class="col-12 mb-4">
                                    <textarea class="form-control" id="contact_message" name="message" rows="6"
                                        placeholder="Your Message *" required></textarea>
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
                                    <input type="number" id="billAmount" placeholder="0" min="0" step="0.01"
                                        onfocus="expandCalculator()" onblur="shrinkCalculatorIfEmpty()">
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

    <!------ Book For Inspection (redesigned) ------>
    <section class="book-section" id="inspection-section">
        <div class="container">

            <!-- Eyebrow + Heading -->
            <div class="book-eyebrow" data-aos="fade-up">Contact Us</div>
            <h2 class="book-heading" data-aos="fade-up" data-aos-delay="60">Get Your Free Quote Today!</h2>

            <!-- Two-column card: form left | info right -->
            <div class="book-card" data-aos="fade-up" data-aos-delay="120">

                <!-- LEFT: Form Panel -->
                <div class="book-form-panel">
                    <form id="inspectionForm" class="inspection-form">
                        <div class="row g-3">

                            <div class="col-sm-6">
                                <label class="form-label">Your Name *</label>
                                <input type="text" name="fullname" class="form-control" placeholder="Ex. John Doe"
                                    required>
                            </div>

                            <div class="col-sm-6">
                                <label class="form-label">Email *</label>
                                <div class="input-group">
                                    <input type="email" name="email" class="form-control"
                                        placeholder="example@gmail.com" required>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <label class="form-label">Phone *</label>
                                <div class="input-group">
                                    <input type="tel" name="phone" class="form-control" placeholder="Enter Phone Number"
                                        required>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <label class="form-label">Service *</label>
                                <select name="property_type" class="form-select" required>
                                    <option value="" disabled selected>Select Services</option>
                                    <option value="Residential">Residential Solar</option>
                                    <option value="Commercial">Commercial Solar</option>
                                    <option value="Site Inspection">Free Site Inspection</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Inspection Date *</label>
                                <input type="date" name="inspection_date" class="form-control" required>
                            </div>

                            <div class="col-sm-6">
                                <label class="form-label">Monthly Bill (₱)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" name="bill" class="form-control" placeholder="e.g. 5000">
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <label class="form-label">Roof Type</label>
                                <select name="roof_type" id="roofTypeSelect" class="form-select">
                                    <option value="" disabled selected>Select roof type</option>
                                    <option value="Concrete/Flat Roof">Concrete / Flat Roof</option>
                                    <option value="Corrugated Metal">Corrugated Metal</option>
                                    <option value="Tile (Clay/Concrete)">Tile (Clay / Concrete)</option>
                                    <option value="Asphalt Shingles">Asphalt Shingles</option>
                                    <option value="Other">Other (Please specify)</option>
                                </select>
                                <input type="text" name="roof_type_other" id="roofOtherInput"
                                    class="form-control mt-2 d-none" placeholder="Please describe your roof type">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Your Message *</label>
                                <textarea name="notes" class="form-control" rows="4"
                                    placeholder="Enter here..."></textarea>
                            </div>

                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn" id="inspectionBtn">
                                <span class="btn-text"><i class="fas fa-calendar-check me-2"></i>Book a Services</span>
                                <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- RIGHT: Info Panel -->
                <div class="book-info-panel">

                    <div class="book-info-group">
                        <h4>Address</h4>
                        <p>Unit 102, Ground Floor, Corporate Center,<br>Madrigal Business Park,<br>1906 Finance St,
                            Muntinlupa, 1770<br>Metro Manila</p>
                    </div>

                    <hr class="book-info-divider">

                    <div class="book-info-group">
                        <h4>Contact</h4>
                        <p>Phone : <a href="tel:+639953947379">+63 995 394 7379</a></p>
                        <p>Email : <a href="mailto:solar@solarpower.com.ph">solar@solarpower.com.ph</a></p>
                    </div>

                    <hr class="book-info-divider">

                    <div class="book-info-group">
                        <h4>Open Time</h4>
                        <p>Monday – Friday : 8:00 AM – 5:00 PM</p>
                        <p>Saturday – Sunday : 8:00 AM – 12:00 PM</p>
                    </div>

                    <hr class="book-info-divider">

                    <div class="book-info-group">
                        <h4>Stay Connected</h4>
                        <div class="book-socials">
                            <a href="#" class="book-social-btn" aria-label="Facebook"><i
                                    class="fab fa-facebook-f"></i></a>
                            <a href="#" class="book-social-btn" aria-label="Twitter/X"><i
                                    class="fab fa-x-twitter"></i></a>
                            <a href="#" class="book-social-btn" aria-label="Pinterest"><i
                                    class="fab fa-pinterest-p"></i></a>
                            <a href="#" class="book-social-btn" aria-label="Instagram"><i
                                    class="fab fa-instagram"></i></a>
                            <a href="https://wa.me/639953947379" class="book-social-btn" aria-label="WhatsApp"
                                target="_blank"><i class="fab fa-whatsapp"></i></a>
                        </div>
                    </div>

                </div>
                <!-- /Right Info Panel -->

            </div>
            <!-- /Two-column card -->

            <!-- Google Maps embed -->
            <div class="book-map">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3862.4553838016!2d121.0208!3d14.4199!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397d13a4f3a3c9b%3A0x1b46c9bcf0b37b9!2sMadrigal+Business+Park!5e0!3m2!1sen!2sph!4v1713172000000"
                    allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                    title="SolarPower Energy Corporation — Madrigal Business Park location">
                </iframe>
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
    <script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
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
    function setupCalculator() {
        const billInput = document.getElementById('billAmount');

        if (billInput) {
            billInput.addEventListener('keypress', function (event) {
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
            document.getElementById('monthlySavings').textContent = '₱' + monthlySavings.toLocaleString('en-PH', { maximumFractionDigits: 0 });
            document.getElementById('yearlySavings').textContent = '₱' + yearlySavings.toLocaleString('en-PH', { maximumFractionDigits: 0 });

            results.classList.add('show');
        }, 100);
    }

    document.addEventListener('DOMContentLoaded', function () {
        setupCalculator();

        const calculatorBox = document.getElementById('calculatorBox');
        if (calculatorBox) {
            calculatorBox.classList.add('collapsed');
        }

        // Add click handler for bulb icon with wiggle animation
        const bulbIcon = document.querySelector('.savings-icon');
        if (bulbIcon) {
            bulbIcon.addEventListener('click', function () {
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
            billInput.addEventListener('keypress', function (event) {
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
            document.getElementById('monthlySavings').textContent = '₱' + monthlySavings.toLocaleString('en-PH', { maximumFractionDigits: 0 });
            document.getElementById('yearlySavings').textContent = '₱' + yearlySavings.toLocaleString('en-PH', { maximumFractionDigits: 0 });

            results.classList.add('show');
        }, 100);
    }

    document.addEventListener('DOMContentLoaded', function () {
        setupCalculator();

        const calculatorBox = document.getElementById('calculatorBox');
        if (calculatorBox) {
            calculatorBox.classList.add('collapsed');
        }
    });
    function submitContactForm(e) {
        e.preventDefault();

        const form = e.target;
        const btn = document.getElementById('contactSubmitBtn');
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
            const btnText = submitBtn.querySelector('.btn-text');
            const spinner = submitBtn.querySelector('.spinner-border');

            btnText.classList.add('d-none');
            spinner.classList.remove('d-none');
            submitBtn.disabled = true;

            try {
                const formData = new FormData(inspectionForm);
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
                    formData2.append('_subject', '🌞 New Solar Inspection Request');
                    formData2.append('_captcha', 'false');
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
        const submitBtn = document.getElementById('inspectionBtn');
        const btnText = submitBtn.querySelector('.btn-text');
        const spinner = submitBtn.querySelector('.spinner-border');

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