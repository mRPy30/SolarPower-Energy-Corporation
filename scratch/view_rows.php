<?php
include 'config/dbconn.php';

echo "=== product_brand_variants rows ===\n";
$res = $conn->query("SELECT * FROM product_brand_variants LIMIT 10");
while($row = $res->fetch_assoc()) {
    print_r($row);
}

echo "=== supplier_brands rows ===\n";
$res = $conn->query("SELECT * FROM supplier_brands LIMIT 10");
while($row = $res->fetch_assoc()) {
    print_r($row);
}

echo "=== brands rows ===\n";
$res = $conn->query("SELECT * FROM brands LIMIT 10");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
