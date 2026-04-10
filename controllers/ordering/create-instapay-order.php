<?php
/**
 * create-instapay-order.php
 * Handles InstaPay order submission with receipt upload.
 * Saves order to database and stores the uploaded receipt file.
 */

// Suppress PHP notices/warnings so they do not corrupt JSON output
error_reporting(0);
ini_set("display_errors", 0);

// Always output JSON
header("Content-Type: application/json");
header("X-Content-Type-Options: nosniff");

session_start();

// Helper: JSON response (always valid JSON, never HTML)
function respond($success, $message = "", $extra = []) {
    echo json_encode(array_merge(["success" => $success, "message" => $message], $extra));
    exit;
}

// Load DB connection
$dbPath = __DIR__ . "/../../config/dbconn.php";
if (!file_exists($dbPath)) {
    respond(false, "Server configuration error: database config not found.");
}
include $dbPath;

if (!isset($conn) || $conn->connect_error) {
    respond(false, "Database connection failed: " . ($conn->connect_error ?? "unknown error"));
}

// 1. Validate request method
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond(false, "Invalid request method.");
}

// 2. Read & sanitize POST fields
$customerName    = trim($_POST["customerName"]    ?? "");
$customerEmail   = trim($_POST["customerEmail"]   ?? "");
$customerPhone   = trim($_POST["customerPhone"]   ?? "");
$customerAddress = trim($_POST["customerAddress"] ?? "");
$paymentType     = trim($_POST["paymentType"]     ?? "full");
$paymentMethod   = "instapay";
$amountPaid      = floatval($_POST["amountPaid"]      ?? 0);
$totalAmount     = floatval($_POST["totalAmount"]     ?? 0);
$deliveryFee     = floatval($_POST["deliveryFee"]     ?? 0);
$installationFee = floatval($_POST["installationFee"] ?? 0);
$itemsJson       = $_POST["items"] ?? "[]";

if (!$customerName || !$customerEmail || !$customerPhone || !$customerAddress) {
    respond(false, "Missing required customer information.");
}

if ($totalAmount <= 0) {
    respond(false, "Invalid order total.");
}

$items = json_decode($itemsJson, true);
if (!is_array($items) || count($items) === 0) {
    respond(false, "No order items found.");
}

// 3. Handle receipt file upload
$receiptPath = null;

if (isset($_FILES["receipt"]) && $_FILES["receipt"]["error"] === UPLOAD_ERR_OK) {
    $file    = $_FILES["receipt"];
    $maxSize = 5 * 1024 * 1024;

    if ($file["size"] > $maxSize) {
        respond(false, "Receipt file is too large. Maximum size is 5MB.");
    }

    $allowed  = ["image/jpeg", "image/png", "image/gif", "image/webp", "application/pdf"];
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file["tmp_name"]);

    if (!in_array($mimeType, $allowed)) {
        respond(false, "Invalid file type. Please upload an image (JPG, PNG, GIF, WEBP) or PDF.");
    }

    $uploadDir = __DIR__ . "/../../uploads/receipts/";
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            respond(false, "Server error: Could not create upload directory.");
        }
    }

    $extension   = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $safeExt     = preg_replace("/[^a-z0-9]/", "", $extension);
    $fileName    = "receipt_" . date("YmdHis") . "_" . bin2hex(random_bytes(4)) . "." . $safeExt;
    $destination = $uploadDir . $fileName;

    if (!move_uploaded_file($file["tmp_name"], $destination)) {
        respond(false, "Server error: Could not save receipt file. Check folder permissions.");
    }

    $receiptPath = "uploads/receipts/" . $fileName;

} elseif (isset($_FILES["receipt"]) && $_FILES["receipt"]["error"] !== UPLOAD_ERR_NO_FILE) {
    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE   => "File exceeds server upload limit.",
        UPLOAD_ERR_FORM_SIZE  => "File exceeds form size limit.",
        UPLOAD_ERR_PARTIAL    => "File was only partially uploaded.",
        UPLOAD_ERR_NO_TMP_DIR => "Server missing temp folder.",
        UPLOAD_ERR_CANT_WRITE => "Server failed to write file.",
        UPLOAD_ERR_EXTENSION  => "Upload blocked by server extension.",
    ];
    $errCode = $_FILES["receipt"]["error"];
    respond(false, $uploadErrors[$errCode] ?? "Unknown upload error (code " . $errCode . ").");
}

