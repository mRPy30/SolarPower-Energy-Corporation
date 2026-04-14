<!-- NEWSLETTER SUBSCRIBE POPUP -->
<div class="modal fade" id="newsletterPopupModal" tabindex="-1" aria-labelledby="newsletterPopupLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered newsletter-modal-dialog">
    <div class="modal-content newsletter-modal-content border-0 overflow-hidden">
      <div class="row g-0 h-100">

        <!-- LEFT PANEL (form) -->
        <div class="col-12 col-md-7 newsletter-left d-flex flex-column justify-content-center p-4 p-md-5">
          <button type="button" class="newsletter-close-btn-mobile" data-bs-dismiss="modal" aria-label="Close">✕</button>
          <h2 class="newsletter-heading">Subscribe<br>Now!</h2>
          <p class="newsletter-subtext">Get weekly solar tips, updates, and exclusive offers delivered to your inbox</p>
          <form id="newsletterPopupForm" action="controllers/subscribe.php" method="POST" novalidate class="w-100" style="max-width: 450px;">
            <div class="mb-3">
              <input
                type="email"
                name="email"
                id="newsletterPopupEmail"
                class="form-control newsletter-email-input"
                placeholder="Email"
                required
              >
              <div class="newsletter-error-msg" id="newsletterEmailError"></div>
            </div>
            <button type="submit" class="btn newsletter-submit-btn w-100">Submit</button>
          </form>
          <div id="newsletterPopupMsg" class="newsletter-feedback-msg mt-2"></div>
        </div>

        <!-- RIGHT PANEL (image) -->
        <div class="col-12 col-md-5 newsletter-right d-flex align-items-center justify-content-center p-0">
          <button type="button" class="newsletter-close-btn" data-bs-dismiss="modal" aria-label="Close">
            ✕
          </button>
          <img src="assets/img/march-popup.jpg" alt="Solar Panel Products" class="newsletter-product-img w-100 h-100">
        </div>
      </div>
    </div>
  </div>
</div>

<style>
/* ── Modal sizing ────────────────────────────────────── */
#newsletterPopupModal {
  z-index: 10500 !important;
}
.modal-backdrop {
  z-index: 10499 !important;
}
.newsletter-modal-dialog {
  max-width: 950px;
  width: 92vw;
}

.newsletter-modal-content {
  border-radius: 8px;
  min-height: 480px;
}

/* ── LEFT PANEL ─────────────────────────────────────── */
.newsletter-left {
  background-color: #00612D;
  color: #fff;
  border-radius: 8px 0 0 8px;
  position: relative;
  overflow: hidden;
}

/* Subtle radial glow for depth */
.newsletter-left::before {
  content: '';
  position: absolute;
  top: -60px;
  right: -60px;
  width: 260px;
  height: 260px;
  background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%);
  pointer-events: none;
  z-index: 0;
}

.newsletter-left > * {
  position: relative;
  z-index: 1;
}

/* ── CLOSE BUTTON — inside right panel, top-right ── */
.newsletter-close-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  background: rgba(255, 255, 255, 0.9);
  border: none;
  color: #333;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  padding: 0;
  line-height: 1;
  font-weight: 700;
  font-size: 14px;
  z-index: 10;
}

.newsletter-close-btn:hover {
  background: #fff;
  transform: scale(1.08);
}

.newsletter-heading {
  font-family: 'Poppins', sans-serif;
  font-weight: 900;
  font-size: 3rem;
  line-height: 1.05;
  color: #ffffff;
  letter-spacing: -0.5px;
  margin-bottom: 16px;
}

.newsletter-subtext {
  font-family: 'Poppins', sans-serif;
  font-weight: 400;
  font-size: 0.95rem;
  color: rgba(255, 255, 255, 0.85);
  line-height: 1.6;
  margin-bottom: 36px;
}

.newsletter-email-input {
  width: 100%;
  padding: 18px 20px;
  font-size: 0.95rem;
  font-family: 'Inter', sans-serif;
  color: #444;
  background-color: #ffffff;
  border: none;
  border-radius: 0;
  outline: none;
  transition: box-shadow 0.2s ease;
}

.newsletter-email-input::placeholder {
  color: #aaa;
}

.newsletter-email-input:focus {
  box-shadow: 0 0 0 3px rgba(255, 214, 0, 0.45);
}

.newsletter-error-msg {
  font-family: 'Inter', sans-serif;
  font-size: 0.8rem;
  color: #ffb3b3;
  margin-top: 8px;
  margin-bottom: 12px;
  text-align: left;
}

