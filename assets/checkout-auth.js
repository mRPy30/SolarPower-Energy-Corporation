(function () {
    const APP_BASE = window.SOLAR_APP_BASE || '/SolarPower-Energy-Corporation/';

    async function getClientSession() {
        const response = await fetch(APP_BASE + 'controllers/auth/session.php', { credentials: 'same-origin' });
        return response.json();
    }

    function fillClientFields(client) {
        if (!client) return;
        const fullName = [client.firstName || '', client.lastName || ''].join(' ').trim();
        const fields = {
            cust_name: fullName,
            cust_email: client.email || '',
            cust_phone: client.contact_number || '',
            cust_address: client.address || ''
        };

        Object.keys(fields).forEach((id) => {
            const input = document.getElementById(id);
            if (input && !input.value && fields[id]) {
                input.value = fields[id];
            }
        });
    }

    function showAuthModal() {
        let modal = document.getElementById('checkoutAuthModal');
        if (!modal) {
            const returnTo = encodeURIComponent(window.location.href);
            document.body.insertAdjacentHTML('beforeend', `
                <div class="modal fade" id="checkoutAuthModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Sign in to checkout</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <a class="btn btn-light border w-100 mb-3 fw-semibold" href="${APP_BASE}controllers/auth/oauth-start.php?provider=google&return_to=${returnTo}">
                                    Continue with Google
                                </a>
                                <a class="btn btn-primary w-100 fw-semibold" href="${APP_BASE}controllers/auth/oauth-start.php?provider=facebook&return_to=${returnTo}">
                                    Continue with Facebook
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            `);
            modal = document.getElementById('checkoutAuthModal');
        }

        if (window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modal).show();
        } else {
            modal.style.display = 'block';
        }
    }

    async function requireCheckoutAuth(next) {
        // TEMPORARILY DISABLED FOR LIVE PAYMENT TESTING.
        // Keep the OAuth modal and session-sync code above intact for future re-enable.
        // Original behavior checked controllers/auth/session.php and called showAuthModal()
        // when no $_SESSION['client_id'] existed.
        if (typeof next === 'function') {
            next();
        }
    }

    window.SolarCheckoutAuth = {
        requireCheckoutAuth,
        fillClientFields,
        refreshClientFields: async function () {
            const session = await getClientSession();
            if (session.logged_in) fillClientFields(session.client);
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        window.SolarCheckoutAuth.refreshClientFields().catch(function () {});
    });
})();

