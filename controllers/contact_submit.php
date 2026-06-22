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

/* 2️⃣ SEND EMAIL NOTIFICATION VIA RESEND */
try {
    $resendApiKey = 're_Fh6X1rKo_JzjtWaAfUfRiEQs5HHxE4VsV'; 
    $subject = "New Contact Inquiry - " . $name;
    
    $emailBody = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px; background-color: #fcfcfc; }
            .header { background: linear-gradient(135deg, #115e59, #0f766e); color: #fff; padding: 20px; text-align: center; border-radius: 6px 6px 0 0; }
            .content { padding: 20px; background: #fff; }
            table { width: 100%; border-collapse: collapse; margin-top: 15px; }
            th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background-color: #f2f2f2; font-weight: bold; width: 35%; }
            .message-box { background-color: #f9f9f9; padding: 15px; border-left: 4px solid #10b981; margin-top: 15px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2 style='margin:0;'>New Contact Inquiry</h2>
                <p style='margin:5px 0 0 0;font-size:14px;'>SolarPower Energy Corporation</p>
            </div>
            <div class='content'>
                <p>A new message has been sent via the Contact Us form:</p>
                <table>
                    <tr>
                        <th>Name</th>
                        <td>" . htmlspecialchars($name) . "</td>
                    </tr>
                    <tr>
                        <th>Email Address</th>
                        <td>" . htmlspecialchars($email) . "</td>
                    </tr>
                    <tr>
                        <th>Contact Number</th>
                        <td>" . htmlspecialchars($phone) . "</td>
                    </tr>
                </table>
                <h3 style='margin-top:20px;color:#0f766e;'>Message</h3>
                <div class='message-box'>" . nl2br(htmlspecialchars($message)) . "</div>
            </div>
        </div>
    </body>
    </html>";
    
    $payload = [
        'from' => 'SolarPower Contact Form <onboarding@resend.dev>',
        'to' => ['solar@solarpower.com.ph'],
        'reply_to' => $email,
        'subject' => $subject,
        'html' => $emailBody
    ];
    
    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $resendApiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_exec($ch);
    curl_close($ch);
} catch (Exception $e) {
    // Silence exceptions
}

echo json_encode(["success" => true, "message" => "Message sent successfully"]);