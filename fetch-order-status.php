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

function tracking_identifier(string $name): string
{
    return '`' . str_replace('`', '``', $name) . '`';
}

function tracking_table_exists(mysqli $conn, string $table): bool
{
    $stmt = $conn->prepare('SHOW TABLES LIKE ?');
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('s', $table);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();

    return $exists;
}

function tracking_column_exists(mysqli $conn, string $table, string $column): bool
{
    $sql = 'SHOW COLUMNS FROM ' . tracking_identifier($table) . ' LIKE ?';
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('s', $column);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();

    return $exists;
}

function tracking_add_column_if_missing(mysqli $conn, string $table, string $column, string $definition): void
{
    if (tracking_column_exists($conn, $table, $column)) {
        return;
    }

    @$conn->query(
        'ALTER TABLE ' . tracking_identifier($table) .
        ' ADD COLUMN ' . tracking_identifier($column) . ' ' . $definition
    );
}

function tracking_ensure_schema(mysqli $conn): void
{
    if (!tracking_table_exists($conn, 'orders')) {
        return;
    }

    tracking_add_column_if_missing($conn, 'orders', 'tracking_number', 'VARCHAR(120) DEFAULT NULL');
    tracking_add_column_if_missing($conn, 'orders', 'current_location', 'VARCHAR(255) DEFAULT NULL');
    tracking_add_column_if_missing($conn, 'orders', 'estimated_delivery', 'DATE DEFAULT NULL');
    tracking_add_column_if_missing($conn, 'orders', 'delivered_at', 'TIMESTAMP NULL DEFAULT NULL');

    @$conn->query(
        "CREATE TABLE IF NOT EXISTS order_tracking_history (
            id INT(11) NOT NULL AUTO_INCREMENT,
            order_id INT(11) NOT NULL,
            status VARCHAR(50) NOT NULL,
            location VARCHAR(255) DEFAULT NULL,
            description TEXT DEFAULT NULL,
            updated_by_staff_id INT(11) DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY order_tracking_history_order_id_idx (order_id),
            KEY order_tracking_history_created_at_idx (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST ?: $_GET ?: [];
}

$reference = tracking_clean($input['order_reference'] ?? $input['reference'] ?? '');
if ($reference === '') {
    tracking_json([
        'success' => false,
        'message' => 'Please enter your order reference or tracking number.'
    ], 422);
}

require __DIR__ . '/config/dbconn.php';

if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_errno) {
    tracking_json([
        'success' => false,
        'message' => 'Order tracking is temporarily unavailable.'
    ], 500);
}

tracking_ensure_schema($conn);

$stmt = $conn->prepare(
    "SELECT id, order_reference, customer_name, customer_email, customer_phone,
            total_amount, payment_method, payment_status, order_status,
            tracking_number, current_location, estimated_delivery, delivered_at, created_at
     FROM orders
     WHERE UPPER(order_reference) = ?
        OR UPPER(COALESCE(tracking_number, '')) = ?
     LIMIT 1"
);

if (!$stmt) {
    $conn->close();
    tracking_json([
        'success' => false,
        'message' => 'Order tracking is temporarily unavailable.'
    ], 500);
}

$stmt->bind_param('ss', $reference, $reference);
$stmt->execute();

$stmt->bind_result(
    $orderId,
    $orderReference,
    $customerName,
    $customerEmail,
    $customerPhone,
    $totalAmount,
    $paymentMethod,
    $paymentStatus,
    $orderStatus,
    $trackingNumber,
    $currentLocation,
    $estimatedDelivery,
    $deliveredAt,
    $createdAt
);

$order = null;
if ($stmt->fetch()) {
    $order = [
        'id' => $orderId,
        'order_reference' => $orderReference,
        'customer_name' => $customerName,
        'customer_email' => $customerEmail,
        'customer_phone' => $customerPhone,
        'total_amount' => $totalAmount,
        'payment_method' => $paymentMethod,
        'payment_status' => $paymentStatus,
        'order_status' => $orderStatus,
        'tracking_number' => $trackingNumber,
        'current_location' => $currentLocation,
        'estimated_delivery' => $estimatedDelivery,
        'delivered_at' => $deliveredAt,
        'created_at' => $createdAt,
    ];
}
$stmt->close();

if (!$order) {
    $conn->close();
    tracking_json([
        'success' => false,
        'message' => 'No order found for that reference or tracking number.'
    ], 404);
}

$history = [];
if (tracking_table_exists($conn, 'order_tracking_history')) {
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
        $historyStmt->bind_result($historyStatus, $historyLocation, $historyDescription, $historyCreatedAt);

        while ($historyStmt->fetch()) {
            $history[] = [
                'status' => $historyStatus,
                'location' => $historyLocation,
                'description' => $historyDescription,
                'created_at' => $historyCreatedAt,
            ];
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

$historyMaxStage = 0;
$latestHistoryLocation = '';

foreach ($history as $row) {
    $historyStage = tracking_stage_from_history_status($row['status'] ?? '');
    $historyMaxStage = max($historyMaxStage, $historyStage);

    if ($historyStage > 0 && empty($stageTimestamps[$historyStage])) {
        $stageTimestamps[$historyStage] = $row['created_at'] ?? '';
    }

    if (!empty($row['location'])) {
        $latestHistoryLocation = (string) $row['location'];
    }
}

if ($historyMaxStage > $stage) {
    $stage = $historyMaxStage;
}

if (empty($order['current_location']) && $latestHistoryLocation !== '') {
    $order['current_location'] = $latestHistoryLocation;
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
