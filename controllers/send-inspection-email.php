<?php
header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request');
    }

    $fullname = trim($_POST['fullname'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $address  = trim($_POST['address'] ?? '');
    $bill     = trim($_POST['bill'] ?? '');
    $notes    = trim($_POST['notes'] ?? '');

    if (!$fullname || !$email || !$phone || !$address) {
        throw new Exception('Required fields missing');
    }

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'solar@solarpower.com.ph';
    $mail->Password   = 'iwnf tcyt uheg iznn'; // Gmail App Password
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    // Set the sender with customer's name (Gmail will show this name)
    $mail->setFrom('noreply@solarpower.com.ph', $fullname);
    
    // Set reply-to as the customer's email
    $mail->addReplyTo($email, $fullname);
    
    // Recipient
    $mail->addAddress('solar@solarpower.com.ph');

    $mail->isHTML(true);
    $mail->Subject = "New Solar Inspection Request - {$fullname}";

    // Refined Styling & Variables
    $primaryColor = '#f39c12'; // Solar Gold
    $darkColor    = '#2c3e50'; // Navy/Charcoal
    $bgColor      = '#f8f9fa';
    $currentYear  = date('Y');

    $mail->isHTML(true);
    $mail->Subject = "New Inspection Request: {$fullname}";

    $mail->Body = "
    <div style='background-color: {$bgColor}; padding: 40px 10px; font-family: \"Segoe UI\", Helvetica, Arial, sans-serif;'>
        <div style='max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1);'>
            
            <div style='background-color: {$darkColor}; padding: 30px; text-align: center; border-bottom: 5px solid {$primaryColor};'>
                <h1 style='color: #ffffff; margin: 0; font-size: 24px; letter-spacing: 1px;'>SOLARPOWER INSPECTION</h1>
                <p style='color: {$primaryColor}; margin: 5px 0 0; font-weight: bold; text-transform: uppercase; font-size: 13px;'>New Lead Received</p>
            </div>

            <div style='padding: 40px 30px;'>
                <p style='font-size: 16px; color: #444; line-height: 1.6;'>You have received a new inquiry from the website. Here are the customer details:</p>
                
                <div style='margin-top: 25px;'>
                    <div style='border-bottom: 1px solid #eee; padding: 12px 0; display: flex;'>
                        <span style='width: 120px; color: #888; font-size: 13px; font-weight: bold; text-transform: uppercase;'>Customer</span>
                        <span style='color: #222; font-weight: 600;'>{$fullname}</span>
                    </div>
                    
                    <div style='border-bottom: 1px solid #eee; padding: 12px 0;'>
                        <span style='width: 120px; color: #888; font-size: 13px; font-weight: bold; text-transform: uppercase;'>Phone</span>
                        <span style='color: #222; font-weight: 600;'><a href='tel:{$phone}' style='color: {$darkColor}; text-decoration: none;'>{$phone}</a></span>
                    </div>

                    <div style='border-bottom: 1px solid #eee; padding: 12px 0;'>
                        <span style='width: 120px; color: #888; font-size: 13px; font-weight: bold; text-transform: uppercase;'>Location</span>
                        <span style='color: #222;'>{$address}</span>
                    </div>

                    <div style='border-bottom: 1px solid #eee; padding: 12px 0;'>
                        <span style='width: 120px; color: #888; font-size: 13px; font-weight: bold; text-transform: uppercase;'>Monthly Bill</span>
                        <span style='color: #27ae60; font-weight: bold;'>₱ " . number_format((float)$bill, 2) . "</span>
                    </div>

                    <div style='padding: 12px 0;'>
                        <span style='display: block; color: #888; font-size: 13px; font-weight: bold; text-transform: uppercase; margin-bottom: 5px;'>Notes</span>
                        <div style='background: #fdfcf0; border: 1px dashed {$primaryColor}; padding: 15px; border-radius: 6px; color: #555; font-style: italic;'>
                            \"" . nl2br(htmlspecialchars($notes)) . "\"
                        </div>
                    </div>
                </div>

                <div style='margin-top: 40px; text-align: center;'>
                    <a href='mailto:{$email}' style='background-color: {$primaryColor}; color: #ffffff; padding: 15px 35px; text-decoration: none; border-radius: 50px; font-weight: bold; display: inline-block; box-shadow: 0 4px 10px rgba(243, 156, 18, 0.3);'>
                        Reply via Email
                    </a>
                    <p style='font-size: 12px; color: #999; margin-top: 15px;'>Please respond within the 24-hour SLA.</p>
                </div>
            </div>

            <div style='background: #f4f6f8; padding: 25px; text-align: center;'>
                <p style='margin: 0; font-size: 12px; color: #777;'>
                    &copy; {$currentYear} SolarPower Energy Corp. <br>
                    Automated lead notification. Please do not reply directly to this sender.
                </p>
            </div>
        </div>
    </div>
    ";
    $mail->send();

    echo json_encode([
        'success' => true,
        'message' => 'Inspection request sent successfully!'
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}