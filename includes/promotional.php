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
            border-radius: 0;
        }

        .promo-card-sm img {
            width: 100%;
            height: 100%;
            display: block;
        }

        .promo-card-sm .promo-card-overlay h3 {
            font-size: 1.05rem;
        }

        .promo-card-sm .promo-card-overlay p {
            font-size: 0.8rem;
        }

        .promo-link-wrap {
            display: block;
            text-decoration: none;
            color: inherit;
            cursor: pointer;
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

        .promo-link-wrap:hover .fb-hover-badge {
            opacity: 1;
            transform: translateY(0);
        }

        @media (max-width: 767px) {
            .fb-hover-badge {
                opacity: 1;
                transform: translateY(0);
            }
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
                    <a href="https://www.facebook.com/share/p/1Lo6QbRZm6/" target="_blank" rel="noopener noreferrer" class="promo-card promo-card-main promo-link-wrap">
                        <img src="assets/img/go-solar.jpg" alt="Solar Power Solutions" class="promo-video-bg">

                        <div class="play-icon">
                            <i class="fas fa-play"></i>
                        </div>
                        <div class="promo-card-overlay"></div>
                        <div class="fb-hover-badge"><i class="fab fa-facebook"></i> View Post</div>
                    </a>
                </div>
                <div class="col-md-6 promo-right-col">
                    <a href="https://www.facebook.com/permalink.php?story_fbid=pfbid02XrvG3bZ8mQH6f6kPHqPmrmSWxeTDcR6kWmgVgo8NUWkaZ77srag41zQjFqStU4rMl&id=61578373983187"
                        target="_blank" rel="noopener noreferrer" class="promo-card promo-card-sm promo-link-wrap">
                        <img src="assets/img/installnow.jpg" alt="Grid-Tie Packages">
                        <div class="fb-hover-badge"><i class="fab fa-facebook"></i> View Post</div>
                    </a>
                    <a href="https://www.facebook.com/photo/?fbid=122169296384945799"
                        target="_blank" rel="noopener noreferrer" class="promo-card promo-card-sm promo-link-wrap">
                        <img src="assets/img/occular.jpg" alt="Hybrid Packages">
                        <div class="fb-hover-badge"><i class="fab fa-facebook"></i> View Post</div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>