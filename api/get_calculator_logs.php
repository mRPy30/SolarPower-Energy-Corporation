<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include __DIR__ . "/../config/dbconn.php";

$dateFilter = isset($_GET['date_filter']) ? $_GET['date_filter'] : 'month';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : 'all'; // all, converted, no_action

// Build logs query
$query = "SELECT * FROM `calculator_logs` WHERE 1=1";
$params = [];
$types = "";

// Date filtering logic
$today = date('Y-m-d');
if ($dateFilter === 'today') {
    $query .= " AND DATE(timestamp) = ?";
    $params[] = $today;
    $types .= "s";
} elseif ($dateFilter === 'week') {
    $query .= " AND timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
} elseif ($dateFilter === 'month') {
    $query .= " AND timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
}

// Status filtering logic
if ($status === 'converted') {
    $query .= " AND action IN ('messenger', 'viber')";
} elseif ($status === 'no_action') {
    $query .= " AND action = 'calculated'";
}

// Search queries
if (!empty($search)) {
    $query .= " AND (user_type LIKE ? OR system_size LIKE ? OR lead_phone LIKE ? OR lead_email LIKE ? OR bill LIKE ?)";
    $likeSearch = '%' . $search . '%';
    $params[] = $likeSearch;
    $params[] = $likeSearch;
    $params[] = $likeSearch;
    $params[] = $likeSearch;
    $params[] = $likeSearch;
    $types .= "sssss";
}

$query .= " ORDER BY timestamp DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$logs = [];
while ($row = $result->fetch_assoc()) {
    $logs[] = [
        "id" => $row['id'],
        "timestamp" => $row['timestamp'],
        "user_type" => $row['user_type'],
        "lead_name" => $row['lead_name'],
        "lead_phone" => $row['lead_phone'],
        "lead_email" => $row['lead_email'],
        "bill" => floatval($row['bill']),
        "system_size" => $row['system_size'],
        "action" => $row['action'],
        "action_label" => $row['action_label']
    ];
}
$stmt->close();

// Compute Metrics based on filtered criteria
$totalUses = count($logs);
$totalBill = 0;
$systemSizes = [];
$conversions = 0;

foreach ($logs as $log) {
    $totalBill += $log['bill'];
    $systemSizes[] = $log['system_size'];
    if ($log['action'] === 'messenger' || $log['action'] === 'viber') {
        $conversions++;
    }
}

$avgBill = $totalUses > 0 ? round($totalBill / $totalUses) : 0;

// Calculate mode (most common system size)
$mostCommonSize = "N/A";
if (!empty($systemSizes)) {
    $valueCounts = array_count_values($systemSizes);
    arsort($valueCounts);
    $mostCommonSize = array_key_first($valueCounts);
}

$conversionRate = $totalUses > 0 ? round(($conversions / $totalUses) * 100, 1) : 0;

// Construct timeline chart trends query
$chartLabels = [];
$chartData = [];

if ($dateFilter === 'today') {
    // Group by hour for today
    $chartQuery = "SELECT HOUR(timestamp) as hr, COUNT(id) as count 
                   FROM `calculator_logs` 
                   WHERE DATE(timestamp) = CURRENT_DATE 
                   GROUP BY HOUR(timestamp) 
                   ORDER BY HOUR(timestamp) ASC";
    $res = $conn->query($chartQuery);
    $hourlyCounts = [];
    while ($r = $res->fetch_assoc()) {
        $hourlyCounts[intval($r['hr'])] = intval($r['count']);
    }
    for ($i = 0; $i < 24; $i += 4) {
        $label = sprintf("%02d:00", $i);
        $chartLabels[] = $label;
        $countVal = 0;
        for ($j = $i; $j < $i+4; $j++) {
            $countVal += isset($hourlyCounts[$j]) ? $hourlyCounts[$j] : 0;
        }
        $chartData[] = $countVal;
    }
} else {
    // Group by day of week
    $chartQuery = "SELECT DATE_FORMAT(timestamp, '%a') as day_name, DATE(timestamp) as day_date, COUNT(id) as count 
                   FROM `calculator_logs` 
                   WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
                   GROUP BY DATE(timestamp) 
                   ORDER BY DATE(timestamp) ASC";
    $res = $conn->query($chartQuery);
    while ($r = $res->fetch_assoc()) {
        $chartLabels[] = $r['day_name'];
        $chartData[] = intval($r['count']);
    }
}

// Fallback chart trends if empty database
if (empty($chartLabels)) {
    $chartLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    $chartData = [0, 0, 0, 0, 0, 0, 0];
}

$response = [
    "metrics" => [
        "total_uses" => $totalUses,
        "avg_bill" => $avgBill,
        "most_common_size" => $mostCommonSize,
        "conversion_rate" => $conversionRate
    ],
    "chart" => [
        "labels" => $chartLabels,
        "data" => $chartData
    ],
    "logs" => $logs
];

echo json_encode($response);
$conn->close();
exit;
