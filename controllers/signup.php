<?php 
include "../config/dbconn.php";

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
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
        $stmt = $conn->prepare("INSERT INTO client (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $password);

        if ($stmt->execute()) {
            $msg = "<div class='alert alert-success'>Account created! Please login.</div>";
        } else {
            $msg = "<div class='alert alert-danger'>Something went wrong.</div>";
        }
    }
}
?>