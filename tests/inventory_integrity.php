<?php
// Dedicated disposable server only. Never reads application database credentials.
if (PHP_SAPI!=='cli') exit;
ini_set('session.save_path',sys_get_temp_dir());
mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
$conn=new mysqli('127.0.0.1','root','','',3319);
$db='solar_inventory_fixture';
$conn->query("CREATE DATABASE IF NOT EXISTS $db");$conn->select_db($db);
function solar_send_resend_email(string $to,string $subject,string $html,array $options=[]):array {return ['success'=>true,'message'=>'Mock transport'];}
require_once __DIR__.'/../includes/checkout-service.php';
if (($argv[1]??'')==='race') {
    $conn->begin_transaction();
    try {inventory_allocate($conn,'race-'.$argv[2],[['product_id'=>1,'quantity'=>1]]);usleep(150000);$conn->commit();echo 'won';}
    catch(Throwable $e){$conn->rollback();echo 'rejected';}exit;
}
foreach(['inventory_allocations','maya_pending_checkouts','order_items','orders','product_images','product_brand_variants','brands','supplier_brands','delivery_rates','product'] as $table)$conn->query("DROP TABLE IF EXISTS `$table`");
// Minimal fixtures use columns already present in the repository schemas/queries.
$sql=[
"CREATE TABLE product(id INT PRIMARY KEY,displayName VARCHAR(100),brandName VARCHAR(100),category VARCHAR(100),packageType VARCHAR(100),stockQuantity INT,moq INT,price DECIMAL(10,2),imagePath VARCHAR(100),status VARCHAR(20)) ENGINE=InnoDB",
"CREATE TABLE product_brand_variants(id INT PRIMARY KEY,product_id INT,brand_id INT,variant_name VARCHAR(100),price DECIMAL(10,2),variant_image VARCHAR(100)) ENGINE=InnoDB",
"CREATE TABLE brands(brand_id INT PRIMARY KEY,brand_name VARCHAR(100)) ENGINE=InnoDB",
"CREATE TABLE supplier_brands(id INT PRIMARY KEY,brandName VARCHAR(100)) ENGINE=InnoDB",
"CREATE TABLE product_images(id INT PRIMARY KEY,product_id INT,image_path VARCHAR(100)) ENGINE=InnoDB",
"CREATE TABLE delivery_rates(id INT PRIMARY KEY,origin_address VARCHAR(100),rate_type VARCHAR(50),location_name VARCHAR(100),price DECIMAL(10,2)) ENGINE=InnoDB",
"CREATE TABLE orders(id INT AUTO_INCREMENT PRIMARY KEY,client_id INT NULL,order_reference VARCHAR(100) UNIQUE,customer_name VARCHAR(100),customer_email VARCHAR(100),customer_phone VARCHAR(50),customer_address VARCHAR(255),delivery_location VARCHAR(150),items_subtotal DECIMAL(12,2),delivery_fee DECIMAL(12,2),total_amount DECIMAL(12,2),payment_method VARCHAR(30),payment_status VARCHAR(30),order_status VARCHAR(30),sales_channel VARCHAR(50),tracking_number VARCHAR(120)) ENGINE=InnoDB",
"CREATE TABLE order_items(id INT AUTO_INCREMENT PRIMARY KEY,order_id INT,product_id INT,product_name VARCHAR(200),quantity INT,price DECIMAL(12,2),subtotal DECIMAL(12,2)) ENGINE=InnoDB",
];foreach($sql as $query)$conn->query($query);
$conn->query(file_get_contents(__DIR__.'/../database-02/inventory_allocations.sql'));
checkout_ensure_pending_maya_table($conn);
$conn->query("INSERT INTO product VALUES(1,'Fixture','Brand','Battery',NULL,10,1,99,'','Active'),(2,'Empty','Brand','Battery',NULL,0,1,50,'','Active'),(3,'Hidden','Brand','Battery',NULL,10,1,50,'','Hidden'),(4,'Bad price','Brand','Battery',NULL,10,1,-1,'','Active')");
$conn->query("INSERT INTO brands VALUES(1,'Fixture brand')");
$conn->query("INSERT INTO product_brand_variants VALUES(1,1,1,'Model A',100,''),(2,1,1,'Model B',150,'')");
$conn->query("INSERT INTO delivery_rates VALUES(1,'Origin','province','Test location',20)");
$checks=0;
function ok($condition,$label){global $checks;if(!$condition)throw new RuntimeException('FAIL '.$label);$checks++;echo "PASS $label\n";}
function rejects(callable $fn,$label){try{$fn();}catch(RuntimeException $e){ok(true,$label);return;}ok(false,$label);}
function item($id=1,$qty=1,$variant=1){return ['product_id'=>$id,'quantity'=>$qty,'variant_id'=>$variant,'price'=>1];}
function stock(){global $conn;return (int)$conn->query('SELECT stockQuantity FROM product WHERE id=1')->fetch_row()[0];}
$v=checkout_validate_items($conn,['items'=>[item()]]);
ok($v['subtotal']===100.0,'crafted browser price ignored');
ok(checkout_fetch_product($conn,1,null)['price']==100,'base selects same cheapest variant as listing');
ok(checkout_fetch_product($conn,1,null,2)['price']==150,'specific variant price respected');
foreach([0,-1,1.5,'1e3','abc'] as $qty)rejects(fn()=>checkout_validate_items($conn,['items'=>[item(1,$qty)]]),'reject invalid quantity '.json_encode($qty));
foreach([2,3,4,999] as $id)rejects(fn()=>checkout_validate_items($conn,['items'=>[item($id,1,null)]]),'reject unavailable/invalid product '.$id);
rejects(fn()=>checkout_validate_items($conn,['items'=>[item(1,11)]]),'overstock rejected');
rejects(fn()=>checkout_validate_items($conn,['items'=>[item(1,6,1),item(1,5,2)]]),'variants share parent stock budget');
rejects(fn()=>checkout_validate_items($conn,['items'=>[item(1,1,999)]]),'deleted variant rejected');
$conn->query('UPDATE product SET stockQuantity=4 WHERE id=1');
ok(checkout_fetch_product($conn,1,null,1)['stockQuantity']==4,'saved stock immediately read');
$conn->query('UPDATE product_brand_variants SET price=120 WHERE id=1');
$fresh=checkout_validate_items($conn,['items'=>[item()]]);
ok($fresh['subtotal']===120.0,'saved variant price immediately read');
$delivery=checkout_delivery_rate_from_input($conn,['delivery_rate_id'=>1,'calculated_delivery_fee'=>0]);
ok($delivery['price']===20.0,'crafted shipping ignored');
$token=checkout_quote_token($v,$delivery);
rejects(fn()=>checkout_confirm_quote(['quote_token'=>$token],$fresh,$delivery),'price change invalidates confirmation');
$token=checkout_quote_token($fresh,$delivery);checkout_confirm_quote(['quote_token'=>$token],$fresh,$delivery);ok(true,'current quote accepted');
rejects(fn()=>checkout_confirm_quote(['quote_token'=>'forged'],$fresh,$delivery),'forged confirmation rejected');
$customer=['name'=>'Fixture','email'=>'candidate@example.test','phone'=>'09000000000','address'=>'Test address'];
$conn->begin_transaction();inventory_allocate($conn,'fixture-paid',$fresh['items']);checkout_store_pending_maya_checkout($conn,'fixture-paid','mock','https://example.test','token',$customer,$fresh,$delivery);$conn->commit();
ok(stock()===3,'checkout reservation decrements available stock once');
$order=checkout_create_order($conn,$customer,$fresh,$delivery,'fixture-paid');
ok(stock()===3,'paid finalization does not decrement again');
$repeat=checkout_create_order($conn,$customer,$fresh,$delivery,'fixture-paid');
ok($repeat['id']===$order['id'] && stock()===3,'duplicate finalization returns same order');
$conn->query('UPDATE product_brand_variants SET price=180 WHERE id=1');
ok((float)$conn->query('SELECT price FROM order_items')->fetch_row()[0]===120.0,'historical price snapshot unchanged');
$conn->begin_transaction();inventory_release($conn,'fixture-paid');$conn->commit();
$conn->begin_transaction();inventory_release($conn,'fixture-paid');$conn->commit();
ok(stock()===4,'repeat cancellation restores only once');
$conn->begin_transaction();inventory_allocate($conn,'fixture-failed',[item()]);$conn->commit();
$conn->begin_transaction();inventory_release($conn,'fixture-failed');$conn->commit();
ok(stock()===4,'failed checkout release restores stock');
$conn->begin_transaction();try{inventory_allocate($conn,'rollback',[item(1,1),item(2,1,null)]);}catch(Throwable $e){$conn->rollback();}
ok(stock()===4,'multi-product failure rolls back prior decrement');
$pending=['total_amount'=>140];$payment=['paymentStatus'=>'PAYMENT_SUCCESS','amount'=>'140','currency'=>'PHP'];
checkout_assert_maya_payment($pending,$payment);ok(true,'verified payment amount/status accepted');
rejects(fn()=>checkout_assert_maya_payment($pending,array_merge($payment,['amount'=>'1'])),'wrong payment amount rejected');
rejects(fn()=>checkout_assert_maya_payment($pending,array_merge($payment,['paymentStatus'=>'PAYMENT_PENDING'])),'pending payment not finalized');
foreach([-1,'','1.5'] as $qty)rejects(fn()=>inventory_quantity($qty,true),'admin stock validation '.json_encode($qty));
ok(inventory_quantity(0,true)===0,'admin zero stock retained');
foreach([-1,0,'abc','1.001'] as $price)rejects(fn()=>inventory_price($price),'admin price validation '.json_encode($price));
$conn->query('UPDATE product SET stockQuantity=1 WHERE id=1');
$pipes=[];$workers=[];
for($i=0;$i<2;$i++)$workers[$i]=proc_open([PHP_BINARY,__FILE__,'race',(string)$i],[1=>['pipe','w'],2=>['pipe','w']],$pipes[$i]);
$results=[];foreach($workers as $i=>$worker){$results[]=stream_get_contents($pipes[$i][1]);fclose($pipes[$i][1]);fclose($pipes[$i][2]);proc_close($worker);}
sort($results);ok($results===['rejected','won'] && stock()===0,'two simultaneous checkouts: exactly one gets last unit');
echo "TOTAL $checks checks passed. No live payments or customer data used.\n";
