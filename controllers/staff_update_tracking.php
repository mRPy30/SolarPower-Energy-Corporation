<?php
// controllers/staff_update_tracking.php
session_start();
header('Content-Type: application/json');

// Check if user can update tracking. Admin dashboard also uses this endpoint.
$userRole = $_SESSION['role'] ?? '';
if (!isset($_SESSION['user_id']) || !in_array($userRole, ['staff', 'admin'], true)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$orderId = intval($data['order_id']);
$orderStatus = trim((string) ($data['order_status'] ?? ''));
$paymentStatus = trim((string) ($data['payment_status'] ?? ''));
$trackingNumber = isset($data['tracking_number']) ? trim((string) $data['tracking_number']) : null;
$currentLocation = isset($data['current_location']) ? trim((string) $data['current_location']) : null;
$estimatedDelivery = isset($data['estimated_delivery']) ? trim((string) $data['estimated_delivery']) : null;
$description = trim((string) ($data['description'] ?? ''));
$sendNotification = true; // Tracking updates always notify the customer by email.

$trackingNumber = $trackingNumber === '' ? null : $trackingNumber;
$currentLocation = $currentLocation === '' ? null : $currentLocation;
$estimatedDelivery = $estimatedDelivery === '' ? null : $estimatedDelivery;

// Validate order status
$validOrderStatuses = [
    'pending', 
    'confirmed', 
    'preparing', 
    'ready_to_ship', 
    'in_transit', 
    'out_for_delivery', 
    'delivered', 
    'cancelled'
];

if (!in_array($orderStatus, $validOrderStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid order status']);
    exit;
}

// Validate payment status
$validPaymentStatuses = ['pending', 'paid', 'partial'];
if (!in_array($paymentStatus, $validPaymentStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid payment status']);
    exit;
}

require_once __DIR__ . '/../config/dbconn.php';

$resendMailerPath = __DIR__ . '/../includes/resend-mailer.php';
if (is_file($resendMailerPath)) {
    require_once $resendMailerPath;
}

if (!function_exists('solar_send_resend_email')) {
    function solar_send_resend_email(string $to, string $subject, string $html, array $options = []): array
    {
        $to = trim($to);
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'provider' => 'resend', 'message' => 'Invalid recipient email.'];
        }

        if (!function_exists('curl_init')) {
            return ['success' => false, 'provider' => 'resend', 'message' => 'PHP cURL extension is unavailable.'];
        }

        $apiKey = trim((string) getenv('RESEND_API_KEY'));
        $configPath = __DIR__ . '/../config/resend.php';
        if ($apiKey === '' && is_file($configPath)) {
            $config = require $configPath;
            $apiKey = trim((string) ($config['api_key'] ?? ''));
        }

        if ($apiKey === '') {
            return ['success' => false, 'provider' => 'resend', 'message' => 'Missing RESEND_API_KEY. Upload config/resend.php or set RESEND_API_KEY in hosting.'];
        }

        $payload = [
            'from' => $options['from'] ?? 'SolarPower Energy Corporation <solar@solarpower.com.ph>',
            'to' => [$to],
            'subject' => $subject,
            'html' => $html,
            'reply_to' => $options['reply_to'] ?? 'solar@solarpower.com.ph',
        ];

        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $responseBody = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($responseBody === false || $curlError !== '') {
            return ['success' => false, 'provider' => 'resend', 'message' => $curlError ?: 'Unable to connect to Resend.'];
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'provider' => 'resend', 'message' => 'Email sent through Resend.'];
        }

        return ['success' => false, 'provider' => 'resend', 'message' => 'Resend API returned HTTP ' . $httpCode . '.'];
    }
}

// Get current order details before update
$orderStmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$orderStmt->bind_param("i", $orderId);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();

if ($orderResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    $orderStmt->close();
    $conn->close();
    exit;
}

$currentOrder = $orderResult->fetch_assoc();
$orderStmt->close();

// Set delivered_at timestamp if status is delivered
$deliveredAt = null;
if ($orderStatus === 'delivered') {
    $deliveredAt = date('Y-m-d H:i:s');
}

