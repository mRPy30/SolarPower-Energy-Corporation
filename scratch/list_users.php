<?php
include __DIR__ . "/../config/dbconn.php";
$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("DB connection failed: " . mysqli_connect_error());
}
$res = $conn->query("SELECT * FROM staff LIMIT 5");
if ($res) {
    echo "=== STAFF ===\n";
    while ($row = $res->fetch_assoc()) {
        print_r($row);
    }
}
$res2 = $conn->query("SELECT * FROM admin LIMIT 5");
if ($res2) {
    echo "=== ADMIN ===\n";
    while ($row = $res2->fetch_assoc()) {
        print_r($row);
    }
}
$res3 = $conn->query("SELECT * FROM client LIMIT 5");
if ($res3) {
    echo "=== CLIENT ===\n";
    while ($row = $res3->fetch_assoc()) {
        print_r($row);
    }
}
