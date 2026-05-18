<?php
include __DIR__ . "/../config/dbconn.php";
$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("DB connection failed: " . mysqli_connect_error());
}
$res = $conn->query("SHOW COLUMNS FROM product");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        print_r($row);
    }
}
