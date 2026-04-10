<?php
require_once '../../config/dbconn.php';
$config = require '../../config/maya.php';

$data = json_decode(file_get_contents("php://input"), true);

$paymentType = $data['paymentType']; // full | downpayment
$items = $data['items'];
$customer = $data['customer'];
$total = $data['amount'];

$amountToPay = ($paymentType === 'downpayment') ? $total * 0.5 : $total;

$orderRef = uniqid("SP-");

/* ===============================
   FIX PHONE NUMBER FOR MAYA
   =============================== */
$rawPhone = preg_replace('/\D/', '', $customer['phone']);

if (strlen($rawPhone) === 11 && str_starts_with($rawPhone, '09')) {
    // 09123456789 → +639123456789
    $phone = '+63' . substr($rawPhone, 1);
} elseif (strlen($rawPhone) === 10 && str_starts_with($rawPhone, '9')) {
    // 9123456789 → +639123456789
    $phone = '+63' . $rawPhone;
} else {
    // Fallback for sandbox testing
    $phone = '+639991234567';
}
/* =============================== */

$payload = [
    "totalAmount" => [
        "value" => round($amountToPay, 2),
        "currency" => "PHP"
    ],
    "buyer" => [
        "firstName" => $customer['name'],
        "contact" => [
            "email" => $customer['email'],
            "phone" => $phone
        ]
    ],
    "items" => array_map(function($item) {
        return [
            "name" => $item['displayName'],
            "quantity" => isset($item['quantity']) ? $item['quantity'] : 1,
            "totalAmount" => [
                "value" => $item['price'] * (isset($item['quantity']) ? $item['quantity'] : 1),
                "currency" => "PHP"
            ]
        ];
    }, $items),
    "requestReferenceNumber" => $orderRef,
    "redirectUrl" => [
        "success" => "https://solarpower.com.ph/payment-success.php",
        "failure" => "https://solarpower.com.ph/payment-failed.php",
        "cancel"  => "https://solarpower.com.ph/payment-cancelled.php"
    ]
];

$ch = curl_init($config['base_url'] . "/checkout/v1/checkouts");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Basic " . base64_encode($config['secret_key'] . ":")
    ]
]);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
