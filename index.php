<?php
session_start();

if (!function_exists('createSlug')) {
    function createSlug($text) {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        return empty($text) ? 'n-a' : $text;
    }
}

// Handle Estimate Form Submission POST Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_estimate') {
    header('Content-Type: application/json');
    require_once __DIR__ . '/config/db_pdo.php';
    try {
        $db = getPDO();
        
        // Auto-create estimates table if it doesn't exist
        $db->exec("CREATE TABLE IF NOT EXISTS `estimates` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `full_name` VARCHAR(255) NOT NULL,
            `email_address` VARCHAR(255) NOT NULL,
            `contact_number` VARCHAR(50) NOT NULL,
            `property_type` VARCHAR(50) NOT NULL,
            `complete_address` TEXT NOT NULL,
            `inspection_date` DATE NOT NULL,
            `monthly_bill` DECIMAL(10, 2) NOT NULL,
            `roof_type` VARCHAR(100) NOT NULL,
            `additional_notes` TEXT NULL,
            `status` VARCHAR(50) NOT NULL DEFAULT 'Pending',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        
        $fullname = trim($_POST['fullname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $phone_full = trim($_POST['phone_full'] ?? '');
        if ($phone_full === '') {
            $phone_full = $phone;
        }
        if (strpos($phone_full, '+63') === false && !empty($phone_full)) {
            if (strlen($phone_full) === 9 && $phone_full[0] !== '9') {
                $phone_full = '+639' . $phone_full;
            } else {
                $phone_full = '+63' . ltrim($phone_full, '0');
            }
        }
        $property_type = trim($_POST['property_type'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $inspection_date = trim($_POST['inspection_date'] ?? '');
        $bill = floatval($_POST['bill'] ?? 0);
        $roof_type = trim($_POST['roof_type'] ?? '');
        if ($roof_type === 'Other' && !empty($_POST['roof_type_other'])) {
            $roof_type = trim($_POST['roof_type_other']);
        }
        $notes = trim($_POST['notes'] ?? '');
        
        if (empty($fullname) || empty($email) || empty($phone_full) || empty($address) || empty($inspection_date)) {
            throw new Exception("Please fill in all required fields.");
        }
        
        $sql = "INSERT INTO `estimates` (full_name, email_address, contact_number, property_type, complete_address, inspection_date, monthly_bill, roof_type, additional_notes, status) 
                VALUES (:fullname, :email, :phone, :property_type, :address, :inspection_date, :bill, :roof_type, :notes, 'Pending')";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':fullname' => $fullname,
            ':email' => $email,
            ':phone' => $phone_full,
            ':property_type' => $property_type,
            ':address' => $address,
            ':inspection_date' => $inspection_date,
            ':bill' => $bill,
            ':roof_type' => $roof_type,
            ':notes' => $notes
        ]);
        
        // Trigger Resend API call (using fallback onboarding sender for testing/unverified domains)
        $resendApiKey = 're_Fh6X1rKo_JzjtWaAfUfRiEQs5HHxE4VsV'; 
        $subject = "New Solar Estimate Request - " . $fullname;
        
        $emailBody = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px; background-color: #fcfcfc; }
                .header { background: linear-gradient(135deg, #115e59, #0f766e); color: #fff; padding: 20px; text-align: center; border-radius: 6px 6px 0 0; }
                .content { padding: 20px; background: #fff; }
                table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
                th { background-color: #f2f2f2; font-weight: bold; width: 35%; }
                .notes { background-color: #f9f9f9; padding: 15px; border-left: 4px solid #f39c12; margin-top: 15px; font-style: italic; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2 style='margin:0;'>New Solar Estimate Request</h2>
                    <p style='margin:5px 0 0 0;font-size:14px;'>SolarPower Energy Corporation</p>
                </div>
                <div class='content'>
                    <p>A new solar estimate request has been submitted. Details are below:</p>
                    <table>
                        <tr>
                            <th>Full Name</th>
                            <td>" . htmlspecialchars($fullname) . "</td>
                        </tr>
                        <tr>
                            <th>Email Address</th>
                            <td>" . htmlspecialchars($email) . "</td>
                        </tr>
                        <tr>
                            <th>Contact Number</th>
                            <td>" . htmlspecialchars($phone_full) . "</td>
                        </tr>
                        <tr>
                            <th>Property Type</th>
                            <td>" . htmlspecialchars($property_type) . "</td>
                        </tr>
                        <tr>
                            <th>Complete Address</th>
                            <td>" . htmlspecialchars($address) . "</td>
                        </tr>
                        <tr>
                            <th>Preferred Assessment Date</th>
                            <td>" . htmlspecialchars($inspection_date) . "</td>
                        </tr>
                        <tr>
                            <th>Monthly Bill</th>
                            <td>₱ " . number_format($bill, 2) . "</td>
                        </tr>
                        <tr>
                            <th>Roof Type</th>
                            <td>" . htmlspecialchars($roof_type) . "</td>
                        </tr>
                    </table>";
                    
        if (!empty($notes)) {
            $emailBody .= "
                    <h3 style='margin-top:20px;color:#0f766e;'>Additional Notes</h3>
                    <div class='notes'>" . nl2br(htmlspecialchars($notes)) . "</div>";
        }
        
        $emailBody .= "
                </div>
            </div>
        </body>
        </html>";
        
        $payload = [
            'from' => 'SolarPower Energy <onboarding@resend.dev>',
            'to' => ['solar@solarpower.com.ph'],
            'subject' => $subject,
            'html' => $emailBody
        ];
        
        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $resendApiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo json_encode([
            'success' => true,
            'message' => 'Estimate request saved and notification sent!'
        ]);
        exit;
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

$isLoggedIn = isset($_SESSION['user_id']);
include "config/dbconn.php";

/* ---------- Fetch Calculator Settings ---------- */
$calc_settings = [
    'solar_panel_wattage' => 400,
    'kwh_rate' => 12.00,
    'average_sun_hours' => 4.50,
    'card1_title' => 'REQUIRED SYSTEM (KWP)',
    'card1_icon' => 'assets/img/system-size.png',
    'card2_title' => 'SOLAR PANELS',
    'card2_icon' => 'assets/img/panels.png',
    'card3_title' => 'EST. MONTHLY SAVINGS',
    'card3_icon' => 'assets/img/monthly-savings.png',
    'card4_title' => 'EST. YEARLY SAVINGS',
    'card4_icon' => 'assets/img/yearly-savings.png'
];
$res_calc = $conn->query("SELECT * FROM calculator_settings LIMIT 1");
if ($res_calc && $row_calc = $res_calc->fetch_assoc()) {
    $calc_settings = $row_calc;
}

$logo_brands = [];
$res_logos = $conn->query("SELECT brand_name, logo_image FROM brands WHERE logo_image IS NOT NULL AND logo_image != '' AND COALESCE(is_visible, 1) = 1");
if ($res_logos) {
    while ($row = $res_logos->fetch_assoc()) {
        $logo_brands[] = [
            'brand_name' => $row['brand_name'],
            'logo_image' => 'uploads/logos/' . $row['logo_image'],
            'is_fallback' => false
        ];
    }
}
if (empty($logo_brands)) {
    $logo_brands = [
        ['brand_name' => 'Ian Solar', 'logo_image' => 'assets/img/iansolar.png', 'is_fallback' => true],
        ['brand_name' => 'LVTopsun', 'logo_image' => 'assets/img/lvtopsun.png', 'is_fallback' => true],
        ['brand_name' => 'Jinko Solar', 'logo_image' => 'assets/img/jinko.png', 'is_fallback' => true],
        ['brand_name' => 'HyxiPower', 'logo_image' => 'assets/img/hyxipower.png', 'is_fallback' => true],
        ['brand_name' => 'Hopewind', 'logo_image' => 'assets/img/Hopewind.jpg', 'is_fallback' => true],
        ['brand_name' => 'Solax Power', 'logo_image' => 'assets/img/solax.png', 'is_fallback' => true],
        ['brand_name' => 'Aiko', 'logo_image' => 'assets/img/aiko.png', 'is_fallback' => true],
        ['brand_name' => 'Hoymiles', 'logo_image' => 'assets/img/hoymiles.png', 'is_fallback' => true],
        ['brand_name' => 'Trina Solar', 'logo_image' => 'assets/img/trinasolar.png', 'is_fallback' => true],
    ];
}

/* ---------- 2.  Fetch products (safe) ---------- */
$products = [];

$sql = "SELECT 
    p.id,
    p.displayName,
    CASE WHEN TRIM(p.brandName) = 'Hybrid System' THEN 'Package' ELSE TRIM(p.brandName) END AS brandName,
    p.price,
    p.stockQuantity,
    p.category,
    COALESCE(p.moq, 1) AS moq,
    pi.image_path
FROM product p
LEFT JOIN product_images pi 
    ON p.id = pi.product_id
WHERE pi.image_path IS NOT NULL 
  AND p.status = 'Active'
  AND (TRIM(p.brandName) = 'Hybrid' OR TRIM(p.brandName) = 'Package')
GROUP BY p.id
ORDER BY p.price ASC";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        // Add ALL products to the same array
        $products[] = $row;
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Primary Meta Tags -->
    <title>SolarPower Energy | Solar Panel Philippines | Solar Calculator & Bill Calculator</title>
    <link rel="icon" type="image/png" href="assets/img/icon.png">

    <meta name="theme-color" content="#f59e0b" />
 
    <meta name="description" content="SolarPower Energy Corporation is the Philippines' leading DOE-accredited solar panel provider. Use our free Solar Calculator and Electricity Bill Calculator to find out how much you can save. Get hybrid and On-grid solar installations for homes and businesses. Save up to 80% on electricity bills. Serving Metro Manila and nationwide." />
    <meta name="keywords" content="SolarPower Energy Corporation, solar panel Philippines, solar installation Philippines, DOE accredited solar, hybrid solar system Philippines, On-grid solar Philippines, renewable energy Philippines, solar power Manila, solar panels for home Philippines, commercial solar Philippines, save electricity bills Philippines, smart energy Philippines, solar calculator Philippines, solar calculator, electricity bill calculator Philippines, bill calculator solar, solar savings calculator Philippines, Meralco bill calculator, solar panel calculator Philippines, solar energy calculator, electricity bill savings calculator, solar ROI calculator Philippines" />
    <meta name="author" content="SolarPower Energy Corporation" />
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1" />
    <meta name="google-adsense-account" content="ca-pub-8363297627454600" />
    <link rel="canonical" href="https://solarpower.com.ph/" />
    
    <!-- Geo Tags -->
    <meta name="geo.region" content="PH" />
    <meta name="geo.placename" content="Metro Manila, Philippines" />
    <meta name="geo.position" content="14.5995;120.9842" />
    <meta name="ICBM" content="14.5995, 120.9842" />
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://solarpower.com.ph/" />
    <meta property="og:title" content="SolarPower Energy Corporation | Solar Panel, Solar Calculator & Bill Calculator Philippines" />
    <meta property="og:description" content="Philippines' leading DOE-accredited solar provider. Use our free Solar Calculator and Electricity Bill Calculator to estimate your savings. Hybrid and On-grid solar installations for homes and businesses. Save up to 80% on electricity bills!" />
    <meta property="og:image" content="https://solarpower.com.ph/assets/img/new_logo.png" />
    <meta property="og:image:alt" content="SolarPower Energy Corporation Logo" />
    <meta property="og:site_name" content="SolarPower Energy Corporation" />
    <meta property="og:locale" content="en_PH" />
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:url" content="https://solarpower.com.ph/" />
    <meta name="twitter:title" content="SolarPower Energy Corporation | Solar Calculator & Bill Calculator Philippines" />
    <meta name="twitter:description" content="Free Solar Calculator & Electricity Bill Calculator. DOE-accredited solar provider. Save up to 80% on electricity bills with Hybrid and On-grid solar solutions across the Philippines." />
    <meta name="twitter:image" content="https://solarpower.com.ph/assets/img/new_logo.png" />
    <meta name="twitter:image:alt" content="SolarPower Energy Corporation Logo" />
    
    <!-- Schema.org Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "LocalBusiness",
      "name": "SolarPower Energy Corporation",
      "alternateName": ["SolarPower Energy", "SolarPower PH"],
      "image": "https://solarpower.com.ph/assets/img/new_logo.png",
      "url": "https://solarpower.com.ph",
      "telephone": "+63-995-394-7379",
      "description": "SolarPower Energy Corporation is the Philippines' leading DOE-accredited solar panel provider offering hybrid and on-grid solar installations for residential and commercial properties.",
      "address": {
        "@type": "PostalAddress",
        "addressLocality": "Metro Manila",
        "addressRegion": "NCR",
        "addressCountry": "PH"
      },
      "geo": {
        "@type": "GeoCoordinates",
        "latitude": 14.5995,
        "longitude": 120.9842
      },
      "openingHoursSpecification": {
        "@type": "OpeningHoursSpecification",
        "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],
        "opens": "08:00",
        "closes": "17:00"
      },
      "serviceType": ["Solar Panel Installation", "Hybrid Solar System", "On-grid Solar System", "Residential Solar", "Commercial Solar", "Solar Maintenance"],
      "areaServed": {
        "@type": "Country",
        "name": "Philippines"
      },
      "hasCredential": "DOE Accredited Solar Provider",
      "sameAs": [
        "https://www.facebook.com/solarpowerenergycorp"
      ]
    }
    </script>
    
    <!-- CSS Libraries -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8363297627454600"
     crossorigin="anonymous"></script>
    <script>
      tailwind.config = {
        corePlugins: {
          preflight: false,
        },
        theme: {
          extend: {
            colors: {
              amber: {
                400: '#fbbf24',
                500: '#f59e0b',
              },
              slate: {
                800: '#1e293b',
                900: '#0f172a',
              }
            }
          }
        }
      }
    </script>

    <!-- CSS Libraries -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            corePlugins: {
                preflight: false,
            },
            theme: {
                extend: {
                    colors: {
                        amber: {
                            400: '#fbbf24',
                            500: '#f59e0b',
                        },
                        slate: {
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* ── Contact Us Section (minimal redesign) ── */
        .contact-us {
            padding: 80px 0;
            background: #fff;
        }

        .contact-info h2 {
            font-size: 28px;
            color: var(--clr-dark);
            font-weight: 700;
            margin-bottom: 6px;
        }

        .contact-section-sub {
            color: var(--clr-text-secondary);
            font-size: 0.92rem;
            margin-bottom: 36px;
            line-height: 1.6;
        }

        /* Visit Us / WhatsApp block */
        .visit-us-section {
            margin-bottom: 32px;
        }

        .visit-us-section h3 {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #999;
            margin-bottom: 4px;
        }

        .visit-us-section p {
            color: var(--clr-text-secondary);
            font-size: 0.88rem;
            line-height: 1.6;
            margin-bottom: 14px;
        }

        /* Contact detail rows */
        .company-info {
            margin-bottom: 24px;
        }

        .contact-detail {
            display: flex;
            align-items: flex-start;
            margin-bottom: 18px;
            gap: 12px;
        }

        .contact-detail .icon-wrap {
            width: 36px;
            height: 36px;
            background: #f0f7f4;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .contact-detail i {
            color: var(--clr-secondary);
            font-size: 14px;
        }

        .company-info .phone-number {
            font-weight: 500;
            color: var(--clr-dark);
            cursor: pointer;
        }

        /* Hours Section */
        .hours-section {
            margin-top: 20px;
        }

        .hours-toggle {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            border: 1px solid #e5e7eb;
            padding: 12px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            color: var(--clr-dark);
            transition: var(--transition-fast);
        }

        .hours-toggle:hover {
            background: #f9fafb;
        }

        .hours-toggle strong {
            color: var(--clr-dark);
        }

        .hours-toggle i {
            transition: transform 0.3s ease;
            color: #aaa;
            font-size: 12px;
        }

        .hours-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s ease;
            margin-top: 4px;
            background: #fafafa;
            border-radius: 0 0 8px 8px;
            border: 1px solid #e5e7eb;
            border-top: none;
        }

        .hour-item {
            display: flex;
            justify-content: space-between;
            padding: 7px 16px;
            font-size: 13px;
            color: #555;
            border-bottom: 1px solid #f0f0f0;
        }

        .hour-item:last-child {
            border-bottom: none;
        }

        .hour-item span:first-child {
            font-weight: 500;
            color: var(--clr-dark);
        }

        .contact-detail strong {
            display: block;
            margin-bottom: 2px;
            color: #999;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .contact-detail p,
        .contact-detail span,
        .contact-detail a {
            color: var(--clr-dark);
            line-height: 1.6;
            margin: 0;
            font-size: 0.88rem;
        }

        .contact-detail a {
            text-decoration: none;
            color: var(--clr-dark);
        }

        .contact-detail a:hover {
            color: var(--clr-secondary);
        }

        /* Form wrapper — clean, no heavy shadow */
        .contact-form-wrapper {
            padding: 0;
            border: none;
            background: transparent;
        }

        .contact-form-wrapper h3 {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--clr-dark);
            margin-bottom: 6px;
        }

        .contact-form-sub {
            font-size: 0.88rem;
            color: var(--clr-text-secondary);
            margin-bottom: 28px;
        }

        .contact-form .form-control {
            padding: 10px 14px;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.875rem;
            background: #fafafa;
            transition: border-color 0.2s, background 0.2s;
            color: var(--clr-dark);
        }

        .contact-form .form-control:focus {
            border-color: var(--clr-secondary);
            background: #fff;
            box-shadow: none;
            outline: none;
        }

        .contact-form .input-group-text {
            background: #fafafa;
            border: 1.5px solid #e5e7eb;
            border-right: none;
            border-radius: 8px 0 0 8px;
            color: var(--clr-secondary);
            font-weight: 700;
            font-size: 0.88rem;
            padding: 10px 12px;
        }

        .contact-form .input-group .form-control {
            border-left: none;
            border-radius: 0 8px 8px 0;
        }

        .contact-form .input-group:focus-within .input-group-text {
            border-color: var(--clr-secondary);
            background: #fff;
        }

        .contact-form .input-group:focus-within .form-control {
            border-color: var(--clr-secondary);
        }

        .contact-form .input-group .input-group-text {
            font-size: 0.875rem;
            color: var(--clr-dark);
            font-weight: 500;
            user-select: none;
        }

        .contact-form textarea.form-control {
            resize: none;
        }

        .btn-submit {
            background: var(--clr-secondary);
            color: #fff;
            padding: 11px 0;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            width: 100%;
            transition: background 0.2s;
            letter-spacing: 0.04em;
        }

        .btn-submit:hover {
            background: #085231;
        }

        /* Social Links */
        .contact-social-links {
            margin-top: 28px;
        }

        .contact-social-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #999;
            margin-bottom: 10px;
        }

        .contact-social-links .social-links {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .contact-social-links .social-links a {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #2c2c2c;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .contact-social-links .social-links a:hover {
            background: var(--clr-secondary);
            color: #fff;
        }

        /* Section divider */
        .contact-divider {
            border: none;
            border-top: 1px solid #f0f0f0;
            margin: 24px 0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .whatsapp-btn {
                width: 100%;
                justify-content: center;
            }

            .contact-us {
                padding: 48px 0;
            }
        }
    </style>

<body>


    <?php include "includes/header.php" ?>

    <div class="hero-container" id="heroContainer" data-checkout-hide>
        <section class="hero" id="home">

            <div class="hero-content">
                <!-- LEFT: HERO TEXT -->
                <div class="hero-text" data-aos="fade-right">
                    <h1 style="color: #FFFFFF; font-weight: 800;">Smart Energy for Smarter Homes.</h1>
                    <p class="hero-tagline" style="color: #F2A900; font-weight: 700;">Sun Powered, Future Driven</p>
                    <p>Invest in solar today - enjoy decades of energy independence and savings.</p>
                    <div class="hero-cta d-flex flex-row gap-3">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#inspectionModal" style="background-color: #F2A900 !important; border-color: #F2A900 !important; color: #000000 !important; font-weight: bold;">GET A FREE QUOTE</button>
                        <a href="loans.php" class="btn btn-secondary text-decoration-none d-inline-flex align-items-center justify-content-center" style="border: 2px solid #FFFFFF !important; color: #FFFFFF !important; font-weight: bold; background: transparent !important;">
                            EXPLORE FINANCING
                        </a>
                    </div>
                </div>

                <!-- RIGHT: CALCULATOR WIDGET -->
                <div class="hero-calculator" data-aos="fade-left" style="max-width: 480px; width: 100%; margin-left: auto;">
                    <div class="bg-white rounded-3xl shadow-2xl p-4 border border-slate-100 text-slate-800" style="border-radius: 24px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);">
                        <!-- Header -->
                        <div class="text-center mb-4">
                            <div class="inline-flex items-center justify-center rounded-full text-amber-500 mb-2 shadow-sm" style="width: 48px; height: 48px; background-color: #FEF3C7; color: #F59E0B; display: inline-flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                                <i class="fa-regular fa-lightbulb"></i>
                            </div>
                            <h4 class="fw-bold mb-1 text-slate-900" style="font-size: 1.25rem; font-family: var(--ff-body); color: #0D5C3A;">Calculate Your Solar Savings</h4>
                            <p class="text-slate-500 small mb-0 px-2" style="font-size: 0.8rem; line-height: 1.4;">See how much you can save by switching to solar energy. Drag the slider to match your monthly electric bill.</p>
                        </div>

                        <!-- Input Section -->
                        <div class="mb-4 text-center">
                            <label for="twBillAmountHero" class="form-label small fw-bold mb-2 text-slate-600 text-uppercase tracking-wider" style="font-size: 0.75rem;">Average Monthly Electric Bill</label>
                            <div class="input-group input-group-sm mx-auto shadow-sm" style="max-width: 220px; border-radius: 8px; overflow: hidden; border: 1px solid #E2E8F0;">
                                <span class="input-group-text bg-white border-0 fw-bold" style="color: #0D5C3A; font-size: 1rem;">₱</span>
                                <input type="text" inputmode="numeric" id="twBillAmountHero" value="5,000"
                                    class="form-control border-0 fw-extrabold text-center"
                                    style="color: #0D5C3A; font-size: 1.25rem; background-color: #FFFFFF;"
                                    placeholder="0" oninput="let raw = this.value.replace(/[^0-9]/g, ''); this.value = raw ? parseInt(raw).toLocaleString('en-US') : ''; document.getElementById('twBillSliderHero').value = raw; updateHeroCalculator(raw)">
                            </div>

                            <!-- Range Slider -->
                            <div class="mt-3 px-2">
                                <input type="range" id="twBillSliderHero" min="2000" max="50000" step="500" value="5000"
                                    class="form-range custom-range w-100"
                                    oninput="document.getElementById('twBillAmountHero').value = parseInt(this.value).toLocaleString('en-US'); updateHeroCalculator(this.value)">
                                <div class="d-flex justify-content-between text-muted mt-2 fw-semibold" style="font-size: 0.72rem;">
                                    <span>₱2,000</span>
                                    <span>₱25,000</span>
                                    <span>₱50,000+</span>
                                </div>
                            </div>
                        </div>

                        <!-- Results Grid -->
                        <div class="row g-2.5">
                            <!-- System Size -->
                            <div class="col-6">
                                <div class="rounded-2xl p-3 text-center transition-all" style="border-radius: 16px; border: 1px solid rgba(13, 92, 58, 0.08); background-color: rgba(13, 92, 58, 0.02); min-height: 110px; display: flex; flex-direction: column; justify-content: space-between;">
                                    <div class="mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; overflow: hidden; background: #fff; border-radius: 50%; padding: 4px; box-shadow: 0 4px 10px rgba(0,0,0,0.04);">
                                        <img src="<?= htmlspecialchars($calc_settings['card1_icon']) ?>" alt="<?= htmlspecialchars($calc_settings['card1_title']) ?>" style="width: 100%; height: 100%; object-fit: contain;">
                                    </div>
                                    <div class="fw-extrabold" style="color: #0D5C3A; font-size: 1.35rem; line-height: 1.1;" id="twKwValueHero">2.4</div>
                                    <div class="text-uppercase text-slate-500 fw-bold mt-1" style="font-size: 0.65rem; letter-spacing: 0.5px; line-height: 1.2;"><?= htmlspecialchars($calc_settings['card1_title']) ?></div>
                                </div>
                            </div>

                            <!-- Solar Panels -->
                            <div class="col-6">
                                <div class="rounded-2xl p-3 text-center transition-all" style="border-radius: 16px; border: 1px solid rgba(242, 169, 0, 0.08); background-color: rgba(242, 169, 0, 0.02); min-height: 110px; display: flex; flex-direction: column; justify-content: space-between;">
                                    <div class="mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; overflow: hidden; background: #fff; border-radius: 50%; padding: 4px; box-shadow: 0 4px 10px rgba(0,0,0,0.04);">
                                        <img src="<?= htmlspecialchars($calc_settings['card2_icon']) ?>" alt="<?= htmlspecialchars($calc_settings['card2_title']) ?>" style="width: 100%; height: 100%; object-fit: contain;">
                                    </div>
                                    <div class="fw-extrabold" style="color: #0D5C3A; font-size: 1.35rem; line-height: 1.1;" id="twPanelsValueHero">6</div>
                                    <div class="text-uppercase text-slate-500 fw-bold mt-1" style="font-size: 0.65rem; letter-spacing: 0.5px; line-height: 1.2;"><?= htmlspecialchars($calc_settings['card2_title']) ?></div>
                                </div>
                            </div>

                            <!-- Est. Monthly Savings -->
                            <div class="col-6">
                                <div class="rounded-2xl p-3 text-center transition-all" style="border-radius: 16px; border: 1px solid rgba(13, 92, 58, 0.12); background-color: rgba(13, 92, 58, 0.04) !important; min-height: 110px; display: flex; flex-direction: column; justify-content: space-between;">
                                    <div class="mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; overflow: hidden; background: #fff; border-radius: 50%; padding: 4px; box-shadow: 0 4px 10px rgba(0,0,0,0.04);">
                                        <img src="<?= htmlspecialchars($calc_settings['card3_icon']) ?>" alt="<?= htmlspecialchars($calc_settings['card3_title']) ?>" style="width: 100%; height: 100%; object-fit: contain;">
                                    </div>
                                    <div class="fw-extrabold text-success" style="font-size: 1.35rem; line-height: 1.1;" id="twMonthlySavingsHero">0</div>
                                    <div class="text-uppercase text-slate-500 fw-bold mt-1" style="font-size: 0.65rem; letter-spacing: 0.5px; line-height: 1.2;"><?= htmlspecialchars($calc_settings['card3_title']) ?></div>
                                </div>
                            </div>

                            <!-- Est. Yearly Savings -->
                            <div class="col-6">
                                <div class="rounded-2xl p-3 text-center transition-all" style="border-radius: 16px; border: 1px solid rgba(242, 169, 0, 0.12); background-color: rgba(242, 169, 0, 0.04) !important; min-height: 110px; display: flex; flex-direction: column; justify-content: space-between;">
                                    <div class="mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; overflow: hidden; background: #fff; border-radius: 50%; padding: 4px; box-shadow: 0 4px 10px rgba(0,0,0,0.04);">
                                        <img src="<?= htmlspecialchars($calc_settings['card4_icon']) ?>" alt="<?= htmlspecialchars($calc_settings['card4_title']) ?>" style="width: 100%; height: 100%; object-fit: contain;">
                                    </div>
                                    <div class="fw-extrabold" style="color: #F2A900; font-size: 1.35rem; line-height: 1.1;" id="twYearlySavingsHero">0</div>
                                    <div class="text-uppercase text-slate-500 fw-bold mt-1" style="font-size: 0.65rem; letter-spacing: 0.5px; line-height: 1.2;"><?= htmlspecialchars($calc_settings['card4_title']) ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- ── CTA: Talk to Our Team ── -->
                        <div class="mt-3 pt-3" style="border-top: 1px solid #F1F5F9;">

                            <p class="text-center mb-2" style="font-size: 0.71rem; font-weight: 600; color: #64748B; letter-spacing: 0.25px; line-height: 1.5; margin: 0 0 10px 0;">
                                Ready to lock in these savings? Talk to our team:
                            </p>

                            <!-- Button Row: side-by-side desktop, stacked mobile -->
                            <div style="display: flex; flex-wrap: wrap; gap: 8px;">

                                <!-- ① Messenger Button -->
                                <a href="https://m.me/61578373983187"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   style="flex: 1 1 120px; display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 9px 12px; border-radius: 10px; background-color: #0084FF; color: #ffffff; font-size: 0.74rem; font-weight: 700; text-decoration: none; letter-spacing: 0.2px; transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease; white-space: nowrap;"
                                   onmouseover="this.style.backgroundColor='#006ACC';this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 20px rgba(0,132,255,0.32)';"
                                   onmouseout="this.style.backgroundColor='#0084FF';this.style.transform='translateY(0)';this.style.boxShadow='none';">
                                    <!-- Official Messenger Bolt SVG -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="white" style="flex-shrink:0;">
                                        <path d="M12 2C6.477 2 2 6.145 2 11.25c0 2.86 1.32 5.42 3.41 7.17.18.15.29.36.3.59l.06 1.84a.75.75 0 0 0 1.05.67l2.06-.91c.18-.08.38-.1.57-.05A11.26 11.26 0 0 0 12 20.5c5.523 0 10-4.145 10-9.25S17.523 2 12 2Zm1.046 12.533-2.597-2.77-5.073 2.77 5.583-5.933 2.663 2.77 5.017-2.77-5.593 5.933Z"/>
                                    </svg>
                                    Via Messenger
                                </a>

                                <!-- ② Viber Button -->
                                <a href="viber://chat?number=639953947379&text=Hi%20SolarPower!%20I%20just%20used%20your%20solar%20calculator%20and%20I%20want%20to%20request%20a%20formal%20quotation."
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   style="flex: 1 1 120px; display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 9px 12px; border-radius: 10px; background-color: #7360F2; color: #ffffff; font-size: 0.74rem; font-weight: 700; text-decoration: none; letter-spacing: 0.2px; transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease; white-space: nowrap;"
                                   onmouseover="this.style.backgroundColor='#5A4BD1';this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 20px rgba(115,96,242,0.32)';"
                                   onmouseout="this.style.backgroundColor='#7360F2';this.style.transform='translateY(0)';this.style.boxShadow='none';">
                                    <!-- Official Viber Phone SVG -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="white" style="flex-shrink:0;">
                                        <path d="M20.435 3.561C18.239 1.494 15.322.43 12.253.47 5.87.47.88 5.396.88 11.693c0 2.014.537 3.979 1.557 5.713L.8 23.53l6.335-1.63a11.624 11.624 0 0 0 5.112 1.192h.005c6.378 0 11.372-4.926 11.372-10.988-.001-2.937-1.146-5.696-3.189-7.543Zm-8.182 16.9h-.004a9.646 9.646 0 0 1-4.886-1.328l-.35-.207-3.63.937.966-3.498-.228-.359a9.467 9.467 0 0 1-1.473-5.033c0-5.284 4.362-9.584 9.71-9.584 2.593.001 5.03.998 6.866 2.806a9.45 9.45 0 0 1 2.848 6.748c-.003 5.285-4.365 9.518-9.819 9.518Zm5.33-7.147c-.29-.144-1.718-.839-1.984-.935-.265-.097-.458-.144-.651.144-.193.289-.748.935-.917 1.127-.169.192-.337.217-.627.072-.29-.145-1.223-.446-2.33-1.425-.861-.762-1.443-1.702-1.612-1.99-.169-.29-.018-.445.127-.59.13-.129.29-.337.435-.505.145-.168.193-.289.29-.48.096-.192.048-.361-.025-.505-.073-.144-.651-1.56-.893-2.136-.235-.561-.474-.485-.651-.494l-.555-.009c-.193 0-.506.072-.77.361-.265.289-1.012.98-1.012 2.392s1.036 2.774 1.18 2.966c.144.193 2.038 3.082 4.939 4.32.69.295 1.229.472 1.649.604.693.218 1.324.187 1.822.113.556-.082 1.718-.695 1.96-1.367.24-.672.24-1.248.169-1.368-.073-.12-.266-.193-.556-.337Z"/>
                                    </svg>
                                    Via Viber
                                </a>

                            </div>
                        </div>
                        <!-- ── END CTA ── -->

                    </div>
                </div>
            </div>
        </section>
    </div>


    <!-- Trusted Partners Marquee Section -->
    <section id="brandPartnersSection" data-checkout-hide class="bg-amber-500 py-6 overflow-hidden relative shadow-inner">
        <!-- Title -->
        <div class="text-center mb-4">
            <h3 class="text-xs sm:text-sm font-bold text-white/90 uppercase tracking-[0.2em]">Our Brand Partners</h3>
        </div>

        <!-- Marquee Container -->
        <div class="relative flex overflow-x-hidden group">
            <!-- Left Gradient Mask -->
            <div class="absolute top-0 left-0 w-16 md:w-32 h-full bg-gradient-to-r from-amber-500 to-transparent z-10 pointer-events-none"></div>

            <!-- Right Gradient Mask -->
            <div class="absolute top-0 right-0 w-16 md:w-32 h-full bg-gradient-to-l from-amber-500 to-transparent z-10 pointer-events-none"></div>

            <!-- Marquee Track -->
            <div class="flex animate-marquee group-hover:animate-marquee-paused whitespace-nowrap py-2">
                <!-- Logos Set 1 -->
                <div class="flex items-center gap-6 px-3">
                    <?php foreach ($logo_brands as $brand): ?>
                    <div class="w-40 h-20 md:w-56 md:h-28 bg-white/90 backdrop-blur-sm rounded-xl flex items-center justify-center p-2 shadow-sm hover:shadow-md transition-shadow">
                        <img src="<?= htmlspecialchars($brand['logo_image']) ?>" alt="<?= htmlspecialchars($brand['brand_name']) ?>" class="w-full h-full object-contain mix-blend-multiply opacity-90">
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Logos Set 2 (Duplicate for Seamless Loop) -->
                <div class="flex items-center gap-6 px-3" aria-hidden="true">
                    <?php foreach ($logo_brands as $brand): ?>
                    <div class="w-40 h-20 md:w-56 md:h-28 bg-white/90 backdrop-blur-sm rounded-xl flex items-center justify-center p-2 shadow-sm hover:shadow-md transition-shadow">
                        <img src="<?= htmlspecialchars($brand['logo_image']) ?>" alt="<?= htmlspecialchars($brand['brand_name']) ?>" class="w-full h-full object-contain mix-blend-multiply opacity-90">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- ---------- CATALOG SECTION ---------- -->
    <section class="catalogs-section" id="catalogSection" data-checkout-hide>
        <div class="container">

            <div class="catalog-header" data-aos="fade-up">
                <h2>Our Products</h2>
                <p class="catalog-subtitle">Premium solar solutions for your energy needs</p>
            </div>

            <!--promotional product-->
            <?php include "includes/promotional.php" ?>

            <!--SEARCH BAR FUNCTION -->
            <?php include "includes/product-search-bar.php" ?>

            <!-- Filter Bar -->
            <!-- Filter Bar commented out
            <div class="filter-bar" data-aos="fade-up">
                <div class="filter-buttons" id="categoryFilters">
                    <button class="filter-btn active" data-filter="all">
                        <i class="fas fa-th"></i> All
                    </button>
                    <button class="filter-btn" data-filter="Grid-tie">
                        <i class="fas fa-solar-panel"></i> Grid-tie
                    </button>
                    <button class="filter-btn" data-filter="Package">
                        <i class="fas fa-box-open"></i> Package Deals
                    </button>
                </div>

                <div class="sort-container">
                    <label class="sort-label">Sort by:</label>
                    <select class="sort-select" id="sortSelect">
                        <option value="default">Default</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                        <option value="name-asc">Name: A to Z</option>
                        <option value="name-desc">Name: Z to A</option>
                    </select>
                </div>
            </div>
            -->

            <!-- Products Grid - FIXED onclick handlers -->
            <div class="products-grid" id="productsGrid">
                <?php if ($products): ?>
                    <?php foreach ($products as $index => $p): ?>
                        <!-- Replace the product card section in index.php with this -->
                        <div class="product-card <?= $index >= 4 ? 'hidden-product' : '' ?>"
                            data-category="<?= htmlspecialchars($p['category']) ?>"
                            data-brand="<?= htmlspecialchars($p['brandName']) ?>"
                            data-name="<?= htmlspecialchars($p['displayName']) ?>"
                            data-price="<?= htmlspecialchars($p['price']) ?>">

                            <!-- Clickable Product Image and Info -->
                            <div onclick="location.href='product-details.php/<?= createSlug($p['displayName']) ?>'" style="cursor: pointer;">
                                <div class="product-image">
                                    <img src="<?= htmlspecialchars($p['image_path'] ?? 'assets/img/placeholder.png') ?>"
                                        alt="<?= htmlspecialchars($p['displayName']) ?>">
                                    <div class="product-badge"> <i class="fas fa-tag"></i>
                                        <?= htmlspecialchars($p['category']) ?></div>
                                </div>

                                <div class="product-info">
                                    <div class="product-brand"><?= htmlspecialchars($p['brandName']) ?></div>
                                    <h3 class="product-name"><?= htmlspecialchars($p['displayName']) ?></h3>
                                    <div class="product-price">
                                        ₱<?= number_format($p['price'], 2) ?>
                                    </div>
                                    <div class="preview-stock" style="display: none;">
                                        <i class="fas fa-box"></i> Stock: <?= htmlspecialchars($p['stockQuantity']) ?> units
                                    </div>
                                    <?php if ($p['category'] === 'Panel' && intval($p['moq']) > 1): ?>
                                        <div class="moq-badge"
                                            style="margin-top:6px; display:inline-block; background:#fff3cd; color:#856404; border:1px solid #ffc107; border-radius:6px; padding:3px 10px; font-size:0.78rem; font-weight:600;">
                                            <i class="fas fa-layer-group"></i> Min. Order: <?= intval($p['moq']) ?> pcs
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Action Buttons (not clickable for navigation) -->
                            <div class="product-actions" onclick="event.stopPropagation()">
                                <button class="btn-add-cart"
                                    data-product='<?= json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
                                    onclick="addToCartFromButton(this)" title="Add to Cart">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>

                                <button type="button" class="btn-buy-now"
                                    data-product='<?= json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
                                    onclick="buyNowFromButton(this)">
                                    Buy Now
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-products">
                        <i class="fas fa-box-open"></i>
                        <p>No products available at the moment</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="view-more-container" id="viewMoreContainer">
                <button class="btn-view-more" id="viewMoreBtn" onclick="toggleViewMore()">
                    <span>View More Products</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
        </div>
    </section>

    <section class="bnpl-section" id="bnplSection" data-checkout-hide>
        <div class="container">
            <!-- Header - Centered with aligned description -->
            <div class="bnpl-header">
                <h2>Install Now, <span class="highlight">Pay Later</span></h2>
                <p class="bnpl-subtitle">Switch to Solar now and enjoy massive savings with 30% down payments.</p>
            </div>

            <!-- Steps Grid -->
            <div class="bnpl-steps">
                <!-- Step 1 -->
                <div class="bnpl-step">
                    <div class="step-circle" data-step="1">
                        <div class="step-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                    <h3>Book Ocular Inspection</h3>
                    <p>Schedule your site visit and let our experts assess your property.</p>
                </div>

                <!-- Step 2 -->
                <div class="bnpl-step">
                    <div class="step-circle" data-step="2">
                        <div class="step-icon">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                    </div>
                    <h3>Get Free Quotation</h3>
                    <p>Receive a detailed proposal tailored to your energy needs and budget.</p>
                </div>

                <!-- Step 3 -->
                <div class="bnpl-step">
                    <div class="step-circle" data-step="3">
                        <div class="step-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                    </div>
                    <h3>Installation Process</h3>
                    <p>Our certified team installs your solar system quickly and professionally.</p>
                </div>

                <!-- Step 4 -->
                <div class="bnpl-step">
                    <div class="step-circle" data-step="4">
                        <div class="step-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                    </div>
                    <h3>Pay Later</h3>
                    <p>Flexible payment plans with zero interest. Start saving from day one!</p>
                </div>
            </div>

            <!-- Optional: Add CTA button -->
            <div class="bnpl-cta" style="text-align: center; margin-top: 50px;">
                <button class="btn btn-primary btn-lg" onclick="window.location.href='#inspectionModal'"
                    data-bs-toggle="modal" data-bs-target="#inspectionModal">
                    Get Started Today
                </button>
            </div>
        </div>
    </section>

    <!-- Rent to Own Section (Industrial & Commercial Only) --
    <section class="rent-to-own-section" id="rentToOwnSection" data-checkout-hide>
        <div class="container">
            <div class="rto-wrapper">
               Left: Form --
                <div class="rto-form-container">
                    <div class="rto-header">
                        <h2>Rent to Own Solar System</h2>
                        <p class="rto-subtitle">For Industrial & Commercial Properties Only</p>
                        <div class="rto-badge">
                            <i class="fas fa-building"></i>
                            <span>Industrial & Commercial</span>
                        </div>
                    </div>

                    <form class="rto-form" id="rentToOwnForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="rto_firstName">First Name <span class="required">*</span></label>
                                <input type="text" id="rto_firstName" name="firstName" required>
                            </div>
                            <div class="form-group">
                                <label for="rto_lastName">Last Name <span class="required">*</span></label>
                                <input type="text" id="rto_lastName" name="lastName" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="rto_email">Email <span class="required">*</span></label>
                                <input type="email" id="rto_email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="rto_contact">Contact Number <span class="required">*</span></label>
                                <input type="tel" id="rto_contact" name="contactNumber" required placeholder="+63">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="rto_company">Company Name <span class="required">*</span></label>
                            <input type="text" id="rto_company" name="companyName" required>
                        </div>

                        <div class="form-group">
                            <label for="rto_province">Province/City <span class="required">*</span></label>
                            <select id="rto_province" name="province" required>
                                <option value="">Select Province/City</option>
                                <option value="Metro Manila">Metro Manila</option>
                                <option value="Cavite">Cavite</option>
                                <option value="Laguna">Laguna</option>
                                <option value="Batangas">Batangas</option>
                                <option value="Rizal">Rizal</option>
                                <option value="Bulacan">Bulacan</option>
                                <option value="Pampanga">Pampanga</option>
                                <option value="Cebu">Cebu</option>
                                <option value="Davao">Davao</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="rto_electricityBill">Monthly Electricity Bill (₱) <span class="required">*</span></label>
                            <input type="number" id="rto_electricityBill" name="electricityBill" required min="8000" placeholder="₱ 50,000">
                            <small>For our smallest system size (5kWp), we recommend your bill to at least be ₱8,000 to maximize savings.</small>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="rto_propertyType">Type of Property <span class="required">*</span></label>
                                <select id="rto_propertyType" name="propertyType" required>
                                    <option value="">Please select</option>
                                    <option value="Factory">Factory</option>
                                    <option value="Warehouse">Warehouse</option>
                                    <option value="Office Building">Office Building</option>
                                    <option value="Manufacturing Plant">Manufacturing Plant</option>
                                    <option value="Commercial Building">Commercial Building</option>
                                    <option value="Industrial Complex">Industrial Complex</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="rto_ownership">Do you own the property? <span class="required">*</span></label>
                                <select id="rto_ownership" name="ownership" required>
                                    <option value="">Please select</option>
                                    <option value="Yes">Yes, I own it</option>
                                    <option value="No">No, I lease/rent it</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="rto_proceed">How would you like to proceed? <span class="required">*</span></label>
                                <select id="rto_proceed" name="proceed" required>
                                    <option value="">Please select</option>
                                    <option value="Site Inspection">Site Inspection</option>
                                    <option value="Get a Quote">Get a Quote</option>
                                    <option value="Consultation">Consultation Call</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="rto_installation">Target Installation <span class="required">*</span></label>
                                <select id="rto_installation" name="installation" required>
                                    <option value="">Please select</option>
                                    <option value="Immediate">Immediate (1-2 months)</option>
                                    <option value="Within 6 months">Within 6 months</option>
                                    <option value="Within 1 year">Within 1 year</option>
                                    <option value="Just exploring">Just exploring</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit-rto">
                            <span class="btn-text">Submit Application</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>
                    </form>
                </div>

                <!-- Right: Image/Visual --
                <div class="rto-visual">
                    <div class="visual-content">
                        <div class="solar-illustration">
                            <i class="fas fa-solar-panel"></i>
                        </div>
                        <h3>Power Your Business</h3>
                        <p>Reduce operational costs with our flexible rent-to-own solar solutions designed for industrial and commercial properties.</p>
                        
                        <div class="benefits-list">
                            <div class="benefit-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Zero upfront costs</span>
                            </div>
                            <div class="benefit-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Flexible payment terms</span>
                            </div>
                            <div class="benefit-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Full ownership after lease</span>
                            </div>
                            <div class="benefit-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Immediate energy savings</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>-->



    <section class="checkout-container" id="checkoutSection" style="display:none; padding-top: 100px;">
        <div class="checkout-shell">
            <div class="checkout-main">
                <div class="checkout-steps" id="checkoutSteps" data-step="1">
                    <div class="step active" id="ind-step1">
                        <span>1</span>
                        <p>Details</p>
                    </div>
                    <div class="step" id="ind-step2">
                        <span>2</span>
                        <p>Payment</p>
                    </div>
                    <div class="step" id="ind-step3">
                        <span>3</span>
                        <p>Confirm</p>
                    </div>
                </div>

                <h2 class="checkout-title">Checkout</h2>

                <div id="checkoutStep1" class="checkout-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3>Delivery & Installation Details</h3>
                        <button class="btn btn-sm btn-outline-primary" onclick="backToCatalog()">
                            <i class="fas fa-plus"></i> Add More Product
                        </button>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-12 mb-2">
                            <label class="form-label fw-bold">Full Name</label>
                            <input type="text" class="form-control" id="cust_name" placeholder="Juan Dela Cruz"
                                required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" class="form-control" id="cust_email" placeholder="juan@example.com"
                                required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label fw-bold">Contact Number</label>
                            <input type="text" class="form-control" id="cust_phone" placeholder="09123456789" required>
                        </div>
                        <!-- Delivery Address -->
                        <div class="col-md-12 mb-2">
                            <label class="form-label fw-bold">House No. / Street / Subdivision</label>
                            <input type="text" class="form-control" id="house_street"
                                placeholder="House No., Street, Subdivision" required>
                        </div>

                        <div class="col-md-4 mb-2">
                            <label class="form-label fw-bold">Province/Region</label>
                            <select class="form-select" id="province" required>
                                <option value="">Select Province</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-2">
                            <label class="form-label fw-bold">City / Municipality</label>
                            <select class="form-select" id="municipality" disabled required>
                                <option value="">Select City / Municipality</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Barangay</label>
                            <select class="form-select" id="barangay" disabled required>
                                <option value="">Select Barangay</option>
                            </select>
                        </div>

                        <!-- Hidden full address (for saving/submitting) -->
                        <input type="hidden" id="cust_address">

                        <!-- Delivery Fee Information -->
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-truck me-2"></i>
                                        <strong>Delivery & Installation Fees Apply</strong>
                                    </div>
                                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal"
                                        data-bs-target="#deliveryFeeModal">
                                        View Rates
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>


                    <div class="checkout-actions">
                        <button class="btn-outline" onclick="backToCatalog()">← Continue Shopping</button>
                        <button class="btn-primary" onclick="validateStep1()">Proceed to Payment →</button>
                    </div>
                </div>

                <div id="checkoutStep2" class="checkout-card" style="display:none;">
                    <h3>Order Summary & Payment</h3>

                    <!-- Payment Method Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-credit-card me-2"></i>Secure Payment Portal
                            </h5>
                            <span class="badge bg-light text-primary" style="font-size: 0.75rem;"><i class="fas fa-lock me-1"></i> BSP Regulated</span>
                        </div>
                        <div class="card-body">
                            <!-- Trust Assurance Notice (Risk Reversal) -->
                            <div class="alert alert-success mb-4" style="border-left: 4px solid #0D5C3A; background-color: rgba(13, 92, 58, 0.04); color: #1E293B;">
                                <div class="d-flex gap-2 align-items-start">
                                    <i class="fas fa-shield-alt text-success mt-1" style="font-size: 1.2rem;"></i>
                                    <div>
                                        <strong style="color: #0D5C3A;">Risk-Free Booking Guarantee:</strong>
                                        <p class="mb-0 small text-muted mt-1">Your payment is fully secured. If our technical site survey reveals roof structural, spacing, or shading issues that prevent solar installation, your booking downpayment is 100% refundable.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- B2B & Government Touchpoint -->
                            <div class="p-3 mb-4 rounded border text-start" style="background-color: #f8fafc; border-left: 4px solid #F2A900 !important;">
                                <h6 class="fw-bold mb-1 text-dark"><i class="fas fa-building text-warning me-2"></i>Corporate or Government Buyer?</h6>
                                <p class="small text-muted mb-2">If you represent an SME, large commercial company, or government institution requiring formal bids, corporate invoices, or grid-tie feasibility studies, skip digital checkouts and request a formal proposal directly.</p>
                                <a href="loans.php#checklist" class="btn btn-sm text-dark fw-bold border-0" style="background-color: #F2A900; border-radius: 4px; padding: 6px 12px; font-size: 0.75rem;"><i class="fas fa-file-contract me-1"></i> Request B2B Engineering Proposal</a>
                            </div>

                            <!-- Payment Term Selection -->
                            <div class="payment-options mb-4">
                                <h6 class="fw-bold mb-3"><i class="fas fa-tasks me-2 text-primary"></i>1. Select Payment Option:</h6>

                                <!-- Full Payment (100%) -->
                                <div class="form-check payment-option mb-3 p-3 border rounded" style="cursor: pointer; transition: background 0.2s;">
                                    <input class="form-check-input ms-0 me-2" type="radio" name="paymentMethod" id="paymentFull"
                                        value="full" checked onchange="updatePaymentDisplay()">
                                    <label class="form-check-label w-100 ps-4" for="paymentFull">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong class="text-dark">Full Payment (100% upfront)</strong>
                                                <p class="text-muted mb-0 small">Saves 3% off your total system cost (Discount applied manually on billing verification)</p>
                                            </div>
                                            <span class="badge bg-success">Best Value</span>
                                        </div>
                                    </label>
                                </div>

                                <!-- 50% Down Payment -->
                                <div class="form-check payment-option mb-3 p-3 border rounded" style="cursor: pointer; transition: background 0.2s;">
                                    <input class="form-check-input ms-0 me-2" type="radio" name="paymentMethod" id="paymentDown"
                                        value="downpayment" onchange="updatePaymentDisplay()">
                                    <label class="form-check-label w-100 ps-4" for="paymentDown">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong class="text-dark">50% Down Payment (Secures Booking)</strong>
                                                <p class="text-muted mb-0 small">Pay 50% now to initiate engineering designs, remaining 50% paid upon site delivery</p>
                                            </div>
                                            <span class="badge bg-warning text-dark">Standard</span>
                                        </div>
                                    </label>
                                </div>

                                <!-- 20% Initial Payment -->
                                <div class="form-check payment-option mb-3 p-3 border rounded" style="cursor: pointer; transition: background 0.2s;">
                                    <input class="form-check-input ms-0 me-2" type="radio" name="paymentMethod" id="paymentInitial"
                                        value="initial" onchange="updatePaymentDisplay()">
                                    <label class="form-check-label w-100 ps-4" for="paymentInitial">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong class="text-dark">20% Booking Fee (Mobilization)</strong>
                                                <p class="text-muted mb-0 small">Secure solar panels and scheduling immediately. Balance paid pre-installation</p>
                                            </div>
                                            <span class="badge bg-info text-white">Low Upfront</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Payment Channels Tab System -->
                            <h6 class="fw-bold mb-3"><i class="fas fa-university me-2 text-primary"></i>2. Choose Payment Channel:</h6>
                            <ul class="nav nav-pills mb-3" id="paymentChannelsTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active fw-bold text-uppercase" id="instapay-tab" data-bs-toggle="pill" data-bs-target="#p-instapay" type="button" role="tab" style="font-size: 0.8rem; border-radius: 8px;"><i class="fas fa-qrcode me-1"></i> InstaPay / GCash</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link fw-bold text-uppercase" id="bank-tab" data-bs-toggle="pill" data-bs-target="#p-bank" type="button" role="tab" style="font-size: 0.8rem; border-radius: 8px;"><i class="fas fa-university me-1"></i> Bank Accounts</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a href="viber://chat?number=639171234567" target="_blank" class="nav-link fw-bold text-uppercase" style="font-size: 0.8rem; color: #7360f2;"><i class="fab fa-viber me-1"></i> Ask Rep / Financing</a>
                                </li>
                            </ul>
                            
                            <div class="tab-content border rounded p-3 mb-4 bg-white" id="paymentTabContent">
                                <!-- InstaPay/GCash Panel -->
                                <div class="tab-pane fade show active text-center" id="p-instapay" role="tabpanel">
                                    <h6 class="mb-2 fw-semibold text-dark">Scan to Pay via InstaPay QR</h6>
                                    <img src="assets/img/UB-QR Code.jpg" alt="InstaPay QR Code" class="img-fluid"
                                        style="max-width: 260px; border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px;">
                                    <p class="text-muted small mt-2 mb-0">Works with GCash, PayMaya, ShopeePay, and all major Philippine Banking Apps.</p>
                                </div>
                                
                                <!-- Bank Accounts Panel -->
                                <div class="tab-pane fade" id="p-bank" role="tabpanel">
                                    <div class="text-start">
                                        <h6 class="mb-3 fw-bold text-dark">Direct Bank Transfer Details:</h6>
                                        <div class="d-flex flex-column gap-3">
                                            <div class="p-2 border rounded" style="background-color: #fafafa;">
                                                <strong class="text-dark"><i class="fas fa-university me-1 text-danger"></i> Bank of the Philippine Islands (BPI)</strong>
                                                <div class="small text-muted mt-1">Account Name: <strong>SolarPower Energy Corporation</strong></div>
                                                <div class="small text-muted">Account Number: <strong>1234-5678-90</strong></div>
                                            </div>
                                            <div class="p-2 border rounded" style="background-color: #fafafa;">
                                                <strong class="text-dark"><i class="fas fa-university me-1 text-primary"></i> Metropolitan Bank & Trust Company (Metrobank)</strong>
                                                <div class="small text-muted mt-1">Account Name: <strong>SolarPower Energy Corporation</strong></div>
                                                <div class="small text-muted">Account Number: <strong>9876-5432-10</strong></div>
                                            </div>
                                            <div class="p-2 border rounded" style="background-color: #fafafa;">
                                                <strong class="text-dark"><i class="fas fa-university me-1 text-warning"></i> UnionBank of the Philippines</strong>
                                                <div class="small text-muted mt-1">Account Name: <strong>SolarPower Energy Corporation</strong></div>
                                                <div class="small text-muted">Account Number: <strong>0011-2233-4455</strong></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Receipt Upload Section -->
                            <div class="alert alert-light border mt-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-upload text-primary me-3"
                                        style="font-size: 1.5rem; margin-top:2px;"></i>
                                    <div class="w-100">
                                        <strong>Upload Your Transaction Receipt</strong>
                                        <p class="text-muted small mb-2 mt-1">Once you complete the payment via InstaPay, GCash, or Direct Bank Transfer, take a screenshot or photo of your transaction confirmation receipt and upload it below. Our billing team will verify it instantly.</p>
                                        <div class="mb-2 text-start">
                                            <label for="receiptUpload" class="form-label fw-bold">
                                                <i class="fas fa-file-image me-1 text-primary"></i> Transaction Receipt
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="file" class="form-control" id="receiptUpload"
                                                accept="image/*,.pdf" onchange="previewReceipt(this)">
                                            <div class="form-text">Accepted formats: JPG, PNG, PDF (Max 5MB)</div>
                                        </div>
                                        <div id="receiptPreviewContainer" style="display:none; margin-top:10px;">
                                            <p class="small fw-bold text-success"><i
                                                    class="fas fa-check-circle me-1"></i> Receipt ready to upload:</p>
                                            <img id="receiptPreviewImg" src="" alt="Receipt Preview"
                                                style="max-width:200px; max-height:200px; border-radius:8px; border:2px solid #28a745; object-fit:cover;">
                                            <p id="receiptFileName" class="small text-muted mt-1 mb-0"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary (hidden from checkout display, values still computed by JS) -->
                    <div class="payment-summary-box p-3 bg-light rounded mb-4" style="display:none;">
                        <h5 class="mb-3"><i class="fas fa-file-invoice-dollar me-2"></i>Payment Summary</h5>

                        <div class="summary-row">
                            <span>Items Subtotal:</span>
                            <span id="checkoutSubtotal" class="fw-bold"></span>
                        </div>

                        <div class="summary-row">
                            <span>Installation Fee:</span>
                            <span id="installationFeeDisplay" class="fw-bold"></span>
                        </div>

                        <div class="summary-row">
                            <span>Delivery Fee:</span>
                            <span id="deliveryFeeDisplay" class="fw-bold text-primary"></span>
                        </div>

                        <hr>

                        <div class="summary-row" style="font-size: 1.2rem;">
                            <span class="fw-bold">Amount to Pay Now:</span>
                            <span id="amountToPay" class="fw-bold text-primary"></span>
                        </div>

                        <div class="summary-row total-row" style="font-size: 1.3rem; color: #2c3e50;">
                            <span class="fw-bold">Total Order Amount:</span>
                            <span id="checkoutTotal" class="fw-bold text-dark"></span>
                        </div>
                    </div>

                    <!-- Payment Note (hidden from view, computed by JS internally) -->
                    <div id="paymentNote" class="alert alert-success" style="display:none;">
                        <i class="fas fa-info-circle"></i> You are paying the <strong>Full Amount (100%)</strong> via
                        InstaPay.
                    </div>

                    <!-- Action Buttons -->
                    <div class="checkout-actions mt-4">
                        <button class="btn-outline" onclick="goToStep(1)">
                            <i class="fas fa-arrow-left me-2"></i>Edit Details
                        </button>
                        <button id="confirmPaymentBtn" class="btn-primary" onclick="confirmInstapayOrder()">
                            <i class="fas fa-check-circle me-2"></i>Confirm &amp; Submit Order
                        </button>
                    </div>
                </div>

                <div id="checkoutStep3" class="checkout-card" style="display:none;">
                    <div class="text-center py-5">

                        <i class="fas fa-check-circle text-success mb-3" style="font-size:64px;"></i>
                        <h3>Order Submitted Successfully!</h3>
                        <p class="text-muted">Thank you, <strong><span id="confCustomerName"></span></strong>! Your
                            order and receipt have been submitted. We will verify your payment shortly.</p>

                        <div class="alert alert-info mt-4 text-start">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Next Steps:</strong>
                            <ul class="list-unstyled mt-2 mb-0">
                                <li>✓ Your order has been saved to our database</li>
                                <li>✓ Your receipt has been uploaded for verification</li>
                                <li>✓ You will receive a confirmation within 24 hours</li>
                                <li>✓ Our team will contact you to schedule delivery/installation</li>
                            </ul>
                        </div>

                        <!-- Order Reference -->
                        <p class="mt-3">
                            <strong>Order Reference:</strong><br>
                            <span id="confOrderRef" class="fw-bold fs-5"></span>
                        </p>
                        <p class="mt-1">
                            <strong>Total Amount:</strong>
                            <span id="confTotalAmount" class="fw-bold text-primary"></span>
                        </p>

                        <!-- Copy Button -->
                        <button class="btn btn-outline-secondary btn-sm mt-2" onclick="copyOrderRef()">
                            <i class="fas fa-copy"></i> Copy Reference
                        </button>

                        <!-- QR Code -->
                        <div class="mt-4">
                            <p class="text-muted small mb-2">Scan or save this QR code to track your order.</p>
                            <div id="orderQr" class="d-inline-block p-2 bg-white"></div>
                        </div>

                        <button class="btn btn-primary mt-4" onclick="location.href='index.php'">
                            Back to Home
                        </button>
                    </div>
                </div>



            </div>

            <aside class="checkout-sidebar">
                <div class="summary-box shadow-sm">
                    <h4 class="border-bottom pb-2">Your Order</h4>
                    <div id="checkoutOrderSummary">
                    </div>
                </div>
            </aside>
        </div>
    </section>

    <!-- ── SECTION 2: CORE SERVICES & PRODUCTS GRID (The 3-Pillar Solution) ── -->
    <section class="py-5 bg-white" id="servicesGrid" data-checkout-hide>
        <div class="container py-lg-4">
            <div class="text-center mb-5" data-aos="fade-up">
                <span class="text-uppercase fw-bold text-success" style="font-size: 0.85rem; letter-spacing: 1.5px; color: #0D5C3A !important;">Core Offerings</span>
                <h2 class="fw-extrabold mt-2" style="color: #0D5C3A; font-family: var(--ff-poppins); font-size: 2.3rem;">Engineered Solar Solutions</h2>
                <p class="text-muted">High-performance solar solutions designed for structural mastery and financial efficiency.</p>
            </div>
            
            <div class="row g-4" data-aos="fade-up" data-aos-delay="100">
                <!-- Card 1 -->
                <div class="col-md-4">
                    <div class="card h-100 p-4 border-0 shadow-sm transition-all" style="border-bottom: 5px solid #F2A900 !important; border-radius: 16px; background: #FFFFFF;">
                        <div class="mb-4 text-success d-flex align-items-center justify-content-center rounded-circle" style="width: 60px; height: 60px; background-color: rgba(13, 92, 58, 0.05); font-size: 1.75rem;">
                            <i class="fas fa-home"></i>
                        </div>
                        <h4 class="fw-bold mb-3" style="color: #0D5C3A;">Residential Solar Systems</h4>
                        <p class="text-muted mb-0">Sleek, high-efficiency home grid roof integrations, specifically engineered for premium subdivisions and private estates.</p>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="col-md-4">
                    <div class="card h-100 p-4 border-0 shadow-sm transition-all" style="border-bottom: 5px solid #F2A900 !important; border-radius: 16px; background: #FFFFFF;">
                        <div class="mb-4 text-success d-flex align-items-center justify-content-center rounded-circle" style="width: 60px; height: 60px; background-color: rgba(13, 92, 58, 0.05); font-size: 1.75rem;">
                            <i class="fas fa-industry"></i>
                        </div>
                        <h4 class="fw-bold mb-3" style="color: #0D5C3A;">Commercial & Industrial</h4>
                        <p class="text-muted mb-0">Heavy-duty solar installations designed to slash corporate operating expenses, support carbon compliance, and secure energy independence.</p>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="col-md-4">
                    <div class="card h-100 p-4 border-0 shadow-sm transition-all" style="border-bottom: 5px solid #F2A900 !important; border-radius: 16px; background: #FFFFFF;">
                        <div class="mb-4 text-success d-flex align-items-center justify-content-center rounded-circle" style="width: 60px; height: 60px; background-color: rgba(13, 92, 58, 0.05); font-size: 1.75rem;">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <h4 class="fw-bold mb-3" style="color: #0D5C3A;">Flexible Solar Financing</h4>
                        <p class="text-muted mb-4">Direct linkage to GSIS, Pag-IBIG, and SSS government backup programs, making clean energy affordable with zero cash outlays.</p>
                        <a href="loans.php" class="btn btn-outline-success btn-sm w-100 mt-auto fw-bold" style="border-color: #0D5C3A; color: #0D5C3A; border-radius: 8px;">Explore Financing</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── SECTION 3: LIVE ENERGY & CARBON TRACKER WIDGET ── -->
    <section class="py-5" style="background-color: var(--solar-bg-gray);" id="trackerSection" data-checkout-hide>
        <div class="container py-lg-4">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="overflow-hidden border border-slate-100 shadow-lg" style="border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.05);">
                        <div class="row g-0">
                            <!-- Left Pane: Configurations -->
                            <div class="col-md-6 p-5 bg-white d-flex flex-column justify-content-center">
                                <span class="text-uppercase fw-bold text-success" style="font-size: 0.8rem; letter-spacing: 1.5px; color: #0D5C3A !important;">Live Analytics</span>
                                <h3 class="fw-extrabold mt-2 mb-4" style="color: #0D5C3A; font-family: var(--ff-poppins);">Real-Time Impact Tracker</h3>
                                <p class="text-muted mb-0">Our solar deployment footprints across the regions. Ticking counters represent live active production values compiled from all our connected arrays.</p>
                            </div>
                            
                            <!-- Right Pane: Green Dashboard Block -->
                            <div class="col-md-6 p-5 d-flex flex-column justify-content-center" style="background-color: #0D5C3A; color: #FFFFFF;">
                                <!-- Metric 1 -->
                                <div class="mb-4">
                                    <span class="text-uppercase fw-bold text-white-50" style="font-size: 0.75rem; letter-spacing: 1px;">Total Megawatts Generated Nationwide</span>
                                    <h2 class="fw-extrabold text-white mt-1 mb-0" id="mwhCounter" style="font-size: 2.75rem; font-family: var(--ff-poppins);">12,456.82</h2>
                                </div>
                                <!-- Metric 2 -->
                                <div class="mb-0">
                                    <span class="text-uppercase fw-bold text-white-50" style="font-size: 0.75rem; letter-spacing: 1px;">Metric Tons of CO2 Reduced</span>
                                    <h2 class="fw-extrabold mt-1 mb-0" id="co2Counter" style="color: #F2A900; font-size: 2.75rem; font-family: var(--ff-poppins);">9,342.61</h2>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Bottom CTA Bar -->
                        <a href="loans.php" class="d-block text-center py-3 fw-bold text-uppercase text-decoration-none transition-all" style="background-color: #F2A900; color: #1A1A1A; letter-spacing: 1px; font-size: 0.95rem;">
                            See How Much Your Roof Can Save &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── SECTION 4: SOLAR INSIGHTS & LATEST BLOGS ── -->
    <section id="blog" class="py-5 bg-white" data-checkout-hide>
        <div class="container py-lg-4">
            <div class="text-center mb-5" data-aos="fade-up">
                <span class="text-uppercase fw-bold text-success" style="font-size: 0.85rem; letter-spacing: 1.5px; color: #0D5C3A !important;">Insights & Guides</span>
                <h2 class="fw-extrabold mt-2" style="color: #0D5C3A; font-family: var(--ff-poppins); font-size: 2.3rem;">Solar Financing Insights & Guides</h2>
                <p class="text-muted">Shifting to solar is now heavily backed by state programs. Learn how to maximize government support.</p>
            </div>
            
            <div class="row g-4" data-aos="fade-up" data-aos-delay="100">
                <!-- Blog 1 -->
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card h-100 border-0 shadow-sm overflow-hidden" style="border-radius: 16px; background-color: #F8F9FA;">
                        <div style="height: 180px; background: linear-gradient(135deg, rgba(13, 92, 58, 0.8), rgba(242, 169, 0, 0.4)), url('assets/img/GSIS.png') no-repeat center center/cover;"></div>
                        <div class="p-4 d-flex flex-column justify-content-between h-100" style="min-height: 380px;">
                            <div>
                                <span class="badge text-uppercase fw-bold mb-3" style="background-color: rgba(13, 92, 58, 0.1); color: #0D5C3A; font-size: 0.72rem; letter-spacing: 0.5px;">Government Financing</span>
                                <h5 class="fw-bold mb-3" style="color: #0D5C3A; font-size: 1.15rem; line-height: 1.4;">GINHAWA SOLAR ENERGY LOAN: Shift to Clean Energy with GSIS</h5>
                                <p class="text-muted small mb-0" style="line-height: 1.6;">The Ginhawa Solar Energy Loan (GSEL) gives GSIS members a smart and accessible way to shift to solar power by offering financing of up to ₱500,000 for home solar panel installation. With rising electricity costs, GSEL empowers members to take control of their energy expenses, reduce monthly bills, and enjoy long-term savings—all while increasing the value of their homes. It’s a practical investment that delivers immediate financial relief and lasting Ginhawa benefits, while supporting a cleaner, more sustainable future.</p>
                            </div>
                            <a href="loans.php" class="btn btn-outline-success btn-sm mt-4 align-self-start fw-bold" style="border-color: #0D5C3A; color: #0D5C3A; border-radius: 8px;">Read More &rarr;</a>
                        </div>
                    </div>
                </div>

                <!-- Blog 2 -->
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card h-100 border-0 shadow-sm overflow-hidden" style="border-radius: 16px; background-color: #F8F9FA;">
                        <div style="height: 180px; background: linear-gradient(135deg, rgba(13, 92, 58, 0.8), rgba(242, 169, 0, 0.4)), url('assets/img/demo-faq.jpeg') no-repeat center center/cover;"></div>
                        <div class="p-4 d-flex flex-column justify-content-between h-100" style="min-height: 380px;">
                            <div>
                                <span class="badge text-uppercase fw-bold mb-3" style="background-color: rgba(13, 92, 58, 0.1); color: #0D5C3A; font-size: 0.72rem; letter-spacing: 0.5px;">Step-by-Step Guide</span>
                                <h5 class="fw-bold mb-3" style="color: #0D5C3A; font-size: 1.15rem; line-height: 1.4;">Pag-IBIG Solar Loan 2026: Complete Guide to Financing Solar with Housing Loans</h5>
                                <p class="text-muted small mb-0" style="line-height: 1.6;">Looking to go solar but don't have ₱300,000–₱500,000 in cash? Your Pag-IBIG housing loan might be the solution. The Home Development Mutual Fund (HDMF) allows qualified members to finance solar panel installations as part of their home improvement loan—potentially turning your electricity savings into your monthly loan payment.</p>
                            </div>
                            <a href="loans.php" class="btn btn-outline-success btn-sm mt-4 align-self-start fw-bold" style="border-color: #0D5C3A; color: #0D5C3A; border-radius: 8px;">Read More &rarr;</a>
                        </div>
                    </div>
                </div>

                <!-- Blog 3 -->
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card h-100 border-0 shadow-sm overflow-hidden" style="border-radius: 16px; background-color: #F8F9FA;">
                        <div style="height: 180px; background: linear-gradient(135deg, rgba(242, 169, 0, 0.8), rgba(13, 92, 58, 0.4)), url('assets/img/demo-solar1.webp') no-repeat center center/cover;"></div>
                        <div class="p-4 d-flex flex-column justify-content-between h-100" style="min-height: 380px;">
                            <div>
                                <span class="badge text-uppercase fw-bold mb-3" style="background-color: rgba(242, 169, 0, 0.1); color: #B45309; font-size: 0.72rem; letter-spacing: 0.5px;">Upcoming Programs</span>
                                <h5 class="fw-bold mb-3" style="color: #0D5C3A; font-size: 1.15rem; line-height: 1.4;">SSS Energy Sustainability Loan Program: What Filipino Households Need to Know</h5>
                                <p class="text-muted small mb-0" style="line-height: 1.6;">The Social Security System (SSS) is set to introduce its Energy Sustainability Loan Program, which will allow qualified members to finance residential solar panel systems. The program represents a proactive response to emerging economic pressures, helping Filipino households save on high electricity rates.</p>
                            </div>
                            <a href="loans.php" class="btn btn-outline-success btn-sm mt-4 align-self-start fw-bold" style="border-color: #0D5C3A; color: #0D5C3A; border-radius: 8px;">Read More &rarr;</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Dynamic Live Ticking Counters
        document.addEventListener('DOMContentLoaded', () => {
            const mwhEl = document.getElementById('mwhCounter');
            const co2El = document.getElementById('co2Counter');
            
            if (mwhEl && co2El) {
                let mwh = 12456.82;
                let co2 = 9342.61;
                
                setInterval(() => {
                    mwh += Math.random() * 0.05;
                    co2 += Math.random() * 0.04;
                    
                    mwhEl.textContent = mwh.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' MWh';
                    co2El.textContent = co2.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' Tons';
                }, 3000);
            }
        });
    </script>
    


    <!-- Tailwind Config Additions & JS Logic for Calculator -->
    <style>
        @keyframes marquee {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-50%);
            }
        }

        .animate-marquee {
            animation: marquee 10s linear infinite;
        }

        @media (min-width: 768px) {
            .animate-marquee {
                animation: marquee 18s linear infinite;
            }
        }

        .animate-marquee-paused {
            animation-play-state: paused;
        }

        /* Fallback for Tailwind range input styling cross-browser */
        input[type=range]::-webkit-slider-thumb {
            -webkit-appearance: none;
            height: 20px;
            width: 20px;
            border-radius: 50%;
            background: #f59e0b;
            /* amber-500 */
            cursor: pointer;
            margin-top: -6px;
            /* You need to specify a margin in Chrome, but in Firefox and IE it is automatic */
            box-shadow: 0 0 10px rgba(245, 158, 11, 0.5);
            transition: transform 0.15s ease-in-out;
        }

        input[type=range]::-webkit-slider-thumb:hover {
            transform: scale(1.2);
            background: #d97706;
            /* amber-600 */
        }

        input[type=range]::-webkit-slider-runnable-track {
            width: 100%;
            height: 8px;
            cursor: pointer;
            background: #e2e8f0;
            /* slate-200 */
            border-radius: 4px;
        }
    </style>

    <script>
        const CALC_CONFIG = {
            kwhRate: <?= floatval($calc_settings['kwh_rate']) ?>,
            panelWattage: <?= intval($calc_settings['solar_panel_wattage']) ?>,
            sunHours: <?= floatval($calc_settings['average_sun_hours']) ?>
        };

        function updateTwCalculator(val) {
            let sanitized = String(val).replace(/[^0-9.]/g, '');
            let bill = parseFloat(sanitized);
            if (isNaN(bill) || bill < 0) bill = 0;

            // 1. kWh per month = Monthly Bill / kWh Rate (Rounded)
            let kwhUsed = Math.round(bill / CALC_CONFIG.kwhRate);

            // 2. Required System (kW) = roundup( kWh_per_month / (30 * sunHours) * 0.95 )
            let kwpRequired = Math.ceil((kwhUsed / (30 * CALC_CONFIG.sunHours)) * 0.95);

            // 3. No. of Panels = roundup( (System_kW * 1000) / panelWattage )
            let panelsNeeded = Math.ceil((kwpRequired * 1000) / CALC_CONFIG.panelWattage);

            // 4. Estimated Savings per Month (kWh) = System_kW * sunHours * 30 * 0.95
            let savingsKwh = kwpRequired * CALC_CONFIG.sunHours * 30 * 0.95;

            // 5. Estimated Savings per Month (₱) = Savings_kWh * kWh_Rate
            let monthlySavings = savingsKwh * CALC_CONFIG.kwhRate;
            let yearlySavings = monthlySavings * 12;

            // Cap it if they put extreme values, or handle minimums
            if (bill < 1000) {
                kwpRequired = 0;
                panelsNeeded = 0;
                monthlySavings = 0;
                yearlySavings = 0;
            }

            // Update DOM with safety check
            let kwEl = document.getElementById('twKwValue');
            if (kwEl) kwEl.textContent = kwpRequired.toFixed(1);
            let panelsEl = document.getElementById('twPanelsValue');
            if (panelsEl) panelsEl.textContent = panelsNeeded;

            // Format Currency
            const formatter = new Intl.NumberFormat('en-PH', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
            let mSavingsEl = document.getElementById('twMonthlySavings');
            if (mSavingsEl) mSavingsEl.textContent = formatter.format(monthlySavings);
            let ySavingsEl = document.getElementById('twYearlySavings');
            if (ySavingsEl) ySavingsEl.textContent = formatter.format(yearlySavings);

            // Show CTA smoothly if there's a valid calculation
            const cta = document.getElementById('twCtaContainer');
            if (cta) {
                if (bill > 1500) {
                    cta.classList.remove('opacity-0', 'translate-y-4');
                    cta.classList.add('opacity-100', 'translate-y-0');
                } else {
                    cta.classList.remove('opacity-100', 'translate-y-0');
                    cta.classList.add('opacity-0', 'translate-y-4');
                }
            }
        }

        function updateHeroCalculator(val) {
            let sanitized = String(val).replace(/[^0-9.]/g, '');
            let bill = parseFloat(sanitized);
            if (isNaN(bill) || bill < 0) bill = 0;

            let kwhUsed = Math.round(bill / CALC_CONFIG.kwhRate);
            let kwpRequired = Math.ceil((kwhUsed / (30 * CALC_CONFIG.sunHours)) * 0.95);
            let panelsNeeded = Math.ceil((kwpRequired * 1000) / CALC_CONFIG.panelWattage);
            let savingsKwh = kwpRequired * CALC_CONFIG.sunHours * 30 * 0.95;
            let monthlySavings = savingsKwh * CALC_CONFIG.kwhRate;
            let yearlySavings = monthlySavings * 12;

            if (bill < 1000) {
                kwpRequired = 0;
                panelsNeeded = 0;
                monthlySavings = 0;
                yearlySavings = 0;
            }

            document.getElementById('twKwValueHero').textContent = kwpRequired.toFixed(1);
            document.getElementById('twPanelsValueHero').textContent = panelsNeeded;

            const formatter = new Intl.NumberFormat('en-PH', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
            document.getElementById('twMonthlySavingsHero').textContent = '₱' + formatter.format(monthlySavings);
            document.getElementById('twYearlySavingsHero').textContent = '₱' + formatter.format(yearlySavings);
        }

        // Initialize on load
        document.addEventListener('DOMContentLoaded', () => {
            let initialBillVal = document.getElementById('twBillAmountHero') ? document.getElementById('twBillAmountHero').value : "5000";
            updateHeroCalculator(initialBillVal);
        });
    </script>


    <!-- Services Section --
    <section class="services-section" data-checkout-hide>
        <div class="container">
            <h2>Our Services</h2>
        </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <h3>Residential, Commercial & Industrial Solar Installation</h3>
                        <p>Expert guidance to help you understand solar energy benefits and determine the best system for your property and energy needs. DOE-accredited installation services performed by certified technicians ensuring quality, safety, and optimal system performance.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fa-solid fa-solar-panel"></i>                        
                        </div>
                        <h3>Grid-Tie and Hybrid Systems</h3>
                        <p><p>Our grid-tie and hybrid solar systems are designed for efficiency, reliability, and long-term savings. Whether reducing energy costs through grid connection or ensuring uninterrupted power with battery storage, we deliver smart solar solutions tailored to your needs.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-pencil-ruler"></i>
                        </div>
                        <h3>Solar Panels Maintenance and Upgrades</h3>
                        <p>Professional maintenance and system upgrades to ensure optimal performance, improved efficiency, and extended lifespan of your solar panels.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <h3>Energy Audit & System Design</h3>
                        <p>Comprehensive energy audits and customized system designs to identify your power needs and maximize solar efficiency. We analyze usage patterns and site conditions to create cost-effective, reliable solar solutions.</p>
                    </div>
                </div>
            </div>
    </section>-->

    <!-- Solar System Types Section -->
    <section class="solar-tips-section" data-checkout-hide>
        <div class="container">
            <div class="text-center mb-5">
                <h2>Types of Solar Systems</h2>
                <p class="section-subtitle">Find the right solar setup for your home or business</p>
            </div>

            <!-- Video Grid -->
            <div class="row g-4 mb-5 justify-content-center">
                <div class="col-lg-6 col-md-10">
                    <div class="video-card">
                        <div class="video-wrapper">
                            <div class="fb-video-responsive">
                                <iframe
                                    src="https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2Freel%2F1556081359036132%2F&show_text=false"
                                    allowfullscreen="true"
                                    allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share">
                                </iframe>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-10">
                    <div class="video-card">
                        <div class="video-wrapper">
                            <div class="fb-video-responsive">
                                <iframe
                                    src="https://www.facebook.com/plugins/video.php?href=https://www.facebook.com/61578373983187/videos/1562743611632906/?__so__=watchlist&__rv__=video_home_www_playlist_video_list"
                                    allowfullscreen="true"
                                    allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share">
                                </iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Solar System Types Comparison -->
            <div class="solar-systems-wrapper">

                <!-- 01 Grid-Tied — right to left -->
                <div class="solar-system-row" id="system-gridtied" data-aos="fade-left" data-aos-duration="900">
                    <div class="system-image-col">
                        <div class="system-img-frame">
                            <img src="assets/img/gridtied.png" alt="Grid-Tied Solar System" class="system-img">
                        </div>
                    </div>
                    <div class="system-info-col">
                        <span class="system-badge">01 — Grid-Tied</span>
                        <h3 class="system-title">Grid-Tie Solar System</h3>
                        <p class="system-desc">The simplest and most cost-effective setup. Your panels feed directly
                            into the utility grid, which acts as a virtual battery through net metering.</p>
                        <ul class="system-features">
                            <li>Uses the grid as a virtual battery — no local storage needed</li>
                            <li>Excess power fed back to the grid earns you credits</li>
                            <li>Lowest upfront cost of any solar configuration</li>
                            <li>Fastest return on investment (ROI)</li>
                        </ul>
                        <div class="system-note system-note--warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Shuts down completely during grid blackouts
                        </div>
                    </div>
                </div>

                <!-- 02 Hybrid — left to right -->
                <div class="solar-system-row solar-system-row--reverse" id="system-hybrid" data-aos="fade-right"
                    data-aos-duration="900">
                    <div class="system-image-col">
                        <div class="system-img-frame">
                            <img src="assets/img/hybrid-solar.png" alt="Hybrid Solar System" class="system-img">
                        </div>
                    </div>
                    <div class="system-info-col">
                        <span class="system-badge">02 — Hybrid</span>
                        <h3 class="system-title">Hybrid Solar System</h3>
                        <p class="system-desc">The best of both worlds — grid-connected with battery backup. Panels
                            power your home, charge the battery, and the grid fills any remaining gaps.</p>
                        <ul class="system-features">
                            <li>Grid-tied system with built-in battery backup storage</li>
                            <li>Solar panels power the home and charge batteries simultaneously</li>
                            <li>Grid provides supplemental power when solar falls short</li>
                            <li>Continues working during blackouts via stored energy</li>
                        </ul>
                        <div class="system-note system-note--success">
                            <i class="fas fa-bolt"></i>
                            Works during blackouts using stored battery energy
                        </div>
                    </div>
                </div>

                <!-- 03 Off-Grid — right to left -->
                <div class="solar-system-row" id="system-offgrid" data-aos="fade-left" data-aos-duration="900">
                    <div class="system-image-col">
                        <div class="system-img-frame">
                            <img src="assets/img/offgrid.png" alt="Off-Grid Solar System" class="system-img">
                        </div>
                    </div>
                    <div class="system-info-col">
                        <span class="system-badge">03 — Off-Grid</span>
                        <h3 class="system-title">Off-Grid Solar System</h3>
                        <p class="system-desc">Complete energy independence. Ideal for remote cabins and rural
                            properties where grid connection is unavailable or simply unwanted.</p>
                        <ul class="system-features">
                            <li>Fully self-sufficient — zero grid connection required</li>
                            <li>Must produce 100% of all energy needs from solar</li>
                            <li>Battery bank and backup generator ensure reliability</li>
                            <li>Complete independence from utility providers</li>
                        </ul>
                        <div class="system-note system-note--green">
                            <i class="fas fa-leaf"></i>
                            Completely independent from the utility grid
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </section>


    <!-- Testimonials Section -->
    <section class="py-5" id="testimonialsSection" data-checkout-hide style="background: var(--bs-light, #f8f9fa);">
        <div class="container py-5">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="fw-bold">What Our Clients Say</h2>
                <p class="text-muted">Real experiences from homeowners and businesses who made the switch.</p>
            </div>
            <div class="row g-4 justify-content-center">

                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div
                        style="background:#fff; border:1px solid rgba(0,0,0,0.08); border-radius:16px; padding:1.5rem; position:relative;">
                        <span
                            style="position:absolute; top:12px; right:18px; font-size:48px; line-height:1; color:#ddd; font-family:Georgia,serif;">&ldquo;</span>
                        <div class="d-flex align-items-center mb-3">
                            <img src="assets/img/user2.jpg" alt="Samantha Esplana" class="rounded-circle me-3"
                                style="width:50px;height:50px;object-fit:cover;">
                            <div>
                                <strong style="font-size:14px;">Samantha Esplana</strong>
                                <p class="text-muted small mb-0">Alabang, Muntinlupa</p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="far fa-star text-warning"></i>
                        </div>
                        <p class="text-muted fst-italic" style="font-size:13px;">"Very professional and reliable
                            service. Everything was done on time and communication was clear throughout the process."
                        </p>
                    </div>
                </div>

                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div
                        style="background:#fff; border:1px solid rgba(0,0,0,0.08); border-radius:16px; padding:1.5rem; position:relative;">
                        <span
                            style="position:absolute; top:12px; right:18px; font-size:48px; line-height:1; color:#ddd; font-family:Georgia,serif;">&ldquo;</span>
                        <div class="d-flex align-items-center mb-3">
                            <img src="assets/img/user2.jpg" alt="Rayne Velasco" class="rounded-circle me-3"
                                style="width:50px;height:50px;object-fit:cover;">
                            <div>
                                <strong style="font-size:14px;">Rayne Velasco</strong>
                                <p class="text-muted small mb-0">Bacoor, Cavite</p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="far fa-star text-warning"></i>
                        </div>
                        <p class="text-muted fst-italic" style="font-size:13px;">"They are so accommodating and
                            responsive! They answered all my questions that I needed to know about installing solar,
                            which really helped me decide. Highly recommended!"</p>
                    </div>
                </div>

            </div>
        </div>
    </section>



    <!-- 6 Reasons Section -->
    <section class="solar-reasons-section" data-checkout-hide>
        <div class="solar-reasons-container">
            <!-- LEFT SIDE - ILLUSTRATION -->
            <div class="reasons-illustration" data-aos="fade-right">
                <h2 class="reasons-title">
                    6 Reasons Why<br>
                    <span class="light-text">Your Home Must Be Powered by</span><br>
                    <span class="brand-text">SolarPower Energy Corporation</span>
                </h2>

                <p class="reasons-subtitle">
                    Smart, sustainable, and cost-efficient energy solutions built for Filipino homes.
                </p>
                <div class="illustration-wrapper">
                    <!-- Sun with Rays -->
                    <div class="sun">
                        <div class="sun-ray"></div>
                        <div class="sun-ray"></div>
                        <div class="sun-ray"></div>
                        <div class="sun-ray"></div>
                        <div class="sun-ray"></div>
                        <div class="sun-ray"></div>
                        <div class="sun-ray"></div>
                        <div class="sun-ray"></div>
                    </div>

                    <!-- Ground -->
                    <div class="ground"></div>

                    <!-- House Container -->
                    <div class="house-container">
                        <!-- Roof -->
                        <div class="roof"></div>

                        <!-- Solar Panel -->
                        <div class="solar-panel">
                            <div class="solar-cell"></div>
                            <div class="solar-cell"></div>
                            <div class="solar-cell"></div>
                            <div class="solar-cell"></div>
                            <div class="solar-cell"></div>
                            <div class="solar-cell"></div>
                            <div class="solar-cell"></div>
                            <div class="solar-cell"></div>
                        </div>

                        <!-- Wiring System -->
                        <div class="wiring">
                            <div class="wire wire-vertical"></div>
                            <div class="wire wire-horizontal"></div>
                            <div class="wire wire-to-left-window"></div>
                            <div class="wire wire-to-right-window"></div>
                        </div>

                        <!-- Junction Box -->
                        <div class="junction-box"></div>

                        <!-- Energy Particles flowing through wires -->
                        <div class="energy-particle particle-1"></div>
                        <div class="energy-particle particle-2"></div>
                        <div class="energy-particle particle-3"></div>

                        <!-- House Body -->
                        <div class="house-body">
                            <div class="window window-left"></div>
                            <div class="window window-right"></div>
                            <div class="door">
                                <div class="door-knob"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Pine Trees -->
                    <div class="tree tree-left">
                        <div class="pine-layer pine-layer-4"></div>
                        <div class="pine-layer pine-layer-3"></div>
                        <div class="pine-layer pine-layer-2"></div>
                        <div class="pine-layer pine-layer-1"></div>
                        <div class="tree-trunk"></div>
                    </div>

                    <div class="tree tree-right">
                        <div class="pine-layer pine-layer-4"></div>
                        <div class="pine-layer pine-layer-3"></div>
                        <div class="pine-layer pine-layer-2"></div>
                        <div class="pine-layer pine-layer-1"></div>
                        <div class="tree-trunk"></div>
                    </div>
                </div>
            </div>

            <!-- RIGHT SIDE - ACCORDION -->
            <div class="reasons-accordion" data-aos="fade-left">

                <!-- Accordion Item 1 -->
                <div class="accordion-item">
                    <div class="accordion-header">
                        <div class="accordion-icon-wrapper">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path
                                    d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </div>
                        <h3 class="accordion-title">Protection Against Rising Electricity Costs</h3>
                        <div class="accordion-toggle">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                            </svg>
                        </div>
                    </div>
                    <div class="accordion-content">
                        <div class="accordion-content-inner">
                            <p>Lock in your energy costs and shield yourself from unpredictable utility rate increases.
                                Solar provides stable, predictable energy expenses for decades.</p>
                            <span class="reason-tag">Financial Security</span>
                        </div>
                    </div>
                </div>

                <!-- Accordion Item 2 -->
                <div class="accordion-item">
                    <div class="accordion-header">
                        <div class="accordion-icon-wrapper">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
                            </svg>
                        </div>
                        <h3 class="accordion-title">Energy Independence</h3>
                        <div class="accordion-toggle">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                            </svg>
                        </div>
                    </div>
                    <div class="accordion-content">
                        <div class="accordion-content-inner">
                            <p>Generate your own clean electricity and reduce reliance on the grid. Take control of your
                                power supply and enjoy freedom from utility companies.</p>
                            <span class="reason-tag">Self-Sufficiency</span>
                        </div>
                    </div>
                </div>

                <!-- Accordion Item 3 -->
                <div class="accordion-item">
                    <div class="accordion-header">
                        <div class="accordion-icon-wrapper">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M3 12h18M3 6h18M3 18h18" />
                                <circle cx="12" cy="12" r="10" />
                            </svg>
                        </div>
                        <h3 class="accordion-title">Environment Friendly</h3>
                        <div class="accordion-toggle">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                            </svg>
                        </div>
                    </div>
                    <div class="accordion-content">
                        <div class="accordion-content-inner">
                            <p>Reduce your carbon footprint and contribute to a cleaner planet. Solar energy produces
                                zero emissions, helping combat climate change for future generations.</p>
                            <span class="reason-tag">Green Living</span>
                        </div>
                    </div>
                </div>

                <!-- Accordion Item 4 -->
                <div class="accordion-item">
                    <div class="accordion-header">
                        <div class="accordion-icon-wrapper">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" />
                            </svg>
                        </div>
                        <h3 class="accordion-title">Low Maintenance</h3>
                        <div class="accordion-toggle">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                            </svg>
                        </div>
                    </div>
                    <div class="accordion-content">
                        <div class="accordion-content-inner">
                            <p>Solar panels require minimal upkeep with no moving parts. Simple occasional cleaning and
                                standard warranties ensure worry-free operation for 25+ years.</p>
                            <span class="reason-tag">Hassle-free</span>
                        </div>
                    </div>
                </div>

                <!-- Accordion Item 5 -->
                <div class="accordion-item">
                    <div class="accordion-header">
                        <div class="accordion-icon-wrapper">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                            </svg>
                        </div>
                        <h3 class="accordion-title">Government Incentives & Rebates</h3>
                        <div class="accordion-toggle">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                            </svg>
                        </div>
                    </div>
                    <div class="accordion-content">
                        <div class="accordion-content-inner">
                            <p>Take advantage of tax credits, rebates, and incentive programs that significantly reduce
                                installation costs. Save thousands with available financial support.</p>
                            <span class="reason-tag">Save More</span>
                        </div>
                    </div>
                </div>

                <!-- Accordion Item 6 -->
                <div class="accordion-item">
                    <div class="accordion-header">
                        <div class="accordion-icon-wrapper">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
                            </svg>
                        </div>
                        <h3 class="accordion-title">Reliable Long-Term Investment</h3>
                        <div class="accordion-toggle">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                            </svg>
                        </div>
                    </div>
                    <div class="accordion-content">
                        <div class="accordion-content-inner">
                            <p>Increase your property value while enjoying immediate savings. Solar systems pay for
                                themselves through energy savings and boost home resale value.</p>
                            <span class="reason-tag">Smart Investment</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Contact Us Section -->
    <section class="contact-us" id="contact-us" data-checkout-hide>
        <div class="container">
            <div class="row">
                <!-- Left Side - Contact Information -->
                <div class="col-lg-5 mb-4 mb-lg-0" data-aos="fade-right">
                    <div class="contact-info">
                        <h2>Contact Us</h2>

                        <!-- Visit Us Section -->
                        <div class="visit-us-section">
                            <h3>Visit Us</h3>
                            <p>Connect with our solarpower experts to discuss the best energy solutions for your needs.
                            </p>
                            <a href="https://api.whatsapp.com/send?phone=639953947379" class="whatsapp-btn" target="_blank">
                                <i class="fab fa-whatsapp"></i>
                                Chat on WhatsApp
                            </a>
                        </div>

                        <!-- Company Information -->
                        <div class="company-info">
                            <div class="contact-detail">
                                <div class="icon-wrap"><i class="fas fa-map-marker-alt"></i></div>
                                <div>
                                    <strong>Address</strong>
                                    <p>4/F PBB Corporate Center, 1906 Finance Drive, Madrigal Business Park 1, Ayala
                                        Alabang, Muntinlupa City, 1780, Philippines</p>
                                </div>
                            </div>

                            <div class="contact-detail">
                                <div class="icon-wrap"><i class="fas fa-phone"></i></div>
                                <div>
                                    <strong>Phone</strong>
                                    <span class="phone-number" id="phone-copy"
                                        onclick="copyToClipboard('+639953947379', this)">+639953947379</span>
                                </div>
                            </div>

                            <div class="contact-detail">
                                <div class="icon-wrap"><i class="fas fa-envelope"></i></div>
                                <div>
                                    <strong>Email</strong>
                                    <a href="mailto:solar@solarpower.com.ph"
                                        class="contact-link">solar@solarpower.com.ph</a>
                                </div>
                            </div>
                        </div>

                        <!-- Business Hours -->
                        <div class="hours-section">
                            <button class="hours-toggle" onclick="toggleHours()">
                                <strong>Business Hours</strong>
                                <i class="fas fa-chevron-down" id="hours-icon"></i>
                            </button>
                            <div class="hours-content" id="hours-content">
                                <div class="hour-item">
                                    <span>Monday</span>
                                    <span>8:00 AM - 5:00 PM</span>
                                </div>
                                <div class="hour-item">
                                    <span>Tuesday</span>
                                    <span>8:00 AM - 5:00 PM</span>
                                </div>
                                <div class="hour-item">
                                    <span>Wednesday</span>
                                    <span>8:00 AM - 5:00 PM</span>
                                </div>
                                <div class="hour-item">
                                    <span>Thursday</span>
                                    <span>8:00 AM - 5:00 PM</span>
                                </div>
                                <div class="hour-item">
                                    <span>Friday</span>
                                    <span>8:00 AM - 5:00 PM</span>
                                </div>
                                <div class="hour-item">
                                    <span>Saturday</span>
                                    <span>8:00 AM - 5:00 PM</span>
                                </div>
                                <div class="hour-item">
                                    <span>Sunday</span>
                                    <span>Closed</span>
                                </div>
                            </div>
                        </div>

                        <!-- Social Links -->
                        <div class="contact-social-links">
                            <p class="contact-social-label">Follow Us</p>
                            <div class="social-links">
                                <a href="https://www.facebook.com/p/SolarPower-Energy-Corporation-61578373983187/"
                                    target="_blank" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                                <a href="https://www.instagram.com/solarpowerenergycorporation?igsh=MWh4YTEyYWpzbDNlNQ=="
                                    target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                                <a href="https://www.tiktok.com/@solarpower.energy?_r=1&_t=ZS-92HlpTBUuzF"
                                    target="_blank" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
                                <a href="https://youtube.com/@solarpowerenergycorporation?si=-kln0fTid4zMZDXq"
                                    target="_blank" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                                <a href="https://www.linkedin.com/in/solar-power-6792283aa" target="_blank"
                                    aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side - Contact Form -->
                <div class="col-lg-7" data-aos="fade-left">
                    <div class="contact-form-wrapper">
                        <h3 class="mb-4">Send us a Message</h3>
                        <form class="contact-form" id="contactForm" onsubmit="submitContactForm(event)">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <input type="text" class="form-control" id="contact_name" name="name"
                                        placeholder="Full Name *" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <input type="email" class="form-control" id="contact_email" name="email"
                                        placeholder="Email Address *" required>
                                </div>

                                <div class="col-12 mb-3">
                                    <div class="input-group">
                                        <span class="input-group-text">+63 &nbsp;<i class="fas fa-chevron-down"
                                                style="font-size:10px;color:#aaa;"></i></span>
                                        <input type="tel" class="form-control" id="contact_phone"
                                            placeholder="9XX XXX XXXX" required maxlength="10" pattern="[0-9]{10}"
                                            oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                        <input type="hidden" id="contact_phone_full" name="phone">
                                    </div>
                                </div>

                                <div class="col-12 mb-4">
                                    <textarea class="form-control" id="contact_message" name="message" rows="6"
                                        placeholder="Your Message *" required></textarea>
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn-submit" id="contactSubmitBtn">
                                        <span class="btn-text">Send Message</span>
                                        <span class="btn-spinner d-none">
                                            <i class="fas fa-spinner fa-spin"></i> Sending...
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="subscription-section" data-checkout-hide>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8" data-aos="zoom-in">
                    <div class="subscription-bar">
                        <h3>Subscribe Now!</h3>
                        <p style="color: rgba(255,255,255,0.9); margin-bottom: 20px;">Get weekly solar tips, updates,
                            and exclusive offers delivered to your inbox</p>
                        <form id="subscribe-form" class="d-flex">
                            <input type="email" name="email" id="subscribe-email" class="form-control"
                                placeholder="Enter your email address" required>
                            <button type="submit" class="btn btn-subscribe" id="subscribe-btn">
                                <span class="btn-text">Subscribe!</span>
                                <span class="btn-spinner d-none">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Success Modal -->
    <div class="modal fade" id="contactSuccessModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Message Sent
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-solar-panel text-success" style="font-size: 48px;"></i>
                    </div>
                    <p class="mb-1">
                        Thank you for sending contacts
                    </p>
                    <strong>Enjoy browsing our website!</strong>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include "includes/footer.php" ?>

    <?php include "includes/faqChat.php" ?>



    <!-- INSPECTION MODAL -->
    <div class="modal fade" id="inspectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 overflow-hidden position-relative">

                <!-- Close Button -->
                <button type="button" class="btn-close position-absolute end-0 m-3" data-bs-dismiss="modal"
                    style="z-index:1060;"></button>

                <div class="row g-0 min-vh-modal">

                    <!-- LEFT INFO PANEL -->
                    <div class="col-lg-5 d-none d-lg-flex inspection-left-panel"
                        style="background-color:#0a5c3d; background-image: linear-gradient(160deg, rgba(20,40,20,.92) 0%, rgba(10,92,61,.85) 100%), url('assets/img/solar-install.jpg'); background-size:cover; background-position:center;">
                        <div class="w-100 p-5 text-white d-flex flex-column justify-content-center">

                            <div class="inspection-badge mb-3">
                                <i class="fas fa-solar-panel me-2"></i> Free Site Assessment
                            </div>

                            <h2 class="fw-bold mb-3">Ready to <span class="text-warning">Switch<br>to Solar?</span></h2>
                            <p class="mb-4 opacity-75">Get a personalized solar quotation and let our certified engineers design the perfect system for your home or business.</p>

                            <ul class="list-unstyled inspection-features">
                                <li class="mb-3"><i class="fas fa-check-circle text-warning me-2"></i> Professional
                                    Assessment</li>
                                <li class="mb-3"><i class="fas fa-check-circle text-warning me-2"></i> Accurate ROI
                                    Projection</li>
                                <li class="mb-3"><i class="fas fa-check-circle text-warning me-2"></i> Custom System
                                    Design</li>
                            </ul>

                            <hr class="border-white opacity-10 my-4">

                            <p class="small opacity-50 mb-0">
                                <i class="fas fa-shield-alt me-1"></i>
                                Your information is secure and will never be shared.
                            </p>
                        </div>
                    </div>

                    <!-- FORM PANEL -->
                    <div class="col-lg-7 bg-white p-4 p-md-4" style="max-height: 80vh; overflow-y: auto;">
                        <div class="mb-3">
                            <h2 class="fw-bold">Get Your Free Solar Estimate</h2>
                            <p class="text-muted small">We'll contact you within 24 hours.</p>
                        </div>

                        <form id="inspectionForm" class="inspection-form">
                            <div class="row">

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small text-uppercase">Full Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" name="fullname" class="form-control"
                                            placeholder="Juan Dela Cruz" required>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small text-uppercase">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" name="email" class="form-control"
                                            placeholder="juan@email.com" required>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small text-uppercase">Contact Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text"
                                            style="background:#e8f4ef;border-color:#dee2e6;color:#0a5c3d;font-weight:700;font-size:0.93rem;">+639</span>
                                        <input type="tel" name="phone" class="form-control" placeholder="XXXXXXXXX"
                                            required maxlength="9" pattern="[0-9]{9}"
                                            oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                    </div>
                                    <input type="hidden" name="phone_full" class="insp-phone-full">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small text-uppercase">Property Type</label>
                                    <select name="property_type" class="form-select" required>
                                        <option value="" disabled selected>Select type</option>
                                        <option value="Residential">Residential</option>
                                        <option value="Commercial">Commercial</option>
                                    </select>
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label fw-semibold small text-uppercase">Complete Address</label>
                                    <textarea name="address" class="form-control" rows="2"
                                        placeholder="House No., Street, Brgy, City" required></textarea>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small text-uppercase">Preferred Assessment Date</label>
                                    <input type="date" name="inspection_date" class="form-control" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small text-uppercase">Monthly Bill (₱)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" name="bill" class="form-control" placeholder="e.g. 5000"
                                            required>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small text-uppercase">Roof Type</label>
                                    <select name="roof_type" id="roofTypeSelect" class="form-select" required>
                                        <option value="" disabled selected>Select roof type</option>
                                        <option value="Concrete/Flat Roof"> Concrete / Flat Roof</option>
                                        <option value="Corrugated Metal"> Corrugated Metal</option>
                                        <option value="Tile (Clay/Concrete)"> Tile (Clay / Concrete)</option>
                                        <option value="Asphalt Shingles"> Asphalt Shingles</option>
                                        <option value="Other">Other (Please specify)</option>
                                    </select>
                                    <input type="text" name="roof_type_other" id="roofOtherInput"
                                        class="form-control mt-2 d-none" placeholder="Please describe your roof type">
                                </div>

                                <!--<div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-uppercase">Terms of Payment</label>
                                <select name="payment_terms" class="form-select" required>
                                    <option value="" disabled selected>Select payment method</option>
                                    <option value="COD">Cash on Delivery (COD)</option>
                                    <option value="Installment">Installment</option>
                                    <option value="Rent To Own">Rent To Own</option>
                                    <option value="Solar Loans">Solar Loans</option>
                                </select>
                            </div>-->

                                <div class="col-12 mb-4">
                                    <label class="form-label fw-semibold small text-uppercase">Additional Notes
                                        (Optional)</label>
                                    <textarea name="notes" class="form-control" rows="3"
                                        placeholder="Tell us about your roof type or any specific concerns..."></textarea>
                                </div>

                            </div>

                            <button type="submit" class="btn w-100 py-3 fw-bold text-uppercase" id="inspectionBtn"
                                style="background:linear-gradient(135deg,#f39c12,#e67e22);color:#fff;border:none;">
                                <span class="btn-text"><i class="fas fa-calculator me-2"></i>GET MY FREE ESTIMATE</span>
                                <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- SUCCESS MODAL -->
    <div class="modal fade" id="inspectionSuccessModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 overflow-hidden text-center">
                <div style="height:5px; background: linear-gradient(90deg,#f39c12,#e67e22);"></div>
                <div class="modal-body py-5 px-4">
                    <i class="fas fa-solar-panel text-warning mb-3" style="font-size:56px;"></i>
                    <h4 class="fw-bold mb-2">Request Submitted!</h4>
                    <p class="text-muted mb-0">
                        Your inspection request has been received.<br>
                        <strong class="text-dark">Our team will contact you within business hours.</strong>
                    </p>
                </div>
                <div class="modal-footer border-0 justify-content-center pb-4">
                    <button type="button" class="btn fw-bold px-5 py-2" id="successOkBtn" data-bs-dismiss="modal">
                        Got it, thanks!
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delivery Fee Modal -->
    <!-- ═══════════════════════════════════════════════════════
         DELIVERY & INSTALLATION METRICS MODAL — Premium Redesign
         Brand: Deep Forest Green #0D5C3A | Solar Gold #F2A900
    ═══════════════════════════════════════════════════════ -->
    <style>
        /* ── Modal Shell ── */
        #deliveryFeeModal .modal-content {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 24px 64px rgba(0,0,0,0.18);
            font-family: 'Inter', 'Segoe UI', sans-serif;
        }

        /* ── Header ── */
        #deliveryFeeModal .dfm-header {
            background: linear-gradient(135deg, #0D5C3A 0%, #0a4a2e 100%);
            padding: 24px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        #deliveryFeeModal .dfm-header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        #deliveryFeeModal .dfm-icon-wrap {
            width: 44px;
            height: 44px;
            background: rgba(242,169,0,0.15);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        #deliveryFeeModal .dfm-title {
            color: #FFFFFF;
            font-size: 1.2rem;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.3px;
        }
        #deliveryFeeModal .dfm-subtitle {
            color: rgba(255,255,255,0.6);
            font-size: 0.78rem;
            margin: 2px 0 0;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }
        #deliveryFeeModal .dfm-close {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.15);
            color: rgba(255,255,255,0.8);
            border-radius: 8px;
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 16px;
            flex-shrink: 0;
        }
        #deliveryFeeModal .dfm-close:hover {
            background: rgba(255,255,255,0.18);
            color: #fff;
            transform: scale(1.08);
        }

        /* ── Body ── */
        #deliveryFeeModal .modal-body {
            padding: 0;
            background: #F8FAFB;
        }
        #deliveryFeeModal .dfm-body {
            display: grid;
            grid-template-columns: 1fr 1px 1fr;
            min-height: 420px;
        }
        #deliveryFeeModal .dfm-divider {
            background: #E5E7EB;
            width: 1px;
        }

        /* ── Column shared ── */
        #deliveryFeeModal .dfm-col {
            padding: 28px 26px;
        }
        #deliveryFeeModal .dfm-col-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #6B7280;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #E5E7EB;
        }
        #deliveryFeeModal .dfm-col-title svg {
            color: #0D5C3A;
            flex-shrink: 0;
        }

        /* ── Sector label ── */
        #deliveryFeeModal .dfm-sector {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.9px;
            color: #9CA3AF;
            margin: 16px 0 8px;
        }
        #deliveryFeeModal .dfm-sector:first-of-type { margin-top: 0; }

        /* ── Price rows ── */
        #deliveryFeeModal .dfm-price-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 9px 10px;
            border-radius: 7px;
            transition: background 0.18s ease;
            cursor: default;
        }
        #deliveryFeeModal .dfm-price-row:hover {
            background-color: #F0FDF4;
        }
        #deliveryFeeModal .dfm-badge {
            display: inline-flex;
            align-items: center;
            background: #F3F4F6;
            border: 1px solid #E5E7EB;
            border-radius: 5px;
            padding: 3px 9px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #374151;
            letter-spacing: 0.2px;
        }
        #deliveryFeeModal .dfm-price {
            font-size: 0.95rem;
            font-weight: 700;
            color: #0D5C3A;
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.3px;
        }
        #deliveryFeeModal .dfm-sub-divider {
            border: none;
            border-top: 1px dashed #E5E7EB;
            margin: 8px 0;
        }

        /* ── Province row ── */
        #deliveryFeeModal .dfm-province-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 10px;
            border-radius: 7px;
            transition: background 0.18s ease;
            cursor: default;
        }
        #deliveryFeeModal .dfm-province-row:hover { background-color: #F0FDF4; }
        #deliveryFeeModal .dfm-province-name {
            font-size: 0.88rem;
            color: #374151;
            font-weight: 500;
        }
        #deliveryFeeModal .dfm-province-price {
            font-size: 0.9rem;
            font-weight: 700;
            color: #0D5C3A;
            font-variant-numeric: tabular-nums;
        }

        /* ── Vismin alert ── */
        #deliveryFeeModal .dfm-vismin-alert {
            margin-top: 16px;
            background: linear-gradient(135deg, rgba(13,92,58,0.05) 0%, rgba(242,169,0,0.05) 100%);
            border: 1px solid rgba(13,92,58,0.15);
            border-left: 3px solid #F2A900;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 0.82rem;
            color: #374151;
            line-height: 1.5;
        }
        #deliveryFeeModal .dfm-vismin-alert strong {
            display: block;
            color: #0D5C3A;
            margin-bottom: 3px;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── Installation cards ── */
        #deliveryFeeModal .dfm-install-card {
            border-radius: 10px;
            padding: 16px 18px;
            margin-bottom: 12px;
        }
        #deliveryFeeModal .dfm-install-card.primary {
            background: rgba(13,92,58,0.05);
            border: 1px solid rgba(13,92,58,0.12);
            border-left: 3px solid #F2A900;
        }
        #deliveryFeeModal .dfm-install-card.secondary {
            background: #FFFFFF;
            border: 1px solid #E5E7EB;
        }
        #deliveryFeeModal .dfm-install-card-label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.9px;
            color: #6B7280;
            margin-bottom: 6px;
        }
        #deliveryFeeModal .dfm-install-card-name {
            font-size: 1rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 6px;
        }
        #deliveryFeeModal .dfm-install-fee {
            display: flex;
            align-items: baseline;
            gap: 6px;
        }
        #deliveryFeeModal .dfm-install-fee-label {
            font-size: 0.8rem;
            color: #6B7280;
        }
        #deliveryFeeModal .dfm-install-fee-value {
            font-size: 1.4rem;
            font-weight: 800;
            color: #0D5C3A;
            letter-spacing: -0.5px;
            font-variant-numeric: tabular-nums;
        }
        #deliveryFeeModal .dfm-install-fee-value.free {
            color: #059669;
        }

        /* ── Checklist ── */
        #deliveryFeeModal .dfm-checklist-title {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.9px;
            color: #6B7280;
            margin: 20px 0 10px;
            padding-top: 16px;
            border-top: 1px solid #E5E7EB;
        }
        #deliveryFeeModal .dfm-check-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 7px 0;
            font-size: 0.86rem;
            color: #374151;
            line-height: 1.4;
        }
        #deliveryFeeModal .dfm-check-item svg {
            flex-shrink: 0;
            margin-top: 1px;
        }

        /* ── Footer ── */
        #deliveryFeeModal .dfm-footer {
            padding: 16px 28px;
            border-top: 1px solid #E5E7EB;
            background: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        #deliveryFeeModal .dfm-footer-note {
            font-size: 0.78rem;
            color: #9CA3AF;
        }
        #deliveryFeeModal .dfm-footer-btn {
            background: #0D5C3A;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 9px 22px;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            letter-spacing: 0.2px;
        }
        #deliveryFeeModal .dfm-footer-btn:hover {
            background: #0a4a2e;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(13,92,58,0.3);
        }

        @media (max-width: 768px) {
            #deliveryFeeModal .dfm-body { grid-template-columns: 1fr; }
            #deliveryFeeModal .dfm-divider { height: 1px; width: 100%; }
        }
    </style>

    <div class="modal fade" id="deliveryFeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 860px;">
            <div class="modal-content">

                <!-- ── HEADER ── -->
                <div class="dfm-header">
                    <div class="dfm-header-left">
                        <div class="dfm-icon-wrap">
                            <!-- Delivery Truck SVG -->
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1 3h15v13H1V3z" stroke="#F2A900" stroke-width="1.7" stroke-linejoin="round"/>
                                <path d="M16 8h4l3 3v5h-7V8z" stroke="#F2A900" stroke-width="1.7" stroke-linejoin="round"/>
                                <circle cx="5.5" cy="18.5" r="2" stroke="#F2A900" stroke-width="1.6"/>
                                <circle cx="18.5" cy="18.5" r="2" stroke="#F2A900" stroke-width="1.6"/>
                            </svg>
                        </div>
                        <div>
                            <div class="dfm-title">Delivery &amp; Installation Metrics</div>
                            <div class="dfm-subtitle">SolarPower Energy Corporation · Rate Sheet</div>
                        </div>
                    </div>
                    <button class="dfm-close" data-bs-dismiss="modal" aria-label="Close">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M1 1l12 12M13 1L1 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </button>
                </div>

                <!-- ── BODY ── -->
                <div class="modal-body p-0">
                    <div class="dfm-body">

                        <!-- ── LEFT: DELIVERY ── -->
                        <div class="dfm-col">
                            <div class="dfm-col-title">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0D5C3A" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                                Deliveries by Region
                            </div>

                            <!-- Sector 1: Metro Manila -->
                            <div class="dfm-sector">Metro Manila &amp; Adjacent Hubs</div>

                            <div class="dfm-price-row">
                                <span class="dfm-badge">1 – 5 km</span>
                                <span class="dfm-price">₱2,000</span>
                            </div>
                            <div class="dfm-price-row">
                                <span class="dfm-badge">6 – 10 km</span>
                                <span class="dfm-price">₱2,500</span>
                            </div>
                            <div class="dfm-price-row">
                                <span class="dfm-badge">11 – 20 km</span>
                                <span class="dfm-price">₱4,000</span>
                            </div>
                            <div class="dfm-price-row">
                                <span class="dfm-badge">21 – 30 km</span>
                                <span class="dfm-price">₱6,000</span>
                            </div>

                            <!-- Sector 2: Provincial Luzon -->
                            <div class="dfm-sector" style="margin-top:20px;">Provincial Luzon Operations</div>

                            <div style="font-size:0.7rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;letter-spacing:0.6px;padding:0 10px 4px;margin-bottom:2px;">South Luzon</div>
                            <div class="dfm-province-row">
                                <span class="dfm-province-name">Cavite</span>
                                <span class="dfm-province-price">₱4,200</span>
                            </div>
                            <hr class="dfm-sub-divider">
                            <div class="dfm-province-row">
                                <span class="dfm-province-name">Laguna</span>
                                <span class="dfm-province-price">₱6,000</span>
                            </div>
                            <hr class="dfm-sub-divider">
                            <div class="dfm-province-row">
                                <span class="dfm-province-name">Batangas</span>
                                <span class="dfm-province-price">₱8,500</span>
                            </div>
                            <hr class="dfm-sub-divider">
                            <div class="dfm-province-row">
                                <span class="dfm-province-name">Rizal</span>
                                <span class="dfm-province-price">₱7,000</span>
                            </div>

                            <div style="font-size:0.7rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;letter-spacing:0.6px;padding:14px 10px 4px;margin-bottom:2px;">North Luzon</div>
                            <div class="dfm-province-row">
                                <span class="dfm-province-name">Bulacan</span>
                                <span class="dfm-province-price">₱7,000</span>
                            </div>
                            <hr class="dfm-sub-divider">
                            <div class="dfm-province-row">
                                <span class="dfm-province-name">Pampanga</span>
                                <span class="dfm-province-price">₱10,000</span>
                            </div>
                            <hr class="dfm-sub-divider">
                            <div class="dfm-province-row">
                                <span class="dfm-province-name">Tarlac</span>
                                <span class="dfm-province-price">₱10,000</span>
                            </div>

                            <!-- Vismin Alert -->
                            <div class="dfm-vismin-alert">
                                <strong>🗺 Visayas &amp; Mindanao</strong>
                                Shipping costs may vary due to cargo weight and distance. Please contact us for a custom cargo quote.
                            </div>
                        </div>

                        <!-- ── DIVIDER ── -->
                        <div class="dfm-divider"></div>

                        <!-- ── RIGHT: INSTALLATION ── -->
                        <div class="dfm-col">
                            <div class="dfm-col-title">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0D5C3A" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                                Comprehensive Installation Rates
                            </div>

                            <!-- Card A: Grid-tie & Hybrid -->
                            <div class="dfm-install-card primary">
                                <div class="dfm-install-card-label">
                                    <svg width="10" height="10" viewBox="0 0 10 10" fill="#F2A900"><circle cx="5" cy="5" r="5"/></svg>
                                    &nbsp;Grid-tie &amp; Hybrid Systems
                                </div>
                                <div class="dfm-install-card-name">Complete Solar Package Install</div>
                                <div class="dfm-install-fee">
                                    <span class="dfm-install-fee-label">Installation Fee</span>
                                    <span class="dfm-install-fee-value">₱2,000</span>
                                </div>
                            </div>

                            <!-- Card B: Other Components -->
                            <div class="dfm-install-card secondary">
                                <div class="dfm-install-card-label">
                                    <svg width="10" height="10" viewBox="0 0 10 10" fill="#D1D5DB"><circle cx="5" cy="5" r="5"/></svg>
                                    &nbsp;Other Components &amp; Products
                                </div>
                                <div class="dfm-install-card-name">Individual Component Supply</div>
                                <div class="dfm-install-fee">
                                    <span class="dfm-install-fee-label">Installation</span>
                                    <span class="dfm-install-fee-value free">FREE</span>
                                </div>
                            </div>

                            <!-- What's Included Checklist -->
                            <div class="dfm-checklist-title">What's Included in Installation</div>

                            <div class="dfm-check-item">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="8" fill="#0D5C3A" fill-opacity="0.1"/><path d="M4.5 8l2.5 2.5 4.5-5" stroke="#0D5C3A" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Professional deployment by certified grid technicians
                            </div>
                            <div class="dfm-check-item">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="8" fill="#0D5C3A" fill-opacity="0.1"/><path d="M4.5 8l2.5 2.5 4.5-5" stroke="#0D5C3A" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                System testing and utility net-metering optimization
                            </div>
                            <div class="dfm-check-item">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="8" fill="#0D5C3A" fill-opacity="0.1"/><path d="M4.5 8l2.5 2.5 4.5-5" stroke="#0D5C3A" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Basic administrative training on system operation
                            </div>
                            <div class="dfm-check-item">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="8" fill="#0D5C3A" fill-opacity="0.1"/><path d="M4.5 8l2.5 2.5 4.5-5" stroke="#0D5C3A" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                1-year comprehensive installation warranty
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ── FOOTER ── -->
                <div class="dfm-footer">
                    <span class="dfm-footer-note">
                        <svg width="12" height="12" viewBox="0 0 16 16" fill="none" style="margin-right:4px;vertical-align:middle;"><circle cx="8" cy="8" r="7" stroke="#9CA3AF" stroke-width="1.5"/><path d="M8 7v5M8 5.5v.5" stroke="#9CA3AF" stroke-width="1.5" stroke-linecap="round"/></svg>
                        All prices are exclusive of VAT. Subject to change without prior notice.
                    </span>
                    <button class="dfm-footer-btn" data-bs-dismiss="modal">Got it, Close</button>
                </div>

            </div>
        </div>
    </div>





    <!-- Bootstrap JS Bundle -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });
    </script>

