<?php
// CLI only; default is read-only. --apply explicitly finalizes verified payments/releases verified failures.
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require_once __DIR__.'/../config/dbconn.php';
require_once __DIR__.'/../includes/checkout-service.php';
$apply=in_array('--apply',$argv,true);
$rows=$conn->query("SELECT order_reference FROM maya_pending_checkouts WHERE status='pending' ORDER BY id LIMIT 100")->fetch_all(MYSQLI_ASSOC);
foreach($rows as $row){
    $ref=$row['order_reference'];
    try {
        $pending=checkout_load_pending_maya_checkout($conn,$ref);
        $payment=checkout_maya_payment($pending);
        $status=$payment['paymentStatus']??$payment['status']??'UNKNOWN';
        if($apply){
            if($status==='PAYMENT_SUCCESS') checkout_finalize_paid_maya_order($conn,$ref,$pending['success_token']);
            else checkout_reconcile_failure($conn,$ref);
        }
        echo $ref,' ', $status,$apply?' reconciled':' (read-only)',PHP_EOL;
    }catch(Throwable $e){echo $ref," requires manual verification; hold retained.",PHP_EOL;}
}
