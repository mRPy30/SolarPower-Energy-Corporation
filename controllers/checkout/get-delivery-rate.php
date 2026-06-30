<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once __DIR__ . '/../../config/dbconn.php';
require_once __DIR__ . '/../../includes/checkout-service.php';

function delivery_rate_input(): array
{
    $input = json_decode(file_get_contents('php://input'), true);
    if (is_array($input)) {
        return $input;
    }

    return $_POST ?: $_GET ?: [];
}

function delivery_rate_clean($value): string
{
    $text = strtoupper(trim((string) $value));
    $text = str_replace(['(', ')', '.', ','], ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    return trim($text);
}

function delivery_rate_requested_location(array $input): string
{
    $provinceRegion = delivery_rate_clean($input['province_region'] ?? $input['province'] ?? $input['region'] ?? $input['location'] ?? '');
    $city = delivery_rate_clean($input['city'] ?? $input['municipality'] ?? '');

    if ($provinceRegion !== '') {
        if (strpos($provinceRegion, 'NCR') !== false || strpos($provinceRegion, 'METRO MANILA') !== false || strpos($provinceRegion, 'NATIONAL CAPITAL') !== false) {
            return 'METRO MANILA';
        }

        return $provinceRegion;
    }

    if ($city !== '') {
        return $city;
    }

    return '';
}

try {
    checkout_ensure_delivery_rates_table($conn);

    $input = delivery_rate_input();

    // Capture parameters (supporting JSON payload and standard POST/GET)
    $province = $input['province_region'] ?? $input['province'] ?? $_POST['province'] ?? '';
    $city = $input['city'] ?? $input['municipality'] ?? $_POST['city'] ?? '';

    $provinceClean = trim((string)$province);
    $cityClean = trim((string)$city);

    // Intelligent Metro Manila Override Switch
    if ($provinceClean === 'Metro Manila (NCR)') {
        $ncr_city_mapping = [
            'CITY OF MUNTINLUPA' => 'Metro Manila 1-5 km',
            'MUNTINLUPA' => 'Metro Manila 1-5 km',
            'MUNTINLUPA CITY' => 'Metro Manila 1-5 km',
            
            'CITY OF LAS PIÑAS' => 'Metro Manila 6-10 km',
            'CITY OF LAS PINAS' => 'Metro Manila 6-10 km',
            'LAS PIÑAS' => 'Metro Manila 6-10 km',
            'LAS PINAS' => 'Metro Manila 6-10 km',
            'LAS PIÑAS CITY' => 'Metro Manila 6-10 km',
            'LAS PINAS CITY' => 'Metro Manila 6-10 km',
            
            'CITY OF PARAÑAQUE' => 'Metro Manila 11-20 km',
            'CITY OF PARANAQUE' => 'Metro Manila 11-20 km',
            'PARAÑAQUE' => 'Metro Manila 11-20 km',
            'PARANAQUE' => 'Metro Manila 11-20 km',
            'PARAÑAQUE CITY' => 'Metro Manila 11-20 km',
            'PARANAQUE CITY' => 'Metro Manila 11-20 km',
            
            'CITY OF TAGUIG' => 'Metro Manila 11-20 km',
            'TAGUIG' => 'Metro Manila 11-20 km',
            'TAGUIG CITY' => 'Metro Manila 11-20 km',
            
            'PASAY CITY' => 'Metro Manila 11-20 km',
            'CITY OF PASAY' => 'Metro Manila 11-20 km',
            'PASAY' => 'Metro Manila 11-20 km',
            
            'CITY OF MAKATI' => 'Metro Manila 11-20 km',
            'MAKATI' => 'Metro Manila 11-20 km',
            'MAKATI CITY' => 'Metro Manila 11-20 km',
            
            'PATEROS' => 'Metro Manila 11-20 km',
            'MUNICIPALITY OF PATEROS' => 'Metro Manila 11-20 km',
            
            'CITY OF MANILA' => 'Metro Manila 21-30 km',
            'MANILA' => 'Metro Manila 21-30 km',
            'FIRST DISTRICT' => 'Metro Manila 21-30 km',
            
            'CITY OF PASIG' => 'Metro Manila 21-30 km',
            'PASIG' => 'Metro Manila 21-30 km',
            'PASIG CITY' => 'Metro Manila 21-30 km',
            
            'CITY OF MANDALUYONG' => 'Metro Manila 21-30 km',
            'MANDALUYONG' => 'Metro Manila 21-30 km',
            'MANDALUYONG CITY' => 'Metro Manila 21-30 km',
            
            'CITY OF SAN JUAN' => 'Metro Manila 21-30 km',
            'SAN JUAN' => 'Metro Manila 21-30 km',
            'SAN JUAN CITY' => 'Metro Manila 21-30 km',
            
            'QUEZON CITY' => 'Metro Manila 31-40 km',
            'CITY OF QUEZON' => 'Metro Manila 31-40 km',
            'QUEZON' => 'Metro Manila 31-40 km',
            
            'CITY OF MARIKINA' => 'Metro Manila 31-40 km',
            'MARIKINA' => 'Metro Manila 31-40 km',
            'MARIKINA CITY' => 'Metro Manila 31-40 km',
            
            'CITY OF CALOOCAN' => 'Metro Manila 31-40 km',
            'CALOOCAN' => 'Metro Manila 31-40 km',
            'CALOOCAN CITY' => 'Metro Manila 31-40 km',
            
            'CITY OF MALABON' => 'Metro Manila 31-40 km',
            'MALABON' => 'Metro Manila 31-40 km',
            'MALABON CITY' => 'Metro Manila 31-40 km',
            
            'CITY OF NAVOTAS' => 'Metro Manila 31-40 km',
            'NAVOTAS' => 'Metro Manila 31-40 km',
            'NAVOTAS CITY' => 'Metro Manila 31-40 km',
            
            'CITY OF VALENZUELA' => 'Metro Manila 41-50 km',
            'VALENZUELA' => 'Metro Manila 41-50 km',
            'VALENZUELA CITY' => 'Metro Manila 41-50 km',
        ];

        $normalizedCity = delivery_rate_clean($cityClean);
        $location = $ncr_city_mapping[$normalizedCity] ?? 'Metro Manila 1-5 km';
    } else {
        $location = delivery_rate_requested_location($input);
    }

    if ($location === '') {
        echo json_encode([
            'success' => false,
            'message' => 'No delivery location selected.',
        ]);
        exit;
    }

    $searchLike = '%' . $location . '%';
    $stmt = $conn->prepare(
        "SELECT id, origin_address, rate_type, location_name, price
         FROM delivery_rates
         WHERE UPPER(location_name) = ?
            OR UPPER(location_name) LIKE ?
            OR ? LIKE CONCAT('%', UPPER(location_name), '%')
         ORDER BY
            CASE WHEN UPPER(location_name) = ? THEN 0 ELSE 1 END,
            price ASC,
            id ASC
         LIMIT 1"
    );
    $stmt->bind_param('ssss', $location, $searchLike, $location, $location);
    $stmt->execute();
    $rate = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$rate || (float) $rate['price'] <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Delivery is not available for this location.',
            'requested_location' => $location,
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'rate' => [
            'id' => (int) $rate['id'],
            'origin_address' => $rate['origin_address'],
            'rate_type' => $rate['rate_type'],
            'location_name' => $rate['location_name'],
            'price' => (float) $rate['price'],
        ],
        'delivery_fee' => (float) $rate['price'],
        'requested_location' => $location,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to check delivery availability.',
        'error' => $e->getMessage(),
    ]);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
