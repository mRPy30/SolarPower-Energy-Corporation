<?php
http_response_code(404);

require_once __DIR__ . '/includes/routes.php';

$homeUrl = clean_url('index.php');
$productsUrl = clean_url('product.php');
$contactUrl = clean_url('contact.php');
$logoUrl = asset_url('assets/img/solarpower_energy_corp.png');
$iconUrl = asset_url('assets/img/icon.png');
$backgroundUrl = asset_url('assets/img/homepage-cover.png');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found | SolarPower Energy</title>
    <link rel="icon" type="image/png" href="<?php echo htmlspecialchars($iconUrl); ?>">
    <style>
        :root {
            --forest: #0f382c;
            --green: #176b45;
            --gold: #f3b400;
            --ink: #13231d;
            --muted: #5f7069;
            --line: rgba(15, 56, 44, 0.14);
            --panel: rgba(255, 255, 255, 0.88);
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
            margin: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            color: var(--ink);
            background:
                linear-gradient(120deg, rgba(12, 39, 31, 0.94), rgba(20, 87, 57, 0.76)),
                url('<?php echo htmlspecialchars($backgroundUrl); ?>') center/cover no-repeat fixed;
        }

        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px;
            position: relative;
            overflow: hidden;
        }

        .error-page::before {
            content: '';
            position: absolute;
            inset: auto -12vw -18vw auto;
            width: 44vw;
            height: 44vw;
            min-width: 360px;
            min-height: 360px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(243, 180, 0, 0.28), rgba(243, 180, 0, 0));
            pointer-events: none;
        }

        .error-shell {
            width: min(100%, 1040px);
            display: grid;
            grid-template-columns: 0.96fr 1.04fr;
            border: 1px solid rgba(255, 255, 255, 0.42);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 28px 80px rgba(2, 19, 12, 0.34);
            backdrop-filter: blur(18px);
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        .brand-panel {
            min-height: 560px;
            padding: 44px;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background:
                linear-gradient(160deg, rgba(15, 56, 44, 0.96), rgba(23, 107, 69, 0.86)),
                url('<?php echo htmlspecialchars($backgroundUrl); ?>') center/cover no-repeat;
        }

        .brand-logo {
            width: 230px;
            max-width: 80%;
            height: auto;
            display: block;
            filter: drop-shadow(0 12px 20px rgba(0, 0, 0, 0.2));
        }

        .brand-kicker {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            width: fit-content;
            margin-top: 34px;
            padding: 9px 14px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.26);
            background: rgba(255, 255, 255, 0.12);
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0;
        }

        .brand-kicker span {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: var(--gold);
            box-shadow: 0 0 0 6px rgba(243, 180, 0, 0.18);
        }

        .brand-panel h1 {
            margin: 22px 0 14px;
            font-size: clamp(40px, 6vw, 72px);
            line-height: 0.96;
            letter-spacing: 0;
        }

        .brand-panel p {
            max-width: 420px;
            margin: 0;
            color: rgba(255, 255, 255, 0.82);
            font-size: 17px;
            line-height: 1.65;
        }

        .brand-footnote {
            display: grid;
            gap: 8px;
            color: rgba(255, 255, 255, 0.82);
            font-size: 14px;
            line-height: 1.5;
        }

        .content-panel {
            padding: 52px;
            display: flex;
            align-items: center;
            background: var(--panel);
        }

        .content-inner {
            width: 100%;
        }

        .error-code {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 108px;
            height: 48px;
            padding: 0 20px;
            border-radius: 999px;
            background: #fff7dc;
            color: #8a6300;
            border: 1px solid rgba(243, 180, 0, 0.42);
            font-size: 18px;
            font-weight: 900;
        }

        .content-panel h2 {
            margin: 24px 0 12px;
            color: var(--forest);
            font-size: clamp(34px, 4vw, 56px);
            line-height: 1.02;
            letter-spacing: 0;
        }

        .content-panel p {
            margin: 0;
            color: var(--muted);
            font-size: 17px;
            line-height: 1.7;
        }

        .action-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 30px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            padding: 0 20px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 900;
            font-size: 14px;
            transition: transform 180ms ease, box-shadow 180ms ease, background 180ms ease;
        }

        .btn-primary {
            background: var(--gold);
            color: #17211c;
            box-shadow: 0 14px 28px rgba(243, 180, 0, 0.28);
        }

        .btn-secondary {
            background: #fff;
            color: var(--forest);
            border: 1px solid var(--line);
        }

        .btn:hover,
        .btn:focus {
            transform: translateY(-2px);
            outline: none;
        }

        .btn-primary:hover,
        .btn-primary:focus {
            box-shadow: 0 18px 36px rgba(243, 180, 0, 0.34);
        }

        .help-strip {
            margin-top: 34px;
            padding: 18px;
            border-radius: 18px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.74);
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .help-strip strong {
            display: block;
            margin-bottom: 2px;
            color: var(--forest);
        }

        @media (max-width: 860px) {
            .error-page {
                padding: 18px;
                align-items: flex-start;
            }

            .error-shell {
                grid-template-columns: 1fr;
                border-radius: 20px;
            }

            .brand-panel {
                min-height: auto;
                gap: 56px;
                padding: 30px;
            }

            .content-panel {
                padding: 32px 26px;
            }

            .brand-logo {
                width: 190px;
            }
        }

        @media (max-width: 480px) {
            .error-page {
                padding: 12px;
            }

            .brand-panel,
            .content-panel {
                padding: 24px 20px;
            }

            .action-row {
                display: grid;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <main class="error-page">
        <section class="error-shell" aria-labelledby="errorTitle">
            <aside class="brand-panel">
                <div>
                    <img src="<?php echo htmlspecialchars($logoUrl); ?>" alt="SolarPower Energy Corporation" class="brand-logo">
                    <div class="brand-kicker"><span></span> SolarPower Energy Corporation</div>
                    <h1>Lost in the grid?</h1>
                    <p>The page you opened is unavailable, but your solar journey can continue from the right place.</p>
                </div>
                <div class="brand-footnote">
                    <strong>Need help?</strong>
                    <span>Our team can guide you back to products, services, solar loans, or estimates.</span>
                </div>
            </aside>

            <div class="content-panel">
                <div class="content-inner">
                    <span class="error-code">404</span>
                    <h2 id="errorTitle">Page not found</h2>
                    <p>
                        This link may have moved, expired, or been typed incorrectly. Use the button below to return to the homepage.
                    </p>

                    <div class="action-row">
                        <a href="<?php echo htmlspecialchars($homeUrl); ?>" class="btn btn-primary">Back to Home</a>
                        <a href="<?php echo htmlspecialchars($productsUrl); ?>" class="btn btn-secondary">Browse Products</a>
                        <a href="<?php echo htmlspecialchars($contactUrl); ?>" class="btn btn-secondary">Contact Us</a>
                    </div>

                    <div class="help-strip">
                        <strong>Tip for staff access</strong>
                        Use the clean login URL: <a href="<?php echo htmlspecialchars(clean_url('login.php')); ?>">Login</a>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
