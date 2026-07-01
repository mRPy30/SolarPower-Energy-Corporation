<?php
if (ob_get_level() === 0) {
    ob_start();
}

header('Content-Type: application/json');
ini_set('display_errors', '0');
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_OFF);

function tracking_json(array $payload, int $statusCode = 200): void
{
    if (ob_get_length()) {
        ob_clean();
    }

    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

function tracking_clean($value): string
{
    $text = strtoupper(trim((string) $value));
    $text = preg_replace('/[^A-Z0-9_-]+/', '', $text);
    return substr($text, 0, 60);
}

function tracking_status_key($value): string
{
    $key = strtolower(trim((string) $value));
    $key = str_replace([' ', '-'], '_', $key);
    return preg_replace('/_+/', '_', $key);
}

function tracking_stage_from_status(string $orderStatus, string $paymentStatus): int
{
    $status = tracking_status_key($orderStatus);
    $payment = tracking_status_key($paymentStatus);

    if (in_array($status, ['cancelled', 'canceled'], true) || in_array($payment, ['failed', 'voided', 'refunded'], true)) {
        return 0;
    }

    if (in_array($status, ['installed', 'completed', 'complete', 'delivered'], true)) {
        return 4;
    }

    if (in_array($status, ['fleet_delivery', 'in_transit', 'out_for_delivery', 'ready_for_delivery'], true)) {
        return 3;
    }

    if (in_array($status, ['processing', 'preparing', 'ready_to_ship', 'quality_check', 'qc_testing'], true)) {
        return 2;
    }

    return 1;
}

function tracking_stage_from_history_status(string $status): int
{
    $key = tracking_status_key($status);
    if (in_array($key, ['installed', 'completed', 'complete', 'delivered'], true)) {
        return 4;
    }
    if (in_array($key, ['fleet_delivery', 'in_transit', 'out_for_delivery', 'ready_for_delivery'], true)) {
        return 3;
    }
    if (in_array($key, ['processing', 'preparing', 'ready_to_ship', 'quality_check', 'qc_testing'], true)) {
        return 2;
    }
    if (in_array($key, ['pending', 'confirmed', 'paid', 'payment_verified'], true)) {
        return 1;
    }

    return 0;
}

function tracking_format_timestamp($value): string
{
    if (!$value) {
        return '';
    }

    $time = strtotime((string) $value);
    if (!$time) {
        return '';
    }

    return date('M j, Y g:i A', $time);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST ?: $_GET ?: [];
}

$reference = tracking_clean($input['order_reference'] ?? $input['reference'] ?? '');
if ($reference === '') {
    tracking_json([
        'success' => false,
        'message' => 'Please enter your order reference number.'
    ], 422);
}

require __DIR__ . '/config/dbconn.php';

if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_errno) {
    tracking_json([
        'success' => false,
        'message' => 'Order tracking is temporarily unavailable.'
    ], 500);
}

$stmt = $conn->prepare(
    'SELECT id, order_reference, customer_name, customer_email, customer_phone,
            total_amount, payment_method, payment_status, order_status,
            tracking_number, current_location, estimated_delivery, delivered_at, created_at
     FROM orders
     WHERE UPPER(order_reference) = ?
     LIMIT 1'
);

if (!$stmt) {
    $conn->close();
    tracking_json([
        'success' => false,
        'message' => 'Order tracking is temporarily unavailable.'
    ], 500);
}

$stmt->bind_param('s', $reference);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    $conn->close();
    tracking_json([
        'success' => false,
        'message' => 'No order found for that reference number.'
    ], 404);
}

$history = [];
$historyResult = $conn->query("SHOW TABLES LIKE 'order_tracking_history'");
if ($historyResult && $historyResult->num_rows > 0) {
    $historyStmt = $conn->prepare(
        'SELECT status, location, description, created_at
         FROM order_tracking_history
         WHERE order_id = ?
         ORDER BY created_at ASC'
    );

    if ($historyStmt) {
        $orderId = (int) $order['id'];
        $historyStmt->bind_param('i', $orderId);
        $historyStmt->execute();
        $historyRows = $historyStmt->get_result();
        while ($row = $historyRows->fetch_assoc()) {
            $history[] = $row;
        }
        $historyStmt->close();
    }
}

$conn->close();

$stage = tracking_stage_from_status($order['order_status'] ?? '', $order['payment_status'] ?? '');
$stageTimestamps = [
    1 => $order['created_at'] ?? '',
    2 => '',
    3 => '',
    4 => $order['delivered_at'] ?? '',
];

foreach ($history as $row) {
    $historyStage = tracking_stage_from_history_status($row['status'] ?? '');
    if ($historyStage > 0 && empty($stageTimestamps[$historyStage])) {
        $stageTimestamps[$historyStage] = $row['created_at'] ?? '';
    }
}

if ($stage >= 4 && empty($stageTimestamps[4])) {
    $lastHistory = $history ? end($history) : null;
    $stageTimestamps[4] = $order['delivered_at'] ?: ($lastHistory['created_at'] ?? '');
}

$milestoneTemplates = [
    1 => [
        'title' => 'Order Confirmed',
        'description' => 'Payment verified via Maya/UnionBank',
    ],
    2 => [
        'title' => 'Processing',
        'description' => 'Solar equipment sorting & rigorous QC testing',
    ],
    3 => [
        'title' => 'Fleet Delivery',
        'description' => 'SolarPower Direct Fleet delivery van is en route',
    ],
    4 => [
        'title' => 'Delivered',
        'description' => 'Order successfully delivered',
    ],
];

$milestones = [];
foreach ($milestoneTemplates as $number => $template) {
    $milestones[] = [
        'stage' => $number,
        'title' => $template['title'],
        'description' => $template['description'],
        'active' => $stage >= $number,
        'current' => $stage === $number,
        'timestamp' => $stage >= $number ? tracking_format_timestamp($stageTimestamps[$number] ?? '') : '',
    ];
}

tracking_json([
    'success' => true,
    'reference' => $order['order_reference'],
    'status' => $stage,
    'status_stage' => $stage,
    'order_status' => $order['order_status'],
    'payment_status' => $order['payment_status'],
    'customer_name' => $order['customer_name'],
    'total_amount' => (float) $order['total_amount'],
    'tracking_number' => $order['tracking_number'] ?? '',
    'current_location' => $order['current_location'] ?? '',
    'estimated_delivery' => tracking_format_timestamp($order['estimated_delivery'] ?? ''),
    'milestones' => $milestones,
]);
