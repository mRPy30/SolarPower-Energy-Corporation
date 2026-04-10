<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../views/login.php");
    exit;
}

include "../config/dbconn.php";

$product_id = intval($_POST['product_id']);

$stmt = $conn->prepare("DELETE FROM product WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();

$stmt->close();
$conn->close();

header("Location: ../views/staff/dashboard.php#product");
exit;
