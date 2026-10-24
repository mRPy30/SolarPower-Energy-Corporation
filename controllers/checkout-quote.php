<?php
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-store');
require_once __DIR__.'/../config/dbconn.php';
require_once __DIR__.'/../includes/checkout-service.php';
try {
    if ($_SERVER['REQUEST_METHOD']!=='POST') {http_response_code(405);exit;}
    $input=checkout_json_input();
    $validated=checkout_validate_items($conn,$input);
    $delivery=checkout_delivery_rate_from_input($conn,$input);
    echo json_encode(['success'=>true,'items'=>$validated['items'],'subtotal'=>$validated['subtotal'],'delivery_fee'=>$delivery['price'],'total'=>round($validated['subtotal']+$delivery['price'],2),'quote_token'=>checkout_quote_token($validated,$delivery)]);
} catch (RuntimeException $e) {http_response_code(409);echo json_encode(['success'=>false,'message'=>$e->getMessage()]);}
catch(Throwable $e){http_response_code(503);echo json_encode(['success'=>false,'message'=>'Unable to check current stock and prices. Please try again.']);}
