<?php
// PHP session serialization plus a stable attempt reference prevents browser retry double-orders.
function checkout_legacy_reference(mysqli $conn,array $input,string $method,string $fallback): string {
    $fingerprint=hash('sha256',json_encode([$method,$input]));
    $attempt=$_SESSION['legacy_checkout_attempts'][$fingerprint]??null;
    if(!$attempt || $attempt['expires']<time()) {
        $attempt=['reference'=>$fallback,'expires'=>time()+300];
        $_SESSION['legacy_checkout_attempts'][$fingerprint]=$attempt;
    }
    foreach($_SESSION['legacy_checkout_attempts'] as $key=>$entry)if($entry['expires']<time())unset($_SESSION['legacy_checkout_attempts'][$key]);
    $existing=checkout_find_order_by_reference($conn,$attempt['reference']);
    if($existing){echo json_encode(['success'=>true,'orderRef'=>$existing['order_reference'],'trackingNumber'=>$existing['tracking_number'],'message'=>'Your order was already received.']);exit;}
    return $attempt['reference'];
}
// Legacy COD/manual-payment requests still use database prices and delivery rates.
function checkout_legacy_totals(mysqli $conn,array $input,array $items): array {
    $validated=checkout_validate_items($conn,['items'=>$items]);
    if (empty($input['delivery_rate_id'])) {
        $location=trim((string)($input['selected_location_name']??$input['deliveryLocation']??''));
        $stmt=$conn->prepare('SELECT id FROM delivery_rates WHERE location_name=? LIMIT 2');
        $stmt->bind_param('s',$location);$stmt->execute();$rates=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        if(count($rates)!==1) throw new RuntimeException('Please use Checkout and select a configured delivery location.');
        $input['delivery_rate_id']=$rates[0]['id'];
    }
    $delivery=checkout_delivery_rate_from_input($conn,$input);
    $total=round($validated['subtotal']+$delivery['price'],2);
    foreach(array_values($items) as $i=>$item) {
        if (!isset($validated['items'][$i]) || !isset($item['price']) || round((float)$item['price'],2)!==$validated['items'][$i]['price']) throw new RuntimeException('Product prices changed. Refresh your cart and confirm the new total in Checkout.');
    }
    if (!isset($input['totalAmount']) || round((float)$input['totalAmount'],2)!==$total) throw new RuntimeException('Your total changed. Review the current prices and delivery charge in Checkout before confirming.');
    return ['validated'=>$validated,'delivery'=>$delivery,'total'=>$total];
}
function checkout_quote_token(array $validated,array $delivery): string {
    if (empty($_SESSION['checkout_quote_key'])) $_SESSION['checkout_quote_key']=bin2hex(random_bytes(32));
    $lines=[];
    foreach ($validated['items'] as $item) $lines[]=[(int)$item['product_id'],(int)($item['variant_id']??0),(int)$item['quantity'],(int)round($item['price']*100)];
    sort($lines);
    return hash_hmac('sha256',json_encode([$lines,(int)$delivery['id'],(int)round($delivery['price']*100)]),$_SESSION['checkout_quote_key']);
}
function checkout_confirm_quote(array $input,array $validated,array $delivery): void {
    $token=$input['quote_token']??'';
    if (!is_string($token) || !hash_equals(checkout_quote_token($validated,$delivery),$token)) {
        throw new RuntimeException('Prices or delivery charges changed. Review the refreshed checkout total and confirm again.');
    }
}
function checkout_maya_payment(array $pending): array {
    $config=checkout_maya_config();
    if (empty($config['secret_key']) || empty($pending['checkout_id'])) throw new RuntimeException('Payment needs manual verification. Inventory remains reserved.');
    $ch=curl_init(rtrim($config['base_url'],'/').'/payments/v1/payments/'.rawurlencode($pending['checkout_id']));
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>25,CURLOPT_CONNECTTIMEOUT=>8,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_HTTPHEADER=>['Authorization: Basic '.base64_encode($config['secret_key'].':')]]);
    $body=curl_exec($ch);$status=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);
    $payment=json_decode((string)$body,true);
    if ($status!==200 || !is_array($payment)) throw new RuntimeException('Unable to verify payment with Maya. Please retry later.');
    if (($payment['id']??'')!==$pending['checkout_id'] || ($payment['requestReferenceNumber']??'')!==$pending['order_reference']) throw new RuntimeException('Payment reference mismatch. Contact support.');
    return $payment;
}
function checkout_verify_maya_payment(array $pending): void {
    checkout_assert_maya_payment($pending,checkout_maya_payment($pending));
}
function checkout_assert_maya_payment(array $pending,array $payment): void {
    $amount=$payment['totalAmount']['value']??$payment['amount']??null;
    if (is_array($amount)) $amount=$amount['value']??null;
    $currency=$payment['totalAmount']['currency']??$payment['currency']??'';
    $status=$payment['paymentStatus']??$payment['status']??'';
    if ($status!=='PAYMENT_SUCCESS' || $currency!=='PHP' || !is_numeric($amount) || (int)round((float)$amount*100)!==(int)round((float)$pending['total_amount']*100)) throw new RuntimeException('Payment is not confirmed for the expected amount. Contact support if you were charged.');
}
function checkout_reconcile_failure(mysqli $conn,string $reference): void {
    $pending=checkout_load_pending_maya_checkout($conn,$reference);
    if (!$pending || $pending['status']!=='pending') return;
    $payment=checkout_maya_payment($pending);
    $status=$payment['paymentStatus']??$payment['status']??'';
    if (!in_array($status,['PAYMENT_FAILED','PAYMENT_CANCELLED','PAYMENT_EXPIRED'],true)) return;
    $conn->begin_transaction();
    try {
        $lock=$conn->prepare('SELECT status FROM maya_pending_checkouts WHERE order_reference=? FOR UPDATE');
        $lock->bind_param('s',$reference);$lock->execute();$row=$lock->get_result()->fetch_assoc();
        if ($row && $row['status']==='pending') { inventory_release($conn,$reference);checkout_mark_pending_maya_status($conn,$reference,'failed'); }
        $conn->commit();
    } catch(Throwable $e){$conn->rollback();throw $e;}
}
