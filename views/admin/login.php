<?php
session_start();
include "../../config/dbconn.php";

$msg = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = trim($_POST["email"]);
        $password = trim($_POST["password"]);

        // First check staff table
        $stmt = $conn->prepare("SELECT id, firstName, lastName, password, 'admin' as role FROM admin WHERE email=? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        // If not found in staff, check client table
        if ($stmt->num_rows == 0) {
            $stmt->close();
            $stmt = $conn->prepare("SELECT id, firstName, lastName, password, 'client' as role FROM client WHERE email=? LIMIT 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
        }

        if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $firstName, $lastName, $db_password, $role);
        $stmt->fetch();

        // STAFF: plain text password
        if ($role === 'admin') {
            $passwordMatch = ($password === $db_password);
        }

        // CLIENT: hashed password
        else {
            $passwordMatch = password_verify($password, $db_password);
        }

        if ($passwordMatch) {
            $_SESSION["user_id"] = $id;
            $_SESSION["firstName"] = $firstName;
            $_SESSION["lastName"] = $lastName;
            $_SESSION["role"] = $role;

            if ($role === 'admin') {
                header("Location: dashboard.php");
            } else {
                header("Location: client/dashboard.php");
            }
            exit;
        } else {
            $msg = "<div class='alert alert-danger'>Incorrect password.</div>";
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solar Power Energy - Admin Portal</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --clr-primary: #ffc107;      /* Solar Gold */
            --clr-primary-hover: #e0a800;
            --clr-secondary: #114b3d;    /* Deep Eco Green */
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

        /* Split Screen Layout */
        .login-container {
            display: flex;
            width: 100%;
            height: 100%;
        }

        /* Left Side: Visual/Branding */
        .login-visual {
            flex: 1.2;
            background: linear-gradient(rgba(17, 75, 61, 0.6), rgba(17, 75, 61, 0.6)), 
                        url('../assets/img/cover.png') no-repeat center center/cover;
            display: none; /* Hidden on mobile */
            flex-direction: column;
            justify-content: center;
            padding: 60px;
            color: white;
        }

        @media (min-width: 992px) {
            .login-visual { display: flex; }
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

        /* Right Side: Form */
        .login-form-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: #fff;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
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
            border-color: var(--clr-primary);
            box-shadow: 0 0 0 4px rgba(255, 193, 7, 0.1);
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
            background: #0d3d32;
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
    <div class="login-visual">
        <div class="visual-content">
            <h1>Powering <br><span style="color: var(--clr-primary);">the Future.</span></h1>
            <p>Manage your solar grid and client operations through the centralized energy command center.</p>
        </div>
    </div>

    <div class="login-form-section">
        <div class="login-card">
            <div class="mb-4">
                <div class="solar-dot"></div>
                <h2 class="d-inline-block">Admin Login</h2>
                <br>
                <p class="typing-greet">Welcome back, Manager!</p>
            </div>

            <?php echo $msg; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="name@solarcompany.com" required>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <label class="form-label">Password</label>
                    </div>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <button class="btn btn-login w-100 mt-2" type="submit">Sign In to Dashboard</button>
            </form>

            <footer class="mt-5 text-center text-muted small">
                &copy; 2025 Solar Power Energy System
            </footer>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>