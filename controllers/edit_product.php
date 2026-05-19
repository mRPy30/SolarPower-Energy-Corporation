<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../views/login.php");
    exit;
}

require_once __DIR__ . '/../config/dbconn.php';

$product_id     = intval($_POST['product_id']);
$displayName    = $_POST['displayName'];
$brandName      = $_POST['brandName'];
$price          = floatval($_POST['price']);
$category       = $_POST['category'];
$stockQuantity  = intval($_POST['stockQuantity']);
$description    = $_POST['description'];

$stmt = $conn->prepare("
    UPDATE product 
    SET displayName=?, brandName=?, price=?, category=?, stockQuantity=?, description=?
    WHERE id=?
");
$stmt->bind_param(
    "ssdsisi",
    $displayName,
    $brandName,
    $price,
    $category,
    $stockQuantity,
    $description,
    $product_id
);

$stmt->execute();
$stmt->close();
$conn->close();

/* Reload dashboard product page */
header("Location: ../views/staff/dashboard.php#product");
exit;
