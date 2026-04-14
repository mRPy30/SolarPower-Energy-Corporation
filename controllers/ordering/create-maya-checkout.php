<?php
header('Content-Type: application/json');

$mayaKey = 'YOUR_PUBLIC_KEY';
$mayaSecret = 'YOUR_SECRET_KEY';
$baseUrl = 'https://pg-sandbox.paymaya.com'; // sandbox
// PROD later: https://pg.paymaya.com

$data = json_decode(file_get_contents("php://input"), true);

$amount = number_format($data['amount'], 2, '.', '');
$paymentType = $data['payment_type'];

$checkoutData = [
    "totalAmount" => [
        "value" => $amount,
        "currency" => "PHP"
    ],
    "buyer" => [
        "firstName" => "Customer",
        "lastName" => "Solar",
        "email" => "customer@email.com"
    ],
    "redirectUrl" => [
        "success" => "http://localhost/solar/views/payment-success.php",
        "failure" => "http://localhost/solar/views/payment-failed.php",
        "cancel"  => "http://localhost/solar/views/payment-cancelled.php"
    ],
    "requestReferenceNumber" => "SP-" . time()
];

$ch = curl_init($baseUrl . "/checkout/v1/checkouts");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Basic " . base64_encode($mayaKey . ":" . $mayaSecret)
    ],
    CURLOPT_POSTFIELDS => json_encode($checkoutData)
]);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if (isset($result['redirectUrl'])) {
    echo json_encode([
        'checkoutUrl' => $result['redirectUrl']
    ]);
} else {
    echo json_encode([
        'message' => 'Maya checkout creation failed',
        'error' => $result
    ]);
}