// Begin transaction
$conn->begin_transaction();

try {
    // Update order
    $updateStmt = $conn->prepare("
        UPDATE orders 
        SET order_status = ?, 
            payment_status = ?,
            tracking_number = ?,
            current_location = ?,
            estimated_delivery = ?,
            delivered_at = ?
        WHERE id = ?
    ");

    $updateStmt->bind_param(
        "ssssssi",
        $orderStatus,
        $paymentStatus,
        $trackingNumber,
        $currentLocation,
        $estimatedDelivery,
        $deliveredAt,
        $orderId
    );

    if (!$updateStmt->execute()) {
        throw new Exception('Failed to update order');
    }
    $updateStmt->close();

    // Insert into tracking history
    $historyStmt = $conn->prepare("
        INSERT INTO order_tracking_history 
        (order_id, status, location, description, updated_by_staff_id) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $staffId = $userRole === 'staff' ? (int) $_SESSION['user_id'] : null;
    $historyStmt->bind_param(
        "isssi",
        $orderId,
        $orderStatus,
        $currentLocation,
        $description,
        $staffId
    );

    if (!$historyStmt->execute()) {
        throw new Exception('Failed to log tracking history');
    }
    $historyStmt->close();

    // Commit transaction
    $conn->commit();

    $updatedOrder = $currentOrder;
    $updatedOrder['order_status'] = $orderStatus;
    $updatedOrder['payment_status'] = $paymentStatus;
    $updatedOrder['tracking_number'] = $trackingNumber;
    $updatedOrder['current_location'] = $currentLocation;
    $updatedOrder['estimated_delivery'] = $estimatedDelivery;
    $updatedOrder['delivered_at'] = $deliveredAt;

    $emailResult = $sendNotification
        ? sendTrackingNotification($updatedOrder, $orderStatus, $currentLocation, $description)
        : ['sent' => false, 'provider' => null, 'message' => 'Email notification skipped.'];

    echo json_encode([
        'success' => true,
        'message' => 'Order tracking updated successfully' . (($emailResult['sent'] ?? false) ? ' and customer email notification sent.' : '.'),
        'email_sent' => (bool) ($emailResult['sent'] ?? false),
        'email_provider' => $emailResult['provider'] ?? null,
        'email_message' => $emailResult['message'] ?? ''
    ]);

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update tracking: ' . $e->getMessage()
    ]);
}

$conn->close();

