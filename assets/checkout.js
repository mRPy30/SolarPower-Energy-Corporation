(function () {
    const CART_KEY = 'solarCart';
    const APP_BASE = window.SOLAR_APP_BASE || '';
    const endpoint = window.SOLAR_MAYA_CHECKOUT_ENDPOINT || 'controllers/ordering/create-maya-checkout.php';
    const ratesEndpoint = window.SOLAR_DELIVERY_RATES_ENDPOINT || 'controllers/checkout/get-delivery-rates.php';
    const rateCheckEndpoint = window.SOLAR_DELIVERY_RATE_ENDPOINT || 'controllers/checkout/get-delivery-rate.php';
    const psgcPublicBase = 'https://psgc.gitlab.io/api';
    const imageFallback = 'assets/img/product-placeholder.png';

    let cart = [];
    let deliveryRates = [];
    let deliveryFee = 0;
    let logisticsAvailable = false;
    let logisticsBlocked = false;

    function money(value) {
        const amount = Number(value) || 0;
        return 'PHP ' + amount.toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function optionText(select) {
        if (!select || !select.value || select.selectedIndex < 0) return '';
        return select.options[select.selectedIndex].textContent.trim();
    }

    function deliveryLocationNameFromSelect() {
        const select = document.getElementById('addr_province_region');
        if (!select || !select.value || select.selectedIndex < 0) return '';

        const option = select.options[select.selectedIndex];
        return option.dataset.deliveryName || option.textContent.trim();
    }

    function syncCombinedAddress() {
        const line1 = document.getElementById('addr_line1')?.value.trim() || '';
        const provinceRegion = optionText(document.getElementById('addr_province_region'));
        const city = optionText(document.getElementById('addr_city'));
        const barangay = optionText(document.getElementById('addr_barangay'));
        const hidden = document.getElementById('cust_address');

        if (hidden) {
            hidden.value = [line1, barangay, city, provinceRegion].filter(Boolean).join(', ');
        }

        return hidden ? hidden.value.trim() : '';
    }

    async function fetchJson(url) {
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json'
            }
        });
        if (!response.ok) {
            throw new Error('Unable to load address locations.');
        }
        return response.json();
    }

    async function publicPsgc(path) {
        return fetchJson(psgcPublicBase + path);
    }

    function fillSelect(select, placeholder, rows, mapper) {
        if (!select) return;

        select.innerHTML = '';
        const option = document.createElement('option');
        option.value = '';
        option.textContent = placeholder;
        select.appendChild(option);

        rows.forEach((row) => {
            const mapped = mapper(row);
            if (!mapped || !mapped.value || !mapped.label) return;

            const item = document.createElement('option');
            item.value = mapped.value;
            item.textContent = mapped.label;
            Object.keys(mapped.dataset || {}).forEach((key) => {
                item.dataset[key] = mapped.dataset[key];
            });
            select.appendChild(item);
        });

        select.disabled = rows.length === 0;
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

    function mayaButton() {
        return document.getElementById('maya-submit-btn') || document.getElementById('mayaCheckoutBtn');
    }

    function checkoutDetailsComplete() {
        const name = document.getElementById('cust_name')?.value.trim() || '';
        const email = document.getElementById('cust_email')?.value.trim() || '';
        const phone = document.getElementById('cust_phone')?.value.trim() || '';
        const address = syncCombinedAddress();
        const provinceRegion = document.getElementById('addr_province_region')?.value || '';
        const city = document.getElementById('addr_city')?.value || '';
        const barangay = document.getElementById('addr_barangay')?.value || '';

        return Boolean(cart.length && name && email && phone && address && provinceRegion && city && barangay && selectedDeliveryRate() && logisticsAvailable);
    }

    function updateCheckoutButtonState() {
        const button = mayaButton();
        if (!button) return;

        const ready = checkoutDetailsComplete();
        button.disabled = !ready;
        button.setAttribute('aria-disabled', ready ? 'false' : 'true');
        button.title = ready ? 'Pay securely with Maya' : 'Complete checkout details to continue';
        button.classList.toggle('d-none', logisticsBlocked);
    }

    function normalizeLocationText(value) {
        return String(value || '')
            .toUpperCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/Ñ/g, 'N')
            .replace(/\s+/g, ' ')
            .trim();
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

    function setLogisticsWarning(visible) {
        const warning = document.getElementById('logistics-warning');
        if (warning) {
            warning.classList.toggle('d-none', !visible);
        }
    }

    function selectedAddressPayload() {
        return {
            province_region: optionText(document.getElementById('addr_province_region')),
            province_region_code: document.getElementById('addr_province_region')?.value || '',
            city: optionText(document.getElementById('addr_city')),
            city_code: document.getElementById('addr_city')?.value || '',
            barangay: optionText(document.getElementById('addr_barangay')),
            barangay_code: document.getElementById('addr_barangay')?.value || ''
        };
    }

    function setDeliveryText(value) {
        const deliveryEl = document.getElementById('delivery-fee') || document.getElementById('summaryDelivery');
        if (deliveryEl) {
            deliveryEl.textContent = value;
        }
    }

    function setTotalText(value) {
        const totalEl = document.getElementById('total-price') || document.getElementById('summaryTotal');
        if (totalEl) {
            totalEl.textContent = value;
        }
    }

    async function checkDeliveryAvailability() {
        const provinceRegion = document.getElementById('addr_province_region')?.value || '';
        const city = document.getElementById('addr_city')?.value || '';
        const barangay = document.getElementById('addr_barangay')?.value || '';

        if (!provinceRegion || !city || !barangay) {
            logisticsAvailable = false;
            logisticsBlocked = false;
            setLogisticsWarning(false);
            setSelectedDeliveryRate('');
            updateCheckoutButtonState();
            return;
        }

        try {
            const response = await fetch(rateCheckEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify(selectedAddressPayload())
            });
            const data = await response.json();
            const rate = data.rate || null;
            const price = Number(data.delivery_fee ?? rate?.price ?? 0);

            if (!response.ok || !data.success || !rate || price <= 0) {
                logisticsAvailable = false;
                logisticsBlocked = true;
                deliveryFee = 0;
                setSelectedDeliveryRate('');
                setDeliveryText('Unavailable');
                setTotalText(money(subtotal()));
                setLogisticsWarning(true);
                updateCheckoutButtonState();
                return;
            }

            if (!deliveryRates.some((item) => item.id === parseInt(rate.id, 10))) {
                deliveryRates.push({
                    id: parseInt(rate.id, 10),
                    origin_address: rate.origin_address || '',
                    rate_type: rate.rate_type || 'province',
                    location_name: rate.location_name || '',
                    price: price
                });
            }

            const hiddenSelect = document.getElementById('delivery_location');
            if (hiddenSelect && !Array.from(hiddenSelect.options).some((option) => option.value === String(rate.id))) {
                const option = document.createElement('option');
                option.value = String(rate.id);
                option.dataset.price = String(price);
                option.dataset.locationName = rate.location_name;
                option.dataset.rateType = rate.rate_type || '';
                option.textContent = rate.location_name + ' - ' + money(price);
                hiddenSelect.appendChild(option);
            }

            logisticsAvailable = true;
            logisticsBlocked = false;
            setLogisticsWarning(false);
            setSelectedDeliveryRate(rate.id);
        } catch (error) {
            logisticsAvailable = false;
            logisticsBlocked = true;
            deliveryFee = 0;
            setSelectedDeliveryRate('');
            setDeliveryText('Unavailable');
            setTotalText(money(subtotal()));
            setLogisticsWarning(true);
            updateCheckoutButtonState();
        }
    }

    function renderSummary() {
        const itemSubtotal = subtotal();
        const total = itemSubtotal + deliveryFee;

        const subtotalEl = document.getElementById('items-subtotal') || document.getElementById('summarySubtotal');
        if (subtotalEl) {
            subtotalEl.textContent = money(itemSubtotal);
        }
        setDeliveryText(logisticsBlocked ? 'Unavailable' : money(deliveryFee));
        setTotalText(money(total));

        const badge = document.getElementById('itemCountBadge');
        if (badge) {
            const count = cart.reduce((sum, item) => sum + item.quantity, 0);
            badge.textContent = count + (count === 1 ? ' item' : ' items');
        }

        updateCheckoutButtonState();
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

    function updateDeliveryFee() {
        const rate = selectedDeliveryRate();
        const base_delivery_fee = rate ? Number(rate.price) || 0 : 0;
        
        let selectedTier = 'SunSpeed Standard';
        if (window.jQuery) {
            selectedTier = window.jQuery('input[name="delivery_option"]:checked').val() || 'SunSpeed Standard';
        } else {
            const checkedRadio = document.querySelector('input[name="delivery_option"]:checked');
            selectedTier = checkedRadio ? checkedRadio.value : 'SunSpeed Standard';
        }
        
        let optionFee = 0.00;
        let finalDelivery = base_delivery_fee;
        
        if (selectedTier === 'Eco-Saver Shipping') {
            optionFee = -(base_delivery_fee * 0.15);
            finalDelivery = base_delivery_fee * 0.85;
        } else if (selectedTier === 'SolarFlash Express') {
            optionFee = base_delivery_fee * 0.40;
            finalDelivery = base_delivery_fee * 1.40;
        }
        
        deliveryFee = finalDelivery;
        
        // Keep hidden inputs up to date
        const hiddenTierInput = document.getElementById('delivery_service_tier');
        if (hiddenTierInput) {
            hiddenTierInput.value = selectedTier;
        }
        const calculatedDeliveryFeeEl = document.getElementById('calculated_delivery_fee');
        if (calculatedDeliveryFeeEl) {
            calculatedDeliveryFeeEl.value = deliveryFee.toFixed(2);
        }
        
        // DOM update
        const optionFeeEl = document.getElementById('delivery-option-fee');
        if (optionFeeEl) {
            optionFeeEl.textContent = logisticsBlocked ? 'Unavailable' : money(optionFee);
        }
        const deliveryFeeEl = document.getElementById('delivery-fee');
        if (deliveryFeeEl) {
            deliveryFeeEl.textContent = logisticsBlocked ? 'Unavailable' : money(finalDelivery);
        }
    }

    function setSelectedDeliveryRate(rateId) {
        const select = document.getElementById('delivery_location');
        if (!select) return;

        select.value = rateId ? String(rateId) : '';
        updateDeliveryFee();
        renderSummary();
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
            renderSummary();
            return;
        }

        updateCheckoutButtonState();

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
        const address = syncCombinedAddress();

        const name = document.getElementById('cust_name').value.trim();
        const email = document.getElementById('cust_email').value.trim();
        const phone = document.getElementById('cust_phone').value.trim();
        const provinceRegion = document.getElementById('addr_province_region')?.value || '';
        const city = document.getElementById('addr_city')?.value || '';
        const barangay = document.getElementById('addr_barangay')?.value || '';
        const rate = selectedDeliveryRate();

        if (!name || !email || !phone || !address || !provinceRegion || !city || !barangay) {
            throw new Error('Please complete all required checkout details.');
        }

        if (!rate) {
            throw new Error('Please enter an address that matches a configured delivery location.');
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
            delivery_service_tier: document.getElementById('delivery_service_tier')?.value || 'SunSpeed Standard',
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
        syncCombinedAddress();
        await checkDeliveryAvailability();

        const name = document.getElementById('cust_name')?.value.trim();
        const email = document.getElementById('cust_email')?.value.trim();
        const phone = document.getElementById('cust_phone')?.value.trim();
        const address = document.getElementById('cust_address')?.value.trim();
        const provinceRegion = document.getElementById('addr_province_region')?.value || '';
        const city = document.getElementById('addr_city')?.value || '';
        const barangay = document.getElementById('addr_barangay')?.value || '';
        const rate = selectedDeliveryRate();

        if (!name || !email || !phone || !address || !provinceRegion || !city || !barangay) {
            event.preventDefault();
            setError('Please complete all required checkout details.');
            return;
        }

        if (!rate) {
            event.preventDefault();
            setError('Please enter an address that matches a configured delivery location.');
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
        const button = mayaButton();
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

    function autodetectDeliveryRate() {
        const addressInput = document.getElementById('cust_address');
        if (!addressInput || deliveryRates.length === 0) return;

        const text = normalizeLocationText(addressInput.value);
        if (!text) {
            setSelectedDeliveryRate('');
            return;
        }

        let matchedRate = null;

        for (const rate of deliveryRates) {
            const locationName = normalizeLocationText(rate.location_name);
            if (!locationName || locationName.includes('METRO MANILA')) continue;

            if (text.includes(locationName)) {
                matchedRate = rate;
                break;
            }
        }

        if (!matchedRate) {
            const isMetroManila = [
                'METRO MANILA',
                'NCR',
                'MANILA',
                'MUNTINLUPA',
                'LAS PINAS',
                'MAKATI',
                'QUEZON CITY',
                'TAGUIG',
                'PASIG',
                'CALOOCAN',
                'PASAY',
                'PARANAQUE',
                'MARIKINA',
                'VALENZUELA',
                'MALABON',
                'NAVOTAS',
                'MANDALUYONG',
                'SAN JUAN'
            ].some((keyword) => text.includes(keyword));

            if (isMetroManila) {
                matchedRate = deliveryRates.find((rate) => normalizeLocationText(rate.location_name).includes('METRO MANILA')) || null;
            }
        }

        setSelectedDeliveryRate(matchedRate ? matchedRate.id : '');
    }

    async function loadProvinceRegionOptions() {
        const select = document.getElementById('addr_province_region');
        if (!select) return;

        select.disabled = true;
        select.innerHTML = '<option value="">Loading locations...</option>';

        const [regions, provinces] = await Promise.all([
            publicPsgc('/regions/'),
            publicPsgc('/provinces/')
        ]);

        const ncrRows = regions
            .filter((region) => normalizeLocationText(region.name).includes('NATIONAL CAPITAL') || normalizeLocationText(region.regionName).includes('NATIONAL CAPITAL'))
            .map((region) => ({
                code: region.code,
                name: 'Metro Manila (NCR)',
                type: 'region',
                deliveryName: 'Metro Manila'
            }));

        const provinceRows = provinces.map((province) => ({
            code: province.code,
            name: province.name,
            type: 'province',
            deliveryName: province.name
        }));

        const rows = ncrRows.concat(provinceRows).sort((a, b) => {
            if (a.deliveryName === 'Metro Manila') return -1;
            if (b.deliveryName === 'Metro Manila') return 1;
            return a.name.localeCompare(b.name);
        });

        fillSelect(select, 'Select province/region', rows, (row) => ({
            value: row.code,
            label: row.name,
            dataset: {
                type: row.type,
                deliveryName: row.deliveryName
            }
        }));
    }

    async function loadCityOptions() {
        const provinceRegion = document.getElementById('addr_province_region');
        const city = document.getElementById('addr_city');
        const barangay = document.getElementById('addr_barangay');
        if (!provinceRegion || !city || !barangay) return;

        fillSelect(city, 'Loading cities/municipalities...', [], () => null);
        city.disabled = true;
        fillSelect(barangay, 'Select city/municipality first', [], () => null);
        barangay.disabled = true;

        if (!provinceRegion.value) {
            fillSelect(city, 'Select province/region first', [], () => null);
            syncCombinedAddress();
            autodetectDeliveryRate();
            updateCheckoutButtonState();
            return;
        }

        const type = provinceRegion.options[provinceRegion.selectedIndex]?.dataset.type || 'province';
        const path = type === 'region'
            ? '/regions/' + encodeURIComponent(provinceRegion.value) + '/cities-municipalities/'
            : '/provinces/' + encodeURIComponent(provinceRegion.value) + '/cities-municipalities/';
        const rows = await publicPsgc(path);

        fillSelect(city, 'Select city/municipality', rows, (row) => ({
            value: row.code,
            label: row.name
        }));
        syncCombinedAddress();
        autodetectDeliveryRate();
        updateCheckoutButtonState();
    }

    async function loadBarangayOptions() {
        const city = document.getElementById('addr_city');
        const barangay = document.getElementById('addr_barangay');
        if (!city || !barangay) return;

        fillSelect(barangay, 'Loading barangays...', [], () => null);
        barangay.disabled = true;

        if (!city.value) {
            fillSelect(barangay, 'Select city/municipality first', [], () => null);
            syncCombinedAddress();
            updateCheckoutButtonState();
            return;
        }

        const rows = await publicPsgc('/cities-municipalities/' + encodeURIComponent(city.value) + '/barangays/');
        fillSelect(barangay, 'Select barangay', rows, (row) => ({
            value: row.code,
            label: row.name
        }));
        syncCombinedAddress();
        checkDeliveryAvailability();
    }

    async function initPsgcAddressFields() {
        const line1 = document.getElementById('addr_line1');
        const provinceRegion = document.getElementById('addr_province_region');
        const city = document.getElementById('addr_city');
        const barangay = document.getElementById('addr_barangay');
        if (!line1 || !provinceRegion || !city || !barangay) return;

        line1.addEventListener('input', function () {
            syncCombinedAddress();
            updateCheckoutButtonState();
        });
        provinceRegion.addEventListener('change', function () {
            loadCityOptions().catch((error) => {
                setError(error.message || 'Unable to load cities/municipalities.');
            });
            updateCheckoutButtonState();
        });
        city.addEventListener('change', function () {
            loadBarangayOptions().catch((error) => {
                setError(error.message || 'Unable to load barangays.');
            });
            updateCheckoutButtonState();
        });
        barangay.addEventListener('change', function () {
            syncCombinedAddress();
            checkDeliveryAvailability();
        });

        await loadProvinceRegionOptions();
        syncCombinedAddress();
        autodetectDeliveryRate();
        updateCheckoutButtonState();
    }

    function autodetectDeliveryRate() {
        checkDeliveryAvailability();
    }

    document.addEventListener('DOMContentLoaded', function () {
        loadCart();

        const deliverySelect = document.getElementById('delivery_location');
        if (deliverySelect) {
            deliverySelect.addEventListener('change', function () {
                updateDeliveryFee();
                renderSummary();
            });
        }

        // jQuery calculation loop / change event listener
        if (window.jQuery) {
            const $ = window.jQuery;
            $('input[name="delivery_option"]').on('change', function () {
                const rate = selectedDeliveryRate();
                const base_delivery_fee = rate ? Number(rate.price) || 0 : 0;
                const selectedTier = $(this).val() || 'SunSpeed Standard';
                
                let optionFee = 0.00;
                let finalDelivery = base_delivery_fee;
                
                if (selectedTier === 'Eco-Saver Shipping') {
                    optionFee = -(base_delivery_fee * 0.15);
                    finalDelivery = base_delivery_fee * 0.85;
                } else if (selectedTier === 'SolarFlash Express') {
                    optionFee = base_delivery_fee * 0.40;
                    finalDelivery = base_delivery_fee * 1.40;
                }
                
                deliveryFee = finalDelivery;
                
                // Format and update option fee
                $('#delivery-option-fee').text(logisticsBlocked ? 'Unavailable' : money(optionFee));
                // Format and update final delivery
                $('#delivery-fee').text(logisticsBlocked ? 'Unavailable' : money(finalDelivery));
                
                // Recalculate grand total
                const items_subtotal = subtotal();
                const grandTotal = items_subtotal + finalDelivery;
                $('#total-price').text(money(grandTotal));
                
                // Keep hidden inputs up to date
                $('#delivery_service_tier').val(selectedTier);
                $('#calculated_delivery_fee').val(deliveryFee.toFixed(2));
                
                updateCheckoutButtonState();
            });
        } else {
            // Fallback vanilla JS listener
            document.querySelectorAll('input[name="delivery_option"]').forEach(radio => {
                radio.addEventListener('change', function () {
                    updateDeliveryFee();
                    renderSummary();
                });
            });
        }

        initPsgcAddressFields().catch((error) => {
            setError(error.message || 'Unable to load address locations.');
        });

        ['cust_name', 'cust_email', 'cust_phone'].forEach((id) => {
            const field = document.getElementById(id);
            if (field) {
                field.addEventListener('input', updateCheckoutButtonState);
                field.addEventListener('change', updateCheckoutButtonState);
            }
        });

        const form = document.getElementById('checkoutForm');
        if (form) {
            form.addEventListener('submit', submitCheckout);
        }

        loadDeliveryRates()
            .then(() => {
                renderSummary();
                autodetectDeliveryRate();
                updateCheckoutButtonState();
            })
            .catch((error) => {
                deliveryFee = 0;
                renderSummary();
                updateCheckoutButtonState();
                setError(error.message || 'Unable to load delivery rates.');
            });
    });
})();