</body>
<script src="assets/script.js"></script>
<script>
    // ============================================
    // SOLAR POWER E-COMMERCE - ORGANIZED JAVASCRIPT
    // ============================================

    // ============================================
    // 1. GLOBAL VARIABLES & INITIALIZATION
    // ============================================
    let cart = [];

    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 Solar Power System Initialized');

        // Initialize all modules
        initializeCart();
        initializeFilters();
        initializeSort();
        initializeCheckout();
        initializeSubscription();
        initializeContactForm();
        initializeInspectionForm();
        setupCalculator();

        console.log('✅ All modules loaded successfully');
    });

    // ============================================
    // 2. CART MANAGEMENT
    // ============================================

    function initializeCart() {
        console.log('📦 Initializing cart system...');
        loadCartFromMemory();
        updateCartBadge();
    }

    function loadCartFromMemory() {
        // Load cart from memory (no localStorage in artifacts)
        if (window.cartStorage) {
            try {
                cart = JSON.parse(window.cartStorage);
                console.log('✅ Cart loaded:', cart.length, 'items');
            } catch (error) {
                console.error('❌ Error loading cart:', error);
                cart = [];
            }
        }
    }

    function saveCartToMemory() {
        // Save cart to memory
        try {
            window.cartStorage = JSON.stringify(cart);
            console.log('💾 Cart saved');
        } catch (error) {
            console.error('❌ Error saving cart:', error);
        }
    }

    function addToCartFromButton(btn) {
        console.log('🛒 Adding product to cart...');
        const product = JSON.parse(btn.getAttribute('data-product'));
        addToCartLogic(product);
        showCartPopup();
        showNotificationModal('success', '✅ Product added to cart!');
    }

    function addToCartLogic(product) {
        const existingItem = cart.find(item => item.id === product.id);
        const moq = parseInt(product.moq) || 1;

        if (existingItem) {
            existingItem.quantity += 1;
            console.log('📈 Increased quantity for:', product.displayName);
        } else {
            cart.push({
                id: product.id,
                displayName: product.displayName,
                brandName: product.brandName || '',
                price: parseFloat(product.price),
                image_path: product.image_path,
                quantity: moq, // start at MOQ, not 1
                moq: moq
            });
            if (moq > 1) {
                showNotificationModal('info', `ℹ️ Minimum order for Solar Panels is ${moq} units.`);
            }
            console.log('➕ Added new item:', product.displayName, '| MOQ:', moq);
        }

        saveCartToMemory();
        updateCartBadge();
    }

    function updateCartBadge() {
        const badge = document.querySelector('.cart-badge');
        if (!badge) return;

        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);

        if (totalItems > 0) {
            badge.textContent = totalItems;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }

    function updateCartQuantity(productId, change) {
        const item = cart.find(i => i.id === productId);
        if (item) {
            const moq = item.moq || 1;
            item.quantity += change;

            if (item.quantity < moq) {
                item.quantity = moq;
                if (moq > 1) {
                    showNotificationModal('info', `ℹ️ Minimum order quantity is ${moq} unit(s).`);
                }
                return;
            }

            saveCartToMemory();
            updateCartBadge();
            renderCartPopup();
        }
    }

    function removeFromCartPopup(productId) {
        if (confirm('Remove this item from cart?')) {
            cart = cart.filter(i => i.id !== productId);
            saveCartToMemory();
            updateCartBadge();
            renderCartPopup();
            showNotificationModal('success', 'Item removed from cart');
        }
    }

    function clearCart() {
        cart = [];
        saveCartToMemory();
        updateCartBadge();
    }

    // ============================================
    // 3. CART POPUP MODAL
    // ============================================

    function createCartModal() {
        let modal = document.getElementById('cartModal');
        if (modal) return modal;

        const modalHTML = `
        <div class="modal fade" id="cartModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Shopping Cart
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="cartModalBody">
                        <!-- Cart items will be rendered here -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Continue Shopping
                        </button>
                        <button type="button" class="btn btn-primary" onclick="proceedToCheckout()" id="proceedCheckoutBtn">
                            <i class="fas fa-arrow-right me-2"></i>
                            Proceed to Checkout
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
        return document.getElementById('cartModal');
    }

    function showCartPopup() {
        const modal = createCartModal();
        renderCartPopup();
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }

    function renderCartPopup() {
        const modalBody = document.getElementById('cartModalBody');
        const proceedBtn = document.getElementById('proceedCheckoutBtn');

        if (!modalBody) return;

        if (cart.length === 0) {
            modalBody.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart text-muted" style="font-size: 64px;"></i>
                <p class="mt-3 text-muted">Your cart is empty</p>
                <button class="btn btn-primary" data-bs-dismiss="modal">
                    Start Shopping
                </button>
            </div>
        `;
            if (proceedBtn) proceedBtn.disabled = true;
            return;
        }

        if (proceedBtn) proceedBtn.disabled = false;

        let subtotal = 0;
        let html = '<div class="cart-items-list">';

        cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;
            const moq = item.moq || 1;
            const minusDisabled = item.quantity <= moq ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '';

            html += `
            <div class="cart-item-row d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                <img src="${item.image_path}" 
                     style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;"
                     onerror="this.src='assets/img/placeholder.png'">
                <div class="flex-grow-1">
                    <h6 class="mb-1 fw-bold">${item.displayName}</h6>
                    <p class="text-muted mb-1" style="font-size: 0.9rem;">
                        ₱${item.price.toLocaleString()} × ${item.quantity}
                    </p>
                    <p class="mb-0 fw-bold text-primary">
                        ₱${itemTotal.toLocaleString(undefined, { minimumFractionDigits: 2 })}
                    </p>
                    ${(item.moq || 1) > 1 ? `<small style="color:#856404;background:#fff3cd;border-radius:4px;padding:1px 7px;font-size:0.72rem;"><i class="fas fa-layer-group"></i> MOQ: ${item.moq} pcs</small>` : ''}
                </div>
                <div class="d-flex flex-column gap-2">
                    <div class="quantity-controls d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-outline-secondary" 
                                onclick="updateCartQuantity(${item.id}, -1)" 
                                ${minusDisabled}>
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="fw-bold px-2">${item.quantity}</span>
                        <button class="btn btn-sm btn-outline-secondary" 
                                onclick="updateCartQuantity(${item.id}, 1)">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <button class="btn btn-sm btn-danger" 
                            onclick="removeFromCartPopup(${item.id})">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        `;
        });

        html += '</div>';

        html += `
        <div class="cart-summary bg-light p-3 rounded mt-3">
            <div class="d-flex justify-content-between mb-2">
                <span>Subtotal:</span>
                <span class="fw-bold">₱${subtotal.toLocaleString(undefined, { minimumFractionDigits: 2 })}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Delivery Fee:</span>
                <span class="text-info fw-bold">Calculated at checkout</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Installation Fee:</span>
                <span class="text-info fw-bold">Calculated at checkout</span>
            </div>
            <hr>
            <div class="d-flex justify-content-between" style="font-size: 1.2rem;">
                <span class="fw-bold">Subtotal:</span>
                <span class="fw-bold text-primary">₱${subtotal.toLocaleString(undefined, { minimumFractionDigits: 2 })}</span>
            </div>
            <small class="text-muted d-block mt-2">*Final total including delivery and installation fees will be shown at checkout</small>
        </div>
    `;

        modalBody.innerHTML = html;
    }

    function buyNowFromButton(btn) {
        const product = JSON.parse(btn.getAttribute('data-product'));

        // Clear cart and add only this product
        cart = [];
        addToCartLogic(product);

        // Go directly to checkout
        proceedToCheckout();
    }

    // ============================================
    // 4. CHECKOUT PROCESS
    // ============================================

    function initializeCheckout() {
        console.log('🛒 Initializing checkout system...');

        // Add phone number formatter
        const phoneInput = document.getElementById('cust_phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', formatPhoneNumber);
        }

        // Initialize Philippine address cascade dropdowns
        initializeAddressDropdowns();
    }

    // ============================================
    // PHILIPPINE ADDRESS CASCADE DROPDOWNS
    // Uses PSGC API: https://psgc.gitlab.io/api/
    // ============================================
    const PSGC_BASE = 'https://psgc.gitlab.io/api';

    async function psgcFetch(url) {
        const res = await fetch(url);
        if (!res.ok) throw new Error('PSGC fetch failed: ' + res.status);
        return res.json();
    }

    function setSelectError(selectId, msg) {
        const sel = document.getElementById(selectId);
        if (!sel) return;
        sel.innerHTML = '<option value="">' + msg + '</option>';
        sel.disabled = false;
    }

    function populateSelect(selectId, items, valueKey, labelKey, placeholder) {
        const sel = document.getElementById(selectId);
        if (!sel) return;
        sel.innerHTML = '<option value="">' + placeholder + '</option>';
        items
            .sort((a, b) => (a[labelKey] || '').localeCompare(b[labelKey] || ''))
            .forEach(function(item) {
                const opt = document.createElement('option');
                opt.value = item[valueKey];
                opt.textContent = item[labelKey];
                sel.appendChild(opt);
            });
        sel.disabled = false;
    }

    async function initializeAddressDropdowns() {
        const provinceEl = document.getElementById('province');
        const municipalityEl = document.getElementById('municipality');
        const barangayEl = document.getElementById('barangay');

        if (!provinceEl) return;

        // When province changes — load cities/municipalities
        provinceEl.addEventListener('change', async function() {
            const code = this.value;

            municipalityEl.innerHTML = '<option value="">Select City / Municipality</option>';
            municipalityEl.disabled = true;
            barangayEl.innerHTML = '<option value="">Select Barangay</option>';
            barangayEl.disabled = true;

            if (!code) return;

            // NCR cities stored as NCR_<cityCode>
            if (code.startsWith('NCR_')) {
                const cityCode = code.replace('NCR_', '');
                barangayEl.innerHTML = '<option value="">Loading barangays...</option>';
                try {
                    const barangays = await psgcFetch(PSGC_BASE + '/cities/' + cityCode + '/barangays/');
                    if (!barangays || barangays.length === 0) throw new Error('No barangays');
                    populateSelect('barangay', barangays, 'name', 'name', 'Select Barangay');
                    municipalityEl.innerHTML = '<option value="' + cityCode + '">' + provinceEl.options[provinceEl.selectedIndex].text + '</option>';
                    municipalityEl.disabled = false;
                } catch (e) {
                    setSelectError('barangay', 'Failed to load barangays. Please refresh.');
                }
                return;
            }

            municipalityEl.innerHTML = '<option value="">Loading cities...</option>';
            try {
                const cities = await psgcFetch(PSGC_BASE + '/provinces/' + code + '/cities/').catch(function() {
                    return [];
                });
                const municipalities = await psgcFetch(PSGC_BASE + '/provinces/' + code + '/municipalities/').catch(function() {
                    return [];
                });
                const combined = cities.concat(municipalities);
                if (combined.length === 0) throw new Error('No cities found');
                populateSelect('municipality', combined, 'code', 'name', 'Select City / Municipality');
            } catch (e) {
                console.error('City load error:', e);
                setSelectError('municipality', 'Failed to load cities. Please refresh.');
            }
        });

        // When municipality/city changes — load barangays
        municipalityEl.addEventListener('change', async function() {
            const code = this.value;

            barangayEl.innerHTML = '<option value="">Select Barangay</option>';
            barangayEl.disabled = true;

            if (!code) return;

            barangayEl.innerHTML = '<option value="">Loading barangays...</option>';
            try {
                let barangays = await psgcFetch(PSGC_BASE + '/cities/' + code + '/barangays/').catch(function() {
                    return null;
                });
                if (!barangays || barangays.length === 0) {
                    barangays = await psgcFetch(PSGC_BASE + '/municipalities/' + code + '/barangays/').catch(function() {
                        return [];
                    });
                }
                if (!barangays || barangays.length === 0) throw new Error('No barangays found');
                populateSelect('barangay', barangays, 'name', 'name', 'Select Barangay');
            } catch (e) {
                console.error('Barangay load error:', e);
                setSelectError('barangay', 'Failed to load barangays. Please refresh.');
            }
        });

        // Load all provinces on page init
        provinceEl.innerHTML = '<option value="">Loading provinces...</option>';
        provinceEl.disabled = true;
        try {
            const provinces = await psgcFetch(PSGC_BASE + '/provinces/');
            if (!provinces || provinces.length === 0) throw new Error('Empty provinces response');
            populateSelect('province', provinces, 'code', 'name', 'Select Province');

            // Append NCR (Metro Manila) highly-urbanized cities as a separate group
            const ncrCities = await psgcFetch(PSGC_BASE + '/regions/130000000/cities/').catch(function() {
                return [];
            });
            if (ncrCities && ncrCities.length > 0) {
                const optgroup = document.createElement('optgroup');
                optgroup.label = '--- NCR (Metro Manila) ---';
                ncrCities
                    .sort(function(a, b) {
                        return a.name.localeCompare(b.name);
                    })
                    .forEach(function(city) {
                        const opt = document.createElement('option');
                        opt.value = 'NCR_' + city.code;
                        opt.textContent = city.name + ' (NCR)';
                        optgroup.appendChild(opt);
                    });
                provinceEl.appendChild(optgroup);
            }
        } catch (e) {
            console.error('Province load error:', e);
            setSelectError('province', 'Failed to load provinces. Please refresh.');
        }
    }

    function formatPhoneNumber(event) {
        let value = event.target.value.replace(/\D/g, '');

        if (value.startsWith('09')) {
            value = '+639' + value.substring(2);
        } else if (value.startsWith('9') && value.length >= 10) {
            value = '+639' + value.substring(1);
        } else if (value.startsWith('639')) {
            value = '+' + value;
        } else if (value.startsWith('63') && value.length >= 12) {
            value = '+639' + value.substring(2);
        } else if (value.length > 0 && !value.startsWith('0') && !value.startsWith('6') && !value.startsWith('9')) {
            value = '+639' + value;
        }

        if (value.length > 13) {
            value = value.substring(0, 13);
        }

        event.target.value = value;
    }

    function proceedToCheckout() {
        console.log('📋 Proceeding to checkout...');

        if (cart.length === 0) {
            showNotificationModal('error', 'Your cart is empty');
            return;
        }

        // Close cart modal
        const cartModal = bootstrap.Modal.getInstance(document.getElementById('cartModal'));
        if (cartModal) {
            cartModal.hide();
        }

        // Show checkout section
        showCheckout();
        renderCheckoutSummary();
    }

    function showCheckout() {
        // Hide ALL content sections — keep only header, footer, and floating chat
        document.querySelectorAll('[data-checkout-hide]').forEach(el => {
            el.dataset.prevDisplay = el.style.display || '';
            el.style.display = 'none';
        });

        document.getElementById('checkoutSection').style.display = 'block';
        window.scrollTo(0, 0);

        goToStep(1);
    }

    function backToCatalog() {
        console.log('🔙 Returning to catalog...');

        document.getElementById('checkoutSection').style.display = 'none';

        // Restore all hidden content sections
        document.querySelectorAll('[data-checkout-hide]').forEach(el => {
            el.style.display = el.dataset.prevDisplay || '';
        });

        window.scrollTo(0, document.getElementById('catalogSection').offsetTop - 100);
    }

    function goToStep(step) {
        console.log('📍 Moving to step:', step);

        // Hide all steps
        for (let i = 1; i <= 3; i++) {
            document.getElementById(`checkoutStep${i}`).style.display = 'none';
            document.getElementById(`ind-step${i}`).classList.remove('active', 'completed');
        }

        // Show current step
        document.getElementById(`checkoutStep${step}`).style.display = 'block';
        document.getElementById(`ind-step${step}`).classList.add('active');

        // Mark previous steps as completed
        for (let i = 1; i < step; i++) {
            document.getElementById(`ind-step${i}`).classList.add('completed');
        }

        // Update progress indicator
        const checkoutSteps = document.getElementById('checkoutSteps');
        if (checkoutSteps) {
            checkoutSteps.setAttribute('data-step', step);
        }

        window.scrollTo(0, 0);
    }

    function renderCheckoutSummary() {
        console.log('📊 Rendering checkout summary...');

        const summaryDiv = document.getElementById('checkoutOrderSummary');
        const subtotalDisplay = document.getElementById('checkoutSubtotal');
        const totalDisplay = document.getElementById('checkoutTotal');

        if (cart.length === 0) {
            summaryDiv.innerHTML = '<p class="text-center text-muted">Your cart is empty.</p>';
            if (subtotalDisplay) subtotalDisplay.innerText = "₱0.00";
            if (totalDisplay) totalDisplay.innerText = "₱0.00";
            return;
        }

        let cartSubtotal = 0;
        let html = '';

        cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            cartSubtotal += itemTotal;

            const moq = item.moq || 1;
            const minusDisabled = item.quantity <= moq ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '';

            html += `
            <div class="d-flex align-items-center gap-3 mb-3 border-bottom pb-3">
                <img src="${item.image_path}" 
                     style="width:60px; height:60px; object-fit:cover; border-radius:8px;"
                     onerror="this.src='assets/img/placeholder.png'">
                <div class="flex-grow-1">
                    <p class="mb-1 fw-bold" style="font-size: 0.95rem;">${item.displayName}</p>
                    <small class="text-muted">₱${item.price.toLocaleString()} x ${item.quantity}</small>
                    <p class="mb-0 fw-bold text-primary" style="font-size: 0.9rem;">
                        ₱${itemTotal.toLocaleString(undefined, { minimumFractionDigits: 2 })}
                    </p>
                </div>
                <div class="d-flex flex-column align-items-end gap-2">
                    <div class="quantity-controls d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-outline-secondary" 
                                onclick="updateCheckoutQuantity(${item.id}, -1)" 
                                ${minusDisabled}>
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="fw-bold px-2">${item.quantity}</span>
                        <button class="btn btn-sm btn-outline-secondary" 
                                onclick="updateCheckoutQuantity(${item.id}, 1)">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <button class="btn btn-sm btn-danger" 
                            onclick="removeFromCheckout(${item.id})">
                        <i class="fas fa-trash-alt me-1"></i> Remove
                    </button>
                </div>
            </div>
        `;
        });

        summaryDiv.innerHTML = html;

        // Calculate fees
        const deliveryFee = calculateDeliveryFee();
        const installationFee = hasGridTieOrHybridProduct() ? 2000 : 0;
        const grandTotal = cartSubtotal + deliveryFee + installationFee;

        const formattedSubtotal = "₱" + cartSubtotal.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        const formattedTotal = "₱" + grandTotal.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        if (subtotalDisplay) subtotalDisplay.innerText = formattedSubtotal;
        if (totalDisplay) totalDisplay.innerText = formattedTotal;

        window.currentTotalAmount = grandTotal;

        // Update payment display with new total
        updatePaymentDisplay();
    }

    function updateCheckoutQuantity(productId, change) {
        updateCartQuantity(productId, change);
        renderCheckoutSummary();
    }

    function removeFromCheckout(productId) {
        if (confirm('Remove this item?')) {
            cart = cart.filter(i => i.id !== productId);
            saveCartToMemory();
            updateCartBadge();
            renderCheckoutSummary();

            if (cart.length === 0) {
                showNotificationModal('info', 'Cart is empty. Returning to catalog.');
                setTimeout(() => backToCatalog(), 1500);
            }
        }
    }

    function buildFullAddress() {
        const house = document.getElementById("house_street").value.trim();
        const provinceSel = document.getElementById("province");
        const municipalitySel = document.getElementById("municipality");
        const barangaySel = document.getElementById("barangay");

        let provinceText = provinceSel.options[provinceSel.selectedIndex]?.text || '';
        // Strip the ' (NCR)' suffix added for NCR cities shown in province dropdown
        provinceText = provinceText.replace(' (NCR)', '').replace('--- NCR (Metro Manila) ---', 'Metro Manila');
        const municipalityText = municipalitySel.options[municipalitySel.selectedIndex]?.text || '';
        const barangayText = barangaySel.value || '';

        document.getElementById("cust_address").value =
            `${house}, ${barangayText}, ${municipalityText}, ${provinceText}`;
    }

    function validateStep1() {
        console.log('✅ Validating customer details...');

        clearErrorStates();
        buildFullAddress();

        const name = document.getElementById('cust_name').value.trim();
        const email = document.getElementById('cust_email').value.trim();
        const phone = document.getElementById('cust_phone').value.trim();
        const address = document.getElementById('cust_address').value.trim();
        const house = document.getElementById('house_street')?.value.trim();
        const province = document.getElementById('province')?.value;
        const municipality = document.getElementById('municipality')?.value;
        const barangay = document.getElementById('barangay')?.value;

        let errorMessage = '';

        if (!name) {
            setErrorState('cust_name');
            errorMessage = 'Please enter your full name.';
        } else if (!email) {
            setErrorState('cust_email');
            errorMessage = 'Please enter your email address.';
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            setErrorState('cust_email');
            errorMessage = 'Please enter a valid email address.';
        } else if (!phone) {
            setErrorState('cust_phone');
            errorMessage = 'Please enter your contact number.';
        } else if (!/^\+639\d{9}$/.test(phone)) {
            setErrorState('cust_phone');
            errorMessage = 'Phone must be in format: +639XXXXXXXXX';
        } else if (!house) {
            setErrorState('house_street');
            errorMessage = 'Please enter your house number and street.';
        } else if (!province) {
            setErrorState('province');
            errorMessage = 'Please select a province.';
        } else if (!municipality) {
            setErrorState('municipality');
            errorMessage = 'Please select a city/municipality.';
        } else if (!barangay) {
            setErrorState('barangay');
            errorMessage = 'Please select a barangay.';
        } else if (!address) {
            setErrorState('cust_address');
            errorMessage = 'Please complete your delivery address.';
        }

        if (errorMessage) {
            showNotificationModal('error', errorMessage);
            return;
        }

        console.log('✅ Validation passed!');
        goToStep(2);
        renderCheckoutSummary();
        updatePaymentDisplay();
    }

    function setErrorState(inputId) {
        const input = document.getElementById(inputId);
        if (input) {
            input.classList.add('is-invalid');
            input.style.borderColor = '#dc3545';
            input.style.boxShadow = '0 0 0 0.2rem rgba(220, 53, 69, 0.25)';
        }
    }

    function clearErrorStates() {
        const inputs = ['cust_name', 'cust_email', 'cust_phone', 'cust_address', 'house_street', 'province', 'municipality', 'barangay'];
        inputs.forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.classList.remove('is-invalid');
                input.style.borderColor = '';
                input.style.boxShadow = '';
            }
        });
    }

    // ============================================
    // 5. INSTAPAY PAYMENT INTEGRATION
    // ============================================

    // Delivery fee calculation based on location
    function calculateDeliveryFee() {
        const address = document.getElementById('cust_address')?.value.toLowerCase() || '';

        // Metro Manila / Nearby Areas (1-30km)
        if (address.includes('manila') || address.includes('quezon') || address.includes('caloocan') ||
            address.includes('pasig') || address.includes('makati') || address.includes('taguig') ||
            address.includes('pasay') || address.includes('parañaque') || address.includes('muntinlupa') ||
            address.includes('las piñas') || address.includes('valenzuela') || address.includes('malabon') ||
            address.includes('navotas') || address.includes('marikina') || address.includes('san juan') ||
            address.includes('mandaluyong') || address.includes('pateros')) {

            // For now, return mid-range since we can't determine exact distance
            return 2500; // 6-10km default for Metro Manila
        }

        // South Luzon
        if (address.includes('cavite')) return 4200;
        if (address.includes('laguna')) return 6000;
        if (address.includes('batangas')) return 8500;
        if (address.includes('rizal')) return 7000;

        // North Luzon
        if (address.includes('bulacan')) return 7000;
        if (address.includes('pampanga')) return 10000;
        if (address.includes('tarlac')) return 10000;

        // Visayas & Mindanao - varies
        if (address.includes('cebu') || address.includes('davao') || address.includes('iloilo') ||
            address.includes('bacolod') || address.includes('cagayan de oro') || address.includes('zamboanga')) {
            return 0; // Will vary, show "Contact us"
        }

        // Default - nearby areas
        return 2000; // 1-5km default
    }

    // Check if cart contains Grid-tie or Hybrid products
    function hasGridTieOrHybridProduct() {
        if (!cart || cart.length === 0) return false;

        // We need to check the actual product data
        // For now, we'll check if any product has these keywords in the name
        return cart.some(item => {
            const name = (item.displayName || '').toLowerCase();
            const brand = (item.brandName || '').toLowerCase();
            return brand.includes('grid-tie') || brand.includes('hybrid') ||
                name.includes('grid-tie') || name.includes('hybrid');
        });
    }

    function updatePaymentDisplay() {
        console.log('💳 Updating payment display...');

        const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked')?.value || 'full';

        // Calculate delivery fee
        const deliveryFee = calculateDeliveryFee();
        const deliveryFeeDisplay = document.getElementById('deliveryFeeDisplay');
        if (deliveryFeeDisplay) {
            if (deliveryFee === 0) {
                deliveryFeeDisplay.innerHTML = '<span class="text-info">Contact us</span>';
            } else {
                deliveryFeeDisplay.textContent = '₱' + deliveryFee.toLocaleString(undefined, {
                    minimumFractionDigits: 2
                });
            }
        }

        // Calculate installation fee (only for Grid-tie or Hybrid)
        const installationFee = hasGridTieOrHybridProduct() ? 2000 : 0;
        const installationFeeDisplay = document.getElementById('installationFeeDisplay');
        if (installationFeeDisplay) {
            if (installationFee === 0) {
                installationFeeDisplay.innerHTML = '<span class="text-success">FREE</span>';
            } else {
                installationFeeDisplay.textContent = '₱' + installationFee.toLocaleString(undefined, {
                    minimumFractionDigits: 2
                });
            }
        }

        // Calculate base total from cart
        let cartTotal = 0;
        if (cart && cart.length > 0) {
            cartTotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        }

        // Add installation and delivery fees to total
        const totalAmount = cartTotal + installationFee + deliveryFee;
        window.currentTotalAmount = totalAmount;

        const amountToPayDisplay = document.getElementById('amountToPay');
        const paymentNote = document.getElementById('paymentNote');
        const confirmBtn = document.getElementById('confirmPaymentBtn');

        if (paymentMethod === 'full') {
            amountToPayDisplay.textContent = '₱' + totalAmount.toLocaleString(undefined, {
                minimumFractionDigits: 2
            });
            paymentNote.innerHTML = '<i class="fas fa-info-circle"></i> You are paying the <strong>Full Amount (100%)</strong> via InstaPay.';
            paymentNote.className = 'alert alert-success';
        } else if (paymentMethod === 'downpayment') {
            const downpayment = totalAmount * 0.5;
            amountToPayDisplay.textContent = '₱' + downpayment.toLocaleString(undefined, {
                minimumFractionDigits: 2
            });
            paymentNote.innerHTML = '<i class="fas fa-info-circle"></i> You are paying <strong>50% Down Payment</strong> via InstaPay. Remaining 50% before delivery.';
            paymentNote.className = 'alert alert-warning';
        } else if (paymentMethod === 'initial') {
            const initialPayment = totalAmount * 0.2;
            const remaining = totalAmount - initialPayment;
            amountToPayDisplay.textContent = '₱' + initialPayment.toLocaleString(undefined, {
                minimumFractionDigits: 2
            });
            paymentNote.innerHTML = `
            <i class="fas fa-info-circle"></i> 
            You are paying <strong>20% Initial Payment</strong> (₱${initialPayment.toLocaleString(undefined, { minimumFractionDigits: 2 })}) via InstaPay.<br>
            <small class="text-muted">Remaining balance: ₱${remaining.toLocaleString(undefined, { minimumFractionDigits: 2 })} (80% - to be paid before installation)</small>
        `;
            paymentNote.className = 'alert alert-info';
        }

        // Update checkout total display
        const checkoutTotal = document.getElementById('checkoutTotal');
        if (checkoutTotal) {
            checkoutTotal.textContent = '₱' + totalAmount.toLocaleString(undefined, {
                minimumFractionDigits: 2
            });
        }
    }

    function getCartItems() {
        const items = [];

        if (cart && cart.length > 0) {
            cart.forEach(item => {
                items.push({
                    name: item.displayName || 'Solar Product',
                    price: parseFloat(item.price) || 0,
                    quantity: parseInt(item.quantity) || 1
                });
            });
        }

        return items;
    }

    function previewReceipt(input) {
        const container = document.getElementById('receiptPreviewContainer');
        const previewImg = document.getElementById('receiptPreviewImg');
        const fileNameEl = document.getElementById('receiptFileName');

        if (input.files && input.files[0]) {
            const file = input.files[0];
            fileNameEl.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';

            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewImg.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                previewImg.style.display = 'none';
            }
            container.style.display = 'block';
        } else {
            container.style.display = 'none';
        }
    }

    function confirmInstapayOrder() {
        console.log('💵 Confirming InstaPay order...');

        const custName = document.getElementById('cust_name')?.value.trim();
        const custEmail = document.getElementById('cust_email')?.value.trim();
        const custPhone = document.getElementById('cust_phone')?.value.trim();
        const custAddress = document.getElementById('cust_address')?.value.trim();
        const receiptFile = document.getElementById('receiptUpload')?.files[0];

        if (!custName || !custEmail || !custPhone || !custAddress) {
            showNotificationModal('error', 'Please complete all required customer details.');
            return;
        }

        if (cart.length === 0) {
            showNotificationModal('error', 'Your cart is empty.');
            return;
        }

        if (!receiptFile) {
            showNotificationModal('error', 'Please upload your transaction receipt before submitting.');
            document.getElementById('receiptUpload')?.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            return;
        }

        // Validate file size (max 5MB)
        if (receiptFile.size > 5 * 1024 * 1024) {
            showNotificationModal('error', 'Receipt file is too large. Maximum size is 5MB.');
            return;
        }

        const confirmBtn = document.getElementById('confirmPaymentBtn');
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting Order...';

        const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked')?.value || 'full';
        let paymentPercentage = '100%';
        if (paymentMethod === 'downpayment') paymentPercentage = '50%';
        if (paymentMethod === 'initial') paymentPercentage = '20%';

        const totalAmount = window.currentTotalAmount || 0;
        let amountPaid = totalAmount;
        if (paymentMethod === 'downpayment') amountPaid = totalAmount * 0.5;
        if (paymentMethod === 'initial') amountPaid = totalAmount * 0.2;

        // Build FormData so we can include the file
        const formData = new FormData();
        formData.append('customerName', custName);
        formData.append('customerEmail', custEmail);
        formData.append('customerPhone', custPhone);
        formData.append('customerAddress', custAddress);
        formData.append('paymentType', paymentMethod);
        formData.append('paymentMethod', 'instapay');
        formData.append('amountPaid', amountPaid);
        formData.append('totalAmount', totalAmount);
        formData.append('deliveryFee', calculateDeliveryFee());
        formData.append('installationFee', hasGridTieOrHybridProduct() ? 2000 : 0);
        formData.append('items', JSON.stringify(getCartItems()));
        formData.append('receipt', receiptFile);

        fetch('controllers/ordering/create-instapay-order.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Capture raw text first so we can debug if it is not JSON
                return response.text().then(text => {
                    console.log('Raw server response:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        // Server returned non-JSON (PHP error page, 404, etc.)
                        throw new Error('Server returned invalid response: ' + text.substring(0, 300));
                    }
                });
            })
            .then(data => {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Confirm &amp; Submit Order';

                if (data.success) {
                    console.log('InstaPay order saved:', data.orderRef);
                    const orderRef = data.orderRef || 'ORD-INSTAPAY-' + Date.now();
                    displayOrderConfirmation(orderRef);
                    clearCart();
                    showNotificationModal('success', 'Order submitted successfully! We will verify your payment soon.');
                } else {
                    console.error('Order failed:', data.message);
                    showNotificationModal('error', data.message || 'Failed to submit order. Please try again.');
                }
            })
            .catch(error => {
                console.error(' InstaPay order error:', error.message);
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Confirm &amp; Submit Order';
                // Show the actual server error message so it is easy to debug
                const userMsg = error.message.includes('Server returned') ?
                    'Server error — please check browser console (F12) for details.' :
                    error.message;
                showNotificationModal('error', userMsg);
            });
    }

    // Old Maya payment function - now replaced by InstaPay
    function payWithMaya(paymentType) {
        console.log(' Processing Maya payment...');

        // Validate customer info
        const custName = document.getElementById('cust_name')?.value.trim();
        const custEmail = document.getElementById('cust_email')?.value.trim();
        const custPhone = document.getElementById('cust_phone')?.value.trim();
        const custAddress = document.getElementById('cust_address')?.value.trim();

        if (!custName || !custEmail || !custPhone || !custAddress) {
            showNotificationModal('error', 'Please complete all required fields.');
            return;
        }

        if (cart.length === 0) {
            showNotificationModal('error', 'Your cart is empty.');
            return;
        }

        // Show loading
        const confirmBtn = document.getElementById('confirmPaymentBtn');
        const originalText = confirmBtn.textContent;
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

        // Calculate amount
        const totalAmount = window.currentTotalAmount || 0;
        let amountToPay = totalAmount;

        if (paymentType === 'downpayment') {
            amountToPay = totalAmount * 0.5;
        }

        // Prepare order data
        const orderData = {
            customerName: custName,
            customerEmail: custEmail,
            customerPhone: custPhone,
            customerAddress: custAddress,
            paymentType: paymentType,
            amountToPay: amountToPay,
            totalAmount: totalAmount,
            items: getCartItems()
        };

        console.log('Sending to Maya API:', orderData);

        // Call backend to create Maya payment
        fetch('process_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(orderData)
            })
            .then(response => response.json())
            .then(data => {
                confirmBtn.disabled = false;
                confirmBtn.textContent = originalText;

                if (data.success) {
                    console.log('✅ Maya payment created:', data.orderRef);

                    // Store order reference
                    sessionStorage.setItem('currentOrderRef', data.orderRef);

                    // Redirect to Maya payment (amount is LOCKED by API)
                    console.log('🔗 Redirecting to Maya:', data.paymentUrl);
                    window.location.href = data.paymentUrl;

                } else {
                    console.error('❌ Payment creation failed:', data.error);
                    showNotificationModal('error', data.error || 'Failed to create payment. Please try again.');
                }
            })
            .catch(error => {
                console.error('❌ Payment error:', error);
                confirmBtn.disabled = false;
                confirmBtn.textContent = originalText;
                showNotificationModal('error', 'An error occurred. Please try again.');
            });
    }

    function confirmCODOrder() {
        console.log('💵 Processing COD order...');

        const custName = document.getElementById('cust_name')?.value.trim();
        const custEmail = document.getElementById('cust_email')?.value.trim();
        const custPhone = document.getElementById('cust_phone')?.value.trim();
        const custAddress = document.getElementById('cust_address')?.value.trim();

        if (!custName || !custEmail || !custPhone || !custAddress) {
            showNotificationModal('error', 'Please complete all required fields.');
            return;
        }

        if (cart.length === 0) {
            showNotificationModal('error', 'Your cart is empty.');
            return;
        }

        const confirmed = confirm('Confirm Cash on Delivery order?\n\nYou will pay when order arrives.');
        if (!confirmed) return;

        const confirmBtn = document.getElementById('confirmPaymentBtn');
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

        const totalAmount = window.currentTotalAmount || 0;

        const orderData = {
            customerName: custName,
            customerEmail: custEmail,
            customerPhone: custPhone,
            customerAddress: custAddress,
            paymentType: 'cod',
            amountToPay: 0,
            totalAmount: totalAmount,
            items: getCartItems(),
            paymentMethod: 'cod'
        };

        fetch('controllers/ordering/create-cod-order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(orderData)
            })
            .then(response => response.json())
            .then(data => {
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Confirm COD Order';

                if (data.success) {
                    console.log('✅ COD order placed:', data.orderRef);
                    const orderRef = data.orderRef || 'ORD-COD-' + Date.now();
                    displayOrderConfirmation(orderRef);
                    clearCart();
                    showNotificationModal('success', 'Order placed successfully!');
                } else {
                    console.error('❌ COD order failed:', data.message);
                    showNotificationModal('error', data.message || 'Failed to place order.');
                }
            })
            .catch(error => {
                console.error('❌ COD error:', error);
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Confirm COD Order';
                showNotificationModal('error', 'An error occurred. Please try again.');
            });
    }

    function displayOrderConfirmation(orderRef) {
        console.log('🎉 Displaying order confirmation:', orderRef);

        // Update confirmation step details
        document.getElementById('confOrderRef').textContent = orderRef;
        document.getElementById('confCustomerName').textContent = document.getElementById('cust_name').value;
        document.getElementById('confTotalAmount').textContent = '₱' + (window.currentTotalAmount || 0).toLocaleString(undefined, {
            minimumFractionDigits: 2
        });

        // Switch to step 3
        goToStep(3);
    }

    function copyOrderRef() {
        const orderRef = document.getElementById('confOrderRef')?.textContent;
        if (orderRef) {
            navigator.clipboard.writeText(orderRef).then(() => {
                showNotificationModal('success', '✅ Order reference copied to clipboard!');
            }).catch(() => {
                // Fallback for older browsers
                const el = document.createElement('textarea');
                el.value = orderRef;
                document.body.appendChild(el);
                el.select();
                document.execCommand('copy');
                document.body.removeChild(el);
                showNotificationModal('success', '✅ Order reference copied!');
            });
        }
    }

    function copyToClipboard(text, el) {
        navigator.clipboard.writeText(text).then(() => {
            const orig = el.textContent;
            el.textContent = 'Copied!';
            setTimeout(() => {
                el.textContent = orig;
            }, 1500);
        }).catch(() => {
            const ta = document.createElement('textarea');
            ta.value = text;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
        });
    }

    // ============================================
    // 6. SAVINGS CALCULATOR LOGIC
    // ============================================

    function setupCalculator() {
        const billInput = document.getElementById('billAmount');

        if (billInput) {
            billInput.addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    calculateSavings();
                }
            });
        }
    }

    function expandCalculator() {
        const calculatorBox = document.getElementById('calculatorBox');
        if (calculatorBox) {
            calculatorBox.classList.remove('collapsed');
            calculatorBox.classList.add('expanded');
        }
    }

    function shrinkCalculatorIfEmpty() {
        const billInput = document.getElementById('billAmount');
        const calculatorBox = document.getElementById('calculatorBox');
        const results = document.getElementById('results');

        if (calculatorBox && billInput && !billInput.value && !results.classList.contains('show')) {
            setTimeout(() => {
                calculatorBox.classList.remove('expanded');
                calculatorBox.classList.add('collapsed');
            }, 200);
        }
    }

    function calculateSavings() {
        const billAmount = parseFloat(document.getElementById('billAmount').value);
        const errorMessage = document.getElementById('errorMessage');
        const results = document.getElementById('results');
        const calculatorBox = document.getElementById('calculatorBox');

        if (!billAmount || billAmount <= 0) {
            errorMessage.textContent = 'Please enter a valid electric bill amount';
            results.classList.remove('show');
            return;
        }

        errorMessage.textContent = '';

        if (calculatorBox) {
            calculatorBox.classList.remove('collapsed');
            calculatorBox.classList.add('expanded');
        }

        const avgRate = 14.50;
        const monthlyConsumption = billAmount / avgRate;
        const dailyConsumption = monthlyConsumption / 30;
        const sunHours = 4.5;
        const systemEfficiency = 0.85;
        const panelWattage = 610;
        const savingsPercentage = 0.95;

        const requiredKwp = dailyConsumption / (sunHours * systemEfficiency);
        const numberOfPanels = Math.ceil((requiredKwp * 1000) / panelWattage);
        const monthlySavings = billAmount * savingsPercentage;
        const yearlySavings = monthlySavings * 12;

        setTimeout(() => {
            document.getElementById('kwpValue').textContent = requiredKwp.toFixed(1);
            document.getElementById('panelsValue').textContent = numberOfPanels;
            document.getElementById('monthlySavings').textContent = '₱' + monthlySavings.toLocaleString('en-PH', {
                maximumFractionDigits: 0
            });
            document.getElementById('yearlySavings').textContent = '₱' + yearlySavings.toLocaleString('en-PH', {
                maximumFractionDigits: 0
            });

            results.classList.add('show');
        }, 100);
    }

    document.addEventListener('DOMContentLoaded', function() {
        setupCalculator();

        const calculatorBox = document.getElementById('calculatorBox');
        if (calculatorBox) {
            calculatorBox.classList.add('collapsed');
        }

        // Add click handler for bulb icon with wiggle animation
        const bulbIcon = document.querySelector('.savings-icon');
        if (bulbIcon) {
            bulbIcon.addEventListener('click', function() {
                // Trigger wiggle animation
                this.style.animation = 'none';
                setTimeout(() => {
                    this.style.animation = '';
                }, 10);

                // Expand calculator if collapsed
                const billInput = document.getElementById('billAmount');
                if (calculatorBox && calculatorBox.classList.contains('collapsed')) {
                    expandCalculator();
                    if (billInput) {
                        setTimeout(() => billInput.focus(), 300);
                    }
                }
            });
        }
    });
    // ============================================
    // 7. FILTERS & SEARCH
    // ============================================
    function initializeFilters() {
        const filterButtons = document.querySelectorAll('.filter-btn');

        filterButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                filterButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                const filterValue = this.getAttribute('data-filter');
                filterProducts(filterValue);
            });
        });
    }

    function filterProducts(filterValue) {
        const productsGrid = document.getElementById('productsGrid');
        const products = document.querySelectorAll('.product-card');
        const viewMoreContainer = document.getElementById('viewMoreContainer');
        const viewMoreBtn = document.getElementById('viewMoreBtn');

        let visibleCount = 0;

        products.forEach(product => {
            const productBrand = product.getAttribute('data-brand');

            if (filterValue === 'all' || productBrand === filterValue) {
                product.style.display = 'flex';
                visibleCount++;

                // Handle hidden-product class for View More functionality
                if (visibleCount > 4) {
                    product.classList.add('hidden-product');
                } else {
                    product.classList.remove('hidden-product');
                }
            } else {
                product.style.display = 'none';
                product.classList.remove('hidden-product');
            }
        });

        // Reset the grid to collapsed state
        if (productsGrid) productsGrid.classList.remove('show-all');
        if (viewMoreBtn) {
            viewMoreBtn.classList.remove('expanded');
            const btnText = viewMoreBtn.childNodes[viewMoreBtn.childNodes.length - 1];
            if (btnText) btnText.textContent = ' View More';
        }

        // Show/hide view more button based on filtered results
        if (viewMoreContainer) {
            viewMoreContainer.style.display = visibleCount > 4 ? 'block' : 'none';
        }
    }

    function initializeSort() {
        const sortSelect = document.getElementById('sortSelect');
        if (sortSelect) {
            sortSelect.addEventListener('change', function() {
                sortProducts(this.value);
            });
        }
    }

    function sortProducts(sortType) {
        const grid = document.getElementById('productsGrid');
        if (!grid) return;

        const products = Array.from(document.querySelectorAll('.product-card'));

        products.sort((a, b) => {
            switch (sortType) {
                case 'price-low':
                    return parseFloat(a.getAttribute('data-price')) - parseFloat(b.getAttribute('data-price'));
                case 'price-high':
                    return parseFloat(b.getAttribute('data-price')) - parseFloat(a.getAttribute('data-price'));
                case 'name-asc':
                    return a.getAttribute('data-name').localeCompare(b.getAttribute('data-name'));
                case 'name-desc':
                    return b.getAttribute('data-name').localeCompare(a.getAttribute('data-name'));
                default:
                    return 0;
            }
        });

        products.forEach(product => grid.appendChild(product));
    }

    // ============================================
    // 8. UTILS & NOTIFICATIONS
    // ============================================

    function showNotificationModal(type, message) {
        // Check if simple Toast exists, if not, use alert
        const toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            alert(message);
            return;
        }

        const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0 show" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;

        toastContainer.innerHTML = toastHtml;
        setTimeout(() => {
            toastContainer.innerHTML = '';
        }, 4000);
    }

    function initializeSubscription() {
        const form = document.getElementById('subscribeForm');
        if (!form) return;

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const email = form.querySelector('input[type="email"]').value;
            showNotificationModal('success', `Salamat! ${email} has been subscribed to our newsletter.`);
            form.reset();
        });
    }

    function initializeContactForm() {
        // handled by submitContactForm() called via onsubmit on the form
    }

    async function submitContactForm(event) {
        event.preventDefault();

        const form = document.getElementById('contactForm');
        const submitBtn = document.getElementById('contactSubmitBtn');
        const btnText = submitBtn.querySelector('.btn-text');
        const btnSpinner = submitBtn.querySelector('.btn-spinner');

        // Combine +639 prefix with phone digits
        const phoneInput = document.getElementById('contact_phone');
        const phoneFullInput = document.getElementById('contact_phone_full');
        if (phoneFullInput && phoneInput) {
            phoneFullInput.value = '+63' + phoneInput.value;
            phoneInput.name = '';
        }

        // Show loading state
        btnText.classList.add('d-none');
        btnSpinner.classList.remove('d-none');
        submitBtn.disabled = true;

        try {
            const formData = new FormData(form);

            const response = await fetch('controllers/contact_submit.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                form.reset();
                // Show success modal if it exists, otherwise fallback notification
                const successModal = document.getElementById('contactSuccessModal');
                if (successModal) {
                    const modal = new bootstrap.Modal(successModal);
                    modal.show();
                } else {
                    showNotificationModal('success', 'Message sent! We will get back to you soon.');
                }
            } else {
                showNotificationModal('error', result.message || 'Failed to send message. Please try again.');
            }
        } catch (err) {
            console.error('Contact form error:', err);
            showNotificationModal('error', 'There was an error submitting your message. Please try again or contact us directly at solar@solarpower.com.ph');
        } finally {
            // Restore button state
            btnText.classList.remove('d-none');
            btnSpinner.classList.add('d-none');
            submitBtn.disabled = false;
        }
    }

    document.getElementById('roofTypeSelect').addEventListener('change', function() {
        const other = document.getElementById('roofOtherInput');
        if (this.value === 'Other') {
            other.classList.remove('d-none');
            other.setAttribute('required', 'required');
        } else {
            other.classList.add('d-none');
            other.removeAttribute('required');
            other.value = '';
        }
    });


    function initializeInspectionForm() {
        const inspectionForm = document.getElementById('inspectionForm');

        if (!inspectionForm) return;

        inspectionForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('inspectionBtn');
            const btnText = submitBtn.querySelector('.btn-text');
            const spinner = submitBtn.querySelector('.spinner-border');

            // Show loading state
            btnText.classList.add('d-none');
            spinner.classList.remove('d-none');
            submitBtn.disabled = true;

            const phoneInput = inspectionForm.querySelector('input[name="phone"]');
            const phoneFullInput = inspectionForm.querySelector('.insp-phone-full');
            if (phoneFullInput && phoneInput) {
                phoneFullInput.value = '+639' + phoneInput.value;
            }

            try {
                const formData = new FormData(inspectionForm);
                formData.append('action', 'submit_estimate');

                console.log('📧 Saving lead and sending email notification via PHP/Resend...');

                const phpResponse = await fetch('index.php', {
                    method: 'POST',
                    body: formData
                });

                const phpResult = await phpResponse.json();

                if (phpResult.success) {
                    console.log('✅ Lead saved and email sent successfully');
                    showSuccessAndReset();
                } else {
                    throw new Error(phpResult.message || 'Unknown database/email error');
                }

            } catch (error) {
                console.error('❌ Submission failed:', error);

                // Reset button loading state
                btnText.classList.remove('d-none');
                spinner.classList.add('d-none');
                submitBtn.disabled = false;

                alert('There was an error submitting your request: ' + error.message);
            }
        });
    }

    function showSuccessAndReset() {
        const inspectionForm = document.getElementById('inspectionForm');
        const submitBtn = document.getElementById('inspectionBtn');
        const btnText = submitBtn.querySelector('.btn-text');
        const spinner = submitBtn.querySelector('.spinner-border');

        // Reset button
        btnText.classList.remove('d-none');
        spinner.classList.add('d-none');
        submitBtn.disabled = false;

        // Reset form
        inspectionForm.reset();

        // Close inspection modal
        const inspectionModal = bootstrap.Modal.getInstance(document.getElementById('inspectionModal'));
        if (inspectionModal) {
            inspectionModal.hide();
        }

        // Show success modal
        const successModal = new bootstrap.Modal(document.getElementById('inspectionSuccessModal'));
        successModal.show();
    }

    // Initialize when page loads
    document.addEventListener('DOMContentLoaded', function() {
        initializeInspectionForm();
    });

    // ===========================
    // UTILITIES
    // ===========================
    function toggleHours() {
        const hoursContent = document.getElementById('hours-content');
        const hoursIcon = document.getElementById('hours-icon');

        if (hoursContent.style.maxHeight) {
            hoursContent.style.maxHeight = null;
            hoursIcon.style.transform = 'rotate(0deg)';
        } else {
            hoursContent.style.maxHeight = hoursContent.scrollHeight + 'px';
            hoursIcon.style.transform = 'rotate(180deg)';
        }
    }

    // ============================================
    // 13. RENT TO OWN FORM
    // ============================================
    function initializeRentToOwnForm() {
        const rtoForm = document.getElementById('rentToOwnForm');

        if (!rtoForm) return;

        rtoForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = rtoForm.querySelector('.btn-submit-rto');
            const btnText = submitBtn.querySelector('.btn-text');
            const spinner = submitBtn.querySelector('.spinner-border');

            // Show loading state
            btnText.classList.add('d-none');
            spinner.classList.remove('d-none');
            submitBtn.disabled = true;

            try {
                const formData = new FormData(rtoForm);

                // Add FormSubmit config
                formData.append('_subject', '🏭 New Rent-to-Own Application (Industrial/Commercial)');
                formData.append('_captcha', 'false');
                formData.append('_template', 'box');

                const response = await fetch('https://formsubmit.co/solar@solarpower.com.ph', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    // Success
                    showNotificationModal('success', 'Application submitted successfully! We will contact you soon.');
                    rtoForm.reset();
                } else {
                    throw new Error('Submission failed');
                }

            } catch (error) {
                console.error('Error submitting form:', error);
                showNotificationModal('error', 'There was an error submitting your application. Please try again or contact us directly.');
            } finally {
                // Reset button state
                btnText.classList.remove('d-none');
                spinner.classList.add('d-none');
                submitBtn.disabled = false;
            }
        });
    }

    // Initialize Rent to Own form when page loads
    document.addEventListener('DOMContentLoaded', function() {
        initializeRentToOwnForm();
    });

    // ========== MOBILE-OPTIMIZED VIEW MORE FUNCTIONALITY ==========

    /**
     * Enhanced toggle function for View More button
     * Shows first 4 products on load, then toggles all products
     */
    function toggleViewMore() {
        const productsGrid = document.getElementById('productsGrid');
        const viewMoreBtn = document.getElementById('viewMoreBtn');
        const btnIcon = viewMoreBtn.querySelector('i');
        const btnText = viewMoreBtn.childNodes[viewMoreBtn.childNodes.length - 1];

        // Toggle the show-all class
        productsGrid.classList.toggle('show-all');

        // Update button appearance and text
        if (productsGrid.classList.contains('show-all')) {
            viewMoreBtn.classList.add('expanded');
            btnText.textContent = ' View Less';

            // Scroll smoothly to show newly revealed products
            setTimeout(() => {
                const firstHiddenProduct = document.querySelector('.hidden-product');
                if (firstHiddenProduct) {
                    firstHiddenProduct.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest'
                    });
                }
            }, 100);
        } else {
            viewMoreBtn.classList.remove('expanded');
            btnText.textContent = ' View More';

            // Scroll back to the beginning of the grid
            const catalogSection = document.querySelector('.catalogs-section');
            if (catalogSection) {
                catalogSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    }

    /**
     * Initialize the view more functionality on page load
     */
    document.addEventListener('DOMContentLoaded', function() {
        const productsGrid = document.getElementById('productsGrid');
        const viewMoreContainer = document.getElementById('viewMoreContainer');
        const allProducts = document.querySelectorAll('.product-card');

        // Count visible vs hidden products
        const hiddenProducts = document.querySelectorAll('.hidden-product');

        // Hide the "View More" button if there are 4 or fewer products total
        if (allProducts.length <= 4) {
            if (viewMoreContainer) {
                viewMoreContainer.style.display = 'none';
            }
        } else {
            if (viewMoreContainer) {
                viewMoreContainer.style.display = 'block';
            }
        }

        // Log for debugging
        console.log(`Total products: ${allProducts.length}`);
        console.log(`Hidden products: ${hiddenProducts.length}`);
    });



    /**
     * Optional: Smooth fade-in animation for products when they appear
     */
    function animateProductReveal() {
        const hiddenProducts = document.querySelectorAll('.products-grid.show-all .hidden-product');

        hiddenProducts.forEach((product, index) => {
            setTimeout(() => {
                product.style.animationDelay = `${index * 0.1}s`;
            }, index * 50);
        });
    }
</script>

<!-- Toast Notification Container -->
<div id="toast-container" style="position:fixed; top:20px; right:20px; z-index:99999; min-width:300px;"></div>

<!-- ====================================================
     FLOATING TRACK ORDER BUTTON + PANEL
     Ilagay sa taas ng floating chat button
     ==================================================== -->

<style>
    /* ── Floating Button ── */
    .float-track-btn {
        position: fixed;
        bottom: 150px;
        /* taas ng ibang floating btn — i-adjust kung kailangan */
        right: 20px;
        z-index: 9990;
        width: 54px;
        height: 54px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f39c12, #e67e22);
        color: white;
        border: none;
        box-shadow: 0 4px 15px rgba(243, 156, 18, 0.5);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .float-track-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(243, 156, 18, 0.65);
    }

    .float-track-btn .track-tooltip {
        position: absolute;
        right: 62px;
        background: #2c3e50;
        color: #fff;
        font-size: 12px;
        font-weight: 600;
        padding: 5px 10px;
        border-radius: 6px;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s;
    }

    .float-track-btn:hover .track-tooltip {
        opacity: 1;
    }

    /* ── Slide-up Panel ── */
    .track-panel {
        position: fixed;
        bottom: 215px;
        /* taas ng button */
        right: 20px;
        width: 370px;
        max-width: calc(100vw - 30px);
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.18);
        z-index: 9991;
        overflow: hidden;
        transform: translateY(20px) scale(0.97);
        opacity: 0;
        pointer-events: none;
        transition: transform 0.3s ease, opacity 0.3s ease;
    }

    .track-panel.open {
        transform: translateY(0) scale(1);
        opacity: 1;
        pointer-events: all;
    }

    /* Panel Header */
    .track-panel-header {
        background: linear-gradient(135deg, #2d5016, #3d6b1e);
        color: white;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .track-panel-header h6 {
        margin: 0;
        font-weight: 700;
        font-size: 15px;
    }

    .track-panel-header .close-panel {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: background 0.2s;
    }

    .track-panel-header .close-panel:hover {
        background: rgba(255, 255, 255, 0.35);
    }

    /* Panel Body */
    .track-panel-body {
        padding: 20px;
    }

    /* Search Input */
    .track-input-wrap {
        position: relative;
        margin-bottom: 12px;
    }

    .track-input-wrap i {
        position: absolute;
        left: 13px;
        top: 50%;
        transform: translateY(-50%);
        color: #aaa;
        font-size: 14px;
    }

    .track-input-wrap input {
        width: 100%;
        padding: 11px 12px 11px 36px;
        border: 2px solid #eee;
        border-radius: 10px;
        font-size: 14px;
        outline: none;
        transition: border 0.2s;
    }

    .track-input-wrap input:focus {
        border-color: #f39c12;
    }

    /* Search Button */
    .track-search-btn {
        width: 100%;
        padding: 11px;
        background: linear-gradient(135deg, #f39c12, #e67e22);
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        transition: opacity 0.2s;
    }

    .track-search-btn:hover {
        opacity: 0.9;
    }

    .track-search-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Results */
    .track-results {
        margin-top: 14px;
        max-height: 260px;
        overflow-y: auto;
    }

    .track-results::-webkit-scrollbar {
        width: 4px;
    }

    .track-results::-webkit-scrollbar-thumb {
        background: #ddd;
        border-radius: 4px;
    }

    /* Order Row */
    .track-order-row {
        border: 1px solid #f0f0f0;
        border-radius: 12px;
        padding: 14px;
        margin-bottom: 10px;
        transition: box-shadow 0.2s;
    }

    .track-order-row:hover {
        box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
    }

    .track-order-ref {
        font-size: 11px;
        color: #999;
        font-weight: 600;
    }

    .track-order-items {
        font-size: 13px;
        font-weight: 700;
        color: #2c3e50;
        margin: 4px 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .track-order-location {
        font-size: 12px;
        color: #888;
    }

    .track-status-badge {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        background: rgba(243, 156, 18, 0.12);
        color: #e67e22;
        padding: 3px 9px;
        border-radius: 20px;
    }

    .track-order-amount {
        font-size: 13px;
        font-weight: 700;
        color: #27ae60;
    }

    /* States */
    .track-empty {
        text-align: center;
        padding: 20px 0;
    }

    .track-empty img {
        width: 55px;
        opacity: 0.4;
        margin-bottom: 8px;
    }

    .track-empty p {
        color: #bbb;
        font-size: 13px;
        margin: 0;
    }

    .track-loading {
        text-align: center;
        padding: 20px;
        color: #f39c12;
    }

    /* Full details link */
    .track-full-link {
        display: block;
        text-align: center;
        margin-top: 10px;
        font-size: 12px;
        color: #f39c12;
        text-decoration: none;
        font-weight: 600;
    }

    .track-full-link:hover {
        text-decoration: underline;
    }
</style>

<!-- Floating Button Removed to avoid overlap with new float widgets -->

<!-- Slide-up Panel -->
<div class="track-panel" id="trackPanel">
    <div class="track-panel-header">
        <h6><i class="fas fa-shipping-fast me-2"></i> Track My Order</h6>
        <button class="close-panel" onclick="toggleTrackPanel()"><i class="fas fa-times"></i></button>
    </div>
    <div class="track-panel-body">
        <div class="track-input-wrap">
            <i class="fas fa-phone"></i>
            <input type="tel" id="floatTrackPhone" placeholder="e.g. +639805926760"
                onkeydown="if(event.key==='Enter') doFloatTrack()">
        </div>
        <button class="track-search-btn" id="floatTrackBtn" onclick="doFloatTrack()">
            <i class="fas fa-search me-1"></i> TRACK ORDERS
        </button>
        <div id="floatTrackResults"></div>
    </div>
</div>

<script>
    // ── Toggle panel open/close ──────────────────────────────────────────────────
    function toggleTrackPanel() {
        const panel = document.getElementById('trackPanel');
        panel.classList.toggle('open');
        if (panel.classList.contains('open')) {
            setTimeout(() => document.getElementById('floatTrackPhone').focus(), 200);
        }
    }

    // Close panel when clicking outside
    document.addEventListener('click', function(e) {
        const panel = document.getElementById('trackPanel');
        const btn = document.querySelector('.float-track-btn');
        if (!panel.contains(e.target) && !btn.contains(e.target)) {
            panel.classList.remove('open');
        }
    });

    // ── Status label map (same as track-order.php) ──────────────────────────────
    function getStatusLabel(status) {
        const map = {
            'maya_initial': 'Initial Payment',
            'maya_full': 'Full Payment',
            'down_payment': 'Down Payment',
            'pending': 'Pending',
            'confirmed': 'To Ship',
            'in_transit': 'To Receive',
            'delivered': 'Completed',
        };
        if (!status) return 'Pending';
        return map[status] || status.charAt(0).toUpperCase() + status.slice(1);
    }

    // ── Fetch orders ─────────────────────────────────────────────────────────────
    function doFloatTrack() {
        const phone = document.getElementById('floatTrackPhone').value.trim();
        const btn = document.getElementById('floatTrackBtn');
        const result = document.getElementById('floatTrackResults');

        if (!phone) {
            result.innerHTML = `<p class="text-danger small mt-2"><i class="fas fa-exclamation-circle me-1"></i>Please enter your cellphone number.</p>`;
            return;
        }

        // Loading state
        btn.disabled = true;
        btn.innerHTML = `<i class="fas fa-spinner fa-spin me-1"></i> Searching...`;
        result.innerHTML = `<div class="track-loading"><i class="fas fa-spinner fa-spin fa-lg"></i></div>`;

        fetch(`controllers/customer_track_order.php?phone=${encodeURIComponent(phone)}`)
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = `<i class="fas fa-search me-1"></i> TRACK ORDERS`;

                if (!data.success) {
                    result.innerHTML = `
                    <div class="track-empty">
                        <img src="https://cdn-icons-png.flaticon.com/512/4076/4076432.png" alt="">
                        <p>${data.message || 'No orders found.'}</p>
                    </div>`;
                    return;
                }

                const rows = data.orders.map(order => `
                <div class="track-order-row">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="track-order-ref">${order.order_reference}</span>
                        <span class="track-status-badge">${getStatusLabel(order.order_status)}</span>
                    </div>
                    <div class="track-order-items">${order.items_ordered || 'Solar Product'}</div>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <span class="track-order-location">
                            <i class="fas fa-map-marker-alt me-1"></i>${order.current_location || 'Warehouse'}
                        </span>
                        <span class="track-order-amount">₱${parseFloat(order.total_amount).toLocaleString()}</span>
                    </div>
                </div>
            `).join('');

                result.innerHTML = `
                <div class="track-results">${rows}</div>
                <a href="track-order.php" class="track-full-link">
                    <i class="fas fa-external-link-alt me-1"></i> View full order details
                </a>`;
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerHTML = `<i class="fas fa-search me-1"></i> TRACK ORDERS`;
                result.innerHTML = `<p class="text-danger small mt-2"><i class="fas fa-exclamation-circle me-1"></i>Connection error. Please try again.</p>`;
            });
    }
</script>
<!-- END FLOATING TRACK ORDER -->

</html>