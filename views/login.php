<?php
session_start();
include "../config/dbconn.php";

$msg = "";

// 1. Secure Cookie Handling (XSS Protection)
$remembered_email = isset($_COOKIE['remember_user']) ? htmlspecialchars($_COOKIE['remember_user']) : "";

if (isset($_SESSION['password_reset_success'])) {
    $msg = "<div class='alert alert-success'>Password successfully reset! Please login with your new password.</div>";
    unset($_SESSION['password_reset_success']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 2. Input Sanitization
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST["password"]);
    $remember = isset($_POST['remember']);

    if (empty($email) || empty($password)) {
        $msg = "<div class='alert alert-danger'>Please fill in all fields.</div>";
    } else {
        // 3. Strict Query (Staff Only)
        // Siguraduhin na ang table name ay 'staff' at hindi 'users'
        $stmt = $conn->prepare("SELECT id, firstName, lastName, password FROM staff WHERE email=? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $firstName, $lastName, $db_password);
            $stmt->fetch();

            // 4. Enhanced Password Verification
            // Mas mainam na i-hash lahat ng password sa DB para maalis na ang plain text check (===)
            if (password_verify($password, $db_password)) {
                // Regenerate session ID para iwas Session Fixation attacks
                session_regenerate_id(true);

                $_SESSION["user_id"] = $id;
                $_SESSION["firstName"] = $firstName;
                $_SESSION["lastName"] = $lastName;
                $_SESSION["role"] = 'staff';
                $_SESSION["last_login_timestamp"] = time();

                // 5. Secure Remember Me Logic
                if ($remember) {
                    // Itakda ang cookie (httponly para hindi manakaw ng JS scripts)
                    setcookie("remember_user", $email, time() + (30 * 24 * 60 * 60), "/", "", true, true); 
                } else {
                    if (isset($_COOKIE['remember_user'])) {
                        setcookie("remember_user", "", time() - 3600, "/");
                    }
                }

                header("Location: staff/dashboard.php");
                exit;
            } else {
                $msg = "<div class='alert alert-danger'>Incorrect password.</div>";
            }
        } else {
            $msg = "<div class='alert alert-danger'>Email not found.</div>";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../../assets/img/icon.png">
    <title>Staff Portal | SolarPower Energy Corporation</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/auth.css">
    <style>
        /* Dagdag na Layer: Iwasan ang accidental clicks */
        .btn-login:active { transform: scale(0.98); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light fixed-top bg-transparent">
    <div class="container">
        <a class="navbar-brand" href="../index.php">
            <img src="../assets/img/logo_no_background.png" alt="Site Logo" style="height: 100px;">
        </a>
    </div>
</nav>

<div class="login-bg d-flex justify-content-center align-items-center">
    <div class="login-card shadow-lg p-4 bg-white rounded">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-warning-subtle text-warning rounded-circle mb-3" style="width: 60px; height: 60px;">
                <i class="fa-solid fa-solar-panel fa-2x"></i>
            </div>
            <h2 class="mb-1">Staff Login</h2>
            <p class="text-muted small">Authorized Personnel Only</p>
        </div>

        <?php echo $msg; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-floating mb-3">
                <input type="email" name="email" class="form-control" id="floatingInput" placeholder="name@example.com" value="<?php echo $remembered_email; ?>" required>
                <label for="floatingInput">Email address</label>
            </div>
            
            <div class="form-floating mb-3">
                <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password" required>
                <label for="floatingPassword">Password</label>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" <?php if($remembered_email) echo "checked"; ?>>
                    <label class="form-check-label small text-secondary" for="remember" style="cursor: pointer;">
                        Remember Me
                    </label>
                </div>
                </div>

            <button class="btn w-100 shadow-sm py-2" type="submit" style="background-color: #0a5c3d; color: white; font-weight: 600;">
                <i class="fa-solid fa-right-to-bracket me-2"></i> SIGN IN
            </button>            
        </form>
        
        <div class="text-center mt-4">
            <a href="../index.php" class="text-decoration-none small text-muted">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Homepage
            </a>
        </div>
    </div>
</div>

<div class="admin-login-link position-fixed bottom-0 end-0 m-3">
    <a href="admin/login.php" class="btn btn-sm btn-outline-secondary border-0">
        <i class="fa-solid fa-shield-halved"></i> Admin
    </a>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>