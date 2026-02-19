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
    <link rel="stylesheet" href="assets/style.css">

<style>
/* ============================================
   FAQ Page Styles
   ============================================ */

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Hero Section */
.faq-hero {
    background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('assets/img/demo-faq.jpeg') no-repeat center/cover;
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
    animation: fadeInUp .8s ease-out .2s both;
}

.faq-hero h1 {
    font-size: 3.5rem;
    font-weight: bold;
    margin-bottom: 20px;
    line-height: 1.2;
    animation: fadeInUp .8s ease-out .4s both;
}

.faq-hero p {
    font-size: 1.2rem;
    opacity: .95;
    max-width: 800px;
    margin: 0 auto;
    animation: fadeInUp .8s ease-out .6s both;
}

/* Benefits Cards */
.benefits {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    margin: 48px 12px;
    padding: 48px 0;
}

.benefit-card {
    background: white;
    padding: 30px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0,0,0,.1);
    transition: transform .3s ease, box-shadow .3s ease;
    opacity: 0;
    transform: translateY(30px);
}

.benefit-card.animate { animation: fadeInUp .8s ease-out forwards; }
.benefit-card:nth-child(1).animate { animation-delay: .1s; }
.benefit-card:nth-child(2).animate { animation-delay: .3s; }
.benefit-card:nth-child(3).animate { animation-delay: .5s; }

.benefit-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(45,80,22,.2);
}

