<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

$scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
$sitePrefix = stripos($scriptName, '/SolarPower-Energy-Corporation/') !== false ? '/SolarPower-Energy-Corporation' : '';
$deliveryRatesApiUrl = $sitePrefix . '/views/staff/delivery-rates-api.php';
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

        .rate-modal-backdrop {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(8, 18, 13, 0.58);
            z-index: 1000;
        }

        .rate-modal-backdrop.show {
            display: flex;
        }

        .rate-modal-card {
            width: min(520px, 100%);
            background: #fff;
            border-radius: 14px;
            border: 1px solid rgba(13, 92, 58, 0.18);
            box-shadow: 0 24px 70px rgba(8, 18, 13, 0.26);
            overflow: hidden;
        }

        .rate-modal-head {
            padding: 17px 20px;
            border-bottom: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .rate-modal-head h3 {
            margin: 0;
            font-size: 18px;
        }

        .rate-modal-close {
            width: 34px;
            min-height: 34px;
            padding: 0;
            border-radius: 999px;
            background: #f3f6f4;
            color: var(--muted);
        }

        .rate-modal-body {
            padding: 20px;
        }

        .rate-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding-top: 6px;
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

    <div class="rate-modal-backdrop" id="editRateModal" aria-hidden="true">
        <section class="rate-modal-card" role="dialog" aria-modal="true" aria-labelledby="editRateModalTitle">
            <div class="rate-modal-head">
                <h3 id="editRateModalTitle"><i class="fas fa-pen"></i> Edit Delivery Rate</h3>
                <button class="rate-modal-close" type="button" id="closeEditRateModal" aria-label="Close edit delivery rate modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="rate-modal-body">
                <form id="editRateForm">
                    <input type="hidden" id="edit_rate_id" name="id">
                    <input type="hidden" id="edit_origin_address" name="origin_address" value="Madrigal Business Park, Alabang, Muntinlupa">

                    <div class="field">
                        <label for="edit_rate_type">Rate type</label>
                        <select id="edit_rate_type" name="rate_type" required>
                            <option value="km_range">Metro Manila km range</option>
                            <option value="province">Luzon province</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="edit_location_name">Location name</label>
                        <input id="edit_location_name" name="location_name" maxlength="100" required>
                    </div>

                    <div class="field">
                        <label for="edit_price">Delivery fee</label>
                        <input id="edit_price" name="price" type="number" min="0" step="0.01" required>
                    </div>

                    <div class="rate-modal-actions">
                        <button class="btn-secondary" type="button" id="cancelEditRate">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button class="btn-primary" type="submit">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <script>
        const apiUrl = <?php echo json_encode($deliveryRatesApiUrl); ?>;
        const form = document.getElementById('rateForm');
        const editForm = document.getElementById('editRateForm');
        const editRateModal = document.getElementById('editRateModal');
        const table = document.getElementById('ratesTable');
        const notice = document.getElementById('rateNotice');
        let rates = [];

        async function readApiJson(response) {
            const text = await response.text();
            let data = {};

            try {
                data = text ? JSON.parse(text) : {};
            } catch (error) {
                throw new Error('Delivery Rates API returned an invalid response. Please make sure views/staff/delivery-rates-api.php is uploaded.');
            }

            if (!response.ok || !data.success) {
                throw new Error(data.message || `Delivery Rates API failed with HTTP ${response.status}.`);
            }

            return data;
        }

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

        function openEditRateModal(rate) {
            document.getElementById('edit_rate_id').value = rate.id;
            document.getElementById('edit_rate_type').value = rate.rate_type;
            document.getElementById('edit_location_name').value = rate.location_name;
            document.getElementById('edit_price').value = Number(rate.price).toFixed(2);
            document.getElementById('edit_origin_address').value = rate.origin_address || 'Madrigal Business Park, Alabang, Muntinlupa';
            editRateModal.classList.add('show');
            editRateModal.setAttribute('aria-hidden', 'false');
            document.getElementById('edit_location_name').focus();
        }

        function closeEditRateModal() {
            editRateModal.classList.remove('show');
            editRateModal.setAttribute('aria-hidden', 'true');
            editForm.reset();
        }

        async function saveRate(payload) {
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            });

            return readApiJson(response);
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
            const data = await readApiJson(response);

            rates = data.rates || [];
            renderRates();
        }

        window.editRate = function (id) {
            const rate = rates.find((item) => item.id === id);
            if (!rate) return;

            openEditRateModal(rate);
        };

        window.deleteRate = async function (id) {
            if (!confirm('Delete this delivery rate?')) return;

            try {
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ action: 'delete', id })
                });
                const data = await readApiJson(response);

                showNotice(data.message || 'Delivery rate deleted.', 'ok');
                await loadRates();
            } catch (error) {
                showNotice(error.message || 'Unable to delete rate.', 'error');
            }
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

            try {
                const data = await saveRate(payload);

                resetForm();
                showNotice(data.message || 'Delivery rate saved.', 'ok');
                await loadRates();
            } catch (error) {
                showNotice(error.message || 'Unable to save rate.', 'error');
            }
        });

        editForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const payload = {
                action: 'save',
                id: document.getElementById('edit_rate_id').value,
                origin_address: document.getElementById('edit_origin_address').value,
                rate_type: document.getElementById('edit_rate_type').value,
                location_name: document.getElementById('edit_location_name').value,
                price: document.getElementById('edit_price').value
            };

            try {
                const data = await saveRate(payload);
                closeEditRateModal();
                showNotice(data.message || 'Delivery rate updated.', 'ok');
                await loadRates();
            } catch (error) {
                showNotice(error.message || 'Unable to update rate.', 'error');
            }
        });

        document.getElementById('resetBtn').addEventListener('click', resetForm);
        document.getElementById('refreshBtn').addEventListener('click', () => loadRates().catch((error) => showNotice(error.message, 'error')));
        document.getElementById('closeEditRateModal').addEventListener('click', closeEditRateModal);
        document.getElementById('cancelEditRate').addEventListener('click', closeEditRateModal);
        editRateModal.addEventListener('click', (event) => {
            if (event.target === editRateModal) {
                closeEditRateModal();
            }
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && editRateModal.classList.contains('show')) {
                closeEditRateModal();
            }
        });

        loadRates().catch((error) => showNotice(error.message, 'error'));
    </script>
</body>
</html>
