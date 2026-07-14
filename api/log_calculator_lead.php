<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include __DIR__ . "/../config/dbconn.php";

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$action = isset($data['action']) ? trim($data['action']) : 'calculated';
$bill = isset($data['bill']) ? floatval($data['bill']) : 0;
$system_size = isset($data['system_size']) ? trim($data['system_size']) : '0 kWp';
$ip_address = $_SERVER['REMOTE_ADDR'];

function respond_with_error($message, $status_code = 422) {
    http_response_code($status_code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

function normalize_lead_phone($phone) {
    return preg_replace('/[\s-]+/', '', trim($phone));
}

function is_valid_lead_phone($phone) {
    return preg_match('/^(09\d{9}|\+639\d{9})$/', $phone) === 1;
}

function is_valid_lead_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL)
        && preg_match('/@(gmail\.com|yahoo\.com|yahoo\.com\.ph)$/i', $email) === 1;
}

// Lead info
$lead_name = isset($data['lead_name']) ? trim($data['lead_name']) : null;
$lead_phone = isset($data['lead_phone']) ? normalize_lead_phone($data['lead_phone']) : null;
$lead_email = isset($data['lead_email']) ? strtolower(trim($data['lead_email'])) : null;

$lead_name = $lead_name === '' ? null : $lead_name;
$lead_phone = $lead_phone === '' ? null : $lead_phone;
$lead_email = $lead_email === '' ? null : $lead_email;

$lead_actions = ['submitted', 'messenger', 'viber'];

if (in_array($action, $lead_actions, true) && empty($lead_name)) {
    respond_with_error('Please provide your full name before submitting.');
}

if (!empty($lead_name)) {
    if (empty($lead_phone) && empty($lead_email)) {
        respond_with_error('Please provide a valid contact number or Gmail/Yahoo email address.');
    }

    if (!empty($lead_phone) && !is_valid_lead_phone($lead_phone)) {
        respond_with_error('Please enter an 11-digit PH mobile number or +63 format, for example 09123456789 or +639123456789.');
    }

    if (!empty($lead_email) && !is_valid_lead_email($lead_email)) {
        respond_with_error('Please enter a valid Gmail or Yahoo email address.');
    }
}

// Prevent saving guest calculations with no lead details
if ($action === 'calculated' && empty($lead_name)) {
    echo json_encode(['success' => true, 'message' => 'Guest calculations are not logged']);
    exit;
}

// Determine user type display label
$user_type = 'Guest (' . $ip_address . ')';
$action_label = 'Calculated Only';

if ($action === 'messenger') {
    $action_label = 'Clicked Messenger';
} elseif ($action === 'viber') {
    $action_label = 'Clicked Viber';
} elseif ($action === 'submitted') {
    $action_label = 'Submitted Form';
}

if (!empty($lead_name)) {
    $user_type = $lead_name . ' (Lead)';
}

// ── DUPLICATE PREVENTER LOGIC ──
// Check if same lead name + phone/email or same IP guest exists
$exists = false;
$existing_id = null;

if (!empty($lead_name)) {
    // If it's a lead, check by name + phone or email
    $check_stmt = $conn->prepare("SELECT `id` FROM `calculator_logs` WHERE `lead_name` = ? AND (`lead_phone` = ? OR `lead_email` = ? OR (`lead_phone` IS NULL AND `lead_email` IS NULL)) LIMIT 1");
    $check_stmt->bind_param("sss", $lead_name, $lead_phone, $lead_email);
    $check_stmt->execute();
    $check_res = $check_stmt->get_result();
    if ($check_row = $check_res->fetch_assoc()) {
        $exists = true;
        $existing_id = $check_row['id'];
    }
    $check_stmt->close();
} else {
    // If it's a guest, check if an existing guest record with same IP exists within the last 15 minutes to prevent spamming logs
    $check_stmt = $conn->prepare("SELECT `id` FROM `calculator_logs` WHERE `ip_address` = ? AND `lead_name` IS NULL AND `timestamp` >= DATE_SUB(NOW(), INTERVAL 15 MINUTE) LIMIT 1");
    $check_stmt->bind_param("s", $ip_address);
    $check_stmt->execute();
    $check_res = $check_stmt->get_result();
    if ($check_row = $check_res->fetch_assoc()) {
        $exists = true;
        $existing_id = $check_row['id'];
    }
    $check_stmt->close();
}