.newsletter-submit-btn {
  width: 100%;
  padding: 18px;
  font-family: 'Montserrat', sans-serif;
  font-weight: 700;
  font-size: 1rem;
  letter-spacing: 0.5px;
  color: black;
  background-color: #FFD600;
  border: none;
  border-radius: 0;
  cursor: pointer;
  transition: background-color 0.2s ease, transform 0.1s ease;
}

.newsletter-submit-btn:hover {
  background-color: #FFC200;
  color: #1B5E20;
}

.newsletter-submit-btn:active {
  transform: scale(0.98);
}

.newsletter-feedback-msg {
  font-family: 'Inter', sans-serif;
  font-size: 0.85rem;
  text-align: center;
  margin-top: 12px;
}

/* ── RIGHT PANEL ─────────────────────────────────────── */
.newsletter-right {
  background-color: #ffd600;
  border-radius: 0 8px 8px 0;
  overflow: hidden;
  position: relative;
}

.newsletter-product-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  border-radius: 0 8px 8px 0;
}

/* ── Mobile close button (visible only on small screens) ── */
.newsletter-close-btn-mobile {
  display: none;
}

/* ── Responsive ─────────────────────────────────────── */
@media (max-width: 767.98px) {
  .newsletter-modal-dialog {
    width: 90vw;
    max-width: 400px;
    margin: auto;
  }
  .newsletter-modal-content {
    height: auto;
    min-height: unset;
  }
  .newsletter-left {
    border-radius: 8px;
    padding: 1.8rem 1.5rem 1.5rem !important;
    position: relative;
  }
  /* Hide image panel on mobile */
  .newsletter-right {
    display: none !important;
  }
  /* Show mobile close button */
  .newsletter-close-btn-mobile {
    display: flex;
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(255, 255, 255, 0.9);
    border: none;
    color: #333;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    padding: 0;
    line-height: 1;
    font-weight: 700;
    font-size: 14px;
    z-index: 10;
    transition: all 0.2s;
  }
  .newsletter-close-btn-mobile:hover {
    background: #fff;
    transform: scale(1.08);
  }
  .newsletter-heading {
    font-size: 2rem;
    margin-bottom: 10px;
  }
  .newsletter-subtext {
    font-size: 0.85rem;
    margin-bottom: 20px;
    line-height: 1.5;
  }
  .newsletter-email-input {
    padding: 14px 16px;
    font-size: 0.9rem;
  }
  .newsletter-submit-btn {
    padding: 14px;
    font-size: 0.9rem;
  }
}

/* ── Small phones ── */
@media (max-width: 380px) {
  .newsletter-modal-dialog {
    width: 94vw;
  }
  .newsletter-heading {
    font-size: 1.75rem;
  }
  .newsletter-left {
    padding: 1.5rem 1.2rem 1.3rem !important;
  }
}
</style>

<script>
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('newsletterPopupModal');
    if (!modalEl) return;

    const modal = new bootstrap.Modal(modalEl, { backdrop: true, keyboard: true });

    // Show after 2-second delay
    setTimeout(function () { modal.show(); }, 2000);

    // Form submission
    const form = document.getElementById('newsletterPopupForm');
    const emailInput = document.getElementById('newsletterPopupEmail');
    const errorEl = document.getElementById('newsletterEmailError');
    const feedbackEl = document.getElementById('newsletterPopupMsg');

    form.addEventListener('submit', function (e) {
      e.preventDefault();

      const email = emailInput.value.trim();
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

      // Clear previous errors
      errorEl.textContent = '';
      feedbackEl.textContent = '';

      if (!email) {
        errorEl.textContent = 'Please enter your email address.';
        return;
      }
      if (!emailRegex.test(email)) {
        errorEl.textContent = 'Please enter a valid email address.';
        return;
      }

      const submitBtn = form.querySelector('button[type="submit"]');
      submitBtn.disabled = true;
      submitBtn.textContent = 'Submitting…';

      const formData = new FormData(form);

      fetch(form.action, { method: 'POST', body: formData })
        .then(function (res) { return res.text(); })
        .then(function () {
          feedbackEl.style.color = '#a8f0a8';
          feedbackEl.textContent = 'Thank you! You\'ve been subscribed.';
          form.reset();
          setTimeout(function () { modal.hide(); }, 2200);
        })
        .catch(function () {
          feedbackEl.style.color = '#ff9f9f';
          feedbackEl.textContent = 'Something went wrong. Please try again.';
        })
        .finally(function () {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Submit';
        });
    });
  });
})();
</script>