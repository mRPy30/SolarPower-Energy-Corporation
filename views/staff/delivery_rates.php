<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Rates - SolarPower</title>
    <link rel="icon" type="image/png" href="../../assets/img/icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --green: #0d5c3a;
            --green-dark: #083f29;
            --amber: #f3a712;
            --ink: #18251f;
            --muted: #6b756f;
            --line: #dfe8e2;
            --bg: #f5f7f2;
            --panel: #ffffff;
            --danger: #b42318;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
        }

        .rates-shell {
            padding: 20px;
        }

        .rates-grid {
            display: grid;
            grid-template-columns: minmax(280px, 380px) minmax(0, 1fr);
            gap: 18px;
            align-items: start;
        }

        .panel {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: 0 10px 24px rgba(24, 37, 31, 0.05);
        }

        .panel-head {
            padding: 16px 18px;
            border-bottom: 1px solid var(--line);
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
        }

        .panel-head h1,
        .panel-head h2 {
            margin: 0;
            font-size: 17px;
        }

        .panel-body {
            padding: 18px;
        }

        label {
            display: block;
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 7px;
        }

        input,
        select {
            width: 100%;
            min-height: 42px;
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 9px 11px;
            color: var(--ink);
            font-size: 14px;
            background: #fff;
        }

        .field {
            margin-bottom: 14px;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        button {
            min-height: 40px;
            border: 0;
            border-radius: 6px;
            padding: 0 14px;
            cursor: pointer;
            font-weight: 700;
        }

        .btn-primary {
            background: var(--green);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--green-dark);
        }

        .btn-secondary {
            background: #eef4f0;
            color: var(--ink);
        }

        .icon-btn {
            width: 36px;
            min-height: 36px;
            display: inline-grid;
            place-items: center;
            padding: 0;
            background: #fff;
            color: var(--ink);
            border: 1px solid var(--line);
        }

        .icon-btn.delete {
            color: var(--danger);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 13px 12px;
            border-bottom: 1px solid var(--line);
            text-align: left;
            vertical-align: middle;
            font-size: 14px;
        }

        th {
            color: var(--muted);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            background: #f8faf8;
        }

        .type-pill {
            display: inline-flex;
            align-items: center;
            min-height: 24px;
            padding: 0 9px;
            border-radius: 999px;
            background: #e8f4ee;
            color: var(--green);
            font-size: 12px;
            font-weight: 700;
        }

        .empty {
            padding: 34px;
            text-align: center;
            color: var(--muted);
        }

        .notice {
            display: none;
            margin-bottom: 14px;
            padding: 11px 12px;
            border-radius: 6px;
            font-size: 14px;
        }

        .notice.show {
            display: block;
        }

        .notice.ok {
            background: #e8f4ee;
            color: var(--green);
        }

        .notice.error {
            background: #fdecec;
            color: var(--danger);
        }

        @media (max-width: 860px) {
            .rates-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <main class="rates-shell">
        <div class="rates-grid">
            <section class="panel">
                <div class="panel-head">
                    <h1><i class="fas fa-truck me"></i> Delivery Rate</h1>
                </div>
                <div class="panel-body">
                    <div class="notice" id="rateNotice"></div>
                    <form id="rateForm">
                        <input type="hidden" id="rate_id" name="id">
                        <input type="hidden" id="origin_address" name="origin_address" value="Madrigal Business Park, Alabang, Muntinlupa">

                        <div class="field">
                            <label for="rate_type">Rate type</label>
                            <select id="rate_type" name="rate_type" required>
                                <option value="km_range">Metro Manila km range</option>
                                <option value="province">Luzon province</option>
                            </select>
                        </div>

                        <div class="field">
                            <label for="location_name">Location name</label>
                            <input id="location_name" name="location_name" maxlength="100" placeholder="Cavite or Metro Manila 1-5 km" required>
                        </div>

                        <div class="field">
                            <label for="price">Delivery fee</label>
                            <input id="price" name="price" type="number" min="0" step="0.01" placeholder="0.00" required>
                        </div>

                        <div class="actions">
                            <button class="btn-primary" type="submit">
                                <i class="fas fa-save"></i> Save Rate
                            </button>
                            <button class="btn-secondary" type="button" id="resetBtn">
                                <i class="fas fa-rotate-left"></i> Clear
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <section class="panel">
                <div class="panel-head">
                    <h2>Checkout Delivery Rates</h2>
                    <button class="icon-btn" type="button" id="refreshBtn" title="Refresh rates">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                <div class="panel-body">
                    <table>
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Location</th>
                                <th>Fee</th>
                                <th>Updated</th>
                                <th style="width: 92px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="ratesTable">
                            <tr>
                                <td class="empty" colspan="5">Loading rates...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <script>
        const apiUrl = 'delivery-rates-api.php';
        const form = document.getElementById('rateForm');
        const table = document.getElementById('ratesTable');
        const notice = document.getElementById('rateNotice');
        let rates = [];

        function money(value) {
            return 'PHP ' + (Number(value) || 0).toLocaleString('en-PH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function showNotice(message, type) {
            notice.textContent = message;
            notice.className = 'notice show ' + (type || 'ok');
            window.clearTimeout(showNotice.timer);
            showNotice.timer = window.setTimeout(() => {
                notice.className = 'notice';
            }, 3500);
        }

        function resetForm() {
            form.reset();
            document.getElementById('rate_id').value = '';
            document.getElementById('origin_address').value = 'Madrigal Business Park, Alabang, Muntinlupa';
        }

        function renderRates() {
            if (!rates.length) {
                table.innerHTML = '<tr><td class="empty" colspan="5">No delivery rates configured yet.</td></tr>';
                return;
            }

            table.innerHTML = rates.map((rate) => `
                <tr>
                    <td><span class="type-pill">${rate.rate_type === 'km_range' ? 'KM range' : 'Province'}</span></td>
                    <td>${escapeHtml(rate.location_name)}</td>
                    <td><strong>${money(rate.price)}</strong></td>
                    <td>${escapeHtml(rate.updated_at || '')}</td>
                    <td>
                        <button class="icon-btn" type="button" title="Edit" onclick="editRate(${rate.id})"><i class="fas fa-pen"></i></button>
                        <button class="icon-btn delete" type="button" title="Delete" onclick="deleteRate(${rate.id})"><i class="fas fa-trash-alt"></i></button>
                    </td>
                </tr>
            `).join('');
        }

        function escapeHtml(value) {
            return String(value || '').replace(/[&<>"']/g, (char) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char]));
        }

        async function loadRates() {
            table.innerHTML = '<tr><td class="empty" colspan="5">Loading rates...</td></tr>';
            const response = await fetch(apiUrl, { credentials: 'same-origin' });
            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Unable to load rates.');
            }

            rates = data.rates || [];
            renderRates();
        }

        window.editRate = function (id) {
            const rate = rates.find((item) => item.id === id);
            if (!rate) return;

            document.getElementById('rate_id').value = rate.id;
            document.getElementById('rate_type').value = rate.rate_type;
            document.getElementById('location_name').value = rate.location_name;
            document.getElementById('price').value = Number(rate.price).toFixed(2);
            document.getElementById('origin_address').value = rate.origin_address || 'Madrigal Business Park, Alabang, Muntinlupa';
        };

        window.deleteRate = async function (id) {
            if (!confirm('Delete this delivery rate?')) return;

            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ action: 'delete', id })
            });
            const data = await response.json();

            if (!response.ok || !data.success) {
                showNotice(data.message || 'Unable to delete rate.', 'error');
                return;
            }

            showNotice(data.message || 'Delivery rate deleted.', 'ok');
            await loadRates();
        };

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const payload = {
                action: 'save',
                id: document.getElementById('rate_id').value,
                origin_address: document.getElementById('origin_address').value,
                rate_type: document.getElementById('rate_type').value,
                location_name: document.getElementById('location_name').value,
                price: document.getElementById('price').value
            };

            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            });
            const data = await response.json();

            if (!response.ok || !data.success) {
                showNotice(data.message || 'Unable to save rate.', 'error');
                return;
            }

            resetForm();
            showNotice(data.message || 'Delivery rate saved.', 'ok');
            await loadRates();
        });

        document.getElementById('resetBtn').addEventListener('click', resetForm);
        document.getElementById('refreshBtn').addEventListener('click', () => loadRates().catch((error) => showNotice(error.message, 'error')));

        loadRates().catch((error) => showNotice(error.message, 'error'));
    </script>
</body>
</html>
