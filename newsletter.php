<?php
session_start();

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: views/login.php");
    exit;
}

include "config/dbconn.php";

$firstName = $_SESSION['firstName'] ?? 'User';
$lastName  = $_SESSION['lastName']  ?? '';
$fullName  = trim($firstName . ' ' . $lastName);
$initials  = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));

// Fetch subscriber count
$subscriberCount = 0;
$countResult = $conn->query("SELECT COUNT(*) AS total FROM subscription_tbl WHERE status = 'confirmed'");
if ($countResult) {
    $subscriberCount = $countResult->fetch_assoc()['total'];
}

// Fetch recent newsletter history
$newsletters = [];
$histResult = $conn->query("SELECT * FROM newsletters ORDER BY sent_at DESC LIMIT 10");
if ($histResult && $histResult->num_rows > 0) {
    while ($row = $histResult->fetch_assoc()) {
        $newsletters[] = $row;
    }
}

// Fetch products with images for product picker
$allProducts = [];
$prodResult = $conn->query("
    SELECT p.id, p.displayName, p.brandName, p.price, p.category, pi.image_path
    FROM product p
    LEFT JOIN product_images pi ON p.id = pi.product_id
    WHERE pi.image_path IS NOT NULL
      AND p.status = 'Active'
    GROUP BY p.id
    ORDER BY p.id DESC
    LIMIT 50
");
if ($prodResult && $prodResult->num_rows > 0) {
    while ($row = $prodResult->fetch_assoc()) {
        $allProducts[] = $row;
    }
}

$message     = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject     = trim($_POST['subject']      ?? '');
    $body        = trim($_POST['body']         ?? '');
    $featuredIds = $_POST['featured_products'] ?? [];

    if (!empty($subject) && !empty($body)) {
        // Get all confirmed subscribers
        $stmt = $conn->prepare("SELECT email FROM subscription_tbl WHERE status = 'confirmed'");
        $stmt->execute();
        $result = $stmt->get_result();
        $subscribers = [];
        while ($row = $result->fetch_assoc()) {
            $subscribers[] = $row['email'];
        }
        $stmt->close();

        // Build featured products HTML block
        $featuredHtml = '';
        if (!empty($featuredIds)) {
            $featuredHtml .= "<div style='padding:0 30px 10px;'><h2 style='color:#0a5c3d;font-size:18px;border-bottom:2px solid #FFC107;padding-bottom:8px;margin-bottom:20px;'>&#x2728; Featured Products</h2><table width='100%' cellpadding='0' cellspacing='0'>";
            $chunks = array_chunk($featuredIds, 2);
            foreach ($chunks as $pair) {
                $featuredHtml .= "<tr>";
                foreach ($pair as $pid) {
                    $pid = intval($pid);
                    $pstmt = $conn->prepare("SELECT p.displayName, p.price, p.category, pi.image_path FROM product p LEFT JOIN product_images pi ON p.id=pi.product_id WHERE p.id=? LIMIT 1");
                    $pstmt->bind_param('i', $pid);
                    $pstmt->execute();
                    $prow = $pstmt->get_result()->fetch_assoc();
                    $pstmt->close();
                    if ($prow) {
                        $imgUrl = 'https://solarpower.com.ph/' . ltrim($prow['image_path'], '/');
                        $pname  = htmlspecialchars($prow['displayName']);
                        $pprice = '&#8369;' . number_format($prow['price'], 2);
                        $pcat   = htmlspecialchars($prow['category']);
                        $featuredHtml .= "
                        <td width='50%' style='padding:8px;vertical-align:top;'>
                          <div style='border:1px solid #eee;border-radius:10px;overflow:hidden;text-align:center;'>
                            <img src='{$imgUrl}' alt='{$pname}' style='width:100%;height:180px;object-fit:contain;background:#fafafa;padding:10px;'>
                            <div style='padding:12px;'>
                              <p style='font-size:11px;color:#FFC107;text-transform:uppercase;font-weight:700;margin:0 0 4px;'>{$pcat}</p>
                              <p style='font-size:14px;font-weight:700;color:#1a1a2e;margin:0 0 6px;'>{$pname}</p>
                              <p style='font-size:16px;font-weight:800;color:#0a5c3d;margin:0 0 10px;'>{$pprice}</p>
                              <a href='https://solarpower.com.ph/product.php' style='background:#FFC107;color:#1a1a2e;padding:7px 18px;border-radius:20px;font-size:12px;font-weight:700;text-decoration:none;'>View Product</a>
                            </div>
                          </div>
                        </td>";
                    }
                }
                if (count($pair) === 1) $featuredHtml .= "<td width='50%'></td>";
                $featuredHtml .= "</tr>";
            }
            $featuredHtml .= "</table></div>";
        }

        if (!empty($subscribers)) {
            $htmlBody = "
            <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;background:#fff;border:1px solid #eee;'>
              <div style='background:linear-gradient(135deg,#0a5c3d,#2a7a5b);padding:28px 40px;text-align:center;'>
                <img src='https://solarpower.com.ph/assets/img/new_logo.png' alt='SolarPower Energy' style='height:55px;margin-bottom:12px;'>
                <h1 style='color:#fff;margin:0;font-size:22px;'>" . htmlspecialchars($subject) . "</h1>
              </div>
              <div style='padding:32px 40px;font-size:15px;color:#444;line-height:1.7;'>
                " . nl2br(htmlspecialchars($body)) . "
              </div>
              {$featuredHtml}
              <div style='background:#f8f9fa;padding:20px 40px;text-align:center;border-top:1px solid #eee;margin-top:20px;'>
                <a href='https://solarpower.com.ph/product.php' style='background:#0a5c3d;color:#fff;padding:11px 28px;border-radius:25px;font-size:14px;font-weight:700;text-decoration:none;display:inline-block;margin-bottom:16px;'>Shop All Products</a>
                <p style='color:#aaa;font-size:11px;margin:0;'>&copy; " . date('Y') . " SolarPower Energy Corporation &nbsp;&middot;&nbsp; <a href='https://solarpower.com.ph' style='color:#0a5c3d;'>solarpower.com.ph</a></p>
              </div>
            </div>";

            $headers  = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: SolarPower Energy <solar@solarpower.com.ph>\r\n";
            $headers .= "Reply-To: solar@solarpower.com.ph\r\n";

            $sent = 0;
            foreach ($subscribers as $email) {
                if (mail($email, $subject, $htmlBody, $headers)) $sent++;
            }

            $logStmt = $conn->prepare("INSERT INTO newsletters (subject, message, sent_at) VALUES (?, ?, NOW())");
            if ($logStmt) {
                $logStmt->bind_param('ss', $subject, $body);
                $logStmt->execute();
                $logStmt->close();
            }

            $message = "Newsletter sent to {$sent} of " . count($subscribers) . " subscriber(s)!";
            $messageType = 'success';
        } else {
            $message = 'No confirmed subscribers found.';
            $messageType = 'warning';
        }
    } else {
        $message = 'Please fill in both Subject and Message.';
        $messageType = 'danger';
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/icon.png">
    <title>Newsletter - SolarPower Staff</title>
    <link rel="stylesheet" href="views/staff/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ── Newsletter Page ── */
        .nl-wrap { padding: 28px 24px; }

        .nl-topbar { margin-bottom: 26px; }
        .nl-topbar h2 { font-size: 22px; font-weight: 700; color: #1a1a2e; margin: 0 0 4px; }
        .nl-topbar h2 i { color: #FFC107; margin-right: 8px; }
        .nl-topbar p  { color: #888; font-size: 13px; margin: 0; }

        /* Stats row */
        .nl-stats { display: flex; gap: 14px; margin-bottom: 26px; flex-wrap: wrap; }
        .nl-stat {
            display: flex; align-items: center; gap: 12px;
            background: #fff; border-radius: 12px; padding: 14px 20px;
            flex: 1; min-width: 130px;
            box-shadow: 0 1px 5px rgba(0,0,0,.06); border: 1px solid #f0f0f0;
        }
        .nl-stat i { font-size: 22px; }
        .nl-stat-num { display: block; font-size: 26px; font-weight: 700; line-height: 1.1; }
        .nl-stat-lbl { display: block; font-size: 11px; color: #999; margin-top: 2px; text-transform: uppercase; letter-spacing: .4px; }
        .nl-stat-subs { border-left: 4px solid #FFC107; } .nl-stat-subs i { color: #FFC107; }
        .nl-stat-sent { border-left: 4px solid #28a745; } .nl-stat-sent i { color: #28a745; }

        /* Layout */
        .nl-grid { display: grid; grid-template-columns: 1fr 380px; gap: 20px; align-items: start; }
        @media (max-width: 960px) { .nl-grid { grid-template-columns: 1fr; } }

        /* Compose card */
        .nl-card {
            background: #fff; border-radius: 14px;
            box-shadow: 0 1px 6px rgba(0,0,0,.06); border: 1px solid #f0f0f0;
            overflow: hidden;
        }
        .nl-card-head {
            display: flex; align-items: center; gap: 10px;
            padding: 16px 20px; border-bottom: 1px solid #f0f0f0;
            background: #fafafa;
        }
        .nl-card-head h3 { margin: 0; font-size: 15px; font-weight: 700; color: #1a1a2e; }
        .nl-card-head i { color: #FFC107; font-size: 16px; }
        .nl-card-body { padding: 20px; }

        .nl-field { margin-bottom: 16px; }
        .nl-field label { display: block; font-size: 12px; font-weight: 700; color: #555; margin-bottom: 6px; text-transform: uppercase; letter-spacing: .4px; }
        .nl-field input, .nl-field textarea {
            width: 100%; padding: 10px 14px;
            border: 1.5px solid #e0e0e0; border-radius: 8px;
            font-size: 14px; outline: none; font-family: inherit;
            transition: border-color .2s;
            background: #fafafa;
        }
        .nl-field input:focus, .nl-field textarea:focus { border-color: #FFC107; background: #fff; box-shadow: 0 0 0 3px rgba(255,193,7,.12); }
        .nl-field textarea { resize: vertical; min-height: 200px; }

        /* Preview image */
        .nl-preview-img {
            width: 100%; border-radius: 10px; overflow: hidden;
            margin-bottom: 16px; border: 1.5px solid #f0f0f0;
        }
        .nl-preview-img .nl-preview-header {
            background: linear-gradient(135deg, #0a5c3d, #2a7a5b);
            padding: 20px; text-align: center;
        }
        .nl-preview-img .nl-preview-header img { height: 44px; }
        .nl-preview-img .nl-preview-header p { color: rgba(255,255,255,.8); font-size: 12px; margin: 8px 0 0; }
        .nl-preview-img .nl-preview-body { padding: 18px 20px; font-size: 13px; color: #555; line-height: 1.6; }
        .nl-preview-img .nl-preview-footer { padding: 14px 20px; background: #f8f9fa; text-align: center; font-size: 11px; color: #aaa; border-top: 1px solid #eee; }

        /* Send button */
        .nl-send-btn {
            width: 100%; padding: 12px; border: none; border-radius: 9px;
            background: linear-gradient(135deg, #FFC107, #e6a800);
            color: #333; font-size: 15px; font-weight: 700;
            cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: all .2s;
        }
        .nl-send-btn:hover { background: linear-gradient(135deg, #e6a800, #cc9500); transform: translateY(-1px); box-shadow: 0 4px 14px rgba(255,193,7,.35); }

        /* Alert */
        .nl-alert { padding: 13px 16px; border-radius: 9px; font-size: 14px; font-weight: 600; margin-bottom: 18px; display: flex; align-items: center; gap: 10px; }
        .nl-alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .nl-alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffe082; }
        .nl-alert-danger  { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* History table */
        .nl-table-wrap { background: #fff; border-radius: 12px; box-shadow: 0 1px 6px rgba(0,0,0,.06); border: 1px solid #f0f0f0; overflow: hidden; margin-top: 20px; }
        .nl-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .nl-table thead tr { background: #2a7a5b; }
        .nl-table th { padding: 11px 14px; text-align: left; font-weight: 700; color: #fff; font-size: 11px; letter-spacing: .4px; }
        .nl-table td { padding: 12px 14px; border-bottom: 1px solid #f5f5f5; vertical-align: middle; color: #444; }
        .nl-table tbody tr:last-child td { border-bottom: none; }
        .nl-table tbody tr:hover { background: #fffdf0; }
        .nl-sub-preview { max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #888; }

        /* Product Picker */
        .nl-picker-label { font-size: 12px; font-weight: 700; color: #555; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 10px; display: block; }
        .nl-prod-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; max-height: 320px; overflow-y: auto; padding-right: 4px; }
        .nl-prod-grid::-webkit-scrollbar { width: 4px; } .nl-prod-grid::-webkit-scrollbar-thumb { background: #ddd; border-radius: 4px; }
        .nl-prod-card { position: relative; border: 2px solid #eee; border-radius: 10px; overflow: hidden; cursor: pointer; transition: all .2s; background: #fafafa; }
        .nl-prod-card:hover { border-color: #FFC107; background: #fffdf0; }
        .nl-prod-card.selected { border-color: #FFC107; background: #fffdf0; }
        .nl-prod-card.selected::after { content: '\2713'; position: absolute; top: 6px; right: 6px; width: 20px; height: 20px; background: #FFC107; color: #333; border-radius: 50%; font-size: 11px; font-weight: 900; display: flex; align-items: center; justify-content: center; }
        .nl-prod-card input[type=checkbox] { display: none; }
        .nl-prod-card img { width: 100%; height: 80px; object-fit: contain; padding: 6px; background: #fff; border-bottom: 1px solid #f0f0f0; }
        .nl-prod-card-info { padding: 6px 8px; }
        .nl-prod-card-info p { margin: 0; font-size: 11px; line-height: 1.4; }
        .nl-prod-card-name { font-weight: 700; color: #1a1a2e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .nl-prod-card-price { color: #0a5c3d; font-weight: 800; }
        .nl-prod-empty { padding: 20px; text-align: center; color: #bbb; font-size: 13px; grid-column: span 3; }
        .nl-sel-count { font-size: 11px; color: #888; margin-top: 6px; }
    </style>
</head>
<body>
<div class="container">

    <!-- ══ SIDEBAR ══ -->
    <aside class="sidebar">
        <button class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fas fa-chevron-left"></i>
        </button>
        <div class="logo">
            <a href="views/staff/dashboard.php">
                <img src="assets/img/new_logo.png" alt="Solar Power Logo">
            </a>
        </div>

        <div class="menu-item" onclick="location.href='views/staff/dashboard.php'" data-tooltip="Dashboard">
            <i class="fas fa-chart-line"></i><span>Dashboard</span>
        </div>

        <div class="menu-label">CUSTOMER OPERATIONS</div>
        <div class="menu-item" onclick="location.href='views/staff/dashboard.php'" data-tooltip="Inquiries">
            <i class="fas fa-envelope-open-text"></i><span>Inquiries</span>
        </div>
        <div class="menu-item" onclick="location.href='views/staff/dashboard.php'" data-tooltip="Clients">
            <i class="fas fa-users"></i><span>Clients</span>
        </div>

        <div class="menu-label">PRODUCT MANAGEMENT</div>
        <div class="menu-item" onclick="location.href='views/staff/dashboard.php'" data-tooltip="Brands">
            <i class="fas fa-trademark"></i><span>Brands</span>
        </div>
        <div class="menu-item" onclick="location.href='views/staff/dashboard.php'" data-tooltip="Categories">
            <i class="fas fa-tags"></i><span>Categories</span>
        </div>
        <div class="menu-item" onclick="location.href='views/staff/dashboard.php'" data-tooltip="Product">
            <i class="fas fa-box"></i><span>Product</span>
        </div>
        <div class="menu-item" onclick="location.href='views/staff/dashboard.php'" data-tooltip="Promo Banners">
            <i class="fas fa-images"></i><span>Promo Banners</span>
        </div>

        <div class="menu-label">SALES & TRANSACTIONS</div>
        <div class="menu-item" onclick="location.href='views/staff/dashboard.php'" data-tooltip="Tracking">
            <i class="fas fa-map-marker-alt"></i><span>Tracking</span>
        </div>
        <div class="menu-item" onclick="location.href='views/staff/dashboard.php'" data-tooltip="Orders">
            <i class="fas fa-shopping-bag"></i><span>Orders</span>
        </div>
        <div class="menu-item" onclick="location.href='views/staff/dashboard.php'" data-tooltip="Quotation">
            <i class="fas fa-file-invoice"></i><span>Quotation</span>
        </div>

        <div class="menu-label">MARKETING</div>
        <div class="menu-item active" data-tooltip="Newsletter">
            <i class="fas fa-paper-plane"></i><span>Newsletter</span>
        </div>

        <div class="menu-label">SUPPLY MANAGEMENT</div>
        <div class="menu-item" onclick="location.href='views/staff/dashboard.php'" data-tooltip="Suppliers">
            <i class="fas fa-truck"></i><span>Suppliers</span>
        </div>

        <div class="menu-label">ACCOUNT</div>
        <div class="menu-item" onclick="location.href='views/staff/dashboard.php'" data-tooltip="My Profile">
            <i class="fas fa-user-circle"></i><span>My Profile</span>
        </div>
    </aside>

    <!-- ══ MAIN CONTENT ══ -->
    <main class="main-content">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <h1 id="page-title">Newsletter</h1>
                <p class="section-subtitle">Welcome back, <?= htmlspecialchars($firstName) ?></p>
                <p class="section-subtitle">Compose and send email newsletters to all confirmed subscribers</p>
            </div>
            <div class="user-menu">
                <div class="user-avatar staff-header-avatar staff-header-avatar-small"
                     onclick="document.getElementById('userDropdown').classList.toggle('show')">
                    <span class="staff-avatar-initials"><?= htmlspecialchars($initials) ?></span>
                </div>
                <div class="dropdown-menu" id="userDropdown">
                    <div class="dropdown-header"><?= htmlspecialchars($fullName) ?></div>
                    <ul>
                        <li><a href="controllers/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Page Body -->
        <div class="nl-wrap">
            <!-- Top bar -->
            <div class="nl-topbar">
                <h2><i class="fas fa-paper-plane"></i> Send Newsletter</h2>
                <p>Broadcast a message to all confirmed email subscribers</p>
            </div>

            <!-- Stats -->
            <div class="nl-stats">
                <div class="nl-stat nl-stat-subs">
                    <i class="fas fa-users"></i>
                    <div>
                        <span class="nl-stat-num"><?= $subscriberCount ?></span>
                        <span class="nl-stat-lbl">Confirmed Subscribers</span>
                    </div>
                </div>
                <div class="nl-stat nl-stat-sent">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <span class="nl-stat-num"><?= count($newsletters) ?></span>
                        <span class="nl-stat-lbl">Newsletters Sent</span>
                    </div>
                </div>
            </div>

            <!-- Alert -->
            <?php if ($message): ?>
            <div class="nl-alert nl-alert-<?= $messageType ?>">
                <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : ($messageType === 'warning' ? 'exclamation-triangle' : 'times-circle') ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>

            <!-- Grid -->
            <div class="nl-grid">

                <!-- Compose Form -->
                <div>
                    <div class="nl-card">
                        <div class="nl-card-head">
                            <i class="fas fa-pen-nib"></i>
                            <h3>Compose Newsletter</h3>
                        </div>
                        <div class="nl-card-body">
                            <form method="post" id="newsletterForm">
                                <div class="nl-field">
                                    <label for="subject">Email Subject</label>
                                    <input type="text" id="subject" name="subject"
                                           placeholder="e.g. Special Solar Promo This May!" required>
                                </div>
                                <div class="nl-field">
                                    <label for="body">Message Body</label>
                                    <textarea id="body" name="body" rows="12"
                                              placeholder="Write your newsletter content here..."
                                              oninput="updatePreview()" required></textarea>
                                </div>
                                <div class="nl-field" style="margin-bottom:20px;">
                                    <span class="nl-picker-label"><i class="fas fa-box" style="color:#FFC107;"></i> Featured Products <span style="color:#aaa;font-weight:400;">(optional — max 6)</span></span>
                                    <div class="nl-prod-grid" id="productGrid">
                                        <?php if (empty($allProducts)): ?>
                                        <div class="nl-prod-empty"><i class="fas fa-box-open" style="font-size:28px;display:block;margin-bottom:8px;"></i>No products with images found</div>
                                        <?php else: ?>
                                        <?php foreach ($allProducts as $prod): ?>
                                        <label class="nl-prod-card" onclick="toggleProduct(this)">
                                            <input type="checkbox" name="featured_products[]" value="<?= $prod['id'] ?>">
                                            <img src="<?= htmlspecialchars($prod['image_path']) ?>" alt="<?= htmlspecialchars($prod['displayName']) ?>" onerror="this.src='assets/img/placeholder.png'">
                                            <div class="nl-prod-card-info">
                                                <p class="nl-prod-card-name"><?= htmlspecialchars($prod['displayName']) ?></p>
                                                <p class="nl-prod-card-price">₱<?= number_format($prod['price'], 0) ?></p>
                                            </div>
                                        </label>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <p class="nl-sel-count" id="selCount">0 product(s) selected</p>
                                </div>
                                <button type="submit" class="nl-send-btn">
                                    <i class="fas fa-paper-plane"></i>
                                    Send to <?= $subscriberCount ?> Subscriber<?= $subscriberCount !== 1 ? 's' : '' ?>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- History Table -->
                    <?php if (!empty($newsletters)): ?>
                    <div class="nl-card-head" style="background:#fff; border-radius:14px 14px 0 0; border:1px solid #f0f0f0; border-bottom:none; margin-top:20px;">
                        <i class="fas fa-history" style="color:#FFC107;font-size:16px;"></i>
                        <h3 style="margin:0;font-size:15px;font-weight:700;color:#1a1a2e;">Recent Newsletters</h3>
                    </div>
                    <div class="nl-table-wrap" style="border-radius:0 0 14px 14px; margin-top:0;">
                        <table class="nl-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Subject</th>
                                    <th>Preview</th>
                                    <th>Sent At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($newsletters as $i => $nl): ?>
                                <tr>
                                    <td style="color:#bbb;font-weight:600;"><?= $i + 1 ?></td>
                                    <td style="font-weight:600;"><?= htmlspecialchars($nl['subject']) ?></td>
                                    <td class="nl-sub-preview"><?= htmlspecialchars(substr($nl['message'], 0, 80)) ?>...</td>
                                    <td style="white-space:nowrap;color:#888;"><?= isset($nl['sent_at']) ? date('M d, Y g:i A', strtotime($nl['sent_at'])) : '—' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Email Preview Panel -->
                <div>
                    <div class="nl-card">
                        <div class="nl-card-head">
                            <i class="fas fa-eye"></i>
                            <h3>Email Preview</h3>
                        </div>
                        <div class="nl-card-body" style="padding:16px;">
                            <div class="nl-preview-img">
                                <div class="nl-preview-header">
                                    <img src="assets/img/new_logo.png" alt="SolarPower Energy">
                                    <p id="preview-subject" style="color:rgba(255,255,255,.8);font-size:13px;margin:8px 0 0;">Your newsletter subject will appear here</p>
                                </div>
                                <div class="nl-preview-body" id="preview-body">
                                    <span style="color:#ccc;">Your message content will appear here as you type…</span>
                                </div>
                                <div class="nl-preview-footer">
                                    &copy; <?= date('Y') ?> SolarPower Energy Corporation &nbsp;·&nbsp;
                                    <a href="https://solarpower.com.ph" style="color:#0a5c3d;">Visit Website</a>
                                </div>
                            </div>

                            <div style="background:#f8f9fa;border-radius:9px;padding:14px;font-size:12px;color:#777;line-height:1.7;">
                                <strong style="color:#444;display:block;margin-bottom:6px;">
                                    <i class="fas fa-info-circle" style="color:#FFC107;"></i> Tips
                                </strong>
                                <ul style="margin:0;padding-left:16px;">
                                    <li>Keep subject lines under 60 characters</li>
                                    <li>Start with the most important information</li>
                                    <li>Include a clear call-to-action</li>
                                    <li>Avoid excessive punctuation or ALL CAPS</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- /nl-grid -->
        </div><!-- /nl-wrap -->
    </main>

</div><!-- /container -->

<script>
    // Sidebar toggle (reuse staff dashboard logic)
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const main    = document.querySelector('.main-content');
        sidebar.classList.toggle('collapsed');
        main.classList.toggle('expanded');
    }

    // Live preview
    function updatePreview() {
        const body    = document.getElementById('body').value;
        const subject = document.getElementById('subject').value;

        const previewBody    = document.getElementById('preview-body');
        const previewSubject = document.getElementById('preview-subject');

        previewSubject.textContent = subject || 'Your newsletter subject will appear here';

        if (body.trim()) {
            previewBody.innerHTML = body.replace(/\n/g, '<br>');
        } else {
            previewBody.innerHTML = '<span style="color:#ccc;">Your message content will appear here as you type…</span>';
        }
    }

    document.getElementById('subject').addEventListener('input', updatePreview);

    // Product picker toggle
    function toggleProduct(label) {
        const cb      = label.querySelector('input[type=checkbox]');
        const MAX     = 6;
        const checked = document.querySelectorAll('#productGrid input:checked').length;

        if (!cb.checked && checked >= MAX) {
            alert('You can feature a maximum of ' + MAX + ' products.');
            return;
        }
        cb.checked = !cb.checked;
        label.classList.toggle('selected', cb.checked);

        const count = document.querySelectorAll('#productGrid input:checked').length;
        document.getElementById('selCount').textContent = count + ' product(s) selected';
        updatePreview();
    }


    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('userDropdown');
        if (!e.target.closest('.user-menu')) {
            dropdown.classList.remove('show');
        }
    });
</script>
</body>
</html>