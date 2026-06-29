(function () {
    const CART_KEY = 'solarCart';
    const appBase = window.SOLAR_APP_BASE || '';
    const endpoint = window.SOLAR_CART_AJAX_ENDPOINT || (appBase + 'add-to-cart-ajax.php');
    const countEndpoint = appBase + 'controllers/cart.php?action=get';

    function cartRows(cart) {
        if (Array.isArray(cart)) return cart;
        if (cart && typeof cart === 'object') return Object.values(cart);
        return [];
    }

    function setCartCount(count) {
        const badge = document.getElementById('cart-count');
        if (!badge) return;
        badge.textContent = String(Math.max(0, parseInt(count, 10) || 0));
    }

    async function refreshCartCount() {
        if (!document.getElementById('cart-count')) return;

        try {
            const response = await fetch(countEndpoint, {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            if (!response.ok || !data.success) return;

            const totalItems = Number.isFinite(Number(data.total_items))
                ? Number(data.total_items)
                : cartRows(data.cart).length;

            setCartCount(totalItems);
            if (data.cart) {
                saveCartSnapshot(data.cart);
            }
        } catch (error) {
            console.error('Cart count refresh failed:', error);
        }
    }

    function parseMoney(value) {
        if (typeof value === 'number') return value;
        return parseFloat(String(value || '').replace(/[^0-9.-]+/g, '')) || 0;
    }

    function parseProductData(raw) {
        if (!raw) return null;
        try {
            return JSON.parse(raw);
        } catch (error) {
            console.error('Invalid product data:', error);
            return null;
        }
    }

    function quantityFromContext(trigger, product) {
        const selector = trigger?.dataset?.cartQuantityInput || trigger?.dataset?.quantityInput || '';
        const scopedInput = selector ? document.querySelector(selector) : null;
        const cardInput = trigger?.closest('.product-card, .product-details-wrapper, form')?.querySelector('input[name="quantity"], input[name="qty"], .cart-quantity');
        const detailInput = trigger?.hasAttribute('data-product') ? null : document.getElementById('product-qty');
        const input = scopedInput || cardInput || detailInput;
        const requested = input ? parseInt(input.value, 10) : 1;
        return Math.max(1, requested || 1);
    }

    function detailPagePayload(trigger) {
        const productIdInput = document.getElementById('hidden_product_id');
        if (!productIdInput) return null;

        const selectedVariant = document.querySelector('.variant-radio:checked');
        const qtyInput = document.getElementById('product-qty');
        const title = document.querySelector('.product-title');
        const category = document.querySelector('.category-badge');
        const image = document.getElementById('mainImage');
        const priceText = document.querySelector('.product-price');
        const moq = Math.max(1, parseInt(qtyInput?.getAttribute('min'), 10) || 1);

        return {
            product_id: productIdInput.value,
            variant_id: selectedVariant?.value || '',
            brand_id: selectedVariant?.dataset?.brandId || '',
            brand_name: selectedVariant?.dataset?.brand || '',
            product_name: title?.textContent?.trim() || 'Solar Product',
            category: category?.textContent?.trim() || '',
            price: selectedVariant?.dataset?.price || parseMoney(priceText?.textContent),
            image_path: image?.getAttribute('src') || '',
            quantity: quantityFromContext(trigger, { moq: moq }),
            moq: moq
        };
    }

    function buttonPayload(button) {
        const product = parseProductData(button.getAttribute('data-product'));
        if (!product) {
            return detailPagePayload(button);
        }

        const moq = Math.max(1, parseInt(product.moq, 10) || 1);
        return {
            product_id: product.product_id || product.id,
            variant_id: product.variant_id || '',
            brand_id: product.brand_id || '',
            brand_name: product.brandName || product.brand_name || '',
            product_name: product.product_name || product.displayName || product.name || 'Solar Product',
            category: product.category || '',
            price: product.price || 0,
            image_path: product.image_path || product.imagePath || '',
            quantity: quantityFromContext(button, product),
            moq: moq
        };
    }

    function formPayload(form) {
        const data = new FormData(form);
        const payload = {};

        data.forEach(function (value, key) {
            payload[key] = value;
        });

        const product = parseProductData(form.getAttribute('data-product'));
        if (product) {
            Object.assign(payload, buttonPayload(form));
        }

        payload.product_id = payload.product_id || payload.id || payload.variant_id;
        payload.product_name = payload.product_name || payload.name || payload.displayName;
        payload.quantity = payload.quantity || 1;
        payload.price = payload.price || 0;

        return payload;
    }

    function validPayload(payload) {
        return payload && (parseInt(payload.product_id, 10) > 0 || String(payload.variant_id || '').trim() !== '');
    }

    function saveCartSnapshot(cart) {
        const normalized = cartRows(cart).map(function (item) {
            return {
                id: parseInt(item.product_id || item.id, 10) || 0,
                product_id: parseInt(item.product_id || item.id, 10) || 0,
                variant_id: item.variant_id || '',
                brand_id: item.brand_id || null,
                displayName: item.displayName || item.product_name || item.name || 'Solar Product',
                brandName: item.brandName || item.brand_name || '',
                category: item.category || '',
                price: parseMoney(item.price),
                image_path: item.image_path || item.imagePath || 'assets/img/product-placeholder.png',
                quantity: Math.max(1, parseInt(item.quantity, 10) || 1),
                moq: Math.max(1, parseInt(item.moq, 10) || 1)
            };
        }).filter(function (item) {
            return item.product_id > 0;
        });

        localStorage.setItem(CART_KEY, JSON.stringify(normalized));
        document.dispatchEvent(new CustomEvent('solar:cart-updated', {
            detail: {
                cart: normalized,
                totalItems: normalized.length
            }
        }));
    }

    function ensureToastHost() {
        let host = document.getElementById('cart-toast-host');
        if (host) return host;

        host = document.createElement('div');
        host.id = 'cart-toast-host';
        host.setAttribute('aria-live', 'polite');
        host.style.position = 'fixed';
        host.style.right = '18px';
        host.style.bottom = '18px';
        host.style.zIndex = '5000';
        host.style.display = 'grid';
        host.style.gap = '10px';
        document.body.appendChild(host);
        return host;
    }

    function showToast(message, type) {
        const host = ensureToastHost();
        const toast = document.createElement('div');
        toast.textContent = message;
        toast.style.maxWidth = '320px';
        toast.style.padding = '12px 14px';
        toast.style.borderRadius = '8px';
        toast.style.boxShadow = '0 12px 30px rgba(0, 0, 0, 0.18)';
        toast.style.background = type === 'error' ? '#b42318' : '#155e42';
        toast.style.color = '#fff';
        toast.style.fontSize = '14px';
        toast.style.fontWeight = '700';
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(8px)';
        toast.style.transition = 'opacity 180ms ease, transform 180ms ease';

        host.appendChild(toast);
        requestAnimationFrame(function () {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        });

        setTimeout(function () {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(8px)';
            setTimeout(function () {
                toast.remove();
            }, 220);
        }, 2600);
    }

    function setBusy(control, busy) {
        if (!control || !('disabled' in control)) return;
        control.disabled = busy;
        control.setAttribute('aria-busy', busy ? 'true' : 'false');
    }

    async function addToCart(payload, control) {
        if (!validPayload(payload)) {
            showToast('Unable to add product to cart.', 'error');
            return;
        }

        const body = new URLSearchParams();
        Object.keys(payload).forEach(function (key) {
            const value = payload[key];
            if (value !== undefined && value !== null) {
                body.append(key, value);
            }
        });

        setBusy(control, true);

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: body,
                credentials: 'same-origin'
            });
            const data = await response.json();

            if (!response.ok || (data.status && data.status !== 'success') || data.success === false) {
                throw new Error(data.message || data.error || 'Unable to add product to cart.');
            }

            const totalItems = Number.isFinite(Number(data.total_items))
                ? Number(data.total_items)
                : cartRows(data.cart).length;

            setCartCount(totalItems);
            if (data.cart) {
                saveCartSnapshot(data.cart);
            }
            showToast('Product added to cart successfully!', 'success');
        } catch (error) {
            console.error('Add to cart failed:', error);
            showToast(error.message || 'Unable to add product to cart.', 'error');
        } finally {
            setBusy(control, false);
        }
    }

    document.addEventListener('submit', function (event) {
        const form = event.target;
        if (!form.matches('form.add-to-cart-form, form[data-cart-form], form[action*="add-to-cart"]')) return;

        event.preventDefault();
        event.stopPropagation();
        addToCart(formPayload(form), form.querySelector('[type="submit"]') || form);
    }, true);

    document.addEventListener('click', function (event) {
        const button = event.target.closest('.btn-add-cart, [data-cart-add], [data-action="add-to-cart"]');
        if (!button) return;

        const payload = buttonPayload(button);
        if (!validPayload(payload)) return;

        event.preventDefault();
        event.stopPropagation();
        if (typeof event.stopImmediatePropagation === 'function') {
            event.stopImmediatePropagation();
        }

        addToCart(payload, button);
    }, true);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', refreshCartCount);
    } else {
        refreshCartCount();
    }
})();
