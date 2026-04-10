<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../login.php");
    exit;
}
include "../../config/dbconn.php";

$user_id = $_SESSION['user_id'];
$firstName = $_SESSION['firstName'] ?? 'Staff';
$lastName = $_SESSION['lastName'] ?? '';
$fullName = trim($firstName . ' ' . $lastName);

// Fetch all products grouped by category
$products_query = "SELECT id, displayName, brandName, price, category, stockQuantity, warranty FROM product ORDER BY category, displayName";
$products_result = mysqli_query($conn, $products_query);
$products = [];
$categories = [];
while ($row = mysqli_fetch_assoc($products_result)) {
    $products[] = $row;
    $cat = $row['category'];
    if (!in_array($cat, $categories)) $categories[] = $cat;
}

// Fetch staff for officer dropdown
$officers_result = mysqli_query($conn, "SELECT id, firstName, lastName, UPPER(firstName) as officer_code FROM staff ORDER BY firstName");
$officers = [];
while ($row = mysqli_fetch_assoc($officers_result)) {
    $officers[] = $row;
}

// Get next quotation number
$year = date('Y');
$q_result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM quotations");
$q_count = mysqli_fetch_assoc($q_result)['cnt'] + 1;
$default_q_number = 'Q-' . $year . '-' . sprintf('%03d', $q_count);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Quotation | SolarPower</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root {
    --sun: #F59E0B;
    --sun-light: #FEF3C7;
    --sky: #0EA5E9;
    --sky-dark: #0369A1;
    --earth: #1E293B;
    --earth-mid: #334155;
    --earth-light: #64748B;
    --surface: #F8FAFC;
    --white: #FFFFFF;
    --border: #E2E8F0;
    --green: #10B981;
    --red: #EF4444;
    --shadow-sm: 0 1px 3px rgba(0,0,0,.08);
    --shadow-md: 0 4px 16px rgba(0,0,0,.1);
    --shadow-lg: 0 8px 32px rgba(0,0,0,.12);
    --radius: 12px;
}



