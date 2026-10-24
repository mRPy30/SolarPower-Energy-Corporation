<?php
// ============================================
// AJAX HANDLER FOR PAYMAYA & COD ORDERS
// File: ajax-handler.php
// ============================================

session_start();
header('Content-Type: application/json');
// Include database connection
require_once 'config/dbconn.php';

// Get JSON input
$input = file_get_contents('php://input');
$requestData = json_decode($input, true);

// Validate request
if (!$requestData || !isset($requestData['action'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

if ($requestData['action'] === 'create_maya_checkout') {
    require_once __DIR__ . '/includes/checkout-service.php';

    if (isset($requestData['customer']) && is_array($requestData['customer'])) {
        $requestData['customerName'] = $requestData['customerName'] ?? ($requestData['customer']['name'] ?? '');
        $requestData['customerEmail'] = $requestData['customerEmail'] ?? ($requestData['customer']['email'] ?? '');
        $requestData['customerPhone'] = $requestData['customerPhone'] ?? ($requestData['customer']['phone'] ?? '');
        $requestData['customerAddress'] = $requestData['customerAddress'] ?? ($requestData['customer']['address'] ?? '');
    }

    try {
        $result = checkout_create_maya_checkout($conn, $requestData);
        if (empty($result['success'])) {
            http_response_code(502);
        }
        echo json_encode($result);
    } catch (RuntimeException $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'error' => $e->getMessage(),
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'error' => $e->getMessage(),
        ]);
    }

    $conn->close();
    exit;
}

if ($requestData['action']==='create_cod_order') {
    require __DIR__.'/controllers/ordering/create-cod-order.php';
    exit;
}
http_response_code(400);
echo json_encode(['success'=>false,'error'=>'Invalid action']);
