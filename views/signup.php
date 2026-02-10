<?php 
    
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "solar_power";

$conn = mysqli_connect($servername, $username, $password, $dbname);


    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $firstName = trim($_POST["firstName"]);
    $lastName = trim($_POST["lastName"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    // Check if email exists
    $check = $conn->prepare("SELECT id FROM client WHERE email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $msg = "<div class='alert alert-danger'>Email already exists.</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO client (firstName, lastName, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $password);

        if ($stmt->execute()) {
            $msg = "<div class='alert alert-success'>Account created! Please login.</div>";
        } else {
            $msg = "<div class='alert alert-danger'>Something went wrong.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solar Power Energy - Login</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">

<style>
        body {
            margin: 0;
            padding: 0;
        }

        :root {
            --clr-primary: #ffc107; 
            --clr-secondary: #0a5c3d; 
            --clr-dark: #333;
        }

        .login-bg {
            background: url('../assets/img/cover.png') no-repeat center center/cover;
            height: 100vh;
            width: 100%;
            position: relative;
        }

        .login-bg::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.35);
        }

        .login-card {
            position: relative;
            z-index: 2;
            max-width: 380px;
            width: 100%;
            background: #fff;
            padding: 35px 30px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }

        .login-card h2 {
            font-weight: 700;
            font-size: 28px;
        }

        .login-card input {
            height: 45px;
        }

        .login-card .btn-login {
            background: #114b3d;
            color: #fff;
            height: 45px;
        }

        .login-card .btn-login:hover {
            background: #0d3d32;
        }

        nav.navbar {
            background: #fff !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        }

        .navbar-brand img {
            height: 75px;
        }

        .admin-login-link {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        .admin-login-link a {
            background: #dc3545;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
            transition: all 0.3s ease;
        }

        .admin-login-link a:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
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
        <h2 class="mb-4">Signup</h2>

        <form method="POST" action="">
            <input type="text" name="firstName" class="form-control mb-3" placeholder="First Name" required>
            <input type="text" name="lastName" class="form-control mb-3" placeholder="Last Name" required>
            <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>
            <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>

            <button class="btn btn-login w-100 mb-3" type="submit">Signup</button>

            <a href="login.php" class="small text-decoration-none">I already have an account</a>
        </form>


    </div>

</div>

<!-- Bootstrap JS -->   
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

<script>

</script>
</body>
</html>
