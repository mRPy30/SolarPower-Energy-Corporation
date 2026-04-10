<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get form data
    $fullname = trim($_POST['fullname'] ?? $_POST['Full Name'] ?? '');
    $email = trim($_POST['email'] ?? $_POST['Email'] ?? '');
    $phone = trim($_POST['phone'] ?? $_POST['Contact Number'] ?? '');
    $address = trim($_POST['address'] ?? $_POST['Complete Address'] ?? '');
    $property_type = trim($_POST['property_type'] ?? $_POST['Property Type'] ?? '');
    $inspection_date = trim($_POST['inspection_date'] ?? $_POST['Preferred Inspection Date'] ?? '');
    $bill = trim($_POST['bill'] ?? $_POST['Monthly Electric Bill'] ?? '');
    $notes = trim($_POST['notes'] ?? $_POST['Additional Notes'] ?? 'No additional notes');

    // Validation
    if (!$fullname || !$email || !$phone) {
        throw new Exception('Please fill in all required fields');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please enter a valid email address');
    }

    // Email Configuration
    $to = 'solar@solarpower.com.ph';
    $subject = "🌞 New Solar Inspection Request - {$fullname}";
    
    // Get domain for From address
    $domain = $_SERVER['HTTP_HOST'] ?? 'solarpower.com.ph';
    $domain = str_replace('www.', '', $domain);
    
    // Headers
    $headers = array();
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=UTF-8';
    $headers[] = "From: SolarPower Website <noreply@{$domain}>";
    $headers[] = "Reply-To: {$fullname} <{$email}>";
    $headers[] = 'X-Mailer: PHP/' . phpversion();
    
    // Format date
    $formattedDate = 'Not specified';
    if ($inspection_date) {
        $dateObj = DateTime::createFromFormat('Y-m-d', $inspection_date);
        if ($dateObj) {
            $formattedDate = $dateObj->format('F d, Y');
        }
    }

    // EXACT EMAIL TEMPLATE FROM YOUR IMAGE
    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>New Inspection Request</title>
    </head>
    <body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; background-color: #f5f5f5;">
        
        <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 40px 20px;">
            <tr>
                <td align="center">
                    
                    <!-- Main Container -->
                    <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        
                        <!-- Header -->
                        <tr>
                            <td style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); padding: 30px; text-align: center; border-bottom: 4px solid #f39c12;">
                                <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; letter-spacing: 0.5px;">
                                    SOLARPOWER INSPECTION
                                </h1>
                                <p style="color: #f39c12; margin: 8px 0 0; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">
                                    NEW LEAD RECEIVED
                                </p>
                            </td>
                        </tr>
                        
                        <!-- Body -->
                        <tr>
                            <td style="padding: 40px 30px;">
                                
                                <p style="color: #555; font-size: 15px; line-height: 1.6; margin: 0 0 30px;">
                                    You have received a new inquiry from the website. Here are the customer details:
                                </p>
                                
                                <!-- Customer Info Table -->
                                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 25px;">
                                    
                                    <!-- Customer Name -->
                                    <tr>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #eee;">
                                            <table width="100%" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td style="color: #999; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; width: 140px;">
                                                        CUSTOMER
                                                    </td>
                                                    <td style="color: #333; font-size: 14px; font-weight: 600;">
                                                        ' . htmlspecialchars($fullname) . '
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    
                                    <!-- Phone -->
                                    <tr>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #eee;">
                                            <table width="100%" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td style="color: #999; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; width: 140px;">
                                                        PHONE
                                                    </td>
                                                    <td style="color: #333; font-size: 14px; font-weight: 600;">
                                                        <a href="tel:' . htmlspecialchars($phone) . '" style="color: #333; text-decoration: none;">
                                                            ' . htmlspecialchars($phone) . '
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    
                                    <!-- Email -->
                                    <tr>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #eee;">
                                            <table width="100%" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td style="color: #999; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; width: 140px;">
                                                        EMAIL
                                                    </td>
                                                    <td style="color: #333; font-size: 14px;">
                                                        <a href="mailto:' . htmlspecialchars($email) . '" style="color: #f39c12; text-decoration: none;">
                                                            ' . htmlspecialchars($email) . '
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    
                                    <!-- Property Type -->
                                    <tr>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #eee;">
                                            <table width="100%" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td style="color: #999; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; width: 140px;">
                                                        PROPERTY TYPE
                                                    </td>
                                                    <td style="color: #333; font-size: 14px;">
                                                        ' . htmlspecialchars($property_type) . '
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    
                                    <!-- Location -->
                                    <tr>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #eee;">
                                            <table width="100%" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td style="color: #999; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; width: 140px;">
                                                        LOCATION
                                                    </td>
                                                    <td style="color: #333; font-size: 14px; line-height: 1.5;">
                                                        ' . htmlspecialchars($address) . '
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    
                                    <!-- Inspection Date -->
                                    <tr>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #eee;">
                                            <table width="100%" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td style="color: #999; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; width: 140px;">
                                                        INSPECTION DATE
                                                    </td>
                                                    <td style="color: #333; font-size: 14px;">
                                                        ' . htmlspecialchars($formattedDate) . '
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    
                                    <!-- Monthly Bill -->
                                    <tr>
                                        <td style="padding: 12px 0;">
                                            <table width="100%" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td style="color: #999; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; width: 140px;">
                                                        MONTHLY BILL
                                                    </td>
                                                    <td style="color: #27ae60; font-size: 16px; font-weight: 700;">
                                                        ₱ ' . number_format((float)$bill, 2) . '
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    
                                </table>
                                
                                <!-- Notes Section -->
                                <div style="margin-top: 25px;">
                                    <p style="color: #999; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 10px;">
                                        NOTES
                                    </p>
                                    <div style="background: #fffbf0; border: 1px dashed #f39c12; border-radius: 6px; padding: 15px;">
                                        <p style="color: #666; font-size: 14px; font-style: italic; margin: 0; line-height: 1.6;">
                                            "' . nl2br(htmlspecialchars($notes)) . '"
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Action Button -->
                                <div style="text-align: center; margin-top: 35px;">
                                    <a href="mailto:' . htmlspecialchars($email) . '?subject=Re:%20Solar%20Inspection%20Request" 
                                       style="display: inline-block; background-color: #f39c12; color: #ffffff; padding: 14px 40px; text-decoration: none; border-radius: 50px; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 4px 12px rgba(243, 156, 18, 0.3);">
                                        Reply via Email
                                    </a>
                                </div>
                                
                                <!-- SLA Notice -->
                                <p style="text-align: center; color: #999; font-size: 12px; font-style: italic; margin: 25px 0 0; padding: 12px; background: #f8f9fa; border-radius: 6px;">
                                    ⏰ Please respond within the 24-hour SLA.
                                </p>
                                
                            </td>
                        </tr>
                        
                        <!-- Footer -->
                        <tr>
                            <td style="background-color: #f8f9fa; padding: 25px; text-align: center; border-top: 1px solid #eee;">
                                <p style="margin: 0; font-size: 12px; color: #999; line-height: 1.6;">
                                    © ' . date('Y') . ' SolarPower Energy Corp.<br>
                                    <span style="font-size: 11px; color: #bbb;">Automated lead notification. Please do not reply directly to this sender.</span>
                                </p>
                            </td>
                        </tr>
                        
                    </table>
                    
                </td>
            </tr>
        </table>
        
    </body>
    </html>
    ';

    // Try to send email
    $mailSent = @mail($to, $subject, $message, implode("\r\n", $headers));

    if (!$mailSent) {
        // If PHP mail() fails, log it and still return success
        // (FormSubmit backup will catch it)
        $errorLog = date('Y-m-d H:i:s') . " - PHP mail() failed for {$fullname}, FormSubmit backup activated\n";
        @file_put_contents(__DIR__ . '/email_errors.log', $errorLog, FILE_APPEND);
    } else {
        // Success logging
        $logEntry = date('Y-m-d H:i:s') . " - ✅ Email sent to {$to} from {$fullname} ({$email})\n";
        @file_put_contents(__DIR__ . '/inspection_logs.txt', $logEntry, FILE_APPEND);
    }

    // Always return success (FormSubmit will be the backup)
    echo json_encode([
        'success' => true,
        'message' => 'Inspection request sent successfully! We will contact you within 24 hours.'
    ]);

} catch (Exception $e) {
    $errorLog = date('Y-m-d H:i:s') . " - ERROR: " . $e->getMessage() . "\n";
    @file_put_contents(__DIR__ . '/email_errors.log', $errorLog, FILE_APPEND);
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>