(function () {
    const CART_KEY = 'solarCart';
    const APP_BASE = window.SOLAR_APP_BASE || '';
    const endpoint = window.SOLAR_MAYA_CHECKOUT_ENDPOINT || 'controllers/ordering/create-maya-checkout.php';
    const ratesEndpoint = window.SOLAR_DELIVERY_RATES_ENDPOINT || 'controllers/checkout/get-delivery-rates.php';
    const imageFallback = 'assets/img/product-placeholder.png';

    let cart = [];
    let deliveryRates = [];
    let deliveryFee = 0;

    function money(value) {
        const amount = Number(value) || 0;
        return 'PHP ' + amount.toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function cartRows(source) {
        if (Array.isArray(source)) return source;
        if (source && typeof source === 'object') return Object.values(source);
        return [];
    }

    function normalizeCart(source) {
        return cartRows(source)
            .map((item) => ({
                id: parseInt(item.product_id || item.id, 10) || 0,
                product_id: parseInt(item.product_id || item.id, 10) || 0,
                variant_id: item.variant_id || '',
                brand_id: item.brand_id ? parseInt(item.brand_id, 10) : null,
                displayName: item.displayName || item.product_name || item.name || 'Solar Product',
                brandName: item.brandName || item.brand_name || '',
                category: item.category || '',
                price: parseFloat(item.price) || 0,
                image_path: item.image_path || item.imagePath || imageFallback,
                quantity: Math.max(parseInt(item.quantity, 10) || 1, parseInt(item.moq, 10) || 1),
                moq: parseInt(item.moq, 10) || 1
            }))
            .filter((item) => item.product_id > 0);
    }

    function storedCart() {
        try {
            return JSON.parse(localStorage.getItem(CART_KEY) || '[]');
        } catch (error) {
            return [];
        }
    }

    async function loadCart() {
        cart = normalizeCart(window.SOLAR_SESSION_CART || []);

        renderCart();

        try {
            const response = await fetch(APP_BASE + 'controllers/cart.php?action=get', {
                credentials: 'same-origin'
            });
            const data = await response.json();

            if (response.ok && data.success) {
                cart = normalizeCart(data.cart || []);
                saveCart();
                renderCart();
                return;
            }
        } catch (error) {
            console.error('Session cart load error:', error);
        }

        cart = normalizeCart(storedCart());
        saveCart();
        renderCart();
    }

    function saveCart() {
        localStorage.setItem(CART_KEY, JSON.stringify(cart));
    }

    function subtotal() {
        return cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
    }

    function selectedDeliveryRate() {
        const select = document.getElementById('delivery_location');
        if (!select || !select.value) return null;

        const rateId = parseInt(select.value, 10);
        return deliveryRates.find((rate) => rate.id === rateId) || null;
    }

    function selectedDeliveryLabel() {
        const rate = selectedDeliveryRate();
        return rate ? rate.location_name : '';
    }

    function setError(message) {
        const errorBox = document.getElementById('checkoutError');
        if (!errorBox) return;

        if (!message) {
            errorBox.classList.add('d-none');
            errorBox.textContent = '';
            return;
        }

        errorBox.textContent = message;
        errorBox.classList.remove('d-none');
    }

    function renderSummary() {
        const itemSubtotal = subtotal();
        const total = itemSubtotal + deliveryFee;

        document.getElementById('summarySubtotal').textContent = money(itemSubtotal);
        document.getElementById('summaryDelivery').textContent = money(deliveryFee);
        document.getElementById('summaryTotal').textContent = money(total);

        const badge = document.getElementById('itemCountBadge');
        if (badge) {
            const count = cart.reduce((sum, item) => sum + item.quantity, 0);
            badge.textContent = count + (count === 1 ? ' item' : ' items');
        }
    }

    function rateTypeLabel(rateType) {
        return rateType === 'km_range' ? 'Metro Manila' : 'Luzon Provinces';
    }

    function populateDeliveryRates() {
        const select = document.getElementById('delivery_location');
        if (!select) return;

        select.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = deliveryRates.length ? 'Select location' : 'No delivery rates configured';
        select.appendChild(placeholder);

        const groups = {};
        deliveryRates.forEach((rate) => {
            const groupKey = rate.rate_type || 'province';
            if (!groups[groupKey]) {
                const group = document.createElement('optgroup');
                group.label = rateTypeLabel(groupKey);
                groups[groupKey] = group;
                select.appendChild(group);
            }

            const option = document.createElement('option');
            option.value = String(rate.id);
            option.dataset.price = String(rate.price);
            option.dataset.locationName = rate.location_name;
            option.dataset.rateType = rate.rate_type;
            option.textContent = rate.location_name + ' - ' + money(rate.price);
            groups[groupKey].appendChild(option);
        });

        select.disabled = deliveryRates.length === 0;
    }

    async function loadDeliveryRates() {
        const select = document.getElementById('delivery_location');
        if (!select) return;

        select.disabled = true;
        select.innerHTML = '<option value="">Loading delivery rates...</option>';

        const response = await fetch(ratesEndpoint, { credentials: 'same-origin' });
        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || data.error || 'Unable to load delivery rates.');
        }

        deliveryRates = (data.rates || []).map((rate) => ({
            id: parseInt(rate.id, 10),
            origin_address: rate.origin_address || '',
            rate_type: rate.rate_type || 'province',
            location_name: rate.location_name || '',
            price: Number(rate.price) || 0
        })).filter((rate) => rate.id > 0 && rate.location_name);

        populateDeliveryRates();
    }

    function createQuantityControl(item, index) {
        const wrapper = document.createElement('div');
        wrapper.className = 'qty-control';

        const minus = document.createElement('button');
        minus.type = 'button';
        minus.innerHTML = '<i class="fas fa-minus" aria-hidden="true"></i>';
        minus.disabled = item.quantity <= item.moq;
        minus.addEventListener('click', function () {
            updateQuantity(index, -1);
        });

        const count = document.createElement('span');
        count.textContent = item.quantity;

        const plus = document.createElement('button');
        plus.type = 'button';
        plus.innerHTML = '<i class="fas fa-plus" aria-hidden="true"></i>';
        plus.addEventListener('click', function () {
            updateQuantity(index, 1);
        });

        wrapper.append(minus, count, plus);
        return wrapper;
    }

    function renderCart() {
        const container = document.getElementById('cartItems');
        if (!container) return;

        container.innerHTML = '';

        if (!cart.length) {
            const empty = document.createElement('div');
            empty.className = 'empty-cart';
            empty.innerHTML = '<i class="fas fa-shopping-basket" aria-hidden="true"></i><h2 class="h5">Your cart is empty</h2><a class="btn btn-outline-secondary mt-2" href="product.php">Back to products</a>';
            container.appendChild(empty);
            document.getElementById('mayaCheckoutBtn').disabled = true;
            renderSummary();
            return;
        }

        document.getElementById('mayaCheckoutBtn').disabled = false;

        cart.forEach((item, index) => {
            const row = document.createElement('div');
            row.className = 'cart-row';

            const image = document.createElement('img');
            image.src = item.image_path || imageFallback;
            image.alt = item.displayName;
            image.onerror = function () {
                this.src = imageFallback;
            };

            const details = document.createElement('div');

            const title = document.createElement('div');
            title.className = 'item-title';
            title.textContent = [item.brandName, item.displayName].filter(Boolean).join(' ');

            const meta = document.createElement('div');
            meta.className = 'item-meta mt-1';
            meta.textContent = item.category ? item.category + ' - ' + money(item.price) : money(item.price);

            const line = document.createElement('div');
            line.className = 'fw-bold mt-2';
            line.textContent = money(item.price * item.quantity);

            details.append(title, meta, line);

            const actions = document.createElement('div');
            actions.className = 'cart-actions d-flex align-items-center gap-2';
            actions.appendChild(createQuantityControl(item, index));

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'icon-btn';
            remove.innerHTML = '<i class="fas fa-trash-alt" aria-hidden="true"></i>';
            remove.addEventListener('click', function () {
                fetch(APP_BASE + 'controllers/cart.php?action=remove', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ index: index })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        cart = normalizeCart(data.cart || []);
                        saveCart();
                        renderCart();
                    }
                });
            });
            actions.appendChild(remove);

            row.append(image, details, actions);
            container.appendChild(row);
        });

        renderSummary();
    }

    function updateQuantity(index, change) {
        const item = cart[index];
        if (!item) return;

        const newQty = Math.max(item.moq || 1, item.quantity + change);
        fetch(APP_BASE + 'controllers/cart.php?action=update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ index: index, quantity: newQty })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                cart = normalizeCart(data.cart || []);
                saveCart();
                renderCart();
            }
        });
    }

    function formPayload() {
        const name = document.getElementById('cust_name').value.trim();
        const email = document.getElementById('cust_email').value.trim();
        const phone = document.getElementById('cust_phone').value.trim();
        const address = document.getElementById('cust_address').value.trim();
        const rate = selectedDeliveryRate();

        if (!name || !email || !phone || !address || !rate) {
            throw new Error('Please complete all required checkout details.');
        }

        if (!cart.length) {
            throw new Error('Your cart is empty.');
        }

        return {
            customerName: name,
            customerEmail: email,
            customerPhone: phone,
            customerAddress: address,
            delivery_rate_id: rate.id,
            deliveryLocation: String(rate.id),
            selected_location_name: selectedDeliveryLabel(),
            total_items_amount: subtotal(),
            items: cart.map((item) => ({
                id: item.product_id,
                product_id: item.product_id,
                brand_id: item.brand_id,
                displayName: item.displayName,
                brandName: item.brandName,
                category: item.category,
                price: item.price,
                quantity: item.quantity
            }))
        };
    }

    async function submitCheckout(event) {
        event.preventDefault();
        setError('');

        const name = document.getElementById('cust_name')?.value.trim();
        const email = document.getElementById('cust_email')?.value.trim();
        const phone = document.getElementById('cust_phone')?.value.trim();
        const address = document.getElementById('cust_address')?.value.trim();
        const rate = selectedDeliveryRate();

        if (!name || !email || !phone || !address || !rate) {
            event.preventDefault();
            setError('Please complete all required checkout details.');
            return;
        }

        if (!cart.length) {
            event.preventDefault();
            setError('Your cart is empty.');
            return;
        }

        let payload;
        try {
            payload = formPayload();
        } catch (error) {
            setError(error.message || 'Please complete all required checkout details.');
            return;
        }

        // Set hidden input fields
        const totalItemsAmountEl = document.getElementById('total_items_amount');
        const calculatedDeliveryFeeEl = document.getElementById('calculated_delivery_fee');
        const selectedLocationNameEl = document.getElementById('selected_location_name');

        if (totalItemsAmountEl) totalItemsAmountEl.value = subtotal();
        if (calculatedDeliveryFeeEl) calculatedDeliveryFeeEl.value = deliveryFee;
        if (selectedLocationNameEl) selectedLocationNameEl.value = selectedDeliveryLabel();

        // Start Maya checkout asynchronously.
        const button = document.getElementById('mayaCheckoutBtn');
        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Redirecting to Maya…';
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Redirecting to Maya...';
        }
        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            });
            const data = await response.json();

            if (!response.ok || !data.success || !(data.checkoutUrl || data.paymentUrl)) {
                throw new Error(data.message || data.error || 'Maya Checkout could not be created.');
            }

            localStorage.removeItem(CART_KEY);
            window.location.href = data.checkoutUrl || data.paymentUrl;
        } catch (error) {
            setError(error.message || 'Payment could not be started.');
            if (button) {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-lock me-2"></i>Pay Securely with Maya';
            }
        }
    }

    function autodetectDeliveryRate() {
        const addressInput = document.getElementById('cust_address');
        const deliverySelect = document.getElementById('delivery_location');
        if (!addressInput || !deliverySelect || deliveryRates.length === 0) return;

        const text = addressInput.value.toUpperCase();
        if (!text) return;

        let matchedOptionValue = null;
        const options = Array.from(deliverySelect.options);
        const validOptions = options.filter(opt => opt.value !== "");

        // Try to match provinces first
        for (const opt of validOptions) {
            const locName = (opt.dataset.locationName || opt.textContent).toUpperCase();
            if (locName.includes("METRO MANILA")) continue;
            
            if (text.includes(locName)) {
                matchedOptionValue = opt.value;
                break;
            }
        }

        // Fallback to Metro Manila / NCR cities if no province matched
        if (!matchedOptionValue) {
            const isMetroManila = text.includes("METRO MANILA") || 
                                  text.includes("NCR") || 
                                  text.includes("MANILA") ||
                                  text.includes("MUNTINLUPA") ||
                                  text.includes("LAS PIÑAS") ||
                                  text.includes("MAKATI") ||
                                  text.includes("QUEZON CITY") ||
                                  text.includes("TAGUIG") ||
                                  text.includes("PASIG") ||
                                  text.includes("CALOOCAN") ||
                                  text.includes("PASAY") ||
                                  text.includes("PARAÑAQUE") ||
                                  text.includes("MARIKINA") ||
                                  text.includes("VALENZUELA") ||
                                  text.includes("MALABON") ||
                                  text.includes("NAVOTAS") ||
                                  text.includes("MANDALUYONG") ||
                                  text.includes("SAN JUAN");
            if (isMetroManila) {
                const mmOpt = validOptions.find(opt => {
                    const locName = (opt.dataset.locationName || opt.textContent).toUpperCase();
                    return locName.includes("METRO MANILA");
                });
                if (mmOpt) {
                    matchedOptionValue = mmOpt.value;
                }
            }
        }

        if (matchedOptionValue && deliverySelect.value !== matchedOptionValue) {
            deliverySelect.value = matchedOptionValue;
            deliverySelect.dispatchEvent(new Event('change'));
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        loadCart();

        const deliverySelect = document.getElementById('delivery_location');
        if (deliverySelect) {
            deliverySelect.addEventListener('change', function () {
                const rate = selectedDeliveryRate();
                deliveryFee = rate ? Number(rate.price) || 0 : 0;
                renderSummary();
            });
        }

        const addressInput = document.getElementById('cust_address');
        if (addressInput) {
            addressInput.addEventListener('input', autodetectDeliveryRate);
        }

        const form = document.getElementById('checkoutForm');
        if (form) {
            form.addEventListener('submit', submitCheckout);
        }

        loadDeliveryRates()
            .then(() => {
                renderSummary();
                autodetectDeliveryRate();
            })
            .catch((error) => {
                deliveryFee = 0;
                renderSummary();
                setError(error.message || 'Unable to load delivery rates.');
            });
    });
})();
