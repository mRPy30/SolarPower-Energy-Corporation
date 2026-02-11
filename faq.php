<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/icon.png">
    <title>SolarPower Energy - Smart Energy for Smarter Homes</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <link rel="stylesheet" href="assets/style.css">

<body>

    <style>
        /* Hero Section */
        .faq-hero {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('assets/img/demo-faq.jpeg') no-repeat center/cover;
            height: 50vh;
            display: flex;
            align-items: center;
            color: white;
            text-align: center;
        }

        .faqhero-subtitle {
            color: #ffc107;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 15px;
        }

        .faq-hero h1 {
            font-size: 3.5rem;
            font-weight: bold;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .faq-hero p {
            font-size: 1.2rem;
            opacity: 0.95;
            max-width: 800px;
            margin: 0 auto;
        }

        /* Hero Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .faq-hero .faqhero-subtitle {
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }

        .faq-hero h1 {
            animation: fadeInUp 0.8s ease-out 0.4s both;
        }

        .faq-hero p {
            animation: fadeInUp 0.8s ease-out 0.6s both;
        }

        /* Benefits Cards */
        .benefits {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 50px;
            margin: 48px 12px;
            padding: 48px 0;
        }

        .benefit-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            opacity: 0;
            transform: translateY(30px);
        }

        .benefit-card.animate {
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .benefit-card:nth-child(1).animate {
            animation-delay: 0.1s;
        }

        .benefit-card:nth-child(2).animate {
            animation-delay: 0.3s;
        }

        .benefit-card:nth-child(3).animate {
            animation-delay: 0.5s;
        }

        .benefit-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(45, 80, 22, 0.2);
        }

        .benefit-card .icon {
            font-size: 3em;
            margin-bottom: 15px;
        }

        .benefit-card h4 {
            color: #2d5016;
            margin-bottom: 10px;
            font-size: 1.2em;
        }

        .benefit-card p {
            color: #666;
            font-size: 0.95em;
            line-height: 1.6;
        }

        /* FAQ Section */
        .faq-section {
            background: white;
            padding: 48px 40px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            text-align: center;
            margin-bottom: 40px;
            opacity: 0;
            transform: translateY(30px);
        }

        .section-title.animate {
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .section-title h2 {
            color: #2d5016;
            font-size: 2em;
            margin-bottom: 10px;
        }

        .section-title p {
            color: #666;
            font-size: 1.1em;
        }

        .faq-item {
            margin-bottom: 20px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(30px);
        }

        .faq-item.animate {
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .faq-item:nth-child(2).animate {
            animation-delay: 0.1s;
        }

        .faq-item:nth-child(3).animate {
            animation-delay: 0.2s;
        }

        .faq-item:nth-child(4).animate {
            animation-delay: 0.3s;
        }

        .faq-item:nth-child(5).animate {
            animation-delay: 0.4s;
        }

        .faq-item:nth-child(6).animate {
            animation-delay: 0.5s;
        }

        .faq-item:hover {
            border-color: #ffc107;
        }

        .faq-question {
            background: #f8f9fa;
            padding: 20px 25px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.3s ease;
        }

        .faq-question:hover {
            background: #e9ecef;
        }

        .faq-question h3 {
            color: #2d5016;
            font-size: 1.15em;
            font-weight: 600;
            flex: 1;
        }

        .faq-icon {
            width: 35px;
            height: 35px;
            background: #2d5016;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5em;
            transition: all 0.3s ease;
        }

        .faq-item.active .faq-icon {
            background: #ffc107;
            color: #2d5016;
            transform: rotate(45deg);
        }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease, padding 0.4s ease;
            padding: 0 25px;
            background: white;
        }

        .faq-item.active .faq-answer {
            max-height: 600px;
            padding: 25px;
            border-top: 2px solid #f8f9fa;
        }

        .faq-answer p {
            color: #555;
            line-height: 1.8;
            margin-bottom: 15px;
        }

        .faq-answer ul {
            margin: 15px 0 15px 20px;
            color: #555;
        }

        .faq-answer ul li {
            margin-bottom: 10px;
            line-height: 1.7;
        }

        .faq-answer ul li strong {
            color: #2d5016;
        }

        .highlight {
            background: #fff3cd;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 600;
            color: #2d5016;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, #2d5016 0%, #3d6b1f 100%);
            color: white;
            padding: 48px 0;
            padding: 50px;
            border-radius: 10px;
            margin-top: 50px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(45, 80, 22, 0.3);
        }

        .cta-section h2 {
            font-size: 2.2em;
            margin-bottom: 15px;
        }

        .cta-section p {
            font-size: 1.2em;
            margin-bottom: 30px;
            opacity: 0.95;
        }

        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .image-trust {
            display: flex;
            justify-content: center;
        }


        .cta-button {
            display: inline-block;
            background: #ffc107;
            color: #2d5016;
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.1em;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
        }

        .cta-button:hover {
            background: #ffcd38;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 193, 7, 0.4);
        }

        .cta-button-secondary {
            background: white;
            color: #2d5016;
        }

        .cta-button-secondary:hover {
            background: #f8f9fa;
        }



        /* Responsive */
        @media (max-width: 768px) {

            .top-header,
            .navbar {
                padding: 15px 20px;
                flex-direction: column;
                gap: 15px;
            }

            .nav-links {
                flex-direction: column;
                gap: 15px;
            }

            .hero h1 {
                font-size: 2em;
            }

            .benefits {
                grid-template-columns: 1fr;
            }

            .cta-buttons {
                flex-direction: column;
            }
        }
    </style>
    </head>

    <body>

        <?php include "includes/header.php" ?>

        <!-- Hero Section -->
        <div class="faq-hero">
            <div class="container">
                <div class="faqhero-subtitle">YOUR QUESTIONS ANSWERED</div>
                <h1>Frequently Asked Questions</h1>
                <p>Everything you need to know about going solar in the Philippines</p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container">
            <!-- Benefits Cards -->
            <div class="benefits">
                <div class="benefit-card">
                    <div class="icon image-trust">
                        <svg width="80" height="80" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#2d5016" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                    </div>
                    <h4>Save Time</h4>
                    <p>Get instant answers to common questions without waiting for a callback</p>
                </div>
                <div class="benefit-card">
                    <div class="icon image-trust">
                        <img src="assets/img/trust.jpg" alt="Build Trust" width="80" height="80">
                    </div>
                    <h4>Build Trust</h4>
                    <p>Transparent information helps you make informed decisions with confidence</p>
                </div>
                <div class="benefit-card">
                    <div class="icon image-trust">
                        <img src="assets/img/roi.webp" alt="Understand Costs" width="80" height="80">
                    </div>
                    <h4>Understand Costs</h4>
                    <p>Clear pricing and ROI information to plan your solar investment</p>
                </div>

            </div>

            <!-- FAQ Section -->
            <div class="faq-section">
                <div class="section-title">
                    <h2>Common Questions About Solar Power</h2>
                    <p>Click on any question to see the answer</p>
                </div>

                <!-- FAQ 1 -->
                <div class="faq-item active">
                    <div class="faq-question">
                        <h3>💰 How much does solar cost?</h3>
                        <div class="faq-icon">+</div>
                    </div>
                    <div class="faq-answer">
                        <p>Solar installation costs vary based on your system size and energy needs. Here's a typical breakdown for the Philippines:</p>
                        <ul>
                            <li><strong>Small Residential (3-5kW):</strong> ₱150,000 - ₱250,000</li>
                            <li><strong>Medium Residential (8-12kW):</strong> ₱400,000 - ₱600,000</li>
                            <li><strong>Large Residential/Commercial (20kW+):</strong> Custom pricing based on requirements</li>
                        </ul>
                        <p>💳 We offer <span class="highlight">flexible payment plans</span> and can help you access government incentives and financing options to reduce upfront costs. Many of our customers finance their systems and see immediate savings on their monthly bills!</p>
                    </div>
                </div>

                <!-- FAQ 2 -->
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>📊 How long is the ROI (Return on Investment)?</h3>
                        <div class="faq-icon">+</div>
                    </div>
                    <div class="faq-answer">
                        <p>Most of our Filipino customers achieve <span class="highlight">full ROI within 4-6 years</span>, depending on several factors:</p>
                        <ul>
                            <li><strong>Current electricity bill:</strong> Higher bills = faster payback</li>
                            <li><strong>System size and efficiency:</strong> Quality components maximize production</li>
                            <li><strong>Location and sunlight:</strong> Philippines has excellent solar potential!</li>
                            <li><strong>Net metering:</strong> Selling excess power speeds up ROI</li>
                            <li><strong>Electricity rate increases:</strong> MERALCO rates typically rise 3-5% annually</li>
                        </ul>
                        <p>⚡ After payback, you'll enjoy <strong>FREE electricity for 20+ years</strong> since solar panels last 25-30 years with proper maintenance. That's decades of zero or minimal electric bills!</p>
                    </div>
                </div>

                <!-- FAQ 3 -->
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>🔌 What happens during brownouts or power outages?</h3>
                        <div class="faq-icon">+</div>
                    </div>
                    <div class="faq-answer">
                        <p>This is one of the most common questions in the Philippines! The answer depends on your system type:</p>
                        <ul>
                            <li><strong>Grid-Tied System:</strong> Automatically shuts off during outages for safety (to protect utility workers repairing lines). When power returns, your system automatically reconnects.</li>
                            <li><strong>Hybrid System (with battery backup):</strong> You'll have <span class="highlight">continuous power during brownouts!</span> Your batteries keep essential appliances running - perfect for frequent Philippine outages.</li>
                            <li><strong>Off-Grid System:</strong> Complete independence from the grid with 24/7 backup power from your battery bank.</li>
                        </ul>
                        <p>🔋 <strong>Our recommendation:</strong> Hybrid systems are ideal for the Philippines due to frequent brownouts. Your solar panels charge batteries during the day, and you use stored power during outages or at night. Many customers report never worrying about brownouts again!</p>
                    </div>
                </div>

                <!-- FAQ 4 -->
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>📝 Do you assist with net-metering applications?</h3>
                        <div class="faq-icon">+</div>
                    </div>
                    <div class="faq-answer">
                        <p><strong>Yes, absolutely!</strong> We handle the entire net-metering process from start to finish. This is included in our service:</p>
                        <ul>
                            <li>✅ <strong>Document preparation:</strong> We compile all required forms and technical specs</li>
                            <li>✅ <strong>Submission to utility:</strong> Coordination with MERALCO or your local distribution utility</li>
                            <li>✅ <strong>Bi-directional meter installation:</strong> We arrange the meter replacement</li>
                            <li>✅ <strong>Follow-up and approval:</strong> We track your application until approved</li>
                            <li>✅ <strong>Final inspection:</strong> We coordinate with ERC and utility inspectors</li>
                        </ul>
                        <p>💡 <strong>Why net-metering matters:</strong> With net-metering, <span class="highlight">excess energy goes back to the grid</span> and you get credits that reduce your bill even further. During rainy months when you produce less, you can use these credits. It's like having the grid as your free battery!</p>
                        <p>📋 The entire process typically takes 2-4 months, but we handle all the paperwork so you don't have to worry about it.</p>
                    </div>
                </div>

                <!-- FAQ 5 -->
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>🔧 Is there maintenance required for solar panels?</h3>
                        <div class="faq-icon">+</div>
                    </div>
                    <div class="faq-answer">
                        <p>Great news - solar panels require <span class="highlight">very minimal maintenance!</span> They have no moving parts and are built to withstand Philippine weather conditions. Here's what's recommended:</p>
                        <ul>
                            <li><strong>Panel cleaning:</strong> 2-4 times per year to remove dust, leaves, and bird droppings. Rain naturally cleans panels, but manual cleaning ensures peak performance.</li>
                            <li><strong>Visual inspection:</strong> Check for any physical damage or debris after typhoons</li>
                            <li><strong>Electrical inspection:</strong> Annual checkup of inverter, wiring, and connections</li>
                            <li><strong>Performance monitoring:</strong> Track daily production through our mobile app (we'll alert you to any issues)</li>
                        </ul>
                        <p>🛠️ <strong>We offer maintenance packages:</strong></p>
                        <ul>
                            <li>Quarterly cleaning and inspection</li>
                            <li>Priority response for any issues</li>
                            <li>Performance optimization</li>
                            <li>Warranty coverage extensions</li>
                        </ul>
                        <p>Our maintenance team handles everything, ensuring your system runs at peak efficiency for 25-30 years. Most customers spend less on annual solar maintenance than they used to spend on electricity in a single month!</p>
                    </div>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="cta-section">
                <h2>Still have questions?</h2>
                <p>Our DOE-accredited solar specialists are ready to help! Get personalized answers for your specific situation.</p>
                <div class="cta-buttons">
                    <a href="contact.php" class="cta-button">📞 Contact Us Now</a>
                    <a href="tel:+639953947379" class="cta-button cta-button-secondary">📱 Call: +63 995 394 7379</a>
                </div>
            </div>
        </div>

        <?php include "includes/footer.php" ?>


        <script>
            // Scroll-based animation for benefit cards
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate');
                    }
                });
            }, observerOptions);

            // Observe all benefit cards
            document.querySelectorAll('.benefit-card').forEach(card => {
                observer.observe(card);
            });

            // Observe FAQ section elements
            document.querySelectorAll('.section-title, .faq-item').forEach(element => {
                observer.observe(element);
            });

            // FAQ Accordion functionality
            const faqItems = document.querySelectorAll('.faq-item');

            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question');

                question.addEventListener('click', () => {
                    // Toggle current item
                    item.classList.toggle('active');
                });
            });

            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                });
            });
        </script>
    </body>

</html>