function sendTrackingNotification($order, $status, $location, $description): array {
    $to = trim((string) ($order['customer_email'] ?? ''));
    if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return ['sent' => false, 'provider' => null, 'message' => 'Customer email is missing or invalid.'];
    }

    $customerName = trackingEmailEscape($order['customer_name'] ?? 'Customer');
    $orderRefRaw = (string) ($order['order_reference'] ?? '');
    $orderRef = trackingEmailEscape($orderRefRaw);
    $statusTitle = trackingEmailEscape(getStatusTitle($status));
    $locationText = trackingEmailEscape((string) $location);
    $descriptionHtml = nl2br(trackingEmailEscape((string) $description));
    $trackingNumber = trackingEmailEscape((string) ($order['tracking_number'] ?? ''));
    $estimatedDelivery = trackingFormatDate($order['estimated_delivery'] ?? '');
    $trackUrl = trackingAppBaseUrl() . '/index.php?track_order=' . rawurlencode($orderRefRaw);
    $trackUrlHtml = trackingEmailEscape($trackUrl);
    $subject = "SolarPower Order Update: {$orderRefRaw} - " . getStatusTitle($status);

    $locationBlock = $locationText !== ''
        ? "<p style='margin:8px 0;'><strong>Current Location:</strong> {$locationText}</p>"
        : '';
    $trackingBlock = $trackingNumber !== ''
        ? "<p style='margin:8px 0;'><strong>Tracking Number:</strong> {$trackingNumber}</p>"
        : '';
    $deliveryBlock = $estimatedDelivery !== ''
        ? "<p style='margin:8px 0;'><strong>Estimated Delivery:</strong> {$estimatedDelivery}</p>"
        : '';
    $descriptionBlock = $descriptionHtml !== ''
        ? "<p style='margin:12px 0 0;'>{$descriptionHtml}</p>"
        : '';

    $html = "
    <html>
    <body style='margin:0; padding:0; background:#f4f7f3; font-family:Arial, sans-serif; color:#122033;'>
        <div style='max-width:620px; margin:0 auto; padding:24px;'>
            <div style='background:#0b5f3a; color:#ffffff; padding:24px; border-radius:14px 14px 0 0;'>
                <h1 style='font-size:22px; line-height:1.3; margin:0;'>Order Tracking Update</h1>
                <p style='margin:8px 0 0; color:#d9f7e6;'>SolarPower Energy Corporation</p>
            </div>
            <div style='background:#ffffff; padding:28px; border:1px solid #dce7de; border-top:0; border-radius:0 0 14px 14px;'>
                <p style='margin:0 0 14px;'>Hi {$customerName},</p>
                <p style='margin:0 0 18px;'>Your order <strong>{$orderRef}</strong> has a new tracking update.</p>
                <div style='border-left:5px solid #f3b400; background:#fff9e7; border-radius:10px; padding:18px; margin:20px 0;'>
                    <h2 style='font-size:18px; color:#0b5f3a; margin:0 0 10px;'>{$statusTitle}</h2>
                    {$locationBlock}
                    {$trackingBlock}
                    {$deliveryBlock}
                    {$descriptionBlock}
                </div>
                <p style='margin:20px 0;'>You can check the latest status anytime using your order reference number.</p>
                <a href='{$trackUrlHtml}' style='display:inline-block; background:#f3b400; color:#102018; padding:12px 20px; border-radius:8px; text-decoration:none; font-weight:700;'>Track Order</a>
                <p style='margin:26px 0 0; font-size:13px; color:#596579;'>Need help? Contact our support team at <strong>0995 394 7379</strong>.</p>
            </div>
            <p style='font-size:12px; color:#7b8794; text-align:center; margin:18px 0 0;'>This is an automated tracking notification.</p>
        </div>
    </body>
    </html>";

    $resendResult = solar_send_resend_email($to, $subject, $html, [
        'from' => 'SolarPower Energy Corporation <solar@solarpower.com.ph>',
        'reply_to' => 'solar@solarpower.com.ph',
    ]);
    if ($resendResult['success'] ?? false) {
        return ['sent' => true, 'provider' => 'resend', 'message' => $resendResult['message'] ?? 'Email sent through Resend.'];
    }

    error_log('Tracking email failed for order ' . $orderRefRaw . ': ' . ($resendResult['message'] ?? 'Unknown Resend error'));
    return ['sent' => false, 'provider' => 'resend', 'message' => $resendResult['message'] ?? 'Unable to send email.'];
}

function trackingEmailEscape($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function trackingFormatDate($dateValue): string {
    $dateValue = trim((string) $dateValue);
    if ($dateValue === '') {
        return '';
    }

    $timestamp = strtotime($dateValue);
    return $timestamp ? date('F j, Y', $timestamp) : '';
}

function trackingAppBaseUrl(): string {
    $configured = trim((string) (getenv('APP_BASE_URL') ?: getenv('SITE_URL') ?: ''));
    if ($configured !== '') {
        return rtrim($configured, '/');
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'solarpower.com.ph';

    return $scheme . '://' . $host;
}

function getStatusTitle($status) {
    $titles = [
        'pending' => 'Order Pending',
        'confirmed' => 'Order Confirmed',
        'preparing' => 'Preparing Your Order',
        'ready_to_ship' => 'Ready to Ship',
        'in_transit' => 'In Transit',
        'out_for_delivery' => 'Out for Delivery',
        'delivered' => 'Delivered',
        'cancelled' => 'Order Cancelled'
    ];
    
    return $titles[$status] ?? 'Order Updated';
}
?>
