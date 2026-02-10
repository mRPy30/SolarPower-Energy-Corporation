<style>
/* Footer */
.footer {
    background-color: var(--clr-light);
    color: #1E4940; /* Darker tone, keeping original for contrast */
    padding: 60px 0 var(--spacing-lg);
}

.footer-logo-social img {
    width: 150px;
    margin-bottom: var(--spacing-lg);
}

.social-links {
    display: flex;
    gap: 12px;
    margin-top: var(--spacing-lg);
}

.social-links a {
    width: 40px;
    height: 40px;
    background: #2c2c2c; /* Keeping original dark background */
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--clr-light);
    text-decoration: none;
    transition: var(--transition-fast);
}

.social-links a:hover {
    background: var(--clr-primary);
    color: #1a1a1a;
    transform: translateY(-3px);
}

.footer h4 {
    color: var(--clr-secondary);
    margin-bottom: var(--spacing-lg);
    font-size: 18px;
    font-weight: 700;
}

.footer-links ul {
    list-style: none;
    padding: 0;
}

.footer-links li {
    margin-bottom: 12px;
}

.footer-links a {
    color: #1E4940;
    text-decoration: none;
    font-size: var(--fs-base);
    transition: var(--transition-color);
}

.footer-links a:hover {
    color: var(--clr-primary);
}

.footer-contact .contact-detail {
    margin-bottom: var(--spacing-md);
}

.footer-contact .contact-detail i {
    color: #1E4940;
    margin-right: var(--spacing-sm);
}

.footer-contact .contact-detail span {
    color: #1E4940; 
    font-size: var(--fs-base);
}

.contact-link {
    color: #1E4940;
    text-decoration: none;
    transition: var(--transition-color);
    cursor: pointer;
    position: relative;
}

.contact-link:hover {
    color: var(--clr-primary);
}

/* Tooltip style for "Copied!" message */
.copy-tooltip::after {
    content: "Copied!";
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: #1E4940;
    color: #fff;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 12px;
    display: none;
}

.copy-tooltip.show::after {
    display: block;
}

.footer-shoppee-side img {
    width: 130px;
    background: #fff;
    padding: 12px;
    border-radius: 12px;
    border: 1px solid #e5e5e5;
}

.footer-shoppee-side p {
    margin-top: 8px;
    font-size: 14px;
    color: #1E4940;
}

.footer-shoppee-side h4 {
    margin-bottom: 12px;
}


/* Payment Methods */
.footer-payments {
    margin-top: 30px;
}

.footer-payments h4 {
    color: var(--clr-primary);
    margin-bottom: 15px;
    font-size: 18px;
    font-weight: 700;
}

.payment-icons {
    display: flex;
    flex-wrap: nowrap;        
    justify-content: center;  
    align-items: center;
    gap: 16px;
}

.payment-icons img {
    height: 45px;
    background: #fff;
    padding: 6px 10px;
    border-radius: 6px;
    border: 1px solid #e5e5e5;
    object-fit: contain;
}

.copyright {
    text-align: center;
    padding-top: var(--spacing-xl);
    margin-top: 40px;
    border-top: 1px solid var(--clr-dark);
    font-size: var(--fs-base);
    color: #888;
}

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

</style>    
    
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="footer-logo-social">
                    <img src="assets/img/logo_no_background.png" alt="Logo">
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="../index.php">HOME</a></li>
                        <li><a href="../about.php">ABOUT US</a></li>
                        <li><a href="../services.php">SERVICES</a></li>
                        <li><a href="../product.php">PRODUCT</a></li>
                        <li><a href="../projects.php">PROJECTS</a></li>
                        <li><a href="../contact.php">CONTACT</a></li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="footer-contact">
                    <h4>Contact Info</h4>
                    <div class="contact-detail">
                        <i class="fas fa-envelope"></i>
                        <a href="mailto:solar@solarpower.com.ph" class="contact-link">solar@solarpower.com.ph</a>
                    </div>
                    <div class="contact-detail">
                        <i class="fas fa-phone"></i>
                        <span class="contact-link" id="phone-copy" onclick="copyToClipboard('0952-384-7379', this)">0952-384-7379</span>
                    </div>
                    <div class="contact-detail">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>4/F PBB Corporate Center, 1906 Finance Drive, <br>Madrigal Business Park 1, Muntinlupa City</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4 text-lg-end">
                <div class="footer-shoppee-side">
                    <h4>Shop on Shopee</h4>
                    <img src="assets/img/shopee-qr.png" alt="QR Code">
                    <p style="color: #ee4d2d;">Scan to shop online</p>
                </div>
            </div>
        </div>

        <div class="row footer-payments-row">
            <div class="col-12 text-center">
                <h4 class="mb-3">Payment Methods</h4>
                <div class="payment-icons">
                    <img src="assets/img/payments/BOC Logo.jpg" alt="BOC">
                    <img src="assets/img/payments/GCash-Logo.png" alt="GCash">
                    <img src="assets/img/payments/mastercard-logo.png" alt="MasterCard">
                    <img src="assets/img/payments/Unionbank_logo.png" alt="UnionBank">
                    <img src="assets/img/payments/Maya_logo.png" alt="Maya">
                </div>
            </div>
        </div>

        <div class="copyright">
            <p>&copy; 2025 Solar Power Energy Corporation. All rights reserved.</p>
        </div>
    </div>
</footer>

<script>
function copyToClipboard(text, element) {
    navigator.clipboard.writeText(text).then(() => {
        // Show "Copied!" tooltip
        element.classList.add('copy-tooltip', 'show');
        
        // Hide tooltip after 2 seconds
        setTimeout(() => {
            element.classList.remove('show');
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy: ', err);
    });
}
</script>