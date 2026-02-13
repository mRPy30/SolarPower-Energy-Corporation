<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        .promo-section {
           padding-bottom: 60px;
            background: white;
        }

        .promo-row {
            align-items: stretch;
        }

        .promo-left {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .promo-right-col {
            display: flex;
            flex-direction: column;
        }

        .promo-card {
            border-radius: 16px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: #fff;
        }

        .promo-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.14);
        }

        .promo-card-main {
            height: 100%;
            min-height: 380px;
        }

        .promo-card-main video,
        .promo-card-main img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .promo-card-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 24px;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
            color: #fff;
        }

        .promo-card-overlay .badge-promo {
            display: inline-block;
            background: #ffc107;
            color: #333;
            font-weight: 600;
            font-size: 0.75rem;
            padding: 4px 12px;
            border-radius: 20px;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .promo-card-overlay h3 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .promo-card-overlay p {
            font-size: 0.85rem;
            margin: 0;
            opacity: 0.9;
        }

        .promo-right-col {
            gap: 20px;
        }

        .promo-card-sm {
            flex: 1;
            min-height: 175px;
            position: relative;
        }

        .promo-card-sm img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .promo-card-sm .promo-card-overlay h3 {
            font-size: 1.05rem;
        }

        .promo-card-sm .promo-card-overlay p {
            font-size: 0.8rem;
        }

        .promo-card .play-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: #0a5c3d;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
            opacity: 0;
            transition: opacity 0.3s ease, transform 0.3s ease;
            pointer-events: none;
        }

        .promo-card:hover .play-icon {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1.1);
        }

        @media (max-width: 767px) {
            .promo-row {
                flex-direction: column;
            }

            .promo-card-main {
                min-height: 260px;
            }

            .promo-card-sm {
                min-height: 180px;
            }
        }
    </style>
</head>

<body>
    <section class="promo-section">
        <div class="container">
            <div class="row g-4 promo-row">
                <div class="col-md-6 d-flex">
                    <div class="promo-card promo-card-main">
                        <video autoplay muted loop playsinline>
                            <source src="assets/img/promo-banner.webm" type="video/webm">
                            Your browser does not support the video tag.
                        </video>
                        <div class="play-icon">
                            <i class="fas fa-play"></i>
                        </div>
                        <div class="promo-card-overlay">
                            <span class="badge-promo"><i class="fas fa-bolt"></i> FEATURED</span>
                            <h3>Solar Power Solutions</h3>
                            <p>Reliable &amp; affordable energy packages for your home</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 promo-right-col">
                    <div class="promo-card promo-card-sm">
                        <img src="assets/img/grid-tie.png" alt="Grid-Tie Packages">
                        <div class="promo-card-overlay">
                            <span class="badge-promo"><i class="fas fa-solar-panel"></i> GRID-TIE</span>
                            <h3>Grid-Tie Packages</h3>
                            <p>Reduce your electricity bills with on-grid systems</p>
                        </div>
                    </div>
                    <div class="promo-card promo-card-sm">
                        <img src="assets/img/hybrid.png" alt="Hybrid Packages">
                        <div class="promo-card-overlay">
                            <span class="badge-promo"><i class="fas fa-battery-full"></i> HYBRID</span>
                            <h3>Hybrid Packages</h3>
                            <p>Battery backup with grid connectivity for 24/7 power</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>