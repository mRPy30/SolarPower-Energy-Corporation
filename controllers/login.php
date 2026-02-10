<?php
session_start();
include "../config/dbconn.php";

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    $stmt = $conn->prepare("SELECT id, name, password FROM client WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {

        $stmt->bind_result($id, $name, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            
            $_SESSION["id"] = $id;
            $_SESSION["name"] = $name;

            header("Location: ../login.php");
            exit;

        } else {
            $msg = "<div class='alert alert-danger'>Incorrect password.</div>";
        }

    } else {
        $msg = "<div class='alert alert-danger'>Email not found.</div>";
    }
}
?>
