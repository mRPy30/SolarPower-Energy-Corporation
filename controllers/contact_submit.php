<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
include "../config/dbconn.php";

$resendMailerPath = __DIR__ . '/../includes/resend-mailer.php';
if (is_file($resendMailerPath)) {
    require_once $resendMailerPath;
}

if (!function_exists('solar_send_resend_email')) {
    function solar_send_resend_email(string $to, string $subject, string $html, array $options = []): array
    {
        return [
            'success' => false,
            'provider' => 'resend',
            'message' => 'Email helper file is missing. Upload includes/resend-mailer.php.',
        ];
    }
}

if (!function_exists('solar_send_internal_lead_email')) {
    function solar_send_internal_lead_email(string $subject, string $html, array $options = []): array
    {
        return [
            'success' => false,
            'provider' => 'resend',
            'message' => 'Email helper file is missing. Upload includes/resend-mailer.php.',
        ];
    }
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');
$privacyConsent = isset($_POST['privacy_consent']) && $_POST['privacy_consent'] === '1';

if (!$name || !$email || !$message) {
    echo json_encode(["success" => false, "message" => "Required fields missing"]);
    exit;
}

if (!$privacyConsent) {
    echo json_encode(["success" => false, "message" => "Please confirm the Data Privacy Notice before submitting."]);
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
$emailResult = ['sent' => false, 'provider' => 'resend', 'message' => 'Email notification was not attempted.'];

try {
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
    
    $resendResult = solar_send_internal_lead_email($subject, $emailBody, [
        'reply_to' => $email
    ]);
    $emailResult = [
        'sent' => (bool) ($resendResult['success'] ?? false),
        'provider' => $resendResult['provider'] ?? 'resend',
        'message' => $resendResult['message'] ?? ''
    ];

    if (!$emailResult['sent']) {
        error_log('Contact inquiry email failed: ' . $emailResult['message']);
    }
} catch (Exception $e) {
    $emailResult = ['sent' => false, 'provider' => 'resend', 'message' => $e->getMessage()];
    error_log('Contact inquiry email exception: ' . $e->getMessage());
}

echo json_encode([
    "success" => true,
    "message" => "Message saved successfully",
    "email_sent" => (bool) $emailResult['sent'],
    "email_provider" => $emailResult['provider'],
    "email_message" => $emailResult['message']
]);
