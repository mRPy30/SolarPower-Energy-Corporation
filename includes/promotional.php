<?php
date_default_timezone_set('Asia/Manila');
$_promoDefaults = [

    'main'   => ['image' => 'assets/img/go-solar.jpg', 'link' => '', 'start' => '', 'end' => ''],
    'top'    => ['image' => 'assets/img/installnow.jpg', 'link' => '', 'start' => '', 'end' => ''],
    'bottom' => ['image' => 'assets/img/occular.jpg', 'link' => '', 'start' => '', 'end' => ''],
];
$_promoConfig = $_promoDefaults;
$_promoJsonFile = __DIR__ . '/../views/staff/includes/promo-images.json';

if (file_exists($_promoJsonFile)) {
    $_saved = json_decode(file_get_contents($_promoJsonFile), true);
    if (is_array($_saved)) {
        foreach ($_promoDefaults as $_slot => $_def) {
            if (isset($_saved[$_slot])) {
                if (is_string($_saved[$_slot])) {
                    $_promoConfig[$_slot]['image'] = $_saved[$_slot];
                } else {
                    $_promoConfig[$_slot] = array_merge($_def, $_saved[$_slot]);
                }
            }
        }
    }
}
$_cb = file_exists($_promoJsonFile) ? filemtime($_promoJsonFile) : time();

/**
 * Checks if a promotion slot is currently active based on schedule
 */
function isPromoActive($slotConfig) {
    if (empty($slotConfig['image'])) return false;
    
    $now = time();
    $start = !empty($slotConfig['start']) ? strtotime($slotConfig['start']) : 0;
    
    return ($now >= $start);
}


$_active = [
    'main'   => isPromoActive($_promoConfig['main']),
    'top'    => isPromoActive($_promoConfig['top']),
    'bottom' => isPromoActive($_promoConfig['bottom']),
];

// If no promos are active at all, we might want to hide the whole section
$_anyActive = $_active['main'] || $_active['top'] || $_active['bottom'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promotions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
         .promo-section {
            padding: 60px 0;
            background: #fff;
        }
        <?php if (!$_anyActive): ?>
        .promo-section { display: none; }
        <?php endif; ?>

        .promo-row {
            align-items: stretch;
        }

        .promo-card {
            border-radius: 16px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: #fff;
            height: 100%;
            display: block;
        }

        .promo-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.14);
        }

        .promo-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .promo-card-main {
            min-height: 400px;
        }

        .promo-right-col {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .promo-card-sm {
            flex: 1;
            min-height: 190px;
        }

        .fb-hover-badge {
            position: absolute;
            top: 14px;
            right: 14px;
            background: #1877f2;
            color: #fff;
            font-size: 0.78rem;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 5px;
            opacity: 0;
            transform: translateY(-6px);
            transition: opacity 0.25s ease, transform 0.25s ease;
            pointer-events: none;
            z-index: 10;
        }

        .promo-card:hover .fb-hover-badge {
            opacity: 1;
            transform: translateY(0);
        }

        @media (max-width: 767px) {
            .fb-hover-badge { opacity: 1; transform: translateY(0); }
            .promo-card-main { min-height: 280px; }
        }
    </style>
</head>

<body>
    <?php if ($_anyActive): ?>
    <section class="promo-section">
        <div class="container">
            <div class="row g-4 promo-row">
                
                <?php if ($_active['main']): ?>
                <div class="<?= ($_active['top'] || $_active['bottom']) ? 'col-md-6' : 'col-md-12' ?> d-flex">
                    <a href="<?= htmlspecialchars($_promoConfig['main']['link'] ?: '#') ?>" 
                       target="_blank" rel="noopener noreferrer" class="promo-card promo-card-main">
                        <img src="<?= htmlspecialchars($_promoConfig['main']['image']) ?>?v=<?= $_cb ?>" alt="Main Promo">
                        <div class="fb-hover-badge"><i class="fab fa-facebook"></i> View Post</div>
                    </a>
                </div>
                <?php endif; ?>

                <?php if ($_active['top'] || $_active['bottom']): ?>
                <div class="<?= $_active['main'] ? 'col-md-6' : 'col-md-12' ?> promo-right-col">
                    <?php if ($_active['top']): ?>
                    <a href="<?= htmlspecialchars($_promoConfig['top']['link'] ?: '#') ?>" 
                       target="_blank" rel="noopener noreferrer" class="promo-card promo-card-sm">
                        <img src="<?= htmlspecialchars($_promoConfig['top']['image']) ?>?v=<?= $_cb ?>" alt="Top Promo">
                        <div class="fb-hover-badge"><i class="fab fa-facebook"></i> View Post</div>
                    </a>
                    <?php endif; ?>

                    <?php if ($_active['bottom']): ?>
                    <a href="<?= htmlspecialchars($_promoConfig['bottom']['link'] ?: '#') ?>" 
                       target="_blank" rel="noopener noreferrer" class="promo-card promo-card-sm">
                        <img src="<?= htmlspecialchars($_promoConfig['bottom']['image']) ?>?v=<?= $_cb ?>" alt="Bottom Promo">
                        <div class="fb-hover-badge"><i class="fab fa-facebook"></i> View Post</div>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </section>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>