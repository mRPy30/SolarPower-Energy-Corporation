<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once __DIR__ . '/../../config/dbconn.php';
require_once __DIR__ . '/../../includes/checkout-service.php';

try {
    checkout_ensure_delivery_rates_table($conn);

    $result = $conn->query(
        "SELECT id, origin_address, rate_type, location_name, price, updated_at
         FROM delivery_rates
         ORDER BY FIELD(rate_type, 'km_range', 'province'), id ASC"
    );

    $rates = [];
    while ($row = $result->fetch_assoc()) {
        $rates[] = [
            'id' => (int) $row['id'],
            'origin_address' => $row['origin_address'],
            'rate_type' => $row['rate_type'],
            'location_name' => $row['location_name'],
            'price' => (float) $row['price'],
            'updated_at' => $row['updated_at'],
        ];
    }

    echo json_encode([
        'success' => true,
        'origin' => 'Madrigal Business Park, Alabang, Muntinlupa',
        'rates' => $rates,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to load delivery rates.',
        'error' => $e->getMessage(),
    ]);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
