(function () {
    const CART_KEY = 'solarCart';
    const endpoint = window.SOLAR_MAYA_CHECKOUT_ENDPOINT || 'controllers/ordering/create-maya-checkout.php';
    const imageFallback = 'assets/img/product-placeholder.png';

    let cart = [];
    let deliveryFee = 0;

    const deliveryFees = {
        mm_1_5km: 2000,
        mm_6_10km: 2500,
        mm_11_20km: 4000,
        mm_21_30km: 6000,
        cavite: 4200,
        laguna: 6000,
        batangas: 8500,
        rizal: 7000,
        bulacan: 7000,
        pampanga: 10000,
        tarlac: 10000,
        vismin: 0
    };

    function money(value) {
        const amount = Number(value) || 0;
        return 'PHP ' + amount.toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function loadCart() {
        try {
            const parsed = JSON.parse(localStorage.getItem(CART_KEY) || '[]');
            cart = Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            cart = [];
        }

        cart = cart
            .map((item) => ({
                id: parseInt(item.product_id || item.id, 10) || 0,
                product_id: parseInt(item.product_id || item.id, 10) || 0,
                brand_id: item.brand_id ? parseInt(item.brand_id, 10) : null,
                displayName: item.displayName || item.name || 'Solar Product',
                brandName: item.brandName || '',
                category: item.category || '',
                price: parseFloat(item.price) || 0,
                image_path: item.image_path || item.imagePath || imageFallback,
                quantity: Math.max(parseInt(item.quantity, 10) || 1, parseInt(item.moq, 10) || 1),
                moq: parseInt(item.moq, 10) || 1
            }))
            .filter((item) => item.product_id > 0);
    }

    function saveCart() {
        localStorage.setItem(CART_KEY, JSON.stringify(cart));
    }

    function subtotal() {
        return cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
    }

    function selectedDeliveryLabel() {
        const select = document.getElementById('delivery_location');
        if (!select || !select.value) return '';
        return select.options[select.selectedIndex].text;
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
                cart.splice(index, 1);
                saveCart();
                renderCart();
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

        item.quantity = Math.max(item.moq || 1, item.quantity + change);
        saveCart();
        renderCart();
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

    function formPayload() {
        const name = document.getElementById('cust_name').value.trim();
        const email = document.getElementById('cust_email').value.trim();
        const phone = document.getElementById('cust_phone').value.trim();
        const address = document.getElementById('cust_address').value.trim();
        const deliverySelect = document.getElementById('delivery_location');

        if (!name || !email || !phone || !address || !deliverySelect.value) {
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
            deliveryLocation: deliverySelect.value,
            selected_location_name: selectedDeliveryLabel(),
            calculated_delivery_fee: deliveryFee,
            total_items_amount: subtotal(),
            totalAmount: subtotal() + deliveryFee,
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

        const button = document.getElementById('mayaCheckoutBtn');
        let payload;

        try {
            payload = formPayload();
        } catch (error) {
            setError(error.message);
            return;
        }

        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Opening Maya';

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            });
            const text = await response.text();
            let data;

            try {
                data = JSON.parse(text);
            } catch (error) {
                throw new Error('Checkout server returned an invalid response.');
            }

            if (!response.ok || !data.success) {
                throw new Error(data.message || data.error || 'Maya Checkout could not be created.');
            }

            localStorage.removeItem(CART_KEY);
            window.location.href = data.checkoutUrl || data.paymentUrl;
        } catch (error) {
            setError(error.message || 'Payment could not be started.');
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-lock me-2"></i>Pay Securely with Maya';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        loadCart();
        renderCart();

        const deliverySelect = document.getElementById('delivery_location');
        deliverySelect.addEventListener('change', function () {
            deliveryFee = deliveryFees[this.value] || 0;
            renderSummary();
        });

        document.getElementById('checkoutForm').addEventListener('submit', submitCheckout);
    });
})();
