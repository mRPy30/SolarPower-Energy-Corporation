<?php
/**
 * SMTP DIAGNOSTIC TEST — DELETE THIS FILE AFTER FIXING
 * Open in browser: http://localhost/SolarPower-Energy-Corporation/test_mail.php
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

echo "<pre style='font-family:monospace; font-size:14px; padding:20px; background:#1e1e1e; color:#d4d4d4; border-radius:8px;'>";
echo "<b style='color:#4ec9b0;'>===== SMTP DIAGNOSTIC TEST =====</b>\n\n";

$mail = new PHPMailer(true);

try {
    // Show debug output directly on screen
    $mail->SMTPDebug  = SMTP::DEBUG_SERVER; // Full debug output
    $mail->Debugoutput = function($str, $level) {
        $color = str_contains($str, 'ERROR') || str_contains($str, 'FAIL') ? '#f44747' : '#9cdcfe';
        echo "<span style='color:{$color}'>" . htmlspecialchars($str) . "</span>\n";
    };

    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'solar@solarpower.com.ph';
    $mail->Password   = 'iwnf tcyt uheg iznn';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ]
    ];

    $mail->setFrom('solar@solarpower.com.ph', 'SolarPower Test');
    $mail->addAddress('solar@solarpower.com.ph');
    $mail->Subject = 'SMTP Test';
    $mail->Body    = 'This is a test email to verify SMTP is working.';

    $mail->send();

    echo "\n<b style='color:#4ec9b0; font-size:18px;'>✅ SUCCESS! Email sent successfully!</b>\n";
    echo "Check the solar@solarpower.com.ph inbox.\n";

} catch (Exception $e) {
    echo "\n<b style='color:#f44747; font-size:16px;'>❌ FAILED — Error Details Below:</b>\n\n";
    echo "<b style='color:#f44747;'>PHPMailer Error:</b> " . htmlspecialchars($mail->ErrorInfo) . "\n";
    echo "<b style='color:#f44747;'>Exception Message:</b> " . htmlspecialchars($e->getMessage()) . "\n\n";

    echo "<b style='color:#ffd700;'>===== WHAT TO DO =====</b>\n";

    if (str_contains($mail->ErrorInfo, 'Username and Password not accepted') 
     || str_contains($mail->ErrorInfo, 'authentication')) {
        echo "❌ <b style='color:#f44747;'>App Password is WRONG or EXPIRED</b>\n\n";
        echo "Fix:\n";
        echo "  1. Go to: <b style='color:#ce9178;'>myaccount.google.com</b> (sign in as solar@solarpower.com.ph)\n";
        echo "  2. Security → 2-Step Verification → App passwords\n";
        echo "  3. Generate a new App Password for 'Mail'\n";
        echo "  4. Copy the 16-character password and update it in contact_submit.php\n";
    } elseif (str_contains($mail->ErrorInfo, 'Could not connect') 
           || str_contains($mail->ErrorInfo, 'connection')) {
        echo "❌ <b style='color:#f44747;'>Cannot connect to smtp.gmail.com:587</b>\n\n";
        echo "Fix:\n";
        echo "  Your hosting/network is blocking outbound port 587.\n";
        echo "  Try asking GoDaddy support to unblock port 587 or 465 for SMTP.\n";
    } elseif (str_contains($mail->ErrorInfo, 'SMTP connect() failed')) {
        echo "❌ <b style='color:#f44747;'>SMTP connection blocked</b> (common on GoDaddy shared hosting)\n\n";
        echo "Fix:\n";
        echo "  GoDaddy blocks external SMTP on shared hosting.\n";
        echo "  You need to use GoDaddy's own SMTP relay OR upgrade to a plan that allows it.\n";
        echo "  GoDaddy SMTP settings: Host=relay-hosting.secureserver.net, Port=25, No auth needed.\n";
    }
}

echo "\n<b style='color:#4ec9b0;'>================================</b>";
echo "</pre>";
?>
