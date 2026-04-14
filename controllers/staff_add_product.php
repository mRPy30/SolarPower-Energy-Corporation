<?php
session_start();
require_once "../config/db.php"; // adjust path

// Staff protection
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../login.php");
    exit;
}

if (isset($_POST['publish'])) {

    $category = $_POST['category'];
    $brand = $_POST['brand_name'];
    $name = $_POST['product_name'];
    $warranty = $_POST['warranty'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $description = $_POST['description'];
    $posted_by = $_SESSION['user_id'];

    /* IMAGE UPLOAD */
    $imageName = time() . "_" . $_FILES['product_image']['name'];
    $imageTmp = $_FILES['product_image']['tmp_name'];
    $uploadPath = "../uploads/products/" . $imageName;

    if (!move_uploaded_file($imageTmp, $uploadPath)) {
        die("Image upload failed");
    }

    $stmt = $conn->prepare("
        INSERT INTO products 
        (category, brand_name, product_name, warranty, price, stock, description, image, posted_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ssssdisii",
        $category,
        $brand,
        $name,
        $warranty,
        $price,
        $stock,
        $description,
        $imageName,
        $posted_by
    );

    if ($stmt->execute()) {
        header("Location: ../views/staff/dashboard.php?success=product_added");
        exit;
    } else {
        echo "Error adding product";
    }
}
