<?php
$servername = "";
$username = "i10034377_x7ts1";
$password = "v&ebGvT)Pvr[";
$dbname = "solar_power";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
