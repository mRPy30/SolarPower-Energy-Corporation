<?php
http_response_code(404);
require_once __DIR__ . '/includes/routes.php';

$homeUrl = clean_url('index.php');
// Using the login URL for the secondary button
$loginUrl = clean_url('login.php');
$logoUrl = asset_url('assets/img/solarpower_energy_corp.png');
$iconUrl = asset_url('assets/img/icon.png');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found | SolarPower Energy</title>
    <link rel="icon" type="image/png" href="<?php echo htmlspecialchars($iconUrl); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-green: #0D5C3A;
            --brand-amber: #F2A900;
            --brand-amber-hover: #D99700;
            --text-dark: #1F2937;
            --text-gray: #4B5563;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #FFFFFF;
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header {
            padding: 24px 48px;
            width: 100%;
        }

        .brand-logo {
            height: 48px;
            width: auto;
        }

        .error-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
            position: relative;
            overflow: hidden;
        }

        .solar-bg-element {
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(242, 169, 0, 0.08) 0%, rgba(242, 169, 0, 0) 70%);
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 0;
            pointer-events: none;
            /* Simple fade in animation */
            animation: glowFadeIn 2s ease-out forwards;
        }

        @keyframes glowFadeIn {
            from { opacity: 0; transform: translate(-50%, -50%) scale(0.8); }
            to { opacity: 1; transform: translate(-50%, -50%) scale(1); }
        }

        @media (prefers-reduced-motion: reduce) {
            .solar-bg-element {
                animation: none;
                opacity: 1;
            }
        }

        .error-content {
            position: relative;
            z-index: 1;
            max-width: 640px;
            text-align: center;
        }

        .error-code {
            font-family: 'Poppins', sans-serif;
            font-size: clamp(80px, 12vw, 140px);
            font-weight: 900;
            line-height: 1;
            color: var(--brand-green);
            margin-bottom: 16px;
            text-shadow: 0 10px 30px rgba(13, 92, 58, 0.1);
        }

        .headline {
            font-family: 'Poppins', sans-serif;
            font-size: clamp(28px, 4vw, 40px);
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 16px;
            line-height: 1.2;
        }

        .subtext {
            font-size: 1.125rem;
            color: var(--text-gray);
            line-height: 1.6;
            margin-bottom: 40px;
            max-width: 540px;
            margin-left: auto;
            margin-right: auto;
        }

        .action-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            margin-bottom: 48px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 28px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s ease;
            font-family: 'Inter', sans-serif;
        }

        .btn:focus-visible {
            outline: 3px solid rgba(242, 169, 0, 0.5);
            outline-offset: 2px;
        }

        .btn-primary {
            background-color: var(--brand-amber);
            color: #FFFFFF;
            border: 2px solid var(--brand-amber);
            box-shadow: 0 4px 12px rgba(242, 169, 0, 0.25);
        }

        .btn-primary:hover {
            background-color: var(--brand-amber-hover);
            border-color: var(--brand-amber-hover);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: transparent;
            color: var(--brand-green);
            border: 2px solid var(--brand-green);
        }

        .btn-secondary:hover {
            background-color: rgba(13, 92, 58, 0.05);
            transform: translateY(-2px);
        }

        .help-section {
            padding-top: 32px;
            border-top: 1px solid #E5E7EB;
        }

        .help-heading {
            font-family: 'Poppins', sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--brand-green);
            margin-bottom: 8px;
        }

        .help-text {
            font-size: 1rem;
            color: var(--text-gray);
            line-height: 1.5;
        }

        @media (max-width: 640px) {
            .header {
                padding: 20px;
                text-align: center;
            }
            .action-buttons {
                flex-direction: column;
                gap: 12px;
            }
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="<?php echo htmlspecialchars($homeUrl); ?>">
            <img src="<?php echo htmlspecialchars($logoUrl); ?>" alt="SolarPower Energy Corporation" class="brand-logo">
        </a>
    </header>

    <main class="error-container">
        <div class="solar-bg-element"></div>
        <div class="error-content">
            <div class="error-code">404</div>
            <h1 class="headline">Lost in the grid?</h1>
            <p class="subtext">
                The page you opened is unavailable, but your solar journey can continue from the right place.
            </p>
            
            <div class="action-buttons">
                <a href="<?php echo htmlspecialchars($homeUrl); ?>" class="btn btn-primary">Go Back Home</a>
                <a href="<?php echo htmlspecialchars($loginUrl); ?>" class="btn btn-secondary">Log In Again</a>
            </div>

            <div class="help-section">
                <h3 class="help-heading">Need help?</h3>
                <p class="help-text">Our team can guide you back to products, services, solar loans, or estimates.</p>
            </div>
        </div>
    </main>
</body>
</html>
