<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
include "../config/dbconn.php";

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');

if (!$name || !$email || !$message) {
    echo json_encode(["success" => false, "message" => "Required fields missing"]);
    exit;
}

/* Set Philippine Time */
date_default_timezone_set('Asia/Manila');
$now = date('Y-m-d H:i:s');

/* 1️⃣ SAVE TO DATABASE */
$stmt = $conn->prepare(
    "INSERT INTO contact_messages (name, email, phone, message, created_at) VALUES (?,?,?,?,?)"
);

if (!$stmt) {
    echo json_encode(["success" => false, "message" => "DB prepare failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("sssss", $name, $email, $phone, $message, $now);

if (!$stmt->execute()) {
    echo json_encode(["success" => false, "message" => "DB insert failed: " . $stmt->error]);
    exit;
}

/* 2️⃣ SEND EMAIL NOTIFICATION */
$to      = "solar@solarpower.com.ph";
$subject = "New Contact Inquiry - Solar Power Website";
$body    = "
New inquiry received:

Name: $name
Email: $email
Phone: $phone

Message:
$message
";

$headers  = "From: Website <no-reply@solarpower.com.ph>\r\n";
$headers .= "Reply-To: $email\r\n";

@mail($to, $subject, $body, $headers);

echo json_encode(["success" => true, "message" => "Message sent successfully"]);