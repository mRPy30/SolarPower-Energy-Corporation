<?php
if (session_status()===PHP_SESSION_NONE) session_start();
$input=json_decode(file_get_contents('php://input'),true);
if (!is_array($input)) $input=$_POST;
$_GET['action']=($input['mode']??'')==='buy_now'?'buynow':'add';
$GLOBALS['cart_request_override']=['item'=>$input];
require __DIR__.'/controllers/cart.php';
