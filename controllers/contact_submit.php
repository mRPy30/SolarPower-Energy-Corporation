<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/../config/dbconn.php';

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
$now         = date('Y-m-d H:i:s');
$displayDate = date('F j, Y \a\t g:i A');
$currentYear = date('Y');

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

/* 2️⃣ SEND EMAIL NOTIFICATION via PHPMailer SMTP */
try {
    $mail = new PHPMailer(true);

    // SMTP Configuration (same as send-inspection-email.php)
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'solar@solarpower.com.ph';
    $mail->Password   = 'iwnf tcyt uheg iznn'; // Gmail App Password
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    // Sender & recipient
    $mail->setFrom('solar@solarpower.com.ph', 'SolarPower Website');
    $mail->addReplyTo($email, $name);
    $mail->addAddress('solar@solarpower.com.ph', 'SolarPower Energy Corp.');

    // Email content
    $mail->isHTML(true);
    $mail->Subject = "📩 New Contact Inquiry from {$name}";

    // Design tokens
    $primaryColor = '#f39c12';
    $darkColor    = '#2c3e50';
    $bgColor      = '#f0f4f8';
    $safeMessage  = nl2br(htmlspecialchars($message));
    $safePhone    = htmlspecialchars($phone ?: 'N/A');
    $safeName     = htmlspecialchars($name);
    $safeEmail    = htmlspecialchars($email);

    $mail->Body = "
    <div style='background-color:{$bgColor}; padding:40px 10px; font-family:\"Segoe UI\",Helvetica,Arial,sans-serif;'>
        <div style='max-width:620px; margin:0 auto; background:#ffffff; border-radius:14px; overflow:hidden; box-shadow:0 12px 35px rgba(0,0,0,0.1);'>

            <!-- Header -->
            <div style='background-color:{$darkColor}; padding:30px 30px 20px; text-align:center; border-bottom:5px solid {$primaryColor};'>
                <h1 style='color:#ffffff; margin:0; font-size:22px; letter-spacing:1px; text-transform:uppercase;'>☀ SolarPower Energy Corp.</h1>
                <p style='color:{$primaryColor}; margin:8px 0 0; font-weight:bold; text-transform:uppercase; font-size:13px; letter-spacing:1px;'>New Contact Form Inquiry</p>
            </div>

            <!-- Body -->
            <div style='padding:35px 30px;'>
                <p style='font-size:15px; color:#555; line-height:1.7; margin-top:0;'>
                    A new message has been submitted through the <strong>Contact Us</strong> page on <em>{$displayDate}</em>. Here are the details:
                </p>

                <!-- Details table -->
                <table width='100%' cellpadding='0' cellspacing='0' style='border-collapse:collapse; margin-top:20px;'>
                    <tr>
                        <td style='padding:12px 0; border-bottom:1px solid #eee; width:110px; color:#999; font-size:12px; font-weight:bold; text-transform:uppercase; vertical-align:top;'>Name</td>
                        <td style='padding:12px 0; border-bottom:1px solid #eee; color:#222; font-weight:600; font-size:15px;'>{$safeName}</td>
                    </tr>
                    <tr>
                        <td style='padding:12px 0; border-bottom:1px solid #eee; color:#999; font-size:12px; font-weight:bold; text-transform:uppercase; vertical-align:top;'>Email</td>
                        <td style='padding:12px 0; border-bottom:1px solid #eee;'>
                            <a href='mailto:{$safeEmail}' style='color:{$primaryColor}; font-weight:600; text-decoration:none;'>{$safeEmail}</a>
                        </td>
                    </tr>
                    <tr>
                        <td style='padding:12px 0; border-bottom:1px solid #eee; color:#999; font-size:12px; font-weight:bold; text-transform:uppercase; vertical-align:top;'>Phone</td>
                        <td style='padding:12px 0; border-bottom:1px solid #eee; color:#222; font-weight:600;'>
                            <a href='tel:{$safePhone}' style='color:{$darkColor}; text-decoration:none;'>{$safePhone}</a>
                        </td>
                    </tr>
                    <tr>
                        <td style='padding:14px 0 0; color:#999; font-size:12px; font-weight:bold; text-transform:uppercase; vertical-align:top;'>Message</td>
                        <td style='padding:14px 0 0;'>
                            <div style='background:#fffbf0; border-left:4px solid {$primaryColor}; padding:15px 18px; border-radius:0 8px 8px 0; color:#444; font-size:14px; line-height:1.8; font-style:italic;'>
                                &ldquo;{$safeMessage}&rdquo;
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- CTA -->
                <div style='margin-top:35px; text-align:center;'>
                    <a href='mailto:{$safeEmail}?subject=Re: Your Inquiry - SolarPower Energy Corp.'
                       style='background-color:{$primaryColor}; color:#ffffff; padding:14px 36px; text-decoration:none; border-radius:50px; font-weight:bold; font-size:14px; display:inline-block; box-shadow:0 4px 14px rgba(243,156,18,0.35); letter-spacing:0.5px;'>
                        ✉ Reply to {$safeName}
                    </a>
                    <p style='font-size:12px; color:#aaa; margin-top:12px;'>Please respond within the 24-hour SLA.</p>
                </div>
            </div>

            <!-- Footer -->
            <div style='background:#f4f6f8; padding:22px 30px; text-align:center; border-top:1px solid #e8ecf0;'>
                <p style='margin:0; font-size:12px; color:#999; line-height:1.6;'>
                    &copy; {$currentYear} SolarPower Energy Corp. &mdash; Automated notification from the website contact form.<br>
                    Please do not reply directly to this email.
                </p>
            </div>
        </div>
    </div>
    ";

    // Plain-text fallback
    $mail->AltBody = "New Contact Inquiry\n\nName: {$name}\nEmail: {$email}\nPhone: {$safePhone}\nDate: {$displayDate}\n\nMessage:\n{$message}\n\n---\nSolarPower Energy Corp.";

    $mail->send();

} catch (Exception $e) {
    // Email failed – log it but do NOT block the user response
    error_log("Contact email failed: " . $mail->ErrorInfo);
}

echo json_encode(["success" => true, "message" => "Message sent successfully"]);