if ($exists) {
    // Update existing record to keep metrics clean and prevent duplicates
    $stmt = $conn->prepare("UPDATE `calculator_logs` SET 
        `timestamp` = CURRENT_TIMESTAMP, 
        `user_type` = ?, 
        `lead_phone` = ?, 
        `lead_email` = ?, 
        `bill` = ?, 
        `system_size` = ?, 
        `action` = ?, 
        `action_label` = ? 
        WHERE `id` = ?");
    $stmt->bind_param("sssdsssi", $user_type, $lead_phone, $lead_email, $bill, $system_size, $action, $action_label, $existing_id);
} else {
    // Insert new record
    $stmt = $conn->prepare("INSERT INTO `calculator_logs` 
        (`ip_address`, `user_type`, `lead_name`, `lead_phone`, `lead_email`, `bill`, `system_size`, `action`, `action_label`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssdsss", $ip_address, $user_type, $lead_name, $lead_phone, $lead_email, $bill, $system_size, $action, $action_label);
}

if ($stmt->execute()) {
    // Send email notification via Resend if it's a real lead submission (i.e. lead_name is set)
    if (!empty($lead_name)) {
        try {
            $resendApiKey = 're_Fh6X1rKo_JzjtWaAfUfRiEQs5HHxE4VsV'; 
            $subject = "[Calculator Lead] " . $action_label . " - " . $lead_name;
            
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
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2 style='margin:0;'>New Solar Calculator Lead</h2>
                        <p style='margin:5px 0 0 0;font-size:14px;'>SolarPower Energy Corporation</p>
                    </div>
                    <div class='content'>
                        <p>A user has interacted with the Solar Savings Calculator on the website and provided their details:</p>
                        <table>
                            <tr>
                                <th>Lead Name</th>
                                <td>" . htmlspecialchars($lead_name) . "</td>
                            </tr>
                            <tr>
                                <th>Contact Phone</th>
                                <td>" . htmlspecialchars($lead_phone ?? '—') . "</td>
                            </tr>
                            <tr>
                                <th>Contact Email</th>
                                <td>" . htmlspecialchars($lead_email ?? '—') . "</td>
                            </tr>
                            <tr>
                                <th>Monthly Bill</th>
                                <td>₱ " . number_format($bill, 2) . "</td>
                            </tr>
                            <tr>
                                <th>Recommended Size</th>
                                <td>" . htmlspecialchars($system_size) . "</td>
                            </tr>
                            <tr>
                                <th>Action Taken</th>
                                <td><strong>" . htmlspecialchars($action_label) . "</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </body>
            </html>";
            
            $payload = [
                'from' => 'SolarPower Energy Corporation <solar@solarpower.com.ph>',
                'to' => ['solar@solarpower.com.ph'],
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
            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 && $httpCode !== 201) {
                // Fallback to standard PHP mail()
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";
                $headers .= "From: SolarPower Energy Corporation <solar@solarpower.com.ph>" . "\r\n";
                
                mail('solar@solarpower.com.ph', $subject, $emailBody, $headers);
            }
        } catch (Exception $e) {
            // Fallback if cURL or Resend fails
            try {
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";
                $headers .= "From: SolarPower Energy Corporation <solar@solarpower.com.ph>" . "\r\n";
                mail('solar@solarpower.com.ph', $subject ?? 'New Solar Estimate Request', $emailBody ?? 'Inquiry details saved.', $headers);
            } catch (Exception $mailEx) {
                // Silence mail exceptions
            }
        }
    }

    echo json_encode(['success' => true, 'message' => 'Calculator log saved successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
exit;