.benefit-card .icon { font-size: 3em; margin-bottom: 15px; }
.benefit-card h4 { color: #2d5016; margin-bottom: 10px; font-size: 1.2em; }
.benefit-card p { color: #666; font-size: .95em; line-height: 1.6; }

.image-trust { display: flex; justify-content: center; }

/* FAQ Accordion Section */
.faq-section {
    background: white;
    padding: 48px 40px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,.1);
}

.section-title {
    text-align: center;
    margin-bottom: 40px;
    opacity: 0;
    transform: translateY(30px);
}

.section-title.animate { animation: fadeInUp .8s ease-out forwards; }
.section-title h2 { color: #2d5016; font-size: 2em; margin-bottom: 10px; }
.section-title p { color: #666; font-size: 1.1em; }

.faq-item {
    margin-bottom: 20px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    overflow: hidden;
    transition: all .3s ease;
    opacity: 0;
    transform: translateY(30px);
}

.faq-item.animate { animation: fadeInUp .8s ease-out forwards; }
.faq-item:nth-child(2).animate { animation-delay: .1s; }
.faq-item:nth-child(3).animate { animation-delay: .2s; }
.faq-item:nth-child(4).animate { animation-delay: .3s; }
.faq-item:nth-child(5).animate { animation-delay: .4s; }
.faq-item:nth-child(6).animate { animation-delay: .5s; }

.faq-item:hover { border-color: #ffc107; }

.faq-question {
    background: #f8f9fa;
    padding: 20px 25px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: background .3s ease;
}

.faq-question:hover { background: #e9ecef; }
.faq-question h3 { color: #2d5016; font-size: 1.15em; font-weight: 600; flex: 1; }

.faq-icon {
    width: 35px; height: 35px;
    background: #2d5016;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 1.5em;
    transition: all .3s ease;
}

.faq-item.active .faq-icon { background: #ffc107; color: #2d5016; transform: rotate(45deg); }

.faq-answer {
    max-height: 0;
    overflow: hidden;
    transition: max-height .4s ease, padding .4s ease;
    padding: 0 25px;
    background: white;
}

.faq-item.active .faq-answer {
    max-height: 600px;
    padding: 25px;
    border-top: 2px solid #f8f9fa;
}

.faq-answer p { color: #555; line-height: 1.8; margin-bottom: 15px; }
.faq-answer ul { margin: 15px 0 15px 20px; color: #555; }
.faq-answer ul li { margin-bottom: 10px; line-height: 1.7; }
.faq-answer ul li strong { color: #2d5016; }

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
    padding: 50px;
    border-radius: 10px;
    margin-top: 50px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(45,80,22,.3);
}

.cta-section h2 { font-size: 2.2em; margin-bottom: 15px; }
.cta-section p { font-size: 1.2em; margin-bottom: 30px; opacity: .95; }

.cta-buttons { display: flex; gap: 20px; justify-content: center; flex-wrap: wrap; }

.cta-button {
    display: inline-block;
    background: #ffc107;
    color: #2d5016;
    padding: 15px 40px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 700;
    font-size: 1.1em;
    transition: all .3s ease;
    box-shadow: 0 5px 15px rgba(255,193,7,.3);
}

.cta-button:hover { background: #ffcd38; transform: translateY(-3px); box-shadow: 0 8px 25px rgba(255,193,7,.4); }
.cta-button-secondary { background: white; color: #2d5016; }
.cta-button-secondary:hover { background: #f8f9fa; }

/* ===== Responsive ===== */
@media (max-width: 768px) {
    .faq-hero { height: auto; min-height: 35vh; padding: 40px 0; }
    .faq-hero h1 { font-size: 2em; }
    .benefits { grid-template-columns: 1fr; }
    .faq-section { padding: 30px 20px; }
    .cta-buttons { flex-direction: column; }
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

        <!-- FAQ Accordion Section -->
        <div class="faq-section">
            <div class="section-title">
                <h2>Common Questions About Solar Power</h2>
                <p>Click on any question to see the answer</p>
            </div>

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
                    <p>💳 We offer <span class="highlight">flexible payment plans</span> and can help you access government incentives and financing options to reduce upfront costs.</p>
                </div>
            </div>

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
                    <p>⚡ After payback, you'll enjoy <strong>FREE electricity for 20+ years</strong> since solar panels last 25-30 years!</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>🔌 What happens during brownouts or power outages?</h3>
                    <div class="faq-icon">+</div>
                </div>
                <div class="faq-answer">
                    <p>The answer depends on your system type:</p>
                    <ul>
                        <li><strong>Grid-Tied System:</strong> Automatically shuts off during outages for safety. Reconnects when power returns.</li>
                        <li><strong>Hybrid System (with battery backup):</strong> <span class="highlight">Continuous power during brownouts!</span> Batteries keep essentials running.</li>
                        <li><strong>Off-Grid System:</strong> Complete independence from the grid with 24/7 backup power.</li>
                    </ul>
                    <p>🔋 <strong>Our recommendation:</strong> Hybrid systems are ideal for the Philippines due to frequent brownouts.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>📝 Do you assist with net-metering applications?</h3>
                    <div class="faq-icon">+</div>
                </div>
                <div class="faq-answer">
                    <p><strong>Yes, absolutely!</strong> We handle the entire net-metering process from start to finish:</p>
                    <ul>
                        <li>✅ Document preparation — all required forms and technical specs</li>
                        <li>✅ Submission to utility — coordination with MERALCO</li>
                        <li>✅ Bi-directional meter installation</li>
                        <li>✅ Follow-up and approval tracking</li>
                        <li>✅ Final inspection — ERC & utility inspector coordination</li>
                    </ul>
                    <p>💡 With net-metering, <span class="highlight">excess energy goes back to the grid</span> for bill credits. The process takes 2-4 months.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>🔧 Is there maintenance required for solar panels?</h3>
                    <div class="faq-icon">+</div>
                </div>
                <div class="faq-answer">
                    <p>Solar panels require <span class="highlight">very minimal maintenance!</span> No moving parts. Here's what's recommended:</p>
                    <ul>
                        <li><strong>Panel cleaning:</strong> 2-4 times per year</li>
                        <li><strong>Visual inspection:</strong> After typhoons for damage/debris</li>
                        <li><strong>Electrical inspection:</strong> Annual inverter & wiring checkup</li>
                        <li><strong>Performance monitoring:</strong> Track via our mobile app</li>
                    </ul>
                    <p>🛠️ We offer maintenance packages: quarterly cleaning, priority response, optimization, and warranty extensions.</p>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="cta-section">
            <h2>Still have questions?</h2>
            <p>Our DOE-accredited solar specialists are ready to help!</p>
            <div class="cta-buttons">
                <a href="contact.php" class="cta-button">📞 Contact Us Now</a>
                <a href="tel:+639953947379" class="cta-button cta-button-secondary">📱 Call: +63 995 394 7379</a>
            </div>
        </div>
    </div>

    <?php include "includes/footer.php" ?>

    <script>
    // ======================
    //  FAQ Accordion
    // ======================
    document.querySelectorAll('.faq-question').forEach(q => {
        q.addEventListener('click', () => {
            const item = q.parentElement;
            const wasActive = item.classList.contains('active');
            document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('active'));
            if (!wasActive) item.classList.add('active');
        });
    });

    // ======================
    //  Scroll Animations
    // ======================
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });

    document.querySelectorAll('.benefit-card, .section-title, .faq-item').forEach(el => observer.observe(el));
    </script>
</body>

</html>