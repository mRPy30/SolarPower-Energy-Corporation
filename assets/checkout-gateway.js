(function ($) {
    const googleClientId = '257004722980-0mumh545gr3qtd2l9qagsreivqo68d2l.apps.googleusercontent.com';
    let googleButtonRendered = false;

    function modalElement() {
        return document.getElementById('checkoutGatewayModal');
    }

    function ensureBootstrapJqueryModalBridge() {
        if (!$ || !window.bootstrap || $.fn.modal) return;

        $.fn.modal = function (action) {
            return this.each(function () {
                const instance = bootstrap.Modal.getOrCreateInstance(this);
                if (action === 'show') instance.show();
                if (action === 'hide') instance.hide();
                if (action === 'toggle') instance.toggle();
            });
        };
    }

    function productIdFromButton(button) {
        const explicitId = button.getAttribute('data-product-id');
        if (explicitId) return explicitId;

        const rawProduct = button.getAttribute('data-product');
        if (!rawProduct) return '';

        try {
            const product = JSON.parse(rawProduct);
            return String(product.product_id || product.id || '');
        } catch (error) {
            console.error('Unable to read product data for checkout gateway:', error);
            return '';
        }
    }

    function savePendingProduct(button) {
        const productId = productIdFromButton(button);
        if (productId) {
            localStorage.setItem('pending_checkout_product_id', productId);
        }
        return productId;
    }

    function openCheckoutGateway(button) {
        const productId = savePendingProduct(button);
        if (!productId) return;

        ensureBootstrapJqueryModalBridge();
        renderGoogleButton();
        $('#checkoutGatewayModal').modal('show');
    }

    function decodeGoogleJwt(token) {
        const payload = token.split('.')[1];
        const base64 = payload.replace(/-/g, '+').replace(/_/g, '/');
        const json = decodeURIComponent(
            atob(base64)
                .split('')
                .map(function (char) {
                    return '%' + ('00' + char.charCodeAt(0).toString(16)).slice(-2);
                })
                .join('')
        );

        return JSON.parse(json);
    }

    function checkoutProductId() {
        return localStorage.getItem('pending_checkout_product_id') || '';
    }

    window.checkoutGatewayGoogleCallback = function (response) {
        if (!response || !response.credential) return;

        const googleUser = decodeGoogleJwt(response.credential);
        const productId = checkoutProductId();
        const query = $.param({
            action: 'google',
            name: googleUser.name || '',
            email: googleUser.email || '',
            product_id: productId
        });

        window.location.href = 'checkout.php?' + query;
    };

    function renderGoogleButton(attempt) {
        const anchor = document.getElementById('modal-google-anchor');
        if (!anchor) return;

        if (!window.google || !google.accounts || !google.accounts.id) {
            if ((attempt || 0) < 20) {
                setTimeout(function () {
                    renderGoogleButton((attempt || 0) + 1);
                }, 250);
            }
            return;
        }

        if (!googleButtonRendered) {
            google.accounts.id.initialize({
                client_id: googleClientId,
                callback: window.checkoutGatewayGoogleCallback
            });
            googleButtonRendered = true;
        }

        anchor.innerHTML = '';
        google.accounts.id.renderButton(anchor, {
            theme: 'outline',
            size: 'large',
            text: 'continue_with',
            width: 320
        });
    }

    $(document).on('click', '#orderAsGuestBtn', function () {
        const productId = checkoutProductId();
        if (!productId) return;
        window.location.href = 'checkout.php?action=guest&product_id=' + encodeURIComponent(productId);
    });

    $(document).on('click', '.btn-buy-now', function (event) {
        event.preventDefault();
        event.stopPropagation();
        openCheckoutGateway(this);
    });

    document.addEventListener('click', function (event) {
        const button = event.target.closest('.btn-buy-now');
        if (!button) return;

        event.preventDefault();
        event.stopPropagation();
        if (typeof event.stopImmediatePropagation === 'function') {
            event.stopImmediatePropagation();
        }
        openCheckoutGateway(button);
    }, true);

    window.addEventListener('load', function () {
        ensureBootstrapJqueryModalBridge();
        renderGoogleButton(0);
    });
})(jQuery);
