<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include "../../config/dbconn.php";

// ── Validate product ID ──────────────────────────────────────────────────
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($product_id <= 0) {
    $_SESSION['edit_product_msg'] = 'Invalid Product ID.';
    $_SESSION['edit_product_msg_type'] = 'error';
    header("Location: dashboard.php?page=product");
    exit();
}

// ── Fetch product row ────────────────────────────────────────────────────
$stmt = $conn->prepare("SELECT * FROM product WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$prod = $stmt->get_result()->fetch_assoc();
if (!$prod) {
    $stmt->close();
    $_SESSION['edit_product_msg'] = 'Product not found.';
    $_SESSION['edit_product_msg_type'] = 'error';
    header("Location: dashboard.php?page=product");
    exit();
}
$stmt->close();

// ── Fetch existing brand variants (keyed by brand_id) ───────────────────
$existingVariants = [];
$sv = $conn->prepare("SELECT brand_id, price, variant_image FROM product_brand_variants WHERE product_id = ?");
$sv->bind_param("i", $product_id);
$sv->execute();
$resV = $sv->get_result();
while ($row = $resV->fetch_assoc()) {
    $existingVariants[(int)$row['brand_id']] = ['price' => $row['price'], 'image' => $row['variant_image']];
}
$sv->close();

// ── Fetch all categories ─────────────────────────────────────────────────
$categories = [];
$rc = $conn->query("SELECT category_id AS id, category_name AS categoryName FROM categories ORDER BY category_name");
while ($r = $rc->fetch_assoc()) $categories[] = $r;

// ── Handle POST (update) ─────────────────────────────────────────────────
$msg = '';
$msgType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_product') {
    $pid        = intval($_POST['product_id'] ?? 0);
    $displayName = trim($_POST['product-name'] ?? '');
    $category   = trim($_POST['category'] ?? '');
    $catLower   = strtolower($category);
    $isVariant  = strpos($catLower, 'panel') !== false || strpos($catLower, 'battery') !== false || strpos($catLower, 'inverter') !== false;
    $packageType = trim($_POST['package-type'] ?? '') ?: NULL;
    $stockQty   = isset($_POST['stock-quantity']) && $_POST['stock-quantity'] !== '' ? intval($_POST['stock-quantity']) : 9999;
    $warranty   = trim($_POST['warranty'] ?? '');
    $status     = trim($_POST['status'] ?? 'Active');
    $description = $_POST['description'] ?? '';
    $moq        = isset($_POST['moq']) && $_POST['moq'] !== '' ? intval($_POST['moq']) : 1;
    if (strpos($catLower, 'panel') !== false && $moq < 5) {
        $moq = 5;
    }

    $brand_ids    = $_POST['brand_ids'] ?? [];
    $brand_prices = $_POST['brand_price'] ?? [];

    // Determine fallback brandName & price for the main product row
    if ($isVariant && !empty($brand_ids)) {
        $firstBrandId = (int)$brand_ids[0];
        $fallbackPrice = (float)($brand_prices[$firstBrandId] ?? 0);
        $br = $conn->prepare("SELECT brandName FROM brands WHERE id = ?");
        $br->bind_param("i", $firstBrandId);
        $br->execute();
        $brow = $br->get_result()->fetch_assoc();
        $fallbackBrand = $brow['brandName'] ?? 'Various';
        $br->close();
    } else {
        $fallbackBrand = trim($_POST['brand'] ?? '');
        $fallbackPrice = (float)($_POST['price'] ?? 0);
    }
    if (empty($fallbackBrand)) $fallbackBrand = 'Package';

    if (empty($displayName) || empty($category) || $pid <= 0) {
        $msg = 'Please fill in all required fields.';
        $msgType = 'error';
    } else {
        $conn->begin_transaction();
        try {
            $upd = $conn->prepare("UPDATE product SET displayName=?, brandName=?, price=?, category=?, packageType=?, stockQuantity=?, warranty=?, description=?, status=?, moq=? WHERE id=?");
            $upd->bind_param("ssdsissssii", $displayName, $fallbackBrand, $fallbackPrice, $category, $packageType, $stockQty, $warranty, $description, $status, $moq, $pid);
            $upd->execute();
            $upd->close();

            // ── Variants ────────────────────────────────────────────────
            if ($isVariant) {
                // Preserve existing images
                $existImg = [];
                $ev = $conn->prepare("SELECT brand_id, variant_image FROM product_brand_variants WHERE product_id = ?");
                $ev->bind_param("i", $pid);
                $ev->execute();
                $evR = $ev->get_result();
                while ($r2 = $evR->fetch_assoc()) {
                    $existImg[(int)$r2['brand_id']] = $r2['variant_image'];
                }
                $ev->close();

                $conn->query("DELETE FROM product_brand_variants WHERE product_id = " . $pid);

                $uploadDir = "../../uploads/products/$pid/";
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $allowed = ['jpg','jpeg','png','webp'];

                foreach ($brand_ids as $bid) {
                    $bid = (int)$bid;
                    $vPrice = (float)($brand_prices[$bid] ?? 0);
                    $imgPath = $existImg[$bid] ?? NULL;

                    $fk = 'brand_image_' . $bid;
                    if (isset($_FILES[$fk]) && $_FILES[$fk]['error'] === 0) {
                        $ext = strtolower(pathinfo($_FILES[$fk]['name'], PATHINFO_EXTENSION));
                        if (in_array($ext, $allowed)) {
                            $fname = "variant_{$bid}_" . uniqid() . ".$ext";
                            if (move_uploaded_file($_FILES[$fk]['tmp_name'], $uploadDir . $fname)) {
                                $imgPath = "uploads/products/$pid/$fname";
                            }
                        }
                    }

                    $ins = $conn->prepare("INSERT INTO product_brand_variants (product_id, brand_id, price, variant_image) VALUES (?, ?, ?, ?)");
                    $ins->bind_param("iids", $pid, $bid, $vPrice, $imgPath);
                    $ins->execute();
                    $ins->close();
                }
            }

            // ── Gallery images ───────────────────────────────────────────
            if (isset($_FILES['product-images']) && !empty($_FILES['product-images']['name'][0])) {
                $uploadDir = "../../uploads/products/$pid/";
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $allowed = ['jpg','jpeg','png','gif','webp'];
                foreach ($_FILES['product-images']['name'] as $i => $fname) {
                    if ($_FILES['product-images']['error'][$i] !== UPLOAD_ERR_OK) continue;
                    $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
                    if (!in_array($ext, $allowed)) continue;
                    $newName  = 'img_' . uniqid() . ".$ext";
                    $dest     = $uploadDir . $newName;
                    if (move_uploaded_file($_FILES['product-images']['tmp_name'][$i], $dest)) {
                        $rel  = "uploads/products/$pid/$newName";
                        $ins2 = $conn->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?,?)");
                        $ins2->bind_param("is", $pid, $rel);
                        $ins2->execute();
                        $ins2->close();
                    }
                }
            }

            $conn->commit();
            // Redirect to dashboard product page on success (PRG)
            $_SESSION['edit_product_msg']      = "Product updated successfully!";
            $_SESSION['edit_product_msg_type'] = 'success';
            header("Location: dashboard.php?page=product");
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $msg = 'Error saving product: ' . $e->getMessage();
            $msgType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product – Solar Power Energy Corp.</title>

    <!-- Same CDNs as dashboard -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.quilljs.com/1.3.7/quill.snow.css">
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <link rel="stylesheet" href="style.css">

    <style>
        /* ── Page shell ───────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
        }

        .ep-topbar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 14px 28px;
            display: flex;
            align-items: center;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 6px rgba(0,0,0,.06);
        }
        .ep-topbar a.back-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #64748b;
            font-size: 13px;
            font-weight: 500;
            padding: 6px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            transition: all .2s;
        }
        .ep-topbar a.back-btn:hover { background: #f1f5f9; color: #334155; }
        .ep-topbar h1 {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
        }
        .ep-topbar .product-id-badge {
            font-size: 11px;
            color: #64748b;
            background: #f1f5f9;
            padding: 2px 10px;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
        }

        /* ── Main layout ──────────────────────────────────────────── */
        .ep-wrapper {
            max-width: 1260px;
            margin: 28px auto;
            padding: 0 20px 60px;
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 24px;
        }
        @media (max-width: 960px) {
            .ep-wrapper { grid-template-columns: 1fr; }
        }

        /* Alert banner */
        .ep-alert {
            grid-column: 1 / -1;
            padding: 12px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
        }
        .ep-alert.success { background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; }
        .ep-alert.error   { background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; }

        /* ── Cards / sections ─────────────────────────────────────── */
        .ep-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            padding: 24px;
            margin-bottom: 20px;
        }
        .ep-card h2 {
            font-size: 14px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .ep-card h2 i { color: #f59e0b; }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        @media (max-width: 640px) { .form-grid { grid-template-columns: 1fr; } }

        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group.full { grid-column: 1 / -1; }
        .form-group label {
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: .4px;
        }
        .form-group label i { color: #f59e0b; margin-right: 5px; }
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 10px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            color: #1e293b;
            background: #f8fafc;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
            width: 100%;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245,158,11,.12);
            background: #fff;
        }

        /* Quill wrapper */
        #quill-description-editor {
            min-height: 140px;
            background: #f8fafc;
            border-radius: 0 0 10px 10px;
            font-size: 14px;
        }
        .ql-toolbar { border-radius: 10px 10px 0 0; border: 1.5px solid #e2e8f0 !important; }
        .ql-container { border: 1.5px solid #e2e8f0 !important; border-top: 0 !important; border-radius: 0 0 10px 10px; }

        /* Brand checklist */
        .brands-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px;
        }
        .brands-section h3 {
            font-size: 13px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 12px;
        }
        .brand-variant-row {
            display: grid;
            grid-template-columns: 200px 1fr 1fr;
            gap: 12px;
            align-items: center;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 8px;
        }
        @media (max-width: 640px) { .brand-variant-row { grid-template-columns: 1fr; } }

        .brand-label { display: flex; align-items: center; gap: 8px; }
        .brand-label input[type=checkbox] { width: 17px; height: 17px; cursor: pointer; accent-color: #f59e0b; }
        .brand-label span { font-weight: 600; font-size: 13px; color: #334155; }

        .brand-sub-label { font-size: 11px; color: #64748b; margin-bottom: 4px; }
        .brand-price-input,
        .brand-image-input {
            width: 100%;
            padding: 7px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 13px;
            background: #f1f5f9;
        }
        .brand-price-input:not(:disabled):focus,
        .brand-image-input:not(:disabled):focus {
            border-color: #f59e0b;
            outline: none;
        }
        .brand-price-input:disabled,
        .brand-image-input:disabled { opacity: .45; cursor: not-allowed; }

        /* existing image thumb */
        .variant-img-thumb {
            max-height: 38px;
            border-radius: 5px;
            margin-top: 4px;
            border: 1px solid #e2e8f0;
            display: none;
        }

        /* Image upload */
        .file-upload-box {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            cursor: pointer;
            position: relative;
            transition: border-color .2s;
        }
        .file-upload-box:hover { border-color: #f59e0b; }
        .file-upload-box input[type=file] {
            position: absolute; inset: 0; opacity: 0; cursor: pointer;
            width: 100%; height: 100%;
        }
        .file-upload-box i { font-size: 28px; color: #94a3b8; }
        .file-upload-box p { font-size: 14px; color: #64748b; margin-top: 8px; }

        .image-preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
            gap: 10px;
            margin-top: 12px;
        }
        .image-preview-grid img {
            width: 100%;
            aspect-ratio: 1;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        /* Actions */
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
            justify-content: flex-end;
        }
        .btn-cancel {
            padding: 11px 24px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            background: #fff;
            font-size: 14px;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all .2s;
        }
        .btn-cancel:hover { background: #f1f5f9; }
        .btn-save {
            padding: 11px 28px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: transform .15s, box-shadow .2s;
        }
        .btn-save:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(245,158,11,.35);
        }

        /* Preview card */
        .preview-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            position: sticky;
            top: 80px;
        }
        .preview-header {
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
        }
        .preview-header h3 { font-size: 14px; font-weight: 700; display: flex; align-items: center; gap: 8px; }
        .preview-header h3 i { color: #f59e0b; }
        .preview-header p { font-size: 12px; color: #94a3b8; margin-top: 2px; }

        .preview-img-wrap {
            height: 200px;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        .preview-img-wrap img { width:100%; height:100%; object-fit:cover; display:none; }
        .preview-img-wrap .ph { text-align:center; color:#94a3b8; font-size:13px; }
        .preview-img-wrap .ph i { font-size:32px; display:block; margin-bottom:8px; }

        .preview-body { padding: 16px 20px; }
        .preview-name { font-size: 16px; font-weight: 700; margin-bottom: 4px; }
        .preview-cat {
            font-size: 11px;
            color: #f59e0b;
            background: #fef3c7;
            padding: 2px 10px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 10px;
        }
        .preview-price { font-size: 22px; font-weight: 800; color: #1e293b; }

        /* required star */
        .req { color: #ef4444; }
    </style>
</head>
<body>

<!-- ── Top bar ─────────────────────────────────────────────────────────── -->
<header class="ep-topbar">
    <a href="dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    <h1><i class="fas fa-edit" style="color:#f59e0b;margin-right:6px;"></i>Edit Product</h1>
    <span class="product-id-badge">ID #<?= $product_id ?></span>
</header>

<!-- ── Form ───────────────────────────────────────────────────────────── -->
<form method="POST" enctype="multipart/form-data" id="editProductForm">
<input type="hidden" name="action" value="edit_product">
<input type="hidden" name="product_id" value="<?= $product_id ?>">

<div class="ep-wrapper">

    <?php if ($msg): ?>
    <div class="ep-alert <?= $msgType ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- ── LEFT COLUMN ───────────────────────────────────────── -->
    <div>

        <!-- Basic Info -->
        <div class="ep-card">
            <h2><i class="fas fa-info-circle"></i> Product Details</h2>
            <div class="form-grid">

                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Category <span class="req">*</span></label>
                    <select id="category-select" name="category" required>
                        <option value="">— Select category —</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['categoryName']) ?>"
                            <?= $cat['categoryName'] === $prod['category'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['categoryName']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Standard Brand (non-variant categories) -->
                <div class="form-group" id="standard-brand-group">
                    <label><i class="fas fa-trademark"></i> Brand Name <span class="req">*</span></label>
                    <select id="brand-select" name="brand" disabled>
                        <option value="<?= htmlspecialchars($prod['brandName']) ?>" selected>
                            <?= htmlspecialchars($prod['brandName']) ?>
                        </option>
                    </select>
                </div>

                <!-- Package Type (shown for Package category) -->
                <div class="form-group" id="package-type-group" style="display:none;">
                    <label><i class="fas fa-solar-panel"></i> Package Type <span class="req">*</span></label>
                    <select id="package-type-select" name="package-type">
                        <option value="">Select</option>
                        <option value="On-Grid"  <?= ($prod['packageType'] ?? '') === 'On-Grid'  ? 'selected':'' ?>>On-Grid</option>
                        <option value="Hybrid"   <?= ($prod['packageType'] ?? '') === 'Hybrid'   ? 'selected':'' ?>>Hybrid</option>
                        <option value="Off-Grid" <?= ($prod['packageType'] ?? '') === 'Off-Grid' ? 'selected':'' ?>>Off-Grid</option>
                    </select>
                </div>

                <div class="form-group <?= !in_array($prod['category'], ['Panel','Battery','Inverter']) ? '' : 'full' ?>">
                    <label><i class="fas fa-cube"></i> Product Name / Model <span class="req">*</span></label>
                    <input type="text" id="product-name-input" name="product-name"
                           value="<?= htmlspecialchars($prod['displayName']) ?>" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-shield-alt"></i> Warranty <span class="req">*</span></label>
                    <input type="text" name="warranty"
                           value="<?= htmlspecialchars($prod['warranty'] ?? '5 years') ?>" required>
                </div>

                <!-- Standard Price (non-variant categories) -->
                <div class="form-group" id="standard-price-group">
                    <label><i class="fas fa-peso-sign"></i> Price (PHP) <span class="req">*</span></label>
                    <input type="number" id="price-input" name="price" step="0.01"
                           value="<?= htmlspecialchars($prod['price'] ?? '0') ?>">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-eye"></i> Visibility Status <span class="req">*</span></label>
                    <select name="status" required>
                        <option value="Active" <?= ($prod['status']??'Active') === 'Active' ? 'selected':'' ?>>Active (Visible)</option>
                        <option value="Hidden" <?= ($prod['status']??'Active') === 'Hidden' ? 'selected':'' ?>>Hidden (Draft)</option>
                    </select>
                </div>

                <!-- MOQ (Always Visible) -->
                <div class="form-group" id="moq-field-wrapper">
                    <label><i class="fas fa-layer-group"></i> Min. Order Qty (MOQ)</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="number" name="moq" id="moq-input" min="1"
                               value="<?= (int)($prod['moq'] ?? 1) ?>" <?= (int)($prod['moq'] ?? 1) <= 1 ? 'disabled style="background:#e2e8f0;opacity:0.7;"' : '' ?>>
                        <button type="button" id="moq-toggle-btn" class="btn-cancel" style="margin-top:0; white-space:nowrap; padding: 10px 14px; border-radius: 10px; cursor: pointer; transition: all 0.2s; <?= (int)($prod['moq'] ?? 1) > 1 ? 'background:#f59e0b; color:#fff; border-color:#f59e0b;' : '' ?>">
                            <?= (int)($prod['moq'] ?? 1) > 1 ? '<i class="fas fa-check"></i> MOQ Set' : 'Set as MOQ' ?>
                        </button>
                    </div>
                </div>

                <!-- Stock -->
                <div class="form-group" style="display:none;">
                    <input type="number" id="stock-quantity-input" name="stock-quantity"
                           value="<?= (int)($prod['stockQuantity'] ?? 9999) ?>">
                </div>

            </div>
        </div>

        <!-- ── Available Brands Checklist (variant categories) ── -->
        <div class="ep-card" id="brands-card" style="display:none;">
            <h2><i class="fas fa-certificate"></i> Available Brands Checklist</h2>
            <p style="font-size:13px;color:#64748b;margin-bottom:16px;">
                Check each supplier brand for this product, then set the specific price and variant image.
            </p>
            <div id="brands-checklist-container"></div>
        </div>

        <!-- Description -->
        <div class="ep-card">
            <h2><i class="fas fa-align-left"></i> Description</h2>
            <div id="quill-description-editor"></div>
            <input type="hidden" id="description-hidden" name="description"
                   value="<?= htmlspecialchars($prod['description'] ?? '') ?>">
            <small style="color:#94a3b8;font-size:11px;margin-top:6px;display:block;">
                Use the toolbar to add <strong>bold</strong> text, bullet lists, or numbered specs.
            </small>
        </div>

        <!-- Gallery images -->
        <div class="ep-card">
            <h2><i class="fas fa-images"></i> Add / Update Product Images</h2>
            <div class="file-upload-box">
                <input type="file" id="product-images" name="product-images[]" accept="image/*" multiple>
                <div style="pointer-events:none;">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Upload new images (up to 15)</p>
                    <span style="font-size:12px;color:#94a3b8;">PNG, JPG, WEBP · max 5 MB each</span>
                </div>
            </div>
            <div class="image-preview-grid" id="imagePreviewGrid"></div>
        </div>

        <!-- Action buttons -->
        <div class="form-actions">
            <a href="dashboard.php" class="btn-cancel"><i class="fas fa-times"></i> Cancel</a>
            <button type="submit" class="btn-save" id="saveBtn">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>

    </div><!-- end left column -->

    <!-- ── RIGHT COLUMN — Preview card ─────────────────────── -->
    <div>
        <div class="preview-card">
            <div class="preview-header">
                <h3><i class="fas fa-eye"></i> Live Preview</h3>
                <p>See how your product will appear</p>
            </div>
            <div class="preview-img-wrap">
                <img id="preview-img" src="" alt="preview">
                <div class="ph" id="preview-ph">
                    <i class="fas fa-solar-panel"></i>
                    No image selected
                </div>
            </div>
            <div class="preview-body">
                <div class="preview-name" id="preview-name"><?= htmlspecialchars($prod['displayName']) ?></div>
                <span class="preview-cat" id="preview-cat"><?= htmlspecialchars($prod['category']) ?></span>
                <div class="preview-price" id="preview-price">₱<?= number_format((float)$prod['price'], 2) ?></div>
            </div>
        </div>
    </div>

</div><!-- end ep-wrapper -->
</form>

<script>
// ── Bootstrapped PHP data ─────────────────────────────────────────────────
const PRODUCT_ID   = <?= $product_id ?>;
const INIT_PROD    = <?= json_encode($prod) ?>;
const INIT_VARIANTS = <?= json_encode($existingVariants) ?>; // { brand_id: {price, image} }

// ── Quill setup ──────────────────────────────────────────────────────────
const quill = new Quill('#quill-description-editor', {
    theme: 'snow',
    placeholder: 'Describe your product features, specifications…',
    modules: {
        toolbar: [
            [{ header: [2, 3, false] }],
            ['bold', 'italic', 'underline'],
            [{ list: 'ordered' }, { list: 'bullet' }],
            ['clean']
        ]
    }
});

// Pre-fill description
const rawDesc = INIT_PROD.description || '';
if (rawDesc) {
    // If it's HTML (from Quill) load as HTML; otherwise plain text
    if (/<[a-z][\s\S]*>/i.test(rawDesc)) {
        quill.root.innerHTML = rawDesc;
    } else {
        quill.setText(rawDesc);
    }
}

const hiddenDesc = document.getElementById('description-hidden');
quill.on('text-change', () => {
    const raw = quill.root.innerHTML;
    hiddenDesc.value = (raw === '<p><br></p>') ? '' : raw;
});

// ── Image preview (gallery) ──────────────────────────────────────────────
document.getElementById('product-images').addEventListener('change', function() {
    const grid = document.getElementById('imagePreviewGrid');
    grid.innerHTML = '';
    Array.from(this.files).slice(0, 15).forEach(file => {
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.onload = () => URL.revokeObjectURL(img.src);
        grid.appendChild(img);
    });

    // Also update preview card image
    if (this.files.length) {
        const pi = document.getElementById('preview-img');
        const ph = document.getElementById('preview-ph');
        pi.src = URL.createObjectURL(this.files[0]);
        pi.style.display = 'block';
        ph.style.display = 'none';
    }
});

// ── Live preview name / cat / price ─────────────────────────────────────
function updatePreview() {
    const name = document.getElementById('product-name-input').value || 'Product Name';
    const cat  = document.getElementById('category-select').value  || 'Category';
    const price = document.getElementById('price-input').value || '0';
    document.getElementById('preview-name').textContent = name;
    document.getElementById('preview-cat').textContent  = cat;
    document.getElementById('preview-price').textContent = '₱' + parseFloat(price||0).toLocaleString('en-PH',{minimumFractionDigits:2});
}
document.getElementById('product-name-input').addEventListener('input', updatePreview);
document.getElementById('price-input').addEventListener('input', updatePreview);

// ── Category change handler ──────────────────────────────────────────────
const catSelect   = document.getElementById('category-select');
const brandCard   = document.getElementById('brands-card');
const checklistEl = document.getElementById('brands-checklist-container');
const stdBrandGrp = document.getElementById('standard-brand-group');
const stdPriceGrp = document.getElementById('standard-price-group');
const pkgGrp      = document.getElementById('package-type-group');
const moqWrap     = document.getElementById('moq-field-wrapper');
const brandSel    = document.getElementById('brand-select');
const priceInput  = document.getElementById('price-input');

catSelect.addEventListener('change', function() {
    const cat = this.value;
    const lower = cat.toLowerCase();
    const isVariant = lower.includes('panel') || lower.includes('battery') || lower.includes('inverter');
    const isPkg = lower.includes('package');

    document.getElementById('preview-cat').textContent = cat || 'Category';

    // Package type
    pkgGrp.style.display = isPkg ? 'block' : 'none';

    if (isVariant) {
        stdBrandGrp.style.display = 'none';
        stdPriceGrp.style.display = 'none';
        brandSel.removeAttribute('required');
        priceInput.removeAttribute('required');
        brandCard.style.display = 'block';
        loadBrandChecklist(cat);
    } else {
        brandCard.style.display = 'none';
        checklistEl.innerHTML = '';
        stdBrandGrp.style.display = 'block';
        stdPriceGrp.style.display = 'block';
        brandSel.setAttribute('required','required');
        priceInput.setAttribute('required','required');
        if (cat) loadStandardBrands(cat);
    }
});

// ── Load standard brand dropdown ─────────────────────────────────────────
function loadStandardBrands(cat) {
    brandSel.innerHTML = '<option value="">Loading…</option>';
    brandSel.disabled = true;
    fetch(`../../controllers/brand_data.php?category=${encodeURIComponent(cat)}`)
        .then(r => r.json())
        .then(brands => {
            brandSel.innerHTML = '<option value="">Select brand</option>';
            brands.forEach(b => {
                const o = document.createElement('option');
                o.value = b; o.textContent = b;
                if (b === INIT_PROD.brandName) o.selected = true;
                brandSel.appendChild(o);
            });
            brandSel.disabled = false;
        })
        .catch(() => { brandSel.innerHTML = '<option value="">Failed to load</option>'; });
}

// ── Load brand checklist ─────────────────────────────────────────────────
function loadBrandChecklist(cat) {
    checklistEl.innerHTML = '<p style="color:#64748b;font-size:13px;">Loading brands…</p>';
    fetch(`../../get-supplier-brands.php?category=${encodeURIComponent(cat)}`)
        .then(r => r.json())
        .then(brands => {
            if (!brands.length) {
                checklistEl.innerHTML = '<p style="color:#ef4444;font-size:13px;">No brands found for this category.</p>';
                return;
            }
            checklistEl.innerHTML = '';
            brands.forEach(brand => {
                const bid  = brand.brand_id;
                const exV  = INIT_VARIANTS[bid] || null;
                const checked = !!exV;

                const row = document.createElement('div');
                row.className = 'brand-variant-row';

                const thumbSrc = (exV && exV.image) ? `../../${exV.image}` : '';
                const exPrice  = exV ? exV.price : '';

                row.innerHTML = `
                    <div class="brand-label">
                        <input type="checkbox" id="brand-check-${bid}" name="brand_ids[]"
                               value="${bid}" ${checked ? 'checked' : ''}>
                        <span>${brand.brand_name}</span>
                    </div>
                    <div>
                        <div class="brand-sub-label">Variant Price (PHP)<span style="color:red"> *</span></div>
                        <input type="number" id="price-${bid}" name="brand_price[${bid}]"
                               class="brand-price-input" step="0.01" placeholder="0.00"
                               value="${exPrice}"
                               ${checked ? '' : 'disabled'}>
                    </div>
                    <div>
                        <div class="brand-sub-label">Variant Image<span style="color:red"> *</span></div>
                        <input type="file" id="file-${bid}" name="brand_image_${bid}"
                               class="brand-image-input" accept="image/*"
                               ${checked ? '' : 'disabled'}>
                        ${thumbSrc ? `<img src="${thumbSrc}" class="variant-img-thumb" style="display:block;" alt="${brand.brand_name}">` : ''}
                    </div>
                `;

                const cb     = row.querySelector(`#brand-check-${bid}`);
                const pInput = row.querySelector(`#price-${bid}`);
                const fInput = row.querySelector(`#file-${bid}`);

                cb.addEventListener('change', function() {
                    if (this.checked) {
                        pInput.removeAttribute('disabled');
                        fInput.removeAttribute('disabled');
                    } else {
                        pInput.setAttribute('disabled','disabled');
                        pInput.value = '';
                        fInput.setAttribute('disabled','disabled');
                        fInput.value = '';
                    }
                });

                checklistEl.appendChild(row);
            });
        })
        .catch(() => {
            checklistEl.innerHTML = '<p style="color:#ef4444;font-size:13px;">Error loading brands.</p>';
        });
}

// ── Sync Quill before submit ─────────────────────────────────────────────
document.getElementById('editProductForm').addEventListener('submit', function(e) {
    const raw = quill.root.innerHTML;
    hiddenDesc.value = (raw === '<p><br></p>') ? '' : raw;
    document.getElementById('saveBtn').disabled = true;
    document.getElementById('saveBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';
}, { capture: true });

// ── MOQ Toggle Handler ────────────────────────────────────────────────────
const moqToggleBtn = document.getElementById('moq-toggle-btn');
const moqInput     = document.getElementById('moq-input');

if (moqToggleBtn && moqInput) {
    moqToggleBtn.addEventListener('click', function () {
        const isEnabled = moqInput.hasAttribute('disabled');
        if (isEnabled) {
            moqInput.removeAttribute('disabled');
            moqInput.style.background = '#f8fafc';
            moqInput.style.opacity = '1';
            if (parseInt(moqInput.value) < 5) {
                moqInput.value = 5;
            }
            moqToggleBtn.innerHTML = '<i class="fas fa-check"></i> MOQ Set';
            moqToggleBtn.style.background = '#f59e0b';
            moqToggleBtn.style.color = '#fff';
            moqToggleBtn.style.borderColor = '#f59e0b';
        } else {
            moqInput.setAttribute('disabled', 'disabled');
            moqInput.style.background = '#e2e8f0';
            moqInput.style.opacity = '0.7';
            moqInput.value = 1;
            moqToggleBtn.innerHTML = 'Set as MOQ';
            moqToggleBtn.style.background = '#fff';
            moqToggleBtn.style.color = '#64748b';
            moqToggleBtn.style.borderColor = '#e2e8f0';
        }
    });
}

// ── Boot on DOM ready ─────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    // Trigger category change to wire up the form correctly
    catSelect.dispatchEvent(new Event('change'));
});
</script>
</body>
</html>
