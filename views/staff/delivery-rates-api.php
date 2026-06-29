<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/dbconn.php';
require_once __DIR__ . '/../../includes/checkout-service.php';

function delivery_rates_api_input(): array
{
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    return is_array($json) ? $json : $_POST;
}

function delivery_rates_api_clean($value): string
{
    $text = trim((string) $value);
    $text = preg_replace('/[\r\n\t]+/', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    return trim(strip_tags($text));
}

function delivery_rates_api_rate_payload(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'origin_address' => $row['origin_address'],
        'rate_type' => $row['rate_type'],
        'location_name' => $row['location_name'],
        'price' => (float) $row['price'],
        'updated_at' => $row['updated_at'],
    ];
}

try {
    checkout_ensure_delivery_rates_table($conn);
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        $result = $conn->query(
            "SELECT id, origin_address, rate_type, location_name, price, updated_at
             FROM delivery_rates
             ORDER BY FIELD(rate_type, 'km_range', 'province'), id ASC"
        );

        $rates = [];
        while ($row = $result->fetch_assoc()) {
            $rates[] = delivery_rates_api_rate_payload($row);
        }

        echo json_encode(['success' => true, 'rates' => $rates]);
        exit;
    }

    $input = delivery_rates_api_input();
    $action = $input['action'] ?? '';

    if ($method === 'POST' && $action === 'save') {
        $id = (int) ($input['id'] ?? 0);
        $rateType = delivery_rates_api_clean($input['rate_type'] ?? '');
        $locationName = delivery_rates_api_clean($input['location_name'] ?? '');
        $price = round((float) ($input['price'] ?? -1), 2);
        $originAddress = delivery_rates_api_clean($input['origin_address'] ?? 'Madrigal Business Park, Alabang, Muntinlupa');

        if (!in_array($rateType, ['km_range', 'province'], true)) {
            throw new RuntimeException('Rate type must be km_range or province.');
        }

        if ($locationName === '' || strlen($locationName) > 100) {
            throw new RuntimeException('Location name is required and must be 100 characters or fewer.');
        }

        if ($price < 0) {
            throw new RuntimeException('Price must be zero or greater.');
        }

        if ($originAddress === '') {
            $originAddress = 'Madrigal Business Park, Alabang, Muntinlupa';
        }

        if ($id > 0) {
            $stmt = $conn->prepare(
                'UPDATE delivery_rates
                 SET origin_address = ?, rate_type = ?, location_name = ?, price = ?
                 WHERE id = ?'
            );
            $stmt->bind_param('sssdi', $originAddress, $rateType, $locationName, $price, $id);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt = $conn->prepare(
                'INSERT INTO delivery_rates (origin_address, rate_type, location_name, price)
                 VALUES (?, ?, ?, ?)'
            );
            $stmt->bind_param('sssd', $originAddress, $rateType, $locationName, $price);
            $stmt->execute();
            $id = (int) $conn->insert_id;
            $stmt->close();
        }

        echo json_encode(['success' => true, 'message' => 'Delivery rate saved.', 'id' => $id]);
        exit;
    }

    if ($method === 'POST' && $action === 'delete') {
        $id = (int) ($input['id'] ?? 0);
        if ($id <= 0) {
            throw new RuntimeException('Invalid delivery rate id.');
        }

        $stmt = $conn->prepare('DELETE FROM delivery_rates WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['success' => true, 'message' => 'Delivery rate deleted.']);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Unsupported request.']);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