// 4. Generate order reference
$orderRef = "ORD-" . date("YmdHis") . "-" . strtoupper(bin2hex(random_bytes(3)));

// 5. Build notes
$paymentLabel = ["full" => "100% Full Payment", "downpayment" => "50% Down Payment", "initial" => "20% Initial Payment"];
$notes = ($paymentLabel[$paymentType] ?? strtoupper($paymentType))
       . " | Amount Paid: PHP " . number_format($amountPaid, 2)
       . " | Delivery Fee: PHP " . number_format($deliveryFee, 2)
       . " | Installation Fee: PHP " . number_format($installationFee, 2);

// 6. Check if receipt_path column exists
$hasReceiptColumn = false;
$colCheck = $conn->query("SHOW COLUMNS FROM `orders` LIKE 'receipt_path'");
if ($colCheck && $colCheck->num_rows > 0) {
    $hasReceiptColumn = true;
}

if ($hasReceiptColumn) {
    $stmt = $conn->prepare(
        "INSERT INTO orders 
            (order_reference, customer_name, customer_email, customer_phone, customer_address,
             total_amount, payment_method, payment_status, order_status, receipt_path, staff_notes)
         VALUES (?, ?, ?, ?, ?, ?, ?, 'pending_verification', 'pending', ?, ?)"
    );
    if (!$stmt) {
        respond(false, "DB prepare error: " . $conn->error);
    }
    $stmt->bind_param(
        "sssssdsss",
        $orderRef, $customerName, $customerEmail, $customerPhone,
        $customerAddress, $totalAmount, $paymentMethod,
        $receiptPath, $notes
    );
} else {
    if ($receiptPath) {
        $notes .= " | Receipt: " . $receiptPath;
    }
    $stmt = $conn->prepare(
        "INSERT INTO orders 
            (order_reference, customer_name, customer_email, customer_phone, customer_address,
             total_amount, payment_method, payment_status, order_status, staff_notes)
         VALUES (?, ?, ?, ?, ?, ?, ?, 'pending_verification', 'pending', ?)"
    );
    if (!$stmt) {
        respond(false, "DB prepare error: " . $conn->error);
    }
    $stmt->bind_param(
        "sssssdss",
        $orderRef, $customerName, $customerEmail, $customerPhone,
        $customerAddress, $totalAmount, $paymentMethod, $notes
    );
}

if (!$stmt->execute()) {
    respond(false, "Failed to save order: " . $stmt->error);
}

$orderId = $conn->insert_id;
$stmt->close();

// 7. Insert order items
$itemStmt = $conn->prepare(
    "INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal)
     VALUES (?, ?, ?, ?, ?, ?)"
);

if ($itemStmt) {
    foreach ($items as $item) {
        $productId   = intval($item["id"]       ?? 0);
        $productName = trim($item["name"]        ?? "Product");
        $quantity    = intval($item["quantity"]  ?? 1);
        $price       = floatval($item["price"]   ?? 0);
        $subtotal    = $price * $quantity;

        $itemStmt->bind_param("iisidd", $orderId, $productId, $productName, $quantity, $price, $subtotal);
        $itemStmt->execute();
    }
    $itemStmt->close();
}

// 8. Success
respond(true, "Order submitted successfully.", [
    "orderRef"    => $orderRef,
    "orderId"     => $orderId,
    "receiptPath" => $receiptPath
]);