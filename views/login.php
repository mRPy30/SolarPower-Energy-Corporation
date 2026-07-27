<?php
session_start();
include "../config/dbconn.php";

$msg = "";

// Handle "Remember Me" - Auto-fill email if cookie exists
$remembered_email = isset($_COOKIE['remember_user']) ? $_COOKIE['remember_user'] : "";

if (isset($_SESSION['password_reset_success'])) {
    $msg = "<div class='alert alert-success'>Password successfully reset! Please login with your new password.</div>";
    unset($_SESSION['password_reset_success']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $remember = isset($_POST['remember']);

    // Ensure status column exists (Self-healing migration for staff table)
    $conn->query("ALTER TABLE `staff` ADD COLUMN IF NOT EXISTS `status` VARCHAR(20) NOT NULL DEFAULT 'Active'");

    // Only check staff table
    $stmt = $conn->prepare("SELECT id, firstName, lastName, password, status FROM staff WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $firstName, $lastName, $db_password, $status);
        $stmt->fetch();

        if ($status === 'Inactive') {
            $msg = "<div class='alert alert-danger'>This account has been deactivated. Please contact support.</div>";
        } else if (password_verify($password, $db_password) || $password === $db_password) {
            $_SESSION["user_id"] = $id;
            $_SESSION["firstName"] = $firstName;
            $_SESSION["lastName"] = $lastName;
            $_SESSION["role"] = 'staff';

            // Save Login (Remember Me) Logic
            if ($remember) {
                setcookie("remember_user", $email, time() + (30 * 24 * 60 * 60), "/"); 
            } else {
                if (isset($_COOKIE['remember_user'])) {
                    setcookie("remember_user", "", time() - 3600, "/");
                }
            }

            header("Location: /dashboard");
            exit;
        } else {
            $msg = "<div class='alert alert-danger'>Incorrect password.</div>";
        }
    } else {
        $msg = "<div class='alert alert-danger'>Email not found.</div>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/img/icon.png">
    <title>Staff Login | Solar Power Energy</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        :root {
            --clr-primary: #ffc107;      /* Solar Gold */
            --clr-primary-hover: #e0a800;
            --clr-secondary: #0a5c3d;    /* Deep Eco Green */
            --clr-bg-soft: #f4f7f6;
            --text-dark: #2d3436;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--clr-bg-soft);
            margin: 0;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Split Screen Layout - Reversed (Form left, Video right) */
        .login-container {
            display: flex;
            width: 100%;
            height: 100%;
            position: relative;
        }

        /* Left Side: Form */
        .login-form-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: #fff;
            z-index: 10;
            box-shadow: 10px 0 30px rgba(0, 0, 0, 0.05);
        }

        @media (max-width: 991px) {
            .login-form-section {
                background: transparent;
                position: absolute;
                inset: 0;
                width: 100%;
                height: 100%;
                z-index: 10;
            }
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        @media (max-width: 991px) {
            .login-card {
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border: 1px solid rgba(255, 255, 255, 0.35);
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            }
        }

        .login-card h2 {
            font-weight: 700;
            color: var(--clr-secondary);
            margin-bottom: 8px;
        }

        .typing-greet {
            font-size: 1rem;
            color: #636e72;
            font-weight: 500;
            margin-bottom: 30px;
            border-right: 2px solid var(--clr-primary);
            display: inline-block;
            white-space: nowrap;
            overflow: hidden;
            animation: typing 2.5s steps(30, end), blink .75s step-end infinite;
        }

        /* Right Side: Visual video background */
        .login-visual {
            flex: 1.2;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px;
            color: white;
        }

        @media (max-width: 991px) {
            .login-visual {
                position: absolute;
                inset: 0;
                width: 100%;
                height: 100%;
                z-index: 1;
                padding: 20px;
            }
            .visual-content {
                display: none;
            }
        }

        .bg-video {
            position: absolute;
            top: 50%;
            left: 50%;
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            z-index: 1;
            transform: translate(-50%, -50%);
            object-fit: cover;
        }

        .login-visual::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(10, 92, 61, 0.5), rgba(10, 92, 61, 0.75));
            z-index: 2;
        }

        .visual-content {
            position: relative;
            z-index: 3;
        }

        .visual-content h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            line-height: 1.1;
        }

        .visual-content p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 500px;
        }

        /* Form Styling */
        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--text-dark);
        }

        .form-control {
            height: 50px;
            border-radius: 8px;
            border: 1px solid #dfe6e9;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--clr-secondary);
            box-shadow: 0 0 0 4px rgba(10, 92, 61, 0.1);
        }

        .btn-login {
            background: var(--clr-secondary);
            color: #fff;
            height: 50px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            border: none;
            transition: transform 0.2s ease, background 0.2s ease;
        }

        .btn-login:hover {
            background: #084830;
            color: #fff;
            transform: translateY(-1px);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* Animations */
        @keyframes typing {
            from { width: 0 }
            to { width: 100% }
        }

        @keyframes blink {
            from, to { border-color: transparent }
            50% { border-color: var(--clr-primary) }
        }

        /* Custom decorative element */
        .solar-dot {
            height: 8px;
            width: 8px;
            background-color: var(--clr-primary);
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <!-- Left Side: Form section -->
    <div class="login-form-section">
        <div class="login-card">
            <div class="mb-4 text-center">
                <a href="/">
                    <img src="../assets/img/solarpower_energy_corp.png" alt="SolarPower Logo" style="max-height: 80px; margin-bottom: 25px; width: auto; object-fit: contain;">
                </a>
                <div>
                    <div class="solar-dot"></div>
                    <h2 class="d-inline-block">Staff Login</h2>
                </div>
                <p class="typing-greet">Welcome back, Team!</p>
            </div>

            <?php echo $msg; ?>

            <form method="POST" action="/login">
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="name@solarpower.com.ph" value="<?php echo htmlspecialchars($remembered_email); ?>" required>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <label class="form-label">Password</label>
                    </div>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" 
                           style="width: 17px; height: 17px; cursor: pointer;" 
                           <?php if($remembered_email) echo "checked"; ?>>
                    <label class="form-check-label small text-secondary" for="remember" style="cursor: pointer; margin-left: 5px; padding-top: 2px;">
                        Remember Me
                    </label>
                </div>

                <button class="btn btn-login w-100" type="submit">
                    <i class="fa-solid fa-right-to-bracket me-2"></i> Sign In to Dashboard
                </button>
            </form>

            <footer class="mt-5 text-center text-muted small">
                &copy; 2026 SolarPower Energy Corporation
            </footer>
        </div>
    </div>

    <!-- Right Side: Video background visual -->
    <div class="login-visual">
        <video autoplay muted loop playsinline class="bg-video">
            <source src="../assets/login.mp4" type="video/mp4">
        </video>
        <div class="visual-content">
            <h1>Powering <br><span style="color: var(--clr-primary);">the Future.</span></h1>
            <p>Manage your solar grid and client operations through the centralized energy command center.</p>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