/* ── HEADER ─────────────────────────────────── */
.page-header {
    background: linear-gradient(135deg, var(--earth) 0%, var(--earth-mid) 100%);
    padding: 20px 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: var(--shadow-md);
}
.header-left { display:flex; align-items:center; gap:16px; }
.logo-badge {
    width:44px; height:44px;
    background: linear-gradient(135deg, var(--sun), #F97316);
    border-radius:10px;
    display:flex; align-items:center; justify-content:center;
    font-size:20px; font-weight:800; color:white;
}
.page-title h1 { font-size:20px; font-weight:700; color:white; }
.page-title p { font-size:13px; color:rgba(255,255,255,.6); }
.header-actions { display:flex; gap:10px; }

/* ── BUTTONS ─────────────────────────────────── */
.btn {
    padding: 10px 20px;
    border-radius: 8px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all .2s;
    text-decoration: none;
}
.btn-primary { background: var(--sun); color: white; }
.btn-primary:hover { background: #D97706; transform: translateY(-1px); box-shadow: var(--shadow-md); }
.btn-secondary { background: rgba(255,255,255,.1); color: white; border: 1px solid rgba(255,255,255,.2); }
.btn-secondary:hover { background: rgba(255,255,255,.2); }
.btn-success { background: var(--green); color: white; }
.btn-success:hover { background: #059669; }
.btn-ghost { background: transparent; border: 1px solid var(--border); color: var(--earth-light); }
.btn-ghost:hover { background: var(--border); color: var(--earth); }
.btn-danger { background: var(--red); color:white; padding:6px 12px; font-size:12px; }
.btn-sm { padding:6px 12px; font-size:12px; }


/* ── CARD ─────────────────────────────────── */
.card {
    background: var(--white);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}
.card-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(to right, var(--surface), var(--white));
}
.card-header h2 {
    font-size: 15px;
    font-weight: 700;
    color: var(--earth);
    display: flex;
    align-items: center;
    gap: 8px;
}
.card-header .icon-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: var(--sun);
}
.card-body { padding: 20px; }

/* ── FORM ─────────────────────────────────── */
.form-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.form-group { display:flex; flex-direction:column; gap:6px; }
.form-group.full { grid-column: 1/-1; }
label { font-size:12px; font-weight:600; color:var(--earth-light); text-transform:uppercase; letter-spacing:.5px; }
input, select, textarea {
    padding: 10px 14px;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 14px;
    color: var(--earth);
    transition: border-color .2s, box-shadow .2s;
    background: var(--white);
    width: 100%;
}
input:focus, select:focus, textarea:focus {
    outline: none;
    border-color: var(--sky);
    box-shadow: 0 0 0 3px rgba(14,165,233,.1);
}
textarea { resize: vertical; min-height: 80px; }

/* ── FORMULA SETTINGS ─────────────────────────── */
.formula-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.formula-item {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 12px;
}
.formula-item label { font-size:11px; color:var(--earth-light); display:block; margin-bottom:4px; }
.formula-item input {
    font-size:13px; padding:6px 10px;
    background:var(--white);
}
.formula-hint { font-size:11px; color:var(--earth-light); margin-top:4px; }

/* ── PRODUCT TABLE ─────────────────────────── */
.products-table-wrap { overflow-x:auto; }
table { width:100%; border-collapse:collapse; }
thead tr { background: linear-gradient(to right, var(--earth), var(--earth-mid)); }
thead th {
    padding: 12px 14px;
    text-align:left;
    font-size:11px;
    font-weight:700;
    color:rgba(255,255,255,.8);
    text-transform: uppercase;
    letter-spacing:.5px;
}
tbody tr { border-bottom:1px solid var(--border); transition:background .1s; }
tbody tr:hover { background: rgba(14,165,233,.03); }
tbody td { padding:12px 14px; font-size:13px; vertical-align: middle; }
.product-name-cell { font-weight:600; color:var(--earth); }
.product-name-cell span { display:block; font-size:11px; font-weight:400; color:var(--earth-light); margin-top:2px; }
.badge {
    display:inline-flex; align-items:center;
    padding:3px 8px; border-radius:20px;
    font-size:11px; font-weight:600;
}
.badge-panel { background:#DCFCE7; color:#15803D; }
.badge-inverter { background:#DBEAFE; color:#1D4ED8; }
.badge-battery { background:#FEF3C7; color:#B45309; }
.badge-mounting { background:#F3E8FF; color:#7E22CE; }
.badge-package { background:#FEE2E2; color:#B91C1C; }
.badge-accessories { background:#ECFEFF; color:#0E7490; }
.qty-input {
    width: 72px !important;
    text-align: center;
    font-weight: 600;
}
.srp-input {
    width: 110px !important;
    font-weight: 600;
    color: var(--sky-dark);
}
.markup-input {
    width: 100px !important;
    color: var(--green);
    font-weight:600;
}

/* ── SELECTED ITEMS TABLE ─────────────────── */
.selected-section { margin-top:20px; }
.selected-section h3 {
    font-size:14px; font-weight:700;
    color:var(--earth); margin-bottom:12px;
    display:flex; align-items:center; gap:8px;
}
.selected-badge {
    background: var(--sun);
    color: white;
    border-radius: 20px;
    padding: 2px 10px;
    font-size:12px;
}
#selected-table-wrap { max-height:400px; overflow-y:auto; }
.selected-item-row td { background:var(--surface); }
.row-total { font-weight:700; color:var(--earth); }

/* ── SIDEBAR SUMMARY ─────────────────────── */
.sidebar { display:flex; flex-direction:column; gap:16px; }
.summary-item { display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid var(--border); }
.summary-item:last-child { border:none; }
.summary-label { font-size:13px; color:var(--earth-light); }
.summary-value { font-size:14px; font-weight:700; color:var(--earth); }
.summary-value.highlight { color:var(--sun); font-size:18px; }
.summary-total { 
    background:linear-gradient(135deg,var(--earth),var(--earth-mid));
    border-radius:10px; padding:16px; margin-top:4px;
}
.summary-total .label { color:rgba(255,255,255,.7); font-size:12px; font-weight:600; text-transform:uppercase; }
.summary-total .amount { color:white; font-size:24px; font-weight:800; margin-top:4px; }

/* ── SEARCH ─────────────────────────────── */
.search-bar {
    display:flex; gap:10px; align-items:center;
    padding:12px 20px;
    background:var(--surface);
    border-bottom:1px solid var(--border);
}
.search-bar input {
    flex:1; border-radius:8px;
    background:var(--white);
    font-size:14px;
}
.filter-tabs { display:flex; gap:4px; flex-wrap:wrap; }
.tab-btn {
    padding:5px 12px;
    border-radius:20px; border:1px solid var(--border);
    font-size:12px; font-weight:600; cursor:pointer;
    background:var(--white); color:var(--earth-light);
    transition:all .2s;
}
.tab-btn.active { background:var(--sky); color:white; border-color:var(--sky); }
.tab-btn:hover:not(.active) { background:var(--surface); color:var(--earth); }

/* ── TOAST ─────────────────────────────── */
.toast-container { position:fixed; top:80px; right:24px; z-index:9999; display:flex; flex-direction:column; gap:8px; }
.toast {
    background:var(--earth); color:white;
    padding:12px 20px; border-radius:10px;
    font-size:13px; font-weight:600;
    box-shadow:var(--shadow-lg);
    display:flex; align-items:center; gap:10px;
    animation: slideIn .3s ease;
}
.toast.success { background:var(--green); }
.toast.error { background:var(--red); }
@keyframes slideIn { from{transform:translateX(100%);opacity:0} to{transform:none;opacity:1} }

/* ── MODAL ─────────────────────────────── */
.modal-overlay {
    position:fixed; inset:0;
    background:rgba(0,0,0,.5);
    z-index:1000; display:none;
    align-items:center; justify-content:center;
}
.modal-overlay.open { display:flex; }
.modal {
    background:var(--white);
    border-radius:16px;
    padding:28px;
    max-width:480px; width:90%;
    box-shadow:var(--shadow-lg);
}
.modal h2 { font-size:18px; font-weight:700; margin-bottom:8px; }
.modal p { color:var(--earth-light); font-size:14px; margin-bottom:20px; }
.modal-actions { display:flex; gap:10px; justify-content:flex-end; }

/* ── LOADING ─────────────────────────────── */
.loading-spinner {
    width:18px; height:18px;
    border:2px solid rgba(255,255,255,.3);
    border-top-color:white;
    border-radius:50%;
    animation:spin .6s linear infinite;
}
@keyframes spin { to{transform:rotate(360deg)} }

/* ── SECTION TABS ─────────────────────────── */
.section-tabs { display:flex; gap:0; border-bottom:2px solid var(--border); margin-bottom:0; }
.section-tab {
    padding:12px 20px;
    font-size:14px; font-weight:600;
    cursor:pointer; color:var(--earth-light);
    border-bottom:2px solid transparent;
    margin-bottom:-2px;
    transition:all .2s;
}
.section-tab.active { color:var(--sky); border-bottom-color:var(--sky); }
.tab-panel { display:none; }
.tab-panel.active { display:block; }

/* ── STICKY ACTIONS ─────────────────────────── */
.sticky-save {
    position:sticky; bottom:0;
    background:white;
    border-top:2px solid var(--border);
    padding:16px 20px;
    display:flex; gap:10px;
    justify-content:flex-end;
}

/* ── RESPONSIVE ─────────────────────────── */
@media(max-width:1100px) {
    .main-content { grid-template-columns:1fr; }
    .sidebar { display:grid; grid-template-columns:1fr 1fr; }
}
@media(max-width:600px) {
    .main-content { padding:16px; }
    .form-grid { grid-template-columns:1fr; }
    .sidebar { grid-template-columns:1fr; }
}

.stock-indicator { font-size:11px; font-weight:600; }
.stock-ok { color:var(--green); }
.stock-low { color:var(--sun); }
.stock-out { color:var(--red); }

.add-row-btn {
    background:transparent; border:1px dashed var(--border);
    border-radius:8px; padding:8px 16px;
    cursor:pointer; font-size:13px; color:var(--earth-light);
    transition:all .2s; width:100%; margin-top:8px;
    display:flex; align-items:center; gap:6px; justify-content:center;
}
.add-row-btn:hover { background:var(--surface); color:var(--sky); border-color:var(--sky); }

#items-body tr.dragging { opacity:.5; background:var(--sun-light); }
</style>
</head>
<body>

<!-- HEADER -->
<div class="page-header">
    <div class="header-left">
        <div class="logo-badge">☀</div>
        <div class="page-title">
            <h1>Create Quotation</h1>
            <p>Build and export professional solar quotations</p>
        </div>
    </div>
    <div class="header-actions">
        <button class="btn btn-secondary" onclick="window.history.back()">
            <i class="fas fa-arrow-left"></i> Back
        </button>
        <button class="btn btn-secondary" id="saveDraftBtn" onclick="saveQuotation('draft')">
            <i class="fas fa-save"></i> Save Draft
        </button>
        <button class="btn btn-success" id="exportBtn" onclick="exportToExcel()">
            <i class="fas fa-file-excel"></i> Export Excel
        </button>
        <button class="btn btn-primary" id="saveBtn" onclick="saveQuotation('sent')">
            <i class="fas fa-paper-plane"></i> Save & Send
        </button>
    </div>
</div>

<!-- TOAST CONTAINER -->
<div class="toast-container" id="toastContainer"></div>

<!-- MAIN -->
<div class="main-content">

    <!-- LEFT: FORM + PRODUCTS -->
    <div style="display:flex;flex-direction:column;gap:16px;">

        <!-- CLIENT INFORMATION -->
        <div class="card">
            <div class="card-header">
                <h2><span class="icon-dot"></span> Client & Project Information</h2>
            </div>
            <div class="card-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Client Name *</label>
                        <input type="text" id="clientName" placeholder="Full name or company">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="clientEmail" placeholder="email@example.com">
                    </div>
                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="text" id="clientContact" placeholder="09XX-XXX-XXXX">
                    </div>
                    <div class="form-group">
                        <label>Project Address *</label>
                        <input type="text" id="clientLocation" placeholder="City, Province">
                    </div>
                    <div class="form-group">
                        <label>System Type</label>
                        <select id="systemType">
                            <option value="HYBRID">Hybrid</option>
                            <option value="GRID TIE">Grid Tie</option>
                            <option value="OFF GRID">Off Grid</option>
                            <option value="HYBRID, GRID TIE">Hybrid + Grid Tie</option>
                            <option value="SUPPLY ONLY">Supply Only</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>System Kilowatts</label>
                        <input type="number" id="kw" step="0.5" min="0" placeholder="e.g. 6">
                    </div>
                    <div class="form-group">
                        <label>Officer In-Charge</label>
                        <select id="officer">
                            <?php foreach($officers as $o): ?>
                            <option value="<?= htmlspecialchars($o['officer_code']) ?>"
                                <?= strtoupper($firstName) === $o['officer_code'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($o['firstName'] . ' ' . $o['lastName']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Quotation Number</label>
                        <input type="text" id="quotationNumber" value="<?= $default_q_number ?>">
                    </div>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" id="quotationDate" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select id="status">
                            <option value="DRAFT">Draft</option>
                            <option value="SENT">Sent</option>
                            <option value="CLOSED">Closed</option>
                            <option value="LOSS">Loss</option>
                        </select>
                    </div>
                    <div class="form-group full">
                        <label>Remarks / Notes</label>
                        <textarea id="remarks" placeholder="Additional notes, follow-up details..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- FORMULA SETTINGS -->
        <div class="card">
            <div class="card-header">
                <h2><span class="icon-dot"></span> Quotation Formula Settings</h2>
                <button class="btn btn-ghost btn-sm" onclick="resetFormulas()">
                    <i class="fas fa-undo"></i> Reset Defaults
                </button>
            </div>
            <div class="card-body">
                <p style="font-size:13px;color:var(--earth-light);margin-bottom:14px;">
                    Based on JHG quotation template. Unit price = SRP + Markup. Total = Unit Price × Qty.
                </p>
                <div class="formula-grid">
                    <div class="formula-item">
                        <label>Installation Fee per kW (₱)</label>
                        <input type="number" id="f_install_fee" value="9000" step="100">
                    </div>
                    <div class="formula-item">
                        <label>Installer Fee per kW (₱)</label>
                        <input type="number" id="f_installer" value="4000" step="100">
                    </div>
                    <div class="formula-item">
                        <label>Delivery Charge (₱)</label>
                        <input type="number" id="f_delivery" value="10000" step="500">
                    </div>
                    <div class="formula-item">
                        <label>Discount Rate (%)</label>
                        <input type="number" id="f_discount" value="15" min="0" max="100" step="0.5">
                    </div>
                    <div class="formula-item">
                        <label>Default Markup per Item (%)</label>
                        <input type="number" id="f_default_markup" value="100" min="0" step="5"
                               onchange="applyDefaultMarkup()">
                        <div class="formula-hint">Applied when adding new items. 100% = price doubled.</div>
                    </div>
                    <div class="formula-item">
                        <label>Grand Total Formula</label>
                        <select id="f_formula_type">
                            <option value="standard">Materials Total + Install - Discount</option>
                            <option value="with_delivery">+ Delivery Charge</option>
                            <option value="with_vat">+ 12% VAT</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- PRODUCT SELECTION -->
        <div class="card">
            <div class="card-header">
                <h2><span class="icon-dot"></span> Add Products to Quotation</h2>
            </div>
            <div class="search-bar">
                <input type="text" id="productSearch" placeholder="🔍  Search by name or brand..." oninput="filterProducts()">
                <div class="filter-tabs">
                    <button class="tab-btn active" onclick="filterByCategory('all', this)">All</button>
                    <?php foreach($categories as $cat): ?>
                    <button class="tab-btn" onclick="filterByCategory('<?= htmlspecialchars($cat) ?>', this)">
                        <?= htmlspecialchars($cat) ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="products-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>SRP (₱)</th>
                            <th>Stock</th>
                            <th>Qty</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="productsTableBody">
                    <?php foreach($products as $p):
                        $cat_slug = strtolower(str_replace(' & ', '-', str_replace(' ', '-', $p['category'])));
                        $badge_class = 'badge-accessories';
                        if(stripos($p['category'],'panel')!==false) $badge_class='badge-panel';
                        elseif(stripos($p['category'],'inverter')!==false) $badge_class='badge-inverter';
                        elseif(stripos($p['category'],'battery')!==false) $badge_class='badge-battery';
                        elseif(stripos($p['category'],'mounting')!==false) $badge_class='badge-mounting';
                        elseif(stripos($p['category'],'package')!==false) $badge_class='badge-package';
                    ?>
                    <tr class="product-row" data-category="<?= htmlspecialchars($p['category']) ?>"
                        data-search="<?= strtolower(htmlspecialchars($p['displayName'] . ' ' . $p['brandName'])) ?>">
                        <td>
                            <div class="product-name-cell">
                                <?= htmlspecialchars($p['displayName']) ?>
                                <span><?= htmlspecialchars($p['brandName']) ?> <?= $p['warranty'] ? '· '.$p['warranty'].' warranty' : '' ?></span>
                            </div>
                        </td>
                        <td><span class="badge <?= $badge_class ?>"><?= htmlspecialchars($p['category']) ?></span></td>
                        <td style="font-weight:700;color:var(--sky-dark);">₱<?= number_format($p['price'], 2) ?></td>
                        <td>
                            <?php if($p['stockQuantity'] > 10): ?>
                                <span class="stock-indicator stock-ok">✓ <?= $p['stockQuantity'] ?></span>
                            <?php elseif($p['stockQuantity'] > 0): ?>
                                <span class="stock-indicator stock-low">⚠ <?= $p['stockQuantity'] ?></span>
                            <?php else: ?>
                                <span class="stock-indicator stock-out">✗ Out</span>
                            <?php endif; ?>
                        </td>
                        <td><input type="number" class="qty-input" value="1" min="1" id="qty_<?= $p['id'] ?>"></td>
                        <td>
                            <button class="btn btn-primary btn-sm"
                                onclick="addProduct(<?= $p['id'] ?>, '<?= addslashes($p['displayName']) ?>', '<?= addslashes($p['brandName']) ?>', <?= $p['price'] ?>, '<?= addslashes($p['category']) ?>')">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SELECTED ITEMS -->
        <div class="card">
            <div class="card-header">
                <h2>
                    <span class="icon-dot"></span> Quotation Items
                    <span class="selected-badge" id="itemCount">0</span>
                </h2>
                <button class="btn btn-ghost btn-sm" onclick="addCustomRow()">
                    <i class="fas fa-plus"></i> Add Custom Item
                </button>
            </div>
            <div id="selected-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th>Photo Ref</th>
                            <th>Qty</th>
                            <th>Unit</th>
                            <th>SRP (₱)</th>
                            <th>Markup (₱)</th>
                            <th>Unit Price (₱)</th>
                            <th>Total (₱)</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="items-body">
                        <tr id="empty-row">
                            <td colspan="10" style="text-align:center;padding:40px;color:var(--earth-light);">
                                <i class="fas fa-solar-panel" style="font-size:32px;margin-bottom:8px;display:block;opacity:.3;"></i>
                                Add products from the catalog above
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="sticky-save">
                <button class="btn btn-ghost btn-sm" onclick="clearItems()">
                    <i class="fas fa-trash"></i> Clear All
                </button>
            </div>
        </div>
    </div>

    <!-- SIDEBAR SUMMARY -->
    <div class="sidebar">
        <div class="card">
            <div class="card-header">
                <h2><span class="icon-dot"></span> Quotation Summary</h2>
            </div>
            <div class="card-body">
                <div class="summary-item">
                    <span class="summary-label">Materials Subtotal</span>
                    <span class="summary-value" id="sumMaterials">₱0.00</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Installation Fee</span>
                    <span class="summary-value" id="sumInstall">₱0.00</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Delivery Charge</span>
                    <span class="summary-value" id="sumDelivery">₱0.00</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Discount</span>
                    <span class="summary-value" style="color:var(--red);" id="sumDiscount">-₱0.00</span>
                </div>
                <div class="summary-total">
                    <div class="label">Grand Total</div>
                    <div class="amount" id="sumGrandTotal">₱0.00</div>
                </div>
                <br>
                <div class="summary-item">
                    <span class="summary-label">50% Downpayment</span>
                    <span class="summary-value" id="sumDown">₱0.00</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Items in Quotation</span>
                    <span class="summary-value" id="sumItemCount">0</span>
                </div>
            </div>
        </div>

        <!-- TERMS PREVIEW -->
        <div class="card">
            <div class="card-header">
                <h2><span class="icon-dot"></span> Terms of Payment</h2>
            </div>
            <div class="card-body" style="font-size:13px;color:var(--earth-light);line-height:1.7;">
                <p>• 50% Downpayment upon Contract Signing</p>
                <p>• 20% Progress Billing upon Delivery of Materials</p>
                <p>• 20% Progress Billing upon System Installation</p>
                <p>• 10% Retention after Final Testing & Turnover</p>
                <br>
                <p style="font-weight:600;color:var(--earth);">Notes:</p>
                <p>• Prices subject to change without notice</p>
                <p>• Quotation valid for 15 days</p>
                <p>• VAT Exclusive</p>
                <p>• Solar Panel Warranty: 12 Years</p>
                <p>• Inverter Warranty: 5-10 Years</p>
                <p>• Battery Warranty: 5 Years</p>
            </div>
        </div>

        <!-- COMPANY INFO -->
        <div class="card">
            <div class="card-header">
                <h2><span class="icon-dot"></span> Company Details</h2>
            </div>
            <div class="card-body" style="font-size:13px;color:var(--earth-light);line-height:1.8;">
                <p style="font-weight:700;color:var(--earth);">SOLARPOWER ENERGY CORPORATION</p>
                <p>Ayala Alabang, Muntinlupa City</p>
                <p>📞 0995-234-6995 / 0995-394-7379</p>
                <p>📘 SOLARPOWER ENERGY CORPORATION</p>
                <br>
                <p style="font-weight:600;color:var(--earth);">Bank Transfer:</p>
                <p>Acct Name: Worldcity Development Corp</p>
                <p>Bank: Unionbank Philippines</p>
                <p>Acct #: 0021-8002-7200</p>
            </div>
        </div>

        <!-- EXPORT OPTIONS -->
        <div class="card">
            <div class="card-header">
                <h2><span class="icon-dot"></span> Export Options</h2>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:10px;">
                <button class="btn btn-success" style="width:100%" onclick="exportToExcel()">
                    <i class="fas fa-file-excel"></i> Export as Excel (.xlsx)
                </button>
                <button class="btn btn-primary" style="width:100%;background:var(--sky)" onclick="saveQuotation('sent')">
                    <i class="fas fa-database"></i> Save to Database
                </button>
                <button class="btn btn-ghost" style="width:100%" onclick="saveQuotation('draft')">
                    <i class="fas fa-save"></i> Save as Draft
                </button>
            </div>
        </div>
    </div>

</div>

<script>
// ── STATE ────────────────────────────────────────────
let items = []; // {id, desc, brand, qty, srp, markup, category, supplier, unit}
let itemCounter = 0;
let activeCategory = 'all';

// ── PRODUCT FILTERING ────────────────────────────────
function filterByCategory(cat, btn) {
    activeCategory = cat;
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    filterProducts();
}

function filterProducts() {
    const q = document.getElementById('productSearch').value.toLowerCase();
    document.querySelectorAll('.product-row').forEach(row => {
        const matchCat = activeCategory === 'all' || row.dataset.category === activeCategory;
        const matchSearch = !q || row.dataset.search.includes(q);
        row.style.display = matchCat && matchSearch ? '' : 'none';
    });
}

// ── ADD PRODUCT ──────────────────────────────────────
function addProduct(id, name, brand, srp, category) {
    const qty = parseInt(document.getElementById('qty_' + id).value) || 1;
    const markupPct = parseFloat(document.getElementById('f_default_markup').value) || 100;
    const markup = srp * (markupPct / 100);
    
    // Check if already in list
    const existing = items.find(i => i.productId === id);
    if (existing) {
        existing.qty += qty;
        renderItems();
        showToast(`Updated qty for ${name}`, 'success');
        return;
    }

    items.push({
        rowId: ++itemCounter,
        productId: id,
        desc: name,
        brand: brand,
        srp: srp,
        markup: markup,
        qty: qty,
        category: category,
        supplier: '',
        unit: 'pcs'
    });

    renderItems();
    updateSummary();
    showToast(`Added: ${name}`, 'success');
}

function addCustomRow() {
    items.push({
        rowId: ++itemCounter,
        productId: null,
        desc: '',
        brand: '',
        srp: 0,
        markup: 0,
        qty: 1,
        category: 'Custom',
        supplier: '',
        unit: 'pcs'
    });
    renderItems();
}

function applyDefaultMarkup() {
    const pct = parseFloat(document.getElementById('f_default_markup').value) || 100;
    items.forEach(item => {
        item.markup = item.srp * (pct / 100);
    });
    renderItems();
    updateSummary();
}

// ── RENDER ───────────────────────────────────────────
function renderItems() {
    const tbody = document.getElementById('items-body');
    document.getElementById('itemCount').textContent = items.length;
    document.getElementById('sumItemCount').textContent = items.length;

    if (items.length === 0) {
        tbody.innerHTML = `<tr id="empty-row">
            <td colspan="10" style="text-align:center;padding:40px;color:var(--earth-light);">
                <i class="fas fa-solar-panel" style="font-size:32px;margin-bottom:8px;display:block;opacity:.3;"></i>
                Add products from the catalog above
            </td></tr>`;
        updateSummary();
        return;
    }

    tbody.innerHTML = items.map((item, idx) => {
        const unitPrice = item.srp + item.markup;
        const total = unitPrice * item.qty;
        return `
        <tr class="selected-item-row" data-rowid="${item.rowId}">
            <td style="color:var(--earth-light);font-weight:600;">${idx+1}</td>
            <td>
                <input style="min-width:200px;font-size:13px;font-weight:600;" 
                    value="${escHtml(item.desc)}" 
                    onchange="updateItem(${item.rowId},'desc',this.value)"
                    placeholder="Description">
                <div style="font-size:11px;color:var(--earth-light);margin-top:3px;">${escHtml(item.brand)}</div>
            </td>
            <td>
                <input style="width:80px;font-size:12px;" 
                    value="${escHtml(item.supplier)}"
                    onchange="updateItem(${item.rowId},'supplier',this.value)"
                    placeholder="Source">
            </td>
            <td>
                <input type="number" class="qty-input" value="${item.qty}" min="1"
                    onchange="updateItem(${item.rowId},'qty',+this.value)">
            </td>
            <td>
                <input style="width:60px;font-size:12px;" value="${escHtml(item.unit)}"
                    onchange="updateItem(${item.rowId},'unit',this.value)">
            </td>
            <td>
                <input type="number" class="srp-input" value="${item.srp.toFixed(2)}" step="0.01" min="0"
                    onchange="updateItem(${item.rowId},'srp',+this.value)">
            </td>
            <td>
                <input type="number" class="markup-input" value="${item.markup.toFixed(2)}" step="0.01" min="0"
                    onchange="updateItem(${item.rowId},'markup',+this.value)">
            </td>
            <td style="font-weight:700;color:var(--sky-dark);">₱${formatNum(unitPrice)}</td>
            <td class="row-total">₱${formatNum(total)}</td>
            <td>
                <button class="btn btn-danger btn-sm" onclick="removeItem(${item.rowId})">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        </tr>`;
    }).join('');
    updateSummary();
}

function updateItem(rowId, field, value) {
    const item = items.find(i => i.rowId === rowId);
    if (!item) return;
    item[field] = value;
    renderItems();
}

function removeItem(rowId) {
    items = items.filter(i => i.rowId !== rowId);
    renderItems();
}

function clearItems() {
    if (!items.length) return;
    if (confirm('Clear all items?')) { items = []; renderItems(); }
}

// ── SUMMARY ─────────────────────────────────────────
function updateSummary() {
    const kw = parseFloat(document.getElementById('kw').value) || 0;
    const installFee = parseFloat(document.getElementById('f_install_fee').value) || 9000;
    const installerFee = parseFloat(document.getElementById('f_installer').value) || 4000;
    const delivery = parseFloat(document.getElementById('f_delivery').value) || 10000;
    const discountPct = parseFloat(document.getElementById('f_discount').value) || 15;
    const formulaType = document.getElementById('f_formula_type').value;

    const materialTotal = items.reduce((s, i) => s + (i.srp + i.markup) * i.qty, 0);
    const installTotal = (installFee + installerFee) * kw;
    const discountAmt = materialTotal * (discountPct / 100);

    let grandTotal = materialTotal + installTotal - discountAmt;
    if (formulaType === 'with_delivery') grandTotal += delivery;
    if (formulaType === 'with_vat') grandTotal *= 1.12;

    document.getElementById('sumMaterials').textContent = '₱' + formatNum(materialTotal);
    document.getElementById('sumInstall').textContent = '₱' + formatNum(installTotal);
    document.getElementById('sumDelivery').textContent = '₱' + formatNum(delivery);
    document.getElementById('sumDiscount').textContent = '-₱' + formatNum(discountAmt);
    document.getElementById('sumGrandTotal').textContent = '₱' + formatNum(grandTotal);
    document.getElementById('sumDown').textContent = '₱' + formatNum(grandTotal * 0.5);
}

// Listen for formula changes
['f_install_fee','f_installer','f_delivery','f_discount','f_formula_type','kw'].forEach(id => {
    document.getElementById(id).addEventListener('input', updateSummary);
});

function resetFormulas() {
    document.getElementById('f_install_fee').value = 9000;
    document.getElementById('f_installer').value = 4000;
    document.getElementById('f_delivery').value = 10000;
    document.getElementById('f_discount').value = 15;
    document.getElementById('f_default_markup').value = 100;
    document.getElementById('f_formula_type').value = 'standard';
    updateSummary();
    showToast('Formulas reset to defaults');
}

// ── SAVE QUOTATION ────────────────────────────────────
function saveQuotation(status) {
    const clientName = document.getElementById('clientName').value.trim();
    if (!clientName) { showToast('Please enter client name', 'error'); return; }
    if (!items.length) { showToast('Add at least one product', 'error'); return; }

    document.getElementById('status').value = status.toUpperCase();

    const payload = new FormData();
    payload.append('action', 'create');
    payload.append('clientName', clientName);
    payload.append('email', document.getElementById('clientEmail').value);
    payload.append('contact', document.getElementById('clientContact').value);
    payload.append('location', document.getElementById('clientLocation').value);
    payload.append('systemType', document.getElementById('systemType').value);
    payload.append('kw', document.getElementById('kw').value);
    payload.append('officer', document.getElementById('officer').value);
    payload.append('status', status.toUpperCase());
    payload.append('remarks', document.getElementById('remarks').value);

    const btn = status === 'draft' ? document.getElementById('saveDraftBtn') : document.getElementById('saveBtn');
    const origText = btn.innerHTML;
    btn.innerHTML = '<div class="loading-spinner"></div> Saving...';
    btn.disabled = true;

    fetch('quotation_api.php', { method: 'POST', body: payload })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast('Quotation saved successfully!', 'success');
            } else {
                showToast(data.error || 'Save failed', 'error');
            }
        })
        .catch(() => showToast('Network error', 'error'))
        .finally(() => {
            btn.innerHTML = origText;
            btn.disabled = false;
        });
}

// ── EXPORT TO EXCEL ───────────────────────────────────
function exportToExcel() {
    if (!items.length) { showToast('No items to export', 'error'); return; }
    const clientName = document.getElementById('clientName').value.trim() || 'Draft Client';

    const params = {
        client_name: clientName,
        email: document.getElementById('clientEmail').value,
        contact: document.getElementById('clientContact').value,
        location: document.getElementById('clientLocation').value,
        system_type: document.getElementById('systemType').value,
        kw: document.getElementById('kw').value,
        officer: document.getElementById('officer').options[document.getElementById('officer').selectedIndex]?.text || '',
        quotation_number: document.getElementById('quotationNumber').value,
        quotation_date: document.getElementById('quotationDate').value,
        install_fee: document.getElementById('f_install_fee').value,
        installer_fee: document.getElementById('f_installer').value,
        delivery: document.getElementById('f_delivery').value,
        discount_pct: document.getElementById('f_discount').value,
        formula_type: document.getElementById('f_formula_type').value,
        items: JSON.stringify(items)
    };

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'quotation_export.php';
    form.target = '_blank';
    Object.entries(params).forEach(([k,v]) => {
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = k; inp.value = v;
        form.appendChild(inp);
    });
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
    showToast('Generating Excel file...', 'success');
}

// ── HELPERS ──────────────────────────────────────────
function formatNum(n) {
    return (Math.round(n * 100) / 100).toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2});
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function showToast(msg, type='') {
    const tc = document.getElementById('toastContainer');
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `<i class="fas ${type==='success'?'fa-check-circle':type==='error'?'fa-exclamation-circle':'fa-info-circle'}"></i> ${msg}`;
    tc.appendChild(t);
    setTimeout(() => t.remove(), 3000);
}
</script>
</body>
</html>
