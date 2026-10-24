<?php
// Read-only inventory report. No orders, customer details, credentials or schema writes.
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require_once __DIR__.'/../config/dbconn.php';
$queries=[
 'suspicious_products'=>"SELECT id,displayName,stockQuantity,price,status FROM product WHERE stockQuantity>=9999 OR stockQuantity<0 OR price<=0",
 'invalid_variants'=>"SELECT id,product_id,brand_id,price FROM product_brand_variants WHERE price<=0",
 'same_brand_models'=>"SELECT product_id,brand_id,COUNT(*) AS model_count FROM product_brand_variants GROUP BY product_id,brand_id HAVING COUNT(*)>1",
 'base_variant_price_difference'=>"SELECT p.id,p.displayName,p.price AS base_price,MIN(v.price) AS variant_min_price FROM product p JOIN product_brand_variants v ON v.product_id=p.id GROUP BY p.id,p.displayName,p.price HAVING p.price<>MIN(v.price)",
];
foreach($queries as $name=>$query){echo $name,PHP_EOL;echo json_encode($conn->query($query)->fetch_all(MYSQLI_ASSOC),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE),PHP_EOL;}
