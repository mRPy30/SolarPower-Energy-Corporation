<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "solar_power");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection failed"]);
    exit;
}

$name    = trim($_POST['name']);
$email   = trim($_POST['email']);
$phone   = trim($_POST['phone']);
$message = trim($_POST['message']);

if (!$name || !$email || !$message) {
    echo json_encode(["success" => false, "message" => "Required fields missing"]);
    exit;
}

/* 1️⃣ SAVE TO DATABASE */
$stmt = $conn->prepare(
    "INSERT INTO contact_messages (name, email, phone, message) VALUES (?,?,?,?)"
);
$stmt->bind_param("ssss", $name, $email, $phone, $message);
$stmt->execute();

/* 2️⃣ SEND EMAIL NOTIFICATION */
$to = "araquejanvier@gmail.com"; // company email
$subject = "New Contact Inquiry - Solar Power Website";

$body = "
New inquiry received:

Name: $name
Email: $email
Phone: $phone

Message:
$message
";

$headers = "From: Website <no-reply@solarpower.com.ph>\r\n";
$headers .= "Reply-To: $email\r\n";

@mail($to, $subject, $body, $headers);

echo json_encode(["success" => true, "message" => "Message sent successfully"]);
