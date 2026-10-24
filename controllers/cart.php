<?php
if (session_status()===PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');header('Cache-Control: no-store');
require_once __DIR__.'/../config/dbconn.php';
require_once __DIR__.'/../includes/checkout-service.php';
function cart_refresh(mysqli $conn,array $rows): array {
    if (!$rows) return [];
    $validated=checkout_validate_items($conn,['items'=>$rows]);
    return array_map(static function($item){
        return $item+['id'=>$item['product_id'],'displayName'=>$item['display_name'],'name'=>$item['product_name'],'brandName'=>$item['brand_name']];
    },$validated['items']);
}
try {
    $action=$_GET['action']??'get';
    $input=$GLOBALS['cart_request_override']??checkout_json_input();
    $old=array_values(array_filter($_SESSION['cart']??[],'is_array'));$cart=$old;
    if ($action!=='get' && ($_SERVER['REQUEST_METHOD']??'')!=='POST') {http_response_code(405);throw new RuntimeException('POST required.');}
    if ($action==='clear') $cart=[];
    elseif ($action==='remove') { $index=filter_var($input['index']??null,FILTER_VALIDATE_INT);if($index===false || !isset($cart[$index]))throw new RuntimeException('Invalid cart item.');array_splice($cart,$index,1); }
    elseif ($action==='update') { $index=filter_var($input['index']??null,FILTER_VALIDATE_INT);if($index===false || !isset($cart[$index]))throw new RuntimeException('Invalid cart item.');$cart[$index]['quantity']=inventory_quantity($input['quantity']??null); }
    elseif ($action==='sync') {if(!isset($input['cart']) || !is_array($input['cart']))throw new RuntimeException('Invalid cart.');$cart=$input['cart'];}
    elseif (in_array($action,['add','buynow'],true)) {if(!isset($input['item']) || !is_array($input['item']))throw new RuntimeException('Invalid item.');if($action==='buynow')$cart=[];$cart[]=$input['item'];}
    elseif ($action!=='get') throw new RuntimeException('Unknown cart action.');
    // Removing an unavailable line must remain possible even when another line is invalid.
    if (in_array($action,['remove','clear'],true)) $_SESSION['cart']=$cart;
    $fresh=cart_refresh($conn,$cart);
    $changed=false;
    if ($action==='get') foreach($fresh as $i=>$item) if(!isset($old[$i]['price']) || round((float)$old[$i]['price'],2)!==$item['price'])$changed=true;
    if($changed)$_SESSION['cart_price_changed']=true;
    $_SESSION['cart']=$fresh;
    echo json_encode(['success'=>true,'status'=>'success','message'=>'Cart updated.','cart'=>$fresh,'total_items'=>count($fresh),'price_changed'=>!empty($_SESSION['cart_price_changed'])]);
} catch(RuntimeException $e){http_response_code(409);echo json_encode(['success'=>false,'status'=>'error','message'=>$e->getMessage(),'error'=>$e->getMessage(),'cart'=>array_values($_SESSION['cart']??[])]);}
catch(Throwable $e){http_response_code(503);echo json_encode(['success'=>false,'message'=>'Unable to verify cart stock and prices.']);}
