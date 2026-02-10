<?php
session_start();
require_once 'email_config.php'; // PHPMailer configuration

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "solar_power";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    
    // Check if email exists in staff or client table
    $user_found = false;
    $user_role = "";
    
    // Check staff table
    $stmt = $conn->prepare("SELECT id, firstName FROM staff WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows == 1) {
        $stmt->bind_result($user_id, $firstName);
        $stmt->fetch();
        $user_found = true;
        $user_role = 'staff';
    }
    $stmt->close();
    
    // If not in staff, check client table
    if (!$user_found) {
        $stmt = $conn->prepare("SELECT id, firstName FROM client WHERE email=? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($user_id, $firstName);
            $stmt->fetch();
            $user_found = true;
            $user_role = 'client';
        }
        $stmt->close();
    }
    
    if ($user_found) {
        // Generate 6-digit verification code
        $reset_code = sprintf("%06d", mt_rand(1, 999999));
        $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        // Store reset code in database
        $stmt = $conn->prepare("INSERT INTO password_resets (email, reset_code, user_role, expiry_date) VALUES (?, ?, ?, ?) 
                               ON DUPLICATE KEY UPDATE reset_code=?, expiry_date=?, used=0");
        $stmt->bind_param("ssssss", $email, $reset_code, $user_role, $expiry, $reset_code, $expiry);
        $stmt->execute();
        $stmt->close();
        
        // Send email with reset code
        if (sendResetEmail($email, $firstName, $reset_code)) {
            $_SESSION['reset_email'] = $email;
            header("Location: verify_reset_code.php");
            exit;
        } else {
            $msg = "<div class='alert alert-danger'>Failed to send email. Please try again.</div>";
        }
    } else {
        // Don't reveal if email exists or not for security
        $msg = "<div class='alert alert-info'>If this email is registered, a reset code has been sent.</div>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Solar Power Energy</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/auth.css">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-light fixed-top">
    <div class="container">
        <a class="navbar-brand" href="../index.php">
            <img src="../assets/img/logo_no_background.png" alt="Site Logo">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a href="../index.php" class="nav-link fw-semibold">HOME</a></li>
                <li class="nav-item"><a href="../about.php" class="nav-link fw-semibold">ABOUT US</a></li>
                <li class="nav-item"><a href="../services.php" class="nav-link fw-semibold">SERVICES</a></li>
                <li class="nav-item"><a href="../product.php" class="nav-link fw-semibold">PRODUCTS</a></li>
                <li class="nav-item"><a href="#projects" class="nav-link fw-semibold">PROJECTS</a></li>
                <li class="nav-item"><a href="../contact.php" class="nav-link fw-semibold">CONTACT</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="login-bg d-flex justify-content-center align-items-center">
    <div class="login-card text-center">
        <h2 class="mb-4">Forgot Password</h2>
        <p class="text-muted mb-4">Enter your email address and we'll send you a code to reset your password.</p>

        <?php echo $msg; ?>

        <form method="POST" action="">
            <input type="email" name="email" class="form-control mb-3" placeholder="Enter your email" required>
            
            <button class="btn btn-login w-100 mb-3" type="submit">Send Reset Code</button>
            
            <a href="login.php" class="small text-decoration-none">Back to Login</a>
